<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2014 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.gnu.org/copyleft/gpl.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */



namespace simplerulesengine;

/**
 * This class is for database schema content
 *
 * @author Frank Font
 */
class DBSimpleRulesEngineSchema
{
    /**
     * Add all the schema content for the contra indication tables
     */
    public function addToSchema(&$schema)
    {
        $schema['simplerulesengine_rule'] = array(
          'description' => 'A simple rules engine rule',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping rules in a logical way',
            ),
            'rule_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Must be unique',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Increases each time change is saved',
            ),
            'summary_msg_tx' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Static summary text to show the user when rule is triggered',
            ),
            'msg_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Text to show the user when rule is triggered',
            ),
            'explanation' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'Explanation of the rule purpose',
            ),
            'req_ack_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then an acknowledgement is required',
            ),
            'trigger_crit' => array(
              'type' => 'varchar',
              'length' => 4000,
              'not null' => TRUE,
              'description' => 'The criteria that triggers the rule',
            ),
            'readonly_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this rule record cannot be edited',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this rule is not active',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('rule_nm'),
        );
        
        $schema['simplerulesengine_measure'] = array(
          'description' => 'A measure used by the simple rules engine',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping in a logical way',
            ),
            'measure_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The measure name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this measure',
            ),
            'return_type' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The returned data type',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this measure',
            ),
            'criteria_tx' => array(
              'type' => 'varchar',
              'length' => 4000,
              'not null' => FALSE,
              'description' => 'The measure formula or INPUT if no formula',
            ),
            'readonly_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this measure record should not be edited',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this measure is not available for use in new expressions',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('measure_nm'),
        );
        
        $schema['simplerulesengine_measure_question_choices'] = array(
          'description' => 'A collection of choices for a measure question',
          'fields' => array(
            'measure_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The measure name',
            ),
            'position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Relative position of this choice in the presentation',
            ),
            'show_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'Static text to show the user as the choice',
            ),
            'choice_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'The choice value',
            ),
          ),
          'primary key' => array('measure_nm','position'),
        );
        
        $schema['simplerulesengine_measure_question_validation'] = array(
          'description' => 'Validation for a question answer',
          'fields' => array(
            'measure_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The measure name',
            ),
            'regex' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'If not null, then answer must match this regex',
            ),
            'maxfloat' => array(
              'type' => 'float',
              'unsigned' => FALSE,
              'not null' => FALSE,
              'description' => 'If not null, then answer must be a number less than or equal to this',
            ),
            'minfloat' => array(
              'type' => 'float',
              'unsigned' => FALSE,
              'not null' => FALSE,
              'description' => 'If not null, then answer must be a number more than or equal to this',
            ),
            'maxint' => array(
              'type' => 'int',
              'unsigned' => FALSE,
              'not null' => FALSE,
              'description' => 'If not null, then answer must be a number less than or equal to this',
            ),
            'minint' => array(
              'type' => 'int',
              'unsigned' => FALSE,
              'not null' => FALSE,
              'description' => 'If not null, then answer must be a number more than or equal to this',
            ),
          ),
          'primary key' => array('measure_nm'),
        );
        
        $schema['simplerulesengine_measure_question'] = array(
          'description' => 'A measure question for a user interface to present',
          'fields' => array(
            'measure_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The measure name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this measure',
            ),
            'question_significance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 50,
              'description' => 'Higher values mean the question is more signficant for presentation to user than lower value questions',
            ),
            'question_type_cd' => array(
              'type' => 'char',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'TX=Simple text, NM=Simple number, TF=True/False, YN=Yes/No, MC=Multiple Choice, SC=Single Choice',
            ),
            'answer_limit' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Length for text answers, count for multiple choice answers, ignored for others',
            ),
            'question_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Static text question for user to answer',
            ),
            'explanation_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text explainin the question in more detail',
            ),
          ),
          'primary key' => array('measure_nm'),
        );
    }    
}
    