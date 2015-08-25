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

module_load_include('php', 'raptor_workflow', 'core/AllowedActions');
module_load_include('php', 'raptor_formulas', 'core/LanguageInference');
module_load_include('php', 'raptor_formulas', 'core/Conversions');
module_load_include('php', 'raptor_datalayer', 'core/FacilityRadiationDose');

module_load_include('inc', 'raptor_glue', 'functions/protocol');

require_once 'ProtocolInfoUtility.php';
require_once 'FormHelper.php';

/**
 * This class works with Protocol Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ProtocolInfoDataChecks
{
    public static function checkNoteText($sectionname,$myvalues)
    {
        $validationdetails = array();
        $textfieldname = $sectionname.'_tx';
        $note_tx = $myvalues[$textfieldname];
        $opensubareas = explode('[<',$note_tx);
        $errors = array();
        foreach($opensubareas as $checkthis)
        {
            $endpos = strpos($checkthis,'>]');
            if($endpos !== FALSE)
            {
                $cluetext = trim(substr($checkthis,0,$endpos));
                if($cluetext > '')
                {
                    $errors[] = 'Please fill in the "'.$cluetext.'" portion of the note text';
                }
            }
        }
        if(count($errors) > 0)
        {
            $errorcount = count($errors);
            $validationdetails[$textfieldname] = array();
            $validationdetails[$textfieldname]['errors'] = $errors;
            $validationdetails[$textfieldname]['fieldname'] = $textfieldname;
            $validationdetails[$textfieldname]['errorcount'] = $errorcount;
            if($errorcount > 1)
            {
                $intro = "Note area needs $errorcount corrections";
                $errormarkup = '<div classname="grouped-error-feedback">'
                        . "$intro:<ol><li>"
                        . implode('</li><li>',$errors)
                        . '</ol></div>';
            } else {
                $errormarkup = "Note area needs 1 correction: {$errors[0]}";
            }
            $validationdetails[$textfieldname]['msgmarkup'] = $errormarkup;
        }
        return $validationdetails;
    }
}



