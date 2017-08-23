using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedVisitArrays : AbstractArrayTO
    {
        public TaggedVisitArray[] arrays;

        public TaggedVisitArrays() { }

        public TaggedVisitArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedVisitArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedVisitArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedVisitArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedVisitArray((string)t.GetKey(i), (Visit[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedVisitArray((string)t.GetKey(i), (Visit)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
