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
class DeleteContraindicationPage
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
     * Remove the record IF there are no records referencing this user.
     */
    function updateDatabase($form, $myvalues)
    {
        $rule_nm = $myvalues['rule_nm'];
        $num_deleted = db_delete('raptor_contraindication_rule')
          ->condition('rule_nm', $rule_nm)
          ->execute();            

        //Success?  
        if($num_deleted == 1)
        {
            $feedback = 'The '.$rule_nm.' contraindication has been succesfully deleted.';
            drupal_set_message($feedback);
            return 1;
        } 

        //We are here because we failed.
        $feedback = 'Trouble deleting '.$rule_nm.' contraindication!';
        error_log($feedback . ' delete reported ' . $num_deleted);
        drupal_set_message($feedback, 'warning');
        return 0;
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
        $disabled = TRUE;
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, $disabled, $myvalues);

        //Replace the buttons
        $form["data_entry_area1"]['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Delete Contraindication Rule From System')
                , '#disabled' => FALSE
                );

        $form["data_entry_area1"]['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageContraindications">');

        return $form;
    }
}
