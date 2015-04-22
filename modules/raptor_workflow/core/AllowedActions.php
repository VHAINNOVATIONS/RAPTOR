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

require_once 'Transitions.php';

/**
 * Knows what workflow dependent actions are allowed.
 *
 * @author Frank Font of SAN Business Consultants
 */
class AllowedActions 
{

    private $m_oTransitions = NULL;
    
    function __construct()
    {
        $this->m_oTransitions = new \raptor\Transitions();
    }
    
    public function allowApproveProtocol($current_wfs,&$feedback='')
    {
        if(!$this->m_oTransitions->isAllowedTransition($current_wfs, 'AP'))
        {
            $feedback = "Only active tickets can be approved.";
            return FALSE;
        }
        return TRUE;
    }

    public function allowRequestApproveProtocol($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active tickets can be approved.";
            return FALSE;
        }
        return TRUE;
    }

    public function allowUnapproveProtocol($current_wfs,&$feedback='')
    {
        $allowed=array('AP');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only approved tickets that have not yet completed the exam can be unapproved.";
            return FALSE;
        }
        return TRUE;
    }

    public function allowAcknowledgeProtocol($current_wfs,&$feedback='')
    {
        if(!$this->m_oTransitions->isAllowedTransition($current_wfs, 'PA'))
        {
            $feedback = "Only approved tickets can be acknowledged.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowUnacknowledgeProtocol($current_wfs,&$feedback='')
    {
        $allowed=array('PA');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only acknowledged tickets can be unacknowledged.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowExamComplete($current_wfs,&$feedback='')
    {
        if(!$this->m_oTransitions->isAllowedTransition($current_wfs, 'EC'))
        {
            $feedback = "Only acknowledged tickets can be marked as exam completed.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowInterpretationComplete($current_wfs,&$feedback='')
    {
        $allowed=array('EC');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only exam completed tickets can be interpretted.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowQAComplete($current_wfs,&$feedback='')
    {
        $allowed=array('QA');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only tickets in QA workflow state can be marked as QA complete.";
            return FALSE;
        }
        return TRUE;
    }

    public function allowReplaceOrder($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV','AP','PA','IA');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active orders that have not completed examination can be replaced.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowCancelOrder($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV','AP','PA','IA');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active orders that have not completed examination can be canceled.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowCollaborateTicket($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active tickets can be collaborated.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowReserveTicket($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active tickets can be reserved.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowScheduleTicket($current_wfs,&$feedback='')
    {
        $allowed=array('AC','CO','RV','AP','PA');
        if(!in_array($current_wfs, $allowed))
        {
            $feedback = "Only active tickets where exam has not been completed can be scheduled.";
            return FALSE;
        }
        return TRUE;
    }
    
    public function allowCommitNotesToVista($current_wfs,&$feedback='')
    {
        $allowed=array('EC','QA');
        if(ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_AP)
        {
            $allowed[] = 'AP';
        }
        if(ALLOW_TICKET_STATE_SHORTCUT_TO_QA_FROM_PA)
        {
            $allowed[] = 'PA';
        }
        if(!in_array($current_wfs, $allowed))
        {
            $allowedstates = implode(" ", $allowed);
            $feedback = "Only for tickets in one of the states ($allowedstates) can be committed to Vista.";
            return FALSE;
        }
        return TRUE;
    }
}
