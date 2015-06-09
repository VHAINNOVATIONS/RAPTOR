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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'core/data_user');
module_load_include('inc', 'raptor_glue', 'functions/useradmin_ajax');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
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
        //Set the initial values
        $myvalues = array();
        $myvalues['uid'] = $nUID;
        $myvalues['username'] = '';
        $myvalues['setpassword'] = null;
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
        $myvalues['QA2'] = 0;
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

        $myvalues['lockCEUA1'] = 0;
        $myvalues['lockLACE1'] = 0;
        $myvalues['lockSWI1'] = 0;
        $myvalues['lockPWI1'] = 0;
        $myvalues['lockAPWI1'] = 0;
        $myvalues['lockSUWI1'] = 0;
        $myvalues['lockCE1'] = 0;
        $myvalues['lockQA1'] = 0;
        $myvalues['lockQA2'] = 0;
        $myvalues['lockSP1'] = 0;
        $myvalues['lockVREP1'] = 0;
        $myvalues['lockVREP2'] = 0;
        $myvalues['lockEBO1'] = 0;
        $myvalues['lockUNP1'] = 0;
        $myvalues['lockREP1'] = 0;
        $myvalues['lockDRA1'] = 0;
        $myvalues['lockELCO1'] = 0;
        $myvalues['lockELHO1'] = 0;
        $myvalues['lockELSO1'] = 0;
        $myvalues['lockELSVO1'] = 0;
        $myvalues['lockELRO1'] = 0;
        $myvalues['lockEECC1'] = 0;
        $myvalues['lockECIR1'] = 0;
        $myvalues['lockEERL1'] = 0;
        $myvalues['lockEARM1'] = 0;
        $myvalues['lockCUT1'] = 0;
        
        $myvalues['userpref_modality'] = array();
        $myvalues['specialist_modality'] = array();

        $myvalues['userpref_keywords1'] = array();
        $myvalues['userpref_keywords2'] = array();
        $myvalues['userpref_keywords3'] = array();
        $myvalues['specialist_keywords1'] = array();
        $myvalues['specialist_keywords2'] = array();
        $myvalues['specialist_keywords3'] = array();
        
        //Now lookup real values?
        if($nUID !== NULL)
        {
            //Get the real values now if we have them.
            $filter = array(":uid" => $nUID);
            $result = db_query('SELECT username, role_nm, usernametitle'
                    . ', firstname, lastname, suffix'
                    . ', prefemail, prefphone, accountactive_yn'
                    . ', `CEUA1`, `LACE1`, `SWI1`, `PWI1`'
                    . ', `APWI1`, `SUWI1`, `CE1`, `QA1`, `QA2`, `SP1`'
                    . ', `VREP1`, `VREP2`'
                    . ', `EBO1`, `UNP1`, `REP1`, `DRA1`'
                    . ', `ELCO1`, ELHO1, `ELSO1`, `ELRO1`, `ELSVO1`'
                    . ', `ECIR1`, `EECC1`, `EERL1`, `EARM1`, `CUT1` '
                    . ' FROM raptor_user_profile'
                    . ' WHERE uid = :uid', $filter);
            //We might be here AFTER the row was deleted.
            if($result->rowCount() != 0)
            {
                //Not deleted yet.
                $record = $result->fetchObject();

                $myvalues['username'] = $record->username;
                $myvalues['setpassword'] = NULL;
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
                $myvalues['QA2'] = $record->QA2;
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
                $modality_result = db_query('SELECT modality_abbr, specialist_yn'
                        . ' FROM raptor_user_modality'
                        . ' WHERE uid = :uid', $filter);
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

                $keyword_result = db_query('SELECT weightgroup, keyword'
                        . ', specialist_yn'
                        . ' FROM raptor_user_anatomy'
                        . ' WHERE uid = :uid', $filter);
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

    public function validateModality($myvalues)
    {
        $errors = array();
        if(isset($myvalues['userpref_modality']) && isset($myvalues['specialist_modality']))
        {
            //Make sure they are not declared as specialists in areas that they do not review.
            $userpref_modality = $myvalues['userpref_modality'];
            $specialist_modality = $myvalues['specialist_modality'];
            foreach($specialist_modality as  $k=>$v)
            {
                if($k === $v)   //MUST use TRIPLE EQUAL here!!!!
                {
                    //This checkbox is checked.
                    if(!isset($userpref_modality[$k])
                            || $userpref_modality[$k] !== $v)
                    {
                        $errors[] = $k; // . ' v='.$v;
                    }
                }
            }
            if(count($errors) > 0)
            {
                form_set_error('specialist_modality'
                        ,'Cannot advertise collaboration in'
                        . ' modalities that are not also worklist preferences, see ' 
                        . implode(', ',$errors));
            }
        }
        return count($errors) == 0;
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
        
        if(isset($myvalues['specialist_modality']) && is_array($myvalues['specialist_modality']))
        {
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
            $this->writeKeywords($myvalues['uid'], 1
                    , $userpref_keywords1, 0, $updated_dt);
            $specialist_keywords1 = explode(',',$myvalues['specialist_keywords1']);
            $this->writeKeywords($myvalues['uid'], 1
                    , $specialist_keywords1, 1, $updated_dt);

            $userpref_keywords2 = explode(',',$myvalues['userpref_keywords2']);
            $this->writeKeywords($myvalues['uid'], 2
                    , $userpref_keywords2, 0, $updated_dt);
            $specialist_keywords2 = explode(',',$myvalues['specialist_keywords2']);
            $this->writeKeywords($myvalues['uid'], 2
                    , $specialist_keywords2, 1, $updated_dt);

            $userpref_keywords3 = explode(',',$myvalues['userpref_keywords3']);
            $this->writeKeywords($myvalues['uid'], 3
                    , $userpref_keywords3, 0, $updated_dt);
            $specialist_keywords3 = explode(',',$myvalues['specialist_keywords3']);
            $this->writeKeywords($myvalues['uid'], 3
                    , $specialist_keywords3, 1, $updated_dt);
        }
    }

    public function checkAllowedToViewUser($oContext, $nTargetUID, $throw_exception=TRUE)
    {
        //Verify that the current user is allowed to view this profile
        if($oContext->getUID() != $nTargetUID)
        {
            $userinfo = $oContext->getUserInfo();
            $userprivs = $userinfo->getSystemPrivileges();
            $okayedit=TRUE;
            $targetrole=UserInfo::getRoleOfUser($nTargetUID);
            if($userprivs['CEUA1'] != 1)    //Must NOT use the more rigorous "!==" check!
            {
                $okayedit=FALSE;
                //Take the check to the next level
                if($userprivs['LACE1'] == 1 && $targetrole == 'Resident')
                {
                    $okayedit=TRUE;
                }
            }
            if(!$okayedit)
            {
                if(!$throw_exception)
                {
                    return FALSE;
                } else {
                    throw new \Exception('Unauthorized attempt to view ('
                            .$targetrole.')'
                            . ' uid='.$nTargetUID.' by '
                            .$userinfo->getUserID()
                            .':'.$userinfo->getUserName()
                            ."<br>\nPrivs=".print_r($userprivs,TRUE));
                }
            }
        }
        return TRUE;
    }
    
    public function checkAllowedToAddUser($oContext, $targetrole, $throw_exception=TRUE)
    {
        //Verify that the current user is allowed to edit this profile
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $okayedit=TRUE;
        if($userprivs['CEUA1'] != 1)    //Must NOT use the more rigorous "!==" check!
        {
            $okayedit=FALSE;
            //Take the check to the next level
            if($userprivs['LACE1'] == 1 && $targetrole == 'Resident')
            {
                $okayedit=TRUE;
            }
        }
        if(!$okayedit)
        {
            if(!$throw_exception)
            {
                return FALSE;
            } else {
                throw new \Exception('Unauthorized attempt to add ('
                        .$targetrole.') by '
                        .$userinfo->getUserID()
                        .':'.$userinfo->getUserName()
                        ."<br>\nPrivs=".print_r($userprivs,TRUE));
            }
        }
        return TRUE;
    }
    
    public function checkAllowedToEditUser($oContext, $nTargetUID, $throw_exception=TRUE)
    {
        //Verify that the current user is allowed to edit this profile
        if($oContext->getUID() != $nTargetUID)
        {
            $userinfo = $oContext->getUserInfo();
            $userprivs = $userinfo->getSystemPrivileges();
            $okayedit=TRUE;
            $targetrole=UserInfo::getRoleOfUser($nTargetUID);
            if($userprivs['CEUA1'] != 1)    //Must NOT use the more rigorous "!==" check!
            {
                $okayedit=FALSE;
                //Take the check to the next level
                if($userprivs['LACE1'] == 1 && $targetrole == 'Resident')
                {
                    $okayedit=TRUE;
                }
            }
            if(!$okayedit)
            {
                if(!$throw_exception)
                {
                    return FALSE;
                } else {
                    throw new \Exception('Unauthorized attempt to edit ('
                            .$targetrole.')'
                            .' uid='.$nTargetUID.' by '
                            .$userinfo->getUserID()
                            .':'.$userinfo->getUserName()
                            ."<br>\nPrivs=".print_r($userprivs,TRUE));
                }
            }
        }
        return TRUE;
    }

    public function checkAllowedToDeleteUser($oContext, $nTargetUID, $throw_exception=TRUE)
    {
        //Verify that the current user is allowed to delete this profile
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $okayedit=TRUE;
        $targetrole=UserInfo::getRoleOfUser($nTargetUID);
        if(!$userinfo->isSiteAdministrator())
        {
            //Only site admins can delete profiles
            $okayedit=FALSE;    
        } else
        if($userprivs['CEUA1'] != 1)    //Must NOT use the more rigorous "!==" check!
        {
            $okayedit=FALSE;
            //Take the check to the next level
            if($userprivs['LACE1'] == 1 && $targetrole == 'Resident')
            {
                $okayedit=TRUE;
            }
        }
        if(!$okayedit)
        {
            if(!$throw_exception)
            {
                return FALSE;
            } else {
                throw new \Exception('Unauthorized attempt to delete ('
                        .$targetrole.')'
                        .' uid='.$nTargetUID.' by '
                        .$userinfo->getUserID()
                        .':'.$userinfo->getUserName()
                        ."<br>\nPrivs=".print_r($userprivs,TRUE));
            }
        }
        return TRUE;
    }
    
    /**
     * @return array of all option values for the form
     */
    public function getAllOptions($category = 'STANDARD')
    {
        //Get all the role options from a query DEPRECATED!!!!!!!!!!!
        if($category == 'STANDARD')
        {
            $sWhere = "`name` <> 'Site Administrator'";
        } else {
            //$sWhere = "`name` = 'Site Administrator'";
            $sWhere = "`name` = '$category'";
        }
        $sSQL = 'SELECT `roleid`, `enabled_yn`, `name`' 
                . ', `CEUA1`, `lockCEUA1`'
                . ', `LACE1`, `lockLACE1`'
                . ', `SWI1`, `lockSWI1`'
                . ', `PWI1`, `lockPWI1`'
                . ', `APWI1`, `lockAPWI1`'
                . ', `SUWI1`, `lockSUWI1`'
                . ', `CE1`, `lockCE1`'
                . ', `QA1`, `lockQA1` '
                . ', `QA2`, `lockQA2` '
                . ', `VREP1`, `lockVREP1` '
                . ', `VREP2`, `lockVREP2` '
                . ', `SP1`, `lockSP1`, `EBO1`'
                . ', `lockEBO1`, `UNP1`, `lockUNP1`'
                . ', `REP1`, `lockREP1`'
                . ', `DRA1`, `lockDRA1`'
                . ', `ELCO1`, `lockELCO1`'
                . ', `ELHO1`, `lockELHO1`, `ELSO1`, `lockELSO1`'
                . ', `ELSVO1`, `lockELSVO1`, `ELRO1`, `lockELRO1`'
                . ', `ECIR1`, `lockECIR1`, `EECC1`, `lockEECC1`'
                . ', `EERL1`, `lockEERL1`, `EARM1`, `lockEARM1`'
                . ', `CUT1`, `lockCUT1`'
                . ' FROM `raptor_role`'
                . ' WHERE '.$sWhere.''
                . ' ORDER BY `roleid`';
        $role_result = db_query($sSQL);
        if($role_result->rowCount()==0)
        {
            throw new \Exception('Did NOT find any role options for '.$sWhere);
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
                    'QA2' => $item->QA2 ,
                    'lockQA2' => $item->lockQA2 ,
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
        $service_result = db_query('SELECT service_nm'
                . ', `service_desc`'
                . ' FROM `raptor_list_service`'
                . ' ORDER BY service_nm');
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
            $aFormatted['userpref_keywords1'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords1'], ',');
        }
        if(!isset($myvalues['userpref_keywords2']))
        {
            $aFormatted['userpref_keywords2'] = '';
        } else {
            $aFormatted['userpref_keywords2'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords2'], ',');
        }
        if(!isset($myvalues['userpref_keywords3']))
        {
            $aFormatted['userpref_keywords3'] = '';
        } else {
            $aFormatted['userpref_keywords3'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['userpref_keywords3'], ',');
        }
        
        if(!isset($myvalues['specialist_keywords1']))
        {
            $aFormatted['specialist_keywords1'] = '';
        } else {
            $aFormatted['specialist_keywords1'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords1'], ',');
        }
        if(!isset($myvalues['specialist_keywords2']))
        {
            $aFormatted['specialist_keywords2'] = '';
        } else {
            $aFormatted['specialist_keywords2'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords2'], ',');
        }
        if(!isset($myvalues['specialist_keywords3']))
        {
            $aFormatted['specialist_keywords3'] = '';
        } else {
            $aFormatted['specialist_keywords3'] 
                    = FormHelper::getArrayItemsAsDelimitedText($myvalues['specialist_keywords3'], ',');
        }
        return $aFormatted;
    }

    
    /**
     * Only populate the mainarray with values for keys not already there
     */
    static function init_array_keys(&$mainarray, $initvaluesarray)
    {
        foreach($initvaluesarray as $k=>$v)
        {
            if(!array_key_exists($k, $mainarray))
            {
                $mainarray[$k] = $v;
            }
        }
    }    
    
    private function addCheckboxFormAPIElement(&$formnode, $privkey, $label, $myvalues, $disabled)
    {
        $locked = $disabled || $myvalues['lock'.$privkey]==1;
        $title = ($locked ? t($label) : '<strong>'.t($label).'</strong>');
        $formnode[$privkey] = array(
           '#type' => 'checkbox', 
           '#title' => $title,
           '#default_value' => $myvalues[$privkey], 
           '#disabled' => $locked,
        );    
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, &$myvalues_init, $role_nm=NULL)
    {
        //Get values
        if(isset($form_state['values']) && is_array($form_state['values']))
        {
            $myvalues = $form_state['values'];
        } else {
            $myvalues = array();
        }
        if(is_array($myvalues_init))
        {
            UserPageHelper::init_array_keys($myvalues, $myvalues_init);
        }

        //Get role dependent values
        if($role_nm == NULL)
        {
            $role_nm = $myvalues['role_nm'];
            if($role_nm == NULL)
            {
                throw new \Exception('Must declare the role name!');
            }
        } else {
            if(isset($myvalues['role_nm']))
            {
                if($role_nm !== $myvalues['role_nm'])
                {
                    throw new \Exception('role name conflict!  ['.$role_nm . '] vs [' . $myvalues['role_nm'] . ']');
                }
            }
        }
        
        //Ensure we lock the fields that need to be locked.
        $templatevalues = \raptor\UserInfo::getRoleDefaults($role_nm);
        foreach($templatevalues as $k=>$v)
        {
            if(strlen($k) > 4 && substr($k, 0, 4) == 'lock')
            {
                $myvalues[$k] = $v;
            }
        }
        
        $aOptions = $this->getAllOptions($role_nm);
        $aFormattedKeywordText = $this->formatKeywordText($myvalues);

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section id='input-main-values' class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        $form['#attached']['js'] = array(
          drupal_get_path('module', 'raptor_glue') . '/js/userPageHelper.js',
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
        
        $form['data_entry_area1']['introblurb'] = array(
            '#type'     => 'fieldset',
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['introblurb']['text'] = array(
            '#markup' => '<p>This is an intro blurb</p>',
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
        $is_site_admin = UserInfo::isRoleSiteAdministrator($role_nm);
        $is_collaborator = UserInfo::isRoleCollaborationCandidate($role_nm);
        
        $form['data_entry_area1']['leftpart']['role_nm'] = array(
          '#type' => 'textfield', 
          '#title' => t('Role'), 
          '#value' => $role_nm, 
          '#size' => 40, 
          '#disabled' => TRUE,  //DO NOT ALLOW EDIT
        );        
            
        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#default_value' => trim($myvalues['username']), 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#disabled' => $disabled,
          '#attributes' => array('autocomplete' => 'off'),
        );        
        if($is_site_admin)
        {
            $form['data_entry_area1']['leftpart']['username']['#description'] 
                    =  t('The login name of this system admin user.');

            //Only prompt for password if form is not disabled.
            if(!$disabled)
            {
                $form['data_entry_area1']['leftpart']['setpassword'] = array(
                  '#type' => 'password_confirm', 
                  '#prefix' => '<div id="xxxedit-password">',
                  '#suffix' => '</div>',
                  '#size' => 40, 
                  '#maxlength' => 128, 
                  '#required' => TRUE,
                  '#description' => t('The password for this account.  Pick a strong password and do not share it.'),
                  '#disabled' => $disabled,
                  '#attributes' => array('autocomplete' => 'off'),
                );        
            }
        } else {
            $form['data_entry_area1']['leftpart']['username']['#description'] 
                    =  t('The login name of the user.  This must match their VISTA login name.'.$myvalues['role_nm']);
        }
        
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
          '#size' => 40, 
          '#maxlength' => 50, 
          '#required' => TRUE,
          '#description' => t('First name for this user'),
          '#disabled' => $disabled,
        );        
        
        $form['data_entry_area1']['leftpart']['lastname'] = array(
          '#type' => 'textfield', 
          '#title' => t('Last name'), 
          '#default_value' => $myvalues['lastname'], 
          '#size' => 40, 
          '#maxlength' => 50, 
          '#required' => TRUE,
          '#description' => t('Last name for this user'),
          '#disabled' => $disabled,
        );        

        $form['data_entry_area1']['leftpart']['suffix'] = array(
          '#type' => 'textfield', 
          '#title' => t('Suffix'), 
          '#default_value' => $myvalues['suffix'], 
          '#size' => 15, 
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
          '#size' => 50, 
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

        //Only populate rest of form if a role has been selected
        $is_role_selected = TRUE;
        if($is_role_selected)
        {
            //TICKET MANAGEMENT
            if($is_site_admin)
            {
               //Fewer options for site administrators 
            } else {
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
                
                $ticprivs = array();
                $ticprivs[] = array('SWI1','Select worklist items'); 
                $ticprivs[] = array('PWI1','Can protocol a ticket'); 
                $ticprivs[] = array('APWI1','Can approve a protocol'); 
                $ticprivs[] = array('SUWI1','Can cancel a ticket'); 
                $ticprivs[] = array('CE1','Can complete an exam'); 
                $ticprivs[] = array('QA1','Can QA an exam'); 
                $ticprivs[] = array('SP1','Can edit pass box'); 
                foreach($ticprivs as $ticpriv)
                {
                    $this->addCheckboxFormAPIElement(
                            $form['data_entry_area1']['ticketmgtprivileges']
                            ,$ticpriv[0]
                            , $ticpriv[1]
                            , $myvalues
                            , $disabled);
                }
            }

            //ACCOUNT MANAGEMENT
            unset($form['data_entry_area1']['accountmgtprivileges']['CEUA1']);
            unset($form['data_entry_area1']['accountmgtprivileges']['LACE1']);
            unset($form_state['input']['CEUA1']);
            unset($form_state['input']['LACE1']);
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
            $actprivs = array();
            $actprivs[] = array('CEUA1','Add/Edit Any User Accounts'); 
            $actprivs[] = array('LACE1','Add/Edit Resident User Accounts'); 
            foreach($actprivs as $actpriv)
            {
                $this->addCheckboxFormAPIElement(
                        $form['data_entry_area1']['accountmgtprivileges']
                        ,$actpriv[0]
                        , $actpriv[1]
                        , $myvalues
                        , $disabled);
            }
            $form['data_entry_area1']['sitewideview_config'] = array(
                '#type'     => 'fieldset',
                '#title'    => t('Sitewide View Privileges'),
                '#attributes' => array(
                    'class' => array(
                        'data-entry1-area'
                    )
                 ),
                '#disabled' => $disabled,
            );
            $swprivs = array();
            $swprivs[] = array('VREP1','Can view department activity reports'); 
            $swprivs[] = array('VREP2','Can view user activity reports'); 
            $swprivs[] = array('QA2','Can view all QA results'); 
            foreach($swprivs as $swpriv)
            {
                $this->addCheckboxFormAPIElement(
                        $form['data_entry_area1']['sitewideview_config']
                        ,$swpriv[0]
                        , $swpriv[1]
                        , $myvalues
                        , $disabled);
            }

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
            $swprivs = array();
            //$swprivs[] = array('VREP1','Can view department activity reports'); 
            //$swprivs[] = array('VREP2','Can view user activity reports'); 
            //$swprivs[] = array('QA2','Can view all QA results'); 
            $swprivs[] = array('EBO1','Can edit boilerplate text'); 
            $swprivs[] = array('UNP1','Can upload protocols'); 
            $swprivs[] = array('REP1','Can retire protocols'); 
            $swprivs[] = array('DRA1','Can define default attributes of roles'); 
            $swprivs[] = array('ELCO1','Can edit contrast options'); 
            $swprivs[] = array('ELHO1','Can edit hydration options'); 
            $swprivs[] = array('ELSO1','Can edit list of sedation options'); 
            $swprivs[] = array('ELSVO1','Can edit list of service options'); 
            $swprivs[] = array('ELRO1','Can edit radionuclide options'); 
            $swprivs[] = array('ECIR1','Can edit contraindication results'); 
            $swprivs[] = array('EECC1','Can edit excluded CPRS metadata'); 
            $swprivs[] = array('EERL1','Can edit examination room list'); 
            $swprivs[] = array('EARM1','Can edit the list of at risk medication keywords'); 
            $swprivs[] = array('CUT1','Can edit umbrella terms'); 
            foreach($swprivs as $swpriv)
            {
                $this->addCheckboxFormAPIElement(
                        $form['data_entry_area1']['sitewideconfig']
                        ,$swpriv[0]
                        , $swpriv[1]
                        , $myvalues
                        , $disabled);
            }
            
            //WORKLIST PREFS
            if($is_site_admin)
            {
               //Fewer options for site administrators
            } else {
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

                if($is_collaborator)
                {
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
                }
            }
        }

        return $form;
    }
}
