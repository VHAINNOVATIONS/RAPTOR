<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */

require_once "DBSchema.inc";

/**
 * Create the tables
 */
function raptor_protocollib_update_7101() 
{
    $t = get_t();
    $schema = raptor_protocollib_schema();
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_protocollib_schema()
{
    $schema = array();

    $oSH = new \raptor_protocollib\DBSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    