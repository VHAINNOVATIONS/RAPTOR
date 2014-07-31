<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * This class is for miscellaneous database schema content
 *
 * @author FrankWin7VM
 */
class DBMiscSchema
{
    /**
     * Add all the schema content for the miscellaneous tables
     */
    public function addToSchema(&$schema)
    {

        $schema['raptor_boilerplate_helper'] = array(
          'description' => 'Text snippets',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Short name for display on button',
            ),
            'snippet_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'The text to show',
            ),
            'protocol_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to PROTOCOL context?',
            ),
            'exam_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to EXAM context?',
            ),
            'message_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to MESSAGE context?',
            ),
          ),
        );

        $schema['raptor_cprs_codes'] = array(
          'description' => 'Relevant information about CPRS codes',
          'fields' => array(
            'cprs_cd' => array(
              'type' => 'varchar',
              'length' => 6,
              'not null' => TRUE,
              'description' => 'The CPRS code',
            ),
            'exclude_from_worklist_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then orders of this type are ignored by RAPTOR',
            ),
            'contrast_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1, then contrast, else no contrast.',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => FALSE,
              'description' => 'If not excluded, then this field tells us the modality associated with this order',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => FALSE,
              'description' => 'If not excluded, then this field tells us the service associated with this order',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
        );

        $schema['raptor_radlex_codes'] = array(
          'description' => 'Relevant information about RADLEX codes',
          'fields' => array(
            'radlex_cd' => array(
              'type' => 'varchar',
              'length' => 12,
              'not null' => TRUE,
              'description' => 'The RADLEX code',
            ),
            'exclude_from_worklist_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then orders of this type are ignored by RAPTOR',
            ),
            'contrast_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1, then contrast, else no contrast.',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => FALSE,
              'description' => 'If not excluded, then this field tells us the modality associated with this order',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => FALSE,
              'description' => 'If not excluded, then this field tells us the service associated with this order',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
        );

        $schema['raptor_protocol_code_maps'] = array(
          'description' => 'Map codes to the protocols in the library',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol short name key value',
            ),
            'cprs_cd' => array(
              'type' => 'varchar',
              'length' => 6,
              'not null' => FALSE,
              'description' => 'The CPRS code',
            ),
            'radlex_cd' => array(
              'type' => 'varchar',
              'length' => 12,
              'not null' => FALSE,
              'description' => 'The RADLEX code',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
        );
        
        $schema['raptor_atrisk_meds'] = array(
          'description' => 'Keywords to match with medications considered to be at-risk for procedures',
          'fields' => array(
            'keyword' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Keyword for text matching',
            ),
          ),
          'primary key' => array('keyword'),
        );
        
        
    }    
}
    