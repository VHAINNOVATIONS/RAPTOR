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
    