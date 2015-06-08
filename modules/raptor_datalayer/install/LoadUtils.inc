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

/**
 * This class is for database schema content
 *
 * author Frank Font of SAN Business Consultants
 */
class LoadUtils
{

    public static function getFileAsArray($sFilename)
    {
        $sPath = RAPTOR_DATALAYER_MODULE_PATH.'/install/'.$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $aLines;
    }    

    public static function loadAboutBasedata()
    {
        $created_dt = date("Y-m-d H:i:s", time());
        $inserted = db_insert('raptor_about')
           ->fields(array(
            'category_nm' => 'DBSchemaVersion',
            'info_tx' => 'Initial installation',
            'major_ct' => 2, //UPDATE THIS VALUE IF THE SCHEMA CHANGES!!!!!!
            'minor_ct' => 4, //UPDATE THIS VALUE IF THE SCHEMA CHANGES!!!!!!
            'created_dt' => $created_dt,
            ))
           ->execute();
    }
    
    public static function loadUserBasedata($clearusers=FALSE)
    {
        $aLines = LoadUtils::getFileAsArray('load_demousers.raw');
        $aNonAdminUsernames = array();
        $aAdminUsernames = array();
        $aResetUsernames = array();
        $aTables=array();
        $sTablename=NULL;
        $nRows = NULL;
        $aData=NULL;
        foreach($aLines as $row)
        {
            if(substr($row,0,13) == '>>>tablename=')
            {
                $sTablename = substr($row,13);
                $aTables[] = $sTablename;
            } else
            if(substr($row,0,8) == '>>>rows=')
            {
                $nRows = intval(substr($row,8));
            } else
            if($nRows !== NULL && $nRows > 0 && substr($row,0,8) == '>>>data=')
            {
                $aData = unserialize(substr($row,8));
                if($sTablename == 'raptor_user_profile')
                {
                    foreach($aData as $aRow)
                    {
                        $username = trim($aRow->username);
                        $role_nm = trim($aRow->role_nm);
                        $olduid = $aRow->uid;
                        if($role_nm !== 'Site Administrator')
                        {
                            $aNonAdminUsernames[] = $username;
                        } else {
                            $aAdminUsernames[] = $username;
                        }
                        if($username != 'admin' && $olduid != 1)    //DO NOT INCLUDE THE CORE ADMIN ACCOUNT
                        {
                            $aResetUsernames[] = $username;
                        }
                    }
                }
            }
        }
        if($clearusers)
        {
            db_delete('raptor_user_profile')
                    ->execute();
            db_delete('raptor_user_modality')
                    ->execute();
            db_delete('raptor_user_modality_override')
                    ->execute();
            db_delete('raptor_user_anatomy')
                    ->execute();
            db_delete('raptor_user_anatomy_override')
                    ->execute();
            db_delete('raptor_user_group_membership')
                    ->execute();
            db_delete('raptor_user_service')
                    ->execute();
            //Now clear all the NON-ADMIN users from the drupal users table too
            foreach($aResetUsernames as $username)
            {
                //drupal_set_message("about to delete user " . $username);
                db_delete('users')
                        ->condition('name',$username,'=')
                        ->execute();
            }
        }
        db_insert('raptor_user_profile')->fields(array(
            'uid' => 1,
            'username' => 'admin',
            'firstname' => 'Site',
            'lastname' => 'Admin',
            'role_nm' => 'Site Administrator',
            'prefemail' => 'raptordefaultadmin@talkecho.com',
            'CEUA1' => 1,
            'LACE1' => 1,
            'SWI1' => 0,
            'PWI1' => 0,
            'APWI1' => 0,
            'SUWI1' => 1,
            'CE1' => 0,
            'QA1' => 0,
            'QA2' => 0,
            'SP1' => 1,
            'VREP1' => 1,
            'VREP2' => 1,
            'EBO1' => 1,
            'UNP1' => 1,
            'REP1' => 1,
            'DRA1' => 1,
            'ELCO1' => 1,
            'ELHO1' => 1,
            'ELSO1' => 1,
            'ELSVO1' => 1,
            'ELRO1' => 1,
            'ECIR1' => 1,
            'EECC1' => 1,
            'EERL1' => 1,
            'EARM1' => 1,
            'CUT1' => 1,
            'updated_dt' => date("Y-m-d H:i:s", time()),
          ))->execute();

        //Now load all the others if any exist.
        $sTablename = NULL;
        $nRows=NULL;
        $aData=NULL;
        $aUIDMap = array();
        foreach($aLines as $row)
        {
            if(substr($row,0,13) == '>>>tablename=')
            {
                $sTablename = substr($row,13);
            } else
            if(substr($row,0,8) == '>>>rows=')
            {
                $nRows = intval(substr($row,8));
            } else
            if($nRows !== NULL && $nRows > 0 && substr($row,0,8) == '>>>data=')
            {
                $aData = unserialize(substr($row,8));

                if($sTablename == 'raptor_user_profile')
                {
                    foreach($aData as $aRow)
                    {
                        //Get the UID and USERNAME
                        $newuid = NULL;
                        $olduid = $aRow->uid;
                        $username = trim($aRow->username);
                        $role_nm = trim($aRow->role_nm);
                        $is_siteadmin = ($role_nm !== 'Site Administrator');

                        //Find or Create a DRUPAL user with matching USERNAME
                        $result = db_select('users','u')
                                ->fields('u')
                                ->condition('name',$username,'=')
                                ->execute();
                        $nGot = $result->rowCount();
                        if($nGot == 1)
                        {
                            //Use the existing UID.
                            $record = $result->fetchAssoc();
                            $newuid = $record['uid'];
                        } else {
                            if($nGot > 1)
                            {
                                //Something is not right here, ignore this user.
                                $msg = 'Unable to create raptor user ['.$username.'] because found that name ' . $nGot .' in the users table!';
                                drupal_set_message($msg,'error');
                                error_log('INSTALLATION ERROR!!!!!!>>>>'.$msg);

                            } else {
                                //Create the user in Drupal and grab that UID.
                                $newuser = NULL;
                                try
                                {
                                    $newUserInfo = array(
                                        'is_new' => TRUE,
                                        'name' => $username,
                                        'pass' => ($is_siteadmin ? 'apass' : 'BOGUS123123123B'), // note: do not md5 the password
                                        'mail' => 'my email',
                                        'status' => 1,
                                        'init' => 'my init'
                                        );
                                    $newuser = user_save(null, $newUserInfo, 'RAPTOR_USER');
                                    if($newuser == FALSE)
                                    {
                                        error_log('Unable to create DRUPAL user ' . print_r($newUserInfo,TRUE));
                                        drupal_set_message('Unable to create raptor user '.$username.'!');
                                    }
                                }
                                catch(\Exception $e)
                                {
                                    error_log('Failed to add DRUPAL user: ' . $e . "\nDetails..." . print_r($newUserInfo,true));
                                    drupal_set_message('Unable to create raptor user '.$username.' because ' . $e->getMessage());
                                }
                                $newuid = $newuser->uid;
                            }
                        }

                        //Record the mapping of old uid to new uid.
                        $aUIDMap[$olduid] = $newuid;

                        //Now create the raptor user profile record.
                        $fields = array();
                        foreach($aRow as $fieldname => $value)
                        {
                            if($fieldname == 'uid')
                            {
                                $value = $newuid;
                            }
                            $fields[$fieldname] = $value;
                        }
                        $inserted = db_insert('raptor_user_profile')
                                    ->fields($fields)
                                    ->execute();
                    }
                } else if($sTablename !== NULL){
                    $newuid = NULL;
                    $fields = array();
                    foreach($aData as $aRow)
                    {
                        foreach($aRow as $fieldname => $value)
                        {
                            if($fieldname == 'uid')
                            {
                                $newuid =  $aUIDMap[$value];
                                $value = $newuid;
                            }
                            $fields[$fieldname] = $value;
                        }
                        if(isset($newuid) && $newuid !== NULL)
                        {
                            $inserted = db_insert($sTablename)
                                        ->fields($fields)
                                        ->execute();
                        }
                    }                
                }
                $sTablename = NULL; //Now clear it.
            }
        }
        drupal_set_message('Loaded '.count($aTables).' user account tables<ol><li>'.implode('<li>',$aTables).'</ol>');
    }
    
    
    public static function loadProtocolLibBasedata($clearall=FALSE)
    {
        $aLines = LoadUtils::getFileAsArray('load_demoprotocollib.raw');
        $aProtocols=array();
        $aTables=array();
        $sTablename=NULL;
        $nRows = NULL;
        $aData=NULL;
        foreach($aLines as $row)
        {
            if(substr($row,0,13) == '>>>tablename=')
            {
                $sTablename = substr($row,13);
                $aTables[] = $sTablename;
            } else
            if(substr($row,0,8) == '>>>rows=')
            {
                $nRows = intval(substr($row,8));
            } else
            if($nRows !== NULL && $nRows > 0 && substr($row,0,8) == '>>>data=')
            {
                $aData = unserialize(substr($row,8));
                if($sTablename == 'raptor_protocol_lib')
                {
                    foreach($aData as $aRow)
                    {
                        $protocol_shortname = trim($aRow->protocol_shortname);
                        $aProtocols[] = $protocol_shortname;
                    }
                }
            }
        }
        if($clearall)
        {
            //Clear all the tables declared in the file.
            foreach($aTables as $sTable)
            {
                db_delete($sTable)
                        ->execute();
            }
        }

        //Now load all the tables
        $sTablename = NULL;
        $nRows=NULL;
        $aData=NULL;
        foreach($aLines as $row)
        {
            if(substr($row,0,13) == '>>>tablename=')
            {
                $sTablename = substr($row,13);
            } else
            if(substr($row,0,8) == '>>>rows=')
            {
                $nRows = intval(substr($row,8));
            } else
            if($nRows !== NULL && $nRows > 0 && substr($row,0,8) == '>>>data=')
            {
                $aData = unserialize(substr($row,8));
                if($sTablename !== NULL)
                {
                    $newuid = NULL;
                    $fields = array();
                    foreach($aData as $aRow)
                    {
                        foreach($aRow as $fieldname => $value)
                        {
                            $fields[$fieldname] = $value;
                        }
                        $inserted = db_insert($sTablename)
                                    ->fields($fields)
                                    ->execute();
                    }                
                }
                $sTablename = NULL; //Now clear it.
            }
        }
        drupal_set_message('Loaded '.count($aTables).' protocollib tables<ol><li>'.implode('<li>',$aTables).'</ol>');
    }
    
    private static function insert_raptor_checklist_question($question_shortname, $modality,$relative_position,$question_tx,$comment_prompt_tx,$ask_notapplicable_yn)
    {
        $created_dt = date("Y-m-d H:i:s", time());
        $inserted = db_insert('raptor_checklist_question')
           ->fields(array(
            'type_cd' => 'SC',
            'relative_position' => $relative_position,
            'modality_abbr' => $modality,
            'protocol_shortname' => '',
            'question_shortname' => $question_shortname,
            'version' => 1,
            'question_tx' => $question_tx,
            'comment_prompt_tx' => $comment_prompt_tx,
            'ask_notapplicable_yn' => $ask_notapplicable_yn,
            'created_dt' => $created_dt,
            ))
           ->execute();
    }
    
    public static function loadChecklistBasedata()
    {
        
        $modality='';
        $relative_position = 10;
        $question_shortname = 'GOT_PATIENT';
        $question_tx = 'Correct patient?';
        $comment_prompt_tx='Explain why the patient identity cannot be confirmed as appropriate for the procedure.';
        $ask_notapplicable_yn=0;
        LoadUtils::insert_raptor_checklist_question($question_shortname,$modality, $relative_position, $question_tx, $comment_prompt_tx, $ask_notapplicable_yn);
    
        $modality='';
        $relative_position = 12;
        $question_shortname = 'GOT_IMG_SITE';
        $question_tx = 'Confirmed imaging site?';
        $comment_prompt_tx='Explain why the imaging site cannot be confirmed.';
        $ask_notapplicable_yn=0;
        LoadUtils::insert_raptor_checklist_question($question_shortname,$modality, $relative_position, $question_tx, $comment_prompt_tx, $ask_notapplicable_yn);
        
        $modality='';
        $relative_position = 14;
        $question_shortname = 'SET_PAT_POSITION';
        $question_tx = 'Correct patient positioning?';
        $comment_prompt_tx='Explain why the correct positioning cannot be answered as Yes.';
        $ask_notapplicable_yn=0;
        LoadUtils::insert_raptor_checklist_question($question_shortname,$modality, $relative_position, $question_tx, $comment_prompt_tx, $ask_notapplicable_yn);

        $modality='CT';
        $relative_position = 16;
        $question_shortname = 'GOT_IMG_PROTOCOL';
        $question_tx = 'Correct imaging protocol?';
        $comment_prompt_tx='Explain why the imaging protocol does not appear to be correct and what action you will take.';
        $ask_notapplicable_yn=1;
        LoadUtils::insert_raptor_checklist_question($question_shortname,$modality, $relative_position, $question_tx, $comment_prompt_tx, $ask_notapplicable_yn);

        $modality='CT';
        $relative_position = 18;
        $question_shortname = 'SET_SCNR_PARAMS';
        $question_tx = 'Correct scanner parameters?';
        $comment_prompt_tx='Explain why you cannot confirm the scanner parameters are correct for this procedure.';
        $ask_notapplicable_yn=1;
        LoadUtils::insert_raptor_checklist_question($question_shortname,$modality, $relative_position, $question_tx, $comment_prompt_tx, $ask_notapplicable_yn);
    }
    
    public static function loadQACriteriaBasedata()
    {
        $updated_dt = date("Y-m-d H:i:s", time());
        db_insert('raptor_qa_criteria')
                ->fields(array(
                'context_cd' => 'T',
                'version' => 1,
                'position' => 1,
                'shortname' => 'SAMPLE_QA_QUESTION1',
                'question' => 'QA Evaluation Criteria Item1',
                'explanation' => 'This is a sample evaluation criteria',
                'updated_dt' => $updated_dt,
          ))->execute();
        
        db_insert('raptor_qa_criteria')
                ->fields(array(
            'context_cd' => 'T',
            'version' => 1,
            'position' => 2,
            'shortname' => 'SAMPLE_QA_QUESTION2',
            'question' => 'QA Evaluation Criteria Item2',
            'explanation' => 'This is a sample evaluation criteria',
            'updated_dt' => $updated_dt,
          ))->execute();
		
        db_insert('raptor_qa_criteria')
                ->fields(array(
            'context_cd' => 'T',
            'version' => 1,
            'position' => 3,
            'shortname' => 'SAMPLE_QA_QUESTION3',
            'question' => 'QA Evaluation Criteria Item3',
            'explanation' => 'This is a sample evaluation criteria',
            'updated_dt' => $updated_dt,
          ))->execute();
        
    }
    
    
}

