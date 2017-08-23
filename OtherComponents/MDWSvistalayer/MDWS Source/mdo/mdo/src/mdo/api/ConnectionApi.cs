using System;
using System.Collections.Specialized;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.api
{
    public class ConnectionApi
    {
        DataSource src;
        Connection cxn;
        MultiSourceQuery msq;
        ConnectionManager mgr;

        public ConnectionApi() { }

        public ConnectionApi(DataSource src)
        {
            this.src = src;
            setConnection();
        }

        public ConnectionApi(DataSource[] sources)
        {
            MultiSourceQuery = new MultiSourceQuery(sources);
        }

        public Connection MdoConnection
        {
            get { return cxn; }
        }

        private void setConnection()
        {
            DaoFactory df = DaoFactory.getDaoFactory(DaoFactory.getConstant(src.Protocol));
            this.cxn = df.getConnection(src);
        }

        public MultiSourceQuery MultiSourceQuery
        {
            get { return msq; }
            set { msq = value; }
        }

        public ConnectionManager CxnMgr
        {
            get { return mgr; }
            set { mgr = value; }
        }

        public void connect()
        {
            cxn.connect();
        }

        public void disconnect()
        {
            cxn.disconnect();
        }

        public bool IsConnected
        {
            get { return cxn.IsConnected; }
            set { cxn.IsConnected = value; }
        }

        public String WelcomeMessage
        {
            get { return cxn.getWelcomeMessage(); }
        }

        public StringDictionary getPatientTypes(Connection cxn)
        {
            return cxn.getPatientTypes();
        }

        public StringDictionary getPatientTypes()
        {
            return cxn.getPatientTypes();
        }

        public DateTime getTimestamp()
        {
            return cxn.getTimestamp();
        }

        public IndexedHashtable getTimestamp(MultiSourceQuery msq)
        {
            return msq.execute("Connection", "getTimestamp", new object[] { });
        }

    }
}
