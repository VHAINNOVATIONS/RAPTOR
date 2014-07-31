<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once('FormHelper.php');
module_load_include('inc', 'raptor_datalayer', 'core/data_user.php');

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class UserPageHelper
{
    
    /**
     * Get the values to populate the form.
     * @param type $nUID the user id
     * @return type result of the queries as an array
     */
    public function getFieldValues($nUID)
    {
        $filter = array(":uid" => $nUID);
        $result = db_query('SELECT username, role_nm, usernametitle, firstname, lastname, suffix, prefemail, prefphone, accountactive_yn, '
                            . '`CEUA1`, `LACE1`, `SWI1`, `PWI1`, `APWI1`, `SUWI1`, `CE1`, `QA1`, `SP1`, `VREP1`, `VREP2`, '
                            . '`EBO1`, `UNP1`, `REP1`, `DRA1`, `ELCO1`, ELHO1, `ELSO1`, `ELRO1`, `ELSVO1`, `ECIR1`, `EECC1`, `EERL1`, `EARM1`, `CUT1` '
                            .' FROM raptor_user_profile WHERE uid = :uid', $filter);
        //We might be here AFTER the row was deleted.
        $myvalues = array();
        if($result->rowCount()!==0)
        {
            //Not deleted yet.
            $record = $result->fetchObject();

            $myvalues['uid'] = $nUID;
            $myvalues['username'] = $record->username;
            $myvalues['password'] = null;
            $myvalues['role_nm'] = $record->role_nm;
            $myvalues['usernametitle'] = $record->usernametitle;
            $myvalues['firstname'] = $record->firstname;
            $myvalues['lastname'] = $record->lastname;
            $myvalues['suffix'] = $record->suffix;
            $myvalues['prefemail'] = $record->prefemail;
            $myvalues['prefphone'] = $record->prefphone;
            $myvalues['accountactive_yn'] = $record->accountactive_yn;

            $myvalues['CEUA1'] = $record->CEUA1;
            $myvalues['LACE1'] = $record->LACE1;
            $myvalues['SWI1'] = $record->SWI1;
            $myvalues['PWI1'] = $record->PWI1;
            $myvalues['APWI1'] = $record->APWI1;
            $myvalues['SUWI1'] = $record->SUWI1;
            $myvalues['CE1'] = $record->CE1;
            $myvalues['QA1'] = $record->QA1;
            $myvalues['SP1'] = $record->SP1;
            $myvalues['VREP1'] = $record->VREP1;
            $myvalues['VREP2'] = $record->VREP2;

            $myvalues['EBO1'] = $record->EBO1;
            $myvalues['UNP1'] = $record->UNP1;
            $myvalues['REP1'] = $record->REP1;
            $myvalues['DRA1'] = $record->DRA1;
            $myvalues['ELCO1'] = $record->ELCO1;
            $myvalues['ELHO1'] = $record->ELHO1;
            $myvalues['ELSO1'] = $record->ELSO1;
            $myvalues['ELSVO1'] = $record->ELSVO1;
            $myvalues['ELRO1'] = $record->ELRO1;
            $myvalues['ECIR1'] = $record->ECIR1;
            $myvalues['EECC1'] = $record->EECC1;
            $myvalues['EERL1'] = $record->EERL1;
            $myvalues['EARM1'] = $record->EARM1;
            $myvalues['CUT1'] = $record->CUT1;

            //Get the modality information from a query
            $modality_result = db_query('SELECT modality_abbr, specialist_yn FROM raptor_user_modality WHERE uid = :uid', $filter);
            $myvalues['userpref_modality'] = array();
            $myvalues['specialist_modality'] = array();
            if($modality_result->rowCount()!=0)
            {
                foreach($modality_result as $item) 
                {
                    $myvalues['userpref_modality'][$item->modality_abbr] = $item->modality_abbr;
                    if($item->specialist_yn == 1)
                    {
                        $myvalues['specialist_modality'][$item->modality_abbr] = $item->modality_abbr;
                    }
                }
            }

            $keyword_result = db_query('SELECT weightgroup, keyword, specialist_yn FROM raptor_user_anatomy WHERE uid = :uid', $filter);
            $myvalues['userpref_keywords1'] = array();
            $myvalues['userpref_keywords2'] = array();
            $myvalues['userpref_keywords3'] = array();
            $myvalues['specialist_keywords1'] = array();
            $myvalues['specialist_keywords2'] = array();
            $myvalues['specialist_keywords3'] = array();
            if($keyword_result->rowCount()!=0)
            {
                foreach($keyword_result as $item) 
                {
                    if(isset($item->specialist_yn) && $item->specialist_yn == 1)
                    {
                        if($item->weightgroup == 1)
                        {
                            $myvalues['specialist_keywords1'][] = $item->keyword;
                        } else
                        if($item->weightgroup == 2)
                        {
                            $myvalues['specialist_keywords2'][] = $item->keyword;
                        } else
                        if($item->weightgroup == 3)
                        {
                            $myvalues['specialist_keywords3'][] = $item->keyword;
                        } else {
                            die("Invalid weightgroup value for uid=" . $nUID);
                        }
                    } else {
                        if($item->weightgroup == 1)
                        {
                            $myvalues['userpref_keywords1'][] = $item->keyword;
                        } else
                        if($item->weightgroup == 2)
                        {
                            $myvalues['userpref_keywords2'][] = $item->keyword;
                        } else
                        if($item->weightgroup == 3)
                        {
                            $myvalues['userpref_keywords3'][] = $item->keyword;
                        } else {
                            die("Invalid weightgroup value for uid=" . $nUID);
                        }
                    }
                }
            }
        }
        
        return $myvalues;
    }

    public function writeKeywords($nUID, $weightgroup, $userpref_keywords, $bSpecialist, $updated_dt)
    {
        foreach($userpref_keywords as $keyword)
        {
            $keyword = trim($keyword);
            if($keyword !== '')
            {
                try
                {
                    $nAdded = db_insert('raptor_user_anatomy')
                            ->fields(array(
                                'uid' => $nUID,
                                'weightgroup' => $weightgroup,
                                'keyword' => strtoupper($keyword),  //IMPORTANT MUST ALWAYS BE UPPERCASE IN THE DATABASE!
                                'specialist_yn' => $bSpecialist,
                                'updated_dt' => $updated_dt,
                            ))
                            ->execute();
                } catch (\Exception $e) {
                    //Continue
                }
            }
        }
    }

    /**
     * Write all the child records into the database.
     * @param type $myvalues
     */
    public function writeChildRecords($myvalues)
    {
        if(!isset($myvalues['uid']) || $myvalues['uid'] == '')
        {
            die('Must set the myvalues[uid] value!');
        }
        $updated_dt = date("Y-m-d H:i:s", time());
        
        //Parse the modalities and keywords to write those too into OTHER tables!
        $nDeleted = db_delete('raptor_user_modality')
                ->condition('uid', $myvalues['uid'])
                ->execute();
        $specialist_modality = $myvalues['specialist_modality'];
        foreach($myvalues['userpref_modality'] as $modality_abbr => $value)
        {
            if($value !== 0)
            {
                if($specialist_modality[$modality_abbr] !== 0)
                {
                    $bSpecialist = 1;
                } else {
                    $bSpecialist = 0;
                }
                $nAdded = db_insert('raptor_user_modality')
                        ->fields(array(
                            'uid' => $myvalues['uid'],
                            'modality_abbr' => $modality_abbr,
                            'specialist_yn' => $bSpecialist,
                            'updated_dt' => $updated_dt,
                        ))
                        ->execute();
            }
        }

        $nDeleted = db_delete('raptor_user_anatomy')
                ->condition('uid', $myvalues['uid'])
                ->execute();
        
        $userpref_keywords1 = explode(',',$myvalues['userpref_keywords1']);
        $this->writeKeywords($myvalues['uid'], 1, $userpref_keywords1, 0, $updated_dt);
        $specialist_keywords1 = explode(',',$myvalues['specialist_keywords1']);
        $this->writeKeywords($myvalues['uid'], 1, $specialist_keywords1, 1, $updated_dt);
        
        $userpref_keywords2 = explode(',',$myvalues['userpref_keywords2']);
        $this->writeKeywords($myvalues['uid'], 2, $userpref_keywords2, 0, $updated_dt);
        $specialist_keywords2 = explode(',',$myvalues['specialist_keywords2']);
        $this->writeKeywords($myvalues['uid'], 2, $specialist_keywords2, 1, $updated_dt);

        $userpref_keywords3 = explode(',',$myvalues['userpref_keywords3']);
        $this->writeKeywords($myvalues['uid'], 3, $userpref_keywords3, 0, $updated_dt);
        $specialist_keywords3 = explode(',',$myvalues['specialist_keywords3']);
        $this->writeKeywords($myvalues['uid'], 3, $specialist_keywords3, 1, $updated_dt);
    }
    
    /**
     * @return array of all option values for the form
     */
    public function getAllOptions()
    {
        //Get all the role options from a query
        $sSQL = 'SELECT `roleid`, `enabled_yn`, `name`' 
                . ', `CEUA1`, `lockCEUA1`, `LACE1`, `lockLACE1`, `SWI1`, `lockSWI1`, `PWI1`, `lockPWI1`, `APWI1`, `lockAPWI1`, `SUWI1`, `lockSUWI1`, `CE1`, `lockCE1`, `QA1`, `lockQA1` '
                . ', `VREP1`, `lockVREP1` , `VREP2`, `lockVREP2` '
                . ', `SP1`, `lockSP1`, `EBO1`, `lockEBO1`, `UNP1`, `lockUNP1`, `REP1`, `lockREP1`, `DRA1`, `lockDRA1`, `ELCO1`, `lockELCO1`, `ELHO1`, `lockELHO1`, `ELSO1`, `lockELSO1`, `ELSVO1`, `lockELSVO1`, `ELRO1`, `lockELRO1`, `ECIR1`, `lockECIR1`, `EECC1`, `lockEECC1`, `EERL1`, `lockEERL1`, `EARM1`, `lockEARM1`, `CUT1`, `lockCUT1`'
                . ' FROM `raptor_role` ORDER BY `roleid`';
        $role_result = db_query($sSQL);
        if($role_result->rowCount()==0)
        {
            die('Did NOT find any role options!');
        } else {
            $role_choices=array();
            $role_rights=array();
            foreach($role_result as $item) 
            {
                $role_choices[$item->name] = $item->name;
                $role_rights[$item->name] = array(
                    'CEUA1' => $item->CEUA1 ,
                    'lockCEUA1' => $item->lockCEUA1 ,
                    'LACE1' => $item->LACE1 ,
                    'lockLACE1' => $item->lockLACE1 ,
                    'SWI1' => $item->SWI1 ,
                    'lockSWI1' => $item->lockSWI1 ,
                    'PWI1' => $item->PWI1 ,
                    'lockPWI1' => $item->lockPWI1 ,
                    'APWI1' => $item->APWI1 ,
                    'lockAPWI1' => $item->lockAPWI1 ,
                    'SUWI1' => $item->SUWI1 ,
                    'lockSUWI1' => $item->lockSUWI1 ,
                    'CE1' => $item->CE1 ,
                    'lockCE1' => $item->lockCE1 ,
                    'QA1' => $item->QA1 ,
                    'lockQA1' => $item->lockQA1 ,
                    'SP1' => $item->SP1 ,
                    'lockSP1' => $item->lockSP1 ,
                    'VREP1' => $item->VREP1 ,
                    'lockVREP1' => $item->lockVREP1 ,
                    'VREP2' => $item->VREP2 ,
                    'lockVREP2' => $item->lockVREP2 ,
                    'EBO1' => $item->EBO1 ,
                    'lockEBO1' => $item->lockEBO1 ,
                    'UNP1' => $item->UNP1 ,
                    'lockUNP1' => $item->lockUNP1 ,
                    'REP1' => $item->REP1 ,
                    'lockREP1' => $item->lockREP1 ,
                    'DRA1' => $item->DRA1 ,
                    'lockDRA1' => $item->lockDRA1 ,
                    'ELCO1' => $item->ELCO1 ,
                    'lockELCO1' => $item->lockELCO1 ,
                    'ELHO1' => $item->ELHO1 ,
                    'lockELHO1' => $item->lockELHO1 ,
                    'ELSO1' => $item->ELSO1 ,
                    'lockELSO1' => $item->lockELSO1 ,
                    'ELSVO1' => $item->ELSVO1 ,
                    'lockELSVO1' => $item->lockELSVO1 ,
                    'ELRO1' => $item->ELRO1 ,
                    'lockELRO1' => $item->lockELRO1 ,
                    'ECIR1' => $item->ECIR1 ,
                    'lockECIR1' => $item->lockECIR1 ,
                    'EECC1' => $item->EECC1 ,
                    'lockEECC1' => $item->lockEECC1 ,
                    'EERL1' => $item->EERL1 ,
                    'lockEERL1' => $item->lockEERL1 ,
                    'EARM1' => $item->EARM1 ,
                    'lockEARM1' => $item->lockEARM1 ,
                    'CUT1' => $item->CUT1 ,
                    'lockCUT1' => $item->lockCUT1 ,                
               );
            }
        }
        
        //die('REMOVE ME all role rights>>>>' . print_r($role_rights,TRUE));
        
        //Get all the modality options from a query
        $modality_result = db_query('SELECT modality_abbr, `modality_desc` FROM `raptor_list_modality` ORDER BY modality_abbr');
        if($modality_result->rowCount()==0)
        {
            die('Did NOT find any modality options!');
        } else {
            $modality_choices=array();
            foreach($modality_result as $item) 
            {
                $modality_choices[$item->modality_abbr] = $item->modality_desc;
            }
        }
        
        //Get all the service options from a query
        $service_result = db_query('SELECT service_nm, `service_desc` FROM `raptor_list_service` ORDER BY service_nm');
        if($service_result->rowCount()==0)
        {
            //This is okay if it happens.
            $service_choices=array();
        } else {
            $service_choices=array();
            foreach($service_result as $item) 
            {
                $service_choices[$item->service_nm] = $item->service_nm;
            }
        }

        $aOptions = array();
        $aOptions['role_nm'] = $role_choices;
        $aOptions['role_rights'] = $role_rights;
        $aOptions['modality'] = $modality_choices;
        $aOptions['service'] = $service_choices;
        return $aOptions;
    }

    public function formatKeywordText($myvalues)
    {
        $aFormatted = array();
        if(!isset($myvalues['userpref_keywords1']))
        {
            $aFormatted['userpref_keywords1'] = '';
        } else {
            $aFormatted['userpref_keywords1'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords1'], ',');
        }
        if(!isset($myvalues['userpref_keywords2']))
        {
            $aFormatted['userpref_keywords2'] = '';
        } else {
            $aFormatted['userpref_keywords2'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords2'], ',');
        }
        if(!isset($myvalues['userpref_keywords3']))
        {
            $aFormatted['userpref_keywords3'] = '';
        } else {
            $aFormatted['userpref_keywords3'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords3'], ',');
        }
        
        if(!isset($myvalues['specialist_keywords1']))
        {
            $aFormatted['specialist_keywords1'] = '';
        } else {
            $aFormatted['specialist_keywords1'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords1'], ',');
        }
        if(!isset($myvalues['specialist_keywords2']))
        {
            $aFormatted['specialist_keywords2'] = '';
        } else {
            $aFormatted['specialist_keywords2'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords2'], ',');
        }
        if(!isset($myvalues['specialist_keywords3']))
        {
            $aFormatted['specialist_keywords3'] = '';
        } else {
            $aFormatted['specialist_keywords3'] = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords3'], ',');
        }
        return $aFormatted;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $aOptions = $this->getAllOptions();
        $aFormattedKeywordText = $this->formatKeywordText($myvalues);
        $jsTFtxt = ($disabled ? 'true' : 'false');
        drupal_add_js('jQuery(document).ready(function () { initializePrivilegeControls(document.getElementsByName("role_nm")[0].value,all_role_rights,'.$jsTFtxt.'); });', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        $form['#attached']['js'] = array(
          drupal_get_path('module', 'raptor_datalayer') . '/js/userPageHelper.js',
        );        
        $form['#attributes'] = array('autocomplete' => 'off');
        
        $form["data_script_area1"]['mydata'] = array('#type' => 'item'
                , '#markup' => "\n<script>\nvar all_role_rights=". json_encode($aOptions['role_rights']) . "\n</script>\n"
            );
        
        //The form  mode is carried as a hidden field value.
        $form['data_entry_area1']['formmode'] = array(
          '#type' => 'hidden', 
          '#title' => t('formmode'), 
          '#value' => $myvalues['formmode'], 
          '#required' => TRUE,
          '#disabled' => FALSE,
        );        
        
        if(isset($myvalues['uid']))
        {
            $nUID = $myvalues['uid'];
        } else {
            $nUID = -1; //When we are adding a new user.
        }

        $form['data_entry_area1']['uid'] = array(
          '#type' => 'hidden', 
          '#title' => t('uid'), 
          '#value' => $nUID, 
          '#required' => TRUE,
          '#disabled' => FALSE,
        );        

        $form['data_entry_area1']['leftpart'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        if(!isset($userprivs['CEUA1']))
        {
            $userprivs['CEUA1'] = 0;
        }
        if($nUID == 1 && $userprivs['CEUA1'] !== 1 )
        {
            $form['data_entry_area1']['leftpart']['role_nm'] = array(
                '#type' => 'select',
                '#title' => t('Role'),
                '#options' => $aOptions['role_nm'],
                '#default_value' => $myvalues['role_nm'], 
                '#description' => t('The role or this user cannot be changed by your account.')  ,
                '#required' => TRUE,
                '#disabled' => TRUE,    //This user cannot edit this.  (Set in add form to false.)
                '#attributes' => array('onchange' => "initializePrivilegeControls(document.getElementsByName('role_nm')[0].value,all_role_rights,$jsTFtxt)"),            
            );        
        } else {
            $form['data_entry_area1']['leftpart']['role_nm'] = array(
                '#type' => 'select',
                '#title' => t('Role'),
                '#options' => $aOptions['role_nm'],
                '#default_value' => $myvalues['role_nm'], 
                '#description' => t('The role of the user.'),
                '#required' => TRUE,
                '#disabled' => $disabled,
                '#attributes' => array('onchange' => "initializePrivilegeControls(document.getElementsByName('role_nm')[0].value,all_role_rights,$jsTFtxt)"),            
            );        
            
        }

        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#default_value' => $myvalues['username'], 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The login name of the user.  This must match their VISTA login name.'),
          '#disabled' => $disabled,
          '#attributes' => array('autocomplete' => 'off'),
        );        

        $form['data_entry_area1']['leftpart']['password'] = array(
          '#type' => 'password_confirm', 
          '#prefix' => '<div id="edit-password">',
          '#suffix' => '</div>',
          '#size' => 40, 
          '#required' => TRUE,
          '#description' => t('The password for this account.  Pick a strong password and do not share it.'),
          '#disabled' => $disabled,
          '#attributes' => array('autocomplete' => 'off'),
        );        
        
        $form['data_entry_area1']['leftpart']['usernametitle'] = array(
          '#type' => 'textfield', 
          '#title' => t('Title'), 
          '#default_value' => $myvalues['usernametitle'], 
          '#size' => 16, 
          '#maxlength' => 16, 
          '#required' => FALSE,
          '#description' => t('Title for this user (e.g., Mr, Ms, Dr, etc)'),
          '#disabled' => $disabled,
        );        
        
        $form['data_entry_area1']['leftpart']['firstname'] = array(
          '#type' => 'textfield', 
          '#title' => t('First name'), 
          '#default_value' => $myvalues['firstname'], 
          '#size' => 50, 
          '#maxlength' => 50, 
          '#required' => TRUE,
          '#description' => t('First name for this user'),
          '#disabled' => $disabled,
        );        
        
        $form['data_entry_area1']['leftpart']['lastname'] = array(
          '#type' => 'textfield', 
          '#title' => t('Last name'), 
          '#default_value' => $myvalues['lastname'], 
          '#size' => 50, 
          '#maxlength' => 50, 
          '#required' => TRUE,
          '#description' => t('Last name for this user'),
          '#disabled' => $disabled,
        );        

        $form['data_entry_area1']['leftpart']['suffix'] = array(
          '#type' => 'textfield', 
          '#title' => t('Suffix'), 
          '#default_value' => $myvalues['suffix'], 
          '#size' => 20, 
          '#maxlength' => 20, 
          '#required' => FALSE,
          '#description' => t('Suffix for this user (e.g., PhD)'),
          '#disabled' => $disabled,
        );        

        $form['data_entry_area1']['rightpart'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['rightpart']['prefemail'] = array(
          '#type' => 'textfield', 
          '#title' => t('Preferred email'), 
          '#default_value' => $myvalues['prefemail'], 
          '#size' => 60, 
          '#maxlength' => 128, 
          '#required' => FALSE,
          '#description' => t('Preferred email for this user'),
          '#disabled' => $disabled,
        );        

        $form['data_entry_area1']['rightpart']['prefphone'] = array(
          '#type' => 'textfield', 
          '#title' => t('Preferred phone'), 
          '#default_value' => $myvalues['prefphone'], 
          '#size' => 50, 
          '#maxlength' => 50, 
          '#required' => FALSE,
          '#description' => t('Preferred phone number for this user'),
          '#disabled' => $disabled,
        );     
        
        //The main site admin record CANNOT be disabled.
        if($nUID != 1)
        {
            $form['data_entry_area1']['rightpart']['accountactive_yn'] = array(
               '#type' => 'checkbox', 
               '#title' => t('Account active (Y/N)'),
               '#default_value' => $myvalues['accountactive_yn'], 
               '#description' => t('User is blocked from RAPTOR if account is not active'),
               '#disabled' => $disabled,
            );
        }

        $form['data_entry_area1']['ticketmgtprivileges'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Ticket Managment Privileges'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['SWI1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Select worklist items'),
           '#default_value' => $myvalues['SWI1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['PWI1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can protocol a ticket'),
           '#default_value' => $myvalues['PWI1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['APWI1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can approve a protocol'),
           '#default_value' => $myvalues['APWI1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['SUWI1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can suspend a ticket'),
           '#default_value' => $myvalues['SUWI1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['CE1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can complete an exam'),
           '#default_value' => $myvalues['CE1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['QA1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can QA an exam'),
           '#default_value' => $myvalues['QA1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['ticketmgtprivileges']['SP1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can schedule a procedure'),
           '#default_value' => $myvalues['SP1'], 
           '#disabled' => $disabled ,
        );
        
        $form['data_entry_area1']['accountmgtprivileges'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Account Management Privileges'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['accountmgtprivileges']['CEUA1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Add/Edit Any User Accounts'),
           '#default_value' => $myvalues['CEUA1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['accountmgtprivileges']['LACE1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Add/Edit Resident User Accounts'),
           '#default_value' => $myvalues['LACE1'], 
           '#disabled' => $disabled ,
        );
        
        $form['data_entry_area1']['sitewideconfig'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Sitewide Configuration Privileges'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['sitewideconfig']['VREP1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can view department activity reports'),
           '#default_value' => $myvalues['VREP1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['VREP2'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can view user activity reports'),
           '#default_value' => $myvalues['VREP2'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['EBO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit boilerplate text'),
           '#default_value' => $myvalues['EBO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['UNP1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can upload protocols'),
           '#default_value' => $myvalues['UNP1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['REP1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can retire protocols'),
           '#default_value' => $myvalues['REP1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['DRA1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can define default attributes of roles'),
           '#default_value' => $myvalues['DRA1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ELCO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit contrast options'),
           '#default_value' => $myvalues['ELCO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ELHO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit hydration options'),
           '#default_value' => $myvalues['ELHO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ELSO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit list of sedation options'),
           '#default_value' => $myvalues['ELSO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ELSVO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit list of service options'),
           '#default_value' => $myvalues['ELSVO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ELRO1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit radioisotope options'),
           '#default_value' => $myvalues['ELRO1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['ECIR1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit contraindication results'),
           '#default_value' => $myvalues['ECIR1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['EECC1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit excluded CPRS metadata'),
           '#default_value' => $myvalues['EECC1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['EERL1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit examination room list'),
           '#default_value' => $myvalues['EERL1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['EARM1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit the list of at risk medication keywords'),
           '#default_value' => $myvalues['EARM1'], 
           '#disabled' => $disabled ,
        );
        $form['data_entry_area1']['sitewideconfig']['CUT1'] = array(
           '#type' => 'checkbox', 
           '#title' => t('Can edit umbrella terms'),
           '#default_value' => $myvalues['CUT1'], 
           '#disabled' => $disabled ,
        );
        
        $form['data_entry_area1']['worklistpref'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Worklist Preferences'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['worklistpref']['userpref_modality'] = array(
            '#type' => 'checkboxes',
            '#options' => $aOptions['modality'],
            '#default_value' => $myvalues['userpref_modality'],
            '#title' => t('Modalities'),
            '#description' => t('The modalites for this user'),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['worklistpref']['keywords'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Keywords'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords1'] = array(
          '#type' => 'textfield', 
          '#title' => t('Most Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords1'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of most significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords2'] = array(
          '#type' => 'textfield', 
          '#title' => t('Moderately Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords2'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of moderately significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['worklistpref']['keywords']['userpref_keywords3'] = array(
          '#type' => 'textfield', 
          '#title' => t('Least Significant'), 
          '#default_value' => $aFormattedKeywordText['userpref_keywords3'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of least significant keywords'),
          '#disabled' => $disabled,
        );        

        $form['data_entry_area1']['collaborationpref'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Collaboration Settings'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $form['data_entry_area1']['collaborationpref']['specialist_modality'] = array(
            '#type' => 'checkboxes',
            '#options' => $aOptions['modality'],
            '#default_value' => $myvalues['specialist_modality'],
            '#title' => t('Modalities'),
            '#description' => t('The modalites for this user'),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['collaborationpref']['keywords'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Keywords'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['collaborationpref']['keywords']['specialist_keywords1'] = array(
          '#type' => 'textfield', 
          '#title' => t('Most Significant'), 
          '#default_value' => $aFormattedKeywordText['specialist_keywords1'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of most significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['collaborationpref']['keywords']['specialist_keywords2'] = array(
          '#type' => 'textfield', 
          '#title' => t('Moderately Significant'), 
          '#default_value' => $aFormattedKeywordText['specialist_keywords2'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of moderately significant keywords'),
          '#disabled' => $disabled,
        );        
        $form['data_entry_area1']['collaborationpref']['keywords']['specialist_keywords3'] = array(
          '#type' => 'textfield', 
          '#title' => t('Least Significant'), 
          '#default_value' => $aFormattedKeywordText['specialist_keywords3'], 
          '#size' => 100, 
          '#maxlength' => 128, 
          '#description' => t('Comma delimited list of least significant keywords'),
          '#disabled' => $disabled,
        );        
        
        return $form;
    }
}
