using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class LabPanelTO : AbstractTO
    {
        public LabTestArray tests;
        public String name;
        public OrderTO order;

        public LabPanelTO() { }

        public LabPanelTO(LabPanel mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.tests = new LabTestArray(mdo.Tests);
            this.name = mdo.Name;
            this.order = new OrderTO(mdo.Order);
        }
    }
}