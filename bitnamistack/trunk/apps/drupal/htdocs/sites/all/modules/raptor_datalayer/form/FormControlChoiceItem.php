<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

/**
 * A choice for embedding into a select control of a form
 *
 * @author FrankWin7VM
 */
class FormControlChoiceItem
{
    public $sID;    //If not NULL, this is the value of the choice
    public $sValue; //This is the name to diplay for the choice
    public $bIsDefault=FALSE; //If true, then this is the default choice
    public $sCategory;  //A category that the choice belongs to
    public $nScore=0;
    
    public function __construct($sValue,$sID,$sCategory=NULL,$bIsDefault=false)
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

