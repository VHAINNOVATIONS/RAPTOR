using System;
using System.Web;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.api;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.dao.oracle;
using gov.va.medora.mdo.dao.sql.cdw;

namespace gov.va.medora.mdws
{
    public class PatientLib
    {
        MySession mySession;

        public PatientLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        // this function makes a call to cdwLookup - if results are returned, it replaces each PatientTO with an empty object so as not to return PII
        public PatientArray cdwLookupSlim(string password, string pid)
        {
            PatientArray result = cdwLookup(password, pid);

            if (result.fault != null)
            {
                return result;
            }
            else
            {
                for (int i = 0; i < result.patients.Length; i++)
                {
                    result.patients[i] = new PatientTO();
                }
            }

            return result;
        }

        // this function makes a call to cdwLookup - if results are returned, it replaces each PatientTO with an empty object so as not to return PII
        public PatientArray cdwLookupSlimWithAccount(string domain, string username, string password, string pid)
        {
            PatientArray result = cdwLookup(domain, username, password, pid);

            if (result.fault != null)
            {
                return result;
            }
            else
            {
                for (int i = 0; i < result.patients.Length; i++)
                {
                    result.patients[i] = new PatientTO();
                }
            }

            return result;
        }

        public PatientArray cdwLookup(string password, string pid)
        {
            return cdwLookup(mySession.MdwsConfiguration.CdwSqlConfig.RunasUser.Domain,
                mySession.MdwsConfiguration.CdwSqlConfig.RunasUser.UserName,
                mySession.MdwsConfiguration.CdwSqlConfig.RunasUser.Pwd,
                pid);
        }

        public PatientArray cdwLookup(string domain, string username, string password, string pid)
        {
            PatientArray result = new PatientArray();

            if (String.IsNullOrEmpty(pid))
            {
                result.fault = new FaultTO("Missing patient ID");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                using (CdwConnection cdwCxn = new CdwConnection(
                    new DataSource() { ConnectionString = mySession.MdwsConfiguration.CdwSqlConfig.ConnectionString },
                    new User() { Domain = domain, UserName = username, Pwd = password }))
                {
                    result = new PatientArray(new CdwPatientDao(cdwCxn).match(pid));
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TaggedTextArray getCorrespondingIds(string sitecode, string patientId, string idType)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (mySession == null || mySession.ConnectionSet == null || mySession.ConnectionSet.BaseConnection == null ||
                !mySession.ConnectionSet.HasBaseConnection || !mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("No connections", "Need to login?");
            }
            else if (!String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Lookup by a specific sitecode is not currently supported - please leave this field empty and MDWS will query the base connection");
            }
            else if (String.IsNullOrEmpty(patientId))
            {
                result.fault = new FaultTO("Missing patient ID");
            }
            else if (String.IsNullOrEmpty(idType))
            {
                result.fault = new FaultTO("Missing ID type");
            }
            else if (!String.Equals("DFN", idType, StringComparison.CurrentCultureIgnoreCase)
                && !String.Equals("ICN", idType, StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO("Lookup by " + idType + " is not currently supported");
            }

            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(sitecode))
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                if (String.Equals("ICN", idType, StringComparison.CurrentCultureIgnoreCase))
                {
                    PatientApi patientApi = new PatientApi();
                    string localPid = patientApi.getLocalPid(mySession.ConnectionSet.BaseConnection, patientId);
                    result = new TaggedTextArray(patientApi.getTreatingFacilityIds(mySession.ConnectionSet.BaseConnection, localPid));
                }
                else if (String.Equals("DFN", idType, StringComparison.CurrentCultureIgnoreCase))
                {
                    PatientApi patientApi = new PatientApi();
                    result = new TaggedTextArray(patientApi.getTreatingFacilityIds(mySession.ConnectionSet.BaseConnection, patientId));
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TaggedPatientArray match(string sitecode, string target)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (sitecode == "")
            {
                result.fault = new FaultTO("Missing sitecode");
            }
            if (result.fault != null)
            {
                return result;
            }
            return match(mySession.ConnectionSet.getConnection(sitecode), target);
        }

        internal TaggedPatientArray match(AbstractConnection cxn, string target)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            if (!cxn.IsConnected)
            {
                result = new TaggedPatientArray("Connection not open");
            }
            else if (cxn.DataSource.Uid == "")
            {
                result = new TaggedPatientArray("No user authorized for lookup");
            }
            else if (target == "")
            {
                result.fault = new FaultTO("Missing target");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.match(cxn, target);
                result = new TaggedPatientArray(cxn.DataSource.SiteId.Id, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        //internal TaggedPatientArrays matchLocal(Connection cxn, string target)
        //{
        //    TaggedPatientArrays result = new TaggedPatientArrays();
        //    TaggedPatientArray matches = match(cxn, target);
        //    if (matches.fault != null)
        //    {
        //        result.fault = matches.fault;
        //    }
        //    else
        //    {
        //        result.count = 1;
        //        result.arrays = new TaggedPatientArray[] { matches };
        //    }
        //    return result;
        //}

        public TaggedPatientArrays match(string target)
        {
            TaggedPatientArrays result = new TaggedPatientArrays();
            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(target))
            {
                result.fault = new FaultTO("Missing target");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi api = new PatientApi();
                IndexedHashtable t = api.match(mySession.ConnectionSet, target);
                return new TaggedPatientArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray matchByNameCityState(string name, string city, string stateAbbr)
        {
            return matchByNameCityState(mySession.ConnectionSet.BaseConnection, name, city, stateAbbr);
        }

        public TaggedPatientArray matchByNameCityState(string sitecode, string name, string city, string stateAbbr)
        {
            return matchByNameCityState(mySession.ConnectionSet.getConnection(sitecode), name, city, stateAbbr);
        }

        internal TaggedPatientArray matchByNameCityState(AbstractConnection cxn, string name, string city, string stateAbbr)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            if (name == "")
            {
                result.fault = new FaultTO("Missing name");
            }
            else if (city == "")
            {
                result.fault = new FaultTO("Missing city");
            }
            else if (stateAbbr == "")
            {
                result.fault = new FaultTO("Missing stateAbbr");
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
                PatientApi api = new PatientApi();
                Patient[] matches = api.matchByNameCityState(cxn, name, city, stateAbbr);
                result = new TaggedPatientArray(cxn.DataSource.SiteId.Id, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArrays matchByNameCityStateMS(string name, string city, string stateAbbr)
        {
            TaggedPatientArrays result = new TaggedPatientArrays();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (name == "")
            {
                result.fault = new FaultTO("Missing name");
            }
            else if (city == "")
            {
                result.fault = new FaultTO("Missing city");
            }
            else if (stateAbbr == "")
            {
                result.fault = new FaultTO("Missing stateAbbr");
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
                PatientApi api = new PatientApi();
                IndexedHashtable matches = api.matchByNameCityState(mySession.ConnectionSet, name, city, stateAbbr);
                result = new TaggedPatientArrays(matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray getPatientsByWard(string wardId)
        {
            return getPatientsByWard(null,wardId);
        }

        public TaggedPatientArray getPatientsByWard(string sitecode, string wardId)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (wardId == "")
            {
                result.fault = new FaultTO("Missing wardId");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.getPatientsByWard(cxn, wardId);
                result = new TaggedPatientArray(sitecode, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray getPatientsByClinic(string clinicId)
        {
            return getPatientsByClinic(null, clinicId, "", "");
        }

        public TaggedPatientArray getPatientsByClinic(string clinicId, string fromDate, string toDate)
        {
            return getPatientsByClinic("", clinicId, fromDate, toDate);
        }

        public TaggedPatientArray getPatientsByClinic(string sitecode, string clinicId, string fromDate, string toDate)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (clinicId == "")
            {
                result.fault = new FaultTO("Missing clinicId");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(fromDate))
            {
                fromDate = "T";
            }
            if (String.IsNullOrEmpty(toDate))
            {
                toDate = "T";
            }

            if (String.IsNullOrEmpty(sitecode))
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.getPatientsByClinic(cxn, clinicId, fromDate, toDate);
                result = new TaggedPatientArray(sitecode, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray getPatientsBySpecialty(string specialtyId)
        {
            return getPatientsBySpecialty(null, specialtyId);
        }

        public TaggedPatientArray getPatientsBySpecialty(string sitecode, string specialtyId)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (specialtyId == "")
            {
                result.fault = new FaultTO("Missing specialtyId");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.getPatientsBySpecialty(cxn, specialtyId);
                result = new TaggedPatientArray(sitecode, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray getPatientsByTeam(string teamId)
        {
            return getPatientsByTeam(null, teamId);
        }

        public TaggedPatientArray getPatientsByTeam(string sitecode, string teamId)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (teamId == "")
            {
                result.fault = new FaultTO("Missing teamId");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.getPatientsByTeam(cxn, teamId);
                result = new TaggedPatientArray(sitecode, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientArray getPatientsByProvider(string duz)
        {
            return getPatientsByProvider(null, duz);
        }

        public TaggedPatientArray getPatientsByProvider(string sitecode, string duz)
        {
            TaggedPatientArray result = new TaggedPatientArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (duz == "")
            {
                result.fault = new FaultTO("Missing duz");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi patientApi = new PatientApi();
                Patient[] matches = patientApi.getPatientsByProvider(cxn, duz);
                result = new TaggedPatientArray(sitecode, matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public PatientTO select(string localPid)
        {
            return select(null, localPid);
        }

        public PatientTO select(string sitecode, string localPid)
        {
            PatientTO result = new PatientTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (String.IsNullOrEmpty(localPid))
            {
                result.fault = new FaultTO("Missing local PID");
            }
            if (result.fault != null)
            {
                return result;
            }
            
            if (String.IsNullOrEmpty(sitecode))
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi api = new PatientApi();
                Patient p = api.select(cxn, localPid);
                result = new PatientTO(p);
                mySession.Patient = p;
                mySession.ConnectionSet.getConnection(sitecode).Pid = result.localPid;
                if (p.Confidentiality.Key > 0)
                {
                    if (p.Confidentiality.Key == 1)
                    {
                        // do nothing here - M code takes care of this per documentation
                    }
                    else if (p.Confidentiality.Key == 2)
                    {
                        api.issueConfidentialityBulletin(mySession.ConnectionSet);
                    }
                    else if (p.Confidentiality.Key > 2)
                    {
                        mySession.ConnectionSet.disconnectAll();
                        throw new ApplicationException(p.Confidentiality.Value);
                    }
                }
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedOefOifArray getOefOif()
        {
            TaggedOefOifArray result = new TaggedOefOifArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, null);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }

            string sitecode = mySession.ConnectionSet.BaseSiteId;

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi api = new PatientApi();
                OEF_OIF[] rex = api.getOefOif(cxn);
                result = new TaggedOefOifArray(sitecode, rex);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        //public Patient findAtSite200(string ssn)
        //{
        //    MySession site200Session = new MySession();

        //    try
        //    {
        //        // Get a connection
        //        Site site = (Site)mySession.SiteTable.Sites["200"];
        //        DataSource dataSource = site.getDataSourceByModality("HIS");

        //        ConnectionApi cxnApi = new ConnectionApi(dataSource);
        //        site200Session.cxn = cxnApi.MdoConnection;
        //        site200Session.cxn.connect();

        //        // Visit site 200 as DoD user
        //        User dodUser = new User();
        //        dodUser.SSN = new SocSecNum("123456789");
        //        dodUser.Name = new PersonName("DEPARTMENT OF DEFENSE,USER");
        //        dodUser.LogonSiteUid = "31066";
        //        dodUser.LogonSiteId = new KeyValuePair<string, string>("200", "Site200");
        //        UserApi userApi = new UserApi();
        //        string duz = userApi.visit(site200Session.cxn, dodUser, MdwsConstants.CAPRI_CONTEXT, true);

        //        // Do the lookup
        //        PatientApi patApi = new PatientApi();
        //        Patient[] matches = patApi.match(site200Session.cxn, ssn);

        //        // Process result
        //        if (matches.Length == 0)
        //        {
        //            return null;
        //        }
        //        if (matches.Length > 1)
        //        {
        //            throw new Exception("Multiple matches to SSN");
        //        }
        //        return patApi.select(site200Session.cxn, matches[0].LocalPid);
        //    }
        //    catch (Exception e)
        //    {
        //        throw e;
        //    }
        //    finally 
        //    {
        //        // Disconnect
        //        site200Session.cxn.disconnect();
        //    }
        //}

        //public Patient findAtVisn(string visnId, string ssn)
        //{
        //    MySession visnSession = new MySession();

        //    try
        //    {
        //        Site[] sites = MdwsUtils.parseSiteList(mySession.SiteTable, visnId);
        //        ArrayList sources = new ArrayList(sites.Length);
        //        for (int i = 0; i < sites.Length; i++)
        //        {
        //            for (int j = 0; j < sites[i].Sources.Length; j++)
        //            {
        //                if (sites[i].Sources[j].Protocol == "VISTA")
        //                {
        //                    sources.Add(sites[i].Sources[j]);
        //                }
        //            }
        //        }
        //        ConnectionApi cxnApi = new ConnectionApi((DataSource[])sources.ToArray(typeof(DataSource)));
        //        IndexedHashtable t = cxnApi.MultiSourceQuery.connect();
        //        visnSession.msq = cxnApi.MultiSourceQuery;

        //        // Visit site 200 as DoD user
        //        UserApi userApi = new UserApi();
        //        t = userApi.visit(visnSession.msq, mySession.user, MdwsConstants.CPRS_CONTEXT, true);

        //        // Do the lookup
        //        PatientApi patApi = new PatientApi();
        //        IndexedHashtable matches = patApi.match(visnSession.msq, ssn);

        //        // Process result
        //        for (int i = 0; i < matches.Count; i++)
        //        {
        //            Patient[] pa = (Patient[])matches.GetValue(i);
        //            if (pa.Length == 0)
        //            {
        //                continue;
        //            }
        //            if (pa.Length > 1)
        //            {
        //                throw new Exception("Multiple matches from site " + (string)matches.GetKey(i));
        //            }
        //            string sitecode = (string)matches.GetKey(i);
        //            return patApi.select(visnSession.msq.getConnection(sitecode),pa[0].LocalPid);
        //        }
        //        return null;
        //    }
        //    catch (Exception e)
        //    {
        //        throw e;
        //    }
        //    finally
        //    {
        //        // Disconnect
        //        visnSession.msq.disconnect();
        //    }
        //}

        //public Patient findAtVisns(string ssn)
        //{
        //    Patient result = null;
        //    int visnIdx = 0;
        //    do
        //    {
        //        Region r = (Region)mySession.SiteTable.Regions.GetByIndex(visnIdx++);
        //        result = findAtVisn("v" + Convert.ToString(r.Id), ssn);
        //    } 
        //    while (visnIdx < mySession.SiteTable.Regions.Count && result == null);
        //    return result;
        //}

        //public Patient[] matchAtVisn(string visnId, string ssn)
        //{
        //    MySession visnSession = new MySession();

        //    try
        //    {
        //        Site[] sites = MdwsUtils.parseSiteList(mySession.SiteTable, visnId);
        //        ArrayList sources = new ArrayList(sites.Length);
        //        for (int i = 0; i < sites.Length; i++)
        //        {
        //            for (int j = 0; j < sites[i].Sources.Length; j++)
        //            {
        //                if (sites[i].Sources[j].Protocol == "VISTA")
        //                {
        //                    sources.Add(sites[i].Sources[j]);
        //                }
        //            }
        //        }
        //        ConnectionApi cxnApi = new ConnectionApi((DataSource[])sources.ToArray(typeof(DataSource)));
        //        IndexedHashtable t = cxnApi.MultiSourceQuery.connect();
        //        visnSession.msq = cxnApi.MultiSourceQuery;

        //        // Visit site 200 as DoD user
        //        UserApi userApi = new UserApi();
        //        t = userApi.visit(visnSession.msq, mySession.user, MdwsConstants.CPRS_CONTEXT, true);

        //        // Do the lookup
        //        PatientApi patApi = new PatientApi();
        //        IndexedHashtable matches = patApi.match(visnSession.msq, ssn);

        //        // Process result
        //        ArrayList lst = new ArrayList();
        //        for (int i = 0; i < matches.Count; i++)
        //        {
        //            Patient[] pa = (Patient[])matches.GetValue(i);
        //            if (pa.Length == 0)
        //            {
        //                continue;
        //            }
        //            if (pa.Length > 1)
        //            {
        //                throw new Exception("Multiple matches from site " + (string)matches.GetKey(i));
        //            }
        //            string sitecode = (string)matches.GetKey(i);
        //            Patient p = patApi.select(visnSession.msq.getConnection(sitecode), pa[0].LocalPid);
        //            p.LocalSiteId = sitecode;
        //            lst.Add(p);
        //        }
        //        if (lst.Count == 0)
        //        {
        //            return null;
        //        }
        //        return (Patient[])lst.ToArray(typeof(Patient));
        //    }
        //    catch (Exception e)
        //    {
        //        throw e;
        //    }
        //    finally
        //    {
        //        // Disconnect
        //        visnSession.msq.disconnect();
        //    }
        //}

        //public Patient[] matchAtVisns(string ssn)
        //{
        //    ArrayList lst = new ArrayList();
        //    for (int visnIdx = 0; visnIdx < mySession.SiteTable.Regions.Count; visnIdx++)
        //    {
        //        Region r = (Region)mySession.SiteTable.Regions.GetByIndex(visnIdx);
        //        if (r.Id > 23)
        //        {
        //            continue;
        //        }
        //        Patient[] p = matchAtVisn("v" + Convert.ToString(r.Id), ssn);
        //        if (p != null)
        //        {
        //            for (int i = 0; i < p.Length; i++)
        //            {
        //                lst.Add(p[i]);
        //            }
        //        }
        //    }
        //    if (lst.Count == 0)
        //    {
        //        return null;
        //    }
        //    return (Patient[])lst.ToArray(typeof(Patient));
        //}

        //public Patient findPatient(string ssn)
        //{
        //    Patient result = findAtSite200(ssn);
        //    if (result != null)
        //    {
        //        return result;
        //    }
        //    return findAtVisns(ssn);
        //}

        //public IndexedHashtable matchAtSources(string ssn, int batchSize)
        //{
        //    IndexedHashtable result = new IndexedHashtable(); ;
        //    int nSources = 0;
        //    while (nSources < mySession.SiteTable.Sources.Count)
        //    {
        //        ArrayList srcLst = new ArrayList(batchSize);
        //        int count = 0;
        //        while (count < batchSize && nSources < mySession.SiteTable.Sources.Count)
        //        {
        //            DataSource src = (DataSource)mySession.SiteTable.Sources[nSources++];
        //            if (src.Protocol == "VISTA")
        //            {
        //                srcLst.Add(src);
        //                count++;
        //            }
        //        }
        //        if (srcLst.Count > 0)
        //        {
        //            ConnectionApi cxnApi = new ConnectionApi((DataSource[])srcLst.ToArray(typeof(DataSource)));
        //            IndexedHashtable t = cxnApi.MultiSourceQuery.connect();

        //            // Visit site 200 as DoD user
        //            UserApi userApi = new UserApi();
        //            t = userApi.visit(cxnApi.MultiSourceQuery, mySession.user, MdwsConstants.CPRS_CONTEXT, true);

        //            // Do the lookup
        //            PatientApi patApi = new PatientApi();
        //            IndexedHashtable matches = patApi.match(cxnApi.MultiSourceQuery, ssn);
        //            mySession.msq = cxnApi.MultiSourceQuery;

        //            // Process result
        //            for (int i = 0; i < matches.Count; i++)
        //            {
        //                if (matches.GetValue(i).GetType().Name.EndsWith("Exception"))
        //                {
        //                    //TBD: need to return exception here
        //                    continue;
        //                }
        //                Patient[] pa = (Patient[])matches.GetValue(i);
        //                if (pa.Length == 0)
        //                {
        //                    continue;
        //                }
        //                if (pa.Length > 1)
        //                {
        //                    throw new Exception("Multiple matches from site " + (string)matches.GetKey(i));
        //                }
        //                string sitecode = (string)matches.GetKey(i);
        //                Patient p = patApi.select(cxnApi.MultiSourceQuery.getConnection(sitecode), pa[0].LocalPid);
        //                p.LocalSiteId = sitecode;
        //                result.Add(sitecode,p);
        //            }
        //            mySession.msq.disconnect();
        //        }
        //    }
        //    if (result.Count == 0)
        //    {
        //        return null;
        //    }
        //    return result;
        //}

        public PatientArray findPatient(string ssn)
        {
            return mpiLookup(ssn, "", "", "", "", "", "");
        }

        //public PatientLocationTO locatePatient(string sitecode, string dfn)
        //{
        //    PatientLocationTO result = new PatientLocationTO();
        //    //string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
        //    //if (msg != "OK")
        //    //{
        //    //    result.fault = new FaultTO(msg);
        //    //}
        //    if (sitecode == "")
        //    {
        //        result.fault = new FaultTO("Missing sitecode");
        //    }
        //    else if (dfn == "")
        //    {
        //        result.fault = new FaultTO("Missing DFN");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }

        //    try
        //    {
        //        // get Vista
        //        Site site = mySession.SiteTable.getSite(sitecode);
        //        if (site == null)
        //        {
        //            result.fault = new FaultTO("Invalide sitecode");
        //            return result;
        //        }
        //        DataSource vista = site.getDataSourceByModality("HIS");

        //        // connect to site
        //        ConnectionApi cxnApi = new ConnectionApi(vista);
        //        cxnApi.connect();

        //        // visit site
        //        UserApi userApi = new UserApi();
        //        string duz = userApi.visit(cxnApi.MdoConnection, mySession.user, MdwsConstants.CPRS_CONTEXT, true);

        //        // get all sites
        //        PatientApi patientApi = new PatientApi();
        //        mySession.Patient = patientApi.select(cxnApi.MdoConnection, dfn);
        //        if (mySession.Patient.DeceasedDate != "")
        //        {
        //            result.deceasedDate = mySession.Patient.DeceasedDate;
        //            return result;
        //        }

        //        IndexedHashtable selectedPatients = null;

        //        // catch here if this is the only site
        //        if (mySession.Patient.MpiPid == "")
        //        {
        //            selectedPatients = new IndexedHashtable(1);
        //            selectedPatients.Add(mySession.Patient.LocalPid, mySession.Patient);
        //            cxnApi.MultiSourceQuery = new MultiSourceQuery(new DataSource[] { vista });
        //        }
        //        else
        //        {
        //            // close cxn
        //            cxnApi.disconnect();

        //            // visit all sites
        //            ArrayList lst = new ArrayList(mySession.Patient.SiteIDs.Length);
        //            for (int i = 0; i < mySession.Patient.SiteIDs.Length; i++)
        //            {
        //                site = mySession.SiteTable.getSite(mySession.Patient.SiteIDs[i].Id);
        //                if (site == null)
        //                {
        //                    continue;
        //                }
        //                DataSource ds = site.getDataSourceByModality("HIS");
        //                if (ds.Protocol == "VISTA")
        //                {
        //                    lst.Add(ds);
        //                }
        //            }
        //            DataSource[] sources = (DataSource[])lst.ToArray(typeof(DataSource));
        //            cxnApi = new ConnectionApi(sources);
        //            cxnApi.MultiSourceQuery.connect();
        //            mySession.msq = cxnApi.MultiSourceQuery;

        //            IndexedHashtable t = userApi.visit(mySession.msq, mySession.user, MdwsConstants.CPRS_CONTEXT, true);

        //            // set DFNs
        //            t = patientApi.setLocalPids(mySession.msq, mySession.Patient.MpiPid);

        //            // select patient
        //            selectedPatients = patientApi.select(mySession.msq);

        //            for (int i = 0; i < selectedPatients.Count; i++)
        //            {
        //                Patient p = (Patient)selectedPatients.GetValue(i);
        //                if (p.DeceasedDate != "")
        //                {
        //                    result.deceasedDate = p.DeceasedDate;
        //                    return result;
        //                }
        //            }
        //        }

        //        // get appointments
        //        EncounterApi encounterApi = new EncounterApi();
        //        IndexedHashtable appts = encounterApi.getFutureAppointments(mySession.msq);

        //        // get contacts
        //        IndexedHashtable contacts = patientApi.getPatientAssociates(mySession.msq);

        //        // close cxns
        //        cxnApi.MultiSourceQuery.disconnect();

        //        // check for current inpatient
        //        for (int i = 0; i < selectedPatients.Count; i++)
        //        {
        //            Patient p = (Patient)selectedPatients.GetValue(i);
        //            if (p.IsInpatient)
        //            {
        //                result.medicalCenter = new SiteTO();
        //                result.medicalCenter.sitecode = (string)selectedPatients.GetKey(i);
        //                result.medicalCenter.name = mySession.SiteTable.getSite(result.medicalCenter.sitecode).Name;
        //                result.inpatientLocation = new HospitalLocationTO(p.Location);
        //            }
        //        }

        //        // set appointments
        //        result.futureAppointments = new TaggedAppointmentArrays(appts);

        //        // and contacts
        //        result.contacts = processContacts(contacts);

        //        return result;
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e.Message);
        //    }
        //    return result;
        //}

        internal PatientAssociateArray processContacts(IndexedHashtable t)
        {
            ArrayList lst = new ArrayList();
            for (int siteIdx = 0; siteIdx < t.Count; siteIdx++)
            {
                PatientAssociate[] pas = (PatientAssociate[])t.GetValue(siteIdx);
                for (int i = 0; i < pas.Length; i++)
                {
                    lst.Add(pas[i]);
                }
            }
            return new PatientAssociateArray(lst);
        }

        /// <summary>
        /// Lookup a patient in the Medora Patient Index. This can be a stateless call (i.e. not currently required to login)
        /// </summary>
        /// <param name="SSN">Patient SSN (required)</param>
        /// <param name="lastName">Patient Last Name (optional)</param>
        /// <param name="firstName">Patient First Name (optional)</param>
        /// <param name="middleName">Patient Middle Name (optional)</param>
        /// <param name="nameSuffix">Patient Name Suffix (optional)</param>
        /// <param name="DOB">Patient Date Of Birth (optional)</param>
        /// <param name="gender">Patient Gender (not currently used for matching)</param>
        /// <returns>PatientArray of matches</returns>
        public PatientArray mpiLookup(
            string SSN,
            string lastName,
            string firstName,
            string middleName,
            string nameSuffix,
            string DOB,
            string gender)
        {
            PatientArray result = new PatientArray();

            if (String.IsNullOrEmpty(SSN))
            {
                result.fault = new FaultTO("Missing SSN");
            }
            else if (!SocSecNum.isValid(SSN))
            {
                result.fault = new FaultTO("Invalid SSN");
            }
            // hard coded the cxn string since our MPI database should really be a service for everyone
            //else if (mySession == null || mySession.MdwsConfiguration == null || mySession.MdwsConfiguration.SqlConfiguration == null || 
            //    String.IsNullOrEmpty(mySession.MdwsConfiguration.SqlConfiguration.ConnectionString))
            //{
            //    result.fault = new FaultTO("Your MDWS configuration does not contain a valid SQL connection string");
            //}
            if (result.fault != null)
            {
                return result;
            }

            Patient patient = new Patient();
            patient.SSN = new SocSecNum(SSN);

            if (!String.IsNullOrEmpty(lastName) && !String.IsNullOrEmpty(firstName))
            {
                patient.Name = new PersonName();
                patient.Name.Lastname = lastName;
                patient.Name.Firstname = firstName;
                if(!String.IsNullOrEmpty(middleName))
                {
                    patient.Name.Firstname = firstName + " " + middleName;
                }
                patient.Name.Suffix = nameSuffix;
            }
            if(!String.IsNullOrEmpty(DOB))
            {
                patient.DOB = DOB;
            }
            // SQL query doesn't care about gender so just ignore it for now
            //patient.Gender = gender;

            try
            {
                PatientApi api = new PatientApi();
                Patient[] matches = null;

                Site site = mySession.SiteTable.getSite("500");
                matches = api.mpiMatch(site.Sources[0], SSN);
                //if (patient.Name != null && !String.IsNullOrEmpty(patient.Name.LastNameFirst)) // match all patient info if present
                //{
                //    matches = api.mpiLookup(patient);
                //}
                //else // otherwise just match on SSN
                //{
                //    matches = api.mpiLookup(SSN);
                //}

                result = new PatientArray(matches);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public PatientArray mpiMatchSSN(string ssn)
        {
            PatientArray result = new PatientArray();
            if (!SocSecNum.isValid(ssn))
            {
                result.fault = new FaultTO("Invalid SSN");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi api = new PatientApi();
                Site site = mySession.SiteTable.getSite("500");
                Patient[] p = api.mpiMatch(site.Sources[0], ssn);
                addHomeData(p);
                result = new PatientArray(p);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        internal void addHomeData(Patient[] patients)
        {
            for (int i = 0; i < patients.Length; i++)
            {
                addHomeData(patients[i]);
            }
        }

        //internal void addHomeData(Patient[] patients)
        //{
        //    for (int i = 0; i < patients.Length; i++)
        //    {
        //        Site site = mySession.SiteTable.getSite(patients[i].CmorSiteId);
        //        DataSource src = site.getDataSourceByModality("HIS");
        //        ConnectionApi cxnApi = new ConnectionApi(src);
        //        cxnApi.connect();
        //        UserApi userApi = new UserApi();
        //        string duz = userApi.visit(cxnApi.MdoConnection, mySession.user, MdwsConstants.CPRS_CONTEXT, true);
        //        PatientApi patientApi = new PatientApi();
        //        patients[i].LocalPid = patientApi.getLocalPid(cxnApi.MdoConnection, patients[i].MpiPid);
        //        patientApi.addHomeDate(cxnApi.MdoConnection, patients[i]);
        //        cxnApi.disconnect();
        //    }
        //}

        internal void addHomeData(Patient patient)
        {
            if (patient == null)
            {
                return;
            }
            try
            {
                Site site = mySession.SiteTable.getSite(patient.CmorSiteId);
                DataSource src = site.getDataSourceByModality("HIS");

                MySession newMySession = new MySession(mySession.FacadeName);
                AccountLib accountLib = new AccountLib(newMySession);
                UserTO visitUser = accountLib.visitAndAuthorize("Good players are always lucky", patient.CmorSiteId,
                    mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id, mySession.User.Name.LastNameFirst, 
                    mySession.User.Uid, mySession.User.SSN.toString(), "OR CPRS GUI CHART");

                PatientApi patientApi = new PatientApi();
                patient.LocalPid = patientApi.getLocalPid(newMySession.ConnectionSet.BaseConnection, patient.MpiPid);
                patientApi.addHomeDate(newMySession.ConnectionSet.BaseConnection, patient);
                newMySession.ConnectionSet.BaseConnection.disconnect();
            }
            catch (Exception)
            {
                // just pass back patient unchanged
            }
        }

        internal Patient getHomeData(Patient patient)
        {
            PatientApi api = new PatientApi();
            api.addHomeDate(mySession.ConnectionSet.BaseConnection, patient);
            return patient;
        }

        public TaggedPatientAssociateArray getPatientAssociates(string dfn)
        {
            return getPatientAssociates(mySession.ConnectionSet.BaseConnection, dfn);
        }

        public TaggedPatientAssociateArray getPatientAssociates(string sitecode, string dfn)
        {
            return getPatientAssociates(mySession.ConnectionSet.getConnection(sitecode), dfn);
        }

        internal TaggedPatientAssociateArray getPatientAssociates(AbstractConnection cxn, string dfn)
        {
            TaggedPatientAssociateArray result = new TaggedPatientAssociateArray();
            if (dfn == "")
            {
                result.fault = new FaultTO("Missing dfn");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi patientApi = new PatientApi();
                PatientAssociate[] pas = patientApi.getPatientAssociates(cxn, dfn);
                result = new TaggedPatientAssociateArray(cxn.DataSource.SiteId.Id, pas);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedPatientAssociateArrays getPatientAssociatesMS()
        {
            TaggedPatientAssociateArrays result = new TaggedPatientAssociateArrays();
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi api = new PatientApi();
                IndexedHashtable t = api.getPatientAssociates(mySession.ConnectionSet);
                return new TaggedPatientAssociateArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getConfidentiality()
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
                PatientApi api = new PatientApi();
                result = new TaggedTextArray(api.getConfidentiality(mySession.ConnectionSet));
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray issueConfidentialityBulletin()
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
                PatientApi api = new PatientApi();
                result = new TaggedTextArray(api.issueConfidentialityBulletin(mySession.ConnectionSet));
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TextTO getLocalPid(string mpiPid)
        {
            return getLocalPid(null, mpiPid);
        }

        public TextTO getLocalPid(string sitecode, string mpiPid)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (mpiPid == "")
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                PatientApi api = new PatientApi();
                string localPid = api.getLocalPid(cxn, mpiPid);
                result = new TextTO(localPid);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        /// <summary>
        /// Make a patient inquiry call (address, contact numbers, NOK, etc. information)
        /// </summary>
        /// <returns>TextTO with selected patient inquiry text</returns>
        public TextTO patientInquiry()
        {
            TextTO result = new TextTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(mySession.ConnectionSet.getConnection(mySession.ConnectionSet.BaseSiteId).Pid))
            {
                result.fault = new FaultTO("Need to select patient");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(mySession.ConnectionSet.BaseSiteId);
                string selectedPatient = cxn.Pid;
                PatientApi api = new PatientApi();
                string resultText = api.patientInquiry(cxn, selectedPatient);
                result = new TextTO(resultText);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        //public PatientArray pcsMatch(string ssn)
        //{
        //    PatientArray result = new PatientArray();
        //    if (mySession == null || mySession.cxnMgr == null || mySession.cxnMgr.LoginConnection == null || !mySession.cxnMgr.isAuthorized)
        //    {
        //        result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }
        //    try
        //    {
        //        PatientApi api = new PatientApi();
        //        result = new PatientArray(api.pcsMatch(ssn));
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e);
        //    }
        //    return result;
        //}

        public PatientTO getDemographics()
        {
            PatientTO result = new PatientTO();

            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (String.IsNullOrEmpty(mySession.ConnectionSet.BaseConnection.Pid) || mySession.Patient == null)
            {
                result.fault = new FaultTO("Need to select patient");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                result = new PatientTO(getHomeData(mySession.Patient));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        /// <summary>
        /// Given a national identifier find the patient's sites
        /// </summary>
        /// <param name="mpiPid"></param>
        /// <returns></returns>
        public TaggedTextArray getPatientSitesByMpiPid(string mpiPid)
        {
            TaggedTextArray result = new TaggedTextArray();
            if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            if (result.fault != null)
            {
                return result;
            }

            // Temporary visit to site 200 for initial lookup
            AccountLib acctLib = new AccountLib(mySession);
            result = acctLib.visitDoD(null);
            if (result.fault != null)
            {
                return result;
            }

            TextTO localPid = getLocalPid(mpiPid);
            if (localPid.fault != null)
            {
                result.fault = localPid.fault;
                return result;
            }
            if (String.IsNullOrEmpty(localPid.text))
            {
                result.fault = new FaultTO("Empty DFN returned from VistA");
                return result;
            }

            PatientApi patientApi = new PatientApi();
            StringDictionary siteIds = patientApi.getRemoteSiteIds(mySession.ConnectionSet.BaseConnection, localPid.text);
            mySession.ConnectionSet.disconnectAll();
            result = new TaggedTextArray(siteIds);

            // Fake a logged in user...
            //mySession.user = new User();
            //mySession.user.LogonSiteId = new KeyValuePair<string, string>("506", "Ann Arbor, MI");
            //mySession.user.LogonSiteUid = "";
            //mySession.user.Name = new PersonName("");

            //string[] sitelists = new string[]
            //{
            //    "V11,V2,V4",
            //    "V5,V6,V7",
            //    "V8,V9,V10",
            //    "V12,V15",
            //    "V16,V17,V18",
            //    //"V19,V20,612,662,570,459,640,654",  // exclude Manila from V21
            //    "V3,V22,V23,V11"
            //};

            //ConnectionLib cxnLib = new ConnectionLib(mySession);
            //try
            //{
            //    for (int siteIdx = 0; siteIdx < sitelists.Length; siteIdx++)
            //    {
            //        TaggedTextArray sites = cxnLib.visitSites("", sitelists[siteIdx], MdwsConstants.CPRS_CONTEXT);
            //        if (sites.fault != null)
            //        {
            //            result.fault = sites.fault;
            //            return result;
            //        }
            //        PatientApi patientApi = new PatientApi();
            //        MultiSourceQuery msq = new MultiSourceQuery(mySession.cxnMgr.Connections);
            //        IndexedHashtable t = patientApi.getLocalPids(msq, mpiPid);
            //        for (int i = 0; i < t.Count; i++)
            //        {
            //            if ((string)t.GetValue(i) != "")
            //            {
            //                Connection c = mySession.cxnMgr.getConnection((string)t.GetKey(i));
            //                StringDictionary siteIds = patientApi.getRemoteSiteIds(c, (string)t.GetValue(i));
            //                return new TaggedTextArray(siteIds);
            //            }
            //        }
            //    }
            //}
            //catch (Exception ex)
            //{
            //    result.fault = new FaultTO(ex);
            //}
            //finally
            //{
            //    if (mySession.cxnMgr != null)
            //    {
            //        mySession.cxnMgr.disconnect();
            //    }
            //}
            return result;
        }

        public PatientArray nptLookup(
            string SSN,
            string lastName,
            string firstName,
            string middleName,
            string nameSuffix,
            string DOB,
            string gender)

        {
            PatientArray result = new PatientArray();

            if (String.IsNullOrEmpty(SSN))
            {
                result.fault = new FaultTO("Must supply SSN");
            }
            else if (!SocSecNum.isValid(SSN))
            {
                result.fault = new FaultTO("Invalid SSN");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                PatientApi api = new PatientApi();
                Patient[] patients = api.nptMatch(SSN);
                result = new PatientArray(patients);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedText getPcpForPatient(string pid)
        {
            TaggedText result = new TaggedText();

            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (String.IsNullOrEmpty(pid))
            {
                result.fault = new FaultTO("Empty PID");
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                KeyValuePair<string, string> kvp = Patient.getPcpForPatient(mySession.ConnectionSet.BaseConnection, pid);
                result = new TaggedText(kvp);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedText getMOSReport(string appPwd, string EDIPI)
        {
            TaggedText result = new TaggedText();

            if (String.IsNullOrEmpty(appPwd))
            {
                result.fault = new FaultTO("Missing appPwd");
            }
            // TBD - should we check this? must set to correct MHV password 
            //else if (!String.Equals(appPwd, "MHV APP PWD"))
            //{
            //    result.fault = new FaultTO("Invalid appPwd");
            //}
            else if (String.IsNullOrEmpty(EDIPI))
            {
                result.fault = new FaultTO("Missing EDIPI");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                AbstractConnection cxn = new MdoOracleConnection(new DataSource() { ConnectionString = mySession.MdwsConfiguration.MosConnectionString });
                PatientApi api = new PatientApi();
                Patient p = new Patient() { EDIPI = EDIPI };
                TextReport rpt = api.getMOSReport(cxn, p);
                result.text = rpt.Text;
                result.tag = "VADIR";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

    }
}