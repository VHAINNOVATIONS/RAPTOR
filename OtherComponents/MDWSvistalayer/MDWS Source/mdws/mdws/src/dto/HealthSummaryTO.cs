using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class HealthSummaryTO : AbstractTO
    {
        public string siteCode; // Site code needs to be at a higher level.
        public string id;
        public string title;
        public string text;

        public HealthSummaryTO() { }

        public HealthSummaryTO(HealthSummary mdo, string siteCode)
        {
            Init(mdo, siteCode);
        }

        public void Init(HealthSummary mdo, string siteCode)
        {
            this.id = mdo.Id;
            this.title = mdo.Title;
            this.text = mdo.Text;
            this.siteCode = siteCode;
        }
    }
}
