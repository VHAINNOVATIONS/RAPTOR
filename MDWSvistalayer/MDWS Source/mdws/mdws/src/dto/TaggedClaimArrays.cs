using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedClaimArrays : AbstractArrayTO
    {
        public TaggedClaimArray[] arrays;

        public TaggedClaimArrays() { }

        public TaggedClaimArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedClaimArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedClaimArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    arrays[i] = new TaggedClaimArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedClaimArray((string)t.GetKey(i), (Claim[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedClaimArray((string)t.GetKey(i), (Claim)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
