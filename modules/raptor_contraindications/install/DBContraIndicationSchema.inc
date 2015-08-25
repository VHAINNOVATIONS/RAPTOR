<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
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
 * This class is for ContraIndication database schema content
 *
 * @author Frank Font
 */
class DBContraIndicationSchema
{
    /**
     * Add all the schema content for the contra indication tables
     */
    public function addToSchema(&$schema)
    {
        $loaded = module_load_include('inc','simplerulesengine_core','install/DBSimpleRulesEngineSchema');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the \simplerulesengine_core\DBSimpleRulesEngineSchema class');
        }
        $aRefSchema = array();
        $oReferenceSchema = new \simplerulesengine\DBSimpleRulesEngineSchema();
        $oReferenceSchema->addToSchema($aRefSchema);
        $schema['raptor_contraindication_rule'] = $aRefSchema['simplerulesengine_rule'];
        $schema['raptor_contraindication_rule_replaced'] = $aRefSchema['simplerulesengine_rule'];
        $schema['raptor_contraindication_rule_replaced']['replaced_dt'] = array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was replaced',
            );
        $schema['raptor_contraindication_measure'] = $aRefSchema['simplerulesengine_measure'];
        $schema['raptor_contraindication_measure_replaced'] = $aRefSchema['simplerulesengine_measure'];
        $schema['raptor_contraindication_measure_replaced']['replaced_dt'] = array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was replaced',
            );
    }    
}
    