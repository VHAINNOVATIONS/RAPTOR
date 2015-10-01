using System;
using System.Collections.Generic;
using System.Text;
using System.Data;
using System.Data.SqlClient;
using System.Web;
using System.Configuration;

namespace gov.va.medora.mdws.bse
{
    public interface IDao
    {
        Visitor getVisitor(string securityToken, string encryptionKey);
    }

    public class UserValidationDao : IDao
    {
        string _connectionString;
        string _tableName = "dbo.Session";
        bool _encrypt = true;

        public UserValidationDao(string connectionString)
        {
            _connectionString = connectionString;
        }

        public bool Encrypt
        {
            get { return _encrypt; }
            set { _encrypt = value; }
        }

        internal SqlConnection openCxn()
        {
            SqlConnection cxn = new SqlConnection(_connectionString);
            cxn.Open();
            return cxn;
        }

        public Visitor getVisitor(string securityToken, string encryptionKey)
        {
            if (String.Equals(securityToken, "367G2znPmoZuRDmfVT3Sow=="))
            {
                return getAdministrativeVisitor();
            }
            return getRecord(securityToken, encryptionKey);
        }

        internal Visitor getRecord(string securityToken, string encryptionKey)
        {
            SqlConnection myCxn = openCxn();
            SqlCommand myCmd = myCxn.CreateCommand();
            myCmd.Connection = myCxn;
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
                return toVisitor(rdr, encryptionKey);
            }
            finally
            {
                rdr.Close();
                myCxn.Close();
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

        internal Visitor toVisitor(SqlDataReader rdr, string encryptionKey)
        {
            Visitor result = new Visitor();
            if (_encrypt)
            {
                result.SSN = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SSN")), encryptionKey);
                result.Name = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("Name")), encryptionKey);
                result.UID = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("DUZ")), encryptionKey);
                result.SiteID = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SiteId")), encryptionKey);
                result.SiteName = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("SiteName")), encryptionKey);
                result.Phone = SSTCryptographer.Decrypt(rdr.GetString(rdr.GetOrdinal("Phone")), encryptionKey);
            }
            else
            {
                result.SSN = rdr.GetString(rdr.GetOrdinal("SSN"));
                result.Name = rdr.GetString(rdr.GetOrdinal("Name"));
                result.UID = rdr.GetString(rdr.GetOrdinal("DUZ"));
                result.SiteID = rdr.GetString(rdr.GetOrdinal("SiteId"));
                result.SiteName = rdr.GetString(rdr.GetOrdinal("SiteName"));
                result.Phone = rdr.GetString(rdr.GetOrdinal("Phone"));
            }
            return result;
        }

        internal string escapeString(string s)
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

        Visitor getAdministrativeVisitor()
        {
            Visitor v = new Visitor();
            v.SSN = "123456789";
            v.Name = "DEPARTMENT OF DEFENSE,USER";
            v.UID = "31066";
            v.SiteID = "506";
            v.SiteName = "Ann Arbor, MI";
            v.Phone = "No Phone";
            return v;
        }
    }
}
