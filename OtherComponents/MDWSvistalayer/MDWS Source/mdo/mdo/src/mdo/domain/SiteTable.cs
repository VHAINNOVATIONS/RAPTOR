using System;
using System.Xml;
using System.Collections;
using System.Collections.Generic;
using gov.va.medora.utils;

using System.IO;

namespace gov.va.medora.mdo
{
    public class SiteTable
    {
        SortedList regions;
        SortedList sites;
        ArrayList sources;
        SortedList states;
        SortedList cities;
        SortedList visnsByState;

        public SiteTable(String filepath)
        {
            regions = new SortedList();
            sites = new SortedList();
            sources = new ArrayList();
            parse(filepath);
        }

        public SortedList Regions
        {
            get
            {
                return regions;
            }
        }

        public SortedList Sites
        {
            get
            {
                return sites;
            }
        }

        public ArrayList Sources
        {
            get
            {
                return sources;
            }
        }

        public Site getSite(String siteId)
        {
            return (Site)sites[siteId];
        }

        public Site[] getSites(string sitelist)
        {
            string[] sitecodes = StringUtils.split(sitelist, StringUtils.COMMA);
            sitecodes = StringUtils.trimArray(sitecodes);
            if (sitecodes.Length == 0)
            {
                return null;
            }
            Site[] sites = new Site[sitecodes.Length];
            for (int i = 0; i < sitecodes.Length; i++)
            {
                sites[i] = getSite(sitecodes[i]);
            }
            return sites;
        }

        public Site[] getSites(SiteId[] siteIds)
        {
            if (siteIds == null || siteIds.Length == 0)
            {
                return null;
            }
            Site[] sites = new Site[siteIds.Length];
            for (int i = 0; i < siteIds.Length; i++)
            {
                sites[i] = getSite(siteIds[i].Id);
            }
            return sites;
        }

        public Region getRegion(String regionId)
        {
            return (Region)regions[Convert.ToInt32(regionId)];
        }

        public SortedList States
        {
            get { return states; }
        }

        public State getState(string abbr)
        {
            if (states == null)
            {
                return null;
            }
            foreach (DictionaryEntry de in states)
            {
                State s = (State)de.Value;
                if (s.Abbr == abbr)
                {
                    return s;
                }
            }
            throw new Exception("No such state: " + abbr);
        }

        private void parse(String filepath)
        {
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("No source for site table");
            }

            Region currentRegion = null;
            ArrayList currentSites = null;
            Site currentSite = null;
            DataSource dataSource = null;
            ArrayList currentSources = null;
#if DEBUG
            string currentDirStr = Directory.GetCurrentDirectory();
#endif

            XmlReader reader = null;
            try
            {
                reader = new XmlTextReader(filepath);
                while (reader.Read())
                {
                    switch ((int)reader.NodeType)
                    {
                        case (int)XmlNodeType.Element:
                            String name = reader.Name;
                            if (name == "VhaVisn")
                            {
                                currentRegion = new Region();
                                currentRegion.Id = Convert.ToInt32(reader.GetAttribute("ID"));
                                currentRegion.Name = reader.GetAttribute("name");
                                currentSites = new ArrayList();
                            }
                            else if (name == "VhaSite")
                            {
                                currentSite = new Site();
                                currentSite.Id = reader.GetAttribute("ID");
                                currentSite.Name = reader.GetAttribute("name");
                                String displayName = reader.GetAttribute("displayname");
                                if (displayName == null)
                                {
                                    displayName = currentSite.Name;
                                }
                                currentSite.DisplayName = displayName;
                                currentSite.Moniker = reader.GetAttribute("moniker");
                                currentSite.RegionId = Convert.ToString(currentRegion.Id);
                                currentSources = new ArrayList();
                                currentSites.Add(currentSite);
                            }
                            else if (name == "DataSource")
                            {
                                dataSource = new DataSource();
                                KeyValuePair<string, string> kvp =
                                    new KeyValuePair<string, string>(currentSite.Id, currentSite.Name);
                                dataSource.SiteId = new SiteId(kvp.Key, kvp.Value);
                                dataSource.Modality = reader.GetAttribute("modality");
                                dataSource.Protocol = reader.GetAttribute("protocol");
                                dataSource.Provider = reader.GetAttribute("source");
                                dataSource.Status = reader.GetAttribute("status");
                                String sTmp = reader.GetAttribute("port");
                                if (String.IsNullOrEmpty(sTmp)) // StringUtils.isEmpty(sTmp))
                                {
                                    if (String.Equals(dataSource.Protocol, "VISTA", StringComparison.CurrentCultureIgnoreCase))
                                    {
                                        dataSource.Port = 9200;
                                    }
                                    else if (String.Equals(dataSource.Protocol, "HL7", StringComparison.CurrentCultureIgnoreCase))
                                    {
                                        dataSource.Port = 5000;
                                    }
                                }
                                else
                                {
                                    dataSource.Port = Convert.ToInt32(sTmp);
                                }
                                sTmp = reader.GetAttribute("provider");
                                if (sTmp != null)
                                {
                                    dataSource.Provider = sTmp;
                                }
                                sTmp = reader.GetAttribute("description");
                                if (sTmp != null)
                                {
                                    dataSource.Description = sTmp;
                                }
                                sTmp = reader.GetAttribute("vendor");
                                if (sTmp != null)
                                {
                                    dataSource.Vendor = sTmp;
                                }
                                sTmp = reader.GetAttribute("version");
                                if (sTmp != null)
                                {
                                    dataSource.Version = sTmp;
                                }
                                sTmp = reader.GetAttribute("context");
                                if (sTmp != null)
                                {
                                    dataSource.Context = sTmp;
                                }
                                sTmp = reader.GetAttribute("connectionString");
                                if (!String.IsNullOrEmpty(sTmp))
                                {
                                    dataSource.ConnectionString = sTmp;
                                }
                                sTmp = reader.GetAttribute("test");
	                            if (!String.IsNullOrEmpty(sTmp))
	                            {
	                                dataSource.IsTestSource = true;
	                            }

                                currentSources.Add(dataSource);
                                sources.Add(dataSource);
                            }
                            break;
                        case (int)XmlNodeType.EndElement:
                            name = reader.Name;
                            if (name == "VhaVisn")
                            {
                                currentRegion.Sites = currentSites;
                                regions.Add(currentRegion.Id, currentRegion);
                            }
                            else if (name == "VhaSite")
                            {
                                currentSite.Sources = (DataSource[])currentSources.ToArray(typeof(DataSource));
                                sites.Add(currentSite.Id, currentSite);
                            }
                            break;
                    }
                }
            }
            catch (Exception) { /* do nothing */ }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
            }
        }

        public void parseStateCityFile(string filepath)
        {
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("No source for state/city file");
            }

            states = new SortedList();

            State currentState = null;
            SortedList currentCities = null;
            City currentCity = null;
            ArrayList currentSites = null;
            Site currentSite = null;

            XmlReader reader = null;
            try
            {
                reader = new XmlTextReader(filepath);
                while (reader.Read())
                {
                    switch ((int)reader.NodeType)
                    {
                        case (int)XmlNodeType.Element:
                            String name = reader.Name;
                            if (name == "state")
                            {
                                currentState = new State();
                                currentState.Name = reader.GetAttribute("name");
                                currentState.Abbr = reader.GetAttribute("abbr");
                                currentCities = new SortedList();
                            }
                            else if (name == "city")
                            {
                                currentCity = new City();
                                currentCity.Name = reader.GetAttribute("name");
                                currentCity.State = currentState.Abbr;
                                currentCities.Add(currentCity.Name, currentCity);
                                currentSites = new ArrayList();
                            }
                            else if (name == "site")
                            {
                                currentSite = new Site();
                                currentSite.Name = reader.GetAttribute("name");
                                currentSite.City = currentCity.Name;
                                currentSite.State = currentState.Abbr;
                                currentSite.Id = reader.GetAttribute("sitecode");
                                currentSite.SiteType = reader.GetAttribute("type");
                                currentSites.Add(currentSite);
                            }
                            break;
                        case (int)XmlNodeType.EndElement:
                            name = reader.Name;
                            if (name == "state")
                            {
                                currentState.Cities = currentCities;
                                states.Add(currentState.Name, currentState);
                            }
                            else if (name == "city")
                            {
                                currentCity.Sites = (Site[])currentSites.ToArray(typeof(Site));
                            }
                            else if (name == "site")
                            {
                            }
                            break;
                    }
                }
            }
            catch (Exception) { /* do nothing */ }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
            }
        }

        public void parseStateFile(string filepath)
        {
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("No source for state file");
            }

            states = new SortedList();

            State currentState = null;
            SortedList currentSites = null;
            Site currentSite = null;
            ArrayList currentClinics = null;
            Site currentClinic = null;

            XmlReader reader = null;

            try
            {
                reader = new XmlTextReader(filepath);
                while (reader.Read())
                {
                    switch ((int)reader.NodeType)
                    {
                        case (int)XmlNodeType.Element:
                            String name = reader.Name;
                            if (name == "state")
                            {
                                currentState = new State();
                                currentState.Name = reader.GetAttribute("name");
                                currentState.Abbr = reader.GetAttribute("abbr");
                                currentSites = new SortedList();
                            }
                            else if (name == "vamc")
                            {
                                currentSite = new Site();
                                currentSite.Id = reader.GetAttribute("sitecode");
                                currentSite.Name = reader.GetAttribute("name");
                                currentSite.City = reader.GetAttribute("city");
                                currentSite.SystemName = reader.GetAttribute("system");
                                currentSites.Add(currentSite.Name, currentSite);
                                currentClinics = new ArrayList();
                            }
                            else if (name == "clinic")
                            {
                                currentClinic = new Site();
                                currentClinic.Name = reader.GetAttribute("name");
                                currentClinic.City = reader.GetAttribute("city");
                                currentClinic.State = reader.GetAttribute("state");
                                //if (StringUtils.isEmpty(currentClinic.State))
                                if (String.IsNullOrEmpty(currentClinic.State))
                                {
                                    currentClinic.State = currentState.Abbr;
                                }
                                currentClinic.ParentSiteId = currentSite.Id;
                                currentClinics.Add(currentClinic);
                            }
                            break;
                        case (int)XmlNodeType.EndElement:
                            name = reader.Name;
                            if (name == "state")
                            {
                                currentState.Sites = currentSites;
                                states.Add(currentState.Name, currentState);
                            }
                            else if (name == "vamc")
                            {
                                currentSite.ChildSites = (Site[])currentClinics.ToArray(typeof(Site));
                            }
                            else if (name == "clinic")
                            {
                            }
                            break;
                    }
                }
            }
            catch (Exception) { /* do nothing */ }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
            }

        }

        public SortedList VisnsByState
        {
            get{ return visnsByState; }
        }

        public void parseVisnsByState(string filepath)
        {
            if (String.IsNullOrEmpty(filepath))
            {
                throw new ArgumentNullException("No source for state file");
            }

            visnsByState = new SortedList();

            XmlReader reader = null;
            try
            {
                reader = new XmlTextReader(filepath);
                while (reader.Read())
                {
                    switch ((int)reader.NodeType)
                    {
                        case (int)XmlNodeType.Element:
                            String name = reader.Name;
                            if (name == "state")
                            {
                                State state = new State();
                                state.Name = reader.GetAttribute("name");
                                state.Abbr = reader.GetAttribute("abbr");
                                string visns = reader.GetAttribute("visns");
                                state.VisnIds = StringUtils.split(visns, StringUtils.COMMA);
                                visnsByState.Add(state.Abbr, state);
                            }
                            break;
                    }
                }
            }
            catch (Exception) { /* do nothing */ }
            finally
            {
                if (reader != null)
                {
                    reader.Close();
                }
            }
        }
    }
}