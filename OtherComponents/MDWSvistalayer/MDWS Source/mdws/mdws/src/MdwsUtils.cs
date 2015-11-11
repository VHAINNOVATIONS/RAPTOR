using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Reflection;
using System.Xml;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo;
using gov.va.medora.utils;

/// <summary>
/// Summary description for MplUtils
/// </summary>

namespace gov.va.medora.mdws
{
    public class MdwsUtils
    {
        public MdwsUtils()
        {
            //
            // TODO: Add constructor logic here
            //
        }

        public static SiteTable buildSiteTable(String filepath)
        {
            return new SiteTable(filepath);
        }

        //public static DataSource[] getHisSources(Site[] sites)
        //{
        //    Dictionary<String, DataSource> lst = buildHisList(sites);
        //    DataSource[] result = new DataSource[lst.Count];
        //    lst.Values.CopyTo(result, 0);
        //    return result;
        //}

        //public static DataSource[] getHisSources(Connection localCxn, Site[] sites)
        //{
        //    Dictionary<String, DataSource> lst = buildHisList(sites);
        //    if (!lst.ContainsKey(localCxn.DataSource.SiteId.Id))
        //    {
        //        lst.Add(localCxn.DataSource.SiteId.Id, localCxn.DataSource);
        //    }
        //    DataSource[] result = new DataSource[lst.Count];
        //    lst.Values.CopyTo(result, 0);
        //    return result;
        //}

        private static Dictionary<String, DataSource> buildHisList(Site[] sites)
        {
            Dictionary<String, DataSource> lst = new Dictionary<String, DataSource>(sites.Length);
            for (int i = 0; i < sites.Length; i++)
            {
                DataSource src = sites[i].getDataSourceByModality("HIS");
                if (src != null && !lst.ContainsKey(sites[i].Id))
                {
                    lst.Add(sites[i].Id, src);
                }
            }
            return lst;
        }

        /// <summary>
        /// Replaces special characters in strings.
        /// </summary>
        /// <remarks>
        /// * Behaves like XmlTextWriter.WriteString(), but also replaces
        ///   the apostrophe and double-quote characters -- which WriteString()
        ///   does not, unless it is in an attribute context...
        /// 
        /// * PERFORMANCE NOTE: About 2.5 s slower than old method over the course
        ///   of 1 million runs. TBD see if there is a good way for us to reuse
        ///   the StringWriter and XmlTextWriter so that we don't have to keep
        ///   recreating them. Maybe a factory method?
        /// </remarks>
        /// <param name="s"></param>
        /// <returns></returns>
        public static string replaceSpecialXmlChars(string s)
        {
            // this takes care of all of the characters except ' and " because
            // we are not processing within the context of an attribute
            // 
            System.IO.StringWriter stringWriter = new System.IO.StringWriter();
            using (XmlWriter writer = new XmlTextWriter(stringWriter))
            {
                writer.WriteString(s);
                stringWriter.Close();
                writer.Close();
            }
            string result = stringWriter.ToString();
            result = result.Replace("\"", "&quot;");
            result = result.Replace("'", "&apos;");
            return result;
        }

        public static User getApplicationProxyUser()
        {
            User result = new User();
            result.setSSN("123456789");
            result.setName("DEPARTMENT OF DEFENSE,USER");
            result.Uid = "31066";
            result.LogonSiteId = new SiteId("200", "DoD");
            result.PermissionString = VistaConstants.CAPRI_CONTEXT;
            return result;
        }

        public static Site[] parseSiteList(SiteTable siteTable, String siteList)
        {
            if (siteList == null || siteList == "")
            {
                throw new ArgumentException("Invalid sitelist: no sites specified");
            }
            ArrayList lst = new ArrayList();
            if (siteList == "*")
            {
                IEnumerator e = siteTable.Sites.Values.GetEnumerator();
                while (e.MoveNext())
                {
                    lst.Add((Site)e.Current);
                }
                return (Site[])lst.ToArray(typeof(Site));
            }

            siteList = siteList.ToUpper();
            String[] items = StringUtils.split(siteList, StringUtils.COMMA);
            Hashtable siteIds = new Hashtable();
            for (int i = 0; i < items.Length; i++)
            {
                items[i] = items[i].Trim();
                if (items[i] == "*")
                {
                    throw new ArgumentException("Invalid sitelist: * must be only arg");
                }
                else if (items[i][0] == 'V')
                {
                    Region visn = siteTable.getRegion(items[i].Substring(1));
                    if (visn == null)
                    {
                        throw new ArgumentException("Invalid sitelist: invalid VISN: " + visn);
                    }
                    ArrayList sites = visn.Sites;
                    for (int j = 0; j < sites.Count; j++)
                    {
                        lst.Add((Site)sites[j]);
                    }
                }
                else
                {
                    Site site = siteTable.getSite(items[i]);
                    if (site == null)
                    {
                        throw new ArgumentException("Invalid sitelist: nonexistent sitecode: " + items[i]);
                    }
                    if (siteIds.ContainsKey(items[i]))   // duplicate sitecode - skip it
                    {
                        throw new ArgumentException("Invalid sitelist: duplicate sitecode: " + items[i]);
                    }
                    lst.Add(siteTable.getSite(items[i]));
                    siteIds.Add(items[i], "");
                }
            }
            return (Site[])lst.ToArray(typeof(Site));
        }

        internal static string sessionHasAuthorizedConnection(MySession mySession)
        {
            if (mySession == null)
            {
                return "No session";
            }
            if (mySession.ConnectionSet == null)
            {
                return "There are no open connections";
            }
            if (mySession.ConnectionSet.BaseConnection is VistaPoolConnection)
            {
                return "OK";
            }
            if (mySession.ConnectionSet.BaseConnection is mdo.dao.sql.cdw.CdwConnection)
            {
                return "OK";
            }
            if (!mySession.ConnectionSet.HasBaseConnection ||
                StringUtils.isEmpty(mySession.Credentials.AuthenticationToken))
            {
                return "There is no logged in connection";
            }
            return "OK";
        }

        public static string isAuthorizedConnection(MySession mySession)
        {
            return isAuthorizedConnection(mySession, null);
        }

        public static string isAuthorizedConnection(MySession mySession, string sitecode)
        {
            string msg = sessionHasAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                return msg;
            }

            // At this point we know there is a session, it has a connection manager, the 
            // connection manager has a logged in site and the logged in site has a user ID.
            if (sitecode == null)   // null sitecode means caller had no sitecode arg
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }
            if (sitecode == "")
            {
                return "Missing sitecode";
            }
            if (mySession.SiteTable == null)
            {
                return "No site table";
            }
            if (mySession.SiteTable.getSite(sitecode) == null)
            {
                return "No such site in site table";
            }
            //if (!mySession.cxns.IsAuthorizedForSite(sitecode))
            //{
            //    return "No authorized connection for this site";
            //}
            return "OK";
        }

        public static AbstractCredentials setVisitCredentials(
            string duz,
            string ssn,
            string username,
            string userphone,
            DataSource authenticatingSource,
            string pwd)
        {
            AbstractCredentials credentials = new gov.va.medora.mdo.dao.vista.VistaCredentials();
            credentials.LocalUid = duz;
            credentials.FederatedUid = ssn;
            credentials.SubjectName = username;
            credentials.SubjectPhone = userphone;
            credentials.AuthenticationSource = authenticatingSource;
            credentials.AuthenticationToken = authenticatingSource.SiteId.Id + '_' + duz;
            credentials.SecurityPhrase = pwd;
          
            return credentials;
        }

        public static bool isValidCredentials(string authenticationMethod, AbstractCredentials credentials, AbstractPermission permission)
        {
            if (credentials == null)
            {
                return false;
            }
            if (authenticationMethod == MdwsConstants.LOGIN_CREDENTIALS)
            {
                if (String.IsNullOrEmpty(credentials.AccountName) ||
                    String.IsNullOrEmpty(credentials.AccountPassword))
                {
                    return false;
                }
            }
            else if (authenticationMethod == MdwsConstants.NON_BSE_CREDENTIALS)
            {
                if (String.IsNullOrEmpty(credentials.LocalUid) ||
                    String.IsNullOrEmpty(credentials.FederatedUid) ||
                    String.IsNullOrEmpty(credentials.SubjectName) ||
                    credentials.AuthenticationSource == null ||
                    credentials.AuthenticationSource.SiteId == null ||
                    String.IsNullOrEmpty(credentials.AuthenticationSource.SiteId.Id) ||
                    String.IsNullOrEmpty(credentials.AuthenticationSource.SiteId.Name) ||
                    String.IsNullOrEmpty(credentials.AuthenticationToken))
                {
                    return false;
                }
            }
            else if (authenticationMethod == MdwsConstants.BSE_CREDENTIALS_V2WEB)
            {
                if (String.IsNullOrEmpty(credentials.LocalUid) ||
                    String.IsNullOrEmpty(credentials.FederatedUid) ||
                    String.IsNullOrEmpty(credentials.SubjectName) ||
                    credentials.AuthenticationSource == null ||
                    credentials.AuthenticationSource.SiteId == null ||
                    String.IsNullOrEmpty(credentials.AuthenticationSource.SiteId.Id) ||
                    String.IsNullOrEmpty(credentials.AuthenticationSource.SiteId.Name) ||
                    String.IsNullOrEmpty(credentials.AuthenticationToken) ||
                    String.IsNullOrEmpty(credentials.SecurityPhrase))
                {
                    return false;
                }
            }
            else
            {
                throw new ArgumentException("Invalid credential type");
            }
            if (permission == null || String.IsNullOrEmpty(permission.Name))
            {
                return false;
            }
            return true;
        }

        /// <summary>
        /// Gets a list of all the web services (base type of BaseService or System.Web.Services.WebService)
        /// in the MDWS assembly
        /// </summary>
        /// <returns>A generic list of all the web services</returns>
        public static IList<Type> getMdwsServices()
        {
            Type[] allTypes = Assembly.GetExecutingAssembly().GetTypes();
            if (allTypes != null)
            {
                IList<Type> webServices = new List<Type>();
                foreach (Type type in allTypes)
                {
                    if (type.BaseType == typeof(gov.va.medora.mdws.BaseService) ||
                        type.BaseType == typeof(System.Web.Services.WebService))
                    {
                        webServices.Add(type);
                    }
                }
                return webServices;
            }
            return new List<Type>();
        }

        /// <summary>
        /// The TO objects need to determine if an object is derived from an exception. The most
        /// reliable way to do this is simply to look at the method name and see if it ends with
        /// the text: Exception
        /// </summary>
        /// <param name="obj">The object to check if it is an exception</param>
        /// <returns>True if obj is an exception, False if obj is ny other type of object</returns>
        public static bool isException(object obj)
        {
            try
            {
                if (obj == null)
                {
                    return false;
                }
                // TBD: should we really be checking the object's name? seems awfully kludgy. Maybe a better way to check 
                // if the object derives from the Exception class?
                if (obj.GetType().Name.EndsWith("Exception"))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            catch (Exception)
            {
                return false;
            }
        }

        public static Dictionary<string, object> getArgsDictionary(ParameterInfo[] paramInfo, IList<object> passedArgs)
        {
            Dictionary<string, object> result = new Dictionary<string, object>();
            foreach (ParameterInfo pi in paramInfo)
            {
                if (pi.DefaultValue == null) // if parameter defaults to null - don't include
                {
                    continue;
                }
                result.Add(pi.Name, passedArgs[pi.Position]);
            }
            return result;
        }

        public static void checkNullArgs(Dictionary<string, object> args)
        {
            foreach (string key in args.Keys)
            {
                if (args[key] is String)
                {
                    if (String.IsNullOrEmpty((String)args[key]))
                    {
                        throw new ArgumentNullException("Must supply {0}", key);
                    }
                }
                // TODO - implement other types?
            }
        }

        public static void checkSiteTable(MySession mySession)
        {
            if (mySession == null || mySession.SiteTable == null)
            {
                throw new ConfigurationErrorsException("No site table defined in session");
            }
        }

        public static void checkSiteTable(MySession mySession, string siteCode)
        {
            if (mySession == null || mySession.SiteTable == null)
            {
                throw new ConfigurationErrorsException("No site table defined in session");
            }
            if (String.IsNullOrEmpty(siteCode) || mySession.SiteTable.getSite(siteCode) == null)
            {
                throw new ConfigurationErrorsException("Invalid site - missing or not found in site table");
            }
        }

        public static void checkConnections(MySession mySession, string siteCode)
        {
            checkSiteTable(mySession, siteCode);
            if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0 && mySession.ConnectionSet.HasConnection(siteCode))
            {
                throw new ArgumentException("Already connected to that site");
            }
        }
    }
}