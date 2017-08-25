using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedVisitArray : AbstractTaggedArrayTO
    {
        public VisitTO[] visits;

        public TaggedVisitArray() { }

        public TaggedVisitArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedVisitArray(string tag, Visit[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.visits = new VisitTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.visits[i] = new VisitTO(mdos[i]);
            }
            this.count = visits.Length;
        }

        public TaggedVisitArray(string tag, Visit mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.visits = new VisitTO[1];
            this.visits[0] = new VisitTO(mdo);
            this.count = 1;
        }

        public TaggedVisitArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
