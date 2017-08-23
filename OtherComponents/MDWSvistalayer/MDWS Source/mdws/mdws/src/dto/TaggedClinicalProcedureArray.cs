using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TaggedClinicalProcedureArray : AbstractTaggedArrayTO
    {
        public ClinicalProcedureTO[] array;

        public TaggedClinicalProcedureArray() { }

        public TaggedClinicalProcedureArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedClinicalProcedureArray(string tag, IList<ClinicalProcedure> mdos)
        {
            this.tag = tag;

            if (mdos == null || mdos.Count == 0)
            {
                this.count = 0;
                return;
            }

            array = new ClinicalProcedureTO[mdos.Count];
            for (int i = 0; i < mdos.Count; i++)
            {
                array[i] = new ClinicalProcedureTO(mdos[i]);
            }
        }

        public TaggedClinicalProcedureArray(string tag, Exception exception)
        {
            this.tag = tag;
            this.fault = new FaultTO(exception);
        }
    }
}