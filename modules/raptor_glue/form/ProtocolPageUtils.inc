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

module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'config/ListUtils');
require_once 'FormHelper.php';

class ProtocolPageUtils
{
    static function getAttributeForSelectOption($choices,$attributeName,$optionID)
    {
        //TODO --- check for exam
        return $value;
    }
    
    /**
     * Copy all the user settings from a saved order to a new one.
     * @param type $sourceTID fully qualified source ticket id
     * @param type $targetTID fully qualified target ticket id
     */
    public static function copyProtocolDetailsToNewOrder($sourceTID, $targetTID)
    {
        $source_parts = explode('-', $sourceTID);
        $target_parts = explode('-', $targetTID);
        
        if($source_parts[0] !== $target_parts[0])
        {
            throw new \Exception("Cannot replace source [$sourceTID] order with because [$targetTID] site is not same for both!");
        }
        $source_IEN = $source_parts[1];
        $target_IEN = $target_parts[1];
        if($source_IEN == '' || $target_IEN == '')
        {
            throw new \Exception("Cannot replace source [$sourceTID] order with [$targetTID] because the IEN is missing in one or both!");
        }

        $oTT = new \raptor\TicketTrackingData();
        $oContext = \raptor\Context::getInstance();
        $nUID = $oContext->getUID();
        $oTT->setTicketAsReplaced($sourceTID, $targetTID, $nUID);

        return TRUE;
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

