using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class RadiologyReportTO : AbstractTO
    {
        public string accessionNumber;
        public string caseNumber;
        public string id;
        public string title;
        public string timestamp;
        public AuthorTO author;
        public string text;
        public TaggedText facility;
        public string status;
        public string cptCode;
        public string clinicalHx;
        public string impression;

        public RadiologyReportTO() { }

        public RadiologyReportTO(RadiologyReport mdo)
        {
            if (null == mdo)
            {
                return;
            }

            this.id = mdo.Id;
            this.title = mdo.Title;
            this.timestamp = mdo.Timestamp;
            if (mdo.Author != null)
            {
                this.author = new AuthorTO(mdo.Author);
            }
            this.text = mdo.Text;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            this.status = mdo.Status;
            this.cptCode = mdo.CptCode;
            this.clinicalHx = mdo.ClinicalHx;
            this.impression = mdo.Impression;
            this.accessionNumber = mdo.AccessionNumber;
            this.caseNumber = mdo.CaseNumber;
        }
    }
}
