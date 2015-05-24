<?php
    
//DEPRECATED CONFIGURATION UPGRADE IF YOU ARE USING THIS ONE!!!!

defined("RAPTOR_CONFIG_ID")
    or define("RAPTOR_CONFIG_ID", "20150522.1 Config for TEST 32bit localhost");

/*
 * These are RAPTOR INSTANCE specific settings
 */

defined("VISTA_SITE")
    or define("VISTA_SITE", '500');	

defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://localhost/drupal/raptor/");

// NEW latest MDWS installation w/ order writing APIs uses new path
defined("EMRSERVICE_URL")
    or define("EMRSERVICE_URL", "http://localhost:8888/mdws2.5/emrsvc.asmx");
defined("QUERYSERVICE_URL")
    or define("QUERYSERVICE_URL", "http://localhost:8888/mdws2.5/querysvc.asmx");

defined("EMRSERVICE_LOCAL_FILE")
    or define("EMRSERVICE_LOCAL_FILE", "C:\\BitNami\\drupal-7.31-1\\apps\\drupal\\htdocs\\sites\\all\\modules\\raptor_datalayer\\core\\emrsvc.wsdl");
defined("QUERYSERVICE_LOCAL_FILE")
    or define("QUERYSERVICE_LOCAL_FILE", "C:\\BitNami\\drupal-7.31-1\\apps\\drupal\\htdocs\\sites\\all\\modules\\raptor_datalayer\\core\\querysvc.wsdl");
defined("VIX_STUDIES_URL")
    or define("VIX_STUDIES_URL", "http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined("VIX_THUMBNAIL_URL")
    or define("VIX_THUMBNAIL_URL", "http://localhost:8090/RaptorWebApp/token/thumbnail");
defined("VIX_HTML_VIEWER_URL")
    or define("VIX_HTML_VIEWER_URL", "http://localhost:9000/Viewer/viewer.html");
