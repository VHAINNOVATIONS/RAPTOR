using System;
using System.Web;
using System.Collections;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws;
using gov.va.medora.mdws.dto;

/// <summary>
/// Summary description for UserMgtService
/// </summary>
namespace gov.va.medora.mdws.userMgt
{
    /// <summary>
    /// Summary description for UserMgtService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/userMgt/UserMgtService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    public partial class UserMgtService : BaseService, IUserMgtService
    {
        public UserMgtService()  { }

        [WebMethod(EnableSession = true, Description = "Get all VHA sites")]
        public RegionArray getVHA()
        {
            return (RegionArray)MySession.execute("SitesLib", "getVHA", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Connect to a single VistA system.")]
        public DataSourceTO connectSite(string sitecode)
        {
            return (DataSourceTO)MySession.execute("ConnectionLib", "connectSite", new object[] { sitecode });
        }

        [WebMethod(EnableSession = true, Description = "Log onto a single VistA system.")]
        public UserTO login(string username, string pwd, string context)
        {
            return (UserTO)MySession.execute("AccountLib", "login", new object[] { username, pwd, context });
        }

        [WebMethod(EnableSession = true, Description = "Disconnect from single VistA system.")]
        public TextTO disconnectSite()
        {
            return (TextTO)MySession.execute("ConnectionLib", "disconnectSite", new object[] { });
        }

        [WebMethod(EnableSession = true, Description = "Visit single VistA system.")]
        public UserTO visitSite(string pwd, string sitecode, string userSitecode, string userName, string DUZ, string SSN, string context)
        {
            return (UserTO)MySession.execute("ConnectionLib", "visitSite", new object[] { pwd, sitecode, userSitecode, userName, DUZ, SSN, context });
        }

        [WebMethod(EnableSession = true)]
        public UserArray cprsUserLookup(string target)
        {
            return (UserArray)MySession.execute("UserLib", "cprsUserLookup", new object[] { target });
        }

        [WebMethod(EnableSession = true)]
        public UserArray lookup(string target, string maxRex)
        {
            return (UserArray)MySession.execute("UserLib", "lookup", new object[] { target, maxRex });
        }

        [WebMethod(EnableSession = true)]
        public UserTO getUserInfo(string DUZ)
        {
            return (UserTO)MySession.execute("UserLib", "getUserInfo", new object[] { DUZ });
        }
    }

    interface IUserMgtService
    {
        RegionArray getVHA();
        DataSourceTO connectSite(string sitecode);
        UserTO login(string username, string pwd, string context);
        TextTO disconnectSite();
        UserTO visitSite(string pwd, string sitelist, string userSitecode, string userName, string DUZ, string SSN, string context);
        UserArray cprsUserLookup(string target);
        UserTO getUserInfo(string DUZ);
        UserArray lookup(string target, string maxRex);
    }
}
