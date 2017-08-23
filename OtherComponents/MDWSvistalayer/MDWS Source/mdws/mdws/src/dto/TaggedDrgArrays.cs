using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedDrgArrays : AbstractArrayTO
    {
        public TaggedDrgArray[] arrays;

        public TaggedDrgArrays() { }

        public TaggedDrgArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedDrgArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (MdwsUtils.isException(t.GetValue(i)))
                {
                    fault = new FaultTO((Exception)t.GetValue(i));
                }
                else if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedDrgArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedDrgArray((string)t.GetKey(i), (Drg[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedDrgArray((string)t.GetKey(i), (Drg)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
