using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedSurgicalPathologyRptArray : AbstractTaggedArrayTO
    {
        public SurgicalPathologyRpt[] rpts;

        public TaggedSurgicalPathologyRptArray() { }

        public TaggedSurgicalPathologyRptArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedSurgicalPathologyRptArray(string tag, SurgicalPathologyReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new SurgicalPathologyRpt[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new SurgicalPathologyRpt(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedSurgicalPathologyRptArray(string tag, SurgicalPathologyReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new SurgicalPathologyRpt[1];
            this.rpts[0] = new SurgicalPathologyRpt(mdo);
            this.count = 1;
        }

        public TaggedSurgicalPathologyRptArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
