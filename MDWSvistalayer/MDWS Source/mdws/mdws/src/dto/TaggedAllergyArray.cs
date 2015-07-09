using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedAllergyArray : AbstractTaggedArrayTO
    {
        public AllergyTO[] allergies;

        public TaggedAllergyArray() { }

        public TaggedAllergyArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedAllergyArray(string tag, Allergy[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.allergies = new AllergyTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.allergies[i] = new AllergyTO(mdos[i]);
            }
            this.count = allergies.Length;
        }

        public TaggedAllergyArray(string tag, Allergy mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.allergies = new AllergyTO[1];
            this.allergies[0] = new AllergyTO(mdo);
            this.count = 1;
        }

        public TaggedAllergyArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
