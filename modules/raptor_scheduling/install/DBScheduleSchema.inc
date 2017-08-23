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
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */

namespace raptor_sched;

/**
 * This class is for List database schema content
 *
 * @author FrankWin7VM
 */
class DBScheduleSchema
{
    /**
     * Add all the schema content for the list tables
     */
    public function addToSchema(&$schema)
    {
        
        $schema['raptor_schedule_track'] = array(
          'description' => 'Track scheduling of an exam',
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
            'scheduled_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'Event start date and time',
            ),
            'duration_am' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Duration amount in minutes',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Notes associated with this item',
            ),
            'notes_critical_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => '1 is critcal note, 0 is not critical note',
            ),
            'location_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Location identifier',
            ),
            'confirmed_by_patient_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'If not null, then confirmed by patient on this date',
            ),
            'canceled_reason_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'If not null, then canceled for this reason',
            ),
            'canceled_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'If not null, then canceled on this date',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that selected these values',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_schedule_track_replaced'] = array(
          'description' => 'Track replaced scheduling of an exam',
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
            'scheduled_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'Event start date and time',
            ),
            'duration_am' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Duration amount in minutes',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Notes associated with this item',
            ),
            'notes_critical_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => '1 is critcal note, 0 is not critical note',
            ),
            'location_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Location identifier',
            ),
            'confirmed_by_patient_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'If not null, then confirmed by patient on this date',
            ),
            'canceled_reason_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'If not null, then canceled for this reason',
            ),
            'canceled_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'If not null, then canceled on this date',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that selected these values',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );
    }    
}
    