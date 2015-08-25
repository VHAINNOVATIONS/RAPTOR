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
    