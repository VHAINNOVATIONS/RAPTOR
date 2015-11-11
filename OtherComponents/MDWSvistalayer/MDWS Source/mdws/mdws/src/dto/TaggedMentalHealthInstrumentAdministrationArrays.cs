using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    /// <summary>
    /// 
    /// </summary>
    public class TaggedMentalHealthInstrumentAdministrationArrays : AbstractArrayTO
    {
        public TaggedMentalHealthInstrumentAdministrationArray[] arrays;

        public TaggedMentalHealthInstrumentAdministrationArrays() { }

        public TaggedMentalHealthInstrumentAdministrationArrays(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            arrays = new TaggedMentalHealthInstrumentAdministrationArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedMentalHealthInstrumentAdministrationArray((string)t.GetKey(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedMentalHealthInstrumentAdministrationArray((string)t.GetKey(i), (MentalHealthInstrumentAdministration[])t.GetValue(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    fault = new FaultTO((Exception)t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedMentalHealthInstrumentAdministrationArray((string)t.GetKey(i), (List<MentalHealthInstrumentAdministration>)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
