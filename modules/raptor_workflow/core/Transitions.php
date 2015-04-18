<?php
/**
 * @file
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
     * Return TRUE if a ticket is available for protocoling.
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
     * Return TRUE if a ticket has been canceled.
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
    
    /**
     * One or more workflow states map to single processing state codes.
     * P = Protocol mode
     * E = Examination mode
     * I = Interpretation mode
     * Q = QA mode
     * C = Canceled mode
     */
    public function getTicketProcessingModeCodeFromWFS($sCWFS)
    {
        if($sCWFS == 'AC' || $sCWFS == 'CO' || $sCWFS == 'RV')
        {
            $sProcessingMode = 'P';
        } else
        if($sCWFS == 'AP' || $sCWFS == 'PA')
        {
            $sProcessingMode = 'E';
        } else
        if($sCWFS == 'EC')
        {
            $sProcessingMode = 'I';
        } else
        if($sCWFS == 'QA')
        {
            $sProcessingMode = 'Q';
        } else
        if($sCWFS == 'IA')
        {
            $sProcessingMode = 'C';
        } else {
            throw new \Exception('Did NOT find a valid ProcessingMode for workflow state code=[' . $sCWFS . ']');
        }
        return $sProcessingMode;
    }
    
    /**
     * Expand the code into a phrase.
     */
    public static function getTicketPhraseFromWorflowState($sCWFS)
    {
        if($sCWFS == 'AC')
        {
            $sPhrase = 'Active';
        } else
        if($sCWFS == 'CO')
        {
            $sPhrase = 'Collaboration';
        } else
        if($sCWFS == 'RV')
        {
            $sPhrase = 'Review';
        } else
        if($sCWFS == 'AP')
        {
            $sPhrase = 'Approved';
        } else
        if($sCWFS == 'PA')
        {
            $sPhrase = 'Protocol Acknowledged';
        } else
        if($sCWFS == 'EC')
        {
            $sPhrase = 'Exam Completed';
        } else
        if($sCWFS == 'QA')
        {
            $sPhrase = 'Quality Assurance';
        } else
        if($sCWFS == 'IA')
        {
            $sPhrase = 'Needs Cancel/Replace';
        } else {
            throw new \Exception('Did NOT find a valid Phrase for workflow state code=[' . $sCWFS . ']');
        }
        return $sPhrase;
    }
    
}
