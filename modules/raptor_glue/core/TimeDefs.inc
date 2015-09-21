<?php

defined("TIME_DEFS_VERSION_INFO")
    or define("TIME_DEFS_VERSION_INFO", 'Standard 20150616.1');

/*
 * These are TIME dependent settings
 * 
 * The last term in the name tells the units (MS=miliseconds).
 * 
 * 15 minutes is 900 seconds
 * 30 minutes is 1800 seconds
 * 60 minutes is 3600 seconds
 * 90 minutes is 5400 seconds
 *  4 hours is 14400 seconds
 *  8 hours is 28800 seconds
 * 16 hours is 57600 seconds
 */

//This much inactive time triggers user logout (user gets prompted to stay)
// Production recommendation for USER_TIMEOUT_SECONDS=900
// Production recommendation for USER_TIMEOUT_GRACE_SECONDS=180
// Production recommendation for USER_ALIVE_INTERVAL_SECONDS=60
defined('USER_TIMEOUT_SECONDS') 
    or define('USER_TIMEOUT_SECONDS', 900);
defined('USER_TIMEOUT_GRACE_SECONDS')
    or define('USER_TIMEOUT_GRACE_SECONDS', 180);   //Prompt shows for this long
defined('USER_ALIVE_INTERVAL_SECONDS')
    or define('USER_ALIVE_INTERVAL_SECONDS', 60);

//This much inactive time triggers loss of an edit lock (keep this number pretty big)
//Make sure USER_EDITLOCK_TIMEOUT_SECONDS is at least 3 times bigger than USER_EDITLOCK_ALIVE_INTERVAL_SECONDS
//The USER_EDITLOCK_ALIVE_INTERVAL_SECONDS triggers a DB update, so do not make this too frequent
// Production recommendation for USER_EDITLOCK_TIMEOUT_SECONDS=900
// Production recommendation for USER_EDITLOCK_ALIVE_INTERVAL_SECONDS=60
defined('USER_EDITLOCK_TIMEOUT_SECONDS')
    or define('USER_EDITLOCK_TIMEOUT_SECONDS', 900);
defined('USER_EDITLOCK_ALIVE_INTERVAL_SECONDS')
    or define('USER_EDITLOCK_ALIVE_INTERVAL_SECONDS', 60);

//This does not log the user out, only refreshes the session key (warning: invalidates form data!)
// Production recommendation for SESSION_KEY_TIMEOUT_SECONDS=57600
defined('SESSION_KEY_TIMEOUT_SECONDS')
    or define('SESSION_KEY_TIMEOUT_SECONDS', 57600);

//Additional number of seconds system waits to allow elegant process to kickout first.
// Production recommendation for KICKOUT_DIRTYPADDING=90
defined('KICKOUT_DIRTYPADDING')
    or define('KICKOUT_DIRTYPADDING', 90); 

//Session change conflict check delay
// Production recommendation for CONFLICT_CHECK_DELAY_SECONDS=90
defined('CONFLICT_CHECK_DELAY_SECONDS')
    or define('CONFLICT_CHECK_DELAY_SECONDS', 90);

// After this many milliseconds, volatile data is considered stale in a cache
//Production recommendation for DATA_STALE_VOLATILE_MS=10
defined('DATA_STALE_VOLATILE_MS')
    or define('DATA_STALE_VOLATILE_MS', 10);

// After this many milliseconds, normal data is considered stale in a cache
// Production recommendation for DATA_STALE_NORMAL_MS=10000
defined('DATA_STALE_NORMAL_MS')
    or define('DATA_STALE_NORMAL_MS', 10000);



