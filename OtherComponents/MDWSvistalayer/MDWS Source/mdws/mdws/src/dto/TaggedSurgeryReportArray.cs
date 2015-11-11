using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedSurgeryReportArray : AbstractTaggedArrayTO
    {
        public SurgeryReportTO[] rpts;

        public TaggedSurgeryReportArray() { }

        public TaggedSurgeryReportArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedSurgeryReportArray(string tag, SurgeryReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new SurgeryReportTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new SurgeryReportTO(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedSurgeryReportArray(string tag, SurgeryReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new SurgeryReportTO[1];
            this.rpts[0] = new SurgeryReportTO(mdo);
            this.count = 1;
        }

        public TaggedSurgeryReportArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
