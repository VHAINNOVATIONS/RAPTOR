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

require_once ("DBListSchema.inc");
require_once ("DBGroupSchema.inc");
require_once ("DBMiscSchema.inc");
require_once ("DBProtocolSchema.inc");
require_once ("DBTicketSchema.inc");
require_once ("DBPatientSchema.inc");
require_once ("DBUserSchema.inc");

/**
 * Create the tables
 */
function raptor_datalayer_update_7101() 
{
    $t = get_t();
    $schema = raptor_datalayer_schema();
    db_create_table('raptor_user_profile', $schema['raptor_user_profile']);
    drupal_set_message($t('Created table raptor_user_profile'));
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
    $oSH = new \raptor\DBPatientSchema();
    $oSH->addToSchema($schema);

    $oSH = new \raptor\DBProtocolSchema();
    $oSH->addToSchema($schema);
    $oSH = new \raptor\DBMiscSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    