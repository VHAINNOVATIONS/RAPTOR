using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedChemHemRptArrays : AbstractArrayTO
    {
        public TaggedChemHemRptArray[] arrays;

        public TaggedChemHemRptArrays() { }

        public TaggedChemHemRptArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedChemHemRptArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    IList<ChemHemReport> temp = ((System.Collections.Hashtable)t.GetValue(i))["labs"] as IList<ChemHemReport>;
                    if (temp == null || temp.Count == 0)
                    {
                        arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i));
                    }
                    else
                    {
                        ChemHemReport [] ary = new ChemHemReport[temp.Count];
                        temp.CopyTo(ary, 0);
                        arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i), ary);
                    }

                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i), (ChemHemReport[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedChemHemRptArray((string)t.GetKey(i), (ChemHemReport)t.GetValue(i));
                }
            }
            count = t.Count;
        }

        internal void add(string sitecode, IList<LabReport> labReports)
        {
            //IList<ChemHemReport> chemHemRpts = new List<ChemHemReport>();
            //foreach (LabReport lab in labReports)
            //{
            //    if (lab.
            //}

            //if (arrays == null || arrays.Length == 0)
            //{
            //    arrays = new TaggedMedicationArray[1];
            //}
            //else
            //{
            //    Array.Resize<TaggedMedicationArray>(ref arrays, arrays.Length + 1);
            //}

            //arrays[arrays.Length - 1] = new TaggedMedicationArray(siteId, ((List<Medication>)meds).ToArray());
        }
    }
}
