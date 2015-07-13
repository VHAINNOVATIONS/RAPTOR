<?php

defined('RAPTOR_CONFIG_ID')
    or define('RAPTOR_CONFIG_ID', '20150713.1 Config for 54.235.122.35');

/*
 * These are RAPTOR INSTANCE specific settings
 */

//VISTA values that might change per site
defined('VISTA_SITE')
    or define('VISTA_SITE', '500');	
defined('VISTA_NOTEIEN_RAPTOR_GENERAL')
    or define('VISTA_NOTEIEN_RAPTOR_GENERAL', '142');
defined('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST')
    or define('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST', '149');

//Other site specific values
defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://54.235.122.35/RSite500/RAPTOR/");

defined("EMRSERVICE_URL")
    or define("EMRSERVICE_URL", "http://localhost/mdws2.5/emrsvc.asmx");
defined("QUERYSERVICE_URL")
    or define("QUERYSERVICE_URL", "http://localhost/mdws2.5/querysvc.asmx");

defined("VIX_STUDIES_URL")
    or define("VIX_STUDIES_URL", "http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined("VIX_THUMBNAIL_URL")
    or define("VIX_THUMBNAIL_URL", "http://54.235.122.35:8090/RaptorWebApp/token/thumbnail");
defined("VIX_HTML_VIEWER_URL")
    or define("VIX_HTML_VIEWER_URL", "http://54.235.122.35:9000/hydra/viewer");

//Lab settings might be different for different sites
defined('LAB_EGFR_ALERT_WARN_END_LEVEL')
    or define('LAB_EGFR_ALERT_WARN_END_LEVEL', 60);
defined('LAB_EGFR_ALERT_BAD_END_LEVEL')
    or define('LAB_EGFR_ALERT_BAD_END_LEVEL', 30);
