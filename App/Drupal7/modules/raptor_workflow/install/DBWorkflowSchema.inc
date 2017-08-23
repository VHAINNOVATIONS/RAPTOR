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

namespace raptor;

/**
 * This class is for Workflow database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBWorkflowSchema
{
    /**
     * Add all the schema content for the Ticket tables
     */
    public function addToSchema(&$schema)
    {
        
        $schema['raptor_workflow_state'] = array(
          'description' => 'Workflow states dictionary',
          'fields' => array(
            'abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Workflow state abbreviation',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Workflow state name',
            ),
            'description' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Workflow state description',
            ),
          ),
          'primary key' => array('abbr'),
        );
    }    
}
    