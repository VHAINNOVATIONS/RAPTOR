<?php

defined('RAPTOR_CONFIG_ID')
    or define('RAPTOR_CONFIG_ID', '20150713.1 978 for VA 146 AppServer1');

/*
 * These are RAPTOR INSTANCE specific settings
 */

//VISTA values that might change per site
defined('VISTA_SITE')
    or define('VISTA_SITE', '978');	
defined('VISTA_NOTEIEN_RAPTOR_GENERAL')
    or define('VISTA_NOTEIEN_RAPTOR_GENERAL', '3709');
defined('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST')
    or define('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST', '3710');

//Other site specific values
defined('RAPTOR_ROOT_URL')
    or define("RAPTOR_ROOT_URL", "http://10.184.38.146/RSite978/RAPTOR/");

defined('EMRSERVICE_URL')
    or define('EMRSERVICE_URL', "http://10.184.38.146/mdws2.5/emrsvc.asmx");
defined('QUERYSERVICE_URL')
    or define('QUERYSERVICE_URL', "http://10.184.38.146/mdws2.5/querysvc.asmx");

//Point to HYDRA server
defined('VIX_HTML_VIEWER_URL')
    or define('VIX_HTML_VIEWER_URL', "http://10.184.38.146:9000/hydra/viewer");

//Point to VIX server
defined('VIX_STUDIES_URL')
    or define('VIX_STUDIES_URL', "http://10.184.38.149:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined('VIX_THUMBNAIL_URL')
    or define("VIX_THUMBNAIL_URL", "http://10.184.38.149:8090/RaptorWebApp/token/thumbnail");

//Lab settings might be different for different sites
defined('LAB_EGFR_ALERT_WARN_END_LEVEL')
    or define('LAB_EGFR_ALERT_WARN_END_LEVEL', 60);
defined('LAB_EGFR_ALERT_BAD_END_LEVEL')
    or define('LAB_EGFR_ALERT_BAD_END_LEVEL', 30);

//Base URL for the EWD Federator
defined('EWDFED_BASE_URL')
    or define('EWDFED_BASE_URL', "BASEURLTODO");
