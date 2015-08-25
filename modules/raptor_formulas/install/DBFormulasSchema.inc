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
 * This class is for List database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBFormulasSchema
{
    /**
     * Add all the schema content for the list tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_list_kw_with_contrast'] = array(
          'description' => 'Phrases that indicate an order includes contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
        
        $schema['raptor_list_kw_without_contrast'] = array(
          'description' => 'Phrases that indicate an order is without contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
        
        $schema['raptor_list_kw_withandwithout_contrast'] = array(
          'description' => 'Phrases that indicate an order is with and without contrast',
          'fields' => array(
            'phrase_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'The exact text to look for',
            ),
          ),
        );
    }    
}
    