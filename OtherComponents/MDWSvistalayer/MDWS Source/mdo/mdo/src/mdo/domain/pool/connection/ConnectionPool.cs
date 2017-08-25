using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;
using System.Collections.Concurrent;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.dao.vista;
using System.Threading;
using System.Threading.Tasks;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionPool : AbstractResourcePool
    {
        protected byte SHUTDOWN_FLAG = 0;
        private readonly object _locker = new object(); // don't make static because there may be many pools

        IList<ConnectionThread> _startedCxns = new List<ConnectionThread>();
        IList<ConnectionThread> _cxnsToRemove = new List<ConnectionThread>();

        internal BlockingCollection<AbstractConnection> _pooledCxns = new BlockingCollection<AbstractConnection>();
        DateTime _startupTimestamp;

        DateTime _lastSuccessfulCxn = DateTime.Now;
        Int32 _consecutiveCxnErrorCount = 0;

        IList<Task> _cleanupTasks = new List<Task>();

        /// <summary>
        /// This method removes an object from the pool
        /// </summary>
        /// <param name="obj">Not currently used</param>
        /// <returns>AbstractConnection</returns>
        public override AbstractResource checkOut(object obj)
        {
            AbstractConnection cxn = null;
            if (!_pooledCxns.TryTake(out cxn, this.PoolSource.WaitTime))
            {
               // System.Console.WriteLine("Unable to remove connection from pool - were " + _pooledCxns.Count + " cxns available - total resource count: " + this.TotalResources);
                throw new TimeoutException("No connection could be obtained in the configured allotment");
            }
            return cxn;
        }

        /// <summary>
        /// Return an AbstractConnection to the pool
        /// </summary>
        /// <param name="cxn">The connection to return to the pool</param>
        /// <returns></returns>
        public override object checkIn(AbstractResource cxn)
        {
            AbstractConnection theCxn = (AbstractConnection)cxn; // first get a new reference
            if (!theCxn.IsConnected || !theCxn.isAlive()) // don't add disconnected connections to the pool
            {
                //System.Console.WriteLine("Tried checking in invalid connection");
                this.decrementResourceCount();
                //this.TotalResources--;
                return null;
            }
            theCxn.LastUsed = DateTime.Now;

            if (cxn is VistaPoolConnection)
            {
                if (this.PoolSource.Credentials == null) // if not an authenticated connection
                {
                    ((VistaPoolConnection)theCxn).resetRaw(); // reset the symbol table so we're always receiving fresh from pool
                    ((VistaAccount)((VistaConnection)theCxn).Account).setContext(new MenuOption("XUS SIGNON"));
                }
                _pooledCxns.Add(theCxn);
            }
            else if (cxn is VistaConnection)
            {
                if (this.PoolSource.Credentials == null) // if not an authenticated connection
                {
                    ((VistaAccount)((VistaConnection)theCxn).Account).setContext(new MenuOption("XUS SIGNON"));
                }
                _pooledCxns.Add(theCxn);
            }
            else
            {
                _pooledCxns.Add(theCxn);
            }
            //System.Console.WriteLine("A connection was returned to the pool. {0} available", _pooledCxns.Count);
            return null;
        }

        /// <summary>
        /// The job of the run function is simply to make sure we have connections available in the pool. If the # of 
        /// connections falls below the threshold, the pool expands (up to the limit). The loop also tries to clean
        /// up any connections that may have failed
        /// </summary>
        internal void run()
        {
            //System.Console.WriteLine("Run called...");
            LogUtils.getInstance().Log("Run called on pool " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
            lock (_locker)
            {
               // System.Console.WriteLine("Run entered...");
                _startupTimestamp = DateTime.Now;

                while (!Convert.ToBoolean(SHUTDOWN_FLAG))
                {
                    System.Threading.Thread.Sleep(500); // this small sleep time prevents the thread from consuming 100% of CPU

                    // this first IF statement checks to see if more connections need to be added to the pool
                    if (_pooledCxns.Count < this.PoolSource.MinPoolSize && _startedCxns.Count == 0) // only grow if we haven't started any connections
                    {
                        if (this.TotalResources < this.PoolSource.MaxPoolSize)
                        {
                            growPool();
                        }
                    }

                    // the second IF checks if this pool has started any connections - most of the time this should be false so we check it before the getEnumerator call
                    if (_startedCxns.Count > 0)
                    {
                        //Console.WriteLine("Found {0} connections that were started", _startedCxns.Count);
                        //LogUtils.getInstance().Log(String.Format("{0} connections were scheduled to be started", _startedCxns.Count));
                        IEnumerator<ConnectionThread> enumerator = _startedCxns.GetEnumerator();
                        while (enumerator.MoveNext())
                        {
                            ConnectionThread current = enumerator.Current;
                            Thread t = current.Thread;
                            //if (current.Connection.IsConnected)
                            if (!(t.IsAlive) && current.Connection.IsConnected) // check if started connection is ready for our pool
                            {
                                try { t.Join(0); } catch (Exception) { } 
                                //Console.WriteLine("Found successfully started connection");
                                this.incrementResourceCount();
                                _pooledCxns.Add(current.Connection);
                                _cxnsToRemove.Add(current);
                                LogUtils.getInstance().Log(String.Format("Found a successful connection - incremented resource count to {0} and added to pool containing {1} cxns ({2})", this.TotalResources, this._pooledCxns.Count, ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id));
                            }
                            //else if (!current.Connection.IsConnected) // check if started connection thread has completed but for any reason disconnected
                            else if (!(t.IsAlive) && !current.Connection.IsConnected) // check if started connection thread has completed but for any reason disconnected
                            {
                                try { t.Join(0); } catch (Exception) { }
                               // Console.WriteLine("Found apparent failed connection - removing");
                                LogUtils.getInstance().Log("It appears one of the started connections failed to connect... " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
                                _cxnsToRemove.Add(current);
                            }
                            else if (DateTime.Now.Subtract(current.Timestamp).TotalSeconds > this.PoolSource.WaitTime.TotalSeconds) // lastly check for long running connection attempts
                            {
                                LogUtils.getInstance().Log("Found long running connection attempt - scheduling for cleanup: " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
                                _cxnsToRemove.Add(current);

                                try
                                {                                    
                                    // create async task to wait for the thread to stop and disconnect the cxn just to be sure things are cleaned up
                                    Task disconnectTask = new System.Threading.Tasks.Task(() => 
                                    { 
                                        t.Join(this.PoolSource.WaitTime); // should be enough - waiting for double pool wait time in total
                                        disconnect(current.Connection);
                                    });
                                    _cleanupTasks.Add(disconnectTask);
                                    disconnectTask.Start();
                                }
                                catch (AggregateException ae) { LogUtils.getInstance().Log(ae.ToString()); } 
                                catch (Exception) { /* swallow */ }
                            }
                            
                        }
                    }

                    // per previous IF - can't modify collection while enumerating so the removal of failed connections is a separate step
                    if (_cxnsToRemove.Count > 0)
                    {
                        foreach (ConnectionThread t in _cxnsToRemove)
                        {
                            _startedCxns.Remove(t);
                        }
                        _cxnsToRemove = new List<ConnectionThread>();
                    }

                    try { checkCleanupTasks(); } catch (Exception) { }
                }
            }
            LogUtils.getInstance().Log("Run exited for pool " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
        }

        void checkCleanupTasks()
        {
            bool allCleanupTasksNull = true;
            String poolSiteId = ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id;
            for (int i = 0; i < _cleanupTasks.Count; i++)
            {
                if (_cleanupTasks[i] == null)
                {
                    continue;
                }
                LogUtils.getInstance().Log("Found " + _cleanupTasks.Count + " tasks to clean up: " + poolSiteId);
                if (_cleanupTasks[i].IsFaulted)
                {
                    LogUtils.getInstance().Log("Found a failed cleanup task: " + _cleanupTasks[i].Exception.Message + " - " + poolSiteId);
                    _cleanupTasks[i] = null;
                }
                else if (_cleanupTasks[i].IsCompleted)
                {
                    LogUtils.getInstance().Log("Found a completed cleanup task: " + poolSiteId);
                    _cleanupTasks[i] = null;
                }
                else // if task not
                {
                    LogUtils.getInstance().Log("Cleanup task appears to be still running: " + poolSiteId);
                    allCleanupTasksNull = false;
                }
            }
            // if all cleanup tasks have been addressed then create a new list!
            if (allCleanupTasksNull && _cleanupTasks.Count > 0)
            {
                LogUtils.getInstance().Log("All cleanup tasks finished - " + poolSiteId);
                _cleanupTasks = new List<Task>();
            }
        }

        void growPool()
        {
            if (_consecutiveCxnErrorCount > this.PoolSource.MaxConsecutiveErrors) // we want to recognize when a site might be down, network issues, etc. - wait to retry if we have many connection failures without success
            {
                if (DateTime.Now.Subtract(_lastSuccessfulCxn).CompareTo(this.PoolSource.WaitOnMaxConsecutiveErrors) < 0)
                {
                   // LogUtils.getInstance().Log(String.Format("{0} consecutive failed connection attempts... waiting {1} to start new cxns", _consecutiveCxnErrorCount, this.PoolSource.WaitOnMaxConsecutiveErrors.Subtract(DateTime.Now.Subtract(_lastSuccessfulCxn))));
                    return; // don't start any new cxns if we've seen a lot of errors and haven't waited at least 5 mins (or configurable timespan)
                }
                else // waited configured time, reset error vars so growPool will try creating cxns
                {
                    LogUtils.getInstance().Log("Resetting error related vars so will re-try instantiating cxns.. " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
                    _consecutiveCxnErrorCount = 0;
                    _lastSuccessfulCxn = DateTime.Now;
                }
            }
            if ((_cleanupTasks.Count + this.TotalResources) >= this.PoolSource.MaxPoolSize)
            {
                LogUtils.getInstance().Log("The # of cleanup tasks scheduled + the number of current resources exceeds the max pool size - waiting until cleanup tasks are addressed to grow");
                return; // too many cleanup tasks scheduled! don't grow the pool until the cxns we've attempted to start have been addressed
            }
            // i think there may be a possible race condition here where a cxn may be disconnected during the size
            int growSize = this.PoolSource.PoolExpansionSize;
            if ((this.TotalResources + growSize) > this.PoolSource.MaxPoolSize) // if the growth would expand the pool above the max pool size, only grow by the amount allowed
            {
                growSize = this.PoolSource.MaxPoolSize - this.TotalResources;
            }
            LogUtils.getInstance().Log(String.Format("Connection pool at min size {0}/{1} - growing by {2}. Current total resources: {3} - site: {4}", this.PoolSource.MinPoolSize, this.PoolSource.MaxPoolSize, growSize, this.TotalResources, ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id));
            for (int i = 0; i < growSize; i++)
            {
                ConnectionThread a = new ConnectionThread();
                _startedCxns.Add(a);
                a.Connection = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(((ConnectionPoolSource)this.PoolSource).CxnSource.Protocol))
                    .getConnection(((ConnectionPoolSource)this.PoolSource).CxnSource);
               // Task connectTask = new Task(() => connect(a));
              //  a.Thread = connectTask;
               // connectTask.Start();
                //connect(a);
                Thread t = new Thread(new ParameterizedThreadStart(connect));
                a.Thread = t;
                t.Start(a);
            }
        }

        void connect(object obj)
        {
            ConnectionThread cxn = (ConnectionThread)obj;
            try
            {
                cxn.Connection.connect();
                // Authenticated Vista Connection Handling
                if (this.PoolSource.Credentials != null) // should be an authenticated connection!
                {
                    if (this.PoolSource.Permission == null)
                    {
                        this.PoolSource.Permission = new MenuOption(VistaConstants.CAPRI_CONTEXT);
                    }
                    this.PoolSource.Permission.IsPrimary = true;
                    if (String.IsNullOrEmpty(this.PoolSource.Credentials.AccountName) || String.IsNullOrEmpty(this.PoolSource.Credentials.AccountPassword))
                    {
                        cxn.Connection.Account.AuthenticationMethod = VistaConstants.NON_BSE_CREDENTIALS; // if no A/V codes, visit
                    }
                    else
                    {
                        cxn.Connection.Account.AuthenticationMethod = VistaConstants.LOGIN_CREDENTIALS;
                    }
                    cxn.Connection.Account.authenticateAndAuthorize(this.PoolSource.Credentials, this.PoolSource.Permission);
                }
                // END Authenticated cxn handling
                else if (cxn.Connection is VistaPoolConnection) // else if pool cxn and no creds - get default state
                {
                    ((VistaPoolConnection)cxn.Connection)._rawConnectionSymbolTable = ((VistaPoolConnection)cxn.Connection).getState();
                }
                //this.incrementResourceCount();
                _lastSuccessfulCxn = DateTime.Now;
                _consecutiveCxnErrorCount = 0;
               // System.Console.WriteLine(String.Format("Successfully added a connection - total resources {0}", this.TotalResources));
                //this.TotalResources++;
                cxn.Connection.setTimeout(this.PoolSource.Timeout); // now gracefully timing out connections!
            }
            catch (Exception exc)
            {
                _consecutiveCxnErrorCount++;
                LogUtils.getInstance().Log("There was a problem connecting to " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id + ": " + exc.Message);
                cxn.Connection.IsConnected = false;
                disconnect(cxn.Connection);
            }
        }

        void disconnect(object AbstractConnection)
        {
            try
            {
                ((AbstractConnection)AbstractConnection).disconnect();
            }
            catch (Exception) { }
        }

        /// <summary>
        /// Signal the pool to shutdown. An attempt will be made to wait for as many connections as possible for return to the pool 
        /// before disconnecting each of the connections. Sets SHUTDOWN_FLAG so pool no longer tries to continue to run
        /// </summary>
        public override void shutdown()
        {
            if (SHUTDOWN_FLAG == 1)
            {
                return;
            }
            SHUTDOWN_FLAG = 1;

            AbstractConnection current = null;
            while (_pooledCxns.TryTake(out current, 1000))
            {
                current.disconnect();
            }
        }

        /// <summary>
        /// Check the status of the pool. If the pool has been signalled to shutdown, this should return false. If the pool
        /// has no available resources and has been running for more than one minute, assume something went wrong and return false.
        /// Otherwise, this should return true
        /// </summary>
        public bool IsAlive 
        {
            get
            {
                if (Convert.ToBoolean(SHUTDOWN_FLAG))
                {
                    return false;
                }
                if ((TotalResources == 0 && (_startedCxns == null || _startedCxns.Count == 0)) && // if no total resources AND no started connections
                    DateTime.Now.Subtract(_startupTimestamp).CompareTo(this.PoolSource.WaitTime) > 0) // if we have no resources, no started resources AND pool started more than 60 seconds ago
                {
                    return false;
                }
                return true;
            }
        }

        /// <summary>
        /// Check out an object from the pool and call isAlive to guarantee state. Decrement resource count
        /// on pool if object is not alive and discard it by removing references
        /// </summary>
        /// <param name="obj"></param>
        /// <returns>AbstractResource</returns>
        public override AbstractResource checkOutAlive(object obj)
        {
            AbstractResource resource = checkOut(obj);
            while (!resource.isAlive())
            {
                LogUtils.getInstance().Log("Found disconnected/timed out connection... Fetching another: " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
                this.decrementResourceCount();
                //this.TotalResources--;
                resource = checkOut(obj);
            }
            if (resource is VistaPoolConnection && this.PoolSource.RecycleCount > 0) // only need to step in here if the recycle count was actually set
            {
                VistaPoolConnection cxn = (VistaPoolConnection)resource;
                if (cxn.QueryCount >= this.PoolSource.RecycleCount) // if we've used cxn more times than specified before recycle, we should disconnect this connection and return another
                {
                    LogUtils.getInstance().Log("Max number of queries exceeded for connection - disconnecting and discarding: " + ((ConnectionPoolSource)this.PoolSource).CxnSource.SiteId.Id);
                    new System.Threading.Tasks.Task(() => cxn.disconnect()).Start();
                    this.decrementResourceCount();
                    //this.TotalResources--;
                    return this.checkOutAlive(obj);
                }
            }
            return resource;
        }
    }
}
