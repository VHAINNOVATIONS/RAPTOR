<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *  
 */

namespace raptor;

/**
 * This class is for protocol database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBProtocolSchema
{
    /**
     * Add all the schema content for the protocol tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_protocol_lib'] = array(
          'description' => 'Protocol Library item key attributes',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol short name which must be unique',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this protocol if there have been replacements',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Modality abbreviation',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Service name',
            ),
            'lowerbound_weight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Lower bound weight for this protocol',
            ),
            'upperbound_weight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Upper bound weight for this protocol',
            ),
            'image_guided_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is an image guided protocol',
            ),
            'contrast_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a contrast protocol',
            ),
            'radioisotope_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a radioisotope protocol',
            ),
            'sedation_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a sedation protocol',
            ),
            'multievent_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol can be associated with multiple schedule events',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Filename in RAPTOR of the scanned protocol',
            ),
            'original_filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Original filename of uploaded protocol',
            ),
            'original_file_upload_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this file was last uploaded',
            ),
            'original_file_upload_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol is still active for use',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('protocol_shortname'),
        );

        $schema['raptor_protocol_lib_uploads'] = array(
          'description' => 'Protocol library uploads',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol short name which must be unique',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this protocol to match the parent record',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Filename in RAPTOR of the scanned protocol',
            ),
            'original_filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Original filename of uploaded protocol',
            ),
            'filetype' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'The uppercase file extension of the file',
            ),
            'filesize' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Size in bytes',
            ),
            'file_blob' => array(
              'type' => 'mediumblob',
              'not null' => FALSE,
              'description' => 'The uploaded file as a blob',
            ),
            'uploaded_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment from uploader',
            ),
            'uploaded_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('protocol_shortname','version'),
        );
        
        $schema['raptor_protocol_template'] = array(
          'description' => 'Template values',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Must match the protocol shortname in protocol_lib table',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol is still active for use',
            ),
            'hydration_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'hydration_oral_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'hydration oral default value',
            ),
            'hydration_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'hydration iv default value',
            ),
            'sedation_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'sedation_oral_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'sedation oral default value',
            ),
            'sedation_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'sedation iv default value',
            ),
            'contrast_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'contrast_enteric_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'contrast enteric default value',
            ),
            'contrast_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'contrast iv default value',
            ),
            'radioisotope_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'radioisotope_enteric_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'radioisotope enteric default value',
            ),
            'radioisotope_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'radioisotope iv default value',
            ),
            'consent_req_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'consent required values are yes, no, or unknown.',
            ),
            'protocolnotes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'protocol notes text',
            ),
            'examnotes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'exam notes text',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('protocol_shortname'),
        );
        
        $schema['raptor_protocol_keywords'] = array(
          'description' => 'Anatomy specializations of a protocol',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Must match the protocol shortname in protocol_lib table',
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
          'primary key' => array('protocol_shortname','weightgroup','keyword'),
        );
        
        
        //The replacement tables
        $schema['raptor_protocol_lib_replaced'] = array(
          'description' => 'Protocol Library Replaced Records',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol short name which must be unique',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Protocol name',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this protocol if there have been replacements',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Modality abbreviation',
            ),
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Service name',
            ),
            'lowerbound_weight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Lower bound weight for this protocol',
            ),
            'upperbound_weight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Upper bound weight for this protocol',
            ),
            'image_guided_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is an image guided protocol',
            ),
            'contrast_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a contrast protocol',
            ),
            'radioisotope_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a radioisotope protocol',
            ),
            'sedation_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this is a sedation protocol',
            ),
            'multievent_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol can be associated with multiple schedule events',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Filename in RAPTOR of the scanned protocol',
            ),
            'original_filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Original filename of uploaded protocol',
            ),
            'original_file_upload_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this file was last uploaded',
            ),
            'original_file_upload_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol is still active for use',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
        );

        $schema['raptor_protocol_template_replaced'] = array(
          'description' => 'Template values of replaced records',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Must match the protocol shortname in protocol_lib table',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if this protocol is still active for use',
            ),
            'hydration_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'hydration_oral_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'hydration oral default value',
            ),
            'hydration_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'hydration iv default value',
            ),
            'sedation_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'sedation_oral_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'sedation oral default value',
            ),
            'sedation_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'sedation iv default value',
            ),
            'contrast_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'contrast_enteric_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'contrast enteric default value',
            ),
            'contrast_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'contrast iv default value',
            ),
            'radioisotope_none_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'True if default value is NONE',
            ),
            'radioisotope_enteric_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'radioisotope enteric default value',
            ),
            'radioisotope_iv_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'radioisotope iv default value',
            ),
            'consent_req_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'consent required values are yes, no, or unknown.',
            ),
            'protocolnotes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'protocol notes text',
            ),
            'examnotes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'exam notes text',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
        );
        
        $schema['raptor_protocol_keywords_replaced'] = array(
          'description' => 'Anatomy specializations of a protocol replaced records',
          'fields' => array(
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Must match the protocol shortname in protocol_lib table',
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
    