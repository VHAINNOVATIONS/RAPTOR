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
class DiagnosticPage1
{
    public function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_ewdvista', 'core/load_all_modules');
        module_load_include('php', 'raptor_ewdvista', 'core/Diagnostic');
        module_load_include('php', 'raptor_ewdvista', 'core/Encryption');
        module_load_include('php', 'raptor_ewdvista', 'core/WebServices');
    }

    /**
     * Return the initial field values for the page
     */
    public function getFieldValues($form=NULL, $myvalues=array())
    {
        $myvalues['getfieldvalues_timestamp']=time();
        $myvalues['action'] = 'INIT';
        $myvalues['valid_actions'] = strtoupper('CREATE,INIT,GETCREDENTIALS,LOGIN,GETWORKLIST,getNotesDetailMap,GETVISITS,GETCHEMHEMLABS');
        return $myvalues;
    }
    
    private function arrayItemHasValue($myvalues,$itemname)
    {
        return isset($myvalues[$itemname]) && trim($myvalues[$itemname]) != '';
    }
    
    /**
     * Validate the provided values
     */
    function looksValid($form, $myvalues)
    {
        $isvalid = TRUE;
        $action = trim(isset($myvalues['formaction']) ? $myvalues['formaction'] : trim($myvalues['action']));
        if($action == '')
        {
            form_set_error('formaction',"Missing action");
            $isvalid = FALSE;
        } else {
            $valid_actions_ar = explode(',',$myvalues['valid_actions']);
            if(!in_array($action,$valid_actions_ar))
            {
                form_set_error('formaction',"Invalid action '$action' provided!");
                $isvalid = FALSE;
            }
            if($action == 'GETCREDENTIALS') 
            {
                $itemnames = array('username','password','key');
                foreach($itemnames as $itemname)
                {
                    if(!$this->arrayItemHasValue($myvalues,$itemname))
                    {
                        form_set_error($itemname,"Missing $itemname for $action");
                        $isvalid = FALSE;
                    }
                }
            } else
            if($action == 'LOGIN') 
            {
                //$itemnames = array('authcode','credentials');
                $itemnames = array('username','password');
                foreach($itemnames as $itemname)
                {
                    if(!$this->arrayItemHasValue($myvalues,$itemname))
                    {
                        form_set_error($itemname,"Missing $itemname for $action");
                        $isvalid = FALSE;
                    }
                }
            }
        }
        return $isvalid;
    }
    
    /**
     * Execute the actions indicated by the values
     */
    public function updateDatabase(&$form_state)
    {
        $oDiagnostic = new \raptor_ewdvista\Diagnostic();
        $oWebServices = new \raptor_ewdvista\WebServices();
        
        $myvalues = $form_state['values'];
        $action = isset($myvalues['formaction']) ? $myvalues['formaction'] : trim($myvalues['action']);
        try
        {
            if($action == 'CREATE')
            {
                drupal_set_message("Try to create dao...");
                $mydao = $oDiagnostic->testCreateDao();
                //$mydao = $this->testCreate();
                drupal_set_message("Success!");
            } else if($action == 'INIT') {
                drupal_set_message("Try to run the init...");
                $mydao = $oDiagnostic->testCreateDao();
                //$mydao = $this->testCreate();
                $mydao->initClient();
                $result = $mydao->getPrivateValue(array('init_key','authorization'));
                drupal_set_message("Success result=".print_r($result,TRUE));
            } else if($action == 'GETCREDENTIALS') {
                drupal_set_message("Try to get the encrypted credentials...");
                $username = $myvalues['username'];
                $password = $myvalues['password'];
                $key = $myvalues['key'];
                $credentials = $this->testEncrypt($username,$password,$key);
                drupal_set_message("Success credentials=[$credentials]");
            } else if($action == 'LOGIN') {
                drupal_set_message("(1) Try to login...");
                $mydao = $oDiagnostic->testCreateDao();//$this->testcreate();
                $mydao->initClient();
                $username = $myvalues['username'];
                $password = $myvalues['password'];
                drupal_set_message("(2) executing login action: $action");
                $oDiagnostic->testLogin($mydao,$username,$password);
                drupal_set_message("(3) isAuthenticated=" . $mydao->isAuthenticated());
             } 
             else if($action == 'GETNOTESDETAILMAP') {
                drupal_set_message("(1) Try to login...");
                $mydao = $oDiagnostic->testCreateDao();//$this->testcreate();
                $mydao->initClient();
                $username = $myvalues['username'];
                $password = $myvalues['password'];
                drupal_set_message("(2) executing login for action: $action");
                $oDiagnostic->testLogin($mydao,$username,$password);
                drupal_set_message("(3) executing: $action");
                $oDiagnostic->testGetNotesDetailMap($mydao);
                drupal_set_message("(3) if we gt here then nothing seems failed, but it is not all");
             }
             else if($action == 'GETWORKLIST') {
                drupal_set_message("Try to to get worklist from ...");
                $method = 'initiate';
                $url="http://localhost:8081/RaptorEwdVista/raptor/initiate";
                //$method="emrservice";
                //$url="http://localhost/mdws2.5/emrsvc.asmx";
                drupal_set_message("Testing $action with callAPI($method, $url)");
                $result = $oWebServices->callAPI($method, $url);
                drupal_set_message("TODO $action".print_r($result,TRUE));
            } 
            else if($action == 'GETVISITS') {
                drupal_set_message("(1) Try to login...");
                $mydao = $oDiagnostic->testCreateDao();//$this->testcreate();
                //$mydao->initClient();
                $username = $myvalues['username'];
                $password = $myvalues['password'];
                drupal_set_message("(2) executing login for action: $action");
                $oDiagnostic->testLogin($mydao,$username,$password);
                $mydao->setPatientID(237);
                $patientId = $mydao->getSelectedPatientID();
                drupal_set_message("(3) executing: $action for patientId: $patientId ");
                $result = $oDiagnostic->testGetVisits($mydao);
                drupal_set_message("(4) result: ".print_r($result,TRUE));
                drupal_set_message("(5) if we got here then nothing seems failed, but it is not all");
            }
            else if($action == 'GETCHEMHEMLABS') {
                $v = RAPTOR_CONFIG_ID;
                drupal_set_message("(1) Try to login... CONFIG: $v");
                $username = $myvalues['username'];
                $password = $myvalues['password'];
                drupal_set_message("(2) executing login for action: $action");
                
                $mydao = $oDiagnostic->testCreateDao();//$this->testcreate();
                
                $oDiagnostic->testLogin($mydao,$username,$password);
                
                $mydao->setPatientID(237);
                $patientId = $mydao->getSelectedPatientID();
                drupal_set_message("(3) executing: $action for patientId: $patientId ");
                
                $result = $oDiagnostic->testGetChemHemLabs($mydao);
                
                drupal_set_message("(4) result: ".print_r($result,TRUE));
                drupal_set_message("(5) if we got here then nothing seems failed, but it is not all");
            }
            else {
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
        
        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userinfomarkup = "<p>Logged in as ".$userinfo->getFullName()."</p>";
        $form["data_entry_area1"]['topinfo']['user'] 
                = array('#type' => 'item',
                        '#markup' => $userinfomarkup,
                    );

        /*
        $rawvaluesinfo = "<fieldset><p>Raw Input Values are ".print_r($myvalues,TRUE).'</p></fieldset>';
        $form["data_entry_area1"]['topinfo']['rawvalues'] 
                = array('#type' => 'item',
                        '#markup' => $rawvaluesinfo,
                    );
                    */
        
        $valid_actions = $myvalues['valid_actions'];
        $form['data_entry_area1']['hiddenthings']['valid_actions'] = array(
          '#type' => 'hidden', 
          '#default_value' => $valid_actions, 
        );        
        
        $actiondescription = "Supported actions include the following: <b>$valid_actions</b>";
        $action = isset($myvalues['formaction']) ? $myvalues['formaction'] : trim($myvalues['action']);
        $form['data_entry_area1']['userinput']['formaction'] = array(
          '#type' => 'textfield', 
          '#title' => t('Form Action'), 
          '#default_value' => $action, 
          '#size' => 20, 
          '#disabled' => $disabled,
          '#description' => $actiondescription,
        );        

        $authcode = isset($myvalues['authcode']) ? $myvalues['authcode'] : '';
        $form['data_entry_area1']['userinput']['authcode'] = array(
          '#type' => 'textfield', 
          '#title' => t('Authentication'), 
          '#default_value' => $authcode, 
          '#size' => 80, 
          '#disabled' => $disabled,
          '#description' => 'Get this from INIT response',
        );        

        $key = isset($myvalues['key']) ? $myvalues['key'] : '';
        $form['data_entry_area1']['userinput']['key'] = array(
          '#type' => 'textfield', 
          '#title' => t('Key'), 
          '#default_value' => $key, 
          '#size' => 80, 
          '#disabled' => $disabled,
          '#description' => 'Get this from INIT response',
        );        
        
        $username = isset($myvalues['username']) ? $myvalues['username'] : $userinfo->getUserName();
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
          '#description' => 'Get this from encryption of username, password, and key',
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
    
    /*
    private function testCreate()
    {
        drupal_set_message("Test a new DAO instance");
        $mydao = new \raptor_ewdvista\EwdDao();
        drupal_set_message("Created ".$mydao);
        return $mydao;
    }
    */
    
    private function testEncrypt($username,$password,$key)
    {
        return "TODOENC-{$username}-{$password}-{$key}";
    }
    
}
