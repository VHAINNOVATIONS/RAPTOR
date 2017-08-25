using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedUserArrays : AbstractArrayTO
    {
        public TaggedUserArray[] arrays;

        public TaggedUserArrays() { }

        public TaggedUserArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedUserArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedUserArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedUserArray((string)t.GetKey(i), (User[])t.GetValue(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    fault = new FaultTO((Exception)t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedUserArray((string)t.GetKey(i), (User)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
