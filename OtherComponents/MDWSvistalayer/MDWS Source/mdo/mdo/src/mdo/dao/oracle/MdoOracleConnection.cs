using System;
using System.Collections.Generic;
using System.Text;
using Oracle.DataAccess.Client;
//using Oracle.DataAccess.Types;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;
//using System.Data.OracleClient;

namespace gov.va.medora.mdo.dao.oracle
{
    public class MdoOracleConnection : AbstractConnection, IDisposable
    {
        DataSource _dataSource;
        OracleConnection _cxn;
        OracleTransaction _currentTx;

        public MdoOracleConnection(DataSource src) : base(src) 
        {
            validateSource(src);
            _dataSource = src;
        }

        internal OracleTransaction beginTransaction()
        {
            if (_cxn == null)
            {
                connect();
            }
            return _currentTx = _cxn.BeginTransaction();
        }

        internal void commitTransaction()
        {
            if (_currentTx != null)
            {
                _currentTx.Commit();
            }
            _currentTx = null;
        }

        internal void rollbackTransaction()
        {
            if (_currentTx != null)
            {
                _currentTx.Rollback();
            }
            _currentTx = null;
        }

        internal void validateSource(DataSource src)
        {
            if (src == null)
            {
                throw new MdoException(MdoExceptionCode.DATA_SOURCE_NULL);
            }
            if (String.IsNullOrEmpty(src.ConnectionString))
            {
                throw new MdoException(MdoExceptionCode.DATA_SOURCE_MISSING_CXN_STRING);
            }
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { throw new NotImplementedException(); }
        }

        public override void connect()
        {
            if (!IsConnected)
            {
                _cxn = new OracleConnection(_dataSource.ConnectionString);
                _cxn.Open();
                IsConnected = true;
            }
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

        /// <summary>
        /// Execute a query
        /// </summary>
        /// <param name="request">SQL request</param>
        /// <param name="permission"></param>
        /// <returns>OracleDataReader</returns>
        public override object query(string request, AbstractPermission permission = null)
        {
            if (!IsConnected)
            {
                connect();
            }
            OracleCommand cmd = new OracleCommand();
            cmd.Connection = _cxn;
            cmd.CommandText = request;
            if (_currentTx != null)
            {
                cmd.Transaction = _currentTx;
            }
            OracleDataReader rdr = cmd.ExecuteReader();
            return rdr;
        }

        /// <summary>
        /// Execute a SqlQuery function on the connection. SqlQuery should be of type OracleQuery
        /// </summary>
        /// <param name="request">SqlQuery with SqlCommand already built</param>
        /// <param name="functionToExecute">The SqlCommand.function to execute - should take no parameters</param>
        /// <param name="permission"></param>
        /// <returns>Returns the type returned by SqlCommand.function</returns>
        public override object query(SqlQuery request, Delegate functionToExecute, AbstractPermission permission = null)
        {
            if (!IsConnected)
            {
                connect();
            }
            if (!(request is OracleQuery))
            {
                throw new ArgumentException("request must be of type OracleQuery");
            }
            ((OracleQuery)request).Command.Connection = this._cxn;
            if (_currentTx != null)
            {
                ((OracleQuery)request).Command.Transaction = _currentTx;
            }
            return functionToExecute.DynamicInvoke(null);
        }

        public override void disconnect()
        {
            IsConnected = false;

            if (_cxn != null)
            {
                _cxn.Dispose();
            }
        }

        public void Dispose()
        {
            if (this.IsConnected)
            {
                disconnect();
            }
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
