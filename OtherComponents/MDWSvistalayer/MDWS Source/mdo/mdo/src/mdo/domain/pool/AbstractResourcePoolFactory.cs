using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool
{
    public abstract class AbstractResourcePoolFactory
    {
        /// <summary>
        /// Get and start the resource pool
        /// </summary>
        /// <param name="source">The source for the resource pool</param>
        /// <returns>AbstractResourcePool</returns>
        public static AbstractResourcePool getResourcePool(AbstractPoolSource source)
        {
            if (source is connection.ConnectionPoolSource)
            {
                connection.ConnectionPoolFactory connectionPoolFactory = new connection.ConnectionPoolFactory();
                return connectionPoolFactory.getPool(source);
            }
            else if (source is connection.ConnectionPoolsSource)
            {
                connection.ConnectionPoolFactory connectionPoolFactory = new connection.ConnectionPoolFactory();
                return connectionPoolFactory.getPools(source); // notice the 's' - getting a ConnectionPools object
            }
            else
            {
                throw new NotImplementedException();
            }
        }
    }
}
