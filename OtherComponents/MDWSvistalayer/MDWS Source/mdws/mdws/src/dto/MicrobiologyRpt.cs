using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class MicrobiologyRpt : AbstractTO
    {
        public string id;
        public string title;
        public string timestamp;
        public AuthorTO author;
        public TaggedText facility;
        public LabSpecimenTO specimen;
        public string comment;
        public string sample;
        public string text;

        public MicrobiologyRpt() { }

        public MicrobiologyRpt(MicrobiologyReport mdo)
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
            this.comment = mdo.Comment;
            this.sample = mdo.Sample;
            this.text = mdo.Text;
        }
    }
}
