<?php
/*
 * Sample Repository configuration file
 */

/*
 * Security provider:  Specify the class name that is used to provide security
 */
$conf['access callback'] = 'user_access';

/*
 * Data provider:
 * Specify the class name that will be used to interpret data block files.
 * Note that data blocks in a repository
 *
 */
$conf['data provider'] = 'FrxPDO';

/*
 * URI:
 * The format of the uri depends on the type of data engine that's being used.
 * In database engines it might be the connection string to the db.  In the file
 * engine it would be the path to the directory containting the files
 */
$local_path = realpath(dirname(drupal_get_path('module','forena').'/forena.info'));
$conf['uri'] = 'sqlite:'. $local_path . '/repos/sample/sample.db';
$conf['debug'] = FALSE;
/**
 * Uncomment the following line to specify a particular path in which to save reports.
 * The default value would be controlled by Forena configuration page. If you'd like users
 * to be able to define their own reports in this repository then you need to make sure that this
 * is writable by the web user (e.g. apache).
 */
// $conf['report_path'] = 'sites/files/reports'
