<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * This class is for Ticket database schema content
 *
 * @author FrankWin7VM
 */
class DBTicketSchema
{
    /**
     * Add all the schema content for the Ticket tables
     */
    public function addToSchema(&$schema)
    {
        
        $schema['raptor_ticket_tracking'] = array(
          'description' => 'Ticket Tracking',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'workflow_state' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Current workflow state',
            ),
            'suspended_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'Null if this ticket has not been suspended',
            ),
            'approved_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was approved',
            ),
            'exam_completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this exam was completed',
            ),
            'qa_completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the QA was completed',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('IEN'),
        );

        $schema['raptor_ticket_lock_tracking'] = array(
          'description' => 'Ticket Lock Tracking',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'locked_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who locked the ticket',
            ),
            'locked_type_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => TRUE,
              'default' => 'E',
              'description' => 'Type of lock',
            ),
            'lock_started_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the lock began',
            ),
            'lock_refreshed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'Most recent time the lock has been refreshed',
            ),
          ),
          'primary key' => array('IEN'),
        );

        $schema['raptor_ticket_collaboration'] = array(
          'description' => 'Collaboration on tickets',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'requester_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who requested the collaboration',
            ),
            'requested_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this collaboration was requested',
            ),
            'requester_notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'notes text',
            ),
            'collaborator_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who requested for collaboration',
            ),
            'viewed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the ticket was viewed by the collaborator',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'This value is 1 if request is still active',
            ),
          ),
          'primary key' => array('IEN', 'collaborator_uid' ),
        );

        $schema['raptor_ticket_workflow_history'] = array(
          'description' => 'Ticket workflow history',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'initiating_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user causing the state change',
            ),
            'old_workflow_state' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'The ticket workflow state before change',
            ),
            'new_workflow_state' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'The ticket workflow state after change',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_protocol_settings'] = array(
          'description' => 'Ticket protocol values as provided by protocoler',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'primary_protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Primary protocol short name',
            ),
            'secondary_protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Secondary protocol short name',
            ),
            'current_workflow_state_cd' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Current workflow state code',
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
            'claustrophobic_kw' => array(
              'type' => 'varchar',
              'length' => 4,
              'not null' => FALSE,
              'default' => '',
              'description' => 'claustrophobic values are yes, no, or leave null.',
            ),
            'consent_req_kw' => array(
              'type' => 'varchar',
              'length' => 4,
              'not null' => FALSE,
              'default' => '',
              'description' => 'consent required values are yes, no, or leave null.',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that selected these values',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_protocol_settings_replaced'] = array(
          'description' => 'Ticket protocol values as provided by protocoler which have now been replaced with new values',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'primary_protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Primary protocol short name',
            ),
            'secondary_protocol_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Secondary protocol short name',
            ),
            'current_workflow_state_cd' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Current workflow state code',
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
            'claustrophobic_kw' => array(
              'type' => 'varchar',
              'length' => 4,
              'not null' => FALSE,
              'default' => '',
              'description' => 'claustrophobic values are yes, no, or leave null.',
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
              'length' => 4,
              'not null' => FALSE,
              'default' => '',
              'description' => 'consent required values are yes, no, or leave null.',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that selected these values',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_protocol_notes'] = array(
          'description' => 'Ticket protocol notes',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'default' => '',
              'description' => 'notes text',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that created these notes',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_exam_settings'] = array(
          'description' => 'Ticket protocol values as provided by exam completer',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
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
              'length' => 4,
              'not null' => FALSE,
              'default' => '',
              'description' => 'consent required values are yes, no, or leave null.',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
        );

        $schema['raptor_ticket_exam_notes'] = array(
          'description' => 'Ticket protocol notes',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'default' => '',
              'description' => 'notes text',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that created these notes',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_qa_notes'] = array(
          'description' => 'Ticket protocol notes',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'default' => '',
              'description' => 'notes text',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that created these notes',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );

        $schema['raptor_ticket_suspend_notes'] = array(
          'description' => 'Ticket suspension notes',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Ticket identifier within the site',
            ),
            'notes_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'default' => '',
              'description' => 'notes text',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that created these notes',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
        );
  
    }    
}
    