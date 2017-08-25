using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

/// <summary>
/// Summary description for ApolloService
/// </summary>
namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for ApolloService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/ApolloService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class ApolloService : BaseService
    {
        public ApolloService() {}

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
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
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

        [WebMethod(EnableSession = true, Description = "Visit site 200 from DoD app.")]
        public TaggedTextArray visit200(string pwd)
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "visitSite200", new object[] { });
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

        [WebMethod(EnableSession = true, Description = "Get latest vitals from all connected VistAs")]
        public TaggedVitalSignArrays getLatestVitalSigns()
        {
            return (TaggedVitalSignArrays)MySession.execute("ClinicalLib", "getLatestVitalSigns", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's vital signs.")]
        public TaggedVitalSignSetArrays getVitalSigns(string fromDate, string toDate)
        {
            return (TaggedVitalSignSetArrays)MySession.execute("ClinicalLib", "getVitalSigns", new object[] { fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get allergies from all connected VistAs")]
        public TaggedAllergyArrays getAllergies(string fromDate, string toDate, int nrex)
        {
            return (TaggedAllergyArrays)MySession.execute("ClinicalLib", "getAllergies", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get radiology reports from all connected VistAs")]
        public TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedRadiologyReportArrays)MySession.execute("ClinicalLib", "getRadiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get surgery reports from all connected VistAs")]
        public TaggedSurgeryReportArrays getSurgeryReports()
        {
            return (TaggedSurgeryReportArrays)MySession.execute("ClinicalLib", "getSurgeryReports", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get text for a certain surgery report")]
        public TextTO getSurgeryReportText(string siteId, string rptId)
        {
            return (TextTO)MySession.execute("ClinicalLib", "getSurgeryReportText", new object[] { siteId, rptId });
        }

        [WebMethod(EnableSession = true, Description = "Get problem list from all connected VistAs")]
        public TaggedProblemArrays getProblemList(string type, int nrex)
        {
            return (TaggedProblemArrays)MySession.execute("ClinicalLib", "getProblemList", new object[] { type });
        }

        [WebMethod(EnableSession = true, Description = "Get outpatient medications from all connected VistAs")]
        public TaggedMedicationArrays getOutpatientMeds(string fromDate, string toDate, int nrex)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOutpatientMeds", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get IV medications from all connected VistAs")]
        public TaggedMedicationArrays getIvMeds(string fromDate, string toDate, int nrex)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getIvMeds", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get unit dose medications from all connected VistAs")]
        public TaggedMedicationArrays getUnitDoseMeds(string fromDate, string toDate, int nrex)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getUnitDoseMeds", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get non-VA medications from all connected VistAs")]
        public TaggedMedicationArrays getNonVaMeds(string fromDate, string toDate, int nrex)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOtherMeds", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get all medications from all connected VistAs")]
        public TaggedMedicationArrays getAllMeds(string fromDate, string toDate, int nrex)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getAllMeds", new object[] { });
        }

        //[WebMethod(EnableSession = true, Description = "Get VA medications from all connected VistAs")]
        //public TaggedMedicationArrays getVaMeds()
        //{
        //    return (TaggedMedicationArrays)MySession.execute("MedsLib", "getVaMeds", new object[] { });
        //}

        [WebMethod(EnableSession = true, Description = "Get medication detail from a single connected VistA.")]
        public TextTO getVaMedicationDetail(string siteId, string medId)
        {
            return (TextTO)MySession.execute("MedsLib", "getMedicationDetail", new object[] { siteId, medId });
        }

        [WebMethod(EnableSession = true, Description = "Get signed note metadata from all connected VistAs")]
        public TaggedNoteArrays getSignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getSignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get unsigned note metadata from all connected VistAs")]
        public TaggedNoteArrays getUnsignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getUnsignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get uncosigned note metadata from all connected VistAs")]
        public TaggedNoteArrays getUncosignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getUncosignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get a note from a single connected VistA.")]
        public TextTO getNote(string siteId, string noteId)
        {
            return (TextTO)MySession.execute("NoteLib", "getNote", new object[] { siteId, noteId });
        }

        [WebMethod(EnableSession = true, Description = "Get notes with text from all connected VistAs.")]
        public TaggedNoteArrays getNotesWithText(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getNotesWithText", new object[] { fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedNoteArrays getDischargeSummaries(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getDischargeSummaries", new object[] { fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's current consults.")]
        public TaggedConsultArrays getConsultsForPatient()
        {
            return (TaggedConsultArrays)MySession.execute("OrdersLib", "getConsultsForPatient", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's appointments.")]
        public TaggedAppointmentArrays getAppointments()
        {
            return (TaggedAppointmentArrays)MySession.execute("EncounterLib", "getAppointments", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get note for appointment.")]
        public TextTO getAppointmentText(string siteId, string apptId)
        {
            return (TextTO)MySession.execute("EncounterLib", "getAppointmentText", new object[] { siteId, apptId });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's clinical warnings.")]
        public TaggedTextArray getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getClinicalWarnings", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's advance directives.")]
        public TaggedTextArray getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getAdvanceDirectives", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's crisis notes.")]
        public TaggedTextArray getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getCrisisNotes", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's immunizations.")]
        public TaggedTextArray getImmunizations(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getImmunizations", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's outpatient prescription profile.")]
        public TaggedTextArray getOutpatientRxProfile()
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getOutpatientRxProfile", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's meds administation history.")]
        public TaggedTextArray getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getMedsAdminHx", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's meds administation log.")]
        public TaggedTextArray getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getMedsAdminLog", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArrays getChemHemReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedChemHemRptArrays)MySession.execute("LabsLib", "getChemHemReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's Cytology lab results.")]
        public TaggedCytologyRptArrays getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedCytologyRptArrays)MySession.execute("LabsLib", "getCytologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's microbiology lab results.")]
        public TaggedMicrobiologyRptArrays getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedMicrobiologyRptArrays)MySession.execute("LabsLib", "getMicrobiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's surgical pathology lab results.")]
        public TaggedSurgicalPathologyRptArrays getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedSurgicalPathologyRptArrays)MySession.execute("LabsLib", "getSurgicalPathologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's blood availability reports.")]
        public TaggedTextArray getBloodAvailabilityReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodAvailabilityReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's blood transfusion reports.")]
        public TaggedTextArray getBloodTransfusionReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodTransfusionReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's blood bank reports.")]
        public TaggedTextArray getBloodBankReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodBankReports", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's electron microscopy reports.")]
        public TaggedTextArray getElectronMicroscopyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getElectronMicroscopyReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's cytopathology reports.")]
        public TaggedTextArray getCytopathologyReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getCytopathologyReports", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's autopsy reports.")]
        public TaggedTextArray getAutopsyReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getAutopsyReports", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getOutpatientEncounterReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getOutpatientEncounterReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getAdmissionsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getAdmissionsReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getExpandedAdtReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getExpandedAdtReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getDischargeDiagnosisReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getDischargeDiagnosisReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getDischargesReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getDischargesReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getTransfersReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib","getTransfersReports", new object[]{fromDate, toDate, nrpts});
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getFutureClinicVisitsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib","getFutureClinicVisitsReports", new object[]{fromDate, toDate, nrpts});
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getPastClinicVisitsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib","getPastClinicVisitsReports", new object[]{fromDate, toDate, nrpts});
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getTreatingSpecialtyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib","getTreatingSpecialtyReports", new object[]{fromDate, toDate, nrpts});
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getCompAndPenReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib","getCompAndPenReports", new object[]{fromDate, toDate, nrpts});
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedTextArray getCareTeamReports()
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getCareTeamReports", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedIcdRptArrays getIcdProceduresReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedIcdRptArrays)MySession.execute("EncounterLib", "getIcdProceduresReports", new object[] { fromDate, toDate, nrpts });
        }

        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedIcdRptArrays getIcdSurgeryReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedIcdRptArrays)MySession.execute("EncounterLib", "getIcdSurgeryReports", new object[] { fromDate, toDate, nrpts });
        }
    }
}

