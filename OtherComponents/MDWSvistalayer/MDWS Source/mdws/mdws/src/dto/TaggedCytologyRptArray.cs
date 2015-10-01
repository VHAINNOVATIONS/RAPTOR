using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedCytologyRptArray : AbstractTaggedArrayTO
    {
        public CytologyRpt[] rpts;

        public TaggedCytologyRptArray() { }

        public TaggedCytologyRptArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedCytologyRptArray(string tag, CytologyReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new CytologyRpt[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new CytologyRpt(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedCytologyRptArray(string tag, CytologyReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new CytologyRpt[1];
            this.rpts[0] = new CytologyRpt(mdo);
            this.count = 1;
        }

        public TaggedCytologyRptArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
