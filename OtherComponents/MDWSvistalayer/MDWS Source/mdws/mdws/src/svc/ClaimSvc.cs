using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Services;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for ClaimSvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/ClaimSvc")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [System.ComponentModel.ToolboxItem(false)]
    // To allow this Web Service to be called from script, using ASP.NET AJAX, uncomment the following line. 
    // [System.Web.Script.Services.ScriptService]
    public class ClaimSvc : BaseService
    {
        public const string VERSION = "2.0.0";

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get all VHA sites in a VISN")]
        public RegionTO getVISN(string regionId)
        {
            return (RegionTO)MySession.execute("SitesLib", "getVISN", new object[] { regionId });
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
            return (TaggedTextArray)MySession.execute("AccountLib", "visitSites", new object[] { pwd, sitelist, userSitecode, userName, DUZ, SSN, context });
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
            return (SiteArray)MySession.execute("AccountLib", "setupMultiSourcePatientQuery", new object[] { appPwd, "" });
        }

        [WebMethod(EnableSession = true, Description = "Get patient confidentiality from all connected sites.")]
        public TaggedTextArray issueConfidentialityBulletin()
        {
            return (TaggedTextArray)MySession.execute("PatientLib", "issueConfidentialityBulletin", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get patient prosthetic claims from local site.")]
        public TaggedProstheticClaimArray getProstheticClaims(string dfn, string dateList)
        {
            return (TaggedProstheticClaimArray)MySession.execute("ClaimsLib", "getProstheticClaims", new object[] { dfn, dateList });
        }

        [WebMethod(EnableSession = true, Description = "Get patient's rated disabilities from local site.")]
        public TaggedRatedDisabilityArray getRatedDisabilitiessForPatient(string dfn)
        {
            return (TaggedRatedDisabilityArray)MySession.execute("ClaimsLib", "getRatedDisabilitiesForPatient", new object[] { dfn });
        }

        [WebMethod(EnableSession = true, Description = "Get consult note.")]
        public TextTO getConsultNote(string consultId)
        {
            return (TextTO)MySession.execute("ClinicalLib", "getConsultNote", new object[] { consultId });
        }

        [WebMethod(EnableSession = true, Description = "Get claimants.")]
        public TaggedDemographicsRecordArrays getClaimants(
            string lastName, string firstName, string middleName, string DOB, 
            string zipcode, string state, string city, int maxrex)
        {
            return (TaggedDemographicsRecordArrays)MySession.execute("ClaimsLib", "getClaimants", new object[] { 
                lastName, firstName, middleName, DOB, zipcode, state, city, maxrex });
        }
    }
}
