using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws
{
    public class SoapQuery : QueryTemplate
    {
        public override void setUpQuery(MySession session)
        {
            // TODO - need to fix incomplete implementation of query templates before we can use connection pool w/ soap (i.e. baseservice calls)
            //if (Convert.ToBoolean(session.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            //{
            //    SessionMgr.getInstance().setConnections(session);
            //}
        }

        public override object query(Delegate theMethod, object[] methodArgs)
        {
            return theMethod.DynamicInvoke(methodArgs);
        }

        public override void tearDownQuery(MySession session)
        {
            //if (Convert.ToBoolean(session.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            //{
            //    SessionMgr.getInstance().setConnections(session);
            //}
        }
    }
}