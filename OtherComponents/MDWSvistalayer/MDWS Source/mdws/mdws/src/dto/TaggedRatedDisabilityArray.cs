using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedRatedDisabilityArray : AbstractTaggedArrayTO
    {
        public RatedDisabilityTO[] disabilities;

        public TaggedRatedDisabilityArray() { }

        public TaggedRatedDisabilityArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedRatedDisabilityArray(string tag, RatedDisability[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.disabilities = new RatedDisabilityTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.disabilities[i] = new RatedDisabilityTO(mdos[i]);
            }
            this.count = disabilities.Length;
        }

        public TaggedRatedDisabilityArray(string tag, RatedDisability mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.disabilities = new RatedDisabilityTO[1];
            this.disabilities[0] = new RatedDisabilityTO(mdo);
            this.count = 1;
        }

        public TaggedRatedDisabilityArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
