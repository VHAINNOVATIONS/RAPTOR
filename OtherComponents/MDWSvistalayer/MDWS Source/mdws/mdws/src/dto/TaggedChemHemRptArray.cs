using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedChemHemRptArray : AbstractTaggedArrayTO
    {
        public ChemHemRpt[] rpts;

        public TaggedChemHemRptArray() { }

        public TaggedChemHemRptArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedChemHemRptArray(string tag, ChemHemReport[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new ChemHemRpt[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.rpts[i] = new ChemHemRpt(mdos[i]);
            }
            this.count = rpts.Length;
        }

        public TaggedChemHemRptArray(string tag, ChemHemReport mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.rpts = new ChemHemRpt[1];
            this.rpts[0] = new ChemHemRpt(mdo);
            this.count = 1;
        }

        public TaggedChemHemRptArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
