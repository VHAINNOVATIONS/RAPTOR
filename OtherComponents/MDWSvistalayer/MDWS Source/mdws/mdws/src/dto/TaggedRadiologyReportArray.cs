using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedRadiologyReportArray : AbstractTaggedArrayTO
    {
        public RadiologyReportTO[] rpts;

        public TaggedRadiologyReportArray() { }

        public TaggedRadiologyReportArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedRadiologyReportArray(string tag, RadiologyReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new RadiologyReportTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new RadiologyReportTO(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedRadiologyReportArray(string tag, RadiologyReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new RadiologyReportTO[1];
            this.rpts[0] = new RadiologyReportTO(mdo);
            this.count = 1;
        }

        public TaggedRadiologyReportArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
