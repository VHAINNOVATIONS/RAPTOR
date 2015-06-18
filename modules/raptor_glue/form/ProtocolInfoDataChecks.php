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

module_load_include('php', 'raptor_workflow', 'core/AllowedActions');
module_load_include('php', 'raptor_formulas', 'core/LanguageInference');
module_load_include('php', 'raptor_formulas', 'core/Conversions');
module_load_include('php', 'raptor_datalayer', 'core/FacilityRadiationDose');

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');

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



