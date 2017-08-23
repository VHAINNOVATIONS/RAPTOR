<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
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
 *
 * This is a simple decision support engine module for Drupal.
 */


namespace simplerulesengine;


/**
 * This class helps create measure forms
 *
 * @author Frank Font of SAN Business Consultants
 */
class MeasurePageHelper
{
    protected $m_oSREngine = NULL;
    protected $m_oSREContext = NULL;
    protected $m_urls_arr = NULL;
    protected $m_sMeasureclassname = NULL;
    
    public function __construct($oSREngine, $urls_arr, $sMeasureclassname=NULL)
    {
        $this->m_oSREngine = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr = $urls_arr;
        $this->m_sMeasureclassname = $sMeasureclassname;
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues($measure_nm)
    {
        $measure_tablename = $this->m_oSREContext->getMeasureTablename();
        
        $myvalues = array();
        $myvalues['measure_nm'] = $measure_nm;
        $myvalues = db_select($measure_tablename, 'n')
          ->fields('n')
          ->condition('measure_nm', $measure_nm,'=')
          ->execute()
          ->fetchAssoc();

        return $myvalues;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        $aOptions = array();
        return $aOptions;
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, &$myvalues, $formMode)
    {
        $bGood = TRUE;

        $measure_tablename = $this->m_oSREContext->getMeasureTablename();
        if(trim($myvalues['measure_nm']) == '')
        {
            form_set_error('measure_nm','The measure name cannot be empty');
            $bGood = FALSE;
        } else {
            if($formMode == 'A')
            {
                //Check for duplicate keys too
                $result = db_select($measure_tablename,'p')
                    ->fields('p')
                    ->condition('measure_nm', $myvalues['measure_nm'],'=')
                    ->execute();
                if($result->rowCount() > 0)
                {
                    form_set_error('measure_nm', 'Already have a measure with this name');
                    $bGood = FALSE;
                }
            }
        }

        $loaded = module_load_include('inc','simplerulesengine_core','core/SREngine');
        if(!$loaded)
        {
            $msg = 'Failed to load the SREngine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        
        //Does the formula look good?
        $expression = trim($myvalues['criteria_tx']);
        if($expression == '')
        {
            form_set_error('criteria_tx', 'Compute formula cannot be empty');
            $bGood = FALSE;
        } else {
            try
            {
                $oCIE = $this->m_oSREngine;
                $aDisallowedVariables = array($myvalues['measure_nm']);    //Do not allow self reference
                $aResults = $oCIE->validateMeasureExpression($expression, $aDisallowedVariables);
                $bGood = $aResults['isokay'];
                if(!$bGood)
                {
                    $errors = implode('<li>', $aResults['errors']);
                    form_set_error('criteria_tx', t('Problem with the Compute Formula').':<ol><li>' . $errors . '</ol>');
                    $bGood = FALSE;
                }
            } catch (\Exception $ex) {
                form_set_error('criteria_tx', 'Compute formula has error: '.$ex->getMessage());
                $bGood = FALSE;
            }
        }
        
        //Done with all validations.
        return $bGood;
    }
    
    /**
     * Get all the form contents for rendering
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            $html_classname_overrides = array();
        }
        if(!isset($html_classname_overrides['data-entry-area1']))
        {
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        }
        if(!isset($html_classname_overrides['selectable-text']))
        {
            $html_classname_overrides['selectable-text'] = 'selectable-text';
        }
        $aOptions = $this->getAllOptions();
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );        
        if(isset($myvalues['category_nm']))
        {
            $category_nm = $myvalues['category_nm'];
        } else {
            $category_nm = '';
        }
        if(isset($myvalues['measure_nm']))
        {
            $measure_nm = $myvalues['measure_nm'];
        } else {
            $measure_nm = '';
        }
        if($myvalues['readonly_yn'] == 1)
        {
            //Make sure someone is not trying to cheat.
            if(strpos('ED',$formType) !== FALSE)
            {
                throw new \Exception('Tried change locked '.$myvalues['measure_nm'].' rule!');
            }
        }

        $form["data_entry_area1"]['category_nm'] = array(
            '#type' => 'textfield',
            '#title' => t('Category Name'),
            '#default_value' => $category_nm,
            '#size' => 20,
            '#maxlength' => 20,
            '#required' => TRUE,
            '#description' => t('A label for grouping measures together'),
            '#disabled' => $disabled
        );

        $showfieldname_version = 'version';
        $showfieldname_measure_nm = 'measure_nm';
        $disable_measure_nm = $disabled;   //Default behavior
        $disabled_version = $disabled;  //Default behavior
        if($disabled || $formType == 'E' || $formType == 'A')
        {
            //Hidden values for key fields
            if($formType == 'E')
            {
                $form['hiddenthings']['measure_nm'] 
                    = array('#type' => 'hidden', '#value' => $measure_nm, '#disabled' => FALSE);        
                $showfieldname_measure_nm = 'show_measure_nm';
                $disable_measure_nm = TRUE;
                $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
                $form['hiddenthings']['version'] 
                    = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);        
                $showfieldname_version = 'show_version';
            } else
            if($formType == 'A')
            {
                $newversionnumber = 1;
                $form['hiddenthings']['version'] 
                    = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);        
                $showfieldname_version = 'show_version';
                $myvalues['version'] = $newversionnumber;
            }
            $disabled_version = TRUE;
        }
    
        $form["data_entry_area1"][$showfieldname_measure_nm]     = array(
            '#type' => 'textfield',
            '#title' => t('Measure Name'),
            '#default_value' => $measure_nm,
            '#size' => 40,
            '#maxlength' => 40,
            '#required' => TRUE,
            '#description' => t('The unique name for this measure'),
            '#disabled' => $disable_measure_nm
        );
        $form["data_entry_area1"][$showfieldname_version]     = array(
            '#type' => 'textfield',
            '#title' => t('Version Number'),
            '#default_value' => $myvalues['version'],
            '#size' => 4,
            '#maxlength' => 4,
            '#required' => TRUE,
            '#description' => t('Version number of this measure metadata'),
            '#disabled' => $disabled_version
        );
        $form["data_entry_area1"]['return_type']       = array(
            '#type' => 'textfield',
            '#title' => t('Return Type'),
            '#default_value' => $myvalues['return_type'],
            '#size' => 20,
            '#maxlength' => 20,
            '#required' => TRUE,
            '#description' => t('The data type of the value returned by this measure'),
            '#disabled' => $disabled
        );
        $form["data_entry_area1"]['purpose_tx']       = array(
            '#type' => 'textarea',
            '#title' => t('Purpose'),
            '#default_value' => $myvalues['purpose_tx'],
            '#size' => 80,
            '#maxlength' => 1024,
            '#required' => TRUE,
            '#description' => t('Static text describing purpose of this measure'),
            '#disabled' => $disabled
        );
        

        $form["data_entry_area1"]['trigger_crit_section'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Compute Formula Expression Section'),
            '#description' => t('A measure can compute a value through the use of an expression or simply serve as a named variable for runtime value INPUT by user or systems.'  
                    ),
            '#attributes' => array(
                'class' => array($html_classname_overrides['data-entry-area1'])
             ),
            '#disabled' => $disabled,
            '#tree' => FALSE,
        );     
        
        if($formType == 'A' || $formType == 'E')
        {
            //Allow the user to see all the existing flags right in the form.
            $form["data_entry_area1"]['trigger_crit_section']['flags'] = array(
                '#type' => 'fieldset', 
                '#title' => t('Existing Measures'),
                '#collapsible' => TRUE, 
                '#collapsed' => FALSE, 
            );
            
            $oSREC = $this->m_oSREngine->getSREContext();
            $oDict = $oSREC->getDictionary();
            $aAllRuleInputs = $oDict->getActiveRuleInputs();
            $sFlagsMarkup = '';
            $aInputMap = array();
            foreach($aAllRuleInputs as $aFlagInfo)
            {
                $sName = $aFlagInfo['name'];
                if($sName != $measure_nm)
                {
                    $sCategory = $aFlagInfo['category_nm'];
                    $sName = $aFlagInfo['name'];
                    $sPurpose = $aFlagInfo['purpose_tx'];
                    $myid = $aFlagInfo['name'];
                    $sMarkup = ' <span id="'.$myid.'" draggable="false" class="dragtarget '.$html_classname_overrides['selectable-text'].'" '
                            . ' title="'.$sPurpose.'">'.$sName.'</span> ';
                    $aInputMap[$sCategory][] = $sMarkup;
                }
            }

            $sFlagsMarkup = '';
            foreach($aInputMap as $key=>$value)
            {
                $sFlagsMarkup .= '<h2 class="rule-input-category-heading">'.$key.'</h2>';
                $sFlagsMarkup .= '<div class="rule-input-items">'.implode(' , ', $value).'</div>';
            }

            $form["data_entry_area1"]['trigger_crit_section']['flags']['content'] = array('#type' => 'item', '#markup' => '<p>'.$sFlagsMarkup.'</p>');
        }

        $sExpression = $myvalues['criteria_tx'];
        if(strlen(trim($sExpression)) !== 0)
        {
            try
            {
                $oCIE = $this->m_oSREngine;
                $aDisallowedVariables = array($myvalues['measure_nm']);    //Do not allow self reference
                $aResults = $oCIE->validateMeasureExpression($sExpression, $aDisallowedVariables);
                $bGood = $aResults['isokay'];
                if(!$bGood)
                {
                    $errors = implode('<li>', $aResults['errors']);
                    throw new \Exception(t('Problem with the Compute Formula').':<ol><li>' . $errors . '</ol>');
                }
            } catch (\Exception $ex) {
                throw new \Exception(t('Problem with the Compute Formula: ') . $ex->getMessage());
            }
        }
        $form["data_entry_area1"]['trigger_crit_section']['criteria_tx']       = array(
            '#type' => 'textarea',
            '#title' => t('Compute Formula'),
            '#default_value' => $myvalues['criteria_tx'],
            '#size' => 80,
            '#maxlength' => 4096,
            '#required' => TRUE,
            '#description' => t('The measure formula or keyword "INPUT" if no formula'),
            '#disabled' => $disabled,
            '#attributes' => array(
                'class' => array('droptarget')),
        );

        $ynoptions                                   = array(
            1 => t('Yes'),
            0 => t('No')
        );
        $form["data_entry_area1"]['readonly_yn']    = array(
            '#type' => 'radios',
            '#title' => t('Locked'),
            '#default_value' => isset($myvalues['readonly_yn']) ? $myvalues['readonly_yn'] : 0,
            '#options' => $ynoptions,
            '#description' => t('Is this measure metadata locked from editing?')
        );
        $form["data_entry_area1"]['active_yn']    = array(
            '#type' => 'radios',
            '#title' => t('Active'),
            '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
            '#options' => $ynoptions,
            '#description' => t('Yes if rule is active, else no.')
        );
        
        return $form;
    }
}
