<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once("ContraIndicationPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class AddContraIndicationPage
{
    private $m_oContext = null;
    private $m_oPageHelper = null;
    
    //Call same function as in EditUserPage here!
    function __construct()
    {
        $this->m_oContext    = \raptor\Context::getInstance();
        $this->m_oPageHelper = new \raptor\ContraIndicationPageHelper();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();

        //create a NULL value for each of the indexes in the myvalues array.
        $myvalues['rule_nm'] = NULL;
        $myvalues['category_nm'] = NULL;
        $myvalues['version'] = NULL;
        $myvalues['active_yn'] = NULL;
        $myvalues['explanation'] = NULL;
        $myvalues['summary_msg_tx'] = NULL;
        $myvalues['msg_tx'] = NULL;
        $myvalues['req_ack_yn'] = NULL;
        $myvalues['trigger_crit'] = NULL;
        return $myvalues;
    }
    
    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {

      $updated_dt = date("Y-m-d H:i", time());

      try
      {
        db_insert('raptor_contraindication_rule')
          ->fields(array(
                'rule_nm' => $myvalues['rule_nm'],
                'category_nm' => $myvalues['category_nm'],
                'version' => $myvalues['version'],
                'active_yn' => $myvalues['active_yn'],
                'explanation' => $myvalues['explanation'],
                'msg_tx' => $myvalues['msg_tx'],
                'summary_msg_tx' => $myvalues['summary_msg_tx'],
                'req_ack_yn' => $myvalues['req_ack_yn'],
                'trigger_crit' => $myvalues['trigger_crit'],
                'updated_dt' => $updated_dt,
            ))
              ->execute(); 
      }
      catch(\Exception $ex)
      {
            error_log("Failed to add contraindication into database!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            drupal_set_message('Failed to add the new rule because ' . $ex);
            return 0;
      }
      
        //If we are here then we had success.
        drupal_set_message('Added contraindication rule ' . $myvalues['rule_nm']);
        return 1;

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
        $form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues);

        //Replace the buttons
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Add Contraindication Rule'));
 
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageContraindications">');
        
        return $form;
    }
}
