using System;
using System.Collections.Generic;
using System.Text;
using System.Data.SqlClient;
using System.Configuration;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.sql.npt
{
    public class NptConnection : AbstractConnection, IDisposable
    {
        string _cxnString;
        SqlConnection _myCxn;
        SqlParameterCollection _params;

        /// <summary>
        /// Setter and Getter for SQL Parameterized queries. The query(string) function will use these, if available.
        /// </summary>
        public SqlParameterCollection SqlParameters
        {
            get { return _params; }
            set { _params = value; }
        }

        public NptConnection(DataSource src) : base(src)
        {
            if (src != null && !String.IsNullOrEmpty(src.ConnectionString))
            {
                _cxnString = src.ConnectionString;
            }
            else
            {
                _cxnString = "Server=10.168.98.194;" +
                               "Database=NPT;" +
                               "UID=NPT_DBREADER;" +
                               "PWD=!medora506!";
            }
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            _myCxn = new SqlConnection(_cxnString);
            _myCxn.Open();
            IsConnected = true;
        }

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

        public override string getServerTimeout()
        {
            return null;
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            SqlCommand cmd = new SqlCommand();
            cmd.Connection = _myCxn;
            cmd.CommandText = request;
            if (_params != null && _params.Count > 0)
            {
                int count = _params.Count;
                for (int i = 0; i < count; i++)
                {
                    SqlParameter temp = _params[0];
                    _params.RemoveAt(0);
                    cmd.Parameters.Add(temp);
                }
            }
            SqlDataReader rdr = cmd.ExecuteReader();
            return rdr;
        }

        public virtual object query(SqlDataAdapter adapter, AbstractPermission permission = null)
        {
            if (!IsConnected)
            {
                connect();
            }
            if (adapter.SelectCommand != null)
            {
                adapter.SelectCommand.Connection = _myCxn;
                return adapter.SelectCommand.ExecuteReader();
            }
            else if (adapter.DeleteCommand != null)
            {
                adapter.DeleteCommand.Connection = _myCxn;
                return adapter.DeleteCommand.ExecuteNonQuery();
            }
            else if (adapter.UpdateCommand != null)
            {
                adapter.UpdateCommand.Connection = _myCxn;
                return adapter.UpdateCommand.ExecuteNonQuery();
            }
            else if (adapter.InsertCommand != null)
            {
                adapter.InsertCommand.Connection = _myCxn;
                return adapter.InsertCommand.ExecuteNonQuery();
            }
            throw new ArgumentException("Must supply a SQL command");
        }

        public override void disconnect()
        {
            IsConnected = false;
            if (_myCxn != null)
            {
                _myCxn.Close();
                _myCxn.Dispose();
            }
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public void Dispose()
        {
            disconnect();
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
