using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class RegionArray : AbstractArrayTO
    {
        public RegionTO[] regions;

        public RegionArray() { }

        public RegionArray(Region[] mdoRegions)
        {
            if (mdoRegions == null || mdoRegions.Length == 0)
            {
                count = 0;
                return;
            }
            regions = new RegionTO[mdoRegions.Length];
            for (int i = 0; i < mdoRegions.Length; i++)
            {
                regions[i] = new RegionTO(mdoRegions[i]);
            }
            count = mdoRegions.Length;
        }

        public RegionArray(SortedList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                count = 0;
                return;
            }
            regions = new RegionTO[lst.Count];
            IDictionaryEnumerator e = lst.GetEnumerator();
            int i = 0;
            while (e.MoveNext())
            {
                regions[i++] = new RegionTO((Region)e.Value);
            }
            count = lst.Count;
        }
    }
}
