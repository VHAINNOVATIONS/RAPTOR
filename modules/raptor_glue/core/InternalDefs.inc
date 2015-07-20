<?php

defined("INTERNAL_DEFS_VERSION_INFO")
    or define("INTERNAL_DEFS_VERSION_INFO", '20150715.1');

/*
 * These are application internal settings that should not be
 * edited by anyone other than an application developer.
 */

//User Activity Tracking Codes
defined('UATC_LOGIN')
    or define('UATC_LOGIN', 1);
defined('UATC_LOGOUT')
    or define('UATC_LOGOUT', 2);
defined('UATC_GENERAL')
    or define('UATC_GENERAL', 3);

//Do NOT make this bigger than the MySql max_allowed_packet setting
defined('MAX_CACHE_ITEM_SIZE')
    or define('MAX_CACHE_ITEM_SIZE', 1048576);
