<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../core/data_context.php');
require_once(dirname(__FILE__) . '/../core/data_ticket_tracking.php');
require_once("ProtocolPageUtils.inc");
require_once('FormControlChoiceItem.php');


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
    public static function createSelectList($sName,$aChoices,$bDisabled=false,$aStatesEntry=NULL,$aMyvalues=NULL)
    {    
        $element = array(
            '#type' => 'select',
            '#disabled' => $bDisabled,
        );  
        if(isset($aMyvalues[$sName]))
        {
            if($bDisabled)
            {
                $element['#value']=$aMyvalues[$sName];
            } else { 
                $element['#default_value']=$aMyvalues[$sName];
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
    public static function createCustomSelectPanel($section_name, $sListRootName, $aChoices, $disabled, $aStatesEntry, $myvalues, $bShowCustomText=FALSE)
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
            $sTextboxName                                   = $sListRootName.'customtx';
            $controlroot[$sTextboxName] = array(
                '#type'    => 'textfield',
                '#disabled' => $disabled,
                '#default_value' => $myvalues[$sTextboxName], 
            );
        } else {
    //START PICKLIST PART       
            $element  = FormHelper::createSelectList($sListboxName, $aChoices, $disabled, $aStatesEntry, $myvalues);
            $controlroot[$sListboxName] = $element;
            $controlroot[$sListboxName]['#attributes'] 
                    = array(
                        'onchange' => 'notDefaultValuesInSection("'.$section_name.'")',
                        'style' => $initStyleListbox, 
                );

                        //Declare a button that switches to TEXTBOX mode.	
            $controlroot[$sListboxName.'_customize'] 
                    = array(
                '#markup' => "\n".'<div class="listalter-button-container"><a name="'.$sListRootName.'makecustombtn" style="'.$initStyleListbox.'"  href="javascript:setAsCustomText('."'".$sListRootName."'".')" title="Customize the selected value">CUSTOMIZE</a></div>', 
                '#disabled' => $disabled,
            );
    //CUSTOM TXT PORTION START
            $default_value = isset($myvalues[$sTextboxName]) ? $myvalues[$sTextboxName] : '';
            $controlroot[$sTextboxName] = array(
                '#type'    => 'textfield',
                '#attributes' => array(
                    'onchange' => 'notDefaultValuesInSection("'.$section_name.'")'),
                '#disabled' => $disabled,
                '#states' => $aStatesEntry,
                '#default_value' => $default_value,  //20140715
            );
            $controlroot[$sTextboxName]['#attributes'] 
                    = array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")', 
                        'style' =>  $initStyleTextbox, 
                        );

                        //Declare button that switches to LIST mode.
            $controlroot[$sTextboxName.'_listpick'] = array(
                '#markup' => "\n".'<div class="listalter-button-container"><a name="'.$sListRootName.'makelistpickbtn" style="'.$initStyleTextbox.'" class="listalter-button" href="javascript:setAsPickFromList('."'".$sListRootName."'".')" title="Pick from list instead of custom text">LIST</a></div>', 
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