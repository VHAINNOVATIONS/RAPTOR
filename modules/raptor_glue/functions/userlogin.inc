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
 * These are user authentication and management related global functions. 
 * All the functions in this file are loaded every time.
 */

function raptor_glue_user_logout($account)
{
    $oContext = \raptor\Context::getInstance(TRUE);
    $oContext->logoutSubsystems();
    drupal_set_message(t('Logout completed.'));
}

function raptor_glue_form_user_login_alter( &$form, $form_state )
{
    $form['#attributes']['autocomplete'] = 'off';
    $form['values']['name']['#attributes']['autocomplete'] = 'off';
    $form['values']['pass']['#attributes']['autocomplete'] = 'off';
    $form['#validate'] = array( 'user_login_name_validate'
        , 'user_login_authenticate_validate'
        , 'raptor_glue_login_validate'
        , 'user_login_final_validate' );
}

function raptor_glue_login_validate( $form, &$form_state )
{
    $username = trim($form_state['values']['name']);
    $password = trim($form_state['values']['pass']);
    $havepassword = (trim($password) != '' ? 'Yes' : 'No!');
    error_log("login validating username=[$username] password was provided=[$havepassword]");
    $fatalerrmsg = null;
    $isvalid = raptor_glue_check_user( $username, $password, $fatalerrmsg );
    if ($isvalid === TRUE )
    {
        error_log("login validated username=[$username] Is valid user");
        $candidateuser = user_load_by_name($username);
        if( $candidateuser == null || $candidateuser === FALSE )
        {
            $message = 'Did NOT find account information for username=['.$username.']';
            drupal_set_message($message,'warning');
            error_log($message);
        } else {
            $form_state['uid'] = $candidateuser->uid;
            //Clear any left-over ticket locks this user may have created in a prior session
            module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
            $oTT = new \raptor\TicketTrackingData();
            $nSiteID = VISTA_SITE;
            $oTT->deleteAllStaleTicketLocks($nSiteID);
            $oTT->deleteAllUserTicketLocks($candidateuser->uid,'Cleared as part of login for '.$candidateuser->uid);
            user_login_submit($form, $form_state);
        }
    }

    if($fatalerrmsg != null)
    {
        //Too important not to stop everything here.
        throw new \Exception('FATAL ERROR MESSAGE>>>'.$fatalerrmsg);
    }
    // else drop through to the end and return nothing - Drupal will handle the rejection for us   
}

/**
 * Checks the user by trying to log in.  Do NOT call this to check on 'other' users.
 * @param type $username
 * @param type $password
 * @param type $fatalerrmsg
 * @return boolean
 */
function raptor_glue_check_user($username, $password, &$fatalerrmsg)
{
    $username = trim($username);    //20140620
    $isvalid = TRUE;  //Assume valid user unless we reject it in following code.
    if(trim($username) == '' || trim($password) == '')
    {
        return FALSE;
    }
    
    //Check if in DRUPAL
    $drupaluser = user_load_by_name($username);
    if( $drupaluser == null || $drupaluser === FALSE)
    {
        $message = ('Did NOT find account information for username=['.$username.'] in Drupal');
        error_log($message);
        $isvalid = FALSE;
    } else {
        //If we are here, then we know this is a valid DRUPAL account, but is it a valid RAPTOR user?
        try 
        {
            $oUserInfo = new \raptor\UserInfo($drupaluser->uid);
            if(!isset($oUserInfo) || $oUserInfo->getUserName() == '')
            {
                error_log('Trouble checking for user ['.$username.'] with uid=['.$drupaluser->uid.']: did NOT get UserInfo instance!');
                $isvalid = FALSE;    
            }
        } catch (\Exception $ex) {
            error_log('Exception checking for user ['.$username.'] with uid=['.$drupaluser->uid.']: ' .  $ex->getMessage());
            $isvalid = FALSE;
        } 

        //Check the rest of the attributes.
        if($isvalid)
        {
            if(trim(strtolower($oUserInfo->getUserName())) 
                    !== trim(strtolower($username))) //trim on 20140620
            {
                //Internal configuration error!
                error_log('Tried logging in as user ['.$username
                        .'] with uid=['.$drupaluser->uid.'], but RAPTOR username=['
                        .$oUserInfo->getUserName().'] for that uid! '
                        . '>> first char of username=' . ord($username)  );
                $fatalerrmsg = "Detected username configuration "
                        . "inconsistency for user account '$username'; "
                        . "there is mismatch with the RAPTOR user profile!  "
                        . "Contact the RAPTOR site administrator for correction. [" 
                        . strtolower($oUserInfo->getUserName()) 
                        . "]!==[" . strtolower($username) 
                        .'] >> first char of username=' . ord($username);
                $isvalid = FALSE;
            } else
            if($oUserInfo->getUserID() !== $drupaluser->uid)
            {
                //Internal configuration error!
                error_log('Tried logging in as user ['.$username
                        .'] with Drupal uid=['.$drupaluser->uid
                        .'], but RAPTOR uid=['.$oUserInfo->getUserID()
                        .'] for that username!');
                $fatalerrmsg = "Detected ID configuration "
                        . "inconsistency for user account '$username'; "
                        . "there is mismatch with the RAPTOR user profile!  "
                        . "Contact the RAPTOR site administrator for correction."
                        .$oUserInfo->getUserID()." !== " .$drupaluser->uid;
                $isvalid = FALSE;
            } else
            if(!$oUserInfo->isEnabled())
            {
                error_log('Tried logging in with disabled user ['
                        .$username.'] with uid=['.$drupaluser->uid.']');
                $isvalid = FALSE;
            } else {

                //We are done if this is a site administrator, else keep validating.
                if(!$oUserInfo->isSiteAdministrator())
                {
                    //Check against MDWS
                    $oContext = \raptor\Context::getInstance(TRUE);
                    $oContext->logoutSubsystems();  //Workaround to avoid getting 'already logged in' issue
                    $result = $oContext->authenticateSubsystems($username, $password);
                    if(is_array($result))
                    {
                        //We are here because the authentication failed.
                        error_log('Failed subsystem authentication of user ['
                                .$username.'] (pwdprovided=['
                                .($password > ''?'yes':'no')
                                .']) with uid=['
                                .$drupaluser->uid.']');
                        $isvalid = FALSE;
                    }
                }
            }
            //Still valid?
            if($isvalid)
            {
                $tempUID = $drupaluser->uid; //Use the drupal UID now.
                $updated_dt = date("Y-m-d H:i:s", time());
                try
                {
                    db_insert('raptor_user_activity_tracking')
                        ->fields(array(
                            'uid'=>$tempUID,
                            'action_cd' => UATC_LOGIN,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            'updated_dt'=>$updated_dt,
                        ))
                        ->execute();
                    db_merge('raptor_user_recent_activity_tracking')
                        ->key(array('uid'=>$tempUID,
                            'ipaddress'=>$_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            ))
                        ->fields(array(
                            'uid'=>$tempUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            'most_recent_login_dt'=>$updated_dt,
                            'most_recent_action_dt'=>$updated_dt,
                            'most_recent_action_cd' => UATC_LOGIN,
                        ))
                        ->execute();
                } catch (\Exception $ex) {
                    error_log('Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
                    db_insert('raptor_user_activity_tracking')
                    ->fields(array(
                            'uid'=>$tempUID,
                            'action_cd' => ERRORCODE_AUTHENTICATION,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            'updated_dt'=>$updated_dt,
                        ))
                        ->execute();
                }
            }
        }
    }
    return $isvalid;
}

function raptor_glue_user_login(&$edit, $account) 
{
    if (!isset($_POST['form_id']) 
            || $_POST['form_id'] != 'user_pass_reset' 
            || variable_get('login_destination_immediate_redirect', FALSE)) 
    {
        if(user_is_logged_in()) //20150424
        {
            //login_destination_perform_redirect('login');
            $edit['redirect'] = 'worklist';
        }
    }
}

//User management
function raptor_glue_mngusers_form() 
{
    return drupal_get_form("raptor_glue_mngusers_form_builder");
}

function raptor_glue_viewuser_form() 
{
    return drupal_get_form("raptor_glue_viewuser_form_builder");
}

function raptor_glue_addstandarduser_form() 
{
    return drupal_get_form("raptor_glue_addstandarduser_form_builder");
}
function raptor_glue_addadminuser_form() 
{
    return drupal_get_form("raptor_glue_addadminuser_form_builder");
}

function raptor_glue_edituser_form() 
{
    return drupal_get_form("raptor_glue_edituser_form_builder");
}

function raptor_glue_editselfprofile_form() 
{
    return drupal_get_form("raptor_glue_editselfprofile_form_builder");
}

function raptor_glue_deleteuser_form() 
{
    return drupal_get_form("raptor_glue_deleteuser_form_builder");
}

/*DEPRECATED
function raptor_glue_login_form() 
{
    return drupal_get_form("raptor_glue_login_form_builder");
}
*/

function raptor_glue_chgpwd_form() 
{
    return drupal_get_form("raptor_glue_chgpwd_form_builder");
}

function raptor_glue_mngusers_form_builder($form, &$form_state)
{
    $oPI = new \raptor\ManageUsersPage();
    $form = array();
    $disabled = false;
    $myvalues = array();    //No values for this one.
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_chgpwd_form_builder($form, &$form_state)
{
    $oPI = new \raptor\ChangePasswordPage();
    $form = array();
    $disabled = false;
    $myvalues = array();    //TODO
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_chgpwd_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\ChangePasswordPage();
    $rowsupdated = $oPI->updateDatabase($form, $form_state['values']);
}

function raptor_glue_chgpwd_form_builder_submit($form, &$form_state) 
{
    //Success!
    //drupal_set_message('Created new user ' . $form_state['values']['username'], 'status');
    $form_state['redirect'] = array(
        'worklist',
      );
}


function raptor_glue_addstandarduser_form_builder($form, &$form_state)
{
    $role_nm = trim($_GET['role_nm']);
    if($role_nm == '')
    {
        die("Missing role_nm parameter!");
    }
    $oPI = new \raptor\AddUserPage($role_nm);

    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_addstandarduser_form_builder_validate($form, &$form_state) 
{
    $myvalues = $form_state['values'];
    $role_nm = $myvalues['role_nm'];
            
    $oPI = new \raptor\AddUserPage($role_nm);
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_addstandarduser_form_builder_submit($form, &$form_state) 
{
    $myvalues = $form_state['values'];
    $role_nm = $myvalues['role_nm'];
    
    $oPI = new \raptor\AddUserPage($role_nm);
    $isokay = $oPI->updateDatabase($form, $myvalues);
    if($isokay)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}




function raptor_glue_addadminuser_form_builder($form, &$form_state)
{
    $oPI = new \raptor\AddUserPage('Site Administrator');
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_addadminuser_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\AddUserPage('Site Administrator');
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_addadminuser_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\AddUserPage('Site Administrator');
    $isokay = $oPI->updateDatabase($form, $form_state['values']);
    if($isokay)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}


function raptor_kickout_accountconflict_form() 
{
    return drupal_get_form("raptor_kickout_accountconflict_form_builder");
}

function raptor_kickout_timeout_form() 
{
    return drupal_get_form("raptor_kickout_timeout_form_builder");
}

function raptor_kickout_accountconflict_form_builder($form, &$form_state)
{
    global $base_url;
    if(user_is_logged_in())
    {
        $redirect_title = 'Logout page link';
        $redirect_url = $base_url.'/user/logout'; //.$user->name.'/my-argument';
    } else {
        $redirect_title = 'Login page link';
        $redirect_url = $base_url.'/user/login'; //.$user->name.'/my-argument';
    }

    $form[]['info'] = array(
        '#markup' => '<h1>Another workstation has logged in using the '
        . 'same account you were using.  You have now been kicked out.'
        . '</h1><p>Only one user should have the credentials for a RAPTOR account.</p>'
        . '<p><strong>Change your password and contact security if you did not log into '
        . 'the other workstation because your credentials may have been compromised.</strong></p>'
        .'<p>Links: <a href="'.$redirect_url.'">'.$redirect_title.'</a></p>');

    //drupal_exit();
    return $form;
}

function raptor_kickout_timeout_form_builder($form, &$form_state)
{
    global $base_url;

     //Kill the actual session now otherwise the login link will fail!
    session_destroy(); 
    
    if(user_is_logged_in())
    {
        $redirect_title = 'Logout page link';
        $redirect_url = $base_url.'/user/logout'; //.$user->name.'/my-argument';
    } else {
        $redirect_title = 'Login page link';
        $redirect_url = $base_url.'/user/login'; //.$user->name.'/my-argument';
    }

    //Provide the link.
    $form[]['info'] = array(
        '#markup' => '<h1>Your RAPTOR session has timed out</h1>'
                    .'<p>Links: <a href="'.$redirect_url.'">'.$redirect_title.'</a></p>');
    //drupal_exit();
    return $form;
}

function raptor_kickout_timeout_form_builder_submit($form, &$form_state)
{
    drupal_set_message('Time ' . date("Y-m-d H:i:s", time()));
}

function raptor_glue_edituser_form_builder($form, &$form_state)
{
    if(!isset($_GET['uid']))
    {
        die("Missing required uid url parameter!");
    }
    $nUID = $_GET['uid'];
    if(!is_numeric($nUID))
    {
        die("Invalid uid url parameter: that value MUST be numeric!");
    }

    $oPI = new \raptor\EditUserPage($nUID);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();

    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_edituser_form_builder_validate($form, &$form_state) 
{
    $nUID = $form_state['values']['uid'];
    if(!is_numeric($nUID))
    {
        die("Invalid uid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \raptor\EditUserPage($nUID);
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_edituser_form_builder_submit($form, &$form_state) 
{
    $nUID = $form_state['values']['uid'];
    $oPI = new \raptor\EditUserPage($nUID);
    $isokay = $oPI->updateDatabase($form, $form_state['values']);
    if($isokay == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}


function raptor_glue_editselfprofile_form_builder($form, &$form_state)
{
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    if(!is_numeric($nUID))
    {
        die("Invalid uid '$nUID': that value MUST be numeric!");
    }
    $gobackto='worklist';
    $oPI = new \raptor\EditUserPage($nUID, $gobackto);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_editselfprofile_form_builder_validate($form, &$form_state) 
{
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    if(!is_numeric($nUID))
    {
        die("Invalid uid '$nUID': that value MUST be numeric!");
    }
    $gobackto='worklist';
    $oPI = new \raptor\EditUserPage($nUID, $gobackto);
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_editselfprofile_form_builder_submit($form, &$form_state) 
{
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    $oPI = new \raptor\EditUserPage($nUID);
    $isokay = $oPI->updateDatabase($form, $form_state['values']);
    global $base_url;
    if($isokay == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            $base_url.'/worklist'
          );
    }
}


function raptor_glue_viewuser_form_builder($form, &$form_state)
{
    if(!isset($_GET['uid']))
    {
        die("Missing required uid url parameter!");
    }
    $nUID = $_GET['uid'];
    if(!is_numeric($nUID))
    {
        die("Invalid uid url parameter: that value MUST be numeric!");
    }

    $oPI = new \raptor\ViewUserPage($nUID);
    $form = array();
    $disabled = true;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_deleteuser_form_builder($form, &$form_state)
{
    if(!isset($_GET['uid']))
    {
        die("Missing required uid url parameter!");
    }
    $nUID = $_GET['uid'];
    if(!is_numeric($nUID))
    {
        die("Invalid uid url parameter: that value MUST be numeric!");
    }

    $oPI = new \raptor\DeleteUserPage($nUID);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_deleteuser_form_builder_submit($form, &$form_state) 
{
    if(!isset($_GET['uid']))
    {
        die("Missing required uid url parameter!");
    }
    $nUID = $_GET['uid'];
    if(!is_numeric($nUID))
    {
        die("Invalid uid url parameter: that value MUST be numeric!");
    }

    $oPI = new \raptor\DeleteUserPage($nUID);
    $rowsupdated = $oPI->updateDatabase($form, $form_state['values']);
    if($rowsupdated == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}



/**
 * Implementation of hook_form_alter().
 */
function raptor_glue_form_alter(&$form, &$form_state, $form_id) 
{
  
  // Autofocus on the username field.
  if ($form_id == 'user_login' 
          || $form_id == 'user_login_block'
          || $form_id == 'user_register_form' 
          || $form_id == 'user_pass') 
  {
    $form['name']['#attributes']['autofocus'] = 'autofocus';
  }

  //Add some markup.
  global $user;
  global $base_url;
  switch ($form_id) {
    case 'user_login':
    case 'user_login_block':
        drupal_set_title(t('RAPTOR Login'));
        //The username in VistA is not public information so hide it.
        $form['name'] = array(
          '#type' => 'password', 
          '#title' => t('Username:'),
          '#description' => t('Provide your login name for RAPTOR site '.VISTA_SITE),
          '#required' => TRUE,
        );
        break;

    case 'user_register_form':
        drupal_set_message('Contact your RAPTOR Site Administrator to create an account.'
                , 'warning');
        drupal_goto($base_url.'/user/login');
        break;

    case 'user_pass':
        drupal_set_message('RAPTOR uses VistA authentication for users.'
                . '  Contact your RAPTOR Site Administrator if you need more information.'
                , 'warning');
        drupal_goto($base_url.'/user/login');
        break;
    
    case 'user_profile_form':
        error_log('User '.$user->name
                .' triggered goto user/logout because form_id='.$form_id);
        drupal_goto($base_url.'/user/logout');
        break;
    default:
        /*
        $currentpath = current_path();
        $currentpage = drupal_lookup_path('alias',$currentpath);
        error_log('Did NOT TRAP form_id='.$form_id.' path='.$currentpath.' page='.$currentpage);
         */
        break;
  }
}

function raptor_str_endswith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}  


/**
 * Redirect if we do not have a valid uer.
 */
function raptor_glue_intercept_bad_users()
{
  global $user;
  global $base_url;

  //error_log('In raptor_glue_init as ['.arg(0).']...' . print_r($user,true));
  $oContext = \raptor\Context::getInstance(FALSE,TRUE);
  if (!$user->uid) 
  {
    // We need to collect where they were going in the first place because they may get pissed if 
    // they don't get there after logging in :).
    $destination = '';
    if ($_GET['q']) 
    {
      $destination = array('destination' => $_GET['q']);
      //error_log('In raptor_glue_init setting destination as ' . print_r($destination,true));
    }

    // If this site is set to private we want to redirect all anonymous users to the login form.
    if (variable_get('raptor_glue_private')) 
    {
      // Because of Drush we only want to block anything not from CLI.
      if (arg(0) !== 'user' && php_sapi_name() !== 'cli') 
      {
        drupal_goto($base_url.'/user/login', array('query' => $destination));
      }
    }
  
    // Make sure that anonomous users cannot go to just /user but directly to the login form.
    if ((arg(0) == 'user') && !arg(1) && php_sapi_name() !== 'cli') 
    {
        drupal_goto($base_url.'/user/login');
    }
  } else {
    //Don't let any user just go to user url
    if (php_sapi_name() !== 'cli') 
    {
        $currentpath = trim(current_path());
        $currentpage = trim(drupal_lookup_path('alias',$currentpath));
        
        //Just the plain user path?
        if($currentpath == 'user' && $currentpage == '')
        {
            drupal_set_message('Tip: Return to <a href="'.$base_url.'/worklist'.'">the worklist</a>','warning');
            //CAUSES INFINITE REDIRECT drupal_goto($base_url.'/user/login');
        }
    }
  }
}


