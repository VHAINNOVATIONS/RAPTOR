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

require_once ("DBContraIndicationSchema.inc");

/**
 * Create the tables
 */
function raptor_contraindications_update_7101() 
{
  //if (db_table_exists('raptor_contraindication_rule')) 
  //{
  //  return st('Unable to create table because raptor_contraindication_rule exists!');
  //}
    $schema = raptor_contraindications_schema();
    db_create_table('raptor_contraindication_rule', $schema['raptor_contraindication_rule']);
    $t = get_t();
    drupal_set_message($t('Created table raptor_contraindication_rule'));
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_contraindications_schema()
{
    $schema = array();

    $oSH = new \raptor\DBContraIndicationSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    