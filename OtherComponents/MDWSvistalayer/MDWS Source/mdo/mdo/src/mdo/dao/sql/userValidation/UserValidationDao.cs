using System;
using System.Collections.Generic;
using System.Text;
using System.Data;
using System.Data.SqlClient;
using System.Configuration;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo.dao.sql.UserValidation
{
    public class UserValidationDao
    {
        string _tableName = "dbo.Session"; // this is currently static - probably should stay that way
        //string _encryptionKey = VistaConstants.ENCRYPTION_KEY;
        bool _encrypt = true;
        AbstractConnection _cxn;

        public UserValidationDao(UserValidationConnection cxn)
        {
            _cxn = cxn;
        }

        public bool Encrypt
        {
            get { return _encrypt; }
            set { _encrypt = value; }
        }

        //internal SqlConnection openCxn()
        //{
        //    //string cxnString = "server=" + _server +
        //    //    ";uid=" + _uid + ";pwd=" + _pwd + ";database=" + _dbName;
        //    SqlConnection cxn = new SqlConnection(_connectionString);
        //    cxn.Open();
        //    return cxn;
        //}

        public void addRecord(AbstractCredentials creds, string encryptionKey)
        {
            string sql = buildAddRecordStatement(creds, encryptionKey);

            _cxn.connect();
            SqlCommand myCmd = new SqlCommand(sql, ((UserValidationConnection)_cxn).SqlConnection);
            int rows = -1;
            try
            {
                rows = myCmd.ExecuteNonQuery();
            }
            catch (SqlException e)
            {
                if (e.Number != 2627) // duplicate key exception code. i.e. primary key violations are ok
                {
                    throw;
                }
            }
            finally
            {
                _cxn.disconnect();
            }
        }

        internal string buildAddRecordStatement(AbstractCredentials creds, string encryptionKey)
        {
            string phoneNum = "No phone";
            if (!String.IsNullOrEmpty(creds.SubjectPhone))
            {
                phoneNum = creds.SubjectPhone;
            }
            string result = "INSERT INTO " + _tableName +
                " (SessionID,SSN,Name,DUZ,SiteId,SiteName,Phone) VALUES (";
            if (_encrypt)
            {
                result += "'" + escapeString(SSTCryptographer.Encrypt(creds.AuthenticationToken, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(creds.FederatedUid, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(creds.SubjectName, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(creds.LocalUid, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(creds.AuthenticationSource.SiteId.Id, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(creds.AuthenticationSource.SiteId.Name, encryptionKey)) + "'," +
                            "'" + escapeString(SSTCryptographer.Encrypt(phoneNum, encryptionKey)); 
            }
            else
            {
                result += "'" + escapeString(creds.AuthenticationToken) + "'," +
                            "'" + escapeString(creds.FederatedUid) + "'," +
                            "'" + escapeString(creds.SubjectName) + "'," +
                            "'" + escapeString(creds.LocalUid) + "'," +
                            "'" + escapeString(creds.AuthenticationSource.SiteId.Id) + "'," +
                            "'" + escapeString(creds.AuthenticationSource.SiteId.Name) + "'," +
                            "'" + escapeString(phoneNum);
            }
            result += "');";
            return result;
        }

        public User getRecord(string securityToken, string encryptionKey)
        {
            _cxn.connect();
            SqlCommand myCmd = ((UserValidationConnection)_cxn).SqlConnection.CreateCommand();
            myCmd.Connection = ((UserValidationConnection)_cxn).SqlConnection;
            SqlDataReader rdr = null;

            try
            {
                Encrypt = false;
                myCmd.CommandText = buildGetRecordStatement(securityToken, encryptionKey);
                rdr = myCmd.ExecuteReader();
                if (!rdr.HasRows)
                {
                    return null;
                }
                rdr.Read();
                Encrypt = true;
                return toUser(rdr, encryptionKey);
            }
            finally
            {
                rdr.Close();
                _cxn.disconnect();
            }
        }

        internal string buildGetRecordStatement(string securityToken, string encryptionKey)
        {
            if (_encrypt)
            {
                return "SELECT * FROM " + _tableName + " WHERE SessionId ='" +
                    escapeString(SSTCryptographer.Encrypt(securityToken, encryptionKey)) + "';";
            }
            else
            {
                return "SELECT * FROM " + _tableName + " WHERE SessionId ='" +
                    escapeString(securityToken) + "';";
            }
        }

        internal User toUser(SqlDataReader rdr, string encryptionKey)
        {
            User result = new User();
            if (_encrypt)
            {
                result.SSN = new SocSecNum(SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SSN")), encryptionKey));
                result.Name = new PersonName(SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("Name")), encryptionKey));
                result.Uid = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("DUZ")), encryptionKey);
                string siteId = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SiteId")), encryptionKey);
                string siteName = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SiteName")), encryptionKey);
                result.LogonSiteId = new SiteId(siteId, siteName);
                result.Phone = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("Phone")), encryptionKey);
            }
            else
            {
                //result.SSN = new SocSecNum(rdr.GetString(rdr.GetOrdinal("SSN")));
                string s = rdr.GetString(rdr.GetOrdinal("Name"));
                result.Name = new PersonName(rdr.GetString(rdr.GetOrdinal("Name")));
                result.Uid = rdr.GetString(rdr.GetOrdinal("DUZ"));
                string siteId = rdr.GetString(rdr.GetOrdinal("SiteId"));
                string siteName = rdr.GetString(rdr.GetOrdinal("SiteName"));
                result.LogonSiteId = new SiteId(siteId, siteName);
                result.Phone = rdr.GetString(rdr.GetOrdinal("Phone"));
            }
            return result;
        }

        public void deleteRecord(string securityToken, string encryptionKey)
        {
            _cxn.connect();
            SqlCommand myCmd = ((UserValidationConnection)_cxn).SqlConnection.CreateCommand();
            myCmd.Connection = ((UserValidationConnection)_cxn).SqlConnection;

            int rows = -1;
            try
            {
                myCmd.CommandText = buildDeleteRecordStatement(securityToken, encryptionKey);
                rows = myCmd.ExecuteNonQuery();
            }
            finally
            {
                _cxn.disconnect();
            }
            if (rows != 1)
            {
                throw new UnexpectedDataException(myCmd.CommandText + " returned wrong number of records: " + rows);
            }
        }

        internal string buildDeleteRecordStatement(string securityToken, string encryptionKey)
        {
            if (_encrypt)
            {
                return "DELETE FROM " + _tableName + " WHERE SessionId ='" +
                    escapeString(SSTCryptographer.Encrypt(securityToken, encryptionKey)) + "';";
            }
            else
            {
                return "DELETE FROM " + _tableName + " WHERE SessionId ='" +
                    escapeString(securityToken) + "';";
            }
        }

        public static string escapeString(string s)
        {
            string result = "";
            if (StringUtils.isEmpty(s))
            {
                return result;
            }
            for (int i = 0; i < s.Length; i++)
            {
                if (s[i] != '\'')
                {
                    result += s[i];
                }
                else
                {
                    result += "'" + s[i];
                }
            }
            return result;
        }

        public string getPassword(string clientName)
        {
            _cxn.connect();
            SqlCommand myCmd = ((UserValidationConnection)_cxn).SqlConnection.CreateCommand();
            myCmd.Connection = ((UserValidationConnection)_cxn).SqlConnection;
            SqlDataReader rdr = null;

            try
            {
                myCmd.CommandText = "SELECT Password FROM dbo.Clients WHERE ClientName ='" +
                    escapeString(clientName) + "';";
                rdr = myCmd.ExecuteReader();
                if (!rdr.HasRows)
                {
                    return null;
                }
                rdr.Read();
                return rdr.GetString(0);
            }
            finally
            {
                rdr.Close();
                _cxn.disconnect();
            }
        }

        public string getPhrase(string password)
        {
            _cxn.connect();
            SqlCommand myCmd = ((UserValidationConnection)_cxn).SqlConnection.CreateCommand();
            myCmd.Connection = ((UserValidationConnection)_cxn).SqlConnection;
            SqlDataReader rdr = null;

            try
            {
                myCmd.CommandText = "SELECT Phrase FROM dbo.Clients WHERE Password ='" +
                    escapeString(password) + "';";
                rdr = myCmd.ExecuteReader();
                if (!rdr.HasRows)
                {
                    return null;
                }
                rdr.Read();
                return rdr.GetString(0);
            }
            finally
            {
                rdr.Close();
                _cxn.disconnect();
            }
        }
    }
}
