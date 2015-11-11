<?php
/**
 * @file
 * ----------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font(ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano(acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  
 * Contact SAN to share updates.
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
 * ----------------------------------------------------------------------------
 * 
 */

namespace simplerulesengine;


/**
 * This class helps create rule forms
 *
 * @author Frank Font of SAN Business Consultants
 */
class RulePageHelper
{
    protected $m_oSREngine = NULL;
    protected $m_oSREContext = NULL;
    protected $m_urls_arr = NULL;
    protected $m_rule_classname = NULL;
    
    public function __construct($oSREngine
            , $urls_arr
            , $rule_classname=NULL)
    {
        $this->m_oSREngine = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr = $urls_arr;
        $this->m_rule_classname = $rule_classname;
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues($rule_nm)
    {
        $rule_tablename = $this->m_oSREContext->getRuleTablename();
        
        $myvalues = array();
        $myvalues['rule_nm'] = $rule_nm;
        $myvalues = db_select($rule_tablename, 'n')
          ->fields('n')
          ->condition('rule_nm', $rule_nm,'=')
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

        $rule_tablename = $this->m_oSREContext->getRuleTablename();
        if(trim($myvalues['rule_nm']) == '')
        {
            form_set_error('rule_nm','The rule name cannot be empty');
            $bGood = FALSE;
        } else {
            if($formMode == 'A')
            {
                //Check for duplicate keys too
                $result = db_select($rule_tablename,'p')
                    ->fields('p')
                    ->condition('rule_nm', $myvalues['rule_nm'],'=')
                    ->execute();
                if($result->rowCount() > 0)
                {
                    form_set_error('rule_nm', 'Already have a rule with this name');
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
        
        //Create consolidated expression.
        $aConsolidation = $this->getConsolidatedExpression($myvalues);
        $sExpression = $aConsolidation['expression'];
        if($aConsolidation['isokay'] != TRUE)
        {
            $bGood = FALSE;
        } else {
            if(strlen($sExpression) == 0)
            {
                $bGood = FALSE;
                form_set_error('trigger_crit_section','The trigger criteria is required.');
            }
        }

        //Done with all validations.
        return $bGood;
    }
    
    /**
     * Consolidate the expression parts into one expression.
     */
    public function getConsolidatedExpression($myvalues)
    {
        try
        {
            $bGood = TRUE;
            $aResult = array();
            $oCIE = $this->m_oSREngine;
            $sExpression = '';
            foreach($myvalues['trigger_crit_section']['trigger_crit_parts'] as $key=>$value)
            {
                if(trim($value) > '')
                {
                    $sFunction = $key . '(' . $value . ')';

                    $aDisallowedVariables = array($myvalues['rule_nm']);    //Do not allow self reference
                    $aResults = $oCIE->validateRuleExpression($sFunction, $aDisallowedVariables);
                    $bPart = $aResults['isokay'];
                    if(!$bPart)
                    {
                        $bGood = FALSE;
                        $errors = implode('<li>', $aResults['errors']);
                        form_set_error("trigger_crit_section][trigger_crit_parts][$key",'Problem with the '.$key.' trigger criteria:<ol><li>' . $errors . '</ol>');
                    }

                    //Create the consolidated expression.
                    if($sExpression > '')
                    {
                        $sExpression .= ' and ';
                    }
                    $sExpression .= $sFunction;
                }
            }
            $aResult['expression'] = $sExpression;
            $aResult['isokay'] = $bGood;
            return $aResult;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get all the form contents for rendering
     * @param letter $formType valid values are A, E, D, and V
     * @return drupal renderable array
     * @throws \Exception
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
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
            if(isset($myvalues['rule_nm']))
            {
                $rule_nm = $myvalues['rule_nm'];
            } else {
                $rule_nm = '';
            }
            if($myvalues['readonly_yn'] == 1)
            {
                //Make sure someone is not trying to cheat.
                if(strpos('ED',$formType) !== FALSE)
                {
                    throw new \Exception('Tried change locked '.$myvalues['rule_nm'].' rule!');
                }
            }

            $form["data_entry_area1"]['category_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Category Name'),
                '#default_value' => $category_nm,
                '#size' => 20,
                '#maxlength' => 20,
                '#required' => TRUE,
                '#description' => t('A label for grouping rules together'),
                '#disabled' => $disabled
            );

            $showfieldname_version = 'version';
            $showfieldname_rule_nm = 'rule_nm';
            $disable_rule_nm = $disabled;   //Default behavior
            $disabled_version = $disabled;  //Default behavior
            if($disabled || $formType == 'E' || $formType == 'A')
            {
                //Hidden values for key fields
                if($formType == 'E')
                {
                    $form['hiddenthings']['rule_nm'] 
                        = array('#type' => 'hidden', '#value' => $rule_nm, '#disabled' => FALSE);        
                    $showfieldname_rule_nm = 'show_rule_nm';
                    $disable_rule_nm = TRUE;
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

            $form["data_entry_area1"][$showfieldname_rule_nm]     = array(
                '#type' => 'textfield',
                '#title' => t('Rule Name'),
                '#default_value' => $rule_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this rule'),
                '#disabled' => $disable_rule_nm
            );
            $form["data_entry_area1"][$showfieldname_version]     = array(
                '#type' => 'textfield',
                '#title' => t('Version Number'),
                '#default_value' => $myvalues['version'],
                '#size' => 4,
                '#maxlength' => 4,
                '#required' => TRUE,
                '#description' => t('The unique name for this rule'),
                '#disabled' => $disabled_version
            );

            $form["data_entry_area1"]['summary_msg_tx']       = array(
                '#type' => 'textfield',
                '#title' => t('Summary User Message'),
                '#default_value' => $myvalues['summary_msg_tx'],
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('A static message to show the user when the warning is detected.'),
                '#disabled' => $disabled
            );

            $form["data_entry_area1"]['msg_tx']       = array(
                '#type' => 'textarea',
                '#title' => t('Full User Message'),
                '#default_value' => $myvalues['msg_tx'],
                '#size' => 60,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t('Full message to show the user'),
                '#disabled' => $disabled
            );

            $form["data_entry_area1"]['explanation'] = array(
                '#type' => 'textarea',
                '#title' => t('Explanation Text'),
                '#default_value' => $myvalues['explanation'],
                '#size' => 60,
                '#maxlength' => 2048,
                '#required' => TRUE,
                '#description' => t('Detailed explanation about the warning'),
                '#disabled' => $disabled
            );

            $form["data_entry_area1"]['trigger_crit_section'] = array(
                '#type'     => 'fieldset',
                '#title'    => t('Trigger Criteria Section'),
                '#description' => t('This rule is triggered (presented to the user at runtime) only when each of the populated trigger criteria subsections below evaluate to the boolean value of "True".  If any subsection evaluates to "False" then the rule will not trigger.'
                        .'<br><br>Contents of the subsections should simply be valid flag names. (A flag is simply a boolean measure.)'  
                        .'<br><br>A subsection is ignored if it is left blank.<br><br>'),
                '#attributes' => array(
                    'class' => array($html_classname_overrides['data-entry-area1'])
                 ),
                '#disabled' => $disabled,
                '#tree' => TRUE,
            );     

            if($formType == 'A' || $formType == 'E')
            {
                //Allow the user to see all the existing flags right in the form.
                $form["data_entry_area1"]['trigger_crit_section']['flags'] = array(
                    '#type' => 'fieldset', 
                    '#title' => t('Existing Flags'),
                    '#collapsible' => TRUE, 
                    '#collapsed' => FALSE, 
                    '#attributes' => array(
                        'class' => array('droptarget')),
                );

                $oSREC = $this->m_oSREngine->getSREContext();
                $oDict = $oSREC->getDictionary();
                $aAllRuleInputs = $oDict->getActiveRuleInputs();
                $sFlagsMarkup = '';
                $aInputMap = array();
                foreach($aAllRuleInputs as $aFlagInfo)
                {
                    if($aFlagInfo['return'] == 'boolean')
                    {
                        $sCategory = $aFlagInfo['category_nm'];
                        $sName = $aFlagInfo['name'];
                        $sPurpose = $aFlagInfo['purpose_tx'];
                        $myid = $aFlagInfo['name'];
                        $sMarkup = ' <span id="'.$myid.'" draggable="true" class="dragtarget '.$html_classname_overrides['selectable-text'].'" '
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

            $sExpression = $myvalues['trigger_crit'];
            $aTopFunctions['AnyFlagTrue'] = '';
            $aTopFunctions['AllFlagsTrue'] = '';
            $aTopFunctions['AllFlagsFalse'] = '';
            $aTopFunctions['AllFlagsNull'] = '';
            if(strlen(trim($sExpression)) !== 0)
            {
                try
                {
                    $oCIE = $this->m_oSREngine;
                    $aDisallowedVariables = array($myvalues['rule_nm']);    //Do not allow self reference
                    $aResults = $oCIE->validateRuleExpression($sExpression, $aDisallowedVariables);
                    $bGood = $aResults['isokay'];
                    if(!$bGood)
                    {
                        $errors = implode('<li>', $aResults['errors']);
                        throw new \Exception(t('Problem with the trigger criteria').':<ol><li>' . $errors . '</ol>');
                    }
                } catch (\Exception $ex) {
                    throw new \Exception(t('Problem with the trigger criteria: ') . $ex->getMessage());
                }
                $a = $aResults['functions'];
                if(count($a) == 0 && trim($sExpression)>0)
                {
                    drupal_set_message(t('Trouble did NOT find any functions in expression '.$sExpression),'error');
                }
                foreach($a as $key=>$value)
                {
                    $aTopFunctions[$key] = $value;
                }
            }
            foreach($aTopFunctions as $key=>$value)
            {

                if($key == 'AnyFlagTrue')
                {
                    $describe = 'The '.$key.' subsection triggers when any of its flags evaluate as True regardless of the value for the rest of the flags.  Separate each flag name with a comma.';
                } else
                if($key == 'AllFlagsTrue')
                {
                    $describe = 'The '.$key.' subsection triggers only when all its flags evaluate as True.  Separate each flag name with a comma.';
                } else
                if($key == 'AllFlagsFalse')
                {
                    $describe = 'The '.$key.' subsection triggers only when all its flags evaluate as False.';
                } else
                if($key == 'AllFlagsNull')
                {
                    $describe = 'The '.$key.' subsection triggers only when all its flags evaluate as Null.  A Null value occurs when a key data value for a flag is unknown or is NOT provided to the rules engine.';
                } else {
                    $describe = '';    
                }
                $aParamNames = array();
                if(is_array($value))
                {
                    foreach($value as $oParam)
                    {
                        $aParamNames[] = $oParam->getName();
                    }
                }
                $form["data_entry_area1"]['trigger_crit_section']['trigger_crit_parts'][$key] = array(
                    '#type' => 'textarea',
                    '#title' => t($key . ' Trigger Criteria Subsection'),
                    '#default_value' => implode(' , ',$aParamNames),
                    '#size' => 60,
                    '#maxlength' => 2048,
                    '#required' => FALSE,
                    '#description' => t($describe),
                    '#disabled' => $disabled,
                    '#attributes' => array(
                        'class' => array('droptarget')),
                );
            }

            $form["data_entry_area1"]['trigger_crit'] = array(
                '#type' => 'hidden',
                '#title' => t('Consolidated Trigger Criteria'),
                '#default_value' => $myvalues['trigger_crit'],
                '#size' => 60,
                '#maxlength' => 2048,
                '#description' => t('DEBUGGING AREA FOR PROGRAMMER The trigger criteria to invoke the warning'),
            );

            $ynoptions                                   = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form["data_entry_area1"]['req_ack_yn']    = array(
                '#type' => 'radios',
                '#title' => t('Require Acknowledgement'),
                '#default_value' => isset($myvalues['req_ack_yn']) ? $myvalues['req_ack_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Does the user need to acknowledge this warning?')
            );
            $form["data_entry_area1"]['active_yn']    = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if rule is active, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
