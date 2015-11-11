using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class RegionTO : AbstractTO
    {
        public string name;
        public string id;
        public SiteArray sites;

        public RegionTO() { }

        public RegionTO(Region mdoRegion)
        {
            this.name = mdoRegion.Name;
            this.id = Convert.ToString(mdoRegion.Id);
            this.sites = new SiteArray(mdoRegion.Sites);
        }
    }
}
