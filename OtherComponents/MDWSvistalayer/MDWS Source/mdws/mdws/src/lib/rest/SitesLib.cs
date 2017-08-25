using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws.rest
{
    public class SitesLib
    {
        MySession _mySession;

        public SitesLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public RegionArray getVHA()
        {
            return new RegionArray(_mySession.SiteTable.Regions);
        }

    }
}