<?php

/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 * Created 20140729
 */


namespace raptor;

/**
 * Instance of a rule from database.
 */
class ContraIndFlag
{
    private $maFlagInfo = NULL;
    
    /**
     * @param type $aFlagInfo associative array of one record
     */
    public function __construct($aFlagInfo)
    {
        $this->maFlagInfo=$aFlagInfo;
    }
    
    public function __toString()
    {
        return $this->maFlagInfo['flag_nm'] . '=(' . $this->maFlagInfo['criteria_tx'] . ')';
    }
    
    public function getAllInfo()
    {
        return $this->maFlagInfo;
    }
}
