<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * This class is for group database schema content
 *
 * @author FrankWin7VM
 */
class DBGroupSchema
{
    /**
     * Add all the schema content for the group tables
     */
    public function addToSchema(&$schema)
    {

        $schema['raptor_group'] = array(
          'description' => 'Lookup table of groups to which users can belong',
          'fields' => array(
            'name_tx' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group name',
            ),
            'desc_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group name',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('name_tx'),
        );

        $schema['raptor_group_anatomy_keyword'] = array(
          'description' => 'Default anatomy keywords for a group',
          'fields' => array(
            'name_tx' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group name',
            ),
            'weightgroup' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Group 1 has the most weight, 2 less, 3 least',
            ),
            'keyword' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Anatomy keyword',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('name_tx','keyword'),
        );

        $schema['raptor_group_modality'] = array(
          'description' => 'Default modalities associated with a group',
          'fields' => array(
            'group_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group name',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Modality abbreviation',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('group_nm','modality_abbr'),
        );
        
        $schema['raptor_group_service'] = array(
          'description' => 'Default services associated with a group',
          'fields' => array(
            'group_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group name',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'description' => 'Service name',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('group_nm','service_nm'),
        );
        
    }    
}
    