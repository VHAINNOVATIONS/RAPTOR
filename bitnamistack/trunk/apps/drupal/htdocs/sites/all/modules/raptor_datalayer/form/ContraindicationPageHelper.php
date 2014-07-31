<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class ContraindicationPageHelper
{
    
    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
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
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase($myvalues)
    {
        if(!isset($myvalues['rule_nm']))
        {
            die("Cannot update user record because missing rule_nm in array!\n" . var_dump($myvalues));
        }

        //TODO
        die('database update not implemented yet');

        //Returns 1 if everything was okay.
        return $nUpdated;
    }
    
    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        //TODO
        $aOptions = array();
        return $aOptions;
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues, $formMode)
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
        
        $sExpression = $myvalues['trigger_crit'];
        if(strlen(trim($sExpression)) == 0)
        {
            form_set_error('trigger_crit','The trigger criteria cannot be blank');
            $bGood = FALSE;
        } else {
            $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
            if(!$loaded)
            {
                $msg = 'Failed to load the Contraindication Engine';
                throw new \Exception($msg);      //This is fatal, so stop everything now.
            }
            try
            {
                $aDisallowedVariables = array($myvalues['rule_nm']);    //Do not allow self reference
                $aResults = \raptor\ContraIndEngine::validateRuleExpression($sExpression, $aDisallowedVariables);
                $bGood = $aResults['isokay'];
                if(!$bGood)
                {
                    $errors = implode('<li>', $aResults['errors']);
                    form_set_error('trigger_crit','Problem with the trigger criteria:<ol><li>' . $errors . '</ol>');
                }
                //die('LOOK results for expression ['.$sExpression.']>>>' . print_r($aResults, TRUE));
            } catch (Exception $ex) {
                form_set_error('trigger_crit','Problem with the trigger criteria: ' . $ex->getMessage());
                $bGood = FALSE;
            }
        }
        return $bGood;
    }

    /**
     * Get all the form contents for rendering
     * form types are A, E, D, and V
     * @return type renderable array
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues)
    {
        $aOptions = $this->getAllOptions();
        
        $form["data_entry_area1"] = array(
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
            '#default_value' => $myvalues['msg_tx'],
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
            '#title' => t('Explanation text'),
            '#default_value' => $myvalues['explanation'],
            '#size' => 60,
            '#maxlength' => 2048,
            '#required' => TRUE,
            '#description' => t('Detailed explanation about the contraindication'),
            '#disabled' => $disabled
        );

        $form["data_entry_area1"]['trigger_crit'] = array(
            '#type' => 'textarea',
            '#title' => t('Trigger Criteria'),
            '#default_value' => $myvalues['trigger_crit'],
            '#size' => 60,
            '#maxlength' => 2048,
            '#required' => TRUE,
            '#description' => t('The trigger criteria to invoke the contraindication warning'),
            '#disabled' => $disabled
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
}
