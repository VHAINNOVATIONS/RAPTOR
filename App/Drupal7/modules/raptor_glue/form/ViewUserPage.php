<?php
/**
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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
require_once ("FormHelper.php");
require_once ("UserPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewUserPage
{
    private $m_oPageHelper = null;
    private $m_nUID = null;
    
     //Call same function as in EditUserPage here!
    function __construct($nUID)
    {
        if(!isset($nUID) || !is_numeric($nUID))
        {
            die("Missing or invalid uid value = " . $nUID);
        }
        $this->m_nUID = $nUID;
        $this->m_oPageHelper = new \raptor\UserPageHelper();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues($nUID)
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_nUID);
        $myvalues['formmode'] = 'V';
        return $myvalues;
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
        $form = $this->m_oPageHelper->getForm($form, $form_state, TRUE, $myvalues);

        //Replace the username input
        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#default_value' => $myvalues['username'], 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The login name of the user.  This must match their VISTA login name.'),
          '#disabled' => TRUE,
        );        
        
        //Replace the buttons
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form["data_entry_area1"]['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageUsers">');
        
        return $form;
    }
}
