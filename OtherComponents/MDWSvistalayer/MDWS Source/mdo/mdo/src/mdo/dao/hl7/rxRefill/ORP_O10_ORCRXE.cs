using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Model.V24.Message;
using NHapi.Model.V24.Segment;

namespace gov.va.medora.mdo.dao.hl7.rxRefill
{
    public class ORP_O10_ORCRXE : ORP_O10
    {

        public ORP_O10_ORCRXE() : base()
        {
            this.add(typeof(ORC), true, true);
            this.add(typeof(RXE), true, true);
        }

        public ORC getOrc(int rep)
        {
            return (ORC)this.GetStructure("ORC", rep);
        }

        public RXE getRxe(int rep)
        {
            return (RXE)this.GetStructure("RXE", rep);
        }

    }
}
