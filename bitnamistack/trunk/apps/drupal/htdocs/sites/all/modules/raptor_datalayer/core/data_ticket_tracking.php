<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * This class is used to manage the ticket tracking information.
 *
 * @author SAN
 */
class TicketTrackingData 
{
    /**
     * Generate a unique tracking ID from immutable elements of one record from data store
     * The system can use this ID to then find one and only one record in the data store
     * @return string
     */
    public function getTrackingID($nSiteID, $nIEN)
    {
        return $nSiteID . '-' . $nIEN;
    }
    
    /**
     * Tell us what the ticket type is from the workflow state of the ticket.
     * @param type $aTrackingID
     * @return string
     */
    public function getTicketType($sTrackingID)
    {
        $sCWFS = getTicketWorkflowState($sTrackingID);
        return $this->getTicketTypeFromWorflowState($sCWFS);
    }

    /**
     * Tell us what the ticket type is from the workflow state of the ticket.
     * @param type $sCWFS current workflow state
     * @return string
     */
    public function getTicketTypeFromWorflowState($sCWFS)
    {
        if($sCWFS == 'AC' || $sCWFS == 'CO' || $sCWFS == 'RV')
        {
            $sType = 'P';
        } else
        if($sCWFS == 'AP' || $sCWFS == 'PA')
        {
            $sType = 'E';
        } else
        if($sCWFS == 'EC')
        {
            $sType = 'I';
        } else
        if($sCWFS == 'QA' || $sCWFS == 'IA')
        {
            $sType = 'Q';
        } else {
            $sType = NULL;
            drupal_set_message('Did NOT find a valid ticket type for ' . $sTrackingID);
        }
        return $sType;
    }

    /**
     * Return the workflow state of a ticket.
     * @param type $sTrackingID
     * @return string
     */
    public function getTicketWorkflowState($sTrackingID)
    {
        $aParts = explode('-',$sTrackingID);
        $nSiteID = $aParts[0];
        $nIEN = $aParts[1];
        $aWorkflowStateRecord = db_select('raptor_ticket_tracking', 'n')
            ->fields('n')
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->execute()
            ->fetchAssoc();       
        if(!isset($aWorkflowStateRecord['workflow_state']) || $aWorkflowStateRecord['workflow_state'] == '')
        {
            $sCWFS = 'AC';
        } else {
            $sCWFS = $aWorkflowStateRecord['workflow_state'];
        } 
        return $sCWFS;        
    }
    
    /**
     * Returns array if ticket assignment info if ticket is in collaborate mode.
     * Use is_array to see if in collaborate mode.
     * @param type $sTrackingID
     */
    public function getCollaborationInfo($sTrackingID)
    {
        $return = NULL;
        $aParts = explode('-',$sTrackingID);
        $nSiteID = $aParts[0];
        $nIEN = $aParts[1];
        $result = db_select('raptor_ticket_collaboration','p')
                ->fields('p')
                ->condition('siteid',$nSiteID,'=')
                ->condition('IEN',$nIEN,'=')
                ->execute();
        $nRows = $result->rowCount();
        if($nRows > 0)
        {
            //Return the fields of the found record.
            $return = $result->fetchAssoc();
        }
        return $return;
    }
    
    /**
     * Set or clear the collaboration status of a ticket.
     * @param type $sTrackingID
     * @param type $nRequesterUID
     * @param type $sRequesterNotes
     * @param type $nCollaboratorUID
     * @param type $sCWFS current workflow status
     * @param type $updated_dt
     */
    public function setCollaborationUser($sTrackingID, $nRequesterUID, $sRequesterNote, $nCollaboratorUID, $sCWFS=NULL, $updated_dt=NULL)
    {
        $successMsg = NULL;
        if($sCWFS == NULL)
        {
            $sCWFS = $this->getTicketWorkflowState($sTrackingID);
        }
        if($updated_dt == NULL)
        {
            $updated_dt = date("Y-m-d H:i:s", time());
        }
        $aParts = explode('-',$sTrackingID);
        $nSiteID = $aParts[0];
        $nIEN = $aParts[1];

        //Make sure we are okay to reserve this ticket.
        if($sCWFS !== 'AC' && $sCWFS !== 'AP' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
        {
            $msg = 'Only tickets in the active or approved or collaborate status can be reserved!  Ticket ' . $sTrackingID . ' is in the [' .$sCWFS. '] state!';
            error_log($msg);
            die($msg);
        }

        //Create the raptor_ticket_collaboration record now
        try
        {
            if($nCollaboratorUID == NULL)
            {
                //Simply delete the existing collaboration record if it exists.
                $num_deleted = db_delete('raptor_ticket_collaboration')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->execute();
                $successMsg = 'Ticket '.$sTrackingID.' is longer assigned to anyone.';
            } else {
                $result = db_select('raptor_ticket_collaboration','p')
                        ->fields('p')
                        ->condition('siteid',$nSiteID,'=')
                        ->condition('IEN',$nIEN,'=')
                        ->condition('collaborator_uid',$nCollaboratorUID,'=')
                        ->execute();
                $nRows = $result->rowCount();
                if($nRows > 0)
                {
                    $successMsg = 'Already assigned ' . $sTrackingID;
                } else {
                    $result = db_select('raptor_ticket_collaboration','p')
                            ->fields('p')
                            ->condition('siteid',$nSiteID,'=')
                            ->condition('IEN',$nIEN,'=')
                            ->condition('collaborator_uid',$nCollaboratorUID,'<>')
                            ->execute();
                    $nRows = $result->rowCount();
                    $oInsert = db_merge('raptor_ticket_collaboration')
                            ->key(array('siteid' => $nSiteID,'IEN' => $nIEN,))
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'requester_uid' => $nRequesterUID,
                                'requested_dt' => $updated_dt,
                                'requester_notes_tx' => $sRequesterNote,
                                'collaborator_uid' => $nCollaboratorUID,
                                'active_yn' => 1,
                            ))
                            ->execute();
                    if($nRows > 0)
                    {
                        $successMsg = 'Replaced other user assignment ' . $sTrackingID;
                    } else {
                        $successMsg = 'Assigned other user assignment ' . $sTrackingID;
                    }
                }
            }
        }
        catch(PDOException $e)
        {
            error_log('Failed to create raptor_ticket_collaboration: ' . $e . "\nDetails..." . print_r($oInsert,true));
            die('Failed to reserve this ticket!');
            return 0;
        }

        //Did we collaborate or remove collaboration?
        if($nCollaboratorUID == NULL)
        {
            $sNewWFS = 'AC'; 
        } else {
            $sNewWFS = 'CO'; 
        }
        $this->setTicketWorkflowState($sTrackingID, $nRequesterUID, $sNewWFS, $sCWFS, $updated_dt);
        
        return $successMsg;
    }
    
    /**
     * Update the database
     * @param type $sTrackingID
     * @param type $nUID
     * @param type $sNewWFS
     * @param type $sCWFS optional
     * @param type $updated_dt optional
     * @return int Description 0 for failed, 1 for success
     */
    public function setTicketWorkflowState($sTrackingID, $nUID, $sNewWFS, $sCWFS=NULL, $updated_dt=NULL)
    {
        if($sCWFS == NULL)
        {
            $sCWFS = $this->getTicketWorkflowState($sTrackingID);
        }
        if($updated_dt == NULL)
        {
            $updated_dt = date("Y-m-d H:i:s", time());
        }
        $aParts = explode('-',$sTrackingID);
        $nSiteID = $aParts[0];
        $nIEN = $aParts[1];
        
        //Try to create the raptor_ticket_tracking record now
        try
        {
            $aFields = array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'workflow_state' => $sNewWFS,
                        'updated_dt' => $updated_dt,
                );
            if($sNewWFS == 'AP')
            {
                $aFields['approved_dt'] = $updated_dt;
            }
            if($sNewWFS == 'EC')
            {
                $aFields['exam_completed_dt'] = $updated_dt;
            }
            if($sNewWFS == 'QA')
            {
                $aFields['qa_completed_dt'] = $updated_dt;
            }
            if($sNewWFS == 'IA')
            {
                $aFields['suspended_dt'] = $updated_dt;
            }
            $oInsert = db_merge('raptor_ticket_tracking')
                    ->key(array('siteid'=>$nSiteID, 'IEN'=>$nIEN))
                    ->fields($aFields)
                    ->execute();
        }
        catch(PDOException $e)
        {
            error_log('Failed to update raptor_ticket_tracking: ' . $e . "\nDetails..." . print_r($oInsert,true));
            drupal_set_message('Failed to change workflow status of this ticket!','error');
            return 0;
        }

        //Create the raptor_ticket_workflow_history record now
        try
        {
            $oInsert = db_insert('raptor_ticket_workflow_history')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'initiating_uid' => $nUID,
                        'old_workflow_state' => $sCWFS,
                        'new_workflow_state' => $sNewWFS,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(PDOException $e)
        {
            error_log('Failed to create raptor_ticket_workflow_history: ' . $e . "\nDetails..." . print_r($oInsert,true));
            drupal_set_message('Failed to save history for this ticket!','error');
            return 0;
        }
        return 1;
    }

    /**
     * Queries the database to see if ticket is locked
     * @param type $sTrackingID
     * @param type $nUID
     * @return boolean
     */
    public function isTicketLocked($sTrackingID, $nUID)
    {
        //TODO db_select raptor_ticket_lock_tracking
        return false;
    }
    
    /**
     * Update the database
     * @param type $sTrackingID
     * @param type $nUID
     */
    public function updateTicketLock($sTrackingID, $nUID)
    {
        //TODO db_update raptor_ticket_lock_tracking
    }
    
    /**
     * Update the database
     * @param type $sTrackingID
     * @param type $nUID
     */
    public function markTicketLocked($sTrackingID, $nUID)
    {
        //TODO db_update raptor_ticket_lock_tracking
    }

    /**
     * Update the database
     * @param type $sTrackingID
     * @param type $nUID
     */
    public function markTicketUnlocked($sTrackingID, $nUID)
    {
        //TODO db_delete raptor_ticket_lock_tracking
    }

}
