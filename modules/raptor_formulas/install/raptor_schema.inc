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

require_once 'DBFormulasSchema.inc';

/**
 * Create the tables
 */
function raptor_formulas_update_7101() 
{
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_formulas_schema()
{
    $schema = array();

    $oSH = new \raptor\DBFormulasSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    