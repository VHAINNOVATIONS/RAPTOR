using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for QueSvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/QueSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class QueSvc : BaseService
    {
        [WebMethod(EnableSession = true, Description = "Get a questionnaire set")]
        public QuestionnaireSetTO getQuestionnaireSet(string name)
        {
            return (QuestionnaireSetTO)MySession.execute("QuestionnaireLib", "getQuestionnaireSet", new object[] { name });
        }

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceArray connect(string sitelist)
        {
            return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitelist });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("UserLib", "login", new object[] { username, pwd, context });
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

        [WebMethod(EnableSession = true, Description = "Use when switching patient lookup sites.")]
        public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "visit", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
        }

        [WebMethod(EnableSession = true, Description = "Call when launching from CPRS Tools menu: connects, authenticates, selects patient.")]
        public PersonsTO cprsLaunch(string pwd, string sitecode, string DUZ, string DFN)
        {
            return (PersonsTO)MySession.execute("ConnectionLib", "cprsLaunch", new object[] { pwd, sitecode, DUZ, DFN });
        }

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

        [WebMethod(EnableSession = true, Description = "Setup patient's remote sites for querying.")]
        public SiteArray setupMultiSiteQuery(string appPwd)
        {
            //SiteArray result = new SiteArray();
            //Object rtn = MySession.execute("ConnectionLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
            //if (rtn.GetType().IsAssignableFrom(typeof(System.Exception)))
            //{
            //    result.fault = new FaultTO(((Exception)rtn).Message);    
            //}
            //return (SiteArray)result;
            return (SiteArray)MySession.execute("ConnectionLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
        }

        [WebMethod(EnableSession = true, Description = "Get patient confidentiality from all connected sites.")]
        public TaggedTextArray getConfidentiality()
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "getConfidentiality", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient confidentiality from all connected sites.")]
        public TaggedTextArray issueConfidentialityBulletin()
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "issueConfidentialityBulletin", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get problem list from all connected VistAs")]
        public TaggedProblemArrays getFluRelatedProblemList()
        {
            return (TaggedProblemArrays)MySession.execute("ClinicalLib", "getFluRelatedProblemList", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Make a patient inquiry call (address, contact numbers, NOK, etc. information)")]
        public TextTO patientInquiry()
        {
            return (TextTO)MySession.execute("PatientLib", "patientInquiry", new object[] { });
        }

    }
}
