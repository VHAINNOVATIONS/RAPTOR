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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'core/data_user');
require_once 'FormHelper.php';
require_once 'UserPageHelper.php';
require_once 'ChildEditBasePage.php';


/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditUserPage extends \raptor\ChildEditBasePage
{

    private $m_oContext = NULL;
    private $m_oPageHelper = null;
    private $m_nUID = null;
    
     //Call same function as in EditUserPage here!
    function __construct($nUID, $gobacktooverride=NULL)
    {
        if(!isset($nUID) || !is_numeric($nUID))
        {
            throw new \Exception("Missing or invalid uid value = " . $nUID);
        }
        $this->m_nUID = $nUID;
        $this->m_oPageHelper = new \raptor\UserPageHelper();
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oPageHelper->checkAllowedToEditUser($this->m_oContext, $this->m_nUID);
        
        //Set the default gobackto url now
        global $base_url;
        if($gobacktooverride == NULL)
        {
            $this->setGobacktoURL($base_url.'/raptor/manageusers');
        } else {
            $this->setGobacktoURL($base_url.'/'.$gobacktooverride);
        }
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
        if(!isset($myvalues['username']) || trim($myvalues['username']) == '')
        {
            form_set_error('username','Missing username value.');
            $bGood = FALSE;        
        }
        if(isset($myvalues['prefemail']) && trim($myvalues['prefemail']) > ''
                && !valid_email_address($myvalues['prefemail']))
        {
            form_set_error('prefemail','Email address is not valid');
            $bGood = FALSE;        
        }
        
        if($bGood)
        {
            $bGood = $this->m_oPageHelper->validateModality($myvalues);
        }
        
        return $bGood;
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
    function updateDatabase($form, $myvalues)
    {
        if(!isset($myvalues['username']))
        {
            throw new \Exception("Cannot update user record because missing username in array!\n" . var_dump($myvalues));
        }
        if(!isset($myvalues['uid']))
        {
            throw new \Exception("Cannot update user record because missing uid in array!\n" . var_dump($myvalues));
        }
        $updated_dt = date("Y-m-d H:i:s", time());
        if(!isset($myvalues['accountactive_yn']))
        {
            $myvalues['accountactive_yn'] = 1;  //If not set, then assume because user is NOT allowed to disable the account.
        }
        
        //die('CEUA1='.$myvalues['CEUA1'] . 'DUMP ALL...<br>' . print_r($myvalues, true));
        try
        {
            $nUpdated = db_update('raptor_user_profile')
                    -> fields(['role_nm' => $myvalues['role_nm'],
                        'usernametitle' => $myvalues['usernametitle'],
                        'firstname' => $myvalues['firstname'],
                        'lastname' => $myvalues['lastname'],
                        'suffix' => $myvalues['suffix'],
                        'prefemail' => $myvalues['prefemail'],
                        'prefphone' => $myvalues['prefphone'],
                        'accountactive_yn' => $myvalues['accountactive_yn'],

                        'CEUA1' => $this->m_oContext->valForNullOrMissing($myvalues, 'CEUA1'),
                        'LACE1' => $this->m_oContext->valForNullOrMissing($myvalues, 'LACE1'),
                        
                        'SWI1' => $this->m_oContext->valForNullOrMissing($myvalues, 'SWI1'),
                        'PWI1' => $this->m_oContext->valForNullOrMissing($myvalues, 'PWI1'),
                        'APWI1' => $this->m_oContext->valForNullOrMissing($myvalues, 'APWI1'),
                        'SUWI1' => $this->m_oContext->valForNullOrMissing($myvalues, 'SUWI1'),
                        'CE1' => $this->m_oContext->valForNullOrMissing($myvalues, 'CE1'),
                        'QA1' => $this->m_oContext->valForNullOrMissing($myvalues, 'QA1'),
                        'QA2' => $this->m_oContext->valForNullOrMissing($myvalues, 'QA2'),
                        'SP1' => $this->m_oContext->valForNullOrMissing($myvalues, 'SP1'),
                        'VREP1' => $this->m_oContext->valForNullOrMissing($myvalues, 'VREP1'),
                        'VREP2' => $this->m_oContext->valForNullOrMissing($myvalues, 'VREP2'),

                        'EBO1' => $this->m_oContext->valForNull($myvalues['EBO1']),
                        'UNP1' => $this->m_oContext->valForNull($myvalues['UNP1']),
                        'REP1' => $this->m_oContext->valForNull($myvalues['REP1']),
                        'DRA1' => $this->m_oContext->valForNull($myvalues['DRA1']),
                        'ELCO1' => $this->m_oContext->valForNull($myvalues['ELCO1']),
                        'ELHO1' => $this->m_oContext->valForNull($myvalues['ELHO1']),
                        'ELSO1' => $this->m_oContext->valForNull($myvalues['ELSO1']),
                        'ELSVO1' => $this->m_oContext->valForNull($myvalues['ELSVO1']),
                        'ELRO1' => $this->m_oContext->valForNull($myvalues['ELRO1']),
                        'ECIR1' => $this->m_oContext->valForNull($myvalues['ECIR1']),
                        'EECC1' => $this->m_oContext->valForNull($myvalues['EECC1']),
                        'EERL1' => $this->m_oContext->valForNull($myvalues['EERL1']),
                        'EARM1' => $this->m_oContext->valForNull($myvalues['EARM1']),
                        'CUT1' => $this->m_oContext->valForNull($myvalues['CUT1']),

                        'updated_dt' => $updated_dt,
                        ])
            ->condition('username',$myvalues['username'],'=')
            ->execute();
            if($nUpdated !== 1)
            {
                throw new \Exception('Expected to update 1 record but updated '
                        .$nUpdated.' instead!');
            }

            //Now write all the child records.
            $this->m_oPageHelper->writeChildRecords($myvalues);

            //Success if we are here
            $msg = 'Saved updates for '.$myvalues['username'].' ('.$myvalues['role_nm'].')';
            drupal_set_message($msg);
        } catch (\Exception $ex) {
            $nUpdated = FALSE;
            $msg = 'Failed saved updates for '.$myvalues['username'].' ('.$myvalues['role_nm'].')';
            drupal_set_message($msg,'error');
            error_log($msg.' because '.$ex->getMessage()
                    ."\nData Details..."
                    .print_r($myvalues,TRUE));
        }
        
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
    public function getAllOptions()
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

        //Replace the intro blurb
        $form['data_entry_area1']['introblurb'] = NULL;        
        
        if (!$oUserInfo->isSiteAdministrator()) {
            //die('LOOK>>>>sa='.$oUserInfo->isSiteAdministrator().'<br>'.print_r($oUserInfo,TRUE));
            $form['data_entry_area1']['leftpart']['password'] = NULL;
        }

        //Do NOT let them change the role or name of an existing user!!!!
        $form['data_entry_area1']['leftpart']['role_nm']['#disabled'] = TRUE;
        $form['data_entry_area1']['leftpart']['username']['#disabled'] =  TRUE;
        
        $nSelfUID = $oUserInfo->getUserID();
        if(isset($myvalues['uid']))
        {
            $editself = ($nSelfUID == $myvalues['uid']);
        } else {
            $editself = FALSE;
        }
        
        if($editself)
        {
            //Don't allow a user to disable their own account.
            $form['data_entry_area1']['rightpart']['accountactive_yn']['#disabled'] = TRUE;
            
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

        global $base_url;
        $gobacktoURL = $this->getGobacktoFullURL();
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Cancel"'
                . ' data-redirect="'.$gobacktoURL.'">');

        return $form;
    }
}
