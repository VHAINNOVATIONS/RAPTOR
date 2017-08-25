using System;
using System.Collections.Generic;
using gov.va.medora.mdo.domain;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class OrderCheckTO : AbstractTO
    {
        public String id;
        public String name;
        public String level;
        public String abbreviation;

        public OrderCheckTO() { }

        public OrderCheckTO(OrderCheck mdo)
        {
            if (mdo != null)
            {
                this.id = mdo.Id;
                this.name = mdo.Name;
                this.abbreviation = mdo.Abbreviation;
                this.level = mdo.Level;
            }
        }

        public OrderCheckTO(Exception exc)
        {
            this.fault = new FaultTO(exc);
        }
    }
}