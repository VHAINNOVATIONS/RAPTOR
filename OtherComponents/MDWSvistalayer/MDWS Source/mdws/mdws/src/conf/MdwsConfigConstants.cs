using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.conf;

namespace gov.va.medora.mdws.conf
{
    public class MdwsConfigConstants : ConfigFileConstants
    {
        public static string MDWS_CONFIG_SECTION = "MAIN";
        /// <summary>
        /// MDWS sessions log level
        /// </summary>
        public static string SESSIONS_LOG_LEVEL = "SessionsLogLevel";
        /// <summary>
        /// MDWS sessions logging
        /// </summary>
        public static string SESSIONS_LOGGING = "SessionsLogging";
        /// <summary>
        /// Production installation
        /// </summary>
        public static string MDWS_PRODUCTION = "Production";
        /// <summary>
        /// The facade sites file name
        /// </summary>
        public static string FACADE_SITES_FILE = "FacadeSitesFile";
        /// <summary>
        /// True/False
        /// </summary>
        public static string FACADE_PRODUCTION = "FacadeProduction";
        /// <summary>
        /// The facade version information
        /// </summary>
        public static string FACADE_VERSION = "FacadeVersion";

        public static string DEFAULT_VISIT_METHOD = "VisitMethod";

        public static string DEFAULT_CONTEXT = "DefaultContext";
        
        public static string EXCLUDE_SITE_200 = "ExcludeSite200";

        public static string WATCH_SITES_FILE = "WatchSitesFile";

        public static string TIMEOUT = "TimeOut";

        public static string APP_PROXY_NAME = "Name";
        public static string APP_PROXY_USERNAME = "Username";
        public static string APP_PROXY_PASSWORD = "Password";
        public static string APP_PROXY_UID = "UserId";
        public static string APP_PROXY_FEDUID = "FedUserId";
        public static string APP_PROXY_PHONE = "UserPhone";
        public static string APP_PROXY_SITE_ID = "UserSiteId";
        public static string APP_PROXY_SITE_NAME = "UserSiteName";
        public static string APP_PROXY_PERMISSION = "Permission";
        public static string APP_PROXY_CRED_TYPE = "CredentialsType";

        public static string CONNECTION_POOL_CONFIG_SECTION = "Connection Pool";
        public static string CONNECTION_POOLING = "UseConnectionPool";
        public static string CONNECTION_POOL_MAX_CXNS = "MaxCxns";
        public static string CONNECTION_POOL_MIN_CXNS = "MinCxns";
        public static string CONNECTION_POOL_EXPAND_SIZE = "ExpansionSize";
        public static string CONNECTION_POOL_WAIT_TIME = "WaitTime";
        public static string CONNECTION_POOL_CXN_TIMEOUT = "TimeOut";
        public static string CONNECTION_POOL_LOAD_STRATEGY = "LoadStrategy";

    }
}