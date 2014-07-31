<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Frank Smith
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 * Updated 20140625
 */


namespace raptor;

/**
 * Description of contraindications
 *
 * @author vaphsfontf
 */
class Contraindications {
    
    private $maContraindications=array();
    
    /**
     * Provide an array of contraindications.
     * @param type $aContraindications 
     */
    public function __construct($aContraindications)
    {
        $this->maContraindications=$aContraindications;
    }
    
    /**
     * Get all the contra indications
     * @param type $sCategory
     * @return array of instances
     */
    public function getAll()
    {
        return $this->maContraindications;
    }
    
    /**
     * Get all the contra indications whree a confirmation is required.
     * @return type 
     */
    public function getAllConfirmationRequired()
    {
        $aJustCR=array();
        foreach($this->maContraindications as $oCI)
        {
            if($oCI->isConfirmationRequired())
            {
                $aJustCR[] = $oCI;   
            }
        }
        return $aJustCR;
    }
    

    /**
     * Returns all contraindication instances where any of the criteria match.
     * Leave an attibute value as NULL if you do not want to match on it.
     * 
     * @param Contraindication $oMatchCI
     * @param ContraSourceItem $oMatchCSI 
     */
    public function getAllMatching($oMatchCI=null,$oMatchCSI=null)
    {
        $aMatched=array();
        foreach($this->maContraindications as $oCI)
        {
            if(($oMatchCI ? $oCI->isMatch($oMatchCI) : true) && ($oMatchCSI ? $oCI->isMatchOnCSI($oMatchCSI) : true))
            {
                $aMatched[]=$oCI;
            }
        }
        return $aMatched;
    }
}

