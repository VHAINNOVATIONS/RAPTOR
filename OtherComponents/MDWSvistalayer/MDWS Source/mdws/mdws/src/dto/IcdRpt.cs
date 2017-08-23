using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class IcdRpt : AbstractTO
    {
        public string title;
        public string timestamp;
        public TaggedText facility;
        public string icdCode;

        public IcdRpt() { }

        public IcdRpt(IcdReport mdo)
        {
            this.title = mdo.Title;
            this.timestamp = mdo.Timestamp;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            this.icdCode = mdo.IcdCode;
        }
    }
}
