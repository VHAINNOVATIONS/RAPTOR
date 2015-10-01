using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedMedicationArrays : AbstractArrayTO
    {
        public TaggedMedicationArray[] arrays;

        public TaggedMedicationArrays() { }

        public TaggedMedicationArrays(IndexedHashtable t)
        {
            if (t== null || t.Count == 0)
            {
                return;
            }
            arrays = new TaggedMedicationArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedMedicationArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedMedicationArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    IList<Medication> meds = ((System.Collections.Hashtable)t.GetValue(i))["meds"] as IList<Medication>;
                    if (meds == null || meds.Count == 0)
                    {
                        arrays[i] = new TaggedMedicationArray((string)t.GetKey(i));
                    }
                    else
                    {
                        Medication[] ary = new Medication[meds.Count];
                        meds.CopyTo(ary, 0);
                        arrays[i] = new TaggedMedicationArray((string)t.GetKey(i), ary);
                    }
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedMedicationArray((string)t.GetKey(i), (Medication[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedMedicationArray((string)t.GetKey(i), (Medication)t.GetValue(i));
                }
            }
            count = t.Count;
        }

        public void add(string siteId, IList<Medication> meds)
        {
            if (arrays == null || arrays.Length == 0)
            {
                arrays = new TaggedMedicationArray[1];
            }
            else
            {
                Array.Resize<TaggedMedicationArray>(ref arrays, arrays.Length + 1);
            }

            arrays[arrays.Length - 1] = new TaggedMedicationArray(siteId, ((List<Medication>)meds).ToArray());
        }
    }
}
