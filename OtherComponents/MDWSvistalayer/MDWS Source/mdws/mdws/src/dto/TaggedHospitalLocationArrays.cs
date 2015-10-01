using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedHospitalLocationArrays : AbstractArrayTO
    {
        public TaggedHospitalLocationArray[] arrays;

        public TaggedHospitalLocationArrays() { }

        public TaggedHospitalLocationArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedHospitalLocationArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedHospitalLocationArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedHospitalLocationArray((string)t.GetKey(i), (HospitalLocation[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedHospitalLocationArray((string)t.GetKey(i), (HospitalLocation)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
