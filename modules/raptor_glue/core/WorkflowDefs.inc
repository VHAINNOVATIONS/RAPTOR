<?php

defined("WORKFLOW_DEFS_VERSION_INFO")
    or define("WORKFLOW_DEFS_VERSION_INFO", 'Custom 20150422.1');

/*
 * These are Workflow settings
 */

//Affects where the user can complete the ticket workflow
defined("ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_AP")
    or define("ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_AP", TRUE);     //Full workflow would be FALSE

defined("ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_PA")
    or define("ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_PA", TRUE);     //Full workflow would be FALSE

//Prevent progress into a ticket state
defined("BLOCK_TICKET_STATE_PA")
    or define("BLOCK_TICKET_STATE_PA", FALSE);     //Full workflow would be FALSE

defined("BLOCK_TICKET_STATE_EC")
    or define("BLOCK_TICKET_STATE_EC", FALSE);     //Full workflow would be FALSE

