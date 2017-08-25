using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao
{
    public class QueryThreadPool
    {
        public int MaxActiveConnections { get; set; }
        int _maxTries = 5;
        int _cxnRefreshTime;
        int _currentActiveConnections;
        Dictionary<string, IList<AbstractConnection>> _connections = 
            new Dictionary<string, IList<AbstractConnection>>();
        QueryThreadPoolQueue _queue;
        AbstractAccount _account;
        AbstractCredentials _credentials;
        IList<Site> _poolSites;
        SiteTable _siteTable;

        public QueryThreadPool(int connectionPoolSize, int cxnRefreshTime, 
            AbstractAccount account, AbstractCredentials creds, SiteTable siteTable, IList<Site> cxnSites)
        {
            MaxActiveConnections = connectionPoolSize;
            _cxnRefreshTime = cxnRefreshTime;
            _account = account;
            _credentials = creds;
            _siteTable = siteTable;
            _poolSites = cxnSites;


            _queue = new QueryThreadPoolQueue();
            _queue.Changed += new EventHandler(QueueChanged);

            //start();
            //startAsync();
        }

        private void QueueChanged(object sender, EventArgs e)
        {
            QueryThreadPoolEventArgs arg = (QueryThreadPoolEventArgs)e;
            
            if (arg.QueueEventType == QueryThreadPoolEventArgs.QueueChangeEventType.QueryAdded)
            {
                // if connection available then execute
                AbstractConnection cxn = getAvailableConnection(arg.SiteId);
                if (cxn != null)
                {
                    QueryThread qt = _queue.dequeue(arg.SiteId);
                    qt.DequeueTimestamp = DateTime.Now;
                    qt.setConnection(cxn);
                    qt.Changed += new EventHandler(QueryThreadChanged);
                    qt.execute();
                }
            }
        }

        private void QueryThreadChanged(object sender, EventArgs e)
        {
            QueryThread messenger = (QueryThread)sender;
            QueryThreadPoolEventArgs arg = (QueryThreadPoolEventArgs)e;
            string siteId = messenger.Connection.DataSource.SiteId.Id;

            if (arg.ConnectionEventType == QueryThreadPoolEventArgs.ConnectionChangeEventType.ConnectionAvailable)
            {
                messenger.CompleteTimestamp = DateTime.Now;
                messenger.Connection.IsAvailable = true;
                QueryThread qt;
                if ((qt = _queue.dequeue(siteId)) != null) // if there are queued items for this cxn site
                {
                    qt.setConnection(messenger.Connection);
                    qt.DequeueTimestamp = DateTime.Now;
                    qt.Changed += new EventHandler(QueryThreadChanged);
                    qt.execute();
                }
            }
            else if (arg.ConnectionEventType == QueryThreadPoolEventArgs.ConnectionChangeEventType.Disconnected)
            {
                _connections[siteId].Remove(messenger.Connection);
                _currentActiveConnections--;
                // re-allocate connection? some out of process algorithm to re-use?
            }
        }

        /// <summary>
        /// This function shouldn't be utilized in production as thread safety isn't guaranteed
        /// between peek and getAvailableConnection. For controlled testing only
        /// </summary>
        /// <param name="siteId"></param>
        /// <returns></returns>
        internal bool peekAvailableConnection(string siteId)
        {
            lock (_connections)
            {
                if (_connections.ContainsKey(siteId) && _connections[siteId].Count > 0)
                {
                    foreach (AbstractConnection cxn in _connections[siteId])
                    {
                        if (cxn.IsAvailable)
                        {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /// <summary>
        /// This function should locate a connection marked as available, verify the connection
        /// is still active (using a simple getTimestamp query), re-allocate any encountered failed
        /// connections out of process, and return the first connection that is available and connected
        /// </summary>
        /// <param name="siteId"></param>
        /// <returns></returns>
        internal AbstractConnection getAvailableConnection(string siteId)
        {
            lock (_connections)
            {
                if (_connections.ContainsKey(siteId) && _connections[siteId].Count > 0)
                {
                    foreach (AbstractConnection cxn in _connections[siteId])
                    {
                        if (cxn.IsAvailable)
                        {
                            VistaToolsDao dao = new VistaToolsDao(cxn);
                            try
                            {
                                dao.getTimestamp();
                                cxn.IsAvailable = false;
                                return cxn;
                            }
                            catch (Exception) 
                            {
                                _currentActiveConnections--;
                                cxn.IsAvailable = false;
                                reallocateConnection(cxn);
                            }
                        }
                    }
                }
            }
            return null;
        }

        public void reallocateConnection(AbstractConnection cxn)
        {
            Thread t = new Thread(new ParameterizedThreadStart(threadedReallocateConnection));
            t.Start(cxn);
        }

        private void threadedReallocateConnection(object cxn)
        {
            AbstractConnection newCxn = (AbstractConnection)cxn;
            try
            {
                newCxn.disconnect();
                reconnect(newCxn); // this function should call a function which sets the querythread events back in motion
            }
            catch (Exception)
            {
                // what to do... letting heatbeat function clean these up for now
            }
        }

        public void queue(QueryThread qt, string siteId)
        {
            _queue.queue(qt, siteId);
        }

        public void shutdown()
        {
            foreach (string site in _connections.Keys)
            {
                foreach (AbstractConnection cxn in _connections[site])
                {
                    try
                    {
                        cxn.disconnect();
                    }
                    catch (Exception) { }
                    finally
                    {
                        _currentActiveConnections--;
                    }
                }
            }
        }

        internal void heartbeat()
        {
            Dictionary<string, List<AbstractConnection>> cxnsToRemove = new Dictionary<string, List<AbstractConnection>>();

            foreach (string site in _connections.Keys)
            {
                foreach (AbstractConnection cxn in _connections[site])
                {
                    if (DateTime.Now.Subtract(cxn.LastQueryTimestamp).Seconds > _cxnRefreshTime)
                    {
                        try
                        {
                            VistaToolsDao dao = new VistaToolsDao(cxn);
                            dao.getTimestamp();
                        }
                        catch (Exception)
                        {
                            if (!cxnsToRemove.ContainsKey(site))
                            {
                                cxnsToRemove.Add(site, new List<AbstractConnection>());
                            }
                            cxnsToRemove[site].Add(cxn);
                        }
                    }
                }
            }

            if (cxnsToRemove.Count > 0)
            {
                foreach (string site in cxnsToRemove.Keys)
                {
                    foreach (AbstractConnection cxn in cxnsToRemove[site])
                    {
                        _connections[site].Remove(cxn);
                        _currentActiveConnections--;
                    }
                }
            }
        }

        internal void startAsync()
        {
            Thread t = new Thread(new ThreadStart(start));
            t.Start();
        }

        internal void start()
        {
            Site[] sites = _poolSites.ToArray();

            for (int i = 0; _currentActiveConnections < MaxActiveConnections; i++)
            {
                Site currentSite = sites[i%(sites.Length)];
                if (currentSite.Sources == null || currentSite.Sources[0] == null)
                {
                    continue;
                }
                AbstractConnection cxn = connectWithReturn(currentSite, true);
            }
        }

        // should only be used for unit testing since error checking is lacking, only starts one connection per site
        internal void startQuick()
        {
            IList<Thread> startThreads = new List<Thread>();
            foreach (Site s in _poolSites)
            {
                Thread t = new Thread(new ParameterizedThreadStart(connectSite));
                t.Start(s);
                startThreads.Add(t);
            }

            foreach (Thread t in startThreads)
            {
                t.Join();
            }

            // TBD - did have some code that checked connection was created for every site
        }

        /// <summary>
        /// Disconnect old connection and try reconnecting to same site. Set old connection to new connection
        /// if the reconnent is successful
        /// </summary>
        /// <param name="cxn"></param>
        void reconnect(AbstractConnection cxn)
        {
            try
            {
                cxn.disconnect();
                AbstractConnection newCxn = connectWithReturn(_siteTable.getSite(cxn.DataSource.SiteId.Id), false);
                if (newCxn != null)
                {
                    cxn = newCxn;
                }
            }
            catch (Exception)
            {
                // what to do? let heartbeat clean up for now
            }
        }

        void connectSite(object site)
        {
            connectWithReturn((Site)site, true);
        }

        /// <summary>
        /// This function is the heart of the connection pool dictionary. It connects to a site
        /// and attaches the event handlers by executing a standard gettimestamp request
        /// so the connection can immediately begin processing queued query threads
        /// </summary>
        /// <param name="site">Site to which to connect</param>
        /// <param name="addToCxnCollection">Should the site be added to the pools connection collection?</param>
        /// <returns></returns>
        AbstractConnection connectWithReturn(Site site, bool addToCxnCollection)
        {
            //Site[] sites = _poolSites.ToArray();

            Site currentSite = site;
            DataSource src = currentSite.Sources[0];
            AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));
            AbstractConnection c = factory.getConnection(src);
            //c.ConnectionId = new Guid();
            c.Account = _account;
            c.Account = new VistaAccount(c);
            c.Account.AuthenticationMethod = _account.AuthenticationMethod;
            c.Account.Permissions = new Dictionary<string, AbstractPermission>();
            AbstractPermission cprs = new MenuOption("OR CPRS GUI CHART");
            cprs.IsPrimary = true;
            c.Account.Permissions.Add(cprs.Name, cprs);
            // this needs to be up here so we can reference it if an error occurs    
            QueryThread qt = new QueryThread("", new VistaToolsDao(null), "getTimestamp", new object[] { });
            EventHandler eh = new EventHandler(QueryThreadChanged);

            try
            {
                User user = (User)c.authorizedConnect(_credentials, cprs, src);
                c.IsAvailable = true;
                // initiliaze this connection's eventhandler by making a standard timestamp request
                qt.setConnection(c);
                qt.Changed += eh;
                _currentActiveConnections++;
                qt.execute();

                if (!(qt.Result is string))
                {
                    throw new ConnectionException("Connection does not appear to be active");
                }

                if (addToCxnCollection)
                {
                    addConnection(currentSite.Id, c);
                }
                return c;
            }
            catch (Exception)
            {
                qt.Changed -= eh;
                c.IsAvailable = false;
                _currentActiveConnections--;
                try
                {
                    c.disconnect();
                }
                catch (Exception) { }
            }
            return null;            
        }

        private void addConnection(string sitecode, AbstractConnection cxn)
        {
            lock (_connections)
            {
                if (!_connections.ContainsKey(sitecode))
                {
                    _connections.Add(sitecode, new List<AbstractConnection>());
                }
                _connections[sitecode].Add(cxn);
            }
        }
    }
}
