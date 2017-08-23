<?php
/**
 * @file
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
module_load_include('php', 'raptor_datalayer', 'core/data_user');
require_once "FormHelper.php";
require_once 'UserPageHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditUserPage
{

    private $m_oContext = NULL;
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
        $this->m_oContext = \raptor\Context::getInstance();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_nUID);
        $myvalues['formmode'] = 'E';
        return $myvalues;
    }

    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase($myvalues)
    {
        if(!isset($myvalues['username']))
        {
            die("Cannot update user record because missing username in array!\n" . var_dump($myvalues));
        }
        if(!isset($myvalues['uid']))
        {
            die("Cannot update user record because missing uid in array!\n" . var_dump($myvalues));
        }
        $updated_dt = date("Y-m-d H:i:s", time());
        if(!isset($myvalues['accountactive_yn']))
        {
            $myvalues['accountactive_yn'] = 1;  //If not set, then assume because user is NOT allowed to disable the account.
        }
        
        //die('CEUA1='.$myvalues['CEUA1'] . 'DUMP ALL...<br>' . print_r($myvalues, true));
        
        $nUpdated = db_update('raptor_user_profile')
                -> fields(array(
                    'role_nm' => $myvalues['role_nm'],
                    'usernametitle' => $myvalues['usernametitle'],
                    'firstname' => $myvalues['firstname'],
                    'lastname' => $myvalues['lastname'],
                    'suffix' => $myvalues['suffix'],
                    'prefemail' => $myvalues['prefemail'],
                    'prefphone' => $myvalues['prefphone'],
                    'accountactive_yn' => $myvalues['accountactive_yn'],
                    'CEUA1' => $myvalues['CEUA1'],
                    'LACE1' => $myvalues['LACE1'],
                    'SWI1' => $myvalues['SWI1'],
                    'PWI1' => $myvalues['PWI1'],
                    'APWI1' => $myvalues['APWI1'],
                    'SUWI1' => $myvalues['SUWI1'],
                    'CE1' => $myvalues['CE1'],
                    'QA1' => $myvalues['QA1'],
                    'SP1' => $myvalues['SP1'],
                    'VREP1' => $myvalues['VREP1'],
                    'VREP2' => $myvalues['VREP2'],
                            
                    'EBO1' => $myvalues['EBO1'],
                    'UNP1' => $myvalues['UNP1'],
                    'REP1' => $myvalues['REP1'],
                    'DRA1' => $myvalues['DRA1'],
                    'ELCO1' => $myvalues['ELCO1'],
                    'ELHO1' => $myvalues['ELHO1'],
                    'ELSO1' => $myvalues['ELSO1'],
                    'ELSVO1' => $myvalues['ELSVO1'],
                    'ELRO1' => $myvalues['ELRO1'],
                    'ECIR1' => $myvalues['ECIR1'],
                    'EECC1' => $myvalues['EECC1'],
                    'EERL1' => $myvalues['EERL1'],
                    'EARM1' => $myvalues['EARM1'],
                    'CUT1' => $myvalues['CUT1'],
                    
                    'updated_dt' => $updated_dt,
                ))
        ->condition('username',$myvalues['username'],'=')
        ->execute();
        if($nUpdated !== 1)
        {
            error_log("Failed to edit user back to database!\n" . var_dump($myvalues));
            die("Failed to edit user back to database!\n" . var_dump($myvalues));
        }

        //Now write all the child records.
        $this->m_oPageHelper->writeChildRecords($myvalues);

        //Returns 1 if everything was okay.
        return $nUpdated;
    }
    
    private function writeKeywords($nUID, $weightgroup, $userpref_keywords, $bSpecialist, $updated_dt)
    {
        $this->m_oPageHelper->writeKeywords($nUID, $weightgroup, $userpref_keywords, $bSpecialist, $updated_dt);
    }
    
    /**
     * @return array of all option values for the form
     */
    private function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }

    private function formatKeywordText($myvalues)
    {
        return $this->m_oPageHelper->formatKeywordText($myvalues);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $oUserInfo = $this->m_oContext->getUserInfo();
        $form = $this->m_oPageHelper->getForm($form, $form_state, $disabled, $myvalues);

        if (!$oUserInfo->isSiteAdministrator()) {
            $form['data_entry_area1']['leftpart']['password'] = NULL;
        }
        $form['data_entry_area1']['leftpart']['role_nm']['#disabled'] = TRUE;

        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#value' => $myvalues['username'], 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The login name of the user.  This must match their VISTA login name.'),
          '#disabled' => TRUE,  //Do NOT let them change the name of an existing user!!!!
        );        
        
        $nSelfUID = $oUserInfo->getUserID();
        if(isset($myvalues['uid']))
        {
            $editself = ($nSelfUID == $myvalues['uid']);
        } else {
            $editself = FALSE;
        }

        if($editself)
        {
            //Just close the dialog
            $cancelbuttonclass = 'raptor-dialog-cancel';
        } else {
            //Go back to management screen.
            $cancelbuttonclass = 'admin-cancel-button';
        }
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save Profile Updates')
                , '#disabled' => $disabled
            );
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="'.$cancelbuttonclass.'" type="button" value="Cancel" data-redirect="/drupal/worklist?manageUsers?dialog=manageUsers">');

        return $form;
    }
}
