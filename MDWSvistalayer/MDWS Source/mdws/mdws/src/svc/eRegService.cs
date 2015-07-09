using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws.ereg
{
    /// <summary>
    /// Summary description for eRegService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/eRegService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class eRegService : BaseService
    {
        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceArray connect(string sitecode)
        {
            return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("UserLib", "login", new object[] { username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Get patient types for site.")]
		public TaggedTextArray getPatientTypes()
		{
            return (TaggedTextArray)MySession.execute("ConnectionLib", "getPatientTypes", new object[] { });
		}

		[WebMethod(EnableSession = true, Description = "Register patient at site.")]
		public TextArray addPatient(
			string sitecode,
			string name,
			string SSN,
			string DOB,
			string gender,
			string typeIEN,
			string type,
			bool isServiceConnected,
			bool isVeteran
			)
		{
            return (TextArray)MySession.execute(
                "PatientLib", 
                "addPatient", 
                new object[] { 
                    sitecode, name, SSN, DOB, gender, typeIEN, type, isServiceConnected, isVeteran 
                });
		}

		[WebMethod(EnableSession = true, Description = "Unregister patient at site.")]
		public TextTO removePatient(string sitecode, string dfn)
		{
            return (TextTO)MySession.execute("PatientLib", "removePatient", new object[] { sitecode, dfn });
		}

        [WebMethod(EnableSession = true, Description = "Select a patient at logged in site.")]
        public PatientTO select(string DFN)
        {
            return (PatientTO)MySession.execute("PatientLib", "select", new object[] { DFN });
        }

        [WebMethod(EnableSession = true, Description = "Use when switching patient lookup sites.")]
        public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "visit", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from remote Vista systems.")]
        public TaggedTextArray disconnectRemoteSites()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectRemoteSites", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get VHA sites by states")]
        public StateArray getStates()
        {
            return (StateArray)MySession.execute("SitesLib", "getStates", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get VHA sites by states")]
        public ZipcodeTO[] getCitiesInState(string stateAbbr)
        {
            return (ZipcodeTO[])MySession.execute("SitesLib", "getCitiesInState", new object[] { stateAbbr });
        }

    }
}
