<?php
/*
 * Sample Repository configuration file
 */

/*
 * Security provider:  Specify the class name that is used to provide security
 */
$conf['access callback'] = 'user_access';
/*
 * Current user:
 * Specificy the php function that will be used to determine the current user.
 * Forena provides the following helper functions to for the common drupal defaults
 *   forena_current_user_uid
 *   forena_current_user_name
 */
$conf['user callback'] = 'forena_current_user_id';
/*
 * Data provider:
 * Specify the class name that will be used to interpret data block files.
 * Note that data blocks in a repository
 * The following commented lines are used as an example.
 */
//$conf['data provider'] = 'FrxPDO';
//$conf['data provider'] = 'FrxPDO';


$conf['data provider'] = 'FrxDrupal';

/*
 * URI:
 * The format of the uri depends on the type of data engine that's being used.
 * In database engines it might be the connection string to the db.  In the file
 * engine it would be the path to the directory containting the files
 */
// Not applicable to drupal installs
//$conf['uri'] = 'mysql:host=dbhost;dbname=databasename';
//$conf['user'] = 'webdbuser';
//$conf['password'] = 'somesecurepassword';


