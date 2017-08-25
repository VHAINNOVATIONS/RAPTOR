using System;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Data;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.dao.ldap;
using System.DirectoryServices.ActiveDirectory;
using gov.va.medora.mdws.dto.ldap;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Logins and visits.
    /// </summary>
    public class AccountLib
    {
        MySession mySession;
        AbstractConnection myCxn;

        /// <summary>
        /// Only constructor requires a MySession.
        /// </summary>
        /// <param name="mySession">Holds credentials, primary permission, default visit method</param>
        public AccountLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        //public UserTO updateActiveDirectoryProfile(string domain, string username, string password, string title, 
        //    string officePhone, string faxNumber, string addressLine1, string addressLine2, string addressLine2,
        //    string city, string state, string zip, string office, 

        public UserArray ldapUserLookup(string uid, string domainSearchRoot)
        {
            UserArray result = new UserArray();

            if (String.IsNullOrEmpty(uid))
            {
                result.fault = new FaultTO("Must supply domain, username and password");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                DataSource src = new DataSource() { SiteId = new SiteId("1"), Modality = "FEDUID", Protocol = "LDAP", Provider = "GC://dc=va,dc=gov" };
                if (!String.IsNullOrEmpty(domainSearchRoot))
                {
                    src.Provider = domainSearchRoot;
                }
                AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));
                LdapConnection cxn = new LdapConnection(src);
                cxn.Account = new LdapAccount(cxn);

                LdapUserDao dao = new LdapUserDao(cxn);

                IList<User> users = null;
                using (new Impersonator(mySession.MdwsConfiguration.LdapConfiguration.RunasUser))
                {
                    users = dao.userLookupList(new KeyValuePair<String, String>("", uid));
                }

                result = new UserArray(users);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public DomainArray getDomains()
        {
            DomainArray result = new DomainArray();

            try
            {
                using (new Impersonator(mySession.MdwsConfiguration.LdapConfiguration.RunasUser))
                {
                    SortedList<string, Domain> domains = LdapUtils.getCurrentDomains();
                    result = new DomainArray(domains);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public UserTO loginActiveDirectory(string domain, string username, string password)
        {
            UserTO result = new UserTO();

            if (String.IsNullOrEmpty(domain) || String.IsNullOrEmpty(username) || String.IsNullOrEmpty(password))
            {
                result.fault = new FaultTO("Must supply domain, username and password");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                DataSource src = new DataSource() { SiteId = new SiteId("1"), Modality = "FEDUID", Protocol = "LDAP", Provider = domain };
                AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));
                LdapConnection cxn = new LdapConnection(src);
                cxn.Account = new LdapAccount(cxn);

                LdapCredentials creds = new LdapCredentials() { AccountName = username, AccountPassword = password };

                using (new Impersonator(mySession.MdwsConfiguration.LdapConfiguration.RunasUser))
                {
                    string guid = cxn.Account.authenticate(creds);

                    LdapUserDao dao = new LdapUserDao(cxn);
                    IList<User> guidLookupResult = dao.userLookupList(new KeyValuePair<string,string>("", guid));

                    if (guidLookupResult.Count != 1)
                    {
                        throw new ApplicationException("Unexpected error - more than one user returned for authenticated user's GUID");
                    }

                    return new UserTO(guidLookupResult[0]);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        /// <summary>
        /// Log onto a data source.
        /// </summary>
        /// <remarks>
        /// Combines authentication and authorization into a single function.
        /// It will create a new set of session credentials and a primary permission.
        /// These credentials can then be used for subsequent visits.
        /// Login requires a previous connection.
        /// </remarks>
        /// <param name="accountId">Access code</param>
        /// <param name="accountPwd">Verify code</param>
        /// <param name="permissionString">If blank defaults to CPRS context</param>
        /// <returns>UserTO</returns>
        public UserTO login(string accountId, string accountPwd, string permissionString)
        {
            UserTO result = new UserTO();

            if (!mySession.HasBaseConnection)
            {
                result.fault = new FaultTO("There is no connection to log onto");
            }
            else if (accountId == "")
            {
                result.fault = new FaultTO("Missing account ID");
            }
            else if (accountPwd == "")
            {
                result.fault = new FaultTO("Missing account password");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                AbstractConnection c = mySession.ConnectionSet.BaseConnection;

                AbstractCredentials credentials = AbstractCredentials.getCredentialsForCxn(c);
                credentials.AccountName = accountId;
                credentials.AccountPassword = accountPwd;

                c.Account.AuthenticationMethod = MdwsConstants.LOGIN_CREDENTIALS;

                if (String.IsNullOrEmpty(permissionString))
                {
                    permissionString = MdwsConstants.CPRS_CONTEXT;
                }
                mySession.PrimaryPermission = new MenuOption(permissionString);

                mySession.User = c.Account.authenticateAndAuthorize(credentials, mySession.PrimaryPermission);

                mySession.Credentials = credentials;

                result = new UserTO(mySession.User);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        /// <summary>
        /// Connect to and log onto a data source.
        /// </summary>
        /// <remarks>
        /// Combines connecting, authentication and authorization into a single function.
        /// It will create a new set of session credentials and a primary permission.
        /// These credentials can then be used for subsequent visits.  Calls login.  
        /// </remarks>
        /// <param name="sourceId">Station number</param>
        /// <param name="accountId">Access code</param>
        /// <param name="accountPwd">Verify code</param>
        /// <param name="permissionString">If blank defaults to CPRS context</param>
        /// <returns>UserTO</returns>
        public UserTO connectAndLogin(string sourceId, string accountId, string accountPwd, string permissionString)
        {
            UserTO result = new UserTO();
            if (sourceId == "")
            {
                result.fault = new FaultTO("Missing source ID");
            }
            else if (accountId == "")
            {
                result.fault = new FaultTO("Missing account ID");
            }
            else if (accountPwd == "")
            {
                result.fault = new FaultTO("Missing account password");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                ConnectionLib cxnLib = new ConnectionLib(mySession);
                DataSourceArray da = cxnLib.connectToLoginSite(sourceId);
                if (da.fault != null)
                {
                    result.fault = da.fault;
                    return result;
                }
                result = login(accountId, accountPwd, permissionString);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        /// <summary>
        /// Visit a single data source and authorize without a previous login.
        /// </summary>
        /// <remarks>
        /// This method is for visits without logins.  It makes its credentials 
        /// and user the session credentials and session user.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceId">Station number of site to visit</param>
        /// <param name="userSourceId">User's station number</param>
        /// <param name="userName">User's name as it appears in VistA</param>
        /// <param name="userLocalId">User's DUZ</param>
        /// <param name="userFederatedId">User's SSN</param>
        /// <param name="permissionString">If blank defaults to CPRS context</param>
        /// <returns>UserTO</returns>
        public UserTO visitAndAuthorize(
            string pwd,
            string sourceId,
            string userSourceId,
            string userName,
            string userLocalId,
            string userFederatedId,
            string permissionString)
        {
            UserTO result = new UserTO();

            //Make sure we have all the args we need
            if (mySession == null || mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No session has been started");
            }
            else if (sourceId == "")
            {
                result.fault = new FaultTO("Missing sitecode of site to visit");
            }
            else if (mySession.SiteTable.getSite(sourceId) == null)
            {
                result.fault = new FaultTO("No site " + sourceId + " in the site table");
            }
            else if (mySession.ConnectionSet != null &&
                     mySession.ConnectionSet.Count > 0 &&
                     mySession.ConnectionSet.IsConnected(sourceId))
            {
                result.fault = new FaultTO("Site " + sourceId + " already connected");
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0)
            {
                result.fault = new FaultTO("This session has pre-existing connections and this method should be the base connection.","Do a disconnect?");
            }
            else if (userSourceId == "")
            {
                result.fault = new FaultTO("Missing userSitecode");
            }
            else if (userName == "")
            {
                result.fault = new FaultTO("Missing userName");
            }
            else if (userLocalId == "")
            {
                result.fault = new FaultTO("Missing DUZ");
            }
            else if (userFederatedId == "")
            {
                result.fault = new FaultTO("Missing SSN");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                SiteTable t = mySession.SiteTable;
                Site userSite = (Site)t.Sites[userSourceId];
                if (userSite == null)
                {
                    result.fault = new FaultTO("No such site: " + userSourceId);
                    return result;
                }

                Site visitSite = (Site)t.Sites[sourceId];
                if (visitSite == null)
                {
                    result.fault = new FaultTO("No such site: " + sourceId);
                    return result;
                }

                DataSource dataSource = visitSite.getDataSourceByModality("HIS");
                if (dataSource == null)
                {
                    result.fault = new FaultTO("Site " + sourceId + " has no HIS");
                    return result;
                }

                mySession.Credentials = MdwsUtils.setVisitCredentials(userLocalId, userFederatedId, userName, "", userSite.getDataSourceByModality("HIS"), pwd);
                mySession.Credentials.SecurityPhrase = pwd;

                if (permissionString == "")
                {
                    permissionString = mySession.DefaultPermissionString;
                }
                mySession.PrimaryPermission = new MenuOption(permissionString);

                mySession.User = doTheVisit(sourceId, mySession.Credentials, mySession.PrimaryPermission);

                mySession.User.Name = new PersonName(userName);
                mySession.User.SSN = new SocSecNum(userFederatedId);
                mySession.User.LogonSiteId = dataSource.SiteId;

                addMyCxn2CxnSet();
                result = new UserTO(mySession.User);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        /// <summary>
        /// Visit a single data source after a login.
        /// </summary>
        /// <remarks>
        /// This method is for visits after a login and the credentials are therefore
        /// already in mySession.  It does not alter the credentials.  It visits only 
        /// 1 site but returns the multi-site visit return object.  Calls visitSites.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceId">Station number of site to visit</param>
        /// <param name="permissionString">If blank defaults to CPRS context</param>
        /// <returns>TaggedTextArray: each site with user DUZ or an error message</returns>
        public TaggedTextArray visit(string pwd, string sourceId, string permissionString)
        {
            return visitSites(pwd, sourceId, permissionString);
        }

        /// <summary>
        /// Visit multiple data sources after a login.
        /// </summary>
        /// <remarks>
        /// Requires a previous login so the credentials are already in mySession and are
        /// not altered.  Can visit a single source or multiple sources.  Sources may be
        /// VAMCs, VISNs, some combination of VAMCs and VISNs, or the entire VHA.  See the
        /// sourceList parameter.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceList">
        /// A comma-delimited list of station numbers and/or VISN IDs, as in 
        /// "402,550" or "V12,V22" or "V1,V2,456".  To visit all VistA systems set the param
        /// to "*".  Duplicate station #'s are ignored.
        /// </param>
        /// <param name="permissionString">If blank defaults to CPRS context</param>
        /// <returns>TaggedTextArray: each site with user DUZ or an error message</returns>
        public TaggedTextArray visitSites(string pwd, string sourceList, string permissionString)
        {
            TaggedTextArray result = new TaggedTextArray();

            //Say the magic word
            // TBD - needed?????
            //if (pwd != mySession.AppPwd)
            //{
            //    result.fault = new FaultTO("Invalid application password");
            //}
            if (mySession == null || mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No site table");
            }
            else if (sourceList == "")
            {
                result.fault = new FaultTO("Missing sitelist");
            }
            //else if (mySession.user == null)
            //{
            //    result.fault = new FaultTO("No logged in user");
            //}
            else if (mySession.Credentials == null)
            {
                result.fault = new FaultTO("Cannot use this method without previous login");
            }
            if (result.fault != null)
            {
                return result;
            }

            Site[] sites = MdwsUtils.parseSiteList(mySession.SiteTable, sourceList);
            List<DataSource> sources = new List<DataSource>(sites.Length);
            for (int i = 0; i < sites.Length; i++)
            {
                for (int j = 0; j < sites[i].Sources.Length; j++)
                {
                    if (sites[i].Sources[j].Protocol == "VISTA" ||
                        sites[i].Sources[j].Protocol == "FHIE" || sites[i].Sources[j].Protocol == "XVISTA")
                    {
                        sources.Add(sites[i].Sources[j]);
                    }
                }
            }
            return setupMultiSourceQuery(pwd, sources, permissionString);
        }

        /// <summary>
        /// Visit multiple data sources without a login.
        /// </summary>
        /// <remarks>
        /// Requires no previous login.  Used by daemon apps to visit multiple sources.  Sources may be
        /// VAMCs, VISNs, some combination of VAMCs and VISNs, or the entire VHA.  See the
        /// sourceList parameter.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceList">
        /// A comma-delimited list of station numbers and/or VISN IDs, as in 
        /// "402,550" or "V12,V22" or "V1,V2,456".  To visit all VistA systems set the param
        /// to "*".  Duplicate station #'s are ignored.
        /// </param>
        /// <param name="permissionStr">If blank defaults to CPRS context</param>
        /// <returns>TaggedTextArray: each site with user DUZ or an error message</returns>
        public TaggedTextArray visitSites(string pwd, string sourceList, string userSitecode, string userName, string DUZ, string permissionStr)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("Missing pwd");
            }
            else if (String.IsNullOrEmpty(sourceList))
            {
                result.fault = new FaultTO("Missing sitelist");
            }
            else if (String.IsNullOrEmpty(userSitecode))
            {
                result.fault = new FaultTO("Missing userSitecode");
            }
            else if (String.IsNullOrEmpty(userName))
            {
                result.fault = new FaultTO("Missing userName");
            }
            else if (String.IsNullOrEmpty(DUZ))
            {
                result.fault = new FaultTO("Missing DUZ");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(permissionStr))
            {
                permissionStr = mySession.DefaultPermissionString;
            }

            try
            {
                // Do an admin visit to get user's SSN
                User theUser = getVisitorData(userSitecode, DUZ, pwd);

                // Build the credentials
                Site site = mySession.SiteTable.getSite(userSitecode);
                DataSource src = site.getDataSourceByModality("HIS");

                AbstractCredentials creds = new VistaCredentials();
                creds.AuthenticationSource = src;
                creds.AuthenticationToken = userSitecode + '_' + theUser.Uid;
                creds.FederatedUid = theUser.SSN.toString();
                creds.LocalUid = theUser.Uid;
                creds.SecurityPhrase = pwd;
                creds.SubjectName = theUser.Name.getLastNameFirst();
                creds.SubjectPhone = theUser.Phone;

                mySession.PrimaryPermission = new MenuOption(permissionStr);

                // Make next call think there's been a login
                mySession.Credentials = creds;

                // visitSites
                return visitSites(pwd, sourceList, permissionStr);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }

            return result;
        }

        /// <summary>
        /// Visit multiple data sources without a login.
        /// </summary>
        /// <remarks>
        /// Requires no previous login.  Used by daemon apps to visit multiple sources.  Sources may be
        /// VAMCs, VISNs, some combination of VAMCs and VISNs, or the entire VHA.  See the
        /// sourceList parameter.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceList">
        /// A comma-delimited list of station numbers and/or VISN IDs, as in 
        /// "402,550" or "V12,V22" or "V1,V2,456".  To visit all VistA systems set the param
        /// to "*".  Duplicate station #'s are ignored.
        /// </param>
        /// <param name="permissionStr">If blank defaults to CPRS context</param>
        /// <returns>TaggedTextArray: each site with user DUZ or an error message</returns>
        public TaggedTextArray visitSites(string pwd, string sourceList, string userSitecode, string userName, string DUZ, string SSN, string permissionStr)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("Missing pwd");
            }
            else if (String.IsNullOrEmpty(sourceList))
            {
                result.fault = new FaultTO("Missing sitelist");
            }
            else if (String.IsNullOrEmpty(userSitecode))
            {
                result.fault = new FaultTO("Missing userSitecode");
            }
            else if (String.IsNullOrEmpty(userName))
            {
                result.fault = new FaultTO("Missing userName");
            }
            else if (String.IsNullOrEmpty(DUZ))
            {
                result.fault = new FaultTO("Missing DUZ");
            }
            else if (String.IsNullOrEmpty(SSN))
            {
                result.fault = new FaultTO("Missing SSN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(permissionStr))
            {
                permissionStr = mySession.DefaultPermissionString;
            }

            try
            {
                // Do an admin visit to get user's SSN
                //User theUser = getVisitorData(userSitecode, DUZ, pwd);

                // Build the credentials
                Site site = mySession.SiteTable.getSite(userSitecode);
                DataSource src = site.getDataSourceByModality("HIS");

                AbstractCredentials creds = new VistaCredentials();
                creds.AuthenticationSource = src;
                creds.AuthenticationToken = userSitecode + '_' + DUZ;
                creds.FederatedUid = SSN;
                creds.LocalUid = DUZ;
                creds.SecurityPhrase = pwd;
                creds.SubjectName = userName;
                creds.SubjectPhone = "";

                User user = new User();
                user.UserName = userName;
                user.Uid = DUZ;
                user.SSN = new SocSecNum(SSN);

                mySession.PrimaryPermission = new MenuOption(permissionStr);

                // Make next call think there's been a login
                mySession.Credentials = creds;
                mySession.User = user;

                // visitSites
                return visitSites(pwd, sourceList, permissionStr);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }

            return result;
        }

        /// <summary>
        /// After selecting a patient at one site, make subsequent queries multi-site.
        /// </summary>
        /// <remarks>
        /// This function will detect and visit all the other sites at which the patient 
        /// has been seen. Subsequent queries, then, will return data from all these sources.
        /// This method requires a previous login or visit to have set the credentials in
        /// mySession, as well as a previous patient select to have set the patient in 
        /// mySession.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="context">If blank defaults to CPRS context</param>
        /// <returns>SiteArray: the sources subsequent queries will read from</returns>
        public SiteArray setupMultiSourcePatientQuery(string pwd, string context)
        {
            SiteArray result = new SiteArray();

            //Say the magic word
            // TBD - needed?????
            //if (pwd != mySession.AppPwd)
            //{
            //    result.fault = new FaultTO("Invalid application password");
            //}
            //Make sure we have all the args we need
            if (mySession == null || mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No site table");
            }
            //else if (mySession.user == null)
            //{
            //    result.fault = new FaultTO("No user", "Need to login or visit?");
            //}
            else if (mySession.Patient == null)
            {
                result.fault = new FaultTO("No patient", "Need to select a patient?");
            }
            else if (String.IsNullOrEmpty(mySession.Patient.MpiPid))
            {
                result.fault = new FaultTO("Patient has no ICN", "Need to select the patient?");
            }
            else if (mySession.Patient.SiteIDs == null || mySession.Patient.SiteIDs.Length == 0)
            {
                result.fault = new FaultTO("Patient has no sites", "Need to select the patient?");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(context))
            {
                context = mySession.DefaultPermissionString;
            }

            try
            {
                Site[] sites = mySession.SiteTable.getSites(mySession.Patient.SiteIDs);
                List<DataSource> sources = new List<DataSource>(sites.Length);
                for (int i = 0; i < sites.Length; i++)
                {
                    if (sites[i] == null)
                    {
                        continue;
                    }
                    DataSource src = sites[i].getDataSourceByModality("HIS");
                    if (src != null)
                    {
                        sources.Add(src);
                    }
                }

                TaggedTextArray tta = setupMultiSourceQuery(pwd, sources, context);

                PatientApi patientApi = new PatientApi();
                IndexedHashtable t = patientApi.setLocalPids(mySession.ConnectionSet, mySession.Patient.MpiPid);

                // we need to check confidentiality everywhere and issue bulletin if found at any site
                IndexedHashtable confidentialResults = patientApi.getConfidentiality(mySession.ConnectionSet);
                if (confidentialResults != null && confidentialResults.Count > 0)
                {
                    KeyValuePair<int, string> highestConfidentialityResult = new KeyValuePair<int,string>(0, "");
                    for (int i = 0; i < confidentialResults.Count; i++)
                    {
                        KeyValuePair<int, string> siteResult = (KeyValuePair<int, string>)confidentialResults.GetValue(i);
                        if (siteResult.Key > highestConfidentialityResult.Key)
                        {
                            highestConfidentialityResult = siteResult;
                        }
                    }
                    if (highestConfidentialityResult.Key == 1)
                    {
                        // do nothing here - M code takes care of this per documentation
                    }
                    else if (highestConfidentialityResult.Key == 2)
                    {
                        patientApi.issueConfidentialityBulletin(mySession.ConnectionSet);
                    }
                    else if (highestConfidentialityResult.Key > 2)
                    {
                        // catch block below takes care of disconnecting all sites
                        throw new ApplicationException(highestConfidentialityResult.Value);
                    }
                }
                // end confidentiality
                result = new SiteArray(sites);
                for (int i = 0; i < result.sites.Length; i++)
                {
                    if (mySession.ConnectionSet.ExcludeSite200 && result.sites[i].sitecode == "200")
                    {
                        result.sites[i].fault = new FaultTO("Site excluded");
                    }
                        // This is causing an index out of bounds error when length of tta != sites length
                    //else if (tta.results[i].fault != null)
                    //{
                    //    result.sites[i].fault = tta.results[i].fault;
                    //}
                    else if (t.ContainsKey(result.sites[i].sitecode))
                    {
                        // TBD: fault in t?
                        result.sites[i].pid = (string)t.GetValue(result.sites[i].sitecode);
                    }
                }
                // copy faults over if any found
                foreach (TaggedText tt in tta.results)
                {
                    if (tt.fault != null)
                    {
                        foreach (SiteTO s in result.sites)
                        {
                            if (String.Equals(s.sitecode, tt.tag))
                            {
                                s.fault = tt.fault;
                            }
                        }
                    }
                }
            }
            catch (Exception e)
            {
                result = new SiteArray();
                result.fault = new FaultTO(e.Message);
                mySession.ConnectionSet.disconnectAll();
            }
            return result;
        }

        /// <summary>
        /// </summary>
        /// <remarks>
        /// This method assumes there has been no login and therefore no credentials or user
        /// so it makes a new ConnectionSet, new credentials, etc.
        /// </remarks>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="sourceId">Station number</param>
        /// <param name="userId">DUZ</param>
        /// <param name="patientId">DFN</param>
        /// <returns>PersonsTO: a UserTO and a PatientTO</returns>
        public PersonsTO cprsLaunch(string pwd, string sourceId, string userId, string patientId)
        {
            PersonsTO result = new PersonsTO();

            if (String.IsNullOrEmpty(sourceId))
            {
                result.fault = new FaultTO("No sitecode!");
            }
            else if (String.IsNullOrEmpty(userId))
            {
                result.fault = new FaultTO("No DUZ!");
            }
            else if (String.IsNullOrEmpty(patientId))
            {
                result.fault = new FaultTO("No DFN!");
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0)
            {
                result.fault = new FaultTO("This session has pre-existing connections and this method should be the base connection.", "Do a disconnect?");
            }
            if (result.fault != null)
            {
                return result;
            }

            // Get the site
            // Note the visit site and user site are the same!
            Site site = mySession.SiteTable.getSite(sourceId);
            if (site == null)
            {
                result.fault = new FaultTO("No site " + sourceId + " in sites table");
                return result;
            }


            // Now select the patient
            try
            {
                User trueUser = getVisitorData(sourceId, userId, pwd);

                // Now visit as the real user
                // Note context has to be CPRS!
                result.user = visitAndAuthorize(
                    pwd, 
                    sourceId, 
                    sourceId, 
                    trueUser.Name.getLastNameFirst(), 
                    userId, 
                    trueUser.SSN.toString(), 
                    mySession.DefaultPermissionString);

                if (result.user.fault != null)
                {
                    result.fault = result.user.fault;
                    return result;
                }
                PatientApi patientApi = new PatientApi();
                mySession.Patient = patientApi.select(mySession.ConnectionSet.getConnection(sourceId), patientId);
                result.patient = new PatientTO(mySession.Patient);
            }
            catch (Exception e)
            {
                //mySession.cxnMgr.disconnect();
                if (e.Message.StartsWith("Patient unknown to CPRS"))
                {
                    result.patient.fault = new FaultTO(e.Message);
                }
                else
                {
                    result.fault = new FaultTO(e.Message);
                }
            }
            return result;
        }

        /// <summary>
        /// visitDoD - remove?
        /// </summary>
        /// <param name="pwd"></param>
        /// <returns></returns>
        public TaggedTextArray visitDoD(string pwd)
        {
            Site site = mySession.SiteTable.getSite(MdwsConstants.DOD_SITE);
            //Site site = mySession.SiteTable.getSite("506");
            AbstractCredentials credentials = getAdministrativeCredentials(site);
            credentials.SecurityPhrase = MdwsConstants.MY_SECURITY_PHRASE;

            string context = MdwsConstants.MDWS_CONTEXT;
            if (mySession.DefaultVisitMethod == MdwsConstants.NON_BSE_CREDENTIALS)
            {
                context = MdwsConstants.CPRS_CONTEXT;
            }
            AbstractPermission permission = new MenuOption(context);
            permission.IsPrimary = true;

            TaggedTextArray result = new TaggedTextArray();
            try
            {
                User u = doTheVisit(site.Id, credentials, permission);
                result.results = new TaggedText[] { new TaggedText(site.Id, u.Uid) };
                addMyCxn2CxnSet();
                mySession.Credentials = credentials;
                mySession.PrimaryPermission = permission;
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public SiteTO patientVisit(string pwd, string sitecode, string mpiPid)
        {
            SiteTO result = new SiteTO();
            if (mySession == null || mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No session has been started");
            }
            else if (sitecode == "")
            {
                result.fault = new FaultTO("No sitecode");
            }
            else if (mySession.SiteTable.getSite(sitecode) == null)
            {
                result.fault = new FaultTO("No site " + sitecode + " in the site table");
            }
            else if (mySession.ConnectionSet != null &&
                     mySession.ConnectionSet.Count > 0 &&
                     mySession.ConnectionSet.HasConnection(sitecode))
            {
                result.fault = new FaultTO("Site " + sitecode + " already connected");
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0)
            {
                result.fault = new FaultTO("This session has pre-existing connections and this method should be the base connection.", "Do a disconnect?");
            }
            else if (mpiPid == "")
            {
                result.fault = new FaultTO("No MPI PID");
            }
            if (result.fault != null)
            {
                return result;
            }

            SiteArray sa = patientVisit(pwd, sitecode, mpiPid, false);
            if (sa.fault == null)
            {
                result.fault = sa.fault;
            }
            else if (sa.sites.Length == 0)
            {
                result.fault = new FaultTO("Unable to connect to site " + sitecode);
            }
            else
            {
                result = sa.sites[0];
            }
            return result;
        }

        /// <summary>
        /// patientVisit
        /// This method is used by MHV and will probably be used by other PHR apps.
        /// </summary>
        /// <param name="pwd">Client app's BSE security phrase</param>
        /// <param name="homeSitecode">Station number</param>
        /// <param name="mpiPid">ICN</param>
        /// <param name="multiSite">Set to false for now</param>
        /// <returns>SiteArray: Nothing really in it though, except error messages</returns>
        public SiteArray patientVisit(string pwd, string homeSitecode, string mpiPid, bool multiSite)
        {
            SiteArray result = new SiteArray();

            // Say the magic word
            // TBD - needed????
            //if (pwd != mySession.AppPwd)
            //{
            //    result.fault = new FaultTO("Invalid application password");
            //}
            //Make sure we have all the args we need
            if (mySession == null || mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No session has been started");
            }
            else if (homeSitecode == "")
            {
                result.fault = new FaultTO("No homeSitecode");
            }
            else if (mySession.SiteTable.getSite(homeSitecode) == null)
            {
                result.fault = new FaultTO("No site " + homeSitecode + " in the site table");
            }
            else if (mySession.ConnectionSet != null &&
                     mySession.ConnectionSet.Count > 0 &&
                     mySession.ConnectionSet.HasConnection(homeSitecode))
            {
                result.fault = new FaultTO("Site " + homeSitecode + " already connected");
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0)
            {
                result.fault = new FaultTO("This session has pre-existing connections and this method should be the base connection.", "Do a disconnect?");
            }
            else if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("No MPI PID");
            }
            if (result.fault != null)
            {
                return result;
            }

            Site homeSite = mySession.SiteTable.getSite(homeSitecode);
            mySession.Credentials = getAdministrativeCredentials(homeSite);

            mySession.Credentials.SecurityPhrase = pwd;

            string context = MdwsConstants.MDWS_CONTEXT;
            if (mySession.DefaultVisitMethod == MdwsConstants.NON_BSE_CREDENTIALS)
            {
                context = MdwsConstants.CPRS_CONTEXT;
            }
            mySession.PrimaryPermission = new MenuOption(context);

            try
            {
                mySession.User = doTheVisit(homeSitecode, mySession.Credentials, mySession.PrimaryPermission);

                PatientApi patientApi = new PatientApi();
                string localPid = patientApi.getLocalPid(myCxn, mpiPid);
                if (String.IsNullOrEmpty(localPid))
                {
                    myCxn.disconnect();
                    result.fault = new FaultTO("No such patient at this site");
                    return result;
                }

                mySession.Patient = patientApi.select(myCxn, localPid);
                addMyCxn2CxnSet();

                if (multiSite)
                {
                    setupMultiSourcePatientQuery(pwd, context);
                }
            }
            catch (Exception e)
            {
                myCxn.disconnect();
                result.fault = new FaultTO(e.Message);
                return result;
            }
            return result;
        }

        internal TaggedTextArray setupMultiSourceQuery(string pwd, StringDictionary siteIds, string context)
        {
            List<DataSource> sources = new List<DataSource>();
            if (siteIds == null || siteIds.Count == 0)
            {
                return setupMultiSourceQuery(pwd, sources, context);
            }

            foreach (KeyValuePair<string, string> kvp in siteIds)
            {
                DataSource src = new DataSource();
                src.SiteId = new SiteId(kvp.Key, kvp.Value);
                sources.Add(src);
            }
            return setupMultiSourceQuery(pwd, sources, context);
        }

        // This method is exposed to the svcs via visitSites
        internal TaggedTextArray setupMultiSourceQuery(string pwd, List<DataSource> sources, string context)
        {
            TaggedTextArray result = new TaggedTextArray();

            //Say the magic word
            // TBD - needed????
            //if (pwd != mySession.AppPwd)
            //{
            //    result.fault = new FaultTO("Invalid application password");
            //}
            if (sources == null || sources.Count == 0)
            {
                result.fault = new FaultTO("No sources");
            }
            else if (mySession.SiteTable == null)
            {
                result.fault = new FaultTO("No site table");
            }
            //else if (mySession.user == null)
            //{
            //    result.fault = new FaultTO("No user", "Need to login?");
            //}
            else if (mySession.Credentials == null)
            {
                result.fault = new FaultTO("No credentials", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (String.IsNullOrEmpty(context))
            {
                context = mySession.DefaultPermissionString;
            }
            if (mySession.PrimaryPermission == null || String.IsNullOrEmpty(mySession.PrimaryPermission.Name))
            {
                mySession.PrimaryPermission = new MenuOption(context);
            }

            try
            {
                mySession.ConnectionSet.ExcludeSite200 = mySession._excludeSite200;
                mySession.ConnectionSet.Add(sources, mySession.DefaultVisitMethod);
                mySession.Credentials.SecurityPhrase = pwd;
                DataSource validator = new DataSource() { ConnectionString = mySession.MdwsConfiguration.BseValidatorConnectionString };
                IndexedHashtable t = mySession.ConnectionSet.connect(mySession.Credentials, mySession.PrimaryPermission, validator);
                
                if (t.Count == 0)
                {
                    throw new Exception("Unable to connect to remote sites");
                }
                //mySession.user.PermissionString = context;
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result = new TaggedTextArray();
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        // This is the core visit method the others are using. The permission must have been set before
        // getting here.
        internal User doTheVisit(string sitecode, AbstractCredentials credentials, AbstractPermission permission)
        {
            Site site = mySession.SiteTable.getSite(sitecode);
            DataSource src = site.getDataSourceByModality("HIS");
            if (src == null)
            {
                throw new Exception("No HIS data source at site " + sitecode);
            }

            AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));
            myCxn = factory.getConnection(src);
            myCxn.Account.AuthenticationMethod = mySession.DefaultVisitMethod;

            if (!MdwsUtils.isValidCredentials(myCxn.Account.AuthenticationMethod, credentials, permission))
            {
                throw new Exception("Invalid credentials");
            }

            object result = null;
            if (myCxn.Account.AuthenticationMethod == VistaConstants.BSE_CREDENTIALS_V2WEB)
            {
                result = myCxn.authorizedConnect(credentials, permission, 
                    new DataSource() { ConnectionString = mySession.MdwsConfiguration.BseValidatorConnectionString });
            }
            else
            {
                result = myCxn.authorizedConnect(credentials, permission, null);
            }
            if (result.GetType().Name.EndsWith("Exception"))
            {
                throw (Exception)result;
            }
            else
            {
                return (User)result;
            }
        }

        internal void addMyCxn2CxnSet()
        {
            if (mySession.ConnectionSet == null)
            {
                mySession.ConnectionSet = new ConnectionSet(myCxn);
            }
            else
            {
                mySession.ConnectionSet.Add(myCxn);
            }
        }

        // This does an administrative visit in order to get the true user's data
        internal User getVisitorData(string userSitecode, string DUZ, string appPwd)
        {
            Site site = mySession.SiteTable.getSite(userSitecode);
            AbstractCredentials credentials = getAdministrativeCredentials(site);
            credentials.AuthenticationToken = userSitecode + '_' + credentials.LocalUid;
            credentials.SecurityPhrase = appPwd;

            string context = MdwsConstants.MDWS_CONTEXT;
            if (mySession.DefaultVisitMethod == MdwsConstants.NON_BSE_CREDENTIALS)
            {
                context = MdwsConstants.CPRS_CONTEXT;
            }

            // Here we do NOT set mySession.PrimaryPermission.  This context is being set
            // solely for the Admin user to get the true user's credentials.  mySession.PrimaryPermission
            // is for the true user.
            AbstractPermission permission = new MenuOption(context);
            permission.IsPrimary = true;

            User u = doTheVisit(userSitecode, credentials, permission);

            UserApi userApi = new UserApi();
            User trueUser = userApi.getUser(myCxn, DUZ);
            myCxn.disconnect();
            return trueUser;
        }

        internal AbstractCredentials getAdministrativeCredentials(Site site)
        {
            AbstractCredentials credentials = new VistaCredentials();
            credentials.LocalUid = VistaAccount.getAdminLocalUid(site.Id);
            credentials.FederatedUid = "123456789";
            credentials.SubjectName = "DEPARTMENT OF DEFENSE,USER";
            credentials.SubjectPhone = "";
            credentials.AuthenticationSource = site.getDataSourceByModality("HIS");
            credentials.AuthenticationToken = site.Id + '_' + credentials.LocalUid;
            return credentials;
        }
    }
}
