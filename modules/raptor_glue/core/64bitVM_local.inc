<?php
    
//LOCAL 64bit VM
//Updated 20150125 after MDWS updated

defined("RAPTOR_CONFIG_ID")
    or define("RAPTOR_CONFIG_ID", "Config for DEV 64bit localhost");

defined("RAPTOR_ROOT_URL")
    or define("RAPTOR_ROOT_URL", "http://localhost/RSite500/RAPTOR/");

defined("EMRSERVICE_URL")
    or define("EMRSERVICE_URL", "http://localhost/mdws2.5/emrsvc.asmx");
defined("QUERYSERVICE_URL")
    or define("QUERYSERVICE_URL", "http://localhost/mdws2.5/querysvc.asmx");

defined("EMRSERVICE_LOCAL_FILE")
    or define("EMRSERVICE_LOCAL_FILE", "C:\\inetpub\\wwwroot\\RSite500\\RAPTOR\\sites\\all\\modules\\raptor_datalayer\\core\\emrsvc.wsdl");
defined("QUERYSERVICE_LOCAL_FILE")
    or define("QUERYSERVICE_LOCAL_FILE", "C:\\inetpub\\wwwroot\\RSite500\\RAPTOR\\sites\\all\\modules\\raptor_datalayer\\core\\querysvc.wsdl");
defined("VIX_STUDIES_URL")
    or define("VIX_STUDIES_URL", "http://localhost:8090/RaptorWebApp/secure/restservices/raptor/studies/");
defined("VIX_THUMBNAIL_URL")
    or define("VIX_THUMBNAIL_URL", "http://localhost:8090/RaptorWebApp/token/thumbnail");
defined("VIX_HTML_VIEWER_URL")
    or define("VIX_HTML_VIEWER_URL", "http://localhost:9000/hydra/viewer");
