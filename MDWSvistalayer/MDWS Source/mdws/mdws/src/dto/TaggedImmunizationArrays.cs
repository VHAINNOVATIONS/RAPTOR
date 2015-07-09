using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedImmunizationArrays : AbstractArrayTO
    {
        public TaggedImmunizationArray[] arrays;

        public TaggedImmunizationArrays() { }

        public TaggedImmunizationArrays(IndexedHashtable t)
        {
            if (t == null || t.Count == 0)
            {
                return;
            }

            count = t.Count;
            arrays = new TaggedImmunizationArray[t.Count];

            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedImmunizationArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedImmunizationArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    arrays[i] = new TaggedImmunizationArray((string)t.GetKey(i), ((System.Collections.Hashtable)t.GetValue(i))["immunizations"] as IList<Immunization>);
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedImmunizationArray((string)t.GetKey(i), ((Immunization[])t.GetValue(i)).ToList());
                }
                else if (t.GetValue(i).GetType() == typeof(IList<Immunization>))
                {
                    arrays[i] = new TaggedImmunizationArray((string)t.GetKey(i), (IList<Immunization>)t.GetValue(i));
                }
            }
        }

        internal void add(string siteId, IList<Immunization> list)
        {
            if (arrays == null || arrays.Length == 0)
            {
                arrays = new TaggedImmunizationArray[1];
            }
            else
            {
                Array.Resize<TaggedImmunizationArray>(ref arrays, arrays.Length + 1);
            }

            arrays[arrays.Length - 1] = new TaggedImmunizationArray(siteId, list);
            count = arrays.Length;
        }
    }
}