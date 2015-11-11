using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class OrderTypeTO : AbstractTO
    {
        public string id;
        public string name1;
        public string name2;
        public bool requiresApproval;

        public OrderTypeTO() { }

        public OrderTypeTO(OrderType mdoOrderType)
        {
            if (mdoOrderType != null)
            {
                this.id = mdoOrderType.Id;
                this.name1 = mdoOrderType.Name1;
                this.name2 = mdoOrderType.Name2;
                this.requiresApproval = mdoOrderType.RequiresApproval;
            }
        }

        public OrderTypeTO(Exception e)
        {
            this.fault = new FaultTO(e);
        }
    }
}
