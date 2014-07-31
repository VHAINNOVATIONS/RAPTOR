<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140729
 */

namespace raptor;

/**
 * This class is for ContraIndication database schema content
 *
 * @author FrankWin7VM
 */
class DBContraIndicationSchema
{
    /**
     * Add all the schema content for the contra indication tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_contraindication_rule'] = array(
          'description' => 'A contraindication rule',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping rules in a logic way',
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
            'explanation' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'Explanation of the rule purpose',
            ),
            'msg_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Text to show the user when rule is triggered',
            ),
            'req_ack_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then an acknowledgement is required',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this rule is not active',
            ),
            'trigger_crit' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'The criteria that triggers the rule',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('rule_nm'),
        );
        
        $schema['raptor_contraindication_rule_replaced'] = array(
          'description' => 'A replaced contraindication rule',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping rules in a logic way',
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
            'explanation' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'Explanation of the rule purpose',
            ),
            'msg_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Text to show the user when rule is triggered',
            ),
            'req_ack_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then an acknowledgement is required',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this rule is not active',
            ),
            'trigger_crit' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'The criteria that triggers the rule',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated prior to replacement',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
        );

        $schema['raptor_contraindication_flag'] = array(
          'description' => 'A flag used by contraindication engine',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping in a logical way',
            ),
            'flag_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The flag name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this flag',
            ),
            'criteria_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'The flag criteria',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'True if this flag is still active for new use',
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
          'primary key' => array('flag_nm'),
        );
        
        $schema['raptor_contraindication_flag_replaced'] = array(
          'description' => 'A replaced flag used by contraindication engine',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Simply for grouping in a logical way',
            ),
            'flag_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The flag name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The version number of this replaced flag',
            ),
            'criteria_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'The flag criteria',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'True if this flag is still active for new use',
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
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
        );
    }    
}
    