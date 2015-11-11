using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedAdtArrays : AbstractArrayTO
    {
        public TaggedAdtArray[] arrays;

        public TaggedAdtArrays() { }

        public TaggedAdtArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedAdtArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (MdwsUtils.isException(t.GetValue(i)))
                {
                    fault = new FaultTO((Exception)t.GetValue(i));
                }
                else if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedAdtArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedAdtArray((string)t.GetKey(i), (Adt[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedAdtArray((string)t.GetKey(i), (Adt)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
