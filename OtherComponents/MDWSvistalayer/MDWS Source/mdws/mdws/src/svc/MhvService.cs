using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.domain.ccr;
using gov.va.medora.mdws.dto.sm;

/// <summary>
/// Summary description for MhvService
/// </summary>
namespace gov.va.medora.mdws.mhv
{
    /// <summary>
    /// Summary description for MhvService
    /// </summary>
    [WebService(Namespace = "http://mdws.med.va.gov/mhv/MhvService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class MhvService : BaseService
    {
        public MhvService() { }

        #region Identity

        [WebMethod(EnableSession = true, Description = "For Demo purposes only! Lookup a patient in CDW")]
        public PatientArray cdwLookup(string password, string pid)
        {
            return (PatientArray)QueryTemplate.getQuery(QueryType.SOAP).execute(MySession, new Func<string, string, PatientArray>
                (new PatientLib(MySession).cdwLookup), new object[] { password, pid });
        }

        [WebMethod(EnableSession = true, Description = "For Demo purposes only! Lookup a patient in CDW")]
        public PatientArray cdwLookupWithAccount(string domain, string username, string password, string pid)
        {
            return (PatientArray)QueryTemplate.getQuery(QueryType.SOAP).execute(MySession, new Func<string, string, string, string, PatientArray>
                (new PatientLib(MySession).cdwLookup), new object[] { domain, username, password, pid });
        }

        [WebMethod(EnableSession = true, Description = "For Demo purposes only! Lookup a patient in CDW (returns empty PatientTO objects)")]
        public PatientArray cdwLookupSlim(string password, string pid)
        {
            return (PatientArray)QueryTemplate.getQuery(QueryType.SOAP).execute(MySession, new Func<string, string, PatientArray>
                (new PatientLib(MySession).cdwLookupSlim), new object[] { password, pid });
        }

        [WebMethod(EnableSession = true, Description = "For Demo purposes only! Lookup a patient in CDW (returns empty PatientTO objects)")]
        public PatientArray cdwLookupSlimWithAccount(string domain, string username, string password, string pid)
        {
            return (PatientArray)QueryTemplate.getQuery(QueryType.SOAP).execute(MySession, new Func<string, string, string, string, PatientArray>
                (new PatientLib(MySession).cdwLookupSlimWithAccount), new object[] { domain, username, password, pid });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's ROI status from all VistA sites")]
        public BoolTO getIdProofingStatus(string patientId, string patientName, string patientDOB)
        {
            return (BoolTO)MySession.execute("MhvLib", "getIdProofingStatus", new object[] { patientId, patientName, patientDOB });
        }

        [WebMethod(EnableSession = true, Description = "Update patient's ROI status")]
        public TaggedTextArray updateIdProofingStatus(string patientId, string patientName, string patientDOB)
        {
            return (TaggedTextArray)MySession.execute("MhvLib", "updateIdProofingStatus", new object[] { patientId, patientName, patientDOB });
        }

        #endregion

        #region RxRefill
        [WebMethod(EnableSession = true, Description = "Get patient's prescriptions via MHV HL7 routine.")]
        public TaggedMedicationArrays getPrescriptionsHL7(string pwd, string sitecode, string mpiPid)
        {
            return (TaggedMedicationArrays)MySession.execute("MedsLib", "getPrescriptionsHL7", new object[] { pwd, sitecode, mpiPid });
        }

        [WebMethod(EnableSession = true, Description = "Refill patient's prescription")]
        public MedicationTO refillPrescription(string pwd, string sitecode, string mpiPid, string rxId)
        {
            return (MedicationTO)MySession.execute("MedsLib", "refillPrescription", new object[] { pwd, sitecode, mpiPid, rxId });
        }
        #endregion

        #region Secure Messaging
        [WebMethod(EnableSession = true, Description = "Get a Secure Messaging user (valid ID types: SMID, ICN or SSN)")]
        public dto.sm.SmUserTO getSmUser(string pwd, string userId, string idType)
        {
            return (dto.sm.SmUserTO)MySession.execute("SecureMessageLib", "getUser", new object[] { pwd, userId, idType });
        }

        [WebMethod(EnableSession = true, Description = "Send a message that was saved as a draft")]
        public dto.sm.MessageTO sendSecureMessageDraft(string pwd, Int32 messageId, Int32 messageOplock)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "sendDraft", new object[] { pwd, messageId, messageOplock });
        }

        [WebMethod(EnableSession = true, Description = "Save/update a draft message")]
        public dto.sm.ThreadTO saveSecureMessageDraft(string pwd, Int32 replyingToMessageId, string threadSubject, Int32 messageCategory,
            Int32 messageId, Int32 senderId, Int32 recipientId, string messageBody, Int32 messageOplock, Int32 threadOplock)
        {
            return (dto.sm.ThreadTO)MySession.execute("SecureMessageLib", "saveDraft", new object[] 
            { 
                pwd, replyingToMessageId, threadSubject, messageCategory, messageId, senderId, recipientId, messageBody, messageOplock, threadOplock 
            });
        }

        [WebMethod(EnableSession = true, Description = "Delete a draft message")]
        public dto.sm.MessageTO deleteDraft(string pwd, Int32 messageId)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "deleteDraft", new object[] { pwd, messageId });
        }

        [WebMethod(EnableSession = true, Description = "Send a reply message")]
        public dto.sm.MessageTO sendReplyMessage(string pwd, Int32 replyingToMessageId, Int32 senderId, Int32 recipientId, string messageBody)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "sendReplyMessage", new object[] { pwd, replyingToMessageId, senderId, recipientId, messageBody });
        }

        [WebMethod(EnableSession = true, Description = "Send a new secure message")]
        public dto.sm.ThreadTO sendNewMessage(string pwd, string threadSubject, Int32 groupId, Int32 messageCategory, Int32 senderId, Int32 recipientId, string messageBody)
        {
            return (dto.sm.ThreadTO)MySession.execute("SecureMessageLib", "sendNewMessage", new object[] { pwd, threadSubject, groupId, messageCategory, senderId, recipientId, messageBody });
        }

        [WebMethod(EnableSession = true, Description = "Update a secure message thread subject or message category")]
        public dto.sm.ThreadTO updateMessageThread(string pwd, Int32 threadId, string threadSubject, Int32 messageCategory, Int32 threadOplock)
        {
            return (dto.sm.ThreadTO)MySession.execute("SecureMessageLib", "updateMessageThread", new object[] { pwd, threadId, threadSubject, messageCategory, threadOplock });
        }

        [WebMethod(EnableSession = true, Description = "Add an attachment to a secure message")]
        public dto.sm.AttachmentTO addAttachment(string pwd, Int32 messageId, Int32 messageOplock, string fileName, string mimeType, AttachmentTO attachment)
        {
            return (dto.sm.AttachmentTO)MySession.execute("SecureMessageLib", "addAttachment", new object[] { pwd, messageId, messageOplock, fileName, mimeType, attachment });
        }

        [WebMethod(EnableSession = true, Description = "Update an attachment to a secure message")]
        public dto.sm.AttachmentTO updateAttachment(string pwd, Int32 attachmentId, Int32 attachmentOplock, string fileName, string mimeType, AttachmentTO newAttachment)
        {
            return (dto.sm.AttachmentTO)MySession.execute("SecureMessageLib", "updateAttachment", new object[] { pwd, attachmentId, attachmentOplock, fileName, mimeType, newAttachment });
        }

        [WebMethod(EnableSession = true, Description = "Delete an attachment to a secure message")]
        public dto.sm.MessageTO deleteAttachment(string pwd, Int32 messageId)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "deleteAttachment", new object[] { pwd, messageId });
        }

        [WebMethod(EnableSession = true, Description = "Retrieve an attachment file")]
        public dto.sm.AttachmentTO getAttachment(string pwd, Int32 attachmentId)
        {
            return (dto.sm.AttachmentTO)MySession.execute("SecureMessageLib", "getAttachment", new object[] { pwd, attachmentId });
        }

        [WebMethod(EnableSession = true, Description = "Fetch a secure message and mark as read")]
        public dto.sm.MessageTO readMessage(string pwd, Int32 addresseeId, Int32 addresseeOplock)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "readMessage", new object[] { pwd, addresseeId, addresseeOplock });
        }

        [WebMethod(EnableSession = true, Description = "Get user's secure messages")]
        public dto.sm.SecureMessageThreadsTO getSecureMessages(string pwd, Int32 userId, Int32 folderId, Int32 pageStart, Int32 pageSize)
        {
            return (dto.sm.SecureMessageThreadsTO)MySession.execute("SecureMessageLib", "getMessages", new object[] { pwd, userId, folderId, pageStart, pageSize });
        }

        [WebMethod(EnableSession = true, Description = "Move a message to another folder")]
        public dto.sm.MessageTO moveMessage(string pwd, Int32 userId, Int32 messageId, Int32 newFolderId)
        {
            return (dto.sm.MessageTO)MySession.execute("SecureMessageLib", "moveMessage", new object[] { pwd, userId, messageId, newFolderId });
        }
        #endregion


        [WebMethod(EnableSession = true, Description = "Get patient data by type. Use 'getDataTypes' to discover currently supported types")]
        public PatientMedicalRecordTO getData(string pwd, string site, bool multiSite, string pid, string types)
        {
            return (PatientMedicalRecordTO)MySession.execute("MhvLib", "getData", new object[] { pwd, site, multiSite, pid, types });
        }

        [WebMethod(EnableSession = true, Description = "Get supported patient data types")]
        public string getDataTypes()
        {
            return "documents;immunizations;problems;vitals;radiologyExams;demographics;dischargeSummaries;reactions;ekgs;accessions";
        }

        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArrays getChemHemReports(
            string pwd, string sitecode, string mpiPid, string fromDate, string toDate)
        {
            return new TaggedChemHemRptArrays() { fault = new FaultTO("Not currently implemented") };
        }

        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArray getChemHemReportsFromSite(
            string pwd, string sitecode, string mpiPid, string fromDate, string toDate)
        {
            return (TaggedChemHemRptArray)MySession.execute("MhvLib", "getChemHemReportsByReportDateFromSite", new object[] { pwd, sitecode, mpiPid, fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's chem/hem lab results.")]
        public TaggedChemHemRptArray getChemHemReportsForPatientFromSite(
            string pwd, string sitecode, string mpiPid)
        {
            return new TaggedChemHemRptArray() { fault = new FaultTO("Not currently implemented") };
        }

        [WebMethod(EnableSession = true, Description = "Get patient's detailed reminders from single VistA.")]
        public TextTO getDetailedRemindersFromSite(string pwd, string sitecode, string mpiPid)
        {
            return (TextTO)MySession.execute("MhvLib", "getHealthSummary", new object[] { pwd, sitecode, mpiPid, "MHV REMINDERS DETAIL DISPLAY [MHVD]" });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's summary reminders from single VistA.")]
        public TextTO getSummaryRemindersFromSite(string pwd, string sitecode, string mpiPid)
        {
            return (TextTO)MySession.execute("MhvLib", "getHealthSummary", new object[] { pwd, sitecode, mpiPid, "MHV REMINDERS SUMMARY DISPLAY [MHVS]" });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's appointments from all sites.")]
        public TaggedAppointmentArrays getAppointments(string pwd, string sitecode, string mpiPid)
        {
            return new TaggedAppointmentArrays() { fault = new FaultTO("Not currently implemented") };
        }

        [WebMethod(EnableSession = true, Description = "Get patient's appointments from single site.")]
        public TaggedAppointmentArray getAppointmentsFromSite(string pwd, string sitecode, string mpiPid)
        {
            return (TaggedAppointmentArray)MySession.execute("MhvLib", "getAppointmentsFromSite", new object[] { pwd, sitecode, mpiPid });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's future appointments from all sites.")]
        public TaggedAppointmentArrays getFutureAppointments(string pwd, string sitecode, string mpiPid)
        {
            return new TaggedAppointmentArrays() { fault = new FaultTO("Not currently implemented") };
        }

        [WebMethod(EnableSession = true, Description = "Get patient's future appointments from single site.")]
        public TaggedAppointmentArray getFutureAppointmentsFromSite(string pwd, string sitecode, string mpiPid)
        {
            return new TaggedAppointmentArray() { fault = new FaultTO("Not currently implemented") };
        }

        [WebMethod(EnableSession = true, Description = "Get patient's Home Tele-Health Vitals as an XML payload.")]
        public TextTO getHthVitalsAsXML(string pwd, string mpiPid)
        {
            return (TextTO)MySession.execute("MhvLib", "getHthVitalsAsXML", new object[] { pwd, mpiPid });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's allergies as an XML payload.")]
        public TextTO getAllergiesAsXML(string pwd, string mpiPid)
        {
            return (TextTO)MySession.execute("MhvLib", "getAllergiesAsXML", new object[] { pwd, mpiPid });
        }

        //[WebMethod(EnableSession = true, Description = "Get patient's immunizations.")]
        //public ContinuityOfCareRecord getImmunizations(string pwd, string mpiPid)
        //{
        //    return (ContinuityOfCareRecord)MySession.execute("MhvLib", "getImmunizations", new object[] { pwd, mpiPid });
        //}

        [WebMethod(EnableSession = true, Description = "Get patient's lab reports as an XML payload.")]
        public TextTO getLabReportsAsXML(string pwd, string mpiPid, string fromDate, string toDate)
        {
            return (TextTO)MySession.execute("MhvLib", "getLabReportsAsXML", new object[] { pwd, mpiPid, fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's lab reports.")]
        public LabResultTO[] getLabReports(string pwd, string mpiPid, string fromDate, string toDate)
        {
            return (LabResultTO[])MySession.execute("MhvLib", "getLabReports", new object[] { pwd, mpiPid, fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get list of patients with future appointments that have been edited since a timestamp.")]
        public TaggedPatientArrays getPatientsWithUpdatedFutureAppointments(string username, string pwd, string fromDate)
        {
            return (TaggedPatientArrays)MySession.execute("EncounterLib", "getPatientsWithUpdatedFutureAppointments", new object[] { username, pwd, fromDate });
        }

        [WebMethod(EnableSession = true, Description = "Get list of patients with updated chem hem reports.")]
        public TaggedPatientArrays getPatientsWithUpdatedChemHemReports(string username, string pwd, string fromDate)
        {
            return (TaggedPatientArrays)MySession.execute("LabsLib", "getPatientsWithUpdatedChemHemReports", new object[] { username, pwd, fromDate });
        }

        [WebMethod(EnableSession = true, Description = "Get patient MOS report.")]
        public TaggedText getMOSReport(string appPwd, string EDIPI)
        {
            return (TaggedText)MySession.execute("PatientLib", "getMOSReport", new object[] { appPwd, EDIPI });
        }

    }
}
