using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedMicrobiologyRptArray : AbstractTaggedArrayTO
    {
        public MicrobiologyRpt[] rpts;

        public TaggedMicrobiologyRptArray() { }

        public TaggedMicrobiologyRptArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedMicrobiologyRptArray(string tag, MicrobiologyReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new MicrobiologyRpt[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new MicrobiologyRpt(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedMicrobiologyRptArray(string tag, MicrobiologyReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new MicrobiologyRpt[1];
            this.rpts[0] = new MicrobiologyRpt(mdo);
            this.count = 1;
        }

        public TaggedMicrobiologyRptArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
