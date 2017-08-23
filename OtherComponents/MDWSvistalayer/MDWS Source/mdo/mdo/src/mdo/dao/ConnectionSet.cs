using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Threading;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao
{
    public class ConnectionSet
    {
        Dictionary<string, AbstractConnection> myCxns;
        IndexedHashtable myResults = null;
        IndexedHashtable myExceptions = null;
        string baseSiteId;
        bool fExcludeSite200;

        public ConnectionSet() { }

        public ConnectionSet(AbstractConnection c)
        {
            myCxns = new Dictionary<string, AbstractConnection>();
            myCxns.Add(c.DataSource.SiteId.Id, c);
            baseSiteId = c.DataSource.SiteId.Id;
        }

        public ConnectionSet(List<DataSource> sources, string authenticationMethod)
        {
            myCxns = new Dictionary<string, AbstractConnection>(sources.Count);
            foreach (DataSource src in sources)
            {
                Add(src, authenticationMethod);
            }
        }

        

        // MockConnection for unit tests only.  Has no credentials.
        public ConnectionSet(Dictionary<string, AbstractConnection> someCxns)
        {
            myCxns = someCxns;
        }

        public void Add(AbstractConnection cxn)
        {
            if (fExcludeSite200 && cxn.DataSource.SiteId.Id == "200")
            {
                return;
            }
            if (myCxns == null)
            {
                myCxns = new Dictionary<string, AbstractConnection>(1);
            }
            if (!myCxns.ContainsKey(cxn.DataSource.SiteId.Id))
            {
                myCxns.Add(cxn.DataSource.SiteId.Id, cxn);
                if (myCxns.Count == 1)
                {
                    baseSiteId = cxn.DataSource.SiteId.Id;
                }
            }
        }

        public void Add(DataSource source, string authenticationMethod)
        {
            if (fExcludeSite200 && source.SiteId.Id == "200")
            {
                return;
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(source.Protocol));
            AbstractConnection c = f.getConnection(source);
            c.DataSource = source;
            c.Account.AuthenticationMethod = authenticationMethod;
            if (myCxns == null)
            {
                myCxns = new Dictionary<string, AbstractConnection>(1);
            }
            if (!myCxns.ContainsKey(source.SiteId.Id))
            {
                myCxns.Add(source.SiteId.Id, c);
                if (myCxns.Count == 1)
                {
                    baseSiteId = source.SiteId.Id;
                }
            }
        }

        public void Add(Dictionary<string, DataSource> sources)
        {
            if (myCxns == null)
            {
                myCxns = new Dictionary<string, AbstractConnection>();
            }
            foreach (KeyValuePair<string, DataSource> kvp in sources)
            {
                if (fExcludeSite200 && kvp.Key == "200")
                {
                    continue;
                }
                AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(kvp.Value.Protocol));
                AbstractConnection c = f.getConnection(kvp.Value);
                c.DataSource = kvp.Value;
                if (!myCxns.ContainsKey(kvp.Key))
                {
                    myCxns.Add(kvp.Key, c);
                    if (myCxns.Count == 1)
                    {
                        baseSiteId = kvp.Value.SiteId.Id;
                    }
                }
            }
        }

        public void Add(List<DataSource> sources, string authenticationMethod)
        {
            if (myCxns == null)
            {
                myCxns = new Dictionary<string, AbstractConnection>();
            }
            foreach (DataSource src in sources)
            {
                Add(src, authenticationMethod);
            }
        }

        public bool ExcludeSite200
        {
            get { return fExcludeSite200; }
            set { fExcludeSite200 = value; }
        }

        // Only used by the QueryTemplate classes in this file
        protected void Remove(string sourceId)
        {
            if (myCxns.ContainsKey(sourceId))
            {
                myCxns.Remove(sourceId);
            }
        }

        /// <summary>
        /// Disconnects all connections and removes them from the connection set
        /// </summary>
        public void Clear()
        {
            if (myCxns != null && myCxns.Count > 0)
            {
                foreach (KeyValuePair<string, AbstractConnection> kvp in myCxns)
                {
                    if (kvp.Value != null)
                    {
                        kvp.Value.disconnect();
                    }
                    myCxns.Remove(kvp.Key);
                }
            }
        }

        public Dictionary<string, AbstractConnection> Connections
        {
            get { return myCxns; }
        }

        public bool HasConnection(string siteId)
        {
            return myCxns.ContainsKey(siteId);
        }

        public bool IsConnected(string siteId)
        {
            if (!HasConnection(siteId))
            {
                return false;
            }
            return myCxns[siteId].IsConnected;
        }

        public string Status(string siteId)
        {
            if (!myCxns.ContainsKey(siteId))
            {
                return "no connection";
            }
            if (myCxns[siteId].IsConnected)
            {
                return "open";
            }
            return "closed";
        }

        public bool HasExceptions
        {
            get 
            {
                if (myExceptions == null || myExceptions.Count == 0)
                {
                    return false;
                }
                return true;
            }
        }

        public IndexedHashtable Exceptions
        {
            get { return myExceptions; }
            set { myExceptions = value; }
        }

        public IndexedHashtable Results
        {
            get { return myResults; }
            set { myResults = value; }
        }

        public int Count
        {
            get 
            {
                if (myCxns == null)
                {
                    return 0;
                }
                return myCxns.Count; 
            }
        }

        internal IndexedHashtable buildResults()
        {
            IndexedHashtable result = new IndexedHashtable();
            if (myResults != null)
            {
                for (int i = 0; i < myResults.Count; i++)
                {
                    result.Add(myResults.GetKey(i), myResults.GetValue(i));
                }
            }
            if (myExceptions != null)
            {
                for (int i = 0; i < myExceptions.Count; i++)
                {
                    result.Add(myExceptions.GetKey(i), myExceptions.GetValue(i));
                }
            }
            return result;
        }

        public string ExceptionMessageBlock
        {
            get
            {
                string s = "";
                for (int i = 0; i < Exceptions.Count; i++)
                {
                    s += (string)Exceptions.GetKey(i) + ": " + ((Exception)Exceptions.GetValue(i)).Message + "\r\n";
                }
                return s.Substring(0,s.Length-2);
            }
        }

        public IndexedHashtable connect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            permission.IsPrimary = true;
            QueryTemplate qt = new ConnectQuery(this);
            if (validationDataSource == null)
            {
                validationDataSource = new DataSource();
            }
            qt.QueryMethod("AbstractConnection", "authorizedConnect", new object[] { credentials, permission, validationDataSource });
            IndexedHashtable t = buildResults();

            // KLUGE:  This is here to make the number of connections come out right.
            // Note that the first element in the table will be a connection, but
            // the rest either User or Exception.
            if (HasBaseConnection && myResults.Count < myCxns.Count)
            {
                IndexedHashtable t2 = new IndexedHashtable(myCxns.Count);
                t2.Add(BaseConnection.DataSource.SiteId.Id, BaseConnection);
                for (int i = 0; i < t.Count; i++)
                {
                    t2.Add(t.GetKey(i), t.GetValue(i));
                }
                t = t2;
            }
            return t;
        }

        public IndexedHashtable query(string daoName, string methodName, object[] args)
        {
            if (myCxns == null || myCxns.Count == 0)
            {
                throw new NotConnectedException();
            }
            QueryTemplate qt = new QueryQuery(this);
	        qt.QueryMethod(daoName, methodName, args);
            return buildResults();
        }

        public IndexedHashtable disconnectAll()
        {
            if (myCxns == null || myCxns.Count == 0)
            {
                return null;
            }
            QueryTemplate qt = new DisconnectAllQuery(this);
            qt.QueryMethod("AbstractConnection", "disconnect", new object[] { });
            return buildResults();
        }

        public IndexedHashtable disconnectRemotes()
        {
            if (myCxns == null || myCxns.Count == 0)
            {
                return null;
            }
            QueryTemplate qt = new DisconnectRemotesQuery(this);
            qt.QueryMethod("AbstractConnection", "disconnect", new object[] { });
            return buildResults();
        }

        public IndexedHashtable setLocalPids(string globalPid)
        {
            if (String.IsNullOrEmpty(globalPid))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid ICN or Empty ICN");
            }


            if (!StringUtils.isNumericChar(globalPid[0]))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid ICN or Empty ICN");
            }


            QueryTemplate qt = new QueryQuery(this);
            qt.QueryMethod("IPatientDao", "getLocalPid", new object[] { globalPid });
            return buildResults();
        }

        abstract class QueryTemplate
        {
            ConnectionSet myCset;

            public QueryTemplate(ConnectionSet cset)
            {
                myCset = cset;
            }

            protected ConnectionSet ConnectionSet
            {
                get { return myCset; }
            }

            public abstract bool skipThisConnection(AbstractConnection c);
            public abstract object getDao(string daoName, AbstractConnection c);
            public abstract void handleException(QueryThread t);
            public abstract void handleNonException(QueryThread t);

            public void QueryMethod(string daoName, string methodName, object[] args)
            {
                ArrayList queries = new ArrayList(myCset.Count);
                ArrayList threads = new ArrayList(myCset.Count);

                foreach (KeyValuePair<string, AbstractConnection> kvp in myCset.Connections)
                {
                    AbstractConnection c = kvp.Value;
                    if (skipThisConnection(c))
                    {
                        continue;
                    }
                    object dao = getDao(daoName, c);
                    QueryThread q = new QueryThread(c.DataSource.SiteId.Id, dao, methodName, args);
                    Thread t = new Thread(new ThreadStart(q.execute));
                    queries.Add(q);
                    threads.Add(t);
                    t.Start();
                }
                for (int i = 0; i < threads.Count; i++)
                {
                    ((Thread)threads[i]).Join();
                }
                myCset.Results = new IndexedHashtable();
                myCset.Exceptions = null;
                for (int i = 0; i < queries.Count; i++)
                {
                    QueryThread t = (QueryThread)queries[i];
                    if (t.isExceptionResult())
                    {
                        handleException(t);
                        if (myCset.Exceptions == null)
                        {
                            myCset.Exceptions = new IndexedHashtable();
                        }
                        myCset.Exceptions.Add(t.Id, t.Result);
                    }
                    else
                    {
                        handleNonException(t);
                        myCset.Results.Add(t.Id, t.Result);
                    }
                }
            }
        }

        class ConnectQuery : QueryTemplate
        {
            public ConnectQuery(ConnectionSet cset) : base(cset) { }

	        public override bool skipThisConnection(AbstractConnection c)
	        {
		        return c.IsConnected;
	        }
        	
	        public override object getDao(string daoName, AbstractConnection c)
	        {
		        return c;
	        }
        	
	        public override void handleException(QueryThread t)
	        {
		        ConnectionSet.Remove(t.Id);
	        }
        	
	        public override void handleNonException(QueryThread t) { }
        }

        class QueryQuery : QueryTemplate
        {
            public QueryQuery(ConnectionSet cset) : base(cset) { }

	        public override bool skipThisConnection(AbstractConnection c)
	        {
		        return !c.IsConnected;
	        }
        	
	        public override object getDao(string daoName, AbstractConnection c)
	        {
		        AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(c.DataSource.Protocol));
		        return f.getDaoByName(daoName, c);
	        }
        	
	        public override void handleException(QueryThread t) { }
        	
	        public override void handleNonException(QueryThread t) { }
        }

        abstract class DisconnectQueryTemplate : QueryTemplate
        {
            public DisconnectQueryTemplate(ConnectionSet cset) : base(cset) { }

            public override abstract bool skipThisConnection(AbstractConnection c);

            public override object getDao(string daoName, AbstractConnection c)
            {
                return c;
            }

            public override void handleException(QueryThread t)
            {
                ConnectionSet.Remove(t.Id);
            }

            public override void handleNonException(QueryThread t)
            {
                ConnectionSet.Remove(t.Id);
            }
        }

        class DisconnectAllQuery : DisconnectQueryTemplate
        {
            public DisconnectAllQuery(ConnectionSet cset) : base(cset) { }

            public override bool skipThisConnection(AbstractConnection c)
            {
                return !c.IsConnected;
            }
        }

        class DisconnectRemotesQuery : DisconnectQueryTemplate
        {
            public DisconnectRemotesQuery(ConnectionSet cset) : base(cset) { }

            public override bool skipThisConnection(AbstractConnection c)
            {
                return !c.IsConnected || !c.IsRemote;
            }
        }

        public AbstractConnection getConnection(string siteId)
        {
            if (myCxns.ContainsKey(siteId))
            {
                return myCxns[siteId];
            }
            return null;
        }

        public bool IsAuthorized
        {
            get
            {
                // Has to have at least 1 connection
                if (myCxns == null || myCxns.Count == 0)
                {
                    return false;
                }

                // Has to have authorized base connection
                if (!myCxns.ContainsKey(baseSiteId) || !myCxns[baseSiteId].Account.IsAuthorized)
                {
                    return false;
                }
                return true;
            }
        }

        public string BaseSiteId
        {
            get { return baseSiteId; }
        }

        public bool HasBaseConnection
        {
            get
            {
                if (myCxns == null || myCxns.Count == 0 || String.IsNullOrEmpty(BaseSiteId))
                {
                    return false;
                }
                AbstractConnection c = myCxns[BaseSiteId];
                if (c == null || !c.IsConnected)
                {
                    return false;
                }
                return true;
            }
        }

        public AbstractConnection BaseConnection
        {
            get 
            {
                if (!HasBaseConnection)
                {
                    return null;
                }
                return myCxns[BaseSiteId]; 
            }
        }
    }
}
