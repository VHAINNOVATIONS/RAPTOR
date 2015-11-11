using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedMedicationArray : AbstractTaggedArrayTO
    {
        public MedicationTO[] meds;

        public TaggedMedicationArray() { }

        public TaggedMedicationArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedMedicationArray(string tag, Medication[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.meds = new MedicationTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.meds[i] = new MedicationTO(mdos[i]);
            }
            this.count = meds.Length;
        }

        public TaggedMedicationArray(string tag, Medication mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.meds = new MedicationTO[1];
            this.meds[0] = new MedicationTO(mdo);
            this.count = 1;
        }

        public TaggedMedicationArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
