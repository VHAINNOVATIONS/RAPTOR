using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.dao.sql.pssg;
using gov.va.medora.mdo.dao.sql.zipcodeDB;

namespace gov.va.medora.mdo.api
{
    public class SitesApi
    {
        public SitesApi() { }

        public string[] matchCityAndState(string city, string stateAbbr, string connectionString)
        {
            ZipcodeDao dao = new ZipcodeDao(connectionString);
            return dao.matchCityAndState(city, stateAbbr);
        }

        public Site[] getClosestFacilities(string fips, string connectionString)
        {
            PssgDao dao = new PssgDao(connectionString);
            return dao.getClosestFacilities(fips);
        }

        public ClosestFacility getNearestFacility(string zipcode, string connectionString)
        {
            PssgDao dao = new PssgDao(connectionString);
            return dao.getNearestFacility(zipcode);
        }
    }
}
