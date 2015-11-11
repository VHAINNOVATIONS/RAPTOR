using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using gov.va.medora.mdo;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class LocationLib
    {
        MySession mySession;

        public LocationLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedTextArray getSitesForStation()
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                List<SiteId> lst = HospitalLocation.getSitesForStation(mySession.ConnectionSet.BaseConnection);
                if (lst == null || lst.Count == 0)
                {
                    return null;
                }
                result.results = new TaggedText[lst.Count];
                for (int i = 0; i < lst.Count; i++)
                {
                    result.results[i] = new TaggedText(lst[i].Id, lst[i].Name);
                }
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public SiteArray getAllInstitutions()
        {
            SiteArray siteResults = new SiteArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                siteResults.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (siteResults.fault != null)
            {
                return siteResults;
            }

            try
            {
                List<Site> sites = HospitalLocation.getAllInstitutions(mySession.ConnectionSet.BaseConnection);
                siteResults = new SiteArray(sites);
            }
            catch (Exception e)
            {
                siteResults.fault = new FaultTO(e);
            }

            return siteResults;
        }

        public TaggedTextArray getClinicsByName(string name)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(name))
            {
                result.fault = new FaultTO("Empty clinic name");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                OrderedDictionary d = HospitalLocation.getClinicsByName(mySession.ConnectionSet.BaseConnection, name);
                result = new TaggedTextArray(d);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }
    }
}