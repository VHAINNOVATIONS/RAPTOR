using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdo.domain.pool;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws
{
    public class SessionMgr
    {
        #region Member Variables

        Dictionary<string, MySession> _sessions;


        #endregion
        
        #region Singleton Code and ConnectionPools Starter
        static SessionMgr _mgr;

        private SessionMgr() { }

        public static SessionMgr getInstance()
        {
            if (_mgr == null)
            {
                MySession session = new MySession(); // need this so we can read config file for connection pool
                if (Convert.ToBoolean(session.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
                {
                    startConnectionPool();
                }

                _mgr = new SessionMgr();
                _mgr._sessions = new Dictionary<string, MySession>();
            }
            return _mgr;
        }

        private static void startConnectionPool()
        {
            MySession temp = new MySession();
            AbstractPoolSource defaultSrc = new ConnectionPoolSource()
            {
                CxnSource = new mdo.DataSource() { Protocol = "PVISTA" },
                Credentials = new VistaCredentials(),
                LoadStrategy = (LoadingStrategy)Enum.Parse(typeof(LoadingStrategy), 
                    temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_LOAD_STRATEGY]),
                MaxPoolSize = Convert.ToInt32(temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_MAX_CXNS]),
                MinPoolSize = Convert.ToInt32(temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_MIN_CXNS]),
                PoolExpansionSize = Convert.ToInt32(temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_EXPAND_SIZE]),
                WaitTime = TimeSpan.Parse(temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_WAIT_TIME]),
                Timeout = TimeSpan.Parse(temp.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_CXN_TIMEOUT])
            };

            IList<AbstractPoolSource> sources = new List<AbstractPoolSource>();
            //AbstractPoolSource poolsSource = new ConnectionPoolsSource();
            //((ConnectionPoolsSource)poolsSource).CxnSources = new Dictionary<string, ConnectionPoolSource>();
            AbstractPoolSourceFactory factory = new ConnectionPoolSourceFactory(defaultSrc); // instantiate the pool source factory
            AbstractPoolSource result = (ConnectionPoolsSource)factory.getPoolSources(temp.SiteTable);
            AbstractResourcePool pools = AbstractResourcePoolFactory.getResourcePool(result); // this starts the pool
        }
        #endregion


        // TODO - if clients simply abandon their session, the session dictoinary could grow uncontrollably. should be loop through (separate thread is ok)
        // the collection on every access to check for expired connections? maybe have some background process?
        public MySession getSession(string token)
        {
            if (String.IsNullOrEmpty(token))
            {
                return new MySession() { LastUsed = DateTime.Now };
            }
            if (!_sessions.ContainsKey(token))
            {
                return new MySession();
            }
            MySession clientSession = _sessions[token];
            // check timeout
            if (clientSession.hasExpired())
            {
                _sessions.Remove(token); // remove session if timed out
                return new MySession(); // give client a new bare MySession
            }

            clientSession.LastUsed = DateTime.Now;
            return clientSession;
        }

        internal void addSession(MySession newSession)
        {
            if (newSession == null || String.IsNullOrEmpty(newSession.Token))
            {
                throw new ArgumentNullException("Invalid MySession - Unable to add!");
            }
            _sessions.Add(newSession.Token, newSession);
        }

        internal void removeSession(MySession session)
        {
            if (session == null || String.IsNullOrEmpty(session.Token))
            {
                throw new ArgumentNullException("Invalid MySession - Unable to add!");
            }
            if (_sessions.ContainsKey(session.Token))
            {
                _sessions.Remove(session.Token);
            }
            session.Token = null;
        }

        // TODO - this function isn't guaranteed to produce unique tokens due to it's use of the Random class. should we use a GUID? maybe a hash of a GUID or something? ok for now but need to re-visit
        internal string getNewToken()
        {
            return gov.va.medora.utils.StringUtils.getNCharRandom(32);
        }

        internal void setConnection(MySession session)
        {
            if (session.ConnectionSet == null || session.ConnectionSet.BaseConnection == null)
            {
                throw new ArgumentNullException("Invalid session - need to connect?");
            }
            AbstractConnection cxn = (AbstractConnection)ConnectionPools.getInstance().checkOut(session.ConnectionSet.BaseSiteId);
            // set the state for this connection only if the state has been instantiated elsewhere
            if (null != session.Sessions && null != session.Sessions.States && 
                session.Sessions.States.ContainsKey(session.ConnectionSet.BaseSiteId) && 
                null != session.Sessions.States[session.ConnectionSet.BaseSiteId].State)
            {
                cxn.setState(session.Sessions.States[session.ConnectionSet.BaseSiteId].State as Dictionary<string, object>);
            }
        }

        internal void setConnection(MySession session, string sitecode)
        {
            AbstractConnection cxn = (AbstractConnection)ConnectionPools.getInstance().checkOut(sitecode);
            if (session.Sessions == null)
            {
                Dictionary<string, AbstractState> state0 = new Dictionary<string, AbstractState>();
                state0.Add(sitecode, new VistaState());
                session.Sessions = new VistaStates(state0);
            }
            if (null != session.Sessions && null != session.Sessions.States && session.Sessions.States.ContainsKey(sitecode) && null != session.Sessions.States[sitecode].State)
            {
                cxn.setState(session.Sessions.States[sitecode].State as Dictionary<string, object>);
            }
            session.ConnectionSet.Add(cxn); // only adds if not present
        }

        internal void setConnections(MySession session)
        {
            //Dictionary<string, AbstractConnection> cxns = new Dictionary<string, AbstractConnection>();
            foreach (string site in session.ConnectionSet.Connections.Keys)
            {
                AbstractConnection cxn = (AbstractConnection)ConnectionPools.getInstance().checkOut(site);
                cxn.setState(session.Sessions.States[site].State as Dictionary<string, object>);
                //cxns.Add(site, cxn);
                session.ConnectionSet.Add(cxn);
            }
        }

        internal void setConnections(MySession session, IList<string> sites)
        {
            //Dictionary<string, AbstractConnection> cxns = new Dictionary<string, AbstractConnection>();
            foreach (string site in sites)
            {
                AbstractConnection cxn = (AbstractConnection)ConnectionPools.getInstance().checkOut(site);
                cxn.setState(session.Sessions.States[site].State as Dictionary<string, object>);
                //cxns.Add(site, cxn);
                session.ConnectionSet.Add(cxn);
            }
        }

        /// <summary>
        /// Get the state on each connection (XWB SERIALIZE), set the MySession.Sessions state info, return the connections to the pool
        /// </summary>
        /// <param name="session"></param>
        internal void returnConnections(MySession session)
        {
            if (session.ConnectionSet == null || session.ConnectionSet.Count == 0)
            {
                return;
            }
            foreach (string key in session.ConnectionSet.Connections.Keys)
            {
                AbstractConnection cxn = session.ConnectionSet.getConnection(key);
                if (cxn == null) // if no connection was checked out for this key
                {
                    continue;
                }
                session.Sessions.setState(key, new VistaState(cxn.getState()));
                ConnectionPools.getInstance().checkIn(cxn);
            }
        }
    }
}