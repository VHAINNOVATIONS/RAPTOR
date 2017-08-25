using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedVitalSignArray : AbstractTaggedArrayTO
    {
        public VitalSignTO[] vitals;

        public TaggedVitalSignArray() { }

        public TaggedVitalSignArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedVitalSignArray(string tag, IList<VitalSignSet> mdos)
        {
            this.tag = tag;
            if (mdos == null || mdos.Count == null)
            {
                this.count = 0;
                return;
            }

            IList<VitalSignTO> allSigns = new List<VitalSignTO>();
            foreach (VitalSignSet set in mdos)
            {
                if (set == null || set.VitalSigns == null || set.VitalSigns.Length == 0)
                {
                    continue;
                }
                foreach (VitalSign sign in set.VitalSigns)
                {
                    VitalSignTO newVital = new VitalSignTO(sign);
                    if (sign.Facility != null)
                    {
                        newVital.facility = new TaggedText(sign.Facility.Id, sign.Facility.Name);
                    }
                    if (sign.Location != null)
                    {
                        newVital.location = new HospitalLocationTO(set.Location);
                    }
                    newVital.timestamp = set.Timestamp;
                    allSigns.Add(newVital);
                }
            }

            this.vitals = new VitalSignTO[allSigns.Count];
            allSigns.CopyTo(this.vitals, 0);
            this.count = allSigns.Count;
        }

        public TaggedVitalSignArray(string tag, VitalSign[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.vitals = new VitalSignTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.vitals[i] = new VitalSignTO(mdos[i]);
            }
            this.count = vitals.Length;
        }

        public TaggedVitalSignArray(string tag, VitalSign mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.vitals = new VitalSignTO[1];
            this.vitals[0] = new VitalSignTO(mdo);
            this.count = 1;
        }

        public TaggedVitalSignArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
