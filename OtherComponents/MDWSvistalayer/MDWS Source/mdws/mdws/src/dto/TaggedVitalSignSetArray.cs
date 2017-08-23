using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedVitalSignSetArray : AbstractTaggedArrayTO
    {
        public VitalSignSetTO[] sets;

        public TaggedVitalSignSetArray() { }

        public TaggedVitalSignSetArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedVitalSignSetArray(string tag, VitalSignSet[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.sets = new VitalSignSetTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.sets[i] = new VitalSignSetTO(mdos[i]);
            }
            this.count = sets.Length;
        }

        public TaggedVitalSignSetArray(string tag, VitalSignSet mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.sets = new VitalSignSetTO[1];
            this.sets[0] = new VitalSignSetTO(mdo);
            this.count = 1;
        }

        public TaggedVitalSignSetArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
