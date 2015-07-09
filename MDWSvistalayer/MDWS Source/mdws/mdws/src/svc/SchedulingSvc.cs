using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;
using System.ServiceModel;
using System.ServiceModel.Web;
using System.ServiceModel.Activation;

namespace gov.va.medora.mdws
{
    // WSE
    //[ServiceContract(Namespace = "http://mdws.medora.va.gov/SchedulingSvc")]
    [WebService(Namespace = "http://mdws.medora.va.gov/SchedulingSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    // WCF
    [AspNetCompatibilityRequirements(RequirementsMode = AspNetCompatibilityRequirementsMode.Allowed)]
    public partial class SchedulingSvc : BaseService, ISchedulingSvc
    {
        public SchedulingSvc() : base() { }

        [WebMethod(EnableSession = true, Description = "Get a list of reasons for cancelling an appointment")]
        public TaggedTextArray getCancellationReasons()
        {
            return (TaggedTextArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<TaggedTextArray>
                (new SchedulingLib(this.MySession).getCancellationReasons), null);
        }

        [WebMethod(EnableSession = true, Description = "Check logged in user's access to clinic")]
        public BoolTO hasClinicAccess(string clinicId)
        {
            return (BoolTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, BoolTO>
                (new SchedulingLib(this.MySession).hasClinicAccess), new object[] { clinicId });
        }

        [WebMethod(EnableSession = true, Description = "Check if clinic has valid stop code")]
        public BoolTO hasValidStopCode(string clinicId)
        {
            return (BoolTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, BoolTO>
                (new SchedulingLib(this.MySession).hasValidStopCode), new object[] { clinicId });
        }

        [WebMethod(EnableSession = true, Description = "Check if stop code is valid")]
        public BoolTO isValidStopCode(string stopCodeId)
        {
            return (BoolTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, BoolTO>
                (new SchedulingLib(this.MySession).isValidStopCode), new object[] { stopCodeId });
        }

        [WebMethod(EnableSession = true, Description = "Get list of clinics")]
        public TaggedHospitalLocationArray getClinics(string target)
        {
            return (TaggedHospitalLocationArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, string, string, TaggedHospitalLocationArray>
                (new EncounterLib(this.MySession).getClinics), new object[] { "", target, "" });
        }

        [WebMethod(EnableSession = true, Description = "Get clinic scheduling information. e.g. availability, default appointment length, etc.")]
        public HospitalLocationTO getClinicSchedulingDetails(string clinicId)
        {
            return (HospitalLocationTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, HospitalLocationTO>
                (new SchedulingLib(this.MySession).getClinicSchedulingDetails), new object[] { clinicId });
        }

        [WebMethod(EnableSession = true, Description = "Get list of appointment types")]
        public AppointmentTypeArray getAppointmentTypes(string target)
        {
            return (AppointmentTypeArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, AppointmentTypeArray>
                (new SchedulingLib(this.MySession).getAppointmentTypes), new object[] { target });
        }

        [WebMethod(EnableSession = true, Description = "Schedule an appointment")]
        public AppointmentTO makeAppointment(string clinicId, string appointmentTimestamp, string purpose, string purposeSubcategory, string appointmentLength, string appointmentType)
        {
            return (AppointmentTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, string, string, string, string, string, AppointmentTO>
                (new SchedulingLib(this.MySession).makeAppointment), new object[] { clinicId, appointmentTimestamp, purpose, purposeSubcategory, appointmentLength, appointmentType });
        }

        [WebMethod(EnableSession = true, Description = "Get a patient's pending appointments")]
        public TaggedAppointmentArray getPendingAppointments(string startDate)
        {
            return (TaggedAppointmentArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, TaggedAppointmentArray>
               (new SchedulingLib(this.MySession).getPendingAppointments), new object[] { startDate });
        }

        [WebMethod(EnableSession = true, Description = "Get a list of patients by clinic")]
        public PatientArray getPatientsByClinic(string clinicId, string startDate, string stopDate)
        {
            return (PatientArray)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, string, string, PatientArray>
                (new SchedulingLib(this.MySession).getPatientsByClinic), new object[] { clinicId, startDate, stopDate });
        }

        [WebMethod(EnableSession = true, Description = "Select a patient")]
        public PatientTO select(string pid)
        {
            return (PatientTO)QueryTemplate.getQuery(QueryType.SOAP).execute(this.MySession, new Func<string, PatientTO>
                (new PatientLib(this.MySession).select), new object[] { pid });
        }
    }

    // WCF
    [ServiceContract(Namespace = "http://mdws.medora.va.gov/SchedulingSvc", SessionMode = SessionMode.Allowed)]
    public interface ISchedulingSvc
    {
        [OperationContract]
        TaggedHospitalLocationArray getClinics(string target);

        //[OperationContract]
        //TextTO getClinicAvailability(string clinicId);

        [OperationContract]
        HospitalLocationTO getClinicSchedulingDetails(string clinicId);

        [OperationContract]
        AppointmentTypeArray getAppointmentTypes(string target);

        [OperationContract]
        AppointmentTO makeAppointment(string clinicId, string appointmentTimestamp, string purpose, string purposeSubcategory, string appointmentLength, string appointmentType);
        
        [OperationContract]
        TaggedAppointmentArray getPendingAppointments(string startDate);

        [OperationContract]
        PatientArray getPatientsByClinic(string clinicId, string startDate, string stopDate);

        [OperationContract]
        PatientTO select(string pid);

    }
}