using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedIcdRptArray : AbstractTaggedArrayTO
    {
        public IcdRpt[] rpts;

        public TaggedIcdRptArray() { }

        public TaggedIcdRptArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedIcdRptArray(string tag, IcdReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new IcdRpt[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new IcdRpt(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedIcdRptArray(string tag, IcdReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new IcdRpt[1];
            this.rpts[0] = new IcdRpt(mdo);
            this.count = 1;
        }

        public TaggedIcdRptArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
