<?php

defined("GENERAL_DEFS_VERSION_INFO")
    or define("GENERAL_DEFS_VERSION_INFO", '20150314.1');

/*
 * These are General Purpose settings
 */

//Require acknowledgement of default values?
defined("REQUIRE_ACKNOWLEDGE_DEFAULTS")
    or define("REQUIRE_ACKNOWLEDGE_DEFAULTS", FALSE);

defined("DISABLE_TICKET_AGE1_SCORING")
    or define("DISABLE_TICKET_AGE1_SCORING", TRUE);

defined("DISABLE_TICKET_AGE2_SCORING")
    or define("DISABLE_TICKET_AGE2_SCORING", TRUE);

defined("DEFAULT_GET_VISIT_DAYS")
    or define("DEFAULT_GET_VISIT_DAYS", 9999); //For PROD set this to 30 days!!!!!!

