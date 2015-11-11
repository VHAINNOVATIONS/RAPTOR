using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class SiteArray : AbstractArrayTO
    {
        public SiteTO[] sites;

        public SiteArray() { }

        public SiteArray(Site[] mdoSites)
        {
            setProps(mdoSites);
        }

        public SiteArray(ArrayList lst)
        {
            setProps((Site[])lst.ToArray(typeof(Site)));    
        }

        public SiteArray(List<Site> sites) {
            count = sites.Count;
            List<SiteTO> siteToList = new List<SiteTO>();
            foreach(Site site in sites) 
            {
                siteToList.Add(new SiteTO(site));
            }

            count = sites.Count;
            this.sites = siteToList.ToArray();
        }

        private void setProps(Site[] mdoSites)
        {
            if (mdoSites == null)
            {
                return;
            }
            ArrayList al = new ArrayList(mdoSites.Length);
            for (int i = 0; i < mdoSites.Length; i++)
            {
                if (mdoSites[i] != null)
                {
                    al.Add(new SiteTO(mdoSites[i]));
                }
            }
            sites = (SiteTO[])al.ToArray(typeof(SiteTO));
            count = sites.Length;
        }

        public SiteArray(SortedList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                count = 0;
                return;
            }
            ArrayList al = new ArrayList(lst.Count);
            IDictionaryEnumerator e = lst.GetEnumerator();
            while (e.MoveNext())
            {
                Site s = (Site)e.Value;
                if (s != null)
                {
                    al.Add(new SiteTO(s));
                }
            }
            sites = (SiteTO[])al.ToArray(typeof(SiteTO));
            count = sites.Length;
        }
    }
}
