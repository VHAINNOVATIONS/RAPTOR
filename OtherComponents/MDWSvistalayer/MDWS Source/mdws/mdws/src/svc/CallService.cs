using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

/// <summary>
/// Summary description for CallService
/// </summary>
namespace gov.va.medora.mdws
{
	/// <summary>
	/// Summary description for CallService
	/// </summary>
	[WebService(Namespace = "http://mdws.medora.va.gov/CallService")]
	[WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
	[ToolboxItem(false)]
	public partial class CallService : BaseService
    {
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

        [WebMethod(EnableSession = true, Description = "Use when switching patient lookup sites.")]
        public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (TaggedTextArray)MySession.execute("AccountLib", "visitSites", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
        }

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get single site")]
        public SiteTO getSite(string sitecode)
        {
            return (SiteTO)MySession.execute("SitesLib", "getSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Get VHA sites by states")]
        public StateArray getVhaByStates()
        {
            return (StateArray)MySession.execute("SitesLib", "getVhaByStates", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get VHA sites by states")]
        public ClosestFacilityTO getNearestFacility(string city, string stateAbbr)
        {
            return (ClosestFacilityTO)MySession.execute("SitesLib", "getNearestFacility", new object[] { city, stateAbbr });
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

        [WebMethod(EnableSession = true, Description = "Get a VistA's hospital locations (clinics, etc.).")]
        public TaggedHospitalLocationArray getLocations(string target, string direction)
        {
            return (TaggedHospitalLocationArray)MySession.execute("EncounterLib", "getLocations", new object[] { target, direction });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get VHA sites by states")]
        public ZipcodeTO[] getCitiesInState(string stateAbbr)
        {
            return (ZipcodeTO[])MySession.execute("SitesLib", "getCitiesInState", new object[] { stateAbbr });
        }

        [WebMethod(EnableSession = true, Description = "Match by name, city, state at logged in site.")]
        public TaggedPatientArray matchByNameCityState(string name, string city, string stateAbbr)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "matchByNameCityState", new object[] { name, city, stateAbbr });
        }

        [WebMethod(EnableSession = true, Description = "Lookup a patient from MPI.")]
        public PatientArray mpiLookup(string SSN)
        {
            return (PatientArray)MySession.execute("PatientLib", "mpiLookup", new object[] { SSN, "", "", "", "", "", "" });
        }

        [WebMethod(EnableSession = true, Description = "Lookup a patient from NPT.")]
        public PatientArray nptLookup(string SSN)
        {
            return (PatientArray)MySession.execute("PatientLib", "nptLookup", new object[] { SSN, "", "", "", "", "", "" });
        }

        [WebMethod(EnableSession = true, Description = "Get a VistA's TIU note titles.")]
        public TaggedTextArray getNoteTitles(string target, string direction)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getNoteTitles", new object[] { target, direction });
        }

        [WebMethod(EnableSession = true, Description = "Get selected patient's admissions")]
        public TaggedInpatientStayArray getAdmissions()
        {
            return (TaggedInpatientStayArray)MySession.execute("EncounterLib", "getAdmissions", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get selected patient's visits")]
        public TaggedVisitArray getVisits(string fromDate, string toDate)
        {
            return (TaggedVisitArray)MySession.execute("EncounterLib", "getVisits", new object[] { fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Lookup a CPRS-enabled user")]
        public UserArray cprsUserLookup(string target)
        {
            return (UserArray)MySession.execute("UserLib", "cprsUserLookup", new object[] { target });
        }

        [WebMethod(EnableSession = true, Description = "Is given note a surgery note?")]
        public TaggedText isSurgeryNote(string noteDefinitionIEN)
        {
            return (TaggedText)MySession.execute("NoteLib", "isSurgeryNote", new object[] { noteDefinitionIEN });
        }

        [WebMethod(EnableSession = true, Description = "Is given note a surgery note?")]
        public TaggedText isOneVisitNote(string noteDefinitionIEN, string noteTitle, string visitString)
        {
            return (TaggedText)MySession.execute("NoteLib", "isOneVisitNote", new object[] { noteDefinitionIEN, noteTitle, visitString });
        }

        [WebMethod(EnableSession = true, Description = "Is given note a consult note?")]
        public TextTO isConsultNote(string noteDefinitionIEN)
        {
            return (TextTO)MySession.execute("NoteLib", "isConsultNote", new object[] { noteDefinitionIEN });
        }

        [WebMethod(EnableSession = true, Description = "Is given note a PRF note?")]
        public TextTO isPrfNote(string noteDefinitionIEN)
        {
            return (TextTO)MySession.execute("NoteLib", "isPrfNote", new object[] { noteDefinitionIEN });
        }

        [WebMethod(EnableSession = true, Description = "Is cosigner required for this user/author pair?")]
        public TextTO isCosignerRequired(string noteDefinitionIEN, string authorDUZ)
        {
            return (TextTO)MySession.execute("NoteLib", "isCosignerRequired", new object[] { noteDefinitionIEN, authorDUZ });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's current consults.")]
        public TaggedConsultArray getConsultsForPatient()
        {
            return (TaggedConsultArray)MySession.execute("TbiLib", "getConsultsForPatient", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient record flag actions.")]
        public PatientRecordFlagArray getPrfNoteActions(string noteDefinitionIEN)
        {
            return (PatientRecordFlagArray)MySession.execute("NoteLib", "getPrfNoteActions", new object[] { noteDefinitionIEN });
        }

        [WebMethod(EnableSession = true, Description = "Write a note.")]
        public NoteResultTO writeNote(
            string titleIEN,
            string encounterString,
            string text,
            string authorDUZ,
            string cosignerDUZ,
            string consultIEN,
            string prfIEN)
        {
            NoteLib lib = new NoteLib(MySession);
            return lib.writeNote(titleIEN, encounterString, text, authorDUZ, cosignerDUZ, consultIEN, prfIEN);
        }

        [WebMethod(EnableSession = true, Description = "Sign a note.")]
        public TextTO signNote(string noteIEN, string userDUZ, string esig)
        {
            NoteLib lib = new NoteLib(MySession);
            return lib.signNote(noteIEN, userDUZ, esig);
        }

        [WebMethod(EnableSession = true, Description = "Close a note.")]
        public TextTO closeNote(string noteIEN, string consultIEN)
        {
            NoteLib lib = new NoteLib(MySession);
            return lib.closeNote(noteIEN, consultIEN);
        }

    }

    interface ICallService
    {
        RegionArray getVHA();
        SiteTO getSite(string sitecode);
        StateArray getVhaByStates();
        DataSourceArray connect(string sitelist);
        TaggedTextArray disconnect();
        UserTO login(String username, String pwd, String context);
        TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context);
        TaggedPatientArrays match(string target);
        PatientTO select(string DFN);
        TaggedTextArray getLocations(string target, string direction);
        OrderTO sendConsult(string text);
        ZipcodeTO[] getCitiesInState(string stateAbbr);
    }
}

