<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");
require_once("UserPageHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class AddUserPage
{
    private $m_oPageHelper = null;
    
     //Call same function as in EditUserPage here!
    function __construct()
    {
        $this->m_oPageHelper = new \raptor\UserPageHelper();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();
        $myvalues['formmode'] = 'A';
        $myvalues['username'] = '';
        $myvalues['password'] = null;
        $myvalues['role_nm'] = null;
        $myvalues['usernametitle'] = null;
        $myvalues['firstname'] = null;
        $myvalues['lastname'] = null;
        $myvalues['suffix'] = null;
        $myvalues['prefemail'] = null;
        $myvalues['prefphone'] = null;
        $myvalues['accountactive_yn'] = 1;

        $myvalues['CEUA1'] = 0;
        $myvalues['LACE1'] = 0;
        $myvalues['SWI1'] = 0;
        $myvalues['PWI1'] = 0;
        $myvalues['APWI1'] = 0;
        $myvalues['SUWI1'] = 0;
        $myvalues['CE1'] = 0;
        $myvalues['QA1'] = 0;
        $myvalues['SP1'] = 0;
        $myvalues['VREP1'] = 0;
        $myvalues['VREP2'] = 0;

        $myvalues['EBO1'] = 0;
        $myvalues['UNP1'] = 0;
        $myvalues['REP1'] = 0;
        $myvalues['DRA1'] = 0;
        $myvalues['ELCO1'] = 0;
        $myvalues['ELHO1'] = 0;
        $myvalues['ELSO1'] = 0;
        $myvalues['ELSVO1'] = 0;
        $myvalues['ELRO1'] = 0;
        $myvalues['EECC1'] = 0;
        $myvalues['ECIR1'] = 0;
        $myvalues['EERL1'] = 0;
        $myvalues['EARM1'] = 0;
        $myvalues['CUT1'] = 0;
        
        $myvalues['userpref_modality'] = array();
        $myvalues['specialist_modality'] = array();

        $myvalues['userpref_keywords1'] = array();
        $myvalues['userpref_keywords2'] = array();
        $myvalues['userpref_keywords3'] = array();
        $myvalues['specialist_keywords1'] = array();
        $myvalues['specialist_keywords2'] = array();
        $myvalues['specialist_keywords3'] = array();
        
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        //TODO - special checks here
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        if(!isset($myvalues['role_nm']) || trim($myvalues['role_nm']) == '')
        {
            error_log("Cannot add user record because missing role_nm in array! " . print_r($myvalues, TRUE));
            form_set_error('role_nm','Missing role name selection.');
            return 0;        }
        if(!isset($myvalues['username']) || trim($myvalues['username']) == '')
        {
            error_log("Cannot add user record because missing username in array! " . print_r($myvalues, TRUE));
            form_set_error('username','Missing username value.');
            return 0;        
        }
        if(!isset($myvalues['password']) || trim($myvalues['password']) == '')
        {
            error_log("Cannot add user record because missing password in array! " . print_r($myvalues, TRUE));
            form_set_error('password','Missing password value.');
            return 0;        
        }
        
        $updated_dt = date("Y-m-d H:i", time());
        
        //Make sure this user does not already exist.
        $result = db_select('raptor_user_profile', 'u')
                ->fields('u')
                ->condition('username',$myvalues['username'],'=')
                ->execute();
        if($result->rowCount() != 0)
        {
            $record = $result->fetchObject();
            form_set_error('username','Already have a RAPTOR user "'. $record->username .'"');
            return 0;
        }
        
        $result = db_select('users', 'u')
                ->fields('u')
                ->condition('name',$myvalues['username'],'=')
                ->execute();
        if($result->rowCount() != 0)
        {
            //Already exists in DRUPAL, use that record.
            error_log('Reusing existing DRUPAL user to create new RAPTOR user named [' . $myvalues['username'] .']');
            $record = $result->fetchObject();
            $newUID = $record->uid;
            $myvalues['uid'] = $newUID;
        } else {
            try
            {
                $newUserInfo = array(
                    'is_new' => TRUE,
                    'name' => $myvalues['username'],
                    'pass' => $myvalues['password'], // note: do not md5 the password
                    'mail' => $myvalues['prefemail'],
                    'status' => 1,
                    'init' => 'email address'
                    );
                $newuser = user_save(null, $newUserInfo, 'RAPTOR_USER');
                if($newuser == FALSE)
                {
                    error_log('Unable to create DRUPAL user ' . print_r($newUserInfo,TRUE));
                    return 0;
                }
            }
            catch(\Exception $e)
            {
                error_log('Failed to add DRUPAL user: ' . $e . "\nDetails..." . print_r($newUserInfo,true));
                form_set_error("username",'Failed to add drupal record for this user!');
                return 0;
            }
            $newUID = $newuser->uid;
            $myvalues['uid'] = $newUID;
        }

        
        $filter = array(':uid' => $newUID);
        $sSQL = 'select username from raptor_user_profile where uid=:uid';
        $result = db_query($sSQL, $filter);
        if($result->rowCount() != 0)
        {
            //Some kind of corruption due to manual interventions in the Drupal or RAPTOR database.
            $record = $result->fetchObject();
            die('Already have a RAPTOR user "'. $record->username .'" with uid=' . $newUID . ' so cannot match the account created for DRUPAL user "'.$myvalues['username'].'"!');
            return 0;
        }
        
        $filter = array(':username' => $myvalues['username']);
        $sSQL = 'select uid from raptor_user_profile where username=:username';
        $result = db_query($sSQL, $filter);
        if($result->rowCount() != 0)
        {
            form_set_error("username",'A system account with username '.$myvalues['username'].' already exists!');
            return 0;
        }

        //TODO -- enforce that the username matches VISTA name unless Site Administrator role!
        //Put that code here!!!

        //Try to create the record now
        try
        {
            
            //Make sure we clear sections that are not shown.
            if(!isset($form['SWI1']))
            {
                //This is is a workaround to prevent Admin user from getting all 1 values (not sure yet why set to 1)
                $myvalues['CEUA1'] = 0;
                $myvalues['LACE1'] = 0;
                $myvalues['SWI1'] = 0;
                $myvalues['PWI1'] = 0;
                $myvalues['APWI1'] = 0;
                $myvalues['SUWI1'] = 0;
                $myvalues['CE1'] = 0;
                $myvalues['QA1'] = 0;
                $myvalues['SP1'] = 0;
                $myvalues['VREP1'] = 0;
                $myvalues['VREP2'] = 0;
            }
            
            //drupal_set_message('For insert we have this>>>'.print_r($myvalues,TRUE));
            $oInsert = db_insert('raptor_user_profile')
                    ->fields(array(
                        'uid' => $newUID,
                        'username' => $myvalues['username'],
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
                    ->execute();
        }
        catch(PDOException $e)
        {
            error_log('Failed to add user: ' . $e . "\nDetails..." . print_r($oInsert,true));
            //die('Failed to add the user record.  Try again later.');
            form_set_error("username",'Failed to add child records for this user!');
            return 0;
        }
        $this->m_oPageHelper->writeChildRecords($myvalues);

        //Returns 1 if everything was okay.
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
        $form = $this->m_oPageHelper->getForm($form, $form_state, $disabled, $myvalues);

        $form['data_entry_area1']['leftpart']['role_nm']['#disabled'] = FALSE;  //Anyone can edit this field in ADD mode!
        
        //Replace the username input
        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The login name of the user.  This must match their VISTA login name.'),
          '#disabled' => FALSE,
          '#attributes' => array('autocomplete' => 'off'),
          '#default_value' => ($myvalues['username'] == NULL ? ' ' : ''.$myvalues['username']), //Not sure why, but had to put space otherwise filled in.
        );     
        
       //Replace the buttons
       $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'), 'id' => 'add-user-create-user')
                , '#value' => t('Create User'));
 
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=manageUsers">');
        
        return $form;
    }
}
