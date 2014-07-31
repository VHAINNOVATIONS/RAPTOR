<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once("ContraindicationPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class EditContraindicationPage
{
    private $m_oContext = null;
    private $m_oPageHelper = null;
    private $m_rule_nm = null;
    
    //Call same function as in EditUserPage here!
    function __construct($rule_nm)
    {
        if (!isset($rule_nm) || is_numeric($rule_nm)) {
            die("Missing or invalid rule_nm value = " . $rule_nm);
        }
        $this->m_oContext    = \raptor\Context::getInstance();
        $this->m_rule_nm     = $rule_nm;
        $this->m_oPageHelper = new \raptor\ContraIndicationPageHelper();
    }
    
    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_rule_nm);
    }
    
    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues, 'E');
    }
    
    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase($form, $myvalues)
    {
        //TODO --- put all the save code here...
        //Try to create the record now
        
        $nSiteID = $this->m_oContext->getSiteID();
        $nUID = $this->m_oContext->getUID();
        $updated_dt = date("Y-m-d H:i", time());
        $nUpdated = db_update('raptor_contraindication_rule')->fields(array(
            'category_nm' => $myvalues['category_nm'],
            'version' => $myvalues['version'],
            'explanation' => $myvalues['explanation'],
            'summary_msg_tx' => $myvalues['summary_msg_tx'],
            'msg_tx' => $myvalues['msg_tx'],
            'req_ack_yn' => $myvalues['req_ack_yn'],
            'active_yn' => $myvalues['active_yn'],
            'trigger_crit' => $myvalues['trigger_crit'],
            'updated_dt' => $updated_dt,
            
        ))
       ->condition('rule_nm', $myvalues['rule_nm'],'=')
       ->execute(); 
        if ($nUpdated !== 1) {
            
            error_log("Failed to edit user back to database!\n" . var_dump($myvalues));
            drupal_set_message('Updated ' . $nUpdated . ' records instead of 1!');
            return 0;
            //die("Failed to edit user back to database!\n>>>>" . $nUpdated . '>>>>' . var_dump($myvalues));
            
        }
        //die('database update not implemented yet');
        
        //Returns 1 if everything was okay.
        drupal_set_message('Saved update for ' . $myvalues['rule_nm']);
        return $nUpdated;
    }
    
    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $disabled = FALSE; //They can edit the fields.
        
        $form = $this->m_oPageHelper->getForm('E',$form, $form_state, $disabled, $myvalues);
        

        $rule_nm = $myvalues['rule_nm'];

        //Hidden values for key fields
        $form['hiddenthings']['rule_nm'] 
            = array('#type' => 'hidden', '#value' => $rule_nm, '#disabled' => FALSE);        
        $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
        $form['hiddenthings']['version'] 
            = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);        
        $showfieldname = 'show_rule_nm';


        //Do NOT let the user edit these values...
        $form["data_entry_area1"][$showfieldname]     = array(
            '#type' => 'textfield',
            '#title' => t('Rule Name'),
            '#value' => $rule_nm,
            '#size' => 40,
            '#maxlength' => 40,
            '#required' => TRUE,
            '#description' => t('Must be unique'),
            '#disabled' => TRUE
        );
        $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
        $form["data_entry_area1"]['show_version']     = array(
            '#type' => 'textfield',
            '#title' => t('Version Number'),
            '#value' => $newversionnumber,
            '#size' => 4,
            '#maxlength' => 4,
            '#required' => TRUE,
            '#description' => t('Increases each time change is saved'),
            '#disabled' => TRUE
        );

        
        
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array(
                    'admin-action-button'
                )
            ),
            '#value' => t('Save Rule Updates'),
            '#disabled' => FALSE
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array(
            '#type' => 'item',
            '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageContraindications">'
        );
        
        return $form;
    }
}
