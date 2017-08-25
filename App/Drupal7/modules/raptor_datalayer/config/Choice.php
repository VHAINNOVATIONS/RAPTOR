<?php
/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase1 proof of concept
 * Open Source VA Innovation Project 2011-2012
 * Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Frank Smith
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * @author Frank Font
 */

namespace raptor;

/**
 * TODO --- Replace all usage of this class with FormControlChoiceItem class!!!
 * @deprecated since version number
 */
class raptor_datalayer_Choice
{
    public $sID;    //If not NULL, this is the value of the choice
    public $sValue; //This is the name to diplay for the choice
    public $bIsDefault=false; //If true, then this is the default choice
    public $sCategory;  //A category that the choice belongs to
    public $nScore=0;
    
    public function __construct($sValue,$sID,$sCategory=NULL,$bIsDefault=false)
    {
        $this->sValue=$sValue;
        $this->sID=$sID;
        $this->bIsDefault=$bIsDefault;
        $this->sCategory=$sCategory;
    }
}
