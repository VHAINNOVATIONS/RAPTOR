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
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
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

