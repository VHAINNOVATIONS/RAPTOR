using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;
using System.ServiceModel;
using gov.va.medora.mdws.dto.ldap;
using System.Collections.Generic;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for EmrSvc
    /// </summary>
    [ServiceContract(Namespace = "http://mdws.medora.va.gov/EmrSvc")]
    [WebService(Namespace = "http://mdws.medora.va.gov/EmrSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class EmrSvc : BaseService
    {
        /// <summary>
        /// This version should be incremented accordingly (minor for bugfixes, major for API changes, version for contract changes) as the facade is changed
        /// </summary>
        public const string VERSION = "2.0.0";

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Create a new imaging order in Vista")]
        public OrderTO saveNewRadiologyOrder(String patientId,
            String duz,
            String locationIEN,
            String dlgDisplayGroupId,
            String orderableItemIen,
            String urgencyCode,
            String modeCode,
            String classCode,
            String contractSharingIen,
            String submitTo,
            String pregnant,
            String isolation, 
            String reasonForStudy,
            String clinicalHx,
            String startDateTime,
            String preOpDateTime,
            String modifierIds,
            String eSig,
            String orderCheckOverrideReason)
        {
            return (OrderTO)MySession.execute("RadiologyLib", "saveNewRadiologyOrder", new object[] 
            {
                patientId,
                duz,
                locationIEN,
                dlgDisplayGroupId,
                orderableItemIen,
                urgencyCode,
                modeCode,
                classCode,
                contractSharingIen,
                submitTo,
                pregnant,
                isolation, 
                reasonForStudy,
                clinicalHx,
                startDateTime,
                preOpDateTime,
                modifierIds,
                eSig,
                orderCheckOverrideReason
            });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get imaging order types")]
        public List<OrderTypeTO> getImagingOrderTypes()
        {
            return (List<OrderTypeTO>)MySession.execute("RadiologyLib", "getImagingOrderTypes", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get imaging order types")]
        public List<OrderTypeTO> getOrderableItems(String dialogId)
        {
            return (List<OrderTypeTO>)MySession.execute("RadiologyLib", "getOrderableItems", new object[] { dialogId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get order checks")]
        public List<OrderCheckTO> getOrderChecks(String patientId, String orderStartDateTime, String locationId, String orderableItem)
        {
            return (List<OrderCheckTO>)MySession.execute("RadiologyLib", "getOrderChecks", new object[] { patientId, orderStartDateTime, locationId, orderableItem });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get radiology oder dialog")]
        public RadiologyOrderDialogTO getRadiologyOrderDialog(String patientId, String dialogId)
        {
            return (RadiologyOrderDialogTO)MySession.execute("RadiologyLib", "getRadiologyOrderDialog", new object[] { patientId, dialogId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Discontinue a radiology order")]
        public OrderTO discontinueRadiologyOrder(String patientId, String orderIen, String providerDuz, String locationIen, String reasonIen)
        {
            return (OrderTO)MySession.execute("RadiologyLib", "discontinueRadiologyOrder", new object[] { patientId, orderIen, providerDuz, locationIen, reasonIen });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Discontinue and sign a radiology order")]
        public OrderTO discontinueAndSignRadiologyOrder(String patientId, String orderIen, String providerDuz, String locationIen, String reasonIen, String eSig)
        {
            return (OrderTO)MySession.execute("RadiologyLib", "discontinueAndSignRadiologyOrder",
                new object[] { patientId, orderIen, providerDuz, locationIen, reasonIen, eSig });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Sign a radiology order")]
        public TextTO signOrder(String orderId, String providerDuz, String locationIen, String eSig)
        {
            return (TextTO)MySession.execute("RadiologyLib", "signOrder", new object[] { orderId, providerDuz, locationIen, eSig });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get contrast media")]
        public TaggedTextArray getContrastMedia()
        {
            return (TaggedTextArray)MySession.execute("RadiologyLib", "getContrastMedia", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get list of Radiology complications")]
        public TaggedTextArray getComplications()
        {
            return (TaggedTextArray)MySession.execute("RadiologyLib", "getComplications", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's imaging exams")]
        public TaggedImagingExamArray getImagingExamsByPatient(String patientId)
        {
            return (TaggedImagingExamArray)MySession.execute("RadiologyLib", "getImagingExamsByPatient", new object[] { patientId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get list of radiology exam and order hold/cancellation reasons")]
        public RadiologyCancellationReasonArray getRadiologyCancellationReasons()
        {
            return (RadiologyCancellationReasonArray)MySession.execute("RadiologyLib", "getCancellationReasons", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get problems from all connected sites")]
        public TaggedProblemArrays getProblemsFromSites(String status)
        {
            return (TaggedProblemArrays)MySession.execute("ProblemLib", "getProblemsFromSites", new object[] { status });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Dynamically retrieve current list of Active Directory domains")]
        public DomainArray getActiveDirectoryDomains()
        {
            return (DomainArray)MySession.execute("AccountLib", "getDomains", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Verify Active Directory credentials")]
        public UserTO loginActiveDirectory(string domain, string username, string password)
        {
            return (UserTO)MySession.execute("AccountLib", "loginActiveDirectory", new object[] { domain, username, password });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Lookup a user in Active Directory. Can search by username, email address or GUID")]
        public UserArray ldapUserLookup(string uid, string domainSearchRoot)
        {
            return (UserArray)MySession.execute("AccountLib", "ldapUserLookup", new object[] { uid, domainSearchRoot });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get lab test description text")]
        public TaggedTextArray getLabTestDescription(string identifierString)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getTestDescription", new object[] { identifierString });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of lab tests for subsequent call to get test description")]
        public TaggedLabTestArrays getLabTests(string target)
        {
            return (TaggedLabTestArrays)MySession.execute("LabsLib", "getLabTests", new object[] { target });
        }


        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient associates (NOK, caregiver, etc.)")]
        public TaggedPatientAssociateArrays getPatientAssociates()
        {
            return (TaggedPatientAssociateArrays)MySession.execute("PatientLib", "getPatientAssociatesMS", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's adhoc health summary by display name from all connected VistAs.")]
        public TaggedTextArray getAdhocHealthSummaryByDisplayName(string displayName)
        {
            return (TaggedTextArray)MySession.execute("ClinicalLib", "getAdHocHealthSummaryByDisplayName", new object[] { displayName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's health summary by display name from all connected VistAs.")]
        public TaggedHealthSummaryArray EXPERIMENTALgetHealthSummary(string healthSummaryId, string healthSummaryName)
        {
            return (TaggedHealthSummaryArray)MySession.execute("ClinicalLib", "getHealthSummary", new object[] { healthSummaryId, healthSummaryName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's IDs from the session's base connection (i.e. from your local site/authenticated site)")]
        public TaggedTextArray getCorrespondingIds(string sitecode, string patientId, string idType)
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "getCorrespondingIds", new object[] { sitecode, patientId, idType });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get all VHA sites in a VISN")]
        public RegionTO getVISN(string regionId)
        {
            return (RegionTO)MySession.execute("SitesLib", "getVISN", new object[] { regionId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceArray connect(string sitelist)
        {
            return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitelist });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Disconnect from remote Vista systems.")]
        public TaggedTextArray disconnectRemoteSites()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectRemoteSites", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Use when switching patient lookup sites.")]
        public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (TaggedTextArray)MySession.execute("AccountLib", "visitSites", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Call when launching from CPRS Tools menu: connects, authenticates, selects patient.")]
        public PersonsTO cprsLaunch(string pwd, string sitecode, string DUZ, string DFN)
        {
            return (PersonsTO)MySession.execute("AccountLib", "cprsLaunch", new object[] { pwd, sitecode, DUZ, DFN });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Lookup a CPRS-enabled user")]
        public UserArray cprsUserLookup(string target)
        {
            return (UserArray)MySession.execute("UserLib", "cprsUserLookup", new object[] { target });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Match patients at logged in site. Accepts: SSN, 'LAST,FIRST', A1234 (Last name initial + last four SSN)")]
        public TaggedPatientArrays match(string target)
        {
            return (TaggedPatientArrays)MySession.execute("PatientLib", "match", new object[] { target });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Select a patient at logged in site. DFN is the IEN of the patient")]
        public PatientTO select(string DFN)
        {
            return (PatientTO)MySession.execute("PatientLib", "select", new object[] { DFN });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Setup patient's remote sites for querying.")]
        public SiteArray setupMultiSiteQuery(string appPwd)
        {
            return (SiteArray)MySession.execute("AccountLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient confidentiality from all connected sites.")]
        public TaggedTextArray getConfidentiality()
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "getConfidentiality", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Issue patient confidentiality bulletin to all connected sites.")]
        public TaggedTextArray issueConfidentialityBulletin()
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "issueConfidentialityBulletin", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get latest vitals from all connected VistAs")]
        public TaggedVitalSignArrays getLatestVitalSigns()
        {
            return (TaggedVitalSignArrays)MySession.execute("VitalsLib", "getLatestVitalSigns", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's vital signs.")]
        public TaggedVitalSignSetArrays getVitalSigns()
        {
            return (TaggedVitalSignSetArrays)MySession.execute("VitalsLib", "getVitalSigns", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get allergies from all connected VistAs")]
        public TaggedAllergyArrays getAllergies()
        {
            return (TaggedAllergyArrays)MySession.execute("ClinicalLib", "getAllergies", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get allergies from a specific VistA")]
        public TaggedAllergyArrays getAllergiesBySite(string siteCode)
        {
            return (TaggedAllergyArrays)MySession.execute("ClinicalLib", "getAllergiesBySite", new object[] { siteCode });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get radiology reports from all connected VistAs")]
        public TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedRadiologyReportArrays)MySession.execute("ClinicalLib", "getRadiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get radiology reports for a CDW connection")]
        public TaggedRadiologyReportArrays getRadiologyReportsBySite(string fromDate, string toDate, string siteCode)
        {
            return (TaggedRadiologyReportArrays)MySession.execute("ClinicalLib", "getRadiologyReportsBySite", new object[] { fromDate, toDate, siteCode });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get surgery reports from all connected VistAs")]
        public TaggedSurgeryReportArrays getSurgeryReports()
        {
            return (TaggedSurgeryReportArrays)MySession.execute("ClinicalLib", "getSurgeryReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get surgery reports for a specific site")]
        public TaggedSurgeryReportArrays getSurgeryReportsBySite(string siteCode)
        {
            return (TaggedSurgeryReportArrays)MySession.execute("ClinicalLib", "getSurgeryReportsBySite", new object[] { siteCode });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get text for a certain surgery report")]
        public TextTO getSurgeryReportText(string siteId, string rptId)
        {
            return (TextTO)MySession.execute("ClinicalLib", "getSurgeryReportText", new object[] { siteId, rptId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get surgery reports from all connected VistAs")]
        public TaggedSurgeryReportArrays getSurgeryReportsWithText()
        {
            return (TaggedSurgeryReportArrays)MySession.execute("ClinicalLib", "getSurgeryReports", new object[] { true });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get problem list from all connected VistAs")]
        public TaggedProblemArrays getProblemList(string type)
        {
            return (TaggedProblemArrays)MySession.execute("ClinicalLib", "getProblemList", new object[] { type });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get outpatient medications from all connected VistAs")]
        public TaggedMedicationArrays getOutpatientMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOutpatientMeds", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get IV medications from all connected VistAs")]
        public TaggedMedicationArrays getIvMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getIvMeds", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get Inpatient for Outpatient medications from all connected VistAs")]
        public TaggedMedicationArrays getImoMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getImoMeds", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get unit dose medications from all connected VistAs")]
        public TaggedMedicationArrays getUnitDoseMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getUnitDoseMeds", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get non-VA medications from all connected VistAs")]
        public TaggedMedicationArrays getOtherMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOtherMeds", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get all medications from all connected VistAs")]
        public TaggedMedicationArrays getAllMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getAllMeds", new object[] { });
        }

        //[WebMethod(EnableSession = true, Description = "Get VA medications from all connected VistAs")]
        //public TaggedMedicationArrays getVaMeds()
        //{
        //    return (TaggedMedicationArrays)MySession.execute("MedsLib", "getVaMeds", new object[] { });
        //}

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get medication detail from a single connected VistA.")]
        public TextTO getMedicationDetail(string siteId, string medId)
        {
            return (TextTO)MySession.execute("MedsLib", "getMedicationDetail", new object[] { siteId, medId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get signed note metadata from all connected VistAs")]
        public TaggedNoteArrays getSignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getSignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get unsigned note metadata from all connected VistAs")]
        public TaggedNoteArrays getUnsignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getUnsignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get uncosigned note metadata from all connected VistAs")]
        public TaggedNoteArrays getUncosignedNotes(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getUncosignedNotes", new object[] { fromDate, toDate, nNotes });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a note from a single connected VistA.")]
        public TextTO getNote(string siteId, string noteId)
        {
            return (TextTO)MySession.execute("NoteLib", "getNote", new object[] { siteId, noteId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get notes with text from all connected VistAs.")]
        public TaggedNoteArrays getNotesWithText(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getNotesWithText", new object[] { fromDate, toDate, nNotes });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get discharge summaries from all connected VistAs.")]
        public TaggedNoteArrays getDischargeSummaries(string fromDate, string toDate, int nNotes)
        {
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getDischargeSummaries", new object[] { fromDate, toDate, nNotes });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's current consults.")]
        public TaggedConsultArrays getConsultsForPatient()
        {
            return (TaggedConsultArrays)MySession.execute("OrdersLib", "getConsultsForPatient", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's appointments.")]
        public TaggedAppointmentArrays getAppointments()
        {
            return (TaggedAppointmentArrays)MySession.execute("EncounterLib", "getAppointments", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's mental health appointments.")]
        public TaggedAppointmentArrays getMentalHealthAppointments()
        {
            return (TaggedAppointmentArrays)MySession.execute("EncounterLib", "getMentalHealthAppointments", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's mental health appointments.")]
        public TaggedVisitArrays getMentalHealthVisits()
        {
            return (TaggedVisitArrays)MySession.execute("EncounterLib", "getMentalHealthVisits", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get note for appointment.")]
        public TextTO getAppointmentText(string siteId, string apptId)
        {
            return (TextTO)MySession.execute("EncounterLib", "getAppointmentText", new object[] { siteId, apptId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's clinical warnings.")]
        public TaggedTextArray getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getClinicalWarnings", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's advance directives.")]
        public TaggedTextArray getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getAdvanceDirectives", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's crisis notes.")]
        public TaggedTextArray getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getCrisisNotes", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's immunizations.")]
        public TaggedTextArray getImmunizations(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getImmunizations", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's outpatient prescription profile.")]
        public TaggedTextArray getOutpatientRxProfile()
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getOutpatientRxProfile", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's meds administation history.")]
        public TaggedTextArray getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getMedsAdminHx", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's meds administation log.")]
        public TaggedTextArray getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getMedsAdminLog", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArrays getChemHemReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedChemHemRptArrays)MySession.execute("LabsLib", "getChemHemReports", new object[] { fromDate, toDate });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results. Use 0 for number of results to retrieve all results for the time period.")]
        public TaggedChemHemRptArrays getChemHemReportsSimple(string fromDate, string toDate, int nrpts)
        {
            return (TaggedChemHemRptArrays)MySession.execute("LabsLib", "getChemHemReportsRdv", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's Cytology lab results.")]
        public TaggedCytologyRptArrays getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedCytologyRptArrays)MySession.execute("LabsLib", "getCytologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's microbiology lab results.")]
        public TaggedMicrobiologyRptArrays getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedMicrobiologyRptArrays)MySession.execute("LabsLib", "getMicrobiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's surgical pathology lab results.")]
        public TaggedSurgicalPathologyRptArrays getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedSurgicalPathologyRptArrays)MySession.execute("LabsLib", "getSurgicalPathologyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's blood availability reports.")]
        public TaggedTextArray getBloodAvailabilityReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodAvailabilityReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's blood transfusion reports.")]
        public TaggedTextArray getBloodTransfusionReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodTransfusionReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's blood bank reports.")]
        public TaggedTextArray getBloodBankReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getBloodBankReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's electron microscopy reports.")]
        public TaggedTextArray getElectronMicroscopyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getElectronMicroscopyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's cytopathology reports.")]
        public TaggedTextArray getCytopathologyReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getCytopathologyReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's autopsy reports.")]
        public TaggedTextArray getAutopsyReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getAutopsyReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get outpatient encounters from all connected VistAs.")]
        public TaggedTextArray getOutpatientEncounterReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getOutpatientEncounterReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get admission summaries from all connected VistAs.")]
        public TaggedTextArray getAdmissionsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getAdmissionsReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get ADT reports from all connected VistAs.")]
        public TaggedTextArray getExpandedAdtReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getExpandedAdtReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get discharge diagnosis from all connected VistAs.")]
        public TaggedTextArray getDischargeDiagnosisReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getDischargeDiagnosisReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get discharge reports from all connected VistAs.")]
        public TaggedTextArray getDischargesReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getDischargesReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get transfer reports from all connected VistAs.")]
        public TaggedTextArray getTransfersReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getTransfersReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get future clinic visits from all connected VistAs.")]
        public TaggedTextArray getFutureClinicVisitsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getFutureClinicVisitsReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get past clinic visits from all connected VistAs.")]
        public TaggedTextArray getPastClinicVisitsReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getPastClinicVisitsReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get treating specialty from all connected VistAs.")]
        public TaggedTextArray getTreatingSpecialtyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getTreatingSpecialtyReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get C&P reports from all connected VistAs.")]
        public TaggedTextArray getCompAndPenReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getCompAndPenReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get care team from all connected VistAs.")]
        public TaggedTextArray getCareTeamReports()
        {
            return (TaggedTextArray)MySession.execute("EncounterLib", "getCareTeamReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get ICD procedure reports from all connected VistAs.")]
        public TaggedIcdRptArrays getIcdProceduresReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedIcdRptArrays)MySession.execute("EncounterLib", "getIcdProceduresReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get ICD surgery reports from all connected VistAs.")]
        public TaggedIcdRptArrays getIcdSurgeryReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedIcdRptArrays)MySession.execute("EncounterLib", "getIcdSurgeryReports", new object[] { fromDate, toDate, nrpts });
        }

        [OperationContract]
        [WebMethod(EnableSession = true)]
        public TaggedTextArray getNoteTitles(string target, string direction)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getNoteTitles", new object[] { target, direction });
        }

        [OperationContract]
        [WebMethod(EnableSession = true)]
        public TaggedHospitalLocationArray getHospitalLocations(string target, string direction)
        {
            return (TaggedHospitalLocationArray)MySession.execute("EncounterLib", "getLocations", new object[] { target, direction });
        }

        [OperationContract]
        [WebMethod(EnableSession = true)]
        public RadiologyReportTO getImagingReport(string SSN, string accessionNumber)
        {
            return (RadiologyReportTO)MySession.execute("ClinicalLib", "getImagingReport", new object[] { SSN, accessionNumber });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Write a simple, by policy order to a single VistA")]
        public OrderTO writeSimpleOrderByPolicy(string providerDUZ, string esig, string locationIEN, string orderIEN, string startDate)
        {
            return (OrderTO)MySession.execute("OrdersLib", "writeSimpleOrderByPolicy", new object[] { providerDUZ, esig, locationIEN, orderIEN, startDate });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Validate electronic signature")]
        public TextTO isValidEsig(string esig)
        {
            return (TextTO)MySession.execute("UserLib", "isValidEsig", new object[] { esig });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Make a patient inquiry call (address, contact numbers, NOK, etc. information)")]
        public TextTO patientInquiry()
        {
            return (TextTO)MySession.execute("PatientLib", "patientInquiry", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of hospital wards")]
        public TaggedHospitalLocationArray getWards()
        {
            return (TaggedHospitalLocationArray)MySession.execute("EncounterLib", "getWards", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of patients by ward")]
        public TaggedPatientArray getPatientsByWard(string wardId)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "getPatientsByWard", new object[] { wardId });
        }


        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of hospital clinics")]
        public TaggedHospitalLocationArray getClinics(string target)
        {
            return (TaggedHospitalLocationArray)MySession.execute("EncounterLib", "getClinics", new object[] { target });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of patients by clinic")]
        public TaggedPatientArray getPatientsByClinic(string clinicId)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "getPatientsByClinic", new object[] { clinicId });
        }


        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get the list of specialties")]
        public TaggedText getSpecialties()
        {
            return (TaggedText)MySession.execute("EncounterLib", "getSpecialties", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of patients by specialty")]
        public TaggedPatientArray getPatientsBySpecialty(string specialtyId)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "getPatientsBySpecialty", new object[] { specialtyId });
        }


        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get the list of teams")]
        public TaggedText getTeams()
        {
            return (TaggedText)MySession.execute("EncounterLib", "getTeams", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of patients by team")]
        public TaggedPatientArray getPatientsByTeam(string teamId)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "getPatientsByTeam", new object[] { teamId });
        }


        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a list of patients by provider")]
        public TaggedPatientArray getPatientsByProvider(string duz)
        {
            return (TaggedPatientArray)MySession.execute("PatientLib", "getPatientsByProvider", new object[] { duz });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get selected patient's admissions")]
        public TaggedInpatientStayArray getAdmissions()
        {
            return (TaggedInpatientStayArray)MySession.execute("EncounterLib", "getAdmissions", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a VistA's hospital locations (clinics, etc.).")]
        public TaggedHospitalLocationArray getLocations(string target, string direction)
        {
            return (TaggedHospitalLocationArray)MySession.execute("EncounterLib", "getLocations", new object[] { target, direction });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient record flag actions.")]
        public PatientRecordFlagArray getPrfNoteActions(string noteDefinitionIEN)
        {
            return (PatientRecordFlagArray)MySession.execute("NoteLib", "getPrfNoteActions", new object[] { noteDefinitionIEN });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get selected patient's visits")]
        public TaggedVisitArray getVisits(string fromDate, string toDate)
        {
            return (TaggedVisitArray)MySession.execute("EncounterLib", "getVisits", new object[] { fromDate, toDate });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Is given note a consult note?")]
        public TextTO isConsultNote(string noteDefinitionIEN)
        {
            return (TextTO)MySession.execute("NoteLib", "isConsultNote", new object[] { noteDefinitionIEN });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Is cosigner required for this user/author pair?")]
        public TextTO isCosignerRequired(string noteDefinitionIEN, string authorDUZ)
        {
            return (TextTO)MySession.execute("NoteLib", "isCosignerRequired", new object[] { noteDefinitionIEN, authorDUZ });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Is given note a surgery note?")]
        public TaggedText isOneVisitNote(string noteDefinitionIEN, string noteTitle, string visitString)
        {
            return (TaggedText)MySession.execute("NoteLib", "isOneVisitNote", new object[] { noteDefinitionIEN, noteTitle, visitString });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Is given note a PRF note?")]
        public TextTO isPrfNote(string noteDefinitionIEN)
        {
            return (TextTO)MySession.execute("NoteLib", "isPrfNote", new object[] { noteDefinitionIEN });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Is given note a surgery note?")]
        public TaggedText isSurgeryNote(string noteDefinitionIEN)
        {
            return (TaggedText)MySession.execute("NoteLib", "isSurgeryNote", new object[] { noteDefinitionIEN });
        }

        [OperationContract]
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

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Sign a note.")]
        public TextTO signNote(string noteIEN, string userDUZ, string esig)
        {
            NoteLib lib = new NoteLib(MySession);
            return lib.signNote(noteIEN, userDUZ, esig);
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Close a note.")]
        public TextTO closeNote(string noteIEN, string consultIEN)
        {
            NoteLib lib = new NoteLib(MySession);
            return lib.closeNote(noteIEN, consultIEN);
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a patient's demographics")]
        public PatientTO getDemographics()
        {
            PatientLib lib = new PatientLib(MySession);
            return lib.getDemographics();
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Find a patient in the MPI")]
        public PatientArray mpiLookup(string SSN)
        {
            return (PatientArray)MySession.execute("PatientLib", "mpiLookup", new object[] { SSN, "", "", "", "", "", "" });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Find a patient in the NPT")]
        public PatientArray nptLookup(string SSN)
        {
            return (PatientArray)MySession.execute("PatientLib", "nptLookup", new object[] { SSN, "", "", "", "", "", "" });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a patient's orders")]
        public TaggedOrderArrays getAllOrders()
        {
            return (TaggedOrderArrays)MySession.execute("OrdersLib", "getOrdersForPatient", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TextArray getReminderReportTemplates()
        {
            return (TextArray)MySession.execute("ClinicalRemindersLib", "getReminderReportTemplates", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getActiveReminderReports()
        {
            return (TaggedTextArray)MySession.execute("ClinicalRemindersLib", "getActiveReminderReports", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public ReminderReportPatientListTO getPatientListForReminderReport(string rptId)
        {
            return (ReminderReportPatientListTO)MySession.execute("ClinicalRemindersLib", "getPatientListForReminderReport", new object[] { rptId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedText getPcpForPatient(string pid)
        {
            return (TaggedText)MySession.execute("PatientLib", "getPcpForPatient", new object[] { pid });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getSitesForStation()
        {
            return (TaggedTextArray)MySession.execute("LocationLib", "getSitesForStation", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getClinicsByName(string name)
        {
            return (TaggedTextArray)MySession.execute("LocationLib", "getClinicsByName", new object[] { name });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getOrderableItemsByName(string name)
        {
            return (TaggedTextArray)MySession.execute("OrdersLib", "getOrderableItemsByName", new object[] { name });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TextTO getOrderStatusForPatient(string pid, string orderableItemId)
        {
            return (TextTO)MySession.execute("OrdersLib", "getOrderStatusForPatient", new object[] { pid, orderableItemId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getOrderDialogsForDisplayGroup(string displayGroupId)
        {
            return (TaggedTextArray)MySession.execute("OrdersLib", "getOrderDialogsForDisplayGroup", new object[] { displayGroupId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public OrderDialogItemArray getOrderDialogItems(string dialogId)
        {
            return (OrderDialogItemArray)MySession.execute("OrdersLib", "getOrderDialogItems", new object[] { dialogId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getUsersWithOption(string optionName)
        {
            return (TaggedTextArray)MySession.execute("UserLib", "getUsersWithOption", new object[] { optionName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public BoolTO userHasPermission(string uid, string permissionName)
        {
            return (BoolTO)MySession.execute("UserLib", "hasPermission", new object[] { uid, permissionName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public UserSecurityKeyArray getUserSecurityKeys(string uid)
        {
            return (UserSecurityKeyArray)MySession.execute("UserLib", "getUserSecurityKeys", new object[] { uid });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TextTO getVariableValue(string arg)
        {
            return (TextTO)MySession.execute("ToolsLib", "getVariableValue", new object[] { arg });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Retrieves all Mental Health Instruments for a Patient")]
        public TaggedMentalHealthInstrumentAdministrationArrays getMentalHealthInstrumentsForPatient()
        {
            return (TaggedMentalHealthInstrumentAdministrationArrays)MySession.execute("ClinicalLib", "getMentalHealthInstrumentsForPatient", new object[] { });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Retrieves all Mental Health Instruments For a Patient given a Survey acronym")]
        public TaggedMentalHealthInstrumentAdministrationArrays getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            return (TaggedMentalHealthInstrumentAdministrationArrays)MySession.execute("ClinicalLib", "getMentalHealthInstrumentsForPatientBySurvey", new object[] { surveyName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Retrieves the results of a Mental Health Instrument given an administrationId")]
        public MentalHealthInstrumentResultSetTO getMentalHealthInstrumentResultSet(string sitecode, string administrationId)
        {
            return (MentalHealthInstrumentResultSetTO)MySession.execute("ClinicalLib", "getMentalHealthInstrumentResultSet", new object[] { sitecode, administrationId });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Retrieves the results of a Mental Health Instrument given a Survey Name")]
        public TaggedMentalHealthResultSetArray getMentalHealthInstrumentResultSetBySurvey(string siteCode, string surveyName)
        {
            return (TaggedMentalHealthResultSetArray)MySession.execute("ClinicalLib", "getMentalHealthInstrumentResultSetsBySurvey", new object[] { siteCode, surveyName });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "")]
        public TaggedTextArray getNhinData(string types)
        {
            return (TaggedTextArray)MySession.execute("ClinicalLib", "getNhinData", new object[] { types });
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Retrieves all VAMC and CBOC institutions given a state abbreviation")]
        public SiteArray getAllInstitutions()
        {
            return (SiteArray)MySession.execute("LocationLib", "getAllInstitutions", new object[] {});
        }

        [WebMethod(EnableSession = true, Description = "Get a VistA timestamp")]
        public TaggedTextArray getVistaTimestamps()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "getVistaTimestamps", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get Current Team Members for Patient Care Team")]
        public PatientCareTeamTO getPatientCareTeam(string station)
        {
            return (PatientCareTeamTO)MySession.execute("EncounterLib", "getPatientCareTeamMembers", new object[] {station });
        }

        [WebMethod(EnableSession = true, Description = "Retrieve Antibiotic information for a specific organism by site and date range (types = email, phone, firstAndLast, firstOrLast)")]
        public TaggedUserArrays getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string searchType)
        {
            return (TaggedUserArrays)MySession.execute("ClinicalLib", "getStaffByCriteria", new object[] { siteCode, searchTerm, firstName, lastName, searchType });
        }

    }
}
