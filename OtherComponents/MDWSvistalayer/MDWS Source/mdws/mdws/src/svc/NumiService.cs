using System;
using System.Web;
using System.Collections;
using System.Collections.Specialized;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using System.Xml.Serialization;

using gov.va.medora.mdo;
using gov.va.medora.mdws;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws.numi
{
    /// <summary>
    /// Summary description for NumiService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/NumiService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class NumiService : BaseService, INumiService
    {
        public NumiService()
        {
            //Uncomment the following line if using designed components 
            //InitializeComponent(); 
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
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Connect to a VistA and log onto it")]
        public UserTO connectAndLogin(string sitecode, string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "connectAndLogin", new object[] { sitecode, username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Use when switching patient lookup sites.")]
        public TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string context)
        {
            // Refactored to use the credentials in the prerequisite login.
            return (TaggedTextArray)MySession.execute("AccountLib", "visitSites", new object[] { pwd, sitelist, userSitecode, userName, DUZ, context });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
        public TaggedTextArray disconnect()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { });
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

        [WebMethod(EnableSession = true, Description = "Get wards for connected site(s)")]
        public TaggedHospitalLocationArrays getWards()
        {
            return (TaggedHospitalLocationArrays)MySession.execute("NumiLib", "getWards", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get DRG records for connected site(s)")]
        public TaggedDrgArrays getDRGRecords()
        {
            return (TaggedDrgArrays)MySession.execute("EncounterLib", "getDRGRecords", new object[] { });
        }

        [WebMethod(EnableSession = true, Description="Get Patient Movement records for selected patient.")]
        public TaggedAdtArrays getInpatientMoves()
        {
            return (TaggedAdtArrays)MySession.execute("EncounterLib", "getInpatientMoves", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get Patient Movement records falling within given start and end dates")]
        public TaggedAdtArrays getInpatientMovesByDateRange(string fromDate, string toDate)
        {
            return (TaggedAdtArrays)MySession.execute("EncounterLib", "getInpatientMoves", new object[] { fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get Patient Movement discharge records for given patient")]
        public TaggedAdtArray getInpatientDischarges(string sitecode, string DFN)
        {
            return (TaggedAdtArray)MySession.execute("EncounterLib", "getInpatientDischarges", new object[] { sitecode, DFN });
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="fromDate">yyyyMMdd.HHmmss</param>
        /// <param name="toDate">yyyyMMdd.HHmmss</param>
        /// <param name="iterationLength">dd.hhmmss or subset such as "1" for a day or ".01" for an hour</param>
        /// <returns></returns>
        [WebMethod(EnableSession = true, Description = "Get Patient Movement records falling within given start and end dateTime. yyyyMMdd.HHmmss")]
        public TaggedAdtArrays getInpatientMovesByDateTimeRange(string fromDate, string toDate, string iterationLength)
        {
            return (TaggedAdtArrays)MySession.execute("EncounterLib", "getInpatientMoves", new object[] { fromDate, toDate, iterationLength });
        }

        [WebMethod(EnableSession = true, Description = "Get Patient Movement records associated with a checkinId")]
        public TaggedAdtArrays getInpatientMovesByCheckinId(string checkinId)
        {
            return (TaggedAdtArrays)MySession.execute("EncounterLib", "getInpatientMovesByCheckinId", new object[] { checkinId });
        }

        [WebMethod(EnableSession = true, Description = "Get Patient Movement records associated with a checkinId")]
        public InpatientStayTO getStayMovements(string checkinId)
        {
            return (InpatientStayTO)MySession.execute("EncounterLib", "getStayMovements", new object[] { checkinId });
        }

        [WebMethod(EnableSession = true, Description = "Get Patient Movement records associated with a checkinId")]
        public TaggedInpatientStayArrays getStayMovementsByDateRange(string fromDate, string toDate)
        {
            return (TaggedInpatientStayArrays)MySession.execute("EncounterLib", "getStayMovementsByDateRange", new object[] { fromDate, toDate });
        }

        [WebMethod(EnableSession = true, Description = "Get all selected patient movement records")]
        public TaggedInpatientStayArrays getStayMovementsByPatient()
        {
            return (TaggedInpatientStayArrays)MySession.execute("EncounterLib", "getStayMovementsByPatient", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Find a user by partial name")]
        public TaggedUserArrays userLookup(string target, string maxRex)
        {
            return (TaggedUserArrays)MySession.execute("UserLib", "lookupMS", new object[] { target, maxRex });
        }

        [WebMethod(EnableSession = true, Description = "Get a VistA timestamp")]
        public TaggedTextArray getVistaTimestamps()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "getVistaTimestamps", new object[] { });
        }
    }

    public interface INumiService
    {
        DataSourceArray connect(string sitelist);
        UserTO login(string username, string pwd, string context);
        UserTO connectAndLogin(string sitecode, string username, string pwd, string context);
        TaggedTextArray disconnect();
        TaggedDrgArrays getDRGRecords();
        TaggedAdtArrays getInpatientMoves();
        TaggedAdtArrays getInpatientMovesByDateRange(string fromDate, string toDate);
        TaggedAdtArrays getInpatientMovesByDateTimeRange(string fromDate, string toDate, string iterationLength);
        TaggedAdtArrays getInpatientMovesByCheckinId(string checkinId);
        TaggedHospitalLocationArrays getWards();
        TaggedPatientArrays match(string target);
        PatientTO select(string DFN);
        TaggedTextArray visit(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string context);
        TaggedTextArray getConfidentiality();
        TaggedTextArray issueConfidentialityBulletin();
        RegionArray getVHA();
        TaggedUserArrays userLookup(string target, string maxRex);
    }
}
