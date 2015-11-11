using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdws.dto.vista.mgt;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for QuerySvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/QuerySvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public class QuerySvc : BaseService
    {
        [WebMethod(EnableSession = true, Description = "Create a new record in a Vista file")]
        public TextTO create(String jsonDictionaryFieldsAndValues, String file, String parentRecordIdString)
        {
            return (TextTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<String, String, String, TextTO>(new ToolsLib(this.MySession).create),
                new object[] { jsonDictionaryFieldsAndValues, file, parentRecordIdString });
        }

        [WebMethod(EnableSession = true, Description = "Read a record from a Vista file")]
        public VistaRecordTO read(String recordId, String fields, String file)
        {
            return (VistaRecordTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession,
                new Func<String, String, String, VistaRecordTO>(new ToolsLib(this.MySession).read),
                new object[] { recordId, fields, file });
        }

        [WebMethod(EnableSession = true, Description = "Update a record in a Vista file")]
        public TextTO update(String jsonDictionaryFieldsAndValues, String recordId, String file)
        {
            return (TextTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<String, String, String, TextTO>(new ToolsLib(this.MySession).update),
                new object[] { jsonDictionaryFieldsAndValues, recordId, file });
        }

        [WebMethod(EnableSession = true, Description = "Delete a record in a Vista file")]
        public TextTO delete(String recordId, String file)
        {
            return (TextTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<String, String, TextTO>(new ToolsLib(this.MySession).delete),
                new object[] { recordId, file });
        }

        [WebMethod(EnableSession = true, Description = "Get Vista file data dictionary")]
        public VistaFileTO getFile(string fileNumber, bool includeXRefs)
        {
            return (VistaFileTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, bool, VistaFileTO>
                (new ToolsLib(this.MySession).getFile), new object[] { fileNumber, includeXRefs });
        }

        [WebMethod(EnableSession = true, Description = "Get Vista file cross references")]
        public XRefArray getXRefs(string fileNumber)
        {
            return (XRefArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, XRefArray>
                (new ToolsLib(this.MySession).getXRefs), new object[] { fileNumber });
        }

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        //[WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        //public DataSourceArray connect(string sitelist)
        //{
        //    return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitelist });
        //}

        //[WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        //public UserTO login(string username, string pwd, string context)
        //{
        //    return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        //}

        //[WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        //public TaggedTextArray disconnect()
        //{
        //    return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
        //}

        [WebMethod(EnableSession = true, Description = "Match patients at logged in site.")]
        public TaggedPatientArrays match(string target)
        {
            return (TaggedPatientArrays)MySession.execute("PatientLib", "match", new object[] { target });
        }

        [WebMethod(EnableSession = true, Description = "Select a patient at logged in site.")]
        public PatientTO select(string DFN)
        {
            return (PatientTO)MySession.execute("PatientLib", "select", new object[] { DFN });
        }

        [WebMethod(EnableSession = true)]
        public TextTO getVariableValue(string arg)
        {
            return (TextTO)MySession.execute("ToolsLib", "getVariableValue", new object[] { arg });
        }

        [WebMethod(EnableSession = true)]
        public TextArray ddrLister(
            string file,
            string iens,
            string fields,
            string flags,
            string maxrex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            return (TextArray)MySession.execute("ToolsLib", "ddrLister", new object[] { file, iens, fields, flags, maxrex, from, part, xref, screen, identifier });
        }

        [WebMethod(EnableSession = true, Description = "Lookup a user of CPRS")]
        public UserArray cprsUserLookup(string target)
        {
            return (UserArray)MySession.execute("UserLib", "cprsUserLookup", new object[] { target });
        }

        [WebMethod(EnableSession = true, Description = "Lookup any user by DUZ")]
        public UserTO userLookup(string duz)
        {
            return (UserTO)MySession.execute("UserLib", "userLookup", new object[] { duz });
        }

        [WebMethod(EnableSession = true, Description = "Visit single VistA system.")]
        public UserTO visitSite(string pwd, string sitecode, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "visitAndAuthorize", new object[] { pwd, sitecode, userSitecode, userName, DUZ, SSN, context });
        }

        [WebMethod(EnableSession = true, Description = "DDR GETS ENTRY DATA")]
        public TextArray ddrGetsEntry(string file, string iens, string flds, string flags)
        {
            return (TextArray)MySession.execute("ToolsLib", "ddrGetsEntry", new object[] { file, iens, flds, flags });
        }

        [WebMethod(EnableSession = true, Description = "Does this VistA have this patch installed?")]
        public TaggedText siteHasPatch(string patchId)
        {
            return (TaggedText)MySession.execute("ConnectionLib", "siteHasPatch", new object[] { patchId });
        }

        [WebMethod(EnableSession = true, Description = "Do these VistAs have this patch installed?")]
        public TaggedTextArray sitesHavePatch(string sitelist, string patchId)
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "sitesHavePatch", new object[] { sitelist, patchId });
        }

        [WebMethod(EnableSession = true, Description = "Execute Rpc")]
        public TaggedTextArray runRpc(string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {
            return (TaggedTextArray)MySession.execute("ToolsLib", "runRpc", new object[] { rpcName, paramValues, paramTypes, paramEncrypted });
        }

        [WebMethod(EnableSession = true, Description = "Get list of sites")]
        public TaggedTextArray getSites()
        {
            return (TaggedTextArray)MySession.execute("SitesLib", "runRpc", new object[] { });
        }
    }
}
