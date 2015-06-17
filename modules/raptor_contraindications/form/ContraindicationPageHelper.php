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

require_once (RAPTOR_CI_MODULE_PATH . '/core/ContraIndEngine.inc');

/**
 * This class helps with forms
 *
 * @author Frank Font of SAN Business Consultants
 */
class ContraindicationPageHelper
{
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($rule_nm)
    {
        $myvalues = array();
        $myvalues['rule_nm'] = $rule_nm;
        $filter = array(":rule_nm" => $rule_nm);
    
        //https://api.drupal.org/api/drupal/includes!database!database.inc/function/db_select/7
        $myvalues = db_select('raptor_contraindication_rule', 'n')
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

        if(trim($myvalues['rule_nm']) == '')
        {
            form_set_error('rule_nm','The rule name cannot be empty');
            $bGood = FALSE;
        } else {
            if($formMode == 'A')
            {
                //Check for duplicate keys too
                $result = db_select('raptor_contraindication_rule','p')
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

        $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindication Engine';
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
    
    public function getConsolidatedExpression($myvalues)
    {
        $bGood = TRUE;
        $aResult = array();
        $oCIE = \raptor\ContraIndEngine(NULL);
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
    }

    /**
     * Get all the form contents for rendering
     * form types are A, E, D, and V
     * @return type renderable array
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues)
    {
        $aOptions = $this->getAllOptions();

        //TODO - declare a non user class!!!!
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
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
            '#description' => t('A static message to show the user when the contraindication is detected.'),
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
            '#description' => t('Detailed explanation about the contraindication'),
            '#disabled' => $disabled
        );

//xxxxxxxxxxxxxx        

        $form["data_entry_area1"]['trigger_crit_section'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Trigger Criteria Section'),
            '#description' => t('This rule is triggered (presented to the user at runtime) only when each of the populated trigger criteria subsections below evaluate to the boolean value of "True".  If any subsection evaluates to "False" then the rule will not trigger.'
                    .'<br><br>Contents of the subsections should simply be valid flag names.'  
                    .'<br><br>A subsection is ignored if it is left blank.<br><br>'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
            '#tree' => TRUE,
        );        
        $sExpression = $myvalues['trigger_crit'];
        $aTopFunctions['AnyFlagTrue'] = '';
        $aTopFunctions['AllFlagsTrue'] = '';
        $aTopFunctions['AllFlagsFalse'] = '';
        $aTopFunctions['AllFlagsNull'] = '';
        if(strlen(trim($sExpression)) !== 0)
        {
            $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
            if(!$loaded)
            {
                $msg = 'Failed to load the Contraindication Engine';
                throw new \Exception($msg);      //This is fatal, so stop everything now.
            }
            try
            {
                $oCIE = new \raptor\ContraIndEngine(NULL);
                $aDisallowedVariables = array($myvalues['rule_nm']);    //Do not allow self reference
                $aResults = $oCIE->validateRuleExpression($sExpression, $aDisallowedVariables);
                $bGood = $aResults['isokay'];
                if(!$bGood)
                {
                    $errors = implode('<li>', $aResults['errors']);
                    throw new \Exception('Problem with the trigger criteria:<ol><li>' . $errors . '</ol>');
                }
                //die('LOOK results for expression ['.$sExpression.']>>>' . print_r($aResults, TRUE));
            } catch (\Exception $ex) {
                throw new \Exception('Problem with the trigger criteria: ' . $ex->getMessage());
            }
            $a = $aResults['functions'];
            if(count($a) == 0 && trim($sExpression)>0)
            {
                drupal_set_message('Trouble did NOT find any functions in expression '.$sExpression,'error');
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
                $describe = 'The '.$key.' subsection triggers only when all its flags evaluate as Null.  A Null value occurs when a key data value for a flag is unknown or provided to the contraindicaiton engine.';
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
                '#disabled' => $disabled
            );
        }
 
        $form["data_entry_area1"]['trigger_crit'] = array(
            '#type' => 'hidden',
            '#title' => t('Consolidated Trigger Criteria'),
            '#default_value' => $myvalues['trigger_crit'],
            '#size' => 60,
            '#maxlength' => 2048,
            '#description' => t('DEBUGGING AREA FOR PROGRAMMER The trigger criteria to invoke the contraindication warning'),
        );

//xxxxxxxxxxxxxxxxxx
        
        $ynoptions                                   = array(
            1 => t('Yes'),
            0 => t('No')
        );
        $form["data_entry_area1"]['req_ack_yn']    = array(
            '#type' => 'radios',
            '#title' => t('Require Acknowledgement'),
            '#default_value' => isset($myvalues['req_ack_yn']) ? $myvalues['req_ack_yn'] : 1,
            '#options' => $ynoptions,
            '#description' => t('Does the user need to acknowledge this contraindication?')
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
    
    public static function getExitButtonMarkup($goback='',$label='Exit')
    {
        if($goback == '')
        {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="#">'.$label.'</a>');
        } else {
            $markup = array('#type' => 'item'
                    , '#markup' => '<a class="admin-cancel-button" href="'.$goback.'">'.$label.'</a>');
        }
        return $markup;
    }
}
