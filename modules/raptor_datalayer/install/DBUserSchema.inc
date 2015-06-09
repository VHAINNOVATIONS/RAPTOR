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
 *  
 */

namespace raptor;

/**
 * This class is for User database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBUserSchema
{
    /**
     * Add all the schema content for the user tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_user_profile'] = array(
          'description' => 'Main profile record for the user',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Must match the uid in Drupal',
            ),
            'username' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Must match the username in VA system',
            ),
            'role_nm' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => 'RADIOLOGIST',
              'description' => 'The role of this user in RAPTOR',
            ),
            'worklist_cols' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Encoded collection of worklist columns to HIDE (blank means sow all)',
            ),
            'usernametitle' => array(
              'type' => 'varchar',
              'length' => 16,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Title such as Dr, etc',
            ),
            'firstname' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => FALSE,
              'default' => '',
              'description' => 'First name',
            ),
            'lastname' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Last name',
            ),
            'suffix' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Suffix such as PhD, etc',
            ),
            'prefemail' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Preferred email address',
            ),
            'prefphone' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Preferred phone number',
            ),
            'accountactive_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then account is NOT active',
            ),
            'CEUA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit other accounts',
            ),
            'LACE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit resident account',
            ),
            'SWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can select worklist items for view',
            ),
            'PWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can protocol worklist items',
            ),
            'APWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can approve protocoled worklist items',
            ),
            'SUWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can cancel a worklist item',
            ),
            'CE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can complete an examination',
            ),
            'QA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can complete a QA examination',
            ),
            'SP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit pass box',
            ),
              
            'VREP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then user can view the department activity report',
            ),
              
            'VREP2' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can view the user activity report',
            ),
              
            'EBO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit protocol/exam note boilerplate options',
            ),
            'UNP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can upload new protocols',
            ),
            'REP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can retire existing protocols',
            ),

            'DRA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit default and required attribs of roles',
            ),
            'ELCO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit contrast options',
            ),
            'ELHO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit hydration options',
            ),
            'ELRO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit radioisotope options',
            ),
            'ELSO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit sedation options',
            ),
            'ELSVO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit service options',
            ),
            'EECC1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit CPRS code metadata',
            ),

              
            'ECIR1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit contraindication rules',
            ),
            'EERL1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can alter list of examination rooms',
            ),
              

            'EARM1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can alter keywords of at risk medication list',
            ),
              
              
            'CUT1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit umbrella terms and associate keywords with them',
            ),
              
            'QA2' => array(
              'type' => 'int',
              'not null' => FALSE,
              'default' => 0,
              'description' => 'If 1 then user can see all QA results',
            ),
              
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid'),
        );
        
        $schema['raptor_user_anatomy'] = array(
          'description' => 'Anatomy specializations of users',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Must match the uid in drupal',
            ),
            'weightgroup' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Group 1 has the most weight',
            ),
            'keyword' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Specialization keyword',
            ),
            'specialist_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user is a specialist in this area',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid','weightgroup','keyword','specialist_yn'),
        );

        $schema['raptor_user_modality'] = array(
          'description' => 'Modalities in which users specialize',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Must match the uid in drupal',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Modality abbreviation',
            ),
            'specialist_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user is a specialist in this modality',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid','modality_abbr','specialist_yn'),
        );

        $schema['raptor_user_service'] = array(
          'description' => 'Services which the user provides',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Must match the uid in drupal',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'description' => 'Service name',
            ),
            'specialist_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user is a specialist in this service',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid','service_nm','specialist_yn'),
        );

        $schema['raptor_user_group_membership'] = array(
          'description' => 'Groups to which a user belongs',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Must match the uid in drupal',
            ),
            'group_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Group in which user is a member',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid','group_nm'),
        );
        
        $schema['raptor_user_anatomy_override'] = array(
          'description' => 'Override anatomy specializations of users for ranking purposes',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Must match the uid in drupal',
            ),
            'weightgroup' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Group 1 has the most weight',
            ),
            'keyword' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Specialization keyword',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('uid','weightgroup','keyword'),
        );

        $schema['raptor_user_modality_override'] = array(
          'description' => 'Override modalities in which users specialize for ranking purposes',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Must match the uid in drupal',
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
          'primary key' => array('uid','modality_abbr'),
        );

        $schema['raptor_user_service_override'] = array(
          'description' => 'Override default services which the user provides for ranking purposes',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Must match the uid in drupal',
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
          'primary key' => array('uid','service_nm'),
        );
        
        $schema['raptor_user_activity_tracking'] = array(
          'description' => 'Track some basic user actions',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Must match the uid in drupal',
            ),
            'action_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Action code (1=login, 2=logout, 3=interaction)',
            ),
            'ipaddress' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => 'The user ip address',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 200,
              'not null' => FALSE,
              'description' => 'The user session id',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'indexes' => array('main' => array('updated_dt','uid')),
        );
        
        $schema['raptor_user_recent_activity_tracking'] = array(
          'description' => 'Track some basic recent user actions for accounts that have successfully logged in',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Must match the uid in drupal',
            ),
            'ipaddress' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => 'The user ip address',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 200,
              'not null' => FALSE,
              'description' => 'The user session id',
            ),
            'most_recent_login_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was last updated',
            ),
            'most_recent_logout_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was last updated',
            ),
            'most_recent_action_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was last updated',
            ),
            'most_recent_action_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Action code (1=login, 2=logout, 3=interaction)',
            ),
            'most_recent_error_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was last updated',
            ),
            'most_recent_error_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The most recent error code',
            ),
          ),
          'primary key' => array('uid','ipaddress','sessionid'),
        );
        
        
    }    
}
    