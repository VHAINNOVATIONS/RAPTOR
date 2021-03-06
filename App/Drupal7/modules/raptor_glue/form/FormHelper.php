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

module_load_include('php', 'raptor_datalayer', 'core/data_context');
module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');

require_once 'ASimpleFormPage.php';
require_once 'ProtocolPageUtils.inc';
require_once 'FormControlChoiceItem.php';

class FormHelper 
{
   
    public static function getArrayItemsAsDelimitedText($aStuff, $sDelimiter)
    {
        $result = '';
        if(is_array($aStuff))
        {
            $sPrefix = '';
            foreach($aStuff as $keyword)
            {
                $result .= $sPrefix . $keyword;
                if($sPrefix === '')
                {
                    $sPrefix = $sDelimiter;    //So that all other stuff is prefixed with delimiter.
                }
            }
        }
        return $result;
    }
    

    public static function hasSelectValue($values,$sEntryName)
    {
        if(strlen($values[$sEntryName]) < 1 || substr($values[$sEntryName],0,1)=='-')
        {
            return FALSE;
        }
        return TRUE;
    }

    public static function hasRadiosValue($values,$sEntryName)
    {
        if(strlen($values[$sEntryName]) < 1)
        {
            return FALSE;
        }
        return TRUE;
    }

    
    public static function isCheckboxSelected($POST_VALUE)
    {
        $sValue="::".trim($POST_VALUE);
        return ($sValue != "::0");
    }
    

    /**
    * If named entry in the array exists and it matches, then return
    * the true string, else return the false string.
    * @param type $aValues the array of values
    * @param type $sEntryName index in the array
    * @param type $sMatch to match
    * @param type $sTrueVal to return if matched
    * @param type $sFalseVal to return if not matched
    * @return type Either the true or the false string
    */
    public static function getFancyMatch($aValues,$sEntryName,$sMatch,$sTrueVal,$sFalseVal)
    {
        if(isset($aValues[$sEntryName]))
        {
            if($aValues[$sEntryName] == $sMatch)
            {
                return $sTrueVal;
            }
        }
        return $sFalseVal;
    }  

    /*
    * Create a level 2 select list in the Drupal Form API
    */ 
    public static function createSelectList($sName, $aChoices, $bDisabled=false, $aStatesEntry=NULL, $myvalues=NULL, $defaultlistvalue_override=NULL)
    {    
        $element = array(
            '#type' => 'select',
            '#disabled' => $bDisabled,
        );  
        if($defaultlistvalue_override !== NULL)
        {
            $element['#default_value']=$defaultlistvalue_override;
        } else {
            if(isset($myvalues[$sName]))
            {
                $element['#default_value']=$myvalues[$sName];
                //20140819 do not lock the value based on disabled because sometimes we re-enable!!!
            }
        }
        if(!$bDisabled && $aStatesEntry !== NULL)
        {
            $element['#states']=$aStatesEntry;
        }      
        $oControl = ProtocolPageUtils::getFAPI_select_options($element,$aChoices); 
        //die("$sName -> '".$aMyvalues[$sName]."'<hr>Control info...<hr>".print_r($aMyvalues,true)."<hr>". print_r($aChoices,true) ."<hr>".print_r($oControl,true));
        return $oControl;
    }
    
   
    /**
     * Create a control that is both a listbox and a textbox.
     */
    public static function createCustomSelectPanel($section_name, $sListRootName, $aChoices, $disabled
            , $aStatesEntry
            , $myvalues
            , $bShowCustomText=FALSE
            , $default_value_override=NULL
            , $nMaxlen=400)
    {
        $controlroot = array();
        if($bShowCustomText)
        {
            $initStyleListbox = 'display:none;';
            $initStyleTextbox = '';
        } else {
            $initStyleListbox = '';
            $initStyleTextbox = 'display:none;';
        }
        $sListboxName                            = $sListRootName.'id';
        $sTextboxName                            = $sListRootName.'customtx';
        if($disabled)
        {
            //Only supporting readonly of the value.
            $controlroot[$sTextboxName] = array(
                '#type'    => 'textfield',
                '#disabled' => TRUE,
                '#default_value' => $myvalues[$sTextboxName], 
            );
        } else {
    //START PICKLIST PART   
            if($bShowCustomText)
            {
                //No default selection in the picklist.
                $defaultlistvalue = ''; //Make it select blank option
            } else {
                $defaultlistvalue = $myvalues[$sTextboxName];   //Using the value as the key in the list.
            }
            $element  = FormHelper::createSelectList($sListboxName, $aChoices, $disabled, $aStatesEntry, $myvalues, $defaultlistvalue);
            $controlroot[$sListboxName] = $element;
            $controlroot[$sListboxName]['#attributes'] 
                    = array(
                        'onchange' => 'notDefaultValuesInSection("'.$section_name.'")',
                        'style' => $initStyleListbox, 
                );

                        //Declare a button that switches to TEXTBOX mode.	
            $controlroot[$sListboxName.'_customize'] 
                    = array(
                '#markup' => "\n".'<div class="listalter-button-container"><a name="'.$sListRootName.'makecustombtn" style="'.$initStyleListbox.'"  href="javascript:setAsCustomTextByName('."'".$sListRootName."'".')" title="Customize the selected value">CUSTOMIZE</a></div>', 
                '#disabled' => $disabled,
            );
    //CUSTOM TXT PORTION START
            if($default_value_override !== NULL)
            {
                $default_value = $default_value_override;
            } else {
                $default_value = isset($myvalues[$sTextboxName]) ? $myvalues[$sTextboxName] : '';
            }
            $controlroot[$sTextboxName] = array(
                '#type'    => 'textfield',
                '#attributes' => array(
                    'onchange' => 'notDefaultValuesInSection("'.$section_name.'")'),
                '#disabled' => $disabled,
                '#states' => $aStatesEntry,
                '#default_value' => $default_value,  //20140715
                '#maxlength' => $nMaxlen,    //20140810
            );
            if($aStatesEntry !== NULL)
            {
                $controlroot[$sTextboxName]['#states'] = $aStatesEntry; //20140819
            }
            $controlroot[$sTextboxName]['#attributes'] 
                    = array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")', 
                        'style' =>  $initStyleTextbox, 
                        );

            //Declare button that switches to LIST mode.
            $controlroot[$sTextboxName.'_listpick'] = array(
                '#markup' => "\n".'<div class="listalter-button-container"><a name="'.$sListRootName.'makelistpickbtn" style="'.$initStyleTextbox.'" class="listalter-button" href="javascript:setAsPickFromListByName('."'".$sListRootName."'".')" title="Pick from list instead of custom text">LIST</a></div>', 
                '#disabled' => $disabled,
            );
    //CUSTOM TEXT DONE
        }
        return $controlroot;
    }
    
     
    public static function getArrayItem($a,$sIndex,$sAltValue='')
    {
        if(isset($a[$sIndex]))
        {
            return $a[$sIndex];
        }
        return $sAltValue;
    }
    
    

}