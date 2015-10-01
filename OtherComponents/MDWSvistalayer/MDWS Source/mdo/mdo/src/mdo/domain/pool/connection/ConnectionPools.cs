using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using System.Configuration;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionPools : AbstractResourcePool
    {
        static byte SHUTDOWN_FLAG = 0;
        bool _starting = false;
        static readonly object _locker = new object();
        static readonly object _instantiationLocker = new object();

        internal Dictionary<string, ConnectionPool> _pools;

        public static ConnectionPools getInstance()
        {
            if (_thePool == null)
            {
                lock (_instantiationLocker)
                {
                    if (_thePool == null)
                    {
                        _thePool = new ConnectionPools();
                    }
                }
            }
            return _thePool;
        }
        static ConnectionPools _thePool;
        private ConnectionPools() { }


        internal void run()
        {
            // never let two processes call run!!!
            lock (_locker)
            {
                ConnectionPoolsSource poolSource = (ConnectionPoolsSource)this.PoolSource; // just cast this once
                // startup
                _starting = true;
                startUp(poolSource);
                _starting = false;

                while (!Convert.ToBoolean(SHUTDOWN_FLAG))
                {
                    System.Threading.Thread.Sleep(1000);
                    // babysit pools
                    foreach (string siteId in poolSource.CxnSources.Keys)
                    {
                        if (_pools[siteId] == null) // if pool hasn't been instantiated
                        {
                            if (this.PoolSource.LoadStrategy == LoadingStrategy.Lazy) // and loading lazily then just continue
                            {
                                continue;
                            }
                            else if (this.PoolSource.LoadStrategy == LoadingStrategy.Eager) // and loading eagerly then instantiate pool
                            {
                                _pools[siteId] = (ConnectionPool)ConnectionPoolFactory.getResourcePool(poolSource.CxnSources[siteId]);
                            }
                        }
                        // need to either modify isAlive to recognize idle pool or leave commented out/delete because this is re-starting the pool evert time.
                        //else if (!_pools[siteId].IsAlive && !Convert.ToBoolean(SHUTDOWN_FLAG)) // if the pool is not started for some reason and we're not shutting down
                        //{
                        //    //_pools[siteId].shutdown();
                        //    //startPool(siteId, poolSource.CxnSources[siteId]);
                        //    //_pools[siteId] = (ConnectionPool)ConnectionPoolFactory.getResourcePool(poolSource.CxnSources[siteId]);
                        //}
                    }
                }
            }
        }

        /// <summary>
        /// Start the connection pool. This method is rather slow when run in production (130+ sites). A connection pool is started for
        /// each of the sources. A loop is used to start each pool. Each new pool start waits for the previous pool to come to a reasonable
        /// state before starting. If the previous pool hasn't finished starting after 60 seconds, the current pool will go ahead 
        /// and start anyways. 
        /// </summary>
        /// <param name="source">The source for all the connection pool configuration information</param>
        void startUp(ConnectionPoolsSource source)
        {
            // first things first - initialize the pool collection based off the connection sources!
            _pools = new Dictionary<string, ConnectionPool>();
            foreach (string siteId in source.CxnSources.Keys)
            {
                _pools.Add(siteId, null);
            }

            if (source.LoadStrategy == LoadingStrategy.Lazy)
            {
                return; // lazy loading? we're all done with startup!
            }

            // the rest of this code starts the pool for each of the connection sources
            string[] allKeys = new string[source.CxnSources.Count];
            source.CxnSources.Keys.CopyTo(allKeys, 0);
            IList<ConnectionPool> allPools = new List<ConnectionPool>(allKeys.Length);

            for (int i = 0; i < allKeys.Length; i++)
            {
                DateTime lastPoolStart = DateTime.Now;

                // starting 130+ connection pool threads takes a lot of system resources - we should try and let the 
                // previous pool come up or at least give it a reasonable time to start before moving to the next connection pool
                if (i > 0)
                {
                    while (lastPoolStart.Subtract(DateTime.Now).TotalSeconds < 60 &&
                        allPools[i - 1].TotalResources < allPools[i - 1].PoolSource.MinPoolSize)
                    {
                        System.Threading.Thread.Sleep(500);
                    }
                }

                // go ahead and start the pool now
                startPool(allKeys[i], source.CxnSources[allKeys[i]]);
                allPools.Add(_pools[allKeys[i]]);
                //ConnectionPool cxnPool = (ConnectionPool)ConnectionPoolFactory.getResourcePool(source.CxnSources[allKeys[i]]);
                //allPools.Add(cxnPool);
                //_pools[allKeys[i]] = cxnPool;
            }
        }

        /// <summary>
        /// Check a connection in to the pool
        /// </summary>
        /// <param name="objToReturn">The AbstractConnection to check in</param>
        /// <returns>null</returns>
        public override object checkIn(AbstractResource objToReturn)
        {
            if (objToReturn == null || !(objToReturn is AbstractConnection))
            {
                throw new ArgumentException("Invalid object for checkin");
            }
            AbstractConnection theCxn = (AbstractConnection)objToReturn;
            if (theCxn.DataSource == null || theCxn.DataSource.SiteId == null || String.IsNullOrEmpty(theCxn.DataSource.SiteId.Id))
            {
                throw new ArgumentException("The connection source is incomplete");
            }
            if (!_pools.ContainsKey(theCxn.DataSource.SiteId.Id))
            {
                throw new ArgumentException("No pool found for that connection");
            }
            _pools[theCxn.DataSource.SiteId.Id].checkIn(theCxn);
            return null;
        }

        /// <summary>
        /// Checkout a connection
        /// </summary>
        /// <param name="obj">The connection identifier (usually the site ID)</param>
        /// <returns>AbstractConnection</returns>
        public override AbstractResource checkOut(object obj)
        {
            while (_starting) // block while startup is occurring
            {
                System.Threading.Thread.Sleep(10);
            }
            if (!(obj is String))
            {
                throw new ArgumentException("Must supply the ID of the connection pool to check out a connection");
            }
            string site = (String)obj;
            // first make sure we have a dictionary key/queue for this site - if lazy loading then create a new pool for site - else exception
            ConnectionPoolsSource source = (ConnectionPoolsSource)this.PoolSource;
            if (!_pools.ContainsKey(site))
            {
                if (!source.CxnSources.ContainsKey(site))
                {
                    throw new ArgumentException("No configuration information available for that connection pool ID ({0}) - unable to start", site);
                }
                _pools.Add(site, null);
            }
            if (_pools[site] == null)
            {
                if (this.PoolSource.LoadStrategy == LoadingStrategy.Lazy)
                {
                    startPool(site, source.CxnSources[site]);
                    //_pools[site] = (ConnectionPool)ConnectionPoolFactory.getResourcePool(source.CxnSources[site]);
                }
                else // treating this as an error case - if we're not lazy loading and the pool hasn't already been initialized then assume pool is being used incorrectly
                {
                    throw new ConfigurationErrorsException("The pools have not been initialized properly to service your request");
                }
            }
            // try and check out a connection from the pool
            return _pools[site].checkOut(null);
        }

        void startPool(String site, AbstractPoolSource source)
        {
           // System.Console.WriteLine("Instantiating connection pool");
            LogUtils.getInstance().Log("Instantiating connection pool for site " + site);
            if (_pools[site] == null)
            {
                lock (_instantiationLocker) // need to lock here so multiple threads don't try creating the same pool
                {
                    if (_pools[site] == null) // and then check again after lock is entered
                    {
                        _pools[site] = (ConnectionPool)ConnectionPoolFactory.getResourcePool(source);
                    }
                }
            }
        }

        /// <summary>
        /// Sends the shutdown signal to each pool. Marks this pool as shutting down also
        /// </summary>
        public override void  shutdown()
        {
            LogUtils.getInstance().Log("Shutting down the Pool of Pools!");
            if (SHUTDOWN_FLAG == 1)
            {
                return;
            }
            SHUTDOWN_FLAG = 1;
            string[] allKeys = new string[_pools.Keys.Count];
            _pools.Keys.CopyTo(allKeys, 0);
            IList<Thread> shutdownThreads = new List<Thread>();
            for (int i = 0; i < _pools.Count; i++)
            {
                if (_pools[allKeys[i]] != null)
                {
                    Thread shutdownThread = new Thread(new ThreadStart(_pools[allKeys[i]].shutdown));
                    shutdownThreads.Add(shutdownThread);
                    shutdownThread.Start();
                }
            }
            foreach (Thread t in shutdownThreads)
            {
                try
                {
                    t.Join(60000); // give each shutdown thread a minute to complete
                }
                catch (Exception) { }
            }
        }

        public override AbstractResource checkOutAlive(object obj)
        {
            AbstractResource resource = checkOut(obj);
            while (!resource.isAlive())
            {
                // System.Console.WriteLine("Found a disconnected resource from ConnectionPools");
                LogUtils.getInstance().Log("Found a disconnected resource from ConnectionPools... decrementing resource count and checking out another");
                _pools[(String)obj].decrementResourceCount(); // we decrement resource count for this pool here because checkOut doesn't do it
                //_pools[(String)obj].TotalResources--;
                resource = checkOut(obj);
            }
            return resource;
        }
    }
}
