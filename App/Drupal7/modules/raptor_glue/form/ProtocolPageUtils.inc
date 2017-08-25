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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'config/ListUtils');
require_once ('FormHelper.php');

class ProtocolPageUtils
{
    static function getAttributeForSelectOption($choices,$attributeName,$optionID)
    {
        //TODO --- check for exam
        return $value;
    }
    
    /**
     * Get the options for the FAPI selection control
     * @param FORM API instance $element
     * @param object array $choices
     * @return array for DRUPAL FORM API SELECTION CONTROL 
     */
    static function getFAPI_select_options(&$element,$choices)
    {
        //$choice = new raptor_datalayer_Choice('NA','NA');
        $a = array();
        $bHasValue=(isset($element['#default_value']) || isset($element['#value']));
        foreach($choices as $choice)
        {
            $a[$choice->sCategory][$choice->sID] = $choice->sValue;
            if($choice->bIsDefault && !$bHasValue)
            {
                $element['#default_value']=$choice->sID;
            }
        }
        $element['#options'] = $a;   

        //die("<br>choices...".print_r($choices,true)."<br><br>".$nIndex." ------------> ".print_r($a,true)."<br>  kkkkkkkkkkkkkkkkkkkkkkkkkkkkk ".print_r($element,true));
        return $element;
    }    
}

