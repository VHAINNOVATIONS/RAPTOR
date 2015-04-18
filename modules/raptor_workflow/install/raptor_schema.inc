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

require_once 'DBWorkflowSchema.inc';

/**
 * Create the tables
 */
function raptor_workflow_update_7101() 
{
    $t = get_t();
    $schema = raptor_workflow_schema();
    db_create_table('raptor_workflow_state', $schema['raptor_workflow_state']);
    drupal_set_message($t('Created table raptor_workflow_state'));
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function raptor_workflow_schema()
{
    $schema = array();

    $oSH = new \raptor\DBWorkflowSchema();
    $oSH->addToSchema($schema);
  
    return $schema;
}
    