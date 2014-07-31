<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * 20140521
 */

require_once("DBListSchema.inc");
require_once("DBGroupSchema.inc");
require_once("DBMiscSchema.inc");
require_once("DBProtocolSchema.inc");
require_once("DBScheduleSchema.inc");
require_once("DBTicketSchema.inc");
require_once("DBUserSchema.inc");
//require_once("DBContraIndicationSchema.inc");

/**
 * Create the tables
 */
function raptor_datalayer_update_7101() 
{
  //if (db_table_exists('raptor_user_profile')) 
  //{
  //  return st('Unable to create table because raptor_user_profile exists!');
  //}
    $schema = raptor_datalayer_schema();
    db_create_table('raptor_user_profile', $schema['raptor_user_profile']);
    return st('Created table raptor_user_profile');
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_datalayer_schema()
{
    $schema = array();

    $oSH = new \raptor\DBUserSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBGroupSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBListSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBTicketSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBScheduleSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBProtocolSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBMiscSchema();
    $oSH->addToSchema($schema);
    //$oSH = new \raptor\DBContraIndicationSchema();
    //$oSH->addToSchema($schema);
  
    return $schema;
}
    