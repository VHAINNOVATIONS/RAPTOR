<?php

defined("MDWS_DEFS_VERSION_INFO")
    or define("MDWS_DEFS_VERSION_INFO", '20150519.1');

/*
 * These are MDWS specific settings
 */

defined('MDWS_CONTEXT')
    or define('MDWS_CONTEXT', 'OR CPRS GUI CHART');

defined("MDWS_EMR_FACADE")
    or define("MDWS_EMR_FACADE", "EmrSvc");

defined("MDWS_QUERY_FACADE")
    or define("MDWS_QUERY_FACADE", "QuerySvc");

defined("MDWS_CONNECT_MAX_ATTEMPTS")
    or define("MDWS_CONNECT_MAX_ATTEMPTS", 10);

defined("MDWS_VISTA_UNAVAILABLE_RETRY_MAX_ATTEMPTS")
    or define("MDWS_VISTA_UNAVAILABLE_RETRY_MAX_ATTEMPTS", 3);

defined("MDWS_QUERY_RETRY_MAX_ATTEMPTS")
    or define("MDWS_QUERY_RETRY_MAX_ATTEMPTS", 3);

// interval to wait between MDWS queries when re-trying (in milliseconds)
defined("MDWS_QUERY_RETRY_WAIT_INTERVAL_MS")
    or define("MDWS_QUERY_RETRY_WAIT_INTERVAL_MS", 100);

defined("MDWS_CXN_TIMEOUT_ERROR_MSG_1")
    or define("MDWS_CXN_TIMEOUT_ERROR_MSG_1", "Connections not ready for operation");

defined("MDWS_CXN_TIMEOUT_ERROR_MSG_2")
    or define("MDWS_CXN_TIMEOUT_ERROR_MSG_2", "established connection was aborted by the software in your host machine");

defined("MDWS_CXN_TIMEOUT_ERROR_MSG_3")
    or define("MDWS_CXN_TIMEOUT_ERROR_MSG_3", "There is no logged in connection");

defined("MDWS_CXN_TIMEOUT_ERROR_MSG_4")
    or define("MDWS_CXN_TIMEOUT_ERROR_MSG_4", 'Timeout waiting for response from VistA');


