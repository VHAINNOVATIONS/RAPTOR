using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedDemographicsRecordArrays : AbstractArrayTO
    {
        public TaggedDemographicsRecordArray[] arrays;

        public TaggedDemographicsRecordArrays() { }

        public TaggedDemographicsRecordArrays(IndexedHashtable t)
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
            arrays = new TaggedDemographicsRecordArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                string ky = (string)t.GetKey(i);
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedDemographicsRecordArray(ky);
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedDemographicsRecordArray(ky, (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedDemographicsRecordArray(ky, (DemographicsRecord[])t.GetValue(i));
                }
                else if (t.GetValue(i).GetType().IsInstanceOfType(new List<DemographicsRecord>()))
                {
                    arrays[i] = new TaggedDemographicsRecordArray(ky, (List<DemographicsRecord>)t.GetValue(i));
                }
                else
                {
                    //arrays[i] = new TaggedDemographicsRecordArray(ky, (DemographicsRecord)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}
