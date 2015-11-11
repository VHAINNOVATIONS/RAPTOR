using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedHealthSummaryArray : AbstractArrayTO
    {
        public HealthSummaryTO[] healthSummaries;

        public TaggedHealthSummaryArray() { }

#if false
        public TaggedHealthSummaryArray(HealthSummary[] mdo)
        {
            Init(mdo);
        }
        
#endif
        public TaggedHealthSummaryArray(IndexedHashtable mdo)
        {
            Init(mdo);
        }

        public void Init(IndexedHashtable mdo)
        {
            if (mdo == null)
            {
                return;
            }
            healthSummaries = new HealthSummaryTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                healthSummaries[i] = new HealthSummaryTO((HealthSummary)mdo.GetValue(i),(string)mdo.GetKey(i));
            }
            count = mdo.Count;
        }

#if false
        public void Init(HealthSummary[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            healthSummaries = new HealthSummaryTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                healthSummaries[i] = new HealthSummaryTO(mdo[i]);
            }
            count = mdo.Length;
        } 
#endif
    }
}
