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
function raptor_scheduling_update_7101() 
{
  //if (db_table_exists('raptor_user_profile')) 
  //{
  //  return st('Unable to create table because raptor_user_profile exists!');
  //}
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_scheduling_schema()
{
    $schema = array();

    $oSH = new \raptor_sched\DBScheduleSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    