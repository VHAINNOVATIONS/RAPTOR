using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws.emerse
{
    /// <summary>
    /// Summary description for EmerseService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/EmerseService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class EmerseService : BaseService
	{
        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib","getVHA",new object[]{});
        }

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceArray connect(string sitelist)
        {
            return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitelist });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
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

        [WebMethod(EnableSession = true, Description = "Visit one or more VistA systems.")]
		public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context)
		{
            return (TaggedTextArray)MySession.execute("AccountLib", "visitSites", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
        }

		[WebMethod(EnableSession = true, Description = "Get notes with text from all connected VistAs.")]
		public TaggedNoteArrays getNotesWithText(String fromDate, String toDate, int nNotes)
		{
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getNotesWithText", new object[] { fromDate, toDate, nNotes });
		}

        [WebMethod(EnableSession = true, Description = "Get problem list from all connected VistAs")]
        public TaggedProblemArrays getProblemList(string type)
        {
            return (TaggedProblemArrays)MySession.execute("ClinicalLib", "getProblemList", new object[] { type });
        }

        [WebMethod(EnableSession = true, Description = "Get all medications from all connected VistAs")]
        public TaggedMedicationArrays getAllMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getAllMeds", new object[] { });
        }

		[WebMethod(EnableSession = true, Description = "Get patient's outpatient prescription profile.")]
		public TaggedTextArray getOutpatientRxProfile()
		{
            return (TaggedTextArray)MySession.execute("MedsLib", "getOutpatientRxProfile", new object[] { });
		}

        [WebMethod(EnableSession = true, Description = "Get radiology reports from all connected VistAs.")]
        public TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrex)
        {
            return (TaggedRadiologyReportArrays)MySession.execute("ClinicalLib", "getRadiologyReports", new object[] { fromDate, toDate, nrex });
        }

        [WebMethod(EnableSession = true, Description = "Find a patient in the MPI")]
        public PatientArray mpiLookup(string SSN)
        {
            //return (PatientArray)MySession.execute("PatientLib", "mpiLookup", new object[] { SSN, "", "", "", "", "", "" });            
            return (PatientArray)MySession.execute("PatientLib", "mpiMatchSSN", new object[] { SSN });
        }

        [WebMethod(EnableSession = true, Description = "Find a patient in the NPT")]
        public PatientArray nptLookup(string SSN)
        {
            return (PatientArray)MySession.execute("PatientLib", "nptLookup", new object[] { SSN, "", "", "", "", "", "" });            
        }

        [WebMethod(EnableSession = true, Description = "Setup patient's remote sites for querying.")]
        public SiteArray setupMultiSiteQuery(string appPwd)
        {
            return (SiteArray)MySession.execute("AccountLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
        }

	}

	/// <summary>
	/// EmerseService Interface
	/// </summary>
	public interface IEmerseService
	{
		RegionArray getVHA();
		DataSourceArray connect(string sitelist);
		UserTO login(String username, String pwd, String context);
		TaggedTextArray disconnect();
		PersonsTO cprsLaunch(string pwd, String sitecode, String DUZ, String DFN);
		TaggedPatientArrays match(String target);
		PatientTO select(String DFN);
		TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string context);
		TaggedNoteArrays getNotesWithText(String fromDate, String toDate, int nNotes);
		TaggedProblemArrays getProblemList(string type, string fromDate, string toDate, int nrex);
		TaggedMedicationArrays getAllMeds(string fromDate, string toDate, int nrex);
		TaggedTextArray getOutpatientRxProfile();
		TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrex);
        PatientArray mpiLookup(string SSN);
        SiteArray setupMultiSiteQuery(string appPwd);
	}
}
