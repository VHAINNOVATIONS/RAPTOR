<?php

defined("TIME_DEFS_VERSION_INFO")
    or define("TIME_DEFS_VERSION_INFO", 'DEBUG 20150504.1');

/*
 * These are TIME dependent settings
 */


//This much inactive time triggers user logout (user gets prompted to stay)
defined("USER_TIMEOUT_SECONDS")
    or define("USER_TIMEOUT_SECONDS", 60);//900);
defined("USER_TIMEOUT_GRACE_SECONDS")
    or define("USER_TIMEOUT_GRACE_SECONDS", 15);//180);   //Prompt shows for this long
defined("USER_ALIVE_INTERVAL_SECONDS")
    or define("USER_ALIVE_INTERVAL_SECONDS", 10);//60);

//This much inactive time triggers loss of an edit lock (keep this number pretty big)
defined("USER_EDITLOCK_TIMEOUT_SECONDS")
    or define("USER_EDITLOCK_TIMEOUT_SECONDS", 100);//5000);
defined("USER_EDITLOCK_ALIVE_INTERVAL_SECONDS")
    or define("USER_EDITLOCK_ALIVE_INTERVAL_SECONDS", 10);//60);

//This does not log the user out, only refreshes the session key (warning: invalidates form data!)
defined("SESSION_KEY_TIMEOUT_SECONDS")
    or define("SESSION_KEY_TIMEOUT_SECONDS", 57600);    //57600=16 hours

//Additional number of seconds system waits to allow elegant process to kickout first.
defined("KICKOUT_DIRTYPADDING")
    or define("KICKOUT_DIRTYPADDING", 90); 

//Session change conflict check delay
defined("CONFLICT_CHECK_DELAY_SECONDS")
    or define("CONFLICT_CHECK_DELAY_SECONDS", 90);

// After this many milliseconds, volatile data is considered stale in a cache
defined("DATA_STALE_VOLATILE_MS")
    or define("DATA_STALE_VOLATILE_MS", 10);

// After this many milliseconds, normal data is considered stale in a cache
defined("DATA_STALE_NORMAL_MS")
    or define("DATA_STALE_NORMAL_MS", 10000);



