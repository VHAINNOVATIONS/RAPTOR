<?php
/**
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

/**
 * This page edits the password of a drupal user.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ChangePasswordPage
{
    
    private $m_oContext = NULL;
    private $m_oUserInfo = NULL;
    
    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oUserInfo = $this->m_oContext->getUserInfo();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = array();
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        
        $username = $this->m_oUserInfo->getUserName();
        $password = $myvalues['currentpass'];
        if(FALSE === user_authenticate($username, $password))
        {
            form_set_error('currentpass','Wrong current password');
            $bGood = FALSE;
        }
        $newpassword = $myvalues['pass'];
        if(strlen($newpassword) < 4)
        {
            form_set_error('pass','New password is TOO short!');
            $bGood = FALSE;
        }
        
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $username = $this->m_oUserInfo->getUserName();
            $userid = $this->m_oUserInfo->getUserID();
            $newpassword = $myvalues['pass'];
            
            $account = user_load($userid);
            $edit = array('pass'=>$newpassword);
            user_save($account, $edit);
            
            drupal_set_message("Password for $username has now been changed!");
        } catch (\Exception $ex) {
            error_log("Change password FAILED for the following account>>>".print_r($account,TRUE));
            throw new \Exception("Failed to change password for $username",99777,$ex);
        }
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['currentpass'] = array(
          '#type' => 'password', 
          '#size' => 25,
          '#title' => t('Current password'),
        );
        
        $form['data_entry_area1']['pass'] = array(
          '#type' => 'password_confirm', 
          '#size' => 25,
        );
        
        $form['data_entry_area1']['action_buttons']['save'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save New Password')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />');        

        return $form;
    }
}
