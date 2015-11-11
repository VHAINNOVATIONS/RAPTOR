using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class OefOifTO : AbstractTO
    {
        public string location;
        public string fromDate;
        public string toDate;
        public bool dataLocked;
        public string recordedDate;
        public TaggedText recordingSite;

        public OefOifTO() { }

        public OefOifTO(OEF_OIF o)
        {
            this.location = o.Location;
            this.fromDate = o.FromDate.ToString("yyyyMMdd");
            this.toDate = o.ToDate.ToString("yyyyMMdd");
            this.dataLocked = o.DataLocked;
            this.recordedDate = o.RecordedDate.ToString("yyyyMMdd");
            this.recordingSite = new TaggedText(o.RecordingSite);
        }
    }
}
