using System;

// This is just a crazy comment. *CRAZY*
namespace gov.va.medora.mdo.conf
{
    public class ConfigFileConstants
    {
        public static string CONFIG_FILE_NAME = "mdws.conf";

        public static string PRIMARY_CONFIG_SECTION = "MAIN";
        public static string BSE_CONFIG_SECTION = "BSE";
        public static string SQL_CONFIG_SECTION = "SQL";
        public static string VBA_CORP_CONFIG_SECTION = "VBACORP SQL";
        public static string NPT_CONFIG_SECTION = "NPT SQL";
        public static string VADIR_CONFIG_SECTION = "VADIR SQL";
        public static string MHV_CONFIG_SECTION = "MHV SQL";
        public static string ADR_CONFIG_SECTION = "ADR SQL";
        public static string CDW_CONFIG_SECTION = "CDW SQL";
        public static string MOS_CONFIG_SECTION = "MOS SQL";
        public static string APP_PROXY_CONFIG_SECTION = "APPLICATION PROXY";
        public static string SM_CONFIG_SECTION = "SM";

        public static string SERVICE_ACCOUNT_CONFIG_SECTION = "Administrative IDs";

        /// <summary>
        /// The SQL connection string
        /// </summary>
        public static string CONNECTION_STRING = "ConnectionString";
        /// <summary>
        /// The SQL database name
        /// </summary>
        public static string SQL_DB = "SqlDatabase";
        /// <summary>
        /// SQL database password
        /// </summary>
        public static string SQL_PASSWORD = "SqlPassword";
        /// <summary>
        /// SQL database path
        /// </summary>
        public static string SQL_HOSTNAME = "SqlHostname";
        /// <summary>
        /// SQL database username
        /// </summary>
        public static string SQL_USERNAME = "SqlUsername";
        /// <summary>
        /// SQL port
        /// </summary>
        public static string SQL_PORT = "SqlPort";
        /// <summary>
        /// The BSE data encryption key
        /// </summary>
        public static string BSE_SQL_ENCRYPTION_KEY = "EncryptionKey";
        /// <summary>
        /// Valid NHIN data types
        /// </summary>
        public static string NHIN_TYPES = "NhinTypes";
        /// <summary>
        /// The BSE security phrase for MDWS/MDO
        /// </summary>
        public static string SECURITY_PHRASE = "SecurityPhrase";
        /// <summary>
        /// The hashed BSE security phrase for MDWS/MDO
        /// </summary>
        public static string HASHED_SECURITY_PHRASE = "HashedSecurityPhrase";
        /// <summary>
        /// The service account ID
        /// </summary>
        public static string ADMIN_FEDERATED_UID = "AdminUserID";

        public static string RUNAS_USER_DOMAIN = "RunasUserDomain";

        public static string RUNAS_USER_NAME = "RunasUserName";

        public static string RUNAS_USER_PASSWORD = "RunasUserPassword";

        public static string LDAP_CONFIG_SECTION = "LDAP";

        #region SM EMAIL
        public static string SM_EMAIL_FROM = "EmailFrom";

        public static string SM_EMAIL_SUBJECT = "EmailSubject";

        public static string SM_EMAIL_LINK = "EmailLink";

        public static string SM_EMAIL_BODY = "EmailBody";

        public static string SMTP_DOMAIN = "SmtpHostOrIP";

        public static string SMTP_PORT = "SmtpPort";

        public static string SM_EMAIL_DELIVERY_METHOD = "SmtpDeliveryMethod";

        public static string SM_CONNECTION_STRING = "ConnectionString";

        #endregion
    }
}