using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;
using System.Data.SqlClient;

namespace gov.va.medora.mdo.dao.sql.UserValidation
{
    public class UserValidationConnection : AbstractConnection
    {
        public SqlConnection SqlConnection { get; set; }

        public UserValidationConnection(DataSource source) : base(source) { }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            SqlConnection = new SqlConnection(DataSource.ConnectionString);
            SqlConnection.Open();
        }

        //public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission)
        //{
        //    throw new NotImplementedException();
        //}

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            throw new NotImplementedException();
        }

        public override string getWelcomeMessage()
        {
            throw new NotImplementedException();
        }

        public override bool hasPatch(string patchId)
        {
            throw new NotImplementedException();
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override string getServerTimeout()
        {
            throw new NotImplementedException();
        }

        public override void disconnect()
        {
            if (SqlConnection != null)
            {
                SqlConnection.Close();
            }
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
