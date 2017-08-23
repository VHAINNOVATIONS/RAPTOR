using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;

namespace gov.va.medora.mdws.dto
{
    public class LabSpecimenTO : AbstractTO
    {
        public string id;
        public string name;
        public string collectionDate;
        public string accessionNum;
        public string site;
        public TaggedText facility;

        public LabSpecimenTO() { }

        public LabSpecimenTO(LabSpecimen mdo)
        {
            this.id = mdo.Id;
            //this.name = mdo.Name;
            this.name = StringUtils.stripInvalidXmlCharacters(mdo.Name); 
            this.collectionDate = mdo.CollectionDate;
            this.accessionNum = mdo.AccessionNumber;
            this.site = mdo.Site;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Name);
            }
        }
    }
}
