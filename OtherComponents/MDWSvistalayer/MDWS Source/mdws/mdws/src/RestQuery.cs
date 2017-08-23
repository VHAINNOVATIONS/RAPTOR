using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws
{
    public class RestQuery : QueryTemplate
    {
        public override void setUpQuery(MySession session)
        {
            if (Convert.ToBoolean(session.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            {
                SessionMgr.getInstance().setConnections(session);
            }
        }

        public override object query(Delegate theMethod, object[] args)
        {
            return theMethod.DynamicInvoke(args);
        }

        public override void tearDownQuery(MySession session)
        {
            if (Convert.ToBoolean(session.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            {
                SessionMgr.getInstance().returnConnections(session);
            }
        }
    }
}