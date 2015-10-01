using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using System.Security.Principal;
using Microsoft.Win32.SafeHandles; 
using System.Runtime.InteropServices;
using System.Runtime.ConstrainedExecution;
using System.Security;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.conf;
using gov.va.medora.mdo.dao.ldap;
using gov.va.medora.mdo.dao.mock;


namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwConnection : AbstractConnection, IDisposable
    {
        SqlConnection _cxn;
        User _impersonationUser;

        public CdwConnection(DataSource ds)
            : base(ds)
        {
            this.Account = new CdwAccount(this);
            ImpersonationCredentials credentials = new ImpersonationCredentials();
            
            _impersonationUser = credentials.RunAsUser;
        }

        public CdwConnection(DataSource ds, User runAs)
            : base(ds)
        {
            this.Account = new CdwAccount(this);
            _impersonationUser = runAs;
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {   
            if (_impersonationUser != null)
            {
                using (Impersonator imp = new Impersonator(_impersonationUser))
                {
                    _cxn = new SqlConnection(DataSource.ConnectionString);
                    _cxn.Open();
                    IsConnected = true;
                }
            }
            else
            {
                _cxn = new SqlConnection(DataSource.ConnectionString);
                _cxn.Open();
                IsConnected = true;
            }
        }

        internal void validateConnectionParameters(DataSource dataSource)
        {
            if (DataSource == null)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Datasource is null");
            }
            else if (String.IsNullOrEmpty(dataSource.ConnectionString))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Connection String for CDW Connection is null");
            }
            else if (String.IsNullOrEmpty(dataSource.Provider))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Provider address for Vista Authentication is null");
            }
        }

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            throw new NotImplementedException();
        }

        public SqlDataAdapter createAdapterForStoredProcedure(string procedure)
        {
            SqlDataAdapter adapter = new SqlDataAdapter(procedure, _cxn);
            adapter.SelectCommand.CommandType = CommandType.StoredProcedure;

            return adapter;
        }

        public override string getWelcomeMessage()
        {
            if (IsConnected)
            {
                return "OK";
            }
            throw new mdo.exceptions.MdoException(mdo.exceptions.MdoExceptionCode.USAGE_NO_CONNECTION);
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
            Impersonator imp  = null;
            try
            {
                if (_impersonationUser != null)
                {
                    imp = new Impersonator(_impersonationUser);
                }
                using (SqlConnection newCxn = new SqlConnection(this.DataSource.ConnectionString))
                {
                    newCxn.Open();
                    SqlCommand cmd = new SqlCommand();
                    cmd.Connection = newCxn;
                    cmd.CommandText = request;
                    cmd.CommandTimeout = 60 * 10;
                    SqlDataReader rdr = cmd.ExecuteReader();
                    // the SqlDataReader will be closed at the exit of this using block so we copy everything over to our MockDataReader where it will be cached in a DataTable
                    MockDataReader mock = new MockDataReader();
                    DataTable newTable = new DataTable();
                    newTable.Load(rdr);
                    mock.Table = newTable; // the previous couple lines are broken out so the setter on MockDataReader.Table can properly map the column names - IMPORTANT!!
                    return mock;
                }

            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                if (imp != null)
                {
                    imp.stopImpersonation();
                }
            }
        }

        public virtual object query(SqlDataAdapter adapter, AbstractPermission permission = null)
        {
            Impersonator imp  = null;
            try
            {
                if (_impersonationUser != null)
                {
                    imp = new Impersonator(_impersonationUser);
                }
                using (SqlConnection newCxn = new SqlConnection(this.DataSource.ConnectionString))
                {
                    newCxn.Open();

                    if (adapter.SelectCommand != null)
                    {
                        adapter.SelectCommand.Connection = newCxn;
                        //DataSet results = new DataSet();
                        //adapter.Fill(results);
                        //return results;
                        SqlDataReader rdr = adapter.SelectCommand.ExecuteReader();
                        // the SqlDataReader will be closed at the exit of this using block so we copy everything over to our MockDataReader where it will be cached in a DataTable
                        MockDataReader mock = new MockDataReader();
                        DataTable newTable = new DataTable();
                        newTable.Load(rdr);
                        mock.Table = newTable; // the previous couple lines are broken out so the setter on MockDataReader.Table can properly map the column names - IMPORTANT!!

                        return mock;
                    }
                    else if (adapter.DeleteCommand != null)
                    {
                        adapter.DeleteCommand.Connection = newCxn;
                        return adapter.DeleteCommand.ExecuteNonQuery();
                    }
                    else if (adapter.UpdateCommand != null)
                    {
                        adapter.UpdateCommand.Connection = newCxn;
                        return adapter.UpdateCommand.ExecuteNonQuery();
                    }
                    else if (adapter.InsertCommand != null)
                    {
                        adapter.InsertCommand.Connection = newCxn;
                        return adapter.InsertCommand.ExecuteNonQuery();
                    }

                    throw new ArgumentException("Must supply a SQL command");
                }
            }
            catch (Exception)
            {
                throw;
            }
            finally
            {
                if (imp != null)
                {
                    imp.stopImpersonation();
                }
            }
        }

        //public SqlDataAdapter createAdapterForStoredProcedure(string procedure)
        //{
        //    SqlDataAdapter adapter = new SqlDataAdapter(procedure, _cxn);
        //    adapter.SelectCommand.CommandType = CommandType.StoredProcedure;
            
        //    return adapter;
        //}

        public override string getServerTimeout()
        {
            throw new NotImplementedException();
        }

        public override void disconnect()
        {
            //throw new NotImplementedException();
        }

        public void Dispose()
        {
            return;
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


