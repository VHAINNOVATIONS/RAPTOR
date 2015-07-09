using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPersonArrays : AbstractArrayTO
    {
        public TaggedPersonArray[] arrays;

        public TaggedPersonArrays() { }

        public TaggedPersonArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            if (t.Count == 1 && MdwsUtils.isException(t.GetValue(0)))
            {
                fault = new FaultTO((Exception)t.GetValue(0));
                return;
            }
            arrays = new TaggedPersonArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                string ky = (string)t.GetKey(i);
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedPersonArray(ky);
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedPersonArray(ky, (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedPersonArray(ky, (Person[])t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsInstanceOfType(new List<Person>()))
                {
                    arrays[i] = new TaggedPersonArray(ky, (List<Person>)t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedPersonArray(ky, (Person)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
