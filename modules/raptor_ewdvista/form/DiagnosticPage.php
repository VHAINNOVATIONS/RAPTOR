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

namespace raptor_ewdvista;

/**
 * This page is for diagnostic actions
 *
 * @author Frank Font of SAN Business Consultants
 */
class DiagnosticPage
{

    public function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_ewdvista', 'core/load_all_modules');
    }

    /**
     * Return the initial field values for the page
     */
    public function getFieldValues()
    {
        return array();
    }
    
    /**
     * Validate the provided values
     */
    function looksValid($myvalues)
    {
        //Add value validations here and return TRUE if okay
        return TRUE;
    }
    
    /**
     * Execute the actions indicated by the values
     */
    function updateDatabase($myvalues)
    {
        throw new \Exception("Not implemented!");
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-admin raptor-dialog'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area1']['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();

        $userinfomarkup = "<p>Logged in as ".$userinfo->getFullName()."</p>";
        $form["data_entry_area1"]['topinfo']['user'] 
                = array('#type' => 'item',
                        '#markup' => $userinfomarkup,
                    );

        $rawvaluesinfo = "<fieldset><p>Raw Input Values are ".print_r($myvalues,TRUE).'</p></fieldset>';
        $form["data_entry_area1"]['topinfo']['rawvalues'] 
                = array('#type' => 'item',
                        '#markup' => $rawvaluesinfo,
                    );
        
        $action = trim($myvalues['action']);
        try
        {
            if($action == 'CREATE')
            {
                drupal_set_message("Try to create dao...");
                $this->testcreate();
                drupal_set_message("Success!");
            } else if($action == 'INIT') {
                drupal_set_message("Try to run the init...");
                $mydao = $this->testcreate();
                $mydao->initClient();
                drupal_set_message("Success!");
            } else if($action == 'LOGIN') {
                drupal_set_message("Try to login...");
                drupal_set_message("TODO $action");
            } else if($action == 'GETWORKLIST') {
                drupal_set_message("Try to to get worklist...");
                drupal_set_message("TODO $action");
            } else {
                drupal_set_message("No action parameter value was provided",'warn');
            }
        } catch (\Exception $ex) {
            drupal_set_message("Failed for action '$action' because ".$ex,'error');
        }
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['submitpage'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'admin-action-submit')
                , '#value' => t('Submit'));

        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit" />');        

        return $form;
    }
    
    private function testcreate()
    {
        drupal_set_message("Test a new DAO instance");
        $mydao = new \raptor_ewdvista\EwdDao();
        drupal_set_message("Created ".$mydao);
        return $mydao;
    }
    
}
