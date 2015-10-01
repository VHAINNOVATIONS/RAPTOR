using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedConsultArrays : AbstractArrayTO
    {
        public TaggedConsultArray[] arrays;

        public TaggedConsultArrays() { }

        public TaggedConsultArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedConsultArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedConsultArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedConsultArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedConsultArray((string)t.GetKey(i), (Consult[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedConsultArray((string)t.GetKey(i), (Consult)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
