using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class StateSites : AbstractTO
    {
        public string name;
        public string abbr;
        public SiteArray sites;

        public StateSites() { }

        public StateSites(State mdoState)
        {
            this.name = mdoState.Name;
            this.abbr = mdoState.Abbr;
            SortedList lst = new SortedList();
            foreach (DictionaryEntry de in mdoState.Sites)
            {
                Site s = (Site)de.Value;
                if (s.ChildSites != null)
                {
                    for (int i = 0; i < s.ChildSites.Length; i++)
                    {
                        lst.Add(s.ChildSites[i].Name, s.ChildSites[i]);
                    }
                }
                Site clone = new Site();
                clone.Id = s.Id;
                clone.Name = s.Name;
                clone.State = s.State;
                clone.City = s.City;
                clone.DisplayName = s.DisplayName;
                clone.ParentSiteId = s.ParentSiteId;
                clone.RegionId = s.RegionId;
                lst.Add(clone.Name, clone);
            }
            this.sites = new SiteArray(lst);
        }
    }
}
