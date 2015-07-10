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

namespace raptor;

require_once 'FormHelper.php';
require_once 'UserPageHelper.php';
require_once 'ChildEditBasePage.php';

/**
 * This is a page to add a new user
 *
 * @author Frank Font of SAN Business Consultants
 */
class AddUserPage extends \raptor\ChildEditBasePage
{
    private $m_role_nm = NULL;
    private $m_oPageHelper = null;
    private $m_oContext = NULL;
    
     //Call same function as in EditUserPage here!
    function __construct($role_nm=NULL)
    {
        module_load_include('php', 'raptor_datalayer', 'config/Choices');
        
        if($role_nm == NULL)
        {
            throw new \Exception('Must specify the role name!');
        }
        $this->m_role_nm = $role_nm;
        $this->m_oPageHelper = new \raptor\UserPageHelper();
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oPageHelper->checkAllowedToAddUser($this->m_oContext, $this->m_role_nm);
        
        //Set the default gobackto url now
        global $base_url;
        $this->setGobacktoURL($base_url.'/raptor/manageusers');
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues(NULL);
        $myvalues['formmode'] = 'A';
        
        //Initialize all the rights to the default values!
        $templatevalues = \raptor\UserInfo::getRoleDefaults($this->m_role_nm);
        foreach($templatevalues as $k=>$v)
        {
            if($k !== 'roleid' && $k !== 'enabled_yn' && $k !== 'name')
            {
                $myvalues[$k] = $v;
            }
        }
        
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
        
        if(!isset($myvalues['role_nm']) || trim($myvalues['role_nm']) == '')
        {
            form_set_error('role_nm','Missing role name selection.');
            $bGood = FALSE;        
        }
        if(!isset($myvalues['username']) || trim($myvalues['username']) == '')
        {
            form_set_error('username','Missing username value.');
            $bGood = FALSE;        
        } else {
            //If whitespace gets into the field it creates a NIGHTMARE!!!!!
            if($myvalues['username'] !== trim($myvalues['username']))
            {
                form_set_error('username','Remove whitespace from username '
                        . '[' . $myvalues['username'] . ']');
                $bGood = FALSE;        
            }
        }
        if(isset($myvalues['prefemail']) && trim($myvalues['prefemail']) > ''
                && !valid_email_address($myvalues['prefemail']))
        {
            form_set_error('prefemail','Email address is not valid');
            $bGood = FALSE;        
        }

        $is_site_admin = UserInfo::isRoleSiteAdministrator($this->m_role_nm);
        if($is_site_admin)
        {
            //We require a password value
            if(!isset($myvalues['newpassword']) || trim($myvalues['newpassword']) == '')
            {
                form_set_error('newpassword','Missing password value.');
                $bGood = FALSE;        
            } else {
                $thelen = strlen($myvalues['newpassword']);
                if($thelen < MIN_ADMIN_PASSWORD_LEN)
                {
                    form_set_error('newpassword',"Password length of $thelen is too short! (Minimum length is ".MIN_ADMIN_PASSWORD_LEN.")");
                    $bGood = FALSE;        
                }
            }
        }

        //Perform second level of checks
        if($bGood)
        {
            //Make sure this user does not already exist.
            $result = db_select('raptor_user_profile', 'u')
                    ->fields('u')
                    ->condition('username',$myvalues['username'],'=')
                    ->execute();
            if($result->rowCount() != 0)
            {
                $record = $result->fetchObject();
                form_set_error('username','Already have a RAPTOR user "'. $record->username .'"');
                $bGood = FALSE;        
            }
        }

        if($bGood)
        {
            $bGood = $this->m_oPageHelper->validateModality($myvalues);
        }
        
        //Done with all checks
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        
        $result = NULL;
        try 
        {
            $result = db_select('users', 'u')
                    ->fields('u')
                    ->condition('name',$myvalues['username'],'=')
                    ->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        $is_site_admin = UserInfo::isRoleSiteAdministrator($this->m_role_nm);
        
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
                    'name' => trim($myvalues['username']),
                    'pass' => ($is_site_admin ? $myvalues['newpassword'] : 'VISTAAUTH'), // note: do not md5 the password
                    'mail' => $myvalues['prefemail'],
                    'status' => 1,
                    'init' => 'email address'
                    );
                $newuser = user_save(null, $newUserInfo, 'RAPTOR_USER');
                if($newuser == FALSE)
                {
                    $errmsg = 'Unable to create DRUPAL user ' . print_r($newUserInfo,TRUE);
                    error_log($errmsg);
                    throw new \Exception($errmsg);
                }
            }
            catch(\Exception $e)
            {
                $errmsg = 'Failed to add DRUPAL user: ' . $e . "\nDetails..." . print_r($newUserInfo,true);
                error_log($errmsg);
                throw new \Exception($errmsg,9999,$ex);
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
            $errmsg = ('Already have a RAPTOR user "'. $record->username .'" with uid=' . $newUID . ' so cannot match the account created for DRUPAL user "'.$myvalues['username'].'"!');
            throw new \Exception($errmsg);
        }
        
        $filter = array(':username' => $myvalues['username']);
        $sSQL = 'select uid from raptor_user_profile where username=:username';
        $result = db_query($sSQL, $filter);
        if($result->rowCount() != 0)
        {
            $errmsg = 'A system account with username "'.$myvalues['username'].'" already exists!';
            throw new \Exception($errmsg);
        }

        //TODO -- enforce that the username matches VISTA name unless Site Administrator role!
        //Put that code here!!!

        //Try to create the record now
        try
        {
            
            //Make sure we clear sections that are not shown.
            if(!isset($myvalues['SWI1']))
            {
                //This is is a workaround to prevent Admin user from getting all 1 values (not sure yet why set to 1)
                $myvalues['SWI1'] = 0;
                $myvalues['PWI1'] = 0;
                $myvalues['APWI1'] = 0;
                $myvalues['SUWI1'] = 0;
                $myvalues['CE1'] = 0;
                $myvalues['QA1'] = 0;
                $myvalues['SP1'] = 0;
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
                        'QA2' => $myvalues['QA2'],
                        'QA3' => $myvalues['QA3'],
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
        catch(\Exception $ex)
        {
            $errmsg = ('Failed to add user: ' . print_r($ex,TRUE) 
                    . "\nDetails..." . print_r($oInsert,true));
            //die('Failed to add the user record.  Try again later.');
            throw new \Exception($errmsg);
        }
        $this->m_oPageHelper->writeChildRecords($myvalues);

        //Returns 1 if everything was okay.
        $msg = 'Created user '.$myvalues['username'].' ('.$myvalues['role_nm'].')';
        drupal_set_message($msg);
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
    function getForm($baseform, &$form_state, $disabled, $myvalues_override)
    {
        if(isset($form_state['values']) && is_array($form_state['values']))
        {
            $myvalues = $form_state['values'];
        } else {
            $myvalues = array();
        }
        if(is_array($myvalues_override))
        {
            $myvalues = array_merge($myvalues, $myvalues_override);
        }
        $is_site_admin = UserInfo::isRoleSiteAdministrator($this->m_role_nm);
        
        $form = $this->m_oPageHelper
                ->getForm($baseform, $form_state, $disabled, $myvalues, $this->m_role_nm);
        
        //Replace the username input
        $form['data_entry_area1']['leftpart']['username']['#required'] = TRUE;
        $form['data_entry_area1']['leftpart']['username']['#disabled'] = FALSE;
        $form['data_entry_area1']['leftpart']['username']['#default_value'] 
                = ($myvalues['username'] == NULL ? ' ' : ''.$myvalues['username']); //Not sure why, but had to put space otherwise filled in.
        
        //Replace the intro blurb
        if(!$is_site_admin)
        {
            $form['data_entry_area1']['introblurb']['text'] = array(
                '#markup' => '<p>'
                . 'RAPTOR leverages VISTA for user login authentication so that Standard users can interact with patient data.'
                . 'This integration requires that the login name of a standard user in RAPTOR matches the login name in VISTA.'
                . '</p>'
                . '<p>'
                . 'If a standard user account cannot log into VISTA then they will not be able to log into RAPTOR.'
                . '</p>',
            );        
            //Clear the entire password block.
            $form['data_entry_area1']['leftpart']['password'] = NULL;
        } else {
            $form['data_entry_area1']['introblurb']['text'] = array(
                '#markup' => '<p>Admin users do not interact with VISTA patient data.</p>'
            );        
            //Clear the current password input.
            $form['data_entry_area1']['leftpart']['password']['currentpass'] = NULL;
        }
        
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
 
        global $base_url;
        $goback = $this->getGobacktoFullURL();
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Cancel"'
                . ' data-redirect="'.$goback.'">');
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = FormHelper::getExitButtonMarkup($goback);
        return $form;
    }
}
