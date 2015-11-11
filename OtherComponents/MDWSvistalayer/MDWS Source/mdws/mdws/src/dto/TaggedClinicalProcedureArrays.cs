using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Collections;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TaggedClinicalProcedureArrays : AbstractArrayTO
    {
        public TaggedClinicalProcedureArray[] arrays;

        public TaggedClinicalProcedureArrays() { }

        public TaggedClinicalProcedureArrays(IndexedHashtable ihs)
        {
            if (ihs == null || ihs.Count == 0)
            {
                return;
            }

            arrays = new TaggedClinicalProcedureArray[ihs.Count];

            for (int i = 0; i < ihs.Count; i++)
            {
                string tag = (string)ihs.GetKey(i);

                if (ihs.GetValue(i) == null)
                {
                    arrays[i] = new TaggedClinicalProcedureArray(tag);
                }
                else if (MdwsUtils.isException(ihs.GetValue(i)))
                {
                    arrays[i] = new TaggedClinicalProcedureArray(tag, (Exception)ihs.GetValue(i));
                }
                else if (ihs.GetValue(i) is Hashtable && ((Hashtable)ihs.GetValue(i)).ContainsKey("ekgs"))
                {
                    IList<ClinicalProcedure> results = ((Hashtable)ihs.GetValue(i))["ekgs"] as IList<ClinicalProcedure>;
                    arrays[i] = new TaggedClinicalProcedureArray(tag, results);
                }
            }
        }
    }
}