using System;
using System.Data;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for FindPatientService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/patientFinder/FindPatientService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class FindPatientService : BaseService
    {
		public FindPatientService() { }

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceArray connect(string sitecode)
        {
            return (DataSourceArray)MySession.execute("ConnectionLib", "connectToLoginSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

		[WebMethod(EnableSession = true, Description = "Disconnect from single Vista system.")]
		public TextTO disconnectSite()
		{
            return (TextTO)MySession.execute("ConnectionLib", "disconnectSite", new object[] { });
		}

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Get a site")]
        public SiteTO getSite(string sitecode)
        {
            return (SiteTO)MySession.execute("SitesLib", "getSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Visit multiple VistA systems.")]
        public TaggedTextArray visitSites(string pwd, string sitelist, string context)
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "visitSites", new object[] { pwd, sitelist, context }); ;
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from multiple Vista systems.")]
        public TaggedTextArray disconnectSites()
        {
            return (TaggedTextArray)MySession.execute("ConnectionLib", "disconnectAll", new object[] { } );
        }

        [WebMethod(EnableSession = true, Description = "Match by name, city, state at visited sites.")]
        public TaggedPatientArrays matchByNameCityStateMS(string name, string city, string stateAbbr)
        {
            return (TaggedPatientArrays)MySession.execute("PatientLib", "matchByNameCityStateMS", new object[] { name, city, stateAbbr });
        }

		[WebMethod(EnableSession = true, Description = "Locate a patient in VHA.")]
		public PatientLocationTO locatePatient(string sitecode, string DFN)
		{
            return (PatientLocationTO)MySession.execute("PatientLib", "locatePatient", new object[] { sitecode, DFN });
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

        [WebMethod(EnableSession = true, Description = "Get the cities and zipcodes in a given state.")]
        public TaggedTextArray matchCityAndState(string city, string stateAbbr)
        {
            return (TaggedTextArray)MySession.execute("SitesLib", "matchCityAndState", new object[] { city, stateAbbr });
        }

        [WebMethod(EnableSession = true, Description = "Get the cities and zipcodes in a given state.")]
        public SiteArray getFacilitiesForCounty(string fips)
        {
            return (SiteArray)MySession.execute("SitesLib", "getSitesForCounty", new object[] { fips });
        }

        [WebMethod(EnableSession = true, Description = "Get the VISNs in a given state.")]
        public string[] getVisnsForState(string state)
        {
            return (string[])MySession.execute("SitesLib", "getVisnsForState", new object[] { state });
        }
    }
}
