using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedInpatientStayArray : AbstractTaggedArrayTO
    {
        public InpatientStayTO[] stays;

        public TaggedInpatientStayArray() { }

        public TaggedInpatientStayArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedInpatientStayArray(string tag, InpatientStay[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.stays = new InpatientStayTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.stays[i] = new InpatientStayTO(mdos[i]);
            }
            this.count = stays.Length;
        }

        public TaggedInpatientStayArray(string tag, InpatientStay mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.stays = new InpatientStayTO[1];
            this.stays[0] = new InpatientStayTO(mdo);
            this.count = 1;
        }

        public TaggedInpatientStayArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
