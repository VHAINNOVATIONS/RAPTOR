using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class ClinicalProcedure
    {
        public Site Facility { get; set; }
        public string Name { get; set; }
        public string Timestamp { get; set; }
        public Note Note { get; set; }
        public string Id { get; set; }
        public string ReportIen { get; set; }
        public string Report { get; set; }
        public String RequiresApproval { get; set; }
    }
}
