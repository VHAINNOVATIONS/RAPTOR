using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao.vista;

/// <summary>
/// Summary description for VwsConstants
/// </summary>
namespace gov.va.medora.mdws
{
    public class MdwsConstants
    {
        // Paths
        public const string STATES_FILE_NAME = "xml\\VhaSitesByState.xml";
        public const string VISNS_BY_STATE_FILE_NAME = "xml\\VisnsByState.xml";
        public const string DEFAULT_SITES_FILE_NAME = "VhaSites.xml";
        // Session vars
        // NOTE: If you change SITE_TABLE, make sure you change web.config as well
        public const String SITE_TABLE = "VhaSiteTable";
        public const string RUNTIME_PROPERTIES = "RuntimeProperties";
        public const String CXN_API = "CxnApi";
        public const String VISIT_CXN = "VisitCxn";
        public const String THE_USER = "TheUser";
        public const String THE_PATIENT = "ThePatient";
        public const String REMOTE_SITES = "RemoteSites";
        public const String MULTI_SOURCE_QUERY = "MultiSourceQuery";
        public const String PATIENT_SITES = "PatientSites";

        public const string CPRS_CONTEXT = VistaConstants.CPRS_CONTEXT;
        public const string CAPRI_CONTEXT = VistaConstants.CAPRI_CONTEXT;
        public const string MDWS_CONTEXT = VistaConstants.MDWS_CONTEXT;

        public const string CXN_MGR_NOT_READY = "There are no open connections";
        public const string NEED_TO_LOGIN = "Need to login?";

        public const string LOGIN_CREDENTIALS = VistaConstants.LOGIN_CREDENTIALS;
        public const string BSE_CREDENTIALS_V2WEB = VistaConstants.BSE_CREDENTIALS_V2WEB;
        public const string BSE_CREDENTIALS_V2V = VistaConstants.BSE_CREDENTIALS_V2V;
        public const string NON_BSE_CREDENTIALS = VistaConstants.NON_BSE_CREDENTIALS;

        public const string NON_BSE_SECURITY_PHRASE = "NON-BSE";
        public const string MY_SECURITY_PHRASE = "Good players are always lucky";

        public const string DOD_SITE = "200";
    }
}
