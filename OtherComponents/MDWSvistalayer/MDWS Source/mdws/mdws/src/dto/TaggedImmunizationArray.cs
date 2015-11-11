using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedImmunizationArray : AbstractTaggedArrayTO
    {
        public ImmunizationTO[] immunizations;

        public TaggedImmunizationArray() { }

        public TaggedImmunizationArray(string tag, IList<Immunization> mdos)
        {
            this.tag = tag;

            if (mdos == null || mdos.Count <= 0)
            {
                this.count = 0;
                return;
            }

            this.count = mdos.Count;
            immunizations = new ImmunizationTO[this.count];

            for (int i = 0; i < this.count; i++)
            {
                immunizations[i] = new ImmunizationTO(mdos[i]);
            }
        }

        public TaggedImmunizationArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedImmunizationArray(string tag, Exception exception)
        {
            this.tag = tag;
            this.count = 0;
            this.fault = new FaultTO(exception);
        }
    }
}