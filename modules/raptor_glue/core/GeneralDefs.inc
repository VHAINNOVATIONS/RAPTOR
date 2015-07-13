<?php

defined('GENERAL_DEFS_VERSION_INFO')
    or define('GENERAL_DEFS_VERSION_INFO', 'UAT 20150713.1');

/*
 * These are General Purpose settings
 */

//Require acknowledgement of default values?
defined('REQUIRE_ACKNOWLEDGE_DEFAULTS')
    or define('REQUIRE_ACKNOWLEDGE_DEFAULTS', FALSE);

defined('DISABLE_TICKET_AGE1_SCORING')
    or define('DISABLE_TICKET_AGE1_SCORING', TRUE);

defined('DISABLE_TICKET_AGE2_SCORING')
    or define('DISABLE_TICKET_AGE2_SCORING', TRUE);

defined('DEFAULT_GET_VISIT_DAYS')
    or define('DEFAULT_GET_VISIT_DAYS', 9999); //For PROD set this to 30 days!!!!!!

defined('PROTOCOL_SHORTLIST_MIN_SIZE')
    or define('PROTOCOL_SHORTLIST_MIN_SIZE', 3);

//Modality inference configuration items; change with care.
defined('MATCH_MODALITY_ON_PREFIX')
    or define('MATCH_MODALITY_ON_PREFIX', TRUE);
defined('MATCH_MODALITY_STANDARD_TERMS')
    or define('MATCH_MODALITY_STANDARD_TERMS', TRUE);
//Comma delimited lists of terms else NULL
defined('CUSTOM_TERMS4MATCH_MR')
    or define('CUSTOM_TERMS4MATCH_MR', NULL);
defined('CUSTOM_TERMS4MATCH_CT')
    or define('CUSTOM_TERMS4MATCH_CT', NULL);
defined('CUSTOM_TERMS4MATCH_NM')
    or define('CUSTOM_TERMS4MATCH_NM', NULL);
defined('CUSTOM_TERMS4MATCH_FL')
    or define('CUSTOM_TERMS4MATCH_FL', NULL);
defined('CUSTOM_TERMS4MATCH_US')
    or define('CUSTOM_TERMS4MATCH_US', NULL);

//How much of the username to mask from other standard RAPTOR users (99 to hide)
defined('DEFAULT_USERNAME_TAILMASK')
    or define('DEFAULT_USERNAME_TAILMASK', 5);

//Declare minimum passwords length for NON-VISTA user accounts
defined('MIN_ADMIN_PASSWORD_LEN')
    or define('MIN_ADMIN_PASSWORD_LEN', 5);

//Default size of snippet text
defined('RAPTOR_DEFAULT_SNIPPET_LEN')
    or define('RAPTOR_DEFAULT_SNIPPET_LEN', 40);