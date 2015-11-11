using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    class VistaConnectionPoolDaoFactory : VistaDaoFactory
    {
        public override AbstractConnection getConnection(DataSource dataSource)
        {
            VistaPoolConnection myCxn = new VistaPoolConnection(dataSource);
            myCxn.ConnectStrategy = new VistaNatConnectStrategy(myCxn);
            return myCxn;
        }
    }
}
