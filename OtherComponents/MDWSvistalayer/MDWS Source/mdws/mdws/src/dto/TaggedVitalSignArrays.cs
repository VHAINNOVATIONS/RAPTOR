using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedVitalSignArrays : AbstractArrayTO
    {
        public TaggedVitalSignArray[] arrays;

        public TaggedVitalSignArrays() { }

        public TaggedVitalSignArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedVitalSignArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i), (VitalSign[])t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    IList<VitalSignSet> temp = ((System.Collections.Hashtable)t.GetValue(i))["vitals"] as IList<VitalSignSet>;
                    if (temp == null || temp.Count == 0)
                    {
                        arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i));
                    }
                    else
                    {
                        VitalSignSet[] ary = new VitalSignSet[temp.Count];
                        temp.CopyTo(ary, 0);
                        arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i), ary);
                    }

                }
                else
                {
                    arrays[i] = new TaggedVitalSignArray((string)t.GetKey(i), (VitalSign)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
