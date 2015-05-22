<?php

defined("RAPTOR_CONFIG_ID")
    or define("RAPTOR_CONFIG_ID", "20150522.1 948 for VA 147 AppServer2");

/*
 * These are RAPTOR INSTANCE specific settings
 */

defined("VISTA_SITE")
    or define("VISTA_SITE", '948');	
	
defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://10.184.38.147/RSite948/");

defined("EMRSERVICE_URL")
    or define("EMRSERVICE_URL", "http://10.184.38.147/mdws2.5/emrsvc.asmx");
defined("QUERYSERVICE_URL")
    or define("QUERYSERVICE_URL", "http://10.184.38.147/mdws2.5/querysvc.asmx");

defined("EMRSERVICE_LOCAL_FILE")
    or define("EMRSERVICE_LOCAL_FILE", "C:\\inetpub\\wwwroot\\RSite948\\sites\\all\\modules\\raptor_datalayer\\core\\emrsvc.wsdl");
defined("QUERYSERVICE_LOCAL_FILE")
    or define("QUERYSERVICE_LOCAL_FILE", "C:\\inetpub\\wwwroot\\RSite948\\sites\\all\\modules\\raptor_datalayer\\core\\querysvc.wsdl");

//Point to HYDRA server
defined("VIX_HTML_VIEWER_URL")
    or define("VIX_HTML_VIEWER_URL", "http://10.184.38.147:9000/hydra/viewer");

//Point to VIX server
defined("VIX_STUDIES_URL")
    or define("VIX_STUDIES_URL", "http://10.184.38.149:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined("VIX_THUMBNAIL_URL")
    or define("VIX_THUMBNAIL_URL", "http://10.184.38.149:8090/RaptorWebApp/token/thumbnail");

