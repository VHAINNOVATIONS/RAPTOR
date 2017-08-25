using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using System.Xml;
using gov.va.medora.utils;
using gov.va.medora.utils.mock;
using gov.va.medora.mdo.dao.vista;
using System.IO;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.dao.mock;

namespace gov.va.medora.mdo.dao
{
    public class MockConnection : AbstractConnection
    {
        // Need protocols for API level calls that need to use the abstract 
        // factory to create their DAOs.
        public const string VISTA = "VISTA";
        public const string FHIE = "FHIE";
        public const string RPMS = "RPMS";
        private bool verifyRpc = true;
        private bool updateRpc = false;

        AbstractConnection _sqliteCxn;

        public bool VerifyRpc 
        {
            get { return verifyRpc; }

            set { verifyRpc = value; } 
        }

        ISystemFileHandler sysFileHandler;

        public MockConnection(DataSource dataSource) : base(dataSource) 
        {
            _sqliteCxn = new XSqliteConnection(dataSource);
        }

        // This constructor is needed for API level tests.
        public MockConnection(string siteId, string protocol, bool updateRpc = false) : base(null)
        {
            this.DataSource = new DataSource();
            this.DataSource.SiteId = new SiteId(siteId, "Mock");
            this.DataSource.Protocol = protocol;


            _sqliteCxn = new XSqliteConnection(this.DataSource);
            
            this.Account = new MockAccount(this);
            //this.Account.IsAuthenticated = true;
            this.updateRpc = updateRpc;

            AbstractCredentials credentials = new VistaCredentials();
            credentials.AccountName = "AccessCode";
            credentials.AccountPassword = "VerifyCode";
            AbstractPermission permission = new MenuOption(VistaConstants.MDWS_CONTEXT);
            permission.IsPrimary = true;
            this.Account.Permissions.Add(permission.Name, permission);
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get
            {
                if (sysFileHandler == null)
                {
                    sysFileHandler = new VistaSystemFileHandler(this);
                }
                return sysFileHandler;
            }
        }

        public override void connect()
        {
            IsConnected = true;
        }

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            IsConnected = true;
            return null;
        }

        public override void disconnect()
        {
            IsConnected = false;
        }

        public override object query(MdoQuery query, AbstractPermission permission = null)
        {
            // hardcoded datetime request
            if (String.Equals(query.RpcName, "ORWU DT") && String.Equals(((VistaQuery.Parameter)query.Parameters[0]).Value, "NOW"))
            {
                return "3010101.120101";
            }

            if (!IsConnected)
            {
                throw new NotConnectedException();
            }

            string reply = (string)_sqliteCxn.query(query, permission);

            if (reply.Contains("M  ERROR"))
            {
                throw new MdoException(MdoExceptionCode.VISTA_FAULT, reply);
            }
            return reply;
        }

        //internal void updateSqlite(MdoQuery oldRequest, string newRequest, string reply)
        //{
        //    sqlite.SqliteDao sqliteDao = new sqlite.SqliteDao();
        //    string hashedOldQueryString = StringUtils.getMD5Hash(oldRequest.buildMessage());
        //    string hashedNewQueryString = StringUtils.getMD5Hash(newRequest);

        //    try
        //    {
        //        sqliteDao.getObject(this.xmlSource.siteId, hashedOldQueryString); // should throw exception on failure
        //        sqliteDao.updateObject(this.xmlSource.siteId, hashedOldQueryString, newRequest, hashedNewQueryString, reply);
        //    }
        //    catch (Exception)
        //    {
        //        // swallow
        //    }
        //}
        
        //internal void saveToSqlite(MdoQuery request, string reply)
        //{
        //    sqlite.SqliteDao sqliteDao = new sqlite.SqliteDao();
        //    string queryString = request.buildMessage();
        //    string hashedQueryString = StringUtils.getMD5Hash(queryString);

        //    try
        //    {
        //        object savedObj = sqliteDao.getObject(this.xmlSource.siteId, hashedQueryString);
                
        //        if (savedObj as string == reply)
        //        {
        //            return;
        //        }
        //        else
        //        {
        //            sqliteDao.updateObject(this.xmlSource.siteId, hashedQueryString, reply);
        //        }
        //    }
        //    catch (Exception)
        //    {
        //        try
        //        {
        //            if (!sqliteDao.hasTable(this.xmlSource.siteId))
        //            {
        //                sqliteDao.createTableForSite(this.xmlSource.siteId);
        //            }
        //            sqliteDao.saveObject(this.xmlSource.siteId, queryString, reply);
        //        }
        //        catch (Exception exc)
        //        {
        //            throw new Exception("There was a problem saving the XML data to Sqlite: " + exc.Message);
        //        }
        //    }
        //}

        public override object query(string request, AbstractPermission permission = null)
        {
            throw new MethodAccessException("This query method was not expected");
        }

        public override string getWelcomeMessage()
        {
            return "Hullo from MockConnection";
        }

        public override bool hasPatch(string patchId)
        {
            return true;
        }

        public override string getServerTimeout()
        {
            return null;
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

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
