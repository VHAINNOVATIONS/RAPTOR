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
 * This class is for Ticket database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class DBPatientSchema
{
    /**
     * Add all the schema content for the Ticket tables
     */
    public function addToSchema(&$schema)
    {
        
        $schema['raptor_patient_radiation_dose'] = array(
          'description' => 'Radiation exposure for a patient',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'patientid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the patient',
            ),
            'dose' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Amount of exposure',
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
            'dose_source_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
            ),
            'dose_target_area_id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Target area lookup of codes',
            ),
            'dose_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this dose was received',
            ),
            'data_provider' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Data provider for this record',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that associated with entry of this value',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('patientid', 'dose_source_cd', 'dose_dt', 'dose_target_area_id'),
        );

        $schema['raptor_patient_radiation_dose_replaced'] = array(
          'description' => 'Replaced radiation exposure for a patient',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'patientid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the patient',
            ),
            'dose' => array(
              'type' => 'float',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Amount of exposure',
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
            'dose_source_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
            ),
            'dose_target_area_id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Target area lookup of codes',
            ),
            'dose_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this dose was received',
            ),
            'data_provider' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Data provider for this record',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that associated with entry of this value',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'The creation date of the replaced record',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );
    }    
}
    