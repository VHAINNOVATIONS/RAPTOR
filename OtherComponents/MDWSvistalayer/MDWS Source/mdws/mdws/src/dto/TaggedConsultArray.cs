using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedConsultArray : AbstractTaggedArrayTO
    {
        public ConsultTO[] consults;

        public TaggedConsultArray() { }

        public TaggedConsultArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedConsultArray(string tag, Consult[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.consults = new ConsultTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.consults[i] = new ConsultTO(mdos[i]);
            }
            this.count = consults.Length;
        }

        public TaggedConsultArray(string tag, Consult mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.consults = new ConsultTO[1];
            this.consults[0] = new ConsultTO(mdo);
            this.count = 1;
        }

        public TaggedConsultArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
