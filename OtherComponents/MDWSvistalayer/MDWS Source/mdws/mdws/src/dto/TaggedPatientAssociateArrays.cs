using System;
using System.Collections.Specialized;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPatientAssociateArrays : AbstractArrayTO
    {
        public TaggedPatientAssociateArray[] arrays;

        public TaggedPatientAssociateArrays() { }

        public TaggedPatientAssociateArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedPatientAssociateArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedPatientAssociateArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedPatientAssociateArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedPatientAssociateArray((string)t.GetKey(i), (PatientAssociate[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedPatientAssociateArray((string)t.GetKey(i), (PatientAssociate)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
