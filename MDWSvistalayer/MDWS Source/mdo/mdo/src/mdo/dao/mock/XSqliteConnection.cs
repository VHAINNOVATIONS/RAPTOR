using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SQLite;
using gov.va.medora.utils;
using System.IO;

namespace gov.va.medora.mdo.dao.mock
{
    public class XSqliteConnection : AbstractConnection, IDisposable
    {
        static string _sqliteDbPath;

        public bool SaveResults { get; set; }
        public bool UpdateResults { get; set; }

        SQLiteConnection _cxn;
        DataSource _src;

        public XSqliteConnection(DataSource src)
            : base(src)
        {
            if (src == null || String.IsNullOrEmpty(src.ConnectionString))
            {
                DataSource newSrc = new DataSource() { ConnectionString = new sqlite.SqliteDao().getResourcesPath() };
                if (src != null)
                {
                    newSrc.IsTestSource = src.IsTestSource;
                    newSrc.Modality = src.Modality;
                    newSrc.Password = src.Password;
                    newSrc.Port = src.Port;
                    newSrc.Protocol = src.Protocol;
                    newSrc.SiteId = src.SiteId;
                }
                src = newSrc;
            }
            if (String.IsNullOrEmpty(_sqliteDbPath))
            {
                _sqliteDbPath = src.ConnectionString;
            }
            _src = src;
            _cxn = new SQLiteConnection(src.ConnectionString);
        }

        public override string getWelcomeMessage()
        {
            return "SQlite says hello";
        }

        public override void connect()
        {
            if (!IsConnected)
            {
                _cxn.Open();
                IsConnected = true;
            }
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            return query(request.buildMessage(), permission);
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            SQLiteCommand cmd = new SQLiteCommand("SELECT ASSEMBLY_NAME, DOMAIN_OBJECT_SIZE, DOMAIN_OBJECT, QUERY_STRING FROM SITE_" + _src.SiteId.Id +
                " WHERE QUERY_STRING_HASH = '" + StringUtils.getMD5Hash(request) + "';", _cxn);

            connect();
            SQLiteDataReader rdr = cmd.ExecuteReader();

            if (rdr.Read())
            {
                string fullAssemblyName = rdr.GetString(0); // gives us the object type
                Int32 objectSize = rdr.GetInt32(1); // gives us the object size in bytes - should have saved this info to database when mocking data
                byte[] buffer = new byte[objectSize];
                rdr.GetBytes(2, 0, buffer, 0, objectSize);

                return deserializeObject(buffer);
            }
            else
            {
                return "";
                //throw new exceptions.MdoException("Record not found in mock site " + _src.SiteId.Id);
            }
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            string requestString = "";
            if (request is OracleQuery)
            {
                requestString = ((OracleQuery)request).Command.CommandText + buildParametersString(((OracleQuery)request).Command.Parameters);
            }
            else
            {
                throw new ArgumentException("Only supporting OracleQuery request types. Need to implement others...");
            }

            SQLiteCommand cmd = new SQLiteCommand("SELECT ASSEMBLY_NAME, DOMAIN_OBJECT_SIZE, DOMAIN_OBJECT, QUERY_STRING FROM SITE_" + _src.SiteId.Id +
                " WHERE QUERY_STRING_HASH = '" + StringUtils.getMD5Hash(requestString) + "';", _cxn);

            connect();
            SQLiteDataReader rdr = cmd.ExecuteReader();

            if (rdr.Read())
            {
                string fullAssemblyName = rdr.GetString(0); // gives us the object type
                Int32 objectSize = rdr.GetInt32(1); // gives us the object size in bytes - should have saved this info to database when mocking data
                byte[] buffer = new byte[objectSize];
                rdr.GetBytes(2, 0, buffer, 0, objectSize);

                return deserializeObject(buffer);
            }
            else
            {
                throw new exceptions.MdoException("Record not found in mock site " + _src.SiteId.Id);
            }
        }

        public override void disconnect()
        {
            try
            {
                if (IsConnected)
                {
                    _cxn.Close();
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                IsConnected = false;
            }
        }

        public void Dispose()
        {
            if (IsConnected)
            {
                disconnect();
            }
        }

        internal string buildParametersString(Oracle.DataAccess.Client.OracleParameterCollection oracleParams)
        {
            StringBuilder sb = new StringBuilder();

            foreach (Oracle.DataAccess.Client.OracleParameter param in oracleParams)
            {
                if (param.DbType == System.Data.DbType.Binary || param.DbType == System.Data.DbType.Object)
                {
                    sb.Append("BINARY DATA");
                    continue;
                }
                sb.Append(param.Value.ToString());
            }
            return sb.ToString();
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


        #region Not Implemented Members
        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }
        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            throw new NotImplementedException();
        }


        public override bool hasPatch(string patchId)
        {
            throw new NotImplementedException();
        }

        public override string getServerTimeout()
        {
            throw new NotImplementedException();
        }

        #endregion


        public override Dictionary<string, object> getState()
        {
            throw new NotImplementedException();
        }

        public override void setState(Dictionary<string, object> session)
        {
            throw new NotImplementedException();
        }

        public override bool isAlive()
        {
            throw new NotImplementedException();
        }
    }
}
