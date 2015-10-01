using System;
using System.Collections.Generic;
using System.Configuration;
using System.Text;
using gov.va.medora.mdo.conf;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaConstants
    {
	    public const string DDR_CONTEXT = "DVBA CAPRI GUI";
        public const string CAPRI_CONTEXT = "DVBA CAPRI GUI";
	    public const string CPRS_CONTEXT = "OR CPRS GUI CHART";
	    public const string BCMA_CONTEXT = "PSB GUI CONTEXT - USER";
	    public const string VISTA_IMAGING_CONTEXT = "MAG WINDOWS";
        public const string MDWS_CONTEXT = "MWVS MEDICAL DOMAIN WEB SVCS";
        public const string VPR_CONTEXT = "VPR APPLICATION PROXY";

        public const string NON_BSE_SECURITY_PHRASE = "NON-BSE";
        //public static string HASHED_SECURITY_PHRASE = new MdoConfiguration(true, ConfigFileConstants.CONFIG_FILE_NAME)
        //    .AllConfigs[ConfigFileConstants.PRIMARY_CONFIG_SECTION][ConfigFileConstants.HASHED_SECURITY_PHRASE];
        //public static string MY_SECURITY_PHRASE = new MdoConfiguration(true, ConfigFileConstants.CONFIG_FILE_NAME)
        //    .AllConfigs[ConfigFileConstants.PRIMARY_CONFIG_SECTION][ConfigFileConstants.SECURITY_PHRASE];
        //public static string ENCRYPTION_KEY = new MdoConfiguration(true, ConfigFileConstants.CONFIG_FILE_NAME)
        //    .AllConfigs[ConfigFileConstants.PRIMARY_CONFIG_SECTION][ConfigFileConstants.BSE_SQL_ENCRYPTION_KEY];
        public static string HASHED_SECURITY_PHRASE = "WHgafhAkItJqu&]Cbp<H";
        public static string MY_SECURITY_PHRASE = "Good players are always lucky";
        public static string ENCRYPTION_KEY = "Another day in paradise";

        public const string LOGIN_CREDENTIALS = "LOGIN CREDENTIALS";
        public const string BSE_CREDENTIALS_V2WEB = "BSE V2W CREDENTIALS";
        public const string BSE_CREDENTIALS_V2V = "BSE V2V CREDENTIALS";
        public const string NON_BSE_CREDENTIALS = "NON-BSE CREDENTIALS";

        public const string BEGIN_DATA = "[BEGIN_diDATA]";
        public const string END_DATA = "[END_diDATA]";
        public const string MISC = "[Misc]";
        public const string BEGIN_ERRS = "[BEGIN_diERRORS]";
        public const string END_ERRS = "[END_diERRORS]";
        public const string BEGIN_HELP = "[BEGIN_diHELP]";
        public const string END_HELP = "[END_diHELP]";
        public const string BEGIN_IENS = "BEGIN_IENs";
        public const string END_IENS = "END_IENs";
        public const string BEGIN_IDVALS = "BEGIN_IDVALUES";
        public const string END_IDVALS = "END_IDVALUES";
        public const string BEGIN_WIDVALS = "BEGIN_WIDVALUES";
        public const string END_WIDVALS = "END_WIDVALUES";
        public const string UNPACKED_NO_RESULTS = "0^*^0^";

	    // Order actions
	    public const string OA_COPY = "RW";
	    public const string OA_CHANGE = "XX";
	    public const string OA_RENEW = "RN";
	    public const string OA_HOLD = "HD";
	    public const string OA_DC = "DC";
	    public const string OA_UNHOLD = "RL";
	    public const string OA_FLAG = "FL";
	    public const string OA_UNFLAG = "UF";
	    public const string OA_COMPLETE = "CP";
	    public const string OA_ALERT = "AL";
	    public const string OA_REFILL = "RF";
	    public const string OA_VERIFY = "VR";
	    public const string OA_CHART = "CR";
	    public const string OA_RELEASE = "RS";
	    public const string OA_SIGN = "ES";
	    public const string OA_ONCHART = "OC";
	    public const string OA_COMMENT = "CM";
	    public const string OA_TRANSFER = "XFR";
	    public const string OA_CHGEVT = "EV";
	    public const string OA_EDREL = "MN";

	    // Sign & release orders
	    public const string SS_ONCHART  = "0";
	    public const string SS_ESIGNED  = "1";
	    public const string SS_UNSIGNED = "2";
	    public const string SS_NOTREQD  = "3";
	    public const string SS_DIGSIG   = "7";
	    public const string RS_HOLD     = "0";
	    public const string RS_RELEASE  = "1";
	    public const string NO_PROVIDER = "E";
	    public const string NO_VERBAL   = "V";
	    public const string NO_PHONE    = "P";
	    public const string NO_POLICY   = "I";
	    public const string NO_WRITTEN  = "W";

	    // Globals
	    public const string SURGERY_130 = "^SRF(";
	    public const string LABS_60 = "^LAB(60,";

	    // Miscellaneous
	    public const string CONCAT_STR = "_\"^\"_";

        // File Numbers
        public const string DELEGATED_OPTIONS_FILE = "200.19";
        public const string MENU_OPTIONS_FILE = "200.03";
        public const string MENU_OPTIONS = "203";
        public const string MEDICAL_CENTER_DIVISION = "40.8";
        public const string FACILITY_MOVEMENT_TYPES = "405.1";
        public const string MAS_MOVEMENT_TYPES = "405.2";
        public const string PTF_SPECIALTIES = "42.4";
        public const string SERVICES = "49";
        public const string WARD_LOCATIONS = "42";
        public const string ROOM_BEDS = "405.4";
        public const string CLINIC_STOPS = "40.7";
        public const string LOCATION_TYPES = "40.9";
        public const string INSTITUTIONS = "4";
        public const string APPT_TYPES = "409.1";
        public const string TREATING_SPECIALTY = "45.7";
        public const string ORDER_STATUS = "100.01";
        public const string SECURITY_KEY = "19.1";
        public const string TOPOGRAPHY_FIELD = "61";
        public const string TIU_VHA_ENTERPRISE = "8926.1";
        public const string PATIENT_RACES = "10";
        public const string PATIENT_MARITAL_STATUSES = "11";
        public const string PATIENT_TYPES = "391";
        public const string URGENCY_CODES = "62.05";
        public const string COLLECTION_SAMPLE = "62";
        public const string IV_SOLUTIONS = "52.7";
        public const string PHARMACY_ORDERABLE_ITEM = "50.7";
        public const string PHARMACY_PATIENT = "55";
        public const string PHARMACY_PATIENT_IV_MULTIPLE = "55.01";
        public const string MH_ADMINISTRATIONS = "601.84";
        public const string MH_TESTS_AND_SURVEYS = "601.71";
        public const string MH_RESULTS = "601.92";
        public const string MH_SCALES = "601.87";

        public const string GET_VARIABLE_VALUE = "XWB GET VARIABLE VALUE";

        public static string VISTA_FILEDEFS_PATH = utils.ResourceUtils.ResourcesPath + "xml/VistaFiles.xml";

        public static string ADMINISTRATIVE_FEDERATED_UID = "123456789"; // new MdoConfiguration(true, ConfigFileConstants.CONFIG_FILE_NAME)
            //.AllConfigs[ConfigFileConstants.PRIMARY_CONFIG_SECTION][ConfigFileConstants.ADMIN_FEDERATED_UID];


        public static string SCHEDULING_CONTEXT = "SD API";

    }
}
