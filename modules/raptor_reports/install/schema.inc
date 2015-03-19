<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */

require_once('DBScheduleSchema.inc');

/**
 * Create the tables
 */
function raptor_reports_update_7101() 
{
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_reports_schema()
{
    $schema = array();

    $oSH = new \raptor_reports\DBScheduleSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    