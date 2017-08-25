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
    // WCF
    [AspNetCompatibilityRequirements(RequirementsMode = AspNetCompatibilityRequirementsMode.Allowed)]
    [ServiceBehavior(InstanceContextMode = InstanceContextMode.Single)]
    public partial class RestSchedulingSvc : IRestSchedulingSvc
    {
        public RestSchedulingSvc() {        }

        public TaggedHospitalLocationArray getClinics(string token, string target)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (TaggedHospitalLocationArray)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, string, string, TaggedHospitalLocationArray>
                (new EncounterLib(session).getClinics), new object[] { "", target, "" });
        }

        public HospitalLocationTO getClinicSchedulingDetails(string token, string clinicId)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (HospitalLocationTO)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, HospitalLocationTO>
                (new SchedulingLib(session).getClinicSchedulingDetails), new object[] { clinicId });
        }

        public AppointmentTypeArray getAppointmentTypes(string token, string target)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (AppointmentTypeArray)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, AppointmentTypeArray>
                (new SchedulingLib(session).getAppointmentTypes), new object[] { target });
        }

        public AppointmentTO makeAppointment(string token, string clinicId, string appointmentTimestamp, string purpose,
            string purposeSubcategory, string appointmentType, string appointmentLength)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (AppointmentTO)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, string, string, string, string, string, AppointmentTO>
                (new SchedulingLib(session).makeAppointment), new object[] { clinicId, appointmentTimestamp, purpose, purposeSubcategory, appointmentLength, appointmentType });
        }

        public TaggedAppointmentArray getPendingAppointments(string token, string startDate)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (TaggedAppointmentArray)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, TaggedAppointmentArray>
                (new SchedulingLib(session).getPendingAppointments), new object[] { startDate });
        }

        public PatientArray getPatientsByClinic(string token, string clinicId, string startDate, string stopDate)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (PatientArray)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, string, string, PatientArray>
                (new SchedulingLib(session).getPatientsByClinic), new object[] { clinicId, startDate, stopDate });
        }

        public RegionArray getVHA()
        {
            return new rest.SitesLib(new MySession()).getVHA();
        }

        public DataSourceArray connect(string sitelist)
        {
            MySession newSession = new MySession();
            DataSourceArray src = new rest.ConnectionLib(newSession).connectToLoginSite(sitelist);
            if (src.fault == null) // successful connect!
            {
                newSession.Token = SessionMgr.getInstance().getNewToken();
                newSession.LastUsed = DateTime.Now;
                SessionMgr.getInstance().addSession(newSession);

                WebOperationContext.Current.OutgoingResponse.Headers.Add("token", newSession.Token);
            }
            SessionMgr.getInstance().returnConnections(newSession);
            return src;
        }

        public UserTO login(string token, string username, string pwd, string permission)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (UserTO)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, string, string, UserTO>
                (new rest.AccountLib(session).login), new object[] { username, pwd, permission });
        }

        public TaggedTextArray disconnect(string token)
        {
            return new rest.ConnectionLib(SessionMgr.getInstance().getSession(token)).disconnectAll();
        }

        public PatientTO select(string token, string pid)
        {
            MySession session = SessionMgr.getInstance().getSession(token);
            return (PatientTO)QueryTemplate.getQuery(QueryType.REST).execute(session, new Func<string, PatientTO>
                (new PatientLib(session).select), new object[] { pid });
        }
    }

    // WCF
    [ServiceContract(Namespace = "http://mdws.medora.va.gov/SchedulingSvc")]
    public interface IRestSchedulingSvc
    {
        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "getVHA")]
        RegionArray getVHA();

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "connect/{sitelist}")]
        DataSourceArray connect(string sitelist);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "login?username={username}&password={pwd}&permission={permission}&token={token}")]
        UserTO login(string token, string username, string pwd, string permission);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "disconnect?token={token}")]
        TaggedTextArray disconnect(string token);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "patient/{pid}?token={token}")]
        PatientTO select(string token, string pid);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "clinics/{target}?token={token}")]
        TaggedHospitalLocationArray getClinics(string token, string target);

        //[OperationContract]
        //[WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "clinicAvailability/{clinicId}?token={token}")]
        //TextTO getClinicAvailability(string token, string clinicId);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "clinicSchedulingDetails/{clinicId}?token={token}")]
        HospitalLocationTO getClinicSchedulingDetails(string token, string clinicId);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "appointmentTypes/{target}?token={token}")]
        AppointmentTypeArray getAppointmentTypes(string token, string target);

        [OperationContract]
        [WebInvoke(Method = "POST", ResponseFormat = WebMessageFormat.Json, BodyStyle = WebMessageBodyStyle.Wrapped, 
            UriTemplate = "appointment?clinicId={clinicId}&appointmentTimestamp={appointmentTimestamp}&purpose={purpose}&purposeSubcategory={purposeSubcategory}&appointmentLength={appointmentLength}&appointmentType={appointmentType}&token={token}")]
        //AppointmentTO makeAppointment(string token, string clinicId, string appointmentType, string appointmentTimestamp, string appointmentLength);
        AppointmentTO makeAppointment(string token, string clinicId, string appointmentTimestamp, string purpose, string purposeSubcategory, string appointmentLength, string appointmentType);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "pendingAppointments/{startDate}?token={token}")]
        TaggedAppointmentArray getPendingAppointments(string token, string startDate);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, BodyStyle = WebMessageBodyStyle.Wrapped, UriTemplate = "clinicPatients?clinicId={clinicId}&startDate={startDate}&stopDate={stopDate}&token={token}")]
        PatientArray getPatientsByClinic(string token, string clinicId, string startDate, string stopDate);
    }
}