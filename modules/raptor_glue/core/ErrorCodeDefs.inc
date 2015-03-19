<?php

defined("ERRORCODE_DEFS_VERSION_INFO")
    or define("ERRORCODE_DEFS_VERSION_INFO", '20150314.2');

/*
 * These are error code settings
 */

defined("ERRORCODE_KICKOUT_ACCOUNTCONFLICT")
    or define("ERRORCODE_KICKOUT_ACCOUNTCONFLICT", 99102);

defined("ERRORCODE_KICKOUT_TIMEOUT")
    or define("ERRORCODE_KICKOUT_TIMEOUT", 99101);

defined('ERRORCODE_VISTATIMEOUT')
    or define('ERRORCODE_VISTATIMEOUT', 99501);

defined('ERRORCODE_AUTHENTICATION')
    or define('ERRORCODE_AUTHENTICATION', 99502);


function error_data_log($msg)
{
    if(__RAPTOR_DATA_DEBUG__)
    {
        error_log($msg);
    }
}

