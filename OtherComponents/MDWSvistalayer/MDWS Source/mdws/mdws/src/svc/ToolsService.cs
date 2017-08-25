using System;
using System.Data;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for ToolsService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/tools/ToolsService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class ToolsService : BaseService
    {
		public ToolsService()
		{
		}

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceTO connectSite(string sitecode)
        {
            return (DataSourceTO)MySession.execute("ConnectionLib", "connectSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Visit single VistA system.")]
        public UserTO visitSite(string pwd, string sitecode, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (UserTO)MySession.execute("ConnectionLib", "visitSite", new object[] { pwd, sitecode, userSitecode, userName, DUZ, SSN, context });
        }

        //[WebMethod(EnableSession = true, Description = "Visit multiple VistA systems.")]
        //public TaggedTextArray visitSites(string pwd, string sitelist, string context)
        //{
        //    ConnectionLib lib = new ConnectionLib(MySession);
        //    return lib.visitSites(pwd, sitelist, context);
        //}

        [WebMethod(EnableSession = true, Description = "Disconnect from single VistA system.")]
        public TextTO disconnectSite()
        {
            return (TextTO)MySession.execute("ConnectionLib", "disconnectSite", new object[] { });
        }

        //[WebMethod(EnableSession = true, Description = "Disconnect from multiple Vista systems.")]
        //public TaggedTextArray disconnectSites()
        //{
        //    ConnectionLib cxnLib = new ConnectionLib(MySession);
        //    return cxnLib.disconnectSites();
        //}

		[WebMethod(EnableSession = true, Description = "Is RPC available in context?")]
		public TextTO isRpcAvailable(string rpcName, string context)
		{
            return (TextTO)MySession.execute("ToolsLib", "isRpcAvailable", new object[] { rpcName, context });
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

        //[WebMethod(EnableSession = true)]
        //public TaggedTextArray ddrListerMS(
        //    string file,
        //    string iens,
        //    string fields,
        //    string flags,
        //    string maxrex,
        //    string from,
        //    string part,
        //    string xref,
        //    string screen,
        //    string identifier)
        //{
        //    ToolsLib lib = new ToolsLib(MySession);
        //    return lib.ddrListerMS(file, iens, fields, flags, maxrex, from, part, xref, screen, identifier);
        //}

		[WebMethod(EnableSession = true)]
		public TextTO getVariableValue(string arg)
		{
            return (TextTO)MySession.execute("ToolsLib", "getVariableValue", new object[] { arg });
		}

        [WebMethod(EnableSession = true)]
        public TextTO getLocalPid(string ICN)
        {
            return (TextTO)MySession.execute("PatientLib", "getLocalPid", new object[] { ICN });
        }

        [WebMethod(EnableSession = true)]
        public TextTO getLrDfn(string DFN)
        {
            return (TextTO)MySession.execute("LabsLib", "getLrDfn", new object[] { DFN });
        }

        [WebMethod(EnableSession = true)]
        public string visitSiteAsDodUser(string sitecode)
        {
            return (string)MySession.execute("ConnectionLib", "visitSiteAsDodUser", new object[] { sitecode,"" });
        }

    }
}
