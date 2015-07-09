using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class StateTO : AbstractTO
    {
        public string name;
        public string abbr;
        public string fips;
        public SiteArray sites;
        public CityArray cities;

        public StateTO() { }

        public StateTO(State mdoState)
        {
            this.name = mdoState.Name;
            this.abbr = mdoState.Abbr;
            this.fips = mdoState.Fips;
            if (mdoState.Sites != null)
            {
                for (int i = 0; i < mdoState.Sites.Count; i++)
                {
                    this.sites = new SiteArray(mdoState.Sites);
                }
            }
            if (mdoState.Cities != null)
            {
                for (int i = 0; i < mdoState.Cities.Count; i++)
                {
                    this.cities = new CityArray(mdoState.Cities);
                }
            }
        }
    }
}
