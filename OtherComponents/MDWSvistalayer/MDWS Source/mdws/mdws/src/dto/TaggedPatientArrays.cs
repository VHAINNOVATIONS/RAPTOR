using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPatientArrays : AbstractArrayTO
    {
        public TaggedPatientArray[] arrays;

        public TaggedPatientArrays() { }

        public TaggedPatientArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            if (t.Count == 1 && MdwsUtils.isException(t.GetValue(0)))
            {
                fault = new FaultTO((Exception)t.GetValue(0));
                return;
            }
            arrays = new TaggedPatientArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedPatientArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedPatientArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    arrays[i] = new TaggedPatientArray((string)t.GetKey(i), ((System.Collections.Hashtable)t.GetValue(i))["demographics"] as Patient);
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedPatientArray((string)t.GetKey(i), (Patient[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedPatientArray((string)t.GetKey(i), (Patient)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
