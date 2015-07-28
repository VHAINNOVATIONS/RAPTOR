<?php

defined('RAPTOR_BUILD_ID')
    or define('RAPTOR_BUILD_ID', 'Test Candidate 20150728.3');

//If this is not empty, then username must be prefixed with this text
defined('USERNAME_PREFIX_SALT')
    or define('USERNAME_PREFIX_SALT', '');

/*
 * We require one of the following instance specific includes.
 */
//require_once 'RSite500_146.inc';
//require_once 'RSite500_147.inc';
//require_once 'RSite970_146.inc';
//require_once 'RSite970_147.inc';
//require_once 'RSite978_146.inc';
//require_once 'RSite978_147.inc';
//require_once 'RSite948_146.inc';
//require_once 'RSite948_147.inc';
//require_once 'RSite963_146.inc';
//require_once 'RSite963_147.inc';
//require_once '32bitVM_local_newMDWS.inc';
//require_once '64bitVM_184.inc';
//require_once '64bitVM_54-193.inc';
//require_once '64bitVM_54-252-102.inc';
//require_once '64bitVM_54-92-244.inc';
require_once '64bitVM_local.inc';

/*
 * We require all of the following.
 */
require_once 'InternalDefs.inc';
require_once 'TimeDefs.inc';
require_once 'VistaDefs.inc';
require_once 'ErrorCodeDefs.inc';
require_once 'GeneralDefs.inc';
require_once 'WorkflowDefs.inc';
require_once 'UOMDefs.inc';

