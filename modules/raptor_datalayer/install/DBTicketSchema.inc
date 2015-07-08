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
 * This class is for Ticket database schema content
 *
 * author Frank Font of SAN Business Consultants
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
              'default' => 'AC',
              'description' => 'Current workflow state (assume AC if null)',
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
              'description' => 'When the protocol was approved',
            ),
            'acknowledged_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the approved protocol was acknowledged',
            ),
            'exam_completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this exam was completed',
            ),
            'interpret_completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the interpretation was completed',
            ),
            'qa_completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the QA was completed',
            ),
            'exam_details_committed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the exam details were committed to VistA',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('siteid', 'IEN'),
        );

        $schema['raptor_ticket_commit_tracking'] = array(
          'description' => 'Track commit of ticket data into VISTA',
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
              'description' => 'Workflow state of committed data',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that committed this data',
            ),
            'commit_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this data was committed',
            ),
          ),
          'primary key' => array('siteid', 'IEN', 'commit_dt'),
        );

        $schema['raptor_ticket_id_change_tracking'] = array(
          'description' => 'Track change of ticket id when a VISTA order is replaced.',
          'fields' => array(
            'original_siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site for original ticket',
            ),
            'original_IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Original ticket identifier within the site',
            ),
            'new_siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'ID for the site for new ticket',
            ),
            'new_IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'New ticket identifier within the site',
            ),
            'workflow_state_at_change' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Workflow state at time of the change',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The user that initiated this change',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this change happened',
            ),
          ),
          'primary key' => array('original_siteid', 'original_IEN'),
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
          'primary key' => array('siteid', 'IEN'),
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
            'workflow_state' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => FALSE,
              'description' => 'Ticket workflow state at time of request',
            ),
          ),
          'indexes' => array(
            'main_idx' => array('siteid', 'IEN', 'collaborator_uid', 'active_yn'),  
          ),
          //'primary key' => array('siteid', 'IEN', 'collaborator_uid', 'active_yn'),
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
            'collaborate_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The user which is collaborating on this ticket',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
            'allergy_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'allergy values are yes, no, or leave null (unknown).',
            ),
            'claustrophobic_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'claustrophobic values are yes, no, or leave null (unknown).',
            ),
            'consent_req_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Consent required values are yes, no, or leave null (unknown).',
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
          'primary key' => array('siteid', 'IEN'),
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
            'allergy_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'allergy values are yes, no, or unknown.',
            ),
            'claustrophobic_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'claustrophobic values are yes, no, or unknown.',
            ),
            'consent_req_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Consent required values are yes, no, or unknown.',
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
          'description' => 'Ticket exam values as provided by exam completer',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
            'consent_received_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Consent received values are yes, no, or leave null (unknown).',
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

        $schema['raptor_ticket_exam_settings_sofar'] = array(
          'description' => 'Ticket exam values as provided so far by an examiner.  This is a backup record that is deleted once final values are saved.',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
            'consent_received_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Consent received values are yes, no, or leave null (unknown).',
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
        
        $schema['raptor_ticket_exam_settings_replaced'] = array(
          'description' => 'Replaced ticket exam values as provided by exam completer',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
              'description' => 'True if value is NONE',
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
            'consent_received_kw' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Consent received values are yes, no, or leave null (unknown).',
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

        $schema['raptor_ticket_exam_radiation_dose'] = array(
          'description' => 'Radiation exposure associated with an exam',
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
              'description' => 'Ticket identifier within the site',
            ),
            'patientid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the patient',
            ),
            'sequence_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Order of exposures for an exam',
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
          'primary key' => array('siteid', 'IEN', 'dose_source_cd', 'sequence_position'),
        );

        $schema['raptor_ticket_exam_radiation_dose_sofar'] = array(
          'description' => 'Radiation exposure values so far associated with an exam.  This is a backup record that is deleted once final values are saved.',
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
              'description' => 'Ticket identifier within the site',
            ),
            'patientid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the patient',
            ),
            'sequence_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Order of exposures for an exam',
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
        );
        
        $schema['raptor_ticket_exam_radiation_dose_replaced'] = array(
          'description' => 'Radiation exposure associated with an exam',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Ticket identifier within the site',
            ),
            'patientid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the patient',
            ),
            'sequence_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Order of exposures for an exam',
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
              'description' => 'E=Estimated, A=Actual, U=Unknown Quality',
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
              'description' => 'Data provider for this record',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
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
        
        $schema['raptor_ticket_exam_notes'] = array(
          'description' => 'Ticket exam notes',
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

        $schema['raptor_ticket_exam_notes_sofar'] = array(
          'description' => 'Ticket exam notes so far.  This is a backup record that is deleted once final values are saved.',
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
        
        $schema['raptor_ticket_interpret_notes'] = array(
          'description' => 'Ticket interpretation notes',
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

        $schema['raptor_qa_criteria'] = array(
          'description' => 'Quality assurance evaluation criteria',
          'fields' => array(
            'context_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'default' => 'T',
              'description' => 'T=Ticket',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of this criteria if there have been replacements',
            ),
            'position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Relative order for default presentation of the criteria',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'A unique ID for this criteria',
            ),
            'question' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'default' => '',
              'description' => 'The criteria question',
            ),
            'explanation' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'default' => '',
              'description' => 'The criteria explained',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this criteria was last updated',
            ),
          ),
          'primary key' => array('context_cd','shortname'),
        );
        
        $schema['raptor_qa_criteria_replaced'] = array(
          'description' => 'A replaced quality assurance evaluation criteria',
          'fields' => array(
            'context_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'default' => 'T',
              'description' => 'T=Ticket',
            ),
            'version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The version number of the replaced criteria',
            ),
            'position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Relative order for default presentation of the criteria',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'default' => '',
              'description' => 'A unique ID for this criteria',
            ),
            'question' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'default' => '',
              'description' => 'The criteria question',
            ),
            'explanation' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'default' => '',
              'description' => 'The criteria explained',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this criteria was last updated',
            ),
            'replacement_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this criteria was replaced',
            ),
          ),
        );
        
        $schema['raptor_ticket_qa_evaluation'] = array(
          'description' => 'Quality assurance evaluation of a ticket',
          'fields' => array(
            'siteid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'ID for the site',
            ),
            'IEN' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Ticket identifier within the site',
            ),
            'workflow_state' => array(
              'type' => 'varchar',
              'length' => 2,
              'not null' => TRUE,
              'description' => 'Workflow state of evaluated ticket',
            ),
            'criteria_shortname' => array(
              'type' => 'varchar',
              'length' => 32,
              'not null' => TRUE,
              'description' => 'The criteria evaluated',
            ),
            'criteria_version' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The version number of the criteria',
            ),
            'criteria_score' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The score assigned on the criteria',
            ),
            'comment' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'default' => '',
              'description' => 'The comment associated with this evaluation',
            ),
            'author_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The user that evaluated the ticket',
            ),
            'evaluation_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this ticket was evaluated',
            ),
          ),
          'primary key' => array('siteid', 'IEN', 'author_uid', 'criteria_shortname', 'evaluation_dt'),
        );
        
        $schema['raptor_ticket_qa_notes'] = array(
          'description' => 'Ticket quality assurance notes',
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
  
        $schema['raptor_ticket_unsuspend_notes'] = array(
          'description' => 'Ticket unsuspension notes',
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

        $schema['raptor_ticket_checklist'] = array(
          'description' => 'Checklist question responses',
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
            'question_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The unique shortname of the question in the system',
            ),
            'question_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The actual question text the user was asked',
            ),
            'answer_tx' => array(
              'type' => 'varchar',
              'length' => 16,
              'not null' => FALSE,
              'description' => 'The response provided by the user',
            ),
            'comment_prompt_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Prompt shown the user when asking for a comment',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Comment provided by the user',
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
        
        $schema['raptor_ticket_checklist_replaced'] = array(
          'description' => 'Checklist question responses replaced records',
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
            'question_shortname' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The unique shortname of the question in the system',
            ),
            'question_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The actual question text the user was asked',
            ),
            'answer_tx' => array(
              'type' => 'varchar',
              'length' => 16,
              'not null' => FALSE,
              'description' => 'The response provided by the user',
            ),
            'comment_prompt_tx' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Prompt shown the user when asking for a comment',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Comment provided by the user',
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
              'description' => 'The creation date of the replaced record',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the record was replaced',
            ),
          ),
        );

        $schema['raptor_ticket_contraindication'] = array(
          'description' => 'Contraindication acknowledgements',
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
            'rule_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Must be unique',
            ),
            'acknowledged_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If acknowledged then value is 1, else value is 0.',
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
    