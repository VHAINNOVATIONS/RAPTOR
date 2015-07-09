using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class LabReportTO : AbstractTO
    {
        public LabPanelTO panel;
        public LabResultTO result;
        //public LabTestArray tests;
        //public LabResultTO Result { get; set; }
        public AuthorTO author { get; set; }
        public string caseNumber { get; set; }
        public string comment { get; set; }
        public SiteTO facility { get; set; }
        public string id { get; set; }
        public LabSpecimenTO specimen { get; set; }
        public string timestamp { get; set; }
        public string title { get; set; }
        public string text { get; set; }
        public string type { get; set; }

        public LabReportTO() { }

        public LabReportTO(LabReport report)
        {
            if (report == null)
            {
                return;
            }
            this.panel = new LabPanelTO(report.Panel);
            //if (report.Tests != null && report.Tests.Count > 0)
            //{
            //    tests = new LabTestArray(report.Tests);
            //}
            if (report.Result != null)
            {
                result = new LabResultTO(report.Result);
            }
            author = new AuthorTO(report.Author);
            caseNumber = report.CaseNumber;
            comment = report.Comment;
            if (report.Facility != null)
            {
                facility = new SiteTO(new mdo.Site(report.Facility.Id, report.Facility.Name));
            }
            id = report.Id;
            //Specimen = new LabSpecimenTO(report.Specimen);
            timestamp = report.Timestamp;
            title = report.Title;
            type = report.Type;
            text = report.Text;

            if (report.Specimen != null)
            {
                specimen = new LabSpecimenTO(report.Specimen);
            }
        }
    }
}