using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class City
    {
        string name;
        string state;

        ArrayList sites;

        public City() { }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string State
        {
            get { return state; }
            set { state = value; }
        }

        public Site[] Sites
        {
            get { return (Site[])sites.ToArray(typeof(Site)); }
            set
            {
                sites = new ArrayList();
                for (int i = 0; i < ((Site[])value).Length; i++)
                {
                    sites.Add(((Site[])value)[i]);
                }
            }
        }

        public void addSite(Site site)
        {
            sites.Add(site);
        }
    }
}
