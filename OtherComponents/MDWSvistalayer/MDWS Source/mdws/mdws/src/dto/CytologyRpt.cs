using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class CytologyRpt : AbstractTO
    {
        public string id;
        public string title;
        public string timestamp;
        public AuthorTO author;
        public TaggedText facility;
        public LabSpecimenTO specimen;
        public string clinicalHx;
        public string description;
        public string exam;
        public string diagnosis;
        public string comment;
        public string supplementalRpt;

        public CytologyRpt() { }

        public CytologyRpt(CytologyReport mdo)
        {
            this.id = mdo.Id;
            this.title = mdo.Title;
            this.timestamp = mdo.Timestamp;
            if (mdo.Author != null)
            {
                this.author = new AuthorTO(mdo.Author);
            }
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            if (mdo.Specimen != null)
            {
                this.specimen = new LabSpecimenTO(mdo.Specimen);
            }
            this.clinicalHx = mdo.ClinicalHx;
            this.description = mdo.Description;
            this.exam = mdo.Exam;
            this.diagnosis = mdo.Diagnosis;
            this.comment = mdo.Comment;
            this.supplementalRpt = mdo.SupplementalReport;
        }
    }
}
