using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedProstheticClaimArrays : AbstractArrayTO
    {
        public TaggedProstheticClaimArray[] arrays;

        public TaggedProstheticClaimArrays() { }

        public TaggedProstheticClaimArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedProstheticClaimArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedProstheticClaimArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    arrays[i] = new TaggedProstheticClaimArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedProstheticClaimArray((string)t.GetKey(i), (ProstheticClaim[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedProstheticClaimArray((string)t.GetKey(i), (ProstheticClaim)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
