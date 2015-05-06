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
 * This class is for List database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBListSchema
{
    /**
     * Add all the schema content for the list tables
     */
    public function addToSchema(&$schema)
    {
        $schema['raptor_list_hydration'] = array(
          'description' => 'Options for hydration panel',
          'fields' => array(
            'type_nm' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'Oral/IV/Enteric',
            ),
            'option_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'The text to show',
            ),
            'ct_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to CT?',
            ),
            'mr_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to MRI?',
            ),
            'nm_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Nuclear Medicine?',
            ),
            'fl_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Fluoroscopy?',
            ),
            'us_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Ultrasound?',
            ),
          ),
        );

        $schema['raptor_list_sedation'] = array(
          'description' => 'Options for sedation panel',
          'fields' => array(
            'type_nm' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'Oral/IV/Enteric',
            ),
            'option_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'The text to show',
            ),
            'ct_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to CT?',
            ),
            'mr_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to MRI?',
            ),
            'nm_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Nuclear Medicine?',
            ),
            'fl_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Fluoroscopy?',
            ),
            'us_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Ultrasound?',
            ),
          ),
        );

        $schema['raptor_list_radioisotope'] = array(
          'description' => 'Options for radiological pharma panel',
          'fields' => array(
            'type_nm' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'Oral/IV/Enteric',
            ),
            'option_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'The text to show',
            ),
            'ct_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Applies to CT?',
            ),
            'mr_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Applies to MRI?',
            ),
            'nm_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Nuclear Medicine?',
            ),
            'fl_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Fluoroscopy?',
            ),
            'us_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Ultrasound?',
            ),
          ),
        );

        $schema['raptor_list_contrast'] = array(
          'description' => 'Options for contrast panel',
          'fields' => array(
            'type_nm' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'Oral/IV/Enteric',
            ),
            'option_tx' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'The text to show',
            ),
            'ct_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to CT?',
            ),
            'mr_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to MRI?',
            ),
            'nm_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Nuclear Medicine?',
            ),
            'fl_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Fluoroscopy?',
            ),
            'us_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Applies to Ultrasound?',
            ),
          ),
        );
        
        $schema['raptor_list_modality'] = array(
          'description' => 'Modalities in which users specialize',
          'fields' => array(
            'modality_abbr' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Modality abbreviation',
            ),
            'modality_desc' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'Modality description',
            ),
            'prefixes' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'Comma delimited prefixes that indicate study is of this type  (Look only at start of study text)',
            ),
            'keywords' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'Comma delimited keywords that indicate study is of this type.  (Look anywhere in the text.)',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('modality_abbr'),
        );
  
        $schema['raptor_list_service'] = array(
          'description' => 'Services in which users specialize',
          'fields' => array(
            'service_nm' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'description' => 'Service name',
            ),
            'service_desc' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => TRUE,
              'description' => 'Service description',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('service_nm'),
        );

        $schema['raptor_list_radiation_dose_target'] = array(
          'description' => 'Radiation exposure area lookup',
          'fields' => array(
            'id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the area',
            ),
            'area_nm' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Target area',
            ),
            'area_desc' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Description of the target area',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
        );
        
        $schema['raptor_role'] = array(
          'description' => 'Raptor Roles',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Unique for each row of table',
            ),
            'enabled_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Role is available for users only if enabled',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Role name',
            ),
              
              
            'CEUA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit other accounts',
            ),
            'lockCEUA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then CEUA1 value can be changed, else locked.',
            ),
            'LACE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit resident account',
            ),
            'lockLACE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then LACE1 value can be changed, else locked.',
            ),
            'SWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can select worklist items for view',
            ),
            'lockSWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then SWI1 value can be changed, else locked.',
            ),
            'PWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can protocol worklist items',
            ),
            'lockPWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then PWI1 value can be changed, else locked.',
            ),
            'APWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can approve protocoled worklist items',
            ),
            'lockAPWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then APWI1 value can be changed, else locked.',
            ),
            'SUWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can cancel a worklist item',
            ),
            'lockSUWI1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then SUWI1 value can be changed, else locked.',
            ),
            'CE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can complete an examination',
            ),
            'lockCE1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then CE1 value can be changed, else locked.',
            ),
            'QA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can complete a QA examination',
            ),
            'lockQA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then QA1 value can be changed, else locked.',
            ),
            'SP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user edit pass box',
            ),
            'lockSP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then SP1 value can be changed, else locked.',
            ),
              
              
            'VREP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can view department activity report',
            ),
            'lockVREP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then VREP1 value can be changed, else locked.',
            ),
            'VREP2' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can view department activity report',
            ),
            'lockVREP2' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then VREP2 value can be changed, else locked.',
            ),
              
              
              
            'EBO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit protocol/exam note boilerplate options',
            ),
            'lockEBO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then EBO1 value can be changed, else locked.',
            ),
            'UNP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can upload new protocols',
            ),
            'lockUNP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then UNP1 value can be changed, else locked.',
            ),
            'REP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can retire existing protocols',
            ),
            'lockREP1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then REP1 value can be changed, else locked.',
            ),

            'DRA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit default and required attribs of roles',
            ),
            'lockDRA1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then DRA1 value can be changed, else locked.',
            ),
            'ELCO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit contrast options',
            ),
            'lockELCO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ELCO1 value can be changed, else locked.',
            ),
            'ELHO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit hydration options',
            ),
            'lockELHO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ELHO1 value can be changed, else locked.',
            ),
            'ELSO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit sedation options',
            ),
            'lockELSO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ELSO1 value can be changed, else locked.',
            ),
            'ELRO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit radioisotope options',
            ),
            'lockELRO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ELRO1 value can be changed, else locked.',
            ),

            'ELSVO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit service options',
            ),
            'lockELSVO1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ELSVO1 value can be changed, else locked.',
            ),
              
            'EECC1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit CPRS code metadata',
            ),
            'lockEECC1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then EECC1 value can be changed, else locked.',
            ),
              
            'ECIR1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit contraindication rules',
            ),
            'lockECIR1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then ECIR1 value can be changed, else locked.',
            ),
            'EERL1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can alter list of examinatino rooms',
            ),
            'lockEERL1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then EERL1 value can be changed, else locked.',
            ),
              
            'EARM1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can edit at risk medications keywords',
            ),
            'lockEARM1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then EARM1 value can be changed, else locked.',
            ),
              
            'CUT1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can create/edit umbrella terms and associate keywords with them',
            ),
            'lockCUT1' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then CUT1 value can be changed, else locked.',
            ),
              
	    'QA2' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then user can see all QA results',
            ),
            'lockQA2' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then QA2 value can be changed, else locked.',
            ),
              
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('name'),
        );
    }    
}
    