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

namespace raptor_reports;

/**
 * This class is for List database schema content
 *
 * @author SAN
 */
class DBScheduleSchema
{
    /**
     * Add all the schema content for the list tables
     */
    public function addToSchema(&$schema)
    {
        
        $schema['raptor_rep_example_table'] = array(
          'description' => 'Example table just to illustrate installer',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'some_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'Event start date and time',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Notes associated with this item',
            ),
          ),
        );
    }    
}
    