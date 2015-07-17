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
    public function getFieldValues($form=NULL, $myvalues=array())
    {
        $myvalues['values_timestamp']=time();
        return $myvalues;
    }
    
    /**
     * Validate the provided values
     */
    function looksValid($form, $myvalues)
    {
        $isvalid = TRUE;
        $action = isset($myvalues['formaction']) ? $myvalues['formaction'] : trim($myvalues['action']);
        if($action == 'LOGIN') 
        {
            if(!isset($myvalues['username']))
            {
                form_set_error('username',"MIssing username for $action");
                $isvalid = FALSE;
            }
            if(!isset($myvalues['password']))
            {
                form_set_error('password',"MIssing password for $action");
                $isvalid = FALSE;
            }
        }
        
        return $isvalid;
    }
    
    /**
     * Execute the actions indicated by the values
     */
    public function updateDatabase($myvalues)
    {
        error_log("Starting updateDatabase at ".time().">>>".print_r($myvalues));
        drupal_set_message("Starting updateDatabase at ".time(),'error');
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
                $username = $myvalues['username'];
                $password = $myvalues['password'];
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
        
        drupal_set_message("LOOK <pre>".print_r($form_state,TRUE)."</pre>");

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

        $action = isset($myvalues['formaction']) ? $myvalues['formaction'] : trim($myvalues['action']);
        $form['data_entry_area1']['userinput']['formaction'] = array(
          '#type' => 'textfield', 
          '#title' => t('Form Action'), 
          '#default_value' => $action, 
          '#size' => 20, 
          '#disabled' => $disabled,
        );        

        $username = isset($myvalues['username']) ? $myvalues['username'] : '01vehu';
        $form['data_entry_area1']['userinput']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Username'), 
          '#default_value' => $username, 
          '#size' => 20, 
          '#disabled' => $disabled,
        );        
        $password = isset($myvalues['password']) ? $myvalues['password'] : '';
        $form['data_entry_area1']['userinput']['password'] = array(
          '#type' => 'textfield', 
          '#title' => t('Password'), 
          '#default_value' => $password, 
          '#size' => 20, 
          '#disabled' => $disabled,
        );        
        
        $credentials = isset($myvalues['authcode']) ? $myvalues['credentials'] : '';
        $form['data_entry_area1']['userinput']['credentials'] = array(
          '#type' => 'textfield', 
          '#title' => t('Credentials'), 
          '#default_value' => $credentials, 
          '#size' => 80, 
          '#disabled' => $disabled,
        );        
        
        $authcode = isset($myvalues['authcode']) ? $myvalues['authcode'] : '';
        $form['data_entry_area1']['userinput']['authcode'] = array(
          '#type' => 'textfield', 
          '#title' => t('Authentication'), 
          '#default_value' => $authcode, 
          '#size' => 80, 
          '#disabled' => $disabled,
        );        

        $key = isset($myvalues['key']) ? $myvalues['key'] : '';
        $form['data_entry_area1']['userinput']['key'] = array(
          '#type' => 'textfield', 
          '#title' => t('Key'), 
          '#default_value' => $key, 
          '#size' => 80, 
          '#disabled' => $disabled,
        );        

        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
       
        $form['data_entry_area1']['action_buttons']['submitpage'] 
                = array('#type' => 'submit'
                , '#value' => t('Submit'));

        $form['data_entry_area1']['action_buttons']['cancel'] 
                = array('#type' => 'item'
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
