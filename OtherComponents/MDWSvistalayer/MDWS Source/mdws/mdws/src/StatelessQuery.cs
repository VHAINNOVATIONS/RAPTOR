using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.domain.pool;

namespace gov.va.medora.mdws
{
    public class StatelessQuery : QueryTemplate
    {
        public override void setUpQuery(MySession session)
        {
            if (base.QuerySites == null || base.QuerySites.Count == 0)
            {
                throw new ArgumentException("No sites specified for stateless query!");
            }
            session.ConnectionSet = new mdo.dao.ConnectionSet();
            foreach (String site in base.QuerySites)
            {
                session.ConnectionSet.Add((AbstractConnection)ConnectionPools.getInstance().checkOutAlive(site));
            }
        }

        public override object query(Delegate theMethod, object[] methodArgs)
        {
            return theMethod.DynamicInvoke(methodArgs);
        }

        public override void tearDownQuery(MySession session)
        {
            foreach (AbstractConnection cxn in session.ConnectionSet.Connections.Values)
            {
                ConnectionPools.getInstance().checkIn(cxn);
            }
        }
    }
}