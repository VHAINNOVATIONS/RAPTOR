<?php

defined('RAPTOR_CONFIG_ID')
    or define('RAPTOR_CONFIG_ID', '20150930.2 963 for VA 146 AppServer1');

/*
 * These are RAPTOR INSTANCE specific settings
 */

//VISTA values that might change per site
defined('VISTA_SITE')
    or define('VISTA_SITE', '963');	
defined('VISTA_NOTEIEN_RAPTOR_GENERAL')
    or define('VISTA_NOTEIEN_RAPTOR_GENERAL', '4906');
defined('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST')
    or define('VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST', '4907');

//Help with diagnostics
defined('LOG_WORKLIST_METADATA')
    or define('LOG_WORKLIST_METADATA', FALSE);  //Leave as FALSE except for temporary diagnostics

//Other site specific values
defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://10.184.38.146/RSite963/RAPTOR/");

defined("EMRSERVICE_URL")
    or define("EMRSERVICE_URL", "http://10.184.38.146/mdws2.5/emrsvc.asmx");
defined("QUERYSERVICE_URL")
    or define("QUERYSERVICE_URL", "http://10.184.38.146/mdws2.5/querysvc.asmx");

//Point to HYDRA server
defined("VIX_HTML_VIEWER_URL")
    or define("VIX_HTML_VIEWER_URL", "http://10.184.38.146:9000/hydra/viewer");

//Point to VIX server
defined("VIX_STUDIES_URL")
    or define("VIX_STUDIES_URL", "http://10.184.38.149:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined("VIX_THUMBNAIL_URL")
    or define("VIX_THUMBNAIL_URL", "http://10.184.38.149:8090/RaptorWebApp/token/thumbnail");

//Lab settings might be different for different sites
defined('LAB_EGFR_ALERT_WARN_END_LEVEL')
    or define('LAB_EGFR_ALERT_WARN_END_LEVEL', 60);
defined('LAB_EGFR_ALERT_BAD_END_LEVEL')
    or define('LAB_EGFR_ALERT_BAD_END_LEVEL', 30);

//Base URL for the EWD Federator
defined('EWDFED_BASE_URL')
    or define('EWDFED_BASE_URL', "BASEURLTODO");

defined('IMG_INT_MODULE_NAME')
    or define('IMG_INT_MODULE_NAME', 'raptor_imageviewing');

defined('EHR_INT_MODULE_NAME')
    or define('EHR_INT_MODULE_NAME', 'raptor_mdwsvista');

//Disable the AGE scoring if using a test VISTA with VERY OLD orders.
defined('DISABLE_TICKET_AGE1_SCORING')
    or define('DISABLE_TICKET_AGE1_SCORING', FALSE);
defined('DISABLE_TICKET_AGE2_SCORING')
    or define('DISABLE_TICKET_AGE2_SCORING', FALSE);

//Set DAYS very high if using a test VISTA with VERY OLD orders.
defined('DEFAULT_GET_VISIT_DAYS')
    or define('DEFAULT_GET_VISIT_DAYS', 30); //For PROD set this to 30 days!!!!!!

//Set default number of days for which to get labs
defined('DEFAULT_GET_LABS_DAYS')
    or define('DEFAULT_GET_LABS_DAYS', 8000);

//Worklist rank score factors (Icons show at crit>600; urgent>450; high>200; medium>100; low>20)
defined('WLSCORE_URGENCY_STAT')     //Suggested value is 500
    or define('WLSCORE_URGENCY_STAT', 500);
defined('WLSCORE_URGENCY_URGENT')   //Suggested value is 250
    or define('WLSCORE_URGENCY_URGENT', 250);
defined('WLSCORE_ASSIGNED')         //Suggested value is 100
    or define('WLSCORE_ASSIGNED', 100);
defined('WLSCORE_MODALITY')         //Suggested value is 20
    or define('WLSCORE_MODALITY', 20);
defined('WLSCORE_REVIEW')           //Suggested value is 5
    or define('WLSCORE_REVIEW', 5);
defined('WLSCORE_KWM_MOST')         //Suggested value is 20
    or define('WLSCORE_KWM_MOST', 20);
defined('WLSCORE_KWM_MODERATE')      //Suggested value is 10
    or define('WLSCORE_KWM_MODERATE', 10);
defined('WLSCORE_KWM_LEAST')         //Suggested value is 5
    or define('WLSCORE_KWM_LEAST', 5);
defined('WLSCORE_MISSED_SCHED_DT')   //Suggested value is 900
    or define('WLSCORE_MISSED_SCHED_DT', 900);
defined('WLSCORE_SCHED_IN24HR')      //Suggested value is 500
    or define('WLSCORE_SCHED_IN24HR', 500);
defined('WLSCORE_SCHED_DAYSAWAYIN7') //Suggested value is 100
    or define('WLSCORE_SCHED_DAYSAWAYIN7', 100);
defined('WLSCORE_AGE_WEEKS_FACTOR')  //Suggested value is 10
    or define('WLSCORE_AGE_WEEKS_FACTOR', 10);
defined('WLSCORE_AGE_DAYS_FACTOR')   //Suggested value is 1
    or define('WLSCORE_AGE_DAYS_FACTOR', 1);

//Control level of effort in pulling worklist contents from EHR system
//Scans min(WORKLIST_MAXROWS_PER_QUERY * WORKLIST_MAX_QUERY_LOOPS, WORKLIST_ENOUGH_ROWS_COUNT)
//Important: Duplicate checking only reliable if enough rows are scanned
defined('WORKLIST_MAXROWS_PER_QUERY')   //Suggested value is 1000
    or define('WORKLIST_MAXROWS_PER_QUERY', 1000);
defined('WORKLIST_MAX_QUERY_LOOPS')   //Suggested value is 5
    or define('WORKLIST_MAX_QUERY_LOOPS', 5);
defined('WORKLIST_ENOUGH_ROWS_COUNT')   //Suggested value is 5000
    or define('WORKLIST_ENOUGH_ROWS_COUNT', 5000);
defined('WORKLIST_ENOUGH_ROWS_TO_FIND_DUPS')   //Suggested value is 1000 (KEEP THIS HIGH!)
    or define('WORKLIST_ENOUGH_ROWS_TO_FIND_DUPS', 1000);

//On initial deployment, there may be many old tickets you want to ignore.
defined('OLDEST_WORKLIST_TICKET_ID')    
    or define('OLDEST_WORKLIST_TICKET_ID', 2114000);   //To disable this, assign NULL

//Control availability of resource intensive configuration features (NULL enables for everyone)
defined('ENABLE_RAPTOR_PERFORMANCE_TUNING_SPECIAL_PRIV')
    //or define('ENABLE_RAPTOR_PERFORMANCE_TUNING_SPECIAL_PRIV', NULL);	
    or define('ENABLE_RAPTOR_PERFORMANCE_TUNING_SPECIAL_PRIV', 'RUN_PERFORMANCE_CHECK=1');	
