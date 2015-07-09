using System;
using System.Web;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text.RegularExpressions;
using gov.va.medora.mdo;
using gov.va.medora.mdo.api;
using gov.va.medora.utils;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class SitesLib
    {
        MySession mySession;

        public SitesLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public RegionArray getVHA()
        {
            return new RegionArray(mySession.SiteTable.Regions);
        }

        public RegionTO getVISN(string regionId)
        {
            RegionTO region = new RegionTO();
            if (String.IsNullOrEmpty(regionId))
            {
                region.fault = new FaultTO("No region specified", "Need to supply region ID");
                return region;
            }
            int intRegionId = 0;
            try
            {
                intRegionId = Convert.ToInt32(regionId);
            }
            catch (Exception exc)
            {
                region.fault = new FaultTO(exc, "Need to supply a numeric regiod ID");
            }
            if(region.fault != null)
            {
                return region;
            }

            foreach (Region r in mySession.SiteTable.Regions.Values)
            {
                if (r.Id == intRegionId)
                {
                    return new RegionTO(r);
                }
            }

            region.fault = new FaultTO("No region with specified region ID", "Supply a valid region ID");
            return region;
        }

        public StateArray getVhaByStates()
        {
            if (mySession.SiteTable.States == null || mySession.SiteTable.States.Count == 0)
            {
                string filepath = mySession.MdwsConfiguration.ResourcesPath + MdwsConstants.STATES_FILE_NAME;
                mySession.SiteTable.parseStateCityFile(filepath);
            }
            return new StateArray(mySession.SiteTable.States);
        }

        public SiteTO getSite(string sitecode)
        {
            SiteTO result = new SiteTO();

            if (sitecode == "")
            {
                result.fault = new FaultTO("No sitecode!");
            }
            else if (sitecode.Length != 3 || !StringUtils.isNumeric(sitecode))
            {
                result.fault = new FaultTO("Invalid sitecode");
            }
            if (result.fault != null)
            {
                return result;
            }

            Site s = mySession.SiteTable.getSite(sitecode);
            if (s == null)
            {
                result.fault = new FaultTO("No such site!");
                return result;
            }
            return new SiteTO(s);
        }

        public StateArray getStates()
        {
            StateArray result = new StateArray();
            try
            {
                gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao dao =
                    new gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao(mySession.MdwsConfiguration.SqlConnectionString);
                State[] states = dao.getStates();
                result = new StateArray(states);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public ZipcodeTO[] getCitiesInState(string stateAbbr)
        {
            gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao dao =
                new gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao(mySession.MdwsConfiguration.SqlConnectionString);
            ZipcodeTO[] result = new ZipcodeTO[1];
            result[0] = new ZipcodeTO();

            if (String.IsNullOrEmpty(stateAbbr))
            {
                result[0].fault = new FaultTO("Missing state abbreviation");
            }
            else if (stateAbbr.Length != 2)
            {
                result[0].fault = new FaultTO("Invalid state abbreviation", "Please supply a valid 2 letter abbreviation");
            }
            if (result[0].fault != null)
            {
                return result;
            }

            try
            {
                Zipcode[] zips = dao.getCitiesInState(stateAbbr);
                
                IndexedHashtable t = new IndexedHashtable();
                for (int i = 0; i < zips.Length; i++)
                {
                    if (!t.ContainsKey(zips[i].City))
                    {
                        t.Add(zips[i].City, zips[i]);
                    }
                }
                result = new ZipcodeTO[t.Count];
                for (int i = 0; i < t.Count; i++)
                {
                    result[i] = new ZipcodeTO((Zipcode)t.GetValue(i));
                }
            }
            catch (Exception exc)
            {
                result[0].fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO getZipcodeForCity(string city, string stateAbbr)
        {
            TextTO result = new TextTO();
            if (city == "")
            {
                result.fault = new FaultTO("Missing city");
            }
            else if (stateAbbr == "")
            {
                result.fault = new FaultTO("Missing stateAbbr");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao dao =
                    new gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao(mySession.MdwsConfiguration.SqlConnectionString);
                string zip = dao.getZipcode(city, stateAbbr);
                result = new TextTO(zip);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public ClosestFacilityTO getNearestFacility(string city, string stateAbbr)
        {
            ClosestFacilityTO result = new ClosestFacilityTO();
            if (city == "")
            {
                result.fault = new FaultTO("Missing city");
            }
            else if (!State.isValidAbbr(stateAbbr))
            {
                result.fault = new FaultTO("Invalid stateAbbr");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                TextTO zipcode = getZipcodeForCity(city, stateAbbr);
                if (zipcode.fault != null)
                {
                    result.fault = zipcode.fault;
                    return result;
                }
                result = getNearestFacility(zipcode.text);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public string[] getVisnsForState(string stateAbbr)
        {
            if (mySession.SiteTable.VisnsByState == null)
            {
                mySession.SiteTable.parseVisnsByState(mySession.MdwsConfiguration.ResourcesPath + MdwsConstants.VISNS_BY_STATE_FILE_NAME);
            }
            return ((State)mySession.SiteTable.VisnsByState[stateAbbr.ToUpper()]).VisnIds;
        }

        public TaggedTextArray matchCityAndState(string city, string stateAbbr)
        {
            TaggedTextArray result = new TaggedTextArray();
            if (city == "")
            {
                result.fault = new FaultTO("Missing city");
            }
            else if (stateAbbr == "")
            {
                result.fault = new FaultTO("Missing stateAbbr");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                SitesApi api = new SitesApi();
                string[] s = api.matchCityAndState(city, stateAbbr, mySession.MdwsConfiguration.SqlConnectionString);
                result.results = new TaggedText[s.Length];
                for (int i = 0; i < s.Length; i++)
                {
                    string[] parts = StringUtils.split(s[i],StringUtils.CARET);
                    result.results[i] = new TaggedText(parts[0], parts[1]);
                }
                result.count = result.results.Length;
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public SiteArray getSitesForCounty(string fips)
        {
            SiteArray result = new SiteArray();
            if (fips == "")
            {
                result.fault = new FaultTO("Missing fips");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                SitesApi api = new SitesApi();
                Site[] sites = api.getClosestFacilities(fips, mySession.MdwsConfiguration.SqlConnectionString);
                result = new SiteArray(sites);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public ClosestFacilityTO getNearestFacility(string zipcode)
        {
            ClosestFacilityTO result = new ClosestFacilityTO();
            if (zipcode == "")
            {
                result.fault = new FaultTO("Missing zipcode");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                SitesApi api = new SitesApi();
                ClosestFacility fac = api.getNearestFacility(zipcode, mySession.MdwsConfiguration.SqlConnectionString);
                result = new ClosestFacilityTO(fac);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public GeographicLocationArray getGeographicLocations(string zipcode)
        {
            GeographicLocationArray result = new GeographicLocationArray();
            if (zipcode.Length != 5 || !StringUtils.isNumeric(zipcode))
            {
                result.fault = new FaultTO("Invalid zipcode: must be 5 numeric chars");
                return result;
            }

            try
            {
                gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao dao =
                    new gov.va.medora.mdo.dao.sql.zipcodeDB.ZipcodeDao(mySession.MdwsConfiguration.SqlConnectionString);
                GeographicLocation[] locations = dao.getGeographicLocations(zipcode);
                result = new GeographicLocationArray(locations);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public SiteTO addSite(string id, string name, string datasource, string port, string modality, string protocol, string region)
        {
            SiteTO result = new SiteTO();
            Site site = new Site();
            DataSource source = new DataSource();
            int iPort = 0;
            int iRegion = 0;

            if (!mySession.MdwsConfiguration.IsProduction)
            {
                result.fault = new FaultTO("You may not add data sources to non-production MDWS installations");
            }
            else if (String.IsNullOrEmpty(id) || String.IsNullOrEmpty(name) || String.IsNullOrEmpty(datasource) ||
                String.IsNullOrEmpty(port) || String.IsNullOrEmpty(modality) || String.IsNullOrEmpty(protocol) ||
                String.IsNullOrEmpty(region))
            {
                result.fault = new FaultTO("Must supply all parameters");
            }
            else if (mySession.SiteTable.Sites.ContainsKey(id))
            {
                result.fault = new FaultTO("That site id is in use", "Choose a different site id");
            }
            else if (!Int32.TryParse(port, out iPort))
            {
                result.fault = new FaultTO("Non-numeric port", "Provide a numeric value for the port");
            }
            else if (!Int32.TryParse(region, out iRegion))
            {
                result.fault = new FaultTO("Non-numeric region", "Provide a numeric value for the region");
            }
            else if (modality != "HIS")
            {
                result.fault = new FaultTO("Only HIS modality currently supported", "Use 'HIS' as your modality");
            }
            else if (protocol != "VISTA")
            {
                result.fault = new FaultTO("Only VISTA protocol currently supported", "Use 'VISTA' as your protocol");
            }

            if(result.fault != null)
            {
                return result;
            }

            source.Port = iPort;
            source.Modality = modality;
            source.Protocol = protocol;
            source.Provider = datasource;
            source.SiteId = new SiteId(id, name);

            site.Sources = new DataSource[1];
            site.Sources[0] = source;
            site.RegionId = region;
            site.Name = name;
            site.Id = id;
            
            if(!mySession.SiteTable.Regions.ContainsKey(iRegion))
            {
                Region r = new Region();
                r.Id = iRegion;
                r.Name = "Region " + region;
                r.Sites = new ArrayList();
                mySession.SiteTable.Regions.Add(iRegion, r);
            }
            ((Region)mySession.SiteTable.Regions[iRegion]).Sites.Add(site);
            mySession.SiteTable.Sites.Add(id, site);
            mySession.SiteTable.Sources.Add(site.Sources[0]);
            result = new SiteTO(site);
            return result;
        }

        public SiteArray getConnectedSites()
        {
            if (mySession.ConnectionSet == null || mySession.ConnectionSet.Count == 0)
            {
                return null;
            }
            SiteArray result = new SiteArray();
            result.sites = new SiteTO[mySession.ConnectionSet.Count];
            result.count = mySession.ConnectionSet.Count;
            int i = 0;
            foreach (KeyValuePair<string, gov.va.medora.mdo.dao.AbstractConnection> cxn in mySession.ConnectionSet.Connections)
            {
                result.sites[i] = new SiteTO();
                result.sites[i].displayName = cxn.Value.DataSource.SiteId.Name;
                i++;
            }
            return result;
        }
    }
}
