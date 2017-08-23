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
 * A choice for embedding into a select control of a form
 *
 * @author Frank Font of SAN Business Consultants
 */
class FormControlChoiceItem
{
    public $sID;    //If not NULL, this is the value of the choice
    public $sValue; //This is the name to diplay for the choice
    public $bIsDefault=FALSE; //If true, then this is the default choice
    public $sCategory;  //A category that the choice belongs to
    public $nScore=0;
    
    public function __construct($sValue,$sID,$sCategory=NULL,$bIsDefault=FALSE)
    {
        $this->sValue=$sValue;
        $this->sID=$sID;
        $this->bIsDefault=$bIsDefault;
        $this->sCategory=$sCategory;
    }
    
    public function setAsDefault($bDefault)
    {
        $this->bIsDefault = $bDefault;
    }
}

