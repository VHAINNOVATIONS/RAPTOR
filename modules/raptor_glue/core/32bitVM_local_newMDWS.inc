<?php
    
//LOCAL 32bit VM

defined("RAPTOR_CONFIG_ID")
    or define("RAPTOR_CONFIG_ID", "Config for TEST 32bit localhost");

defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://localhost/drupal/");

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
