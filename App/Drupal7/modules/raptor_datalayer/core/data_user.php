<?php
/**
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

require_once ("data_utility.php");

/**
 * The context has all the details relevant to a user
 *
 * @author SAN
 */
class UserInfo 
{
    
    protected $m_nUID = null;
    protected $m_oData = null;
    protected $m_oPrivs = null;
    protected $m_aPreferredModality = null;
    protected $m_aPreferredModalityOverrides = null;
    protected $m_bFoundPreferredModalityOverrides = NULL;
    protected $m_aSpecialistModality = null;
    protected $m_aPreferredService = null;
    protected $m_aSpecialistService = null;
    protected $m_aPreferredAnatomy = null;
    protected $m_aPreferredAnatomyOverrides = null;
    protected $m_bFoundPreferredAnatomyOverrides = NULL;
    protected $m_aSpecialistAnatomy = null;
    protected $m_aGroups = null;
    
    protected $m_bFailIfNotFound = NULL;
    
    function __construct($nUID,$bFailIfNotFound=TRUE)
    {
        if(!isset($nUID) || !is_numeric($nUID))
        {
            throw new \Exception("Missing or invalid uid value = " . $nUID);
        }
        $this->m_nUID = $nUID;
        $this->m_bFailIfNotFound = $bFailIfNotFound;
        //$this->m_oPageHelper = new \raptor\UserPageHelper();
    }
    
    public function getUserID()
    {
        $this->readData();
        return $this->m_oData['uid'];
    }
    
    public function getUserName()
    {
        $this->readData();
        return $this->m_oData['username'];
    }

    public function isEnabled()
    {
        $this->readData();
        return ($this->m_oData['accountactive_yn'] == 1);
    }

    public static function userIsReferenced($nUID)
    {
        $oMe = new UserInfo($nUID);
        return $oMe->isReferenced();
    }
    
    /**
     * Return TRUE if this user is referenced for example as a collaborator or 
     * identified as creator of a ticket etc.
     */
    public function isReferenced()
    {
        //TODO --- Check ALL the relevant tables, not just ticket.
        $sSQL = 'SELECT 1 FROM `raptor_ticket_workflow_history` WHERE initiating_uid = :uid';
        $filter = array(":uid" => $this->m_nUID);
        $result = db_query($sSQL, $filter);
        if($result->rowCount()!=0)
        {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    public function getRoleName()
    {
        $this->readData();
        return $this->m_oData['role_nm'];
    }
    
    public function isSiteAdministrator()
    {
        $role_nm = $this->getRoleName();
        return ($role_nm == 'Site Administrator');
    }

    public function getUserNameTitle()
    {
        $this->readData();
        return $this->m_oData['usernametitle'];
    }
    
    /**
     * @return string of name to show in the reports and pages
     */
    public function getFullName()
    {
        $this->readData();
        return trim($this->m_oData['usernametitle'] . ' ' . $this->m_oData['firstname'] . ' ' . $this->m_oData['lastname'] . ' ' . $this->m_oData['suffix']);
    }

    public function getFirstName()
    {
        $this->readData();
        return $this->m_oData['firstname'];
    }

    public function getLastName()
    {
        $this->readData();
        return $this->m_oData['lastname'];
    }

    public function getUserNameSuffix()
    {
        $this->readData();
        return $this->m_oData['suffix'];
    }

    public function getPreferredEmailAddress()
    {
        $this->readData();
        return $this->m_oData['prefemail'];
    }
    
    public function getPreferredPhoneNumber()
    {
        $this->readData();
        return $this->m_oData['prefphone'];
    }

    public function getGroupMemberships()
    {
        $this->readGroupMembershipData();
        return $this->m_aGroups;
    }

    public function hasModalityPreferencesOverrides()
    {
        $this->readModalityData();
        return $this->m_bFoundPreferredModalityOverrides;
    }
    
    public function getModalityPreferencesOverrides()
    {
        $this->readModalityData();
        return $this->m_aPreferredModalityOverrides;
    }
    
    public function getModalityPreferences()
    {
        $this->readModalityData();
        return $this->m_aPreferredModality;
    }

    public function getServicePreferences()
    {
        $this->readServiceData();
        return $this->m_aPreferredService;
    }

    public function hasWeightedAnatomyPreferencesOverrides()
    {
        $this->readKeywordData();
        return $this->m_bFoundPreferredAnatomyOverrides;
    }

    public function getWeightedAnatomyPreferencesOverrides()
    {
        $this->readKeywordData();
        return $this->m_aPreferredAnatomyOverrides;
    }

    public function getWeightedAnatomyPreferences()
    {
        $this->readKeywordData();
        return $this->m_aPreferredAnatomy;
    }

    public function getSpecialistModality()
    {
        $this->readModalityData();
        return $this->m_aSpecialistModality;
    }

    public function getSpecialistService()
    {
        $this->readServiceData();
        return $this->m_aSpecialistService;
    }

    public function getWeightedSpecialistAnatomy()
    {
        $this->readKeywordData();
        return $this->m_aSpecialistModality;
    }
    
    public function getSystemPrivileges()
    {
        $this->readData();
        return $this->m_oPrivs;
    }

    /**
     * Set the names of columns to hide.
     * @param $aHideColumns array of column names
     */
    public function setPrefWorklistColsHidden($aHideColumns)
    {
        if(isset($this->m_nUID) && $this->m_nUID > 0)
        {
            $serializedinfo = serialize($aHideColumns);
            db_update('raptor_user_profile')
                ->fields(array('worklist_cols' => $serializedinfo))
                ->condition('uid', $this->m_nUID, '=')
                ->execute();
        } else {
            $msg = 'Trying to set worklist_cols but there is no UID!';
            error_log($msg);
        }
    }
    
    /**
     * Get the names of columns to hide in the worklist.
     */
    public function getPrefWorklistColsHidden()
    {
        if(isset($this->m_nUID) && $this->m_nUID > 0)
        {
            $result = db_select('raptor_user_profile', 'u')
                        ->fields('u', array('worklist_cols'))
                        ->condition('uid', $this->m_nUID, '=')
                        ->execute();
            $record = $result->fetchAssoc();
            $serializedinfo = $record['worklist_cols'];
            return unserialize($serializedinfo);
        } else {
            error_log('Trying to get worklist_cols but there is no UID!');
        }
    }

    /**
     * All privileges have a code associated with them.
     * @param type $sPrivCode value such as 'PWI1'
     * @return type 1 if YES, 0 if NO.
     */
    public function getPrivilegeSetting($sPrivCode)
    {
        $this->readData();
        return $this->m_oPrivs[$sPrivCode];
    }

    /**
     * Helper for lazy data loading
     */
    protected function readKeywordData()
    {
        if($this->m_aPreferredAnatomy !== null)
        {
            //Already done.
            return;
        }
        $filter = array(":uid" => $this->m_nUID);

        //Get the overide keywords if there are any
        $keyword_result = db_query('SELECT weightgroup, keyword FROM raptor_user_anatomy_override WHERE uid = :uid', $filter);
        $myvalues['override_userpref_keywords1'] = array();
        $myvalues['override_userpref_keywords2'] = array();
        $myvalues['override_userpref_keywords3'] = array();
        $bFoundOverrideKeywords = FALSE;
        if($keyword_result->rowCount()!=0)
        {
            foreach($keyword_result as $item) 
            {
                if($item->weightgroup == 1)
                {
                    $myvalues['override_userpref_keywords1'][] = $item->keyword;
                } else
                if($item->weightgroup == 2)
                {
                    $myvalues['override_userpref_keywords2'][] = $item->keyword;
                } else
                if($item->weightgroup == 3)
                {
                    $myvalues['override_userpref_keywords3'][] = $item->keyword;
                } else {
                    die("Invalid override weightgroup value for uid=" . $nUID);
                }
                $bFoundOverrideKeywords = TRUE;
            }
        }
        $this->m_bFoundPreferredAnatomyOverrides = $bFoundOverrideKeywords;
        
        //Get the keyword information from a query
        $sSQL = 'SELECT weightgroup, keyword, specialist_yn FROM raptor_user_anatomy WHERE uid = :uid';
        $keyword_result = db_query($sSQL, $filter);
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
                }
            }
        }
        $this->m_aPreferredAnatomy = array($myvalues['userpref_keywords1'],$myvalues['userpref_keywords2'],$myvalues['userpref_keywords3']);
        $this->m_aPreferredAnatomyOverrides = array($myvalues['override_userpref_keywords1'],$myvalues['override_userpref_keywords2'],$myvalues['override_userpref_keywords3']);
        $this->m_aSpecialistAnatomy = array($myvalues['specialist_keywords1'],$myvalues['specialist_keywords2'],$myvalues['specialist_keywords3']);
    }
    
    /**
     * Helper for lazy data loading
     */
    protected function readGroupMembershipData()
    {
        if($this->m_aGroups !== null)
        {
            //Already done.
            return;
        }
        $sSQL = 'SELECT `group_nm` FROM `raptor_user_group_membership` WHERE uid = :uid';
        $filter = array(":uid" => $this->m_nUID);
        $modality_result = db_query($sSQL, $filter);
        $myvalues = array();
        if($modality_result->rowCount()!=0)
        {
            foreach($group_result as $item) 
            {
                $myvalues[] = $item->group_nm;
            }
        }
        $this->m_aGroups = $myvalues;
    }
    

    /**
     * Helper for lazy data loading
     */
    protected function readModalityData()
    {
        if($this->m_aPreferredModality !== null)
        {
            //Already done.
            return;
        }
        $filter = array(":uid" => $this->m_nUID);
        
        //Get the override modalities if there are any
        $modality_result = db_query('SELECT modality_abbr FROM raptor_user_modality_override WHERE uid = :uid', $filter);
        $myvalues['override_userpref_modality'] = array();
        $bFoundOverrideModality = FALSE;
        if($modality_result->rowCount()!=0)
        {
            foreach($modality_result as $item) 
            {
                $myvalues['override_userpref_modality'][$item->modality_abbr] = $item->modality_abbr;
                $bFoundOverrideModality = TRUE;
            }
        }
        $this->m_bFoundPreferredModalityOverrides = $bFoundOverrideModality;
        
        //Get the modality information from a query
        $sSQL = 'SELECT modality_abbr, specialist_yn FROM raptor_user_modality WHERE uid = :uid';
        $modality_result = db_query($sSQL, $filter);
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
        $this->m_aPreferredModality = $myvalues['userpref_modality'];
        $this->m_aPreferredModalityOverrides = $myvalues['override_userpref_modality'];
        $this->m_aSpecialistModality = $myvalues['specialist_modality'];
    }
    
    /**
     * Helper for lazy data loading
     */
    protected function readServiceData()
    {
        if($this->m_aPreferredService !== null)
        {
            //Already done.
            return;
        }
        //Get the service information from a query
        $sSQL = 'SELECT service_nm, specialist_yn FROM raptor_user_service WHERE uid = :uid';
        $filter = array(":uid" => $this->m_nUID);
        $service_result = db_query($sSQL, $filter);
        $myvalues['userpref_service'] = array();
        $myvalues['specialist_service'] = array();
        if($service_result->rowCount()!=0)
        {
            foreach($service_result as $item) 
            {
                $myvalues['userpref_service'][$item->service_nm] = $item->service_nm;
                if($item->specialist_yn == 1)
                {
                    $myvalues['specialist_service'][$item->service_nm] = $item->service_nm;
                }
            }
        }
        $this->m_aPreferredService = $myvalues['userpref_service'];
        $this->m_aSpecialistService = $myvalues['specialist_service'];
    }
    
    /**
     * Helper for lazy data loading
     */
    protected function readData($bFailIfMissing=NULL)
    {
        if($bFailIfMissing === NULL)
        {
            $bFailIfMissing = $this->m_bFailIfNotFound;
        }
        
        if($this->m_oData !== null && isset($this->m_oData['username']))
        {
            //Exit if data is already set.
            return;
        }
        
        //Read the values from the database.
        $sSQL = 'SELECT '
                . ' `username`, `role_nm`, `worklist_cols`, `usernametitle`, `firstname`, `lastname`, `suffix`, `prefemail`, `prefphone`, `accountactive_yn`'
                . ' , `CEUA1`, `LACE1`, `SWI1`, `PWI1`, `APWI1`, `SUWI1`, `CE1`, `QA1`, `SP1`, `VREP1`, `VREP2`'
                . ' , `EBO1`, `UNP1`, `REP1`, `DRA1`, `ELCO1`, `ELHO1`, `ELSO1`, `ELSVO1`, `ELRO1`, `ECIR1`, `EECC1`, `EERL1`, `EARM1`, `CUT1`, `updated_dt` '
                . ' FROM raptor_user_profile WHERE uid = :uid';
        $filter = array(":uid" => $this->m_nUID);
        $result = db_query($sSQL, $filter);
        $myvalues = array();
        $myprivs = array();
        if($result->rowCount()==0)
        {
            $errmsg = 'Did NOT find RAPTOR user with uid=[' . $this->m_nUID .']';
            error_log($errmsg);
            if($bFailIfMissing)
            {
                throw new \Exception($errmsg);
            } else {
                $myvalues['uid'] = -1;  //Indicates no data was found.
                $myvalues['username'] = NULL;  //Indicates no data was found.
                $myvalues['role_nm'] = NULL;   //Indicates no data was found.

                $myvalues['usernametitle'] = NULL;
                $myvalues['firstname'] = NULL;
                $myvalues['lastname'] = NULL;
                $myvalues['suffix'] = NULL;
                $myvalues['prefemail'] = NULL;
                $myvalues['prefphone'] = NULL;
                $myvalues['accountactive_yn'] = NULL;
                $myvalues['worklist_cols'] = NULL;
            }
        } else {
            $record = $result->fetchObject();
            $myvalues['uid'] = $this->m_nUID;
            $myvalues['username'] = $record->username;
            $myvalues['role_nm'] = $record->role_nm;
            $myvalues['usernametitle'] = $record->usernametitle;
            $myvalues['firstname'] = $record->firstname;
            $myvalues['lastname'] = $record->lastname;
            $myvalues['suffix'] = $record->suffix;
            $myvalues['prefemail'] = $record->prefemail;
            $myvalues['prefphone'] = $record->prefphone;
            $myvalues['accountactive_yn'] = $record->accountactive_yn;
            $myvalues['worklist_cols'] = $record->worklist_cols;

            //Capture the privileges too.
            $myprivs['CEUA1'] = $record->CEUA1;
            $myprivs['LACE1'] = $record->LACE1;
            $myprivs['SWI1'] = $record->SWI1;
            $myprivs['PWI1'] = $record->PWI1;
            $myprivs['APWI1'] = $record->APWI1;
            $myprivs['SUWI1'] = $record->SUWI1;
            $myprivs['CE1'] = $record->CE1;
            $myprivs['QA1'] = $record->QA1;
            $myprivs['SP1'] = $record->SP1;
            $myprivs['VREP1'] = $record->VREP1;
            $myprivs['VREP2'] = $record->VREP2;

            $myprivs['EBO1'] = $record->EBO1;
            $myprivs['UNP1'] = $record->UNP1;
            $myprivs['REP1'] = $record->REP1;
            $myprivs['DRA1'] = $record->DRA1;
            $myprivs['ELCO1'] = $record->ELCO1;
            $myprivs['ELHO1'] = $record->ELHO1;
            $myprivs['ELSO1'] = $record->ELSO1;
            $myprivs['ELSVO1'] = $record->ELSVO1;
            $myprivs['ELRO1'] = $record->ELRO1;
            $myprivs['ECIR1'] = $record->ECIR1;
            $myprivs['EECC1'] = $record->EECC1;
            $myprivs['EERL1'] = $record->EERL1;
            $myprivs['EARM1'] = $record->EARM1;
            $myprivs['CUT1'] = $record->CUT1;

            $myvalues['updated_dt'] = $record->updated_dt;
        }
        $this->m_oPrivs = $myprivs;
        $this->m_oData = $myvalues;
    }
}
