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
 * Knows what transitions are allowed.
 *
 * @author Frank Font of SAN Business Consultants
 */
class Transitions 
{
    private $m_aTransitions = array(
        'AC'=>array('AC','CO','RV','IA')
        ,'CO'=>array('AC','CO','RV','IA')
        ,'RV'=>array('AC','CO','RV','IA')
        ,'AP'=>array('PA','IA')
        ,'PA'=>array('EC','IA')
        ,'EC'=>array('QA','IA')
        ,'QA'=>array('QA','IA')
        ,'IA'=>array('AC')
        );
    
    /**
     * Return TRUE if a ticket is available for protocolling.
     */
    public function isReadyForProtocolState($sTicketState)
    {
        return $sTicketState == 'AC' || $sTicketState == 'CO' || $sTicketState == 'RV';
    }

    /**
     * Return TRUE if a ticket is ready for technician.
     */
    public function isReadyForExamState($sTicketState)
    {
        return $sTicketState == 'AP' || $sTicketState == 'PA';
    }
    
    /**
     * Return TRUE if a ticket has completed its patient care workflow.
     */
    public function isExamCompleteState($sTicketState)
    {
        return $sTicketState == 'EC' || $sTicketState == 'QA';
    }
    
    /**
     * Return TRUE if a ticket has been cancelled.
     */
    public function isCancelledState($sTicketState)
    {
        return $sTicketState == 'IA';
    }

    /**
     * Return array of allowed transitions from the provided workflow state.
     */
    public function getAllowedTransitions($sFrom)
    {
        if(!isset($this->m_aTransitions[$sFrom]))
        {
            throw new \Exception('Invalid from state value "'.$sFrom.'"!');
        }
        return $m_aTransitions[$sFrom];
    }
    
    /**
     * Return true if state transition is allowed.
     */
    public function isAllowedTransition($sFrom, $sTo)
    {
        $aAllowed = $this->getAllowedTransitions($sFrom);
        return in_array($sTo, $aAllowed);
    }
}
