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
class ViewContraindicationPage
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
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('V',$form, $form_state, $disabled, $myvalues);
        
        //These buttons should not be in the helper..... TODO move them to the pages
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array(
            '#type' => 'item',
            '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageContraindications">'
        );
        return $form;
    }
}
