using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class VitalSignSetTO : AbstractTO
    {
        public string timestamp;
        public TaggedText facility;
        public VitalSignTO[] vitalSigns;
        public string units;
        public string qualifiers;

        public VitalSignSetTO() { }

        public VitalSignSetTO(VitalSignSet mdo)
        {
            this.timestamp = mdo.Timestamp;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            if (mdo.Count != 0)
            {
                VitalSign[] mdoSigns = mdo.VitalSigns;
                this.vitalSigns = new VitalSignTO[mdoSigns.Length];
                for (int i = 0; i < mdoSigns.Length; i++)
                {
                    this.vitalSigns[i] = new VitalSignTO(mdoSigns[i]);
                }
            }
            this.units = mdo.Units;
            this.qualifiers = mdo.Qualifiers;
        }
    }
}
