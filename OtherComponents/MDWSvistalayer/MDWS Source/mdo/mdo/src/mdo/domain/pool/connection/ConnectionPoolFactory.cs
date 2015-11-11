using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionPoolFactory : AbstractResourcePoolFactory
    {
        public ConnectionPool getPool(AbstractPoolSource source)
        {
            if (source == null)
            {
                throw new ArgumentException("Need to supply pool source before connection pool can be built");
            }
            ConnectionPool pool = new ConnectionPool();
            pool.PoolSource = (ConnectionPoolSource)source;
            Thread poolThread = new Thread(new ThreadStart(pool.run));
            poolThread.Name = "MdwsConnectionPool" + ((ConnectionPoolSource)source).CxnSource.SiteId.Id;
            poolThread.IsBackground = true; // this allows the main process to terminate without the connection pool being forced to clean up - ok?
            poolThread.Start();
            return pool;
        }

        public ConnectionPools getPools(AbstractPoolSource source)
        {
            if (source == null)
            {
                throw new ArgumentException("Need to supply pool source before connection pool can be built");
            }
            ConnectionPools pool = ConnectionPools.getInstance();
            pool.PoolSource = (ConnectionPoolsSource)source;
            Thread poolThread = new Thread(new ThreadStart(pool.run));
            poolThread.Name = "PoolOfPools";
            poolThread.IsBackground = true; // this allows the main process to terminate without the connection pool being forced to clean up - ok?
            poolThread.Start();
            return pool;
        }
    }
}
