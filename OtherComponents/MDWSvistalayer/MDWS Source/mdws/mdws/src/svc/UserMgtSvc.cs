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
    /// Summary description for UserMgtSvc
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/UserMgtSvc/")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class UserMgtSvc : BaseService
    {
        public UserMgtSvc()
		{
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
		public TextTO disconnectSite()
		{
			return (TextTO)MySession.execute("ConnectionLib","disconnectSite",new object[] {} );
		}

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
		public RegionArray getVHA()
		{
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
		}

		[WebMethod(EnableSession = true, Description = "Visit multiple VistA systems.")]
		public TaggedTextArray visitSites(string pwd, string sitelist, string context)
		{
            return (TaggedTextArray)MySession.execute("AccountLib","visitSites",new object[] {pwd,sitelist,context} );
		}

		[WebMethod(EnableSession = true, Description = "Disconnect from multiple Vista systems.")]
		public TaggedTextArray disconnectSites()
		{
			return (TaggedTextArray)MySession.execute("ConnectionLib","disconnectAll",new object[] {} );
		}

		[WebMethod(EnableSession = true)]
		public TaggedTextArray ddrLister(
			string file,
			string iens,
			string fields,
			string flags,
			string maxrex,
			string from,
			string part,
			string xref,
			string screen,
			string identifier)
		{
			return (TaggedTextArray)MySession.execute("ToolsLib","ddrListerMS",new object[] {file,iens,fields,flags,maxrex,from,part,xref,screen,identifier} );
		}

		[WebMethod(EnableSession = true, Description = "Lookup user from single VistA system.")]
		public UserArray lookupByName(string sitecode, string target, string maxRex)
		{
            return (UserArray)MySession.execute("UserLib", "lookup", new object[] { sitecode, target, maxRex });
		}

		[WebMethod(EnableSession = true, Description = "Lookup user from multiple VistA systems.")]
		public TaggedUserArrays lookupByNameMS(string target, string maxRex)
		{
            return (TaggedUserArrays)MySession.execute("UserLib", "lookupMS", new object[] { target, maxRex });
		}

		[WebMethod(EnableSession = true, Description = "Get user.")]
		public UserTO getUser(string sitecode, string DUZ)
		{
            return (UserTO)MySession.execute("UserLib", "getUser", new object[] { sitecode, DUZ });
		}

		[WebMethod(EnableSession = true, Description = "Get user DUZs from multiple VistA systems.")]
		public TaggedTextArray getUserDUZBySSN(string SSN)
		{
            return (TaggedTextArray)MySession.execute("UserLib", "getUserIdBySSN", new object[] { SSN });
		}

		[WebMethod(EnableSession = true, Description = "Add security key for context.")]
		public TextTO addSecurityKeyForContext(string sitecode, string DUZ, string context)
		{
            return (TextTO)MySession.execute("UserLib", "addSecurityKeyForContext", new object[] { sitecode, DUZ, context });
		}

		[WebMethod(EnableSession = true, Description = "Add security key for user.")]
		public TextTO addSecurityKey(string sitecode, string DUZ, string securityKey)
		{
            return (TextTO)MySession.execute("UserLib", "addSecurityKey", new object[] { sitecode, DUZ, securityKey });
		}

		[WebMethod(EnableSession = true, Description = "Remove security key for user.")]
		public TextTO removeSecurityKey(string sitecode, string DUZ, string securityKey)
		{
            return (TextTO)MySession.execute("UserLib", "removeSecurityKey", new object[] { sitecode, DUZ, securityKey });
		}

		[WebMethod(EnableSession = true, Description = "Add menu option for user.")]
		public TextTO addMenuOption(string sitecode, string context, string DUZ)
		{
            return (TextTO)MySession.execute("UserLib", "addMenuOption", new object[] { sitecode, context, DUZ });
		}

		[WebMethod(EnableSession = true, Description = "Remove menu option for user.")]
		public TextTO removeMenuOption(string sitecode, string optNum, string DUZ)
		{
            return (TextTO)MySession.execute("UserLib", "removeMenuOption", new object[] { sitecode, optNum, DUZ });
		}

		[WebMethod(EnableSession = true, Description = "Get security keys for user.")]
		public UserSecurityKeyArray getSecurityKeys(string sitecode, string DUZ)
		{
            return (UserSecurityKeyArray)MySession.execute("UserLib", "getSecurityKeys", new object[] { sitecode, DUZ } );
		}

		[WebMethod(EnableSession = true, Description = "Get menu options for user.")]
		public UserOptionArray getMenuOptions(string sitecode, string DUZ)
		{
            return (UserOptionArray)MySession.execute("UserLib", "getMenuOptions", new object[] { sitecode, DUZ });
		}

		[WebMethod(EnableSession = true, Description = "Get delegated options for user.")]
		public UserOptionArray getDelegatedOptions(string sitecode, string DUZ)
		{
            return (UserOptionArray)MySession.execute("UserLib", "getDelegatedOptions", new object[] { sitecode, DUZ });
		}

        [WebMethod(EnableSession = true)]
        public GeographicLocationArray getGeographicLocations(string zipcode)
        {
            return (GeographicLocationArray)MySession.execute("SitesLib", "getGeographicLocations", new object[] { zipcode });
        }

        [WebMethod(EnableSession = true)]
        public SiteArray getSiteDivisions(string sitecode)
        {
            return (SiteArray)MySession.execute("EncounterLib", "getSiteDivisions", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Send an smtp email message through MDWS")]
        public TextTO sendEmail(string from, string to, string subject, string body, string isBodyHTML, string username, string password)
        {
            return (TextTO)MySession.execute("ToolsLib", "sendEmail", new object[] { from, to, subject, body, isBodyHTML, username, password });
        }

        [WebMethod(EnableSession = true)]
        public TextTO getVariableValue(string arg)
        {
            return (TextTO)MySession.execute("ToolsLib", "getVariableValue", new object[] { arg });
        }

    }
}
