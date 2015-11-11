using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ChemHemRpt : AbstractTO
    {
        public string id;
        public string title;
        public string timestamp;
        public AuthorTO author;
        public TaggedText facility;
        public LabSpecimenTO specimen;
        public string comment;
        public LabResultTO[] results;
        public SiteTO[] labSites;

        public ChemHemRpt() { }

        public ChemHemRpt(ChemHemReport mdo)
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
            if (mdo.Results != null)
            {
                this.results = new LabResultTO[mdo.Results.Length];
                for (int i = 0; i < mdo.Results.Length; i++)
                {
                    this.results[i] = new LabResultTO(mdo.Results[i]);
                }
            }
            if (mdo.LabSites != null)
            {
                this.labSites = new SiteTO[mdo.LabSites.Count];
                int i = 0;
                foreach (DictionaryEntry de in mdo.LabSites)
                {
                    this.labSites[i++] = new SiteTO((Site)de.Value);
                }
            }
        }
    }
}
