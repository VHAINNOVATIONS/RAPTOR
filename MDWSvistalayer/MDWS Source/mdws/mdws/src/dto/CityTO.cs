using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class CityTO : AbstractTO
    {
        public string name;
        public string state;
        public SiteArray sites;

        public CityTO() { }

        public CityTO(City mdo)
        {
            this.name = mdo.Name;
            this.state = mdo.State;
            this.sites = new SiteArray(mdo.Sites);
        }
    }
}
