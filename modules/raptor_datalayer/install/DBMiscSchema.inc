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
 * This class is for miscellaneous database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBMiscSchema
{
    /**
     * Add all the schema content for the miscellaneous tables
     */
    public function addToSchema(&$schema)
    {

        $schema['raptor_about'] = array(
          'description' => 'Information about this RAPTOR installation',
          'fields' => array(
            'category_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Information category',
            ),
            'info_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'The information text to show',
            ),
            'major_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 1,
              'description' => 'Major info number, sometimes useful for versioning',
            ),
            'minor_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 1,
              'description' => 'Minor info number, sometimes useful for versioning',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );
        
        $schema['raptor_cprs_codes'] = array(
          'description' => 'Relevant information about codes used in CPRS.  In general, CPRS uses CPT codes.',
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

        $schema['raptor_cpt_codes'] = array(
          'description' => 'Relevant information about CPT codes',
          'fields' => array(
            'cpt_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The CPT code',
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

        $schema['raptor_icd_codes'] = array(
          'description' => 'Relevant information about ICD codes',
          'fields' => array(
            'icd_cd' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'description' => 'The ICD code',
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

        $schema['raptor_snomed_codes'] = array(
          'description' => 'Relevant information about SNOMED codes',
          'fields' => array(
            'snomed_cd' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The SNOMED code',
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

        $schema['raptor_loinc_codes'] = array(
          'description' => 'Relevant information about LOINC codes',
          'fields' => array(
            'loinc_cd' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'description' => 'The LOINC code',
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
            'cpt_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'The CPT code',
            ),
            'snomed_cd' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => 'The SNOMED code',
            ),
            'icd_cd' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => FALSE,
              'description' => 'The ICD code',
            ),
            'loinc_cd' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => FALSE,
              'description' => 'The LOINC code',
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
        
        $schema['raptor_atrisk_allergy_contrast'] = array(
          'description' => 'Keywords to match with known patient allergies considered to indicate at-risk for contrast',
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

        $schema['raptor_atrisk_bloodthinner'] = array(
          'description' => 'Keywords to match with known blood thinning medications',
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
        
        $schema['raptor_atrisk_rare_contrast'] = array(
          'description' => 'Keywords to match with known controlled or rare contrast',
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
        
        $schema['raptor_atrisk_rare_radioisotope'] = array(
          'description' => 'Keywords to match with known controlled or rare radioisotope',
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
        
        $schema['raptor_checklist_question'] = array(
          'description' => 'Checklist questions',
          'fields' => array(
            'type_cd' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Safety Checklist = SC',
            ),
            'relative_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 500,
              'description' => 'Lower numbers are asked before higher numbered questions',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Modality abbreviation or empty if question applies to all modalities',
            ),
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'A specific protocol for this question or empty if question applies to all protocols',
            ),
            'question_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Uniquely identify the current version of a question',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this question must be incremented if the text is ever updated',
            ),
            'question_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Question to ask the user',
            ),
            'ask_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer Yes',
            ),
            'ask_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer No',
            ),
            'ask_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer Not Sure',
            ),
            'ask_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'User will be prompted to answer Not Applicable',
            ),
            'always_require_comment_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then comment is always required for this question.',
            ),
            'trigger_comment_on_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then comment is requird when user answers Yes',
            ),
            'trigger_comment_on_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers No',
            ),
            'trigger_comment_on_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers Not Sure',
            ),
            'trigger_comment_on_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers Not Applicable',
            ),
            'comment_prompt_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => 'Explanation',
              'description' => 'Prompt to show the user when asking for a comment',
            ),
            'failed_on_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Yes',
            ),
            'failed_on_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then question is considered failed when user answers No',
            ),
            'failed_on_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Not Sure',
            ),
            'failed_on_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Not Applicable',
            ),
            'the_okay_answer_tx' => array(
              'type' => 'varchar',
              'length' => 16,
              'not null' => FALSE,
              'default' => 'yes',
              'description' => 'Identify the answer that indicates the good scenario.  Possible values are yes,no,notsure,notapplicable.  Leave as NULL if no answer is considered special.',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('question_shortname'),
        );
        
        $schema['raptor_checklist_question_replaced'] = array(
          'description' => 'Replaced checklist questions',
          'fields' => array(
            'type_cd' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Safety Checklist = SC',
            ),
            'relative_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 500,
              'description' => 'Lower numbers are asked before higher numbered questions',
            ),
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Modality abbreviation or empty if question applies to all modalities',
            ),
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'A specific protocol for this question or empty if question applies to all protocols',
            ),
            'question_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Uniquely identify the current version of a question',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this question must be incremented if the text is ever updated',
            ),
            'question_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Question to ask the user',
            ),
            'ask_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer Yes',
            ),
            'ask_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer No',
            ),
            'ask_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'User will be prompted to answer Not Sure',
            ),
            'ask_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'User will be prompted to answer Not Applicable',
            ),
            'always_require_comment_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then comment is always required for this question.',
            ),
            'trigger_comment_on_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then comment is requird when user answers Yes',
            ),
            'trigger_comment_on_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers No',
            ),
            'trigger_comment_on_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers Not Sure',
            ),
            'trigger_comment_on_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then comment is requird when user answers Not Applicable',
            ),
            'comment_prompt_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'default' => 'Explanation',
              'description' => 'Prompt to show the user when asking for a comment',
            ),
            'failed_on_yes_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Yes',
            ),
            'failed_on_no_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then question is considered failed when user answers No',
            ),
            'failed_on_notsure_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Not Sure',
            ),
            'failed_on_notapplicable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then question is considered failed when user answers Not Applicable',
            ),
            'the_okay_answer_tx' => array(
              'type' => 'varchar',
              'length' => 16,
              'not null' => FALSE,
              'default' => 'yes',
              'description' => 'Identify the answer that indicates the good scenario.  Possible values are yes,no,notsure,notapplicable.  Leave as NULL if no answer is considered special.',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was originally created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
        );
        
        $schema['raptor_schedule_location'] = array(
          'description' => 'Locations available for scheduling',
          'fields' => array(
            'location_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Location identifier',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Description of the location',
            ),
          ),
          'primary key' => array('location_tx'),
        );

        $schema['raptor_boilerplate_protocol'] = array(
          'description' => 'Boilerplate text options for protocol step',
          'fields' => array(
            'category_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Category of the text',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Short title for the text',
            ),
            'content_tx' => array(
              'type' => 'varchar',
              'length' => 250,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Description of the location',
            ),
          ),
          'primary key' => array('category_tx','title_tx'),
        );

        $schema['raptor_boilerplate_exam'] = array(
          'description' => 'Boilerplate text options for exam step',
          'fields' => array(
            'category_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Category of the text',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Short title for the text',
            ),
            'content_tx' => array(
              'type' => 'varchar',
              'length' => 250,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Description of the location',
            ),
          ),
          'primary key' => array('category_tx','title_tx'),
        );
        
        $schema['raptor_schedule_duration'] = array(
          'description' => 'Exam duration shortcut values available for scheduling',
          'fields' => array(
            'minutes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Exam duration minutes',
            ),
          ),
          'primary key' => array('minutes'),
        );

        
        $schema['raptor_cache_data'] = array(
          'description' => 'Runtime cached data',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who owns this data',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'retry_delay' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Seconds to wait before retry a cache read',
            ),
            'max_age' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'This data expires after this many seconds',
            ),
            'group_name' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Group to which this data belongs',
            ),
            'item_name' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Specific name of data in the group',
            ),
            'item_data' => array(
              'type' => 'blob',
              'mysql_type' => 'mediumblob',
              'not null' => TRUE,
              'description' => 'The cached data',
            ),
          ),
          'primary key' => array('uid','group_name','item_name'),
        );

        $schema['raptor_cache_flag'] = array(
          'description' => 'Runtime cache flags',
          'fields' => array(
            'uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who owns this data',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'retry_delay' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Seconds to wait before retry a flag read',
            ),
            'max_age' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'This flag expires after this many seconds',
            ),
            'group_name' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Group to which this flag belongs',
            ),
            'item_name' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Specific name of item in the group',
            ),
            'flag_name' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Specific name of flag in the group',
            ),
            'flag_value' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Value of the flag',
            ),
          ),
          'primary key' => array('uid','group_name','item_name','flag_name'),
        );
        
        $schema['raptor_protocol_radiation_dose_tracking'] = array(
          'description' => 'Simple average radiation exposure for a protocol at a site.',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Protocol short name key value',
            ),
            'dose_source_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
            ),
            'uom' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'description' => 'Unit of measure for the exposure',
            ),
            'dose_type_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'E=Estimated,A=Actual,U=Unknown Quality',
            ),
            'dose_avg' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Average amount of exposure',
            ),
            'sample_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Number of samples averaged into this record',
            ),
            'baseline_dose_avg' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Initial baseline average amount of exposure',
            ),
            'baseline_sample_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Number of samples averaged into baseline value of this record',
            ),
            'lowest_dose_value' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Lowest dose instance',
            ),
            'highest_dose_value' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Highest dose instance',
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
              'not null' => TRUE,
              'description' => 'When this record was originally created',
            ),
          ),
          'primary key' => array('siteid', 'protocol_shortname', 'dose_source_cd', 'uom', 'dose_type_cd'),
        );
        
    }    
    
}
    