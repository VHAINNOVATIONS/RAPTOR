using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedOrderArrays : AbstractArrayTO
    {
        public TaggedOrderArray[] arrays;

        public TaggedOrderArrays() { /* Empty Constructor */ }

        public TaggedOrderArrays(IndexedHashtable t)
        {
            this.count = 0;
            if(t == null || t.Count == 0)
            {
                return;
            }

            this.count = t.Count;
            arrays = new TaggedOrderArray[t.Count];

            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedOrderArray();
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedOrderArray();
                    arrays[i].fault = new FaultTO((Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() != typeof(Order[]))
                {
                    arrays[i] = new TaggedOrderArray((string)t.GetKey(i), null);
                }
                else
                {
                    arrays[i] = new TaggedOrderArray((string)t.GetKey(i), (Order[])t.GetValue(i));
                }
            }
        }
    }
}