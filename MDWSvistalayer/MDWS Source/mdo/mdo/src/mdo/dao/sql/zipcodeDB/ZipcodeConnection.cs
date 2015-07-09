using System;
using System.Collections.Specialized;
using System.Data;
using System.Data.OleDb;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.sql.zipcodeDB
{
    public class ZipcodeConnection : AbstractConnection
    {
        OleDbConnection cxn;

        public ZipcodeConnection(DataSource dataSource) : base(dataSource) { }
    	
        public override void connect()
        {
            //IsConnected = false;
            //string filepath = HttpContext.Current.Server.MapPath(".") +
            //    MdwsConstants.ZIPCODE_DB_FILE_PATH;
            //cxnString = "Provider=Microsoft.Jet.OLEDB.4.0; User Id=; Password=; Data Source=" + filepath;
            //cxn = new OleDbConnection(cxnString);
            //cxn.Open();
            //IsConnected = true;
        }

        public override void disconnect() 
        {
            cxn.Close();
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override object query(string statement, AbstractPermission permission = null)
        {
            OleDbCommand cmd = cxn.CreateCommand();
            cmd.CommandText = statement;
            return cmd.ExecuteReader();
        }

        public override string getWelcomeMessage()
        {
            return null;
        }

        //public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission)
        //{
        //    return null;
        //}

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            return null;
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { return null; }
        }

        public override string getServerTimeout()
        {
            return null;
        }

        public override bool hasPatch(string patchId)
        {
            throw new Exception("The method or operation is not implemented.");
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override System.Collections.Generic.Dictionary<string, object> getState()
        {
            throw new NotImplementedException();
        }

        public override void setState(System.Collections.Generic.Dictionary<string, object> session)
        {
            throw new NotImplementedException();
        }

        public override bool isAlive()
        {
            throw new NotImplementedException();
        }
    }
}
