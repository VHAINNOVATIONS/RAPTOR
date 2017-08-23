using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class MockConnectStrategy : IConnectStrategy
    {
        MockConnection _cxn;
        DataSource _dataSource;

        public MockConnectStrategy(AbstractConnection cxn)
        {
            _cxn = (MockConnection)cxn;
            _dataSource = cxn.DataSource;
        }

        #region IConnectStrategy Members

        public void connect()
        {
            if (_dataSource == null)
            {
                throw new ArgumentNullException("No data source");
            }
            string hostname = _dataSource.Provider;
            if (String.IsNullOrEmpty(hostname))
            {
                throw new ArgumentNullException("No provider (hostname)");
            }

            // TODO - authenticate against our MockConnection

            _cxn.IsConnected = true;
        }

        #endregion
    }
}
