using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedLabReportArray : AbstractTaggedArrayTO
    {
        public LabReportTO[] arrays { get; set; }

        public TaggedLabReportArray() { }

        public TaggedLabReportArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedLabReportArray(string tag, Exception exc)
        {
            this.tag = tag;
            this.count = 0;
            this.fault = new FaultTO(exc);
        }

        public TaggedLabReportArray(string tag, IList<LabReport> mdos)
        {
            this.tag = tag;

            if (mdos == null || mdos.Count == 0)
            {
                return;
            }

            this.count = mdos.Count;
            arrays = new LabReportTO[mdos.Count];

            for (int i = 0; i < mdos.Count; i++)
            {
                arrays[i] = new LabReportTO(mdos[i]);
            }
        }
    }
}