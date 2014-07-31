<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140725
 */


//define('MY_MODULE_PATH', drupal_get_path('module', 'raptor_datalayer')); 
namespace raptor;

/**
 * This class is for Ticket database schema content
 *
 * @author FrankWin7VM
 */
class LoadUtils
{

    public static function getFileAsArray($sFilename)
    {
        $sPath = MY_MODULE_PATH.'/install/'.$sFilename;
        $aLines = file($sPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $aLines;
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
}

