using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;
using System.ServiceModel;

/// <summary>
/// Summary description for MhvService
/// </summary>
namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for PhrSvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/PhrSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    [ServiceContract(Namespace = "http://mdws.medora.va.gov/PhrSvc")]
    public partial class PhrSvc : BaseService
    {
        /// <summary>
        /// This facade is for Patient Health Record.  It is assumed the user is the patient.
        /// </summary>
        public PhrSvc() { }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Authenticate a user with his/her MHV or DS Logon credentials")]
        public UserTO patientLogin(byte[] cert, string username, string password, string credentialType)
        {
            return new UserTO() { fault = new FaultTO("Not currently implemented") };
        }

        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient medical record")]
        public PatientMedicalRecordTO getMedicalRecord(byte[] cert, string patientId)
        {
            return new PatientMedicalRecordTO() { fault = new FaultTO("Not currently implemented") };
        }
 

        /// <summary>
        /// This method connects to a site.  
        /// </summary>
        /// <param name="pwd">Application's security phrase</param>
        /// <param name="sitecode">Station # of the VistA site</param>
        /// <param name="mpiPid">National patient identifier (ICN)</param>
        /// <returns>SiteTO: name, hostname, port, etc. of connected site</returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Connect to a site.")]
        public SiteTO connect(string pwd, string sitecode, string mpiPid)
        {
            return (SiteTO)MySession.execute("AccountLib", "patientVisit", new object[] { pwd, sitecode, mpiPid });
        }

        /// <summary>
        /// This method closes all VistA connections.
        /// </summary>
        /// <returns>"OK" if successful</returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Disconnect from all Vista systems.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
        }

        ///// <summary>
        ///// This method connects to all the patient's VistA systems.  Subsequent queries will be made 
        ///// against all these sources.
        ///// </summary>
        ///// <param name="appPwd">The application's security phrase</param>
        ///// <returns></returns>
        //[WebMethod(EnableSession = true, Description = "Setup patient's remote sites for querying.")]
        //public SiteArray setupMultiSiteQuery(string appPwd)
        //{
        //    return (SiteArray)MySession.execute("AccountLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
        //}

        /// <summary>
        /// This method gets all the patient's vital signs from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's vital signs from all connected VistAs.")]
        public TaggedVitalSignSetArrays getVitalSigns()
        {
            return (TaggedVitalSignSetArrays)MySession.execute("VitalsLib", "getVitalSigns", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's allergies from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get allergies from all connected VistAs")]
        public TaggedAllergyArrays getAllergies()
        {
            return (TaggedAllergyArrays)MySession.execute("ClinicalLib", "getAllergies", new object[] { });
        }

        /// <summary>
        /// This method gets the patient's radiology reports from VistA sources for a given time frame.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get radiology reports from all connected VistAs")]
        public TaggedRadiologyReportArrays getRadiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedRadiologyReportArrays)MySession.execute("ClinicalLib", "getRadiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's surgery reports from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get surgery reports from all connected VistAs")]
        public TaggedSurgeryReportArrays getSurgeryReports()
        {
            return (TaggedSurgeryReportArrays)MySession.execute("ClinicalLib", "getSurgeryReports", new object[] { true });
        }

        /// <summary>
        /// This method gets all the patient's problem lists of a given type from VistA sources.
        /// </summary>
        /// <param name="type">ACTIVE, INACTIVE, ALL</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get problem list from all connected VistAs")]
        public TaggedProblemArrays getProblemList(string type)
        {
            return (TaggedProblemArrays)MySession.execute("ClinicalLib", "getProblemList", new object[] { type });
        }

        /// <summary>
        /// This method gets all the patient's outpatient meds from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get outpatient medications from all connected VistAs")]
        public TaggedMedicationArrays getOutpatientMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOutpatientMeds", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's IV meds from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get IV medications from all connected VistAs")]
        public TaggedMedicationArrays getIvMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getIvMeds", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's unit dose meds from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get unit dose medications from all connected VistAs")]
        public TaggedMedicationArrays getUnitDoseMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getUnitDoseMeds", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's non-VA meds from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get non-VA medications from all connected VistAs")]
        public TaggedMedicationArrays getOtherMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getOtherMeds", new object[] { });
        }

        /// <summary>
        /// This method get all the patient's outpatient, inpatient and non-VA meds from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get all medications from all connected VistAs")]
        public TaggedMedicationArrays getAllMeds()
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getAllMeds", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's appointments from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's appointments.")]
        public TaggedAppointmentArrays getAppointments()
        {
            return (TaggedAppointmentArrays)MySession.execute("EncounterLib", "getAppointments", new object[] { });
        }

        /// <summary>
        /// This method gets all the detailed MHV health summaries from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's detailed MHV health summaries from all connected VistAs.")]
        public TaggedTextArray getDetailedHealthSummary()
        {
            return (TaggedTextArray)MySession.execute("ClinicalLib", "getAdHocHealthSummaryByDisplayName", new object[] { "MHV REMINDERS DETAIL DISPLAY [MHVD]" });
        }

        /// <summary>
        /// This method gets all the MHV health summaries from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's MHV health summaries from all connected VistAs.")]
        public TaggedTextArray getHealthSummary()
        {
            return (TaggedTextArray)MySession.execute("ClinicalLib", "getAdHocHealthSummaryByDisplayName", new object[] { "MHV REMINDERS SUMMARY DISPLAY [MHVS]" });
        }

        /// <summary>
        /// This method gets all the patient's immunizations from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's immunizations.")]
        public TaggedTextArray getImmunizations(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getImmunizations", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's outpatient meds profiles from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's outpatient prescription profile.")]
        public TaggedTextArray getOutpatientRxProfile()
        {
            return (TaggedTextArray)MySession.execute("MedsLib", "getOutpatientRxProfile", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's chem/hem lab results from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArrays getChemHemReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedChemHemRptArrays)MySession.execute("LabsLib", "getChemHemReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's cytology reports from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's Cytology lab results.")]
        public TaggedCytologyRptArrays getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedCytologyRptArrays)MySession.execute("LabsLib", "getCytologyReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's microbiology reports from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's microbiology lab results.")]
        public TaggedMicrobiologyRptArrays getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedMicrobiologyRptArrays)MySession.execute("LabsLib", "getMicrobiologyReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's surgical pathology lab results.")]
        public TaggedSurgicalPathologyRptArrays getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedSurgicalPathologyRptArrays)MySession.execute("LabsLib", "getSurgicalPathologyReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's electron microscopy reports from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's electron microscopy reports.")]
        public TaggedTextArray getElectronMicroscopyReports(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getElectronMicroscopyReports", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets all the patient's cytopathology reports from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's cytopathology reports.")]
        public TaggedTextArray getCytopathologyReports()
        {
            return (TaggedTextArray)MySession.execute("LabsLib", "getCytopathologyReports", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's admissions from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get selected patient's admissions")]
        public TaggedInpatientStayArray getAdmissions()
        {
            return (TaggedInpatientStayArray)MySession.execute("EncounterLib", "getAdmissions", new object[] { });
        }

        /// <summary>
        /// This method gets all the patient's visits from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get selected patient's visits")]
        public TaggedVisitArray getVisits(string fromDate, string toDate)
        {
            return (TaggedVisitArray)MySession.execute("EncounterLib", "getVisits", new object[] { fromDate, toDate });
        }

        /// <summary>
        /// This method gets all the patient's demographics from VistA sources.
        /// </summary>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a patient's demographics")]
        public PatientTO getDemographics()
        {
            PatientLib lib = new PatientLib(MySession);
            return lib.getDemographics();
        }

        /// <summary>
        /// This method gets all the patient's advance directives from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <param name="nrpts">Max reports from each site. Defaults to 50.</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get patient's advance directives.")]
        public TaggedTextArray getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            return (TaggedTextArray)MySession.execute("NoteLib", "getAdvanceDirectives", new object[] { fromDate, toDate, nrpts });
        }

        /// <summary>
        /// This method gets a synopsis of the patient's data from VistA sources.
        /// </summary>
        /// <param name="fromDate">yyyyMMdd</param>
        /// <param name="toDate">yyyyMMdd</param>
        /// <returns></returns>
        [OperationContract]
        [WebMethod(EnableSession = true, Description = "Get a synopsis from VistA sources")]
        public Synopsis getSynopsis(string fromDate, string toDate)
        {
            Synopsis result = new Synopsis();
            result.advanceDirectives = getAdvanceDirectives(fromDate, toDate, 50);
            result.allergies = getAllergies();
            result.chemHemReports = getChemHemReports(fromDate, toDate, 50);
            result.detailedHealthSummaries = getDetailedHealthSummary();
            result.healthSummaries = getHealthSummary();
            result.immunizations = getImmunizations(fromDate, toDate, 50);
            result.medications = getOutpatientMeds();
            result.microbiologyReports = getMicrobiologyReports(fromDate, toDate, 50);
            result.supplements = getOtherMeds();
            result.problemLists = getProblemList("ACTIVE");
            result.radiologyReports = getRadiologyReports(fromDate, toDate, 50);
            result.surgeryReports = getSurgeryReports();
            result.vitalSigns = getVitalSigns();
            SitesLib sitesLib = new SitesLib(MySession);
            result.treatingFacilities = sitesLib.getConnectedSites();
            return result;
        }
    }
}
