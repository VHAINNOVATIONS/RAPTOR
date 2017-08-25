using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SQLite;
using System.IO;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sqlite
{
    public class SqliteDao
    {
        const string TABLE_NAME_PREFIX = "SITE_";
        const string SQLITE_FILE_NAME = "MockMdoData.sqlite";

        bool _cxnIsOpen;
        SQLiteConnection _cxn;

        public SqliteDao()
        {
            _cxn = new SQLiteConnection(getResourcesPath());
        }

        public object getObject(string dataSourceId, string objectHashString)
        {
            SQLiteCommand cmd = new SQLiteCommand("SELECT ASSEMBLY_NAME, DOMAIN_OBJECT_SIZE, DOMAIN_OBJECT, QUERY_STRING FROM " + TABLE_NAME_PREFIX + dataSourceId +
                " WHERE QUERY_STRING_HASH = '" + objectHashString + "';", _cxn);

            try
            {
                openConnection();

                SQLiteDataReader rdr = cmd.ExecuteReader();

                if (rdr.Read())
                {
                    string assemblyName = rdr.GetString(0); // probably don't need this since we know how to cast the object type already 
                    Int32 domainObjectSize = rdr.GetInt32(1);
                    byte[] domainObject = new byte[domainObjectSize];
                    rdr.GetBytes(2, 0, domainObject, 0, domainObjectSize);
                    string dbQueryString = rdr.GetString(3);

                    return deserializeObject(domainObject);
                }
                else
                {
                    return null;
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                cmd.Dispose();
                closeConnection();
            }
        }

        internal object deserializeObject(byte[] domainObject)
        {
            System.Runtime.Serialization.Formatters.Binary.BinaryFormatter bf = new System.Runtime.Serialization.Formatters.Binary.BinaryFormatter();
            return bf.Deserialize(new MemoryStream(domainObject));
        }

        internal byte[] serializeObject(object domainObject)
        {
            MemoryStream ms = new MemoryStream();
            System.Runtime.Serialization.Formatters.Binary.BinaryFormatter bf = new System.Runtime.Serialization.Formatters.Binary.BinaryFormatter();
            bf.Serialize(ms, domainObject);
            byte[] buffer = new byte[ms.Length];
            buffer = ms.ToArray();
            ms.Dispose();
            return buffer;
        }


        internal void updateObject(string dataSourceId, string hashedOldQueryString, string newQueryString, string hashedNewQueryString, string queryResults)
        {
            string fullAssemblyName = queryResults.GetType().ToString();
            byte[] buffer = serializeObject(queryResults);

            SQLiteCommand command = new SQLiteCommand("UPDATE " + TABLE_NAME_PREFIX + dataSourceId + " SET DOMAIN_OBJECT_SIZE = " + buffer.Length +
                ", DOMAIN_OBJECT = @domainObject, QUERY_STRING = @newQueryString, QUERY_STRING_HASH = @newHash, LAST_UPDATED = CURRENT_TIMESTAMP WHERE QUERY_STRING_HASH = '" + hashedOldQueryString + "';", _cxn);

            SQLiteParameter objParam = new SQLiteParameter("@domainObject", System.Data.DbType.Binary);
            objParam.Value = buffer;
            command.Parameters.Add(objParam);

            SQLiteParameter newQueryStringParam = new SQLiteParameter("@newQueryString", System.Data.DbType.String);
            newQueryStringParam.Value = newQueryString;
            command.Parameters.Add(newQueryStringParam);

            SQLiteParameter hashedNewQueryStringParam = new SQLiteParameter("@newHash", System.Data.DbType.String);
            hashedNewQueryStringParam.Value = hashedNewQueryString;
            command.Parameters.Add(hashedNewQueryStringParam);

            try
            {
                openConnection();

                if (command.ExecuteNonQuery() != 1)
                {
                    throw new exceptions.MdoException("Failed to update record: " + hashedOldQueryString + " to table " + dataSourceId);
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                command.Dispose();
                closeConnection();
            }
        }

        public void updateObject(string dataSourceId, string objectHashString, object queryResults)
        {
            string fullAssemblyName = queryResults.GetType().ToString();
            byte[] buffer = serializeObject(queryResults);

            if (!dataSourceId.StartsWith(TABLE_NAME_PREFIX))
            {
                dataSourceId = TABLE_NAME_PREFIX + dataSourceId;
            }

            SQLiteCommand command = new SQLiteCommand("UPDATE " + dataSourceId + " SET DOMAIN_OBJECT_SIZE = " + buffer.Length +
                ", DOMAIN_OBJECT = @domainObject, LAST_UPDATED = CURRENT_TIMESTAMP WHERE QUERY_STRING_HASH = '" + objectHashString + "';", _cxn);

            SQLiteParameter objParam = new SQLiteParameter("@domainObject", System.Data.DbType.Binary);
            objParam.Value = buffer;
            command.Parameters.Add(objParam);

            SQLiteParameter queryStringParam = new SQLiteParameter("@queryString", System.Data.DbType.String);
            queryStringParam.Value = queryResults;
            command.Parameters.Add(queryStringParam);

            try
            {
                openConnection();

                if (command.ExecuteNonQuery() != 1)
                {
                    throw new exceptions.MdoException("Failed to update record: " + objectHashString + " to table " + dataSourceId);
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                command.Dispose();
                closeConnection();
            }
        }

        public void saveObject(string dataSourceId, string queryString, object results)
        {
            string fullAssemblyName = results.GetType().ToString();
            byte[] buffer = serializeObject(results);
            string md5QueryStringHash = StringUtils.getMD5Hash(queryString);

            if (!dataSourceId.StartsWith(TABLE_NAME_PREFIX))
            {
                dataSourceId = TABLE_NAME_PREFIX + dataSourceId;
            }
            SQLiteCommand command = new SQLiteCommand("INSERT INTO " + dataSourceId + " (ASSEMBLY_NAME, DOMAIN_OBJECT_SIZE, DOMAIN_OBJECT, QUERY_STRING, QUERY_STRING_HASH) " +
                "VALUES ('" + fullAssemblyName + "', " + buffer.Length + ", @domainObject, @queryString, '" + md5QueryStringHash + "');", _cxn);

            SQLiteParameter objParam = new SQLiteParameter("@domainObject", System.Data.DbType.Binary);
            objParam.Value = buffer;
            command.Parameters.Add(objParam);

            SQLiteParameter queryStringParam = new SQLiteParameter("@queryString", System.Data.DbType.String);
            queryStringParam.Value = queryString;
            command.Parameters.Add(queryStringParam);

            try
            {
                openConnection();

                if (!hasTable(dataSourceId))
                {
                    createTableForSite(dataSourceId);
                }

                if (command.ExecuteNonQuery() != 1)
                {
                    throw new exceptions.MdoException("Failed to save query: " + queryString + " to table " + dataSourceId);
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                command.Dispose();
                closeConnection();
            }
        }

        public void saveOrUpdateObject(string dataSourceId, string queryString, object results)
        {
            try
            {
                saveObject(dataSourceId, queryString, results);
            }
            catch (System.Data.SQLite.SQLiteException exc)
            {
                if (exc.ErrorCode == (int)SQLiteErrorCode.Constraint)
                {
                    try
                    {
                        updateObject(dataSourceId, StringUtils.getMD5Hash(queryString), results);
                    }
                    catch (Exception updateExc)
                    {
                        throw new gov.va.medora.mdo.exceptions.MdoException("Unable to saveOrUpdate to SQLite", updateExc);
                    }
                }
                else
                {
                    throw;
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                closeConnection();
            }
        }

        void openConnection()
        {
            if (!_cxnIsOpen)
            {
                _cxn.Open();
            }
            _cxnIsOpen = true;
        }

        void closeConnection()
        {
            _cxnIsOpen = false;
            _cxn.Close();
        }

        // needs a bit of work... very ugly but it seems to serve it's purpose for running tests froim mdo-x and mdo-test
        internal string getResourcesPath()
        {
            // @"Data Source=C:\workspace\MDWS\branches\vhaannmewtoj\mdo\mdo-test\resources\data\MockMdoData";

            string executingAssemblyName = System.Reflection.Assembly.GetExecutingAssembly().GetName().Name;
            string executingAssemblyPath = System.Reflection.Assembly.GetExecutingAssembly().GetName().CodeBase;
            UriBuilder uri = new UriBuilder(executingAssemblyPath);
            executingAssemblyPath = Uri.UnescapeDataString(uri.Path); // removes file:\\ at beginning of string
            executingAssemblyPath = Path.GetDirectoryName(executingAssemblyPath);
            if (!executingAssemblyPath.EndsWith("\\"))
            {
                executingAssemblyPath += "\\";
            }
            if (executingAssemblyPath.EndsWith("Debug\\"))
            {
                executingAssemblyPath = executingAssemblyPath.Replace("Debug\\", "");
            }
            if (executingAssemblyPath.EndsWith("Release\\"))
            {
                executingAssemblyPath = executingAssemblyPath.Replace("Release\\", "");
            }

            string replacementString = "\\mdo-test\\resources\\data\\MockMdoData.sqlite";
            string adjustedPath = "";

            if (executingAssemblyPath.Contains(@"\mdo\bin\"))
            {
                adjustedPath = executingAssemblyPath.Replace(@"\mdo\bin\", replacementString);
            }
            else if (executingAssemblyPath.Contains(@"\mdo-test\bin\"))
            {
                adjustedPath = executingAssemblyPath.Replace(@"\mdo-test\bin\", replacementString);
            }
            else if (executingAssemblyPath.Contains(@"\mdo-x\bin\"))
            {
                adjustedPath = executingAssemblyPath.Replace(@"\mdo-x\bin\", replacementString);
            }
            else if (executingAssemblyPath.Contains(@"\mdws-test\bin\"))
            {
                adjustedPath = executingAssemblyPath.Replace(@"\mdws\mdws-test\bin\", "\\mdo" + replacementString);
            }

            if (!adjustedPath.Contains(replacementString))
            {
                throw new Exception("Unrecognized calling assembly: " + executingAssemblyName);
            }

            return "Data Source=" + adjustedPath;
        }

        internal void createTableForSite(string dataSourceId)
        {
            if (!dataSourceId.StartsWith(TABLE_NAME_PREFIX))
            {
                dataSourceId = TABLE_NAME_PREFIX + dataSourceId;
            }

            string sqlCreateTable = "CREATE TABLE \"" + dataSourceId + "\" (" +
                "\"ID\" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, " +
                "\"ASSEMBLY_NAME\" TEXT NOT NULL, " +
                "\"DOMAIN_OBJECT_SIZE\" INTEGER NOT NULL, " +
                "\"DOMAIN_OBJECT\" BLOB NOT NULL, " +
                "\"QUERY_STRING\" TEXT NOT NULL, " +
                "\"QUERY_STRING_HASH\" TEXT NOT NULL, " +
                "\"LAST_UPDATED\" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP);";

            // index name must share unique site ID name because sqlite doesn't like duplicate index names across tables
            string sqlCreateIndex = "CREATE UNIQUE INDEX IDX_QUERY_STRING_HASH_" + dataSourceId + " ON " + dataSourceId + " (QUERY_STRING_HASH);";

            SQLiteCommand createTableCmd = new SQLiteCommand(sqlCreateTable, _cxn);
            SQLiteCommand createIndexCmd = new SQLiteCommand(sqlCreateIndex, _cxn);

            try
            {
                openConnection();
                createTableCmd.ExecuteNonQuery();
                createIndexCmd.ExecuteNonQuery();
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                createTableCmd.Dispose();
                createIndexCmd.Dispose();
                closeConnection();
            }
        }

        internal bool hasTable(string dataSourceId)
        {
            if (!dataSourceId.StartsWith(TABLE_NAME_PREFIX))
            {
                dataSourceId = TABLE_NAME_PREFIX + dataSourceId;
            }
            string sql = "SELECT COUNT(*) FROM sqlite_master WHERE NAME='" + dataSourceId + "';";
            SQLiteCommand cmd = new SQLiteCommand(sql, _cxn);

            bool wasOpen = _cxnIsOpen;
            try
            {
                openConnection();

                SQLiteDataReader rdr = cmd.ExecuteReader();

                if (rdr.Read())
                {
                    return rdr.GetInt32(0) == 1;
                }
                else
                {
                    return false;
                }
            }
            catch (Exception)
            {
                return false;
            }
            finally
            {
                cmd.Dispose();
                if (!wasOpen)
                {
                    closeConnection();
                }
            }
        }
    }
}
