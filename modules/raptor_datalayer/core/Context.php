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

module_load_include('php', 'raptor_datalayer', 'config/ehr_integration');

require_once 'CustomKeywords.php';
require_once 'UserInfo.php';
require_once 'EhrDao.php';
require_once 'RuntimeResultFlexCache.php';

defined('CONST_NM_RAPTOR_CONTEXT')
    or define('CONST_NM_RAPTOR_CONTEXT', 'RAPTOR150724A');
    //or define('CONST_NM_RAPTOR_CONTEXT', 'RAPTOR150716B');

defined('DISABLE_CONTEXT_DEBUG')
    or define('DISABLE_CONTEXT_DEBUG', TRUE);

/**
 * The context has all the details relevant to the user of the session and their
 * current activities.
 * 
 * NOTE: static members of an object are not serialized.
 *
 * @author SAN
 */
class Context
{
    private $m_nInstanceTimestamp = NULL;           //Gets set when context is instantiated.
    private $m_nLastUpdateTimestamp = NULL;         //Changes when the context changes.
    private $m_nInstanceClearedTimestamp = NULL;
    private $m_nInstanceUserActionTimestamp = NULL;     //Periodically update to check for timeout
    private $m_nInstanceSystemActionTimestamp = NULL;   //Periodically update for internal tuning

    private $m_oRuntimeResultFlexCacheHandler = array();    //20150715
    
    private $m_sCurrentTicketID = NULL;
    private $m_aPersonalBatchStack = NULL;
    private $m_sPersonalBatchStackMessage = NULL;
    
    private $m_nUID = NULL;
    private $m_sVistaUserID = NULL;
    private $m_sVAPassword = NULL;

    private $m_aForceLogoutReason = NULL;   //If not NULL, then we should force a logout.

    
    private $m_oVixDao = NULL;      //20140718 

    private $m_oEhrDao = NULL;      //20150714 
    //private $m_mdwsClient=NULL;     //20140718
    
    private $m_sWorklistMode=NULL;  

    private $m_aLocalCache = array();
    
    /**
     * Return user readable dump that hides passwords.
     */
    public static function safeArrayDump($myarray)
    {
        try
        {
            if(is_array($myarray))
            {
                $dump = array();
                foreach($myarray as $key=>$value)
                {
                    $uckey = strtoupper($key);
                    if($uckey == 'ESIG' || $uckey == 'PASSWORD' || $uckey == 'PSWD')
                    {
                        $dump[] = "$key => !!!VALUEMASKED!!!";
                    } else {
                        $dump[] = "$key => " . print_r($value,TRUE);
                    }
                }
                $keycount = count($dump);
                return "SAFE ARRAY DUMP ($keycount top level keys)...\n\t" . implode("\n\t",$dump);
            } else {
                error_log('Expected an array in safeDumpArray but instead got ' 
                        . $myarray);
                return "SAFE ARRAY DUMP non-array>>>".print_r($myarray,TRUE);
            }
        } catch (\Exception $ex) {
            error_log('Expected an array in safeDumpArray but instead got ' 
                    . $myarray." and error ".$ex->getMessage());
            return "SAFE ARRAY DUMP with exception>>>".print_r($myarray,TRUE);
        }
    }
    
    /**
     * Quick access to a few things that have immutable relationships
     */
    private function checkLocalCache($sKey)
    {
        if(isset($this->m_aLocalCache[$sKey]))
        {
            //error_log('Successful hit on local cache for '.$sKey);
            $aItem = $this->m_aLocalCache[$sKey];
            $aItem['hit'] = microtime();
            return $aItem['value'];
        }
        return NULL;
    }
    
    /**
     * Important that you only map immutable relationships!
     */
    private function updateLocalCache($sKey,$oValue)
    {
        if(count($this->m_aLocalCache) > 1000)
        {
            //Leave evidence of possible tuning requirement.
            error_log("Administrator warning: The local cache size at $sKey is ".$this->m_aLocalCache);
        }
        $aItem['hit'] = 0;
        $aItem['value'] = $oValue;
        $this->m_aLocalCache[$sKey] = $aItem;
    }

    /**
     * Return a formatted string to help debug array content.
     */
    public static function pretty_debug_array($glue,$array,$indent='',$keystring='')
    {
        $result = '';
        foreach($array as $key=>$item)
        {
            $newkeystring = $keystring.'[\''.$key.'\']';
            if(strlen($key) > 80)
            {
                $padded = $newkeystring;
            } else {
                $padded = str_pad($newkeystring, 80);
            }
            $result .= $glue . '@' . $padded."=\t" . $indent;
            if(is_array($item))
            {
                $result .= 'ARRAY(count='.count($item).')' . $glue . Context::pretty_debug_array($glue, $item, $indent.'  ',$newkeystring);
            } else {
                $result .= print_r($item, TRUE);
            }
        }
        return $result;
    }
    
    public static function debugGetCallerInfo($nShowLevelCount=5,$nStartAncestry=1,$bReturnArray=FALSE)
    {
        $aResult = array();
        $aResult['StartAncestryLevel'] = $nStartAncestry;
        $aResult['ShowLevels'] = $nShowLevelCount;
        $trace=debug_backtrace();
        $nLastAncestry = $nStartAncestry + $nShowLevelCount - 1;
        for($nAncestry=$nStartAncestry; $nAncestry <= $nLastAncestry; $nAncestry++  )
        {
            if(isset($trace[$nAncestry]))
            {
                $caller=$trace[$nAncestry];
            } else {
                $caller=array('function'=>'NO CALLING FUNCTION');
                break;  //Get out now.
            }
            $aResult[$nAncestry]['function'] = $caller['function'];
            if (isset($caller['class']))
            {
                $aResult[$nAncestry]['class'] = $caller['class'];
            } else {
                $aResult[$nAncestry]['class']=NULL;
            }
        }
        if($bReturnArray)
        {
            return $aResult;
        } else {
                      
            $sTrace = '<ol>';
            foreach($aResult as $aItem)
            {
                $sTrace .= '<li>' . print_r($aItem,TRUE);
            }
            $sTrace .= '</ol>';
            return $sTrace;
        }
    }
    
    public static function debugDrupalMsg($message,$type='status')
    {
        if(!DISABLE_CONTEXT_DEBUG)
        {
            drupal_set_message('CONTEXT DEBUG>>>'.$message . '...CALLED BY>>>'.Context::debugGetCallerInfo(5), $type);
            error_log('CONTEXT DEBUG ['.$type.']>>>'.$message);
        }
    }
    
    private function __construct($nUID)
    {
        error_log('WORKFLOW Called constructor for Context'
                . "\tInstance ts     = ".$this->m_nInstanceTimestamp
                . "\tUserAction ts   = ".$this->m_nInstanceUserActionTimestamp
                . "\tSystemAction ts = ".$this->m_nInstanceSystemActionTimestamp);
        if(!is_numeric($nUID))
        {
            throw new \Exception('The UID passed into contructor of Context must be numeric, but instead got "'.$nUID.'"');
        }

        $this->m_nUID = $nUID;
        $this->m_nInstanceTimestamp = microtime(TRUE);  //Capture the time this instance was created.
        $this->m_nLastUpdateTimestamp = microtime(TRUE);  
        $this->m_nInstanceUserActionTimestamp = time();
        $this->m_nInstanceSystemActionTimestamp = time();
        
        //Purge old cache contents now.
        RuntimeResultFlexCache::purgeOldItems();
    }    

    /**
     * Make it simpler to output details about this instance.
     * @return text
     */
    public function __toString()
    {
        try
        {
            return 'Context for user ['.$this->m_nUID.'] instance created at ['.$this->m_nInstanceTimestamp . ']'
                    . ' last updated=['.$this->m_nLastUpdateTimestamp . ']'
                    . ' with current TrackingID=['.$this->m_sCurrentTicketID.']';
        } catch (\Exception $ex) {
            return 'Cannot get toString of Context because '.$ex;
        }
    }
    
    public function getInstanceTimestamp()
    {
        return $this->m_nInstanceTimestamp;
    }
    
    public function getLastUpdateTimestamp()
    {
        return $this->m_nLastUpdateTimestamp;
    }
    
    /**
     * Tell us when this user last took an action.
     */
    public function getInstanceUserActionTimestamp()
    {
        return $this->m_nInstanceUserActionTimestamp;
    }

    /**
     * Tell us how long this user has been idle.
     */
    public function getUserIdleSeconds()
    {
        return time() - $this->m_nInstanceUserActionTimestamp;
    }
    
    /**
     * Return a value instead of NULL
     */
    public function valForNull($candidate, $altvalue=0)
    {
        if(!isset($candidate) || $candidate === NULL)
        {
            return $altvalue;
        }
        return $candidate;
    }

    /**
     * Return a value instead of NULL or missing
     */
    public function valForNullOrMissing($map, $key, $altvalue=0)
    {
        if(!isset($map[$key]))
        {
            return $altvalue;
        } else {
            $candidate = $map[$key];
            if(!isset($candidate) || $candidate === NULL)
            {
                return $altvalue;
            }
            return $candidate;
        }
    }
    
    /**
     * Factory implements session singleton.
     * @return \raptor\Context 
     */
    public static function getInstance($forceReset=FALSE, $bSystemDrivenAction=FALSE)
    {
        $currentpath = strtolower(current_path());
        //$currentpage = drupal_lookup_path('alias',$currentpath);
        $forceReset = ($currentpath == 'user/login' || $currentpath == 'user/logout');
    
        if (session_status() == PHP_SESSION_NONE) 
        {
            error_log('CONTEXTgetInstance::Starting session');
            session_start();
            drupal_session_started(TRUE);       //If we dont do this we risk warning messages elsewhere.
        }        
        
        if(!isset($_SESSION['CREATED'])) 
        { 
            $startedtime = time();
            error_log('CONTEXTgetInstance::Setting CREATED value of session to '.$startedtime);
            $_SESSION['CREATED'] = $startedtime;
        } 
        
        global $user;
        $bLocalReset = FALSE;
        $bAccountConflictDetected = FALSE;      //Set to true if something funny is going on.
        $bContextDetectIdleTooLong = FALSE;
        if(user_is_logged_in())
        {
            $tempUID = $user->uid;
        } else {
            $tempUID = 0;
        }
        //error_log('CONTEXTgetInstance::tempUID='.$tempUID);
        $bSessionResetFlagDetected = ($forceReset || (isset($_GET['reset_session']) && $_GET['reset_session'] == 'YES'));
        if(isset($_SESSION[CONST_NM_RAPTOR_CONTEXT]) && !$bSessionResetFlagDetected)
        {
            //We will return this instance unless ...
            $candidate=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
            $wmodeParam = $candidate->getWorklistMode();
            $candidateUID = $candidate->getUID();
            if($candidateUID == 0 && $tempUID != 0 && !$candidate->hasForceLogoutReason())
            {
                //Convert this session instance into instance for the UID, normal occurrence to do this after a login.
                $candidate->m_nUID = $tempUID;
                $candidate->serializeForNewLogin('Set the uid to the user->uid');
            } else 
            if($candidate->m_nUID > -1 && $candidate->getUID() !== $tempUID)
            {
                //This can happen if a user left without proper logout.
                $errmsg = 'Must reset because candidate UID['.$candidate->getUID().'] != current UID['.$tempUID.']';
                //drupal_set_message($errmsg, 'error');
                error_log('CONTEXTgetInstance::'.$errmsg . "\nCANDIDATE at time of reset=".print_r($candidate,TRUE));
                $bLocalReset=TRUE;
                $candidate=NULL;
                $wmodeParam='P';    //Hardcode assumption for now.
            } else {
                $bLocalReset=FALSE;
                if(!isset($candidate->m_nUID)) // !isset($candidate->m_sVistaUserID))         
                {
                   //Log something and continue
                   error_log("WARNING: Did NOT find a RAPTOR USER in existing session!"
                           . "\n\tUSER OBJ=".print_r($user,TRUE)  
                           . "\n\tCANDIDATE=".print_r($candidate,TRUE));
                }
            }
            
        } else {
            //No session already exists, so we will create a new one.
            if($bSessionResetFlagDetected)
            {
                error_log('Creating new session for uid='.$tempUID.' (session reset)');
            } else {
                error_log('Creating new session for uid='.$tempUID.' (missing session)');
            }
            $bLocalReset=TRUE;
            $candidate=NULL;
            $wmodeParam='P';    //Hardcode assumption for now.
        }
        
        if($candidate==NULL)    // $nElapsedSeconds > MAXINACTIVITYSECONDS 
        {
            $bLocalReset=TRUE;
        } else {
            if($bSystemDrivenAction)
            {
                //Update the session info.
                $candidate->m_nInstanceSystemActionTimestamp = time();
                $candidate->serializeNow(); //Store this now!!!
            } else {
                //Update user action tracking in datatabase.
                if($candidate !== NULL)
                {
                    $nElapsedSeconds = time() - $candidate->m_nInstanceUserActionTimestamp;
                } else {
                    $nElapsedSeconds = 0;
                }
                if(isset($tempUID) 
                        && $tempUID !== 0 && ($nElapsedSeconds > 10))
                {
                    try
                    {
                        //First make sure no one else is logged in as same UID
                        $mysessionid = session_id();
                        $other_or = db_or();
                        $other_or->condition('ipaddress', $_SERVER['REMOTE_ADDR'],'<>');
                        $other_or->condition('sessionid', $mysessionid ,'<>');
                        $resultOther = db_select('raptor_user_recent_activity_tracking','u')
                                ->fields('u')
                                ->condition('uid',$tempUID,'=')
                                ->condition($other_or)
                                ->orderBy('most_recent_action_dt','DESC')
                                ->execute();
                        if($resultOther->rowCount() > 0)
                        {
                            //There is always only one record in raptor_user_recent_activity_tracking
                            $resultMe = db_select('raptor_user_activity_tracking','u')
                                    ->fields('u')
                                    ->condition('uid',$tempUID,'=')
                                    ->condition('ipaddress',$_SERVER['REMOTE_ADDR'],'=')
                                    ->condition('sessionid', $mysessionid ,'=')
                                    ->orderBy('updated_dt','DESC')
                                    ->execute();
                            if($resultMe->rowCount() > 0)
                            {
                                $other = $resultOther->fetchAssoc();
                                $me = $resultMe->fetchAssoc();
                                $conflict_logic_info=array();
                                if($other['ipaddress'] == $me['ipaddress'])
                                {
                                    //This is on same machine.
                                    $nSesElapsedSeconds = (time() - $_SESSION['CREATED']);
                                    $conflict_logic_info['same-machine-elapsed-seconds'] 
                                            = $nSesElapsedSeconds;
                                    if (!isset($_SESSION['CREATED']) 
                                            || $nSesElapsedSeconds < CONFLICT_CHECK_DELAY_SECONDS)
                                    {
                                        //Allow for possibility that the session ID has changed for a single user
                                        $bAccountConflictDetected = FALSE;
                                    } else {
                                        //Possible the user has two browsers open with same account.
                                        $bAccountConflictDetected 
                                            = $other['most_recent_action_dt'] >= $me['updated_dt'];
                                    }
                                } else {
                                    //Simple check
                                    $bAccountConflictDetected 
                                            = $other['most_recent_action_dt'] >= $me['updated_dt'];
                                    $conflict_logic_info['simple date check'] = $bAccountConflictDetected;
                                }
                                if($bAccountConflictDetected)
                                {
                                    error_log('CONTEXTgetInstance::Account conflict has '
                                            . 'been detected at '.$_SERVER['REMOTE_ADDR']
                                            . ' for UID=['.$tempUID.']'
                                            . ' this user at '.$me['ipaddress']
                                            . ' other user at '.$other['ipaddress'] 
                                            . ' user sessionid ['.$mysessionid.']'
                                            . ' other sessionid ['.$other['sessionid'].']' 
                                            . '>>> TIMES = other[' 
                                            . $other['most_recent_action_dt'] 
                                            . '] vs this['
                                            . $me['updated_dt'] 
                                            . '] logicinfo='
                                            .print_r($conflict_logic_info,TRUE));
                                } else {
                                    error_log('CONTEXTgetInstance::No account conflict detected '
                                            . 'on check (es='.$nElapsedSeconds.') for UID=['.$tempUID.'] '
                                            . 'this user at '.$_SERVER['REMOTE_ADDR']
                                            . ' other user at '.$other['ipaddress'] 
                                            . '>>> TIMES = other[' 
                                            . $other['most_recent_action_dt'] 
                                            . '] vs this['
                                            . $me['updated_dt'] . ']');
                                }
                            }                    
                        }
                        if(!$forceReset)
                        {
                            //Log our activity.
                            $updated_dt = date("Y-m-d H:i:s", time());
                            db_insert('raptor_user_activity_tracking')
                            ->fields(array(
                                    'uid'=>$tempUID,
                                    'action_cd' => UATC_GENERAL,
                                    'ipaddress' => $_SERVER['REMOTE_ADDR'],
                                    'sessionid' => session_id(),
                                    'updated_dt'=>$updated_dt,
                                ))
                                ->execute();
                            $updated_dt = date("Y-m-d H:i:s", time());

                            //Write the recent activity to the single record that tracks it too.
                            db_merge('raptor_user_recent_activity_tracking')
                            ->key(array('uid'=>$tempUID,
                                ))
                            ->fields(array(
                                    'uid'=>$tempUID,
                                    'ipaddress' => $_SERVER['REMOTE_ADDR'],
                                    'sessionid' => session_id(),
                                    'most_recent_action_dt'=>$updated_dt,
                                    'most_recent_action_cd' => UATC_GENERAL,
                                ))
                                ->execute();
                        }
                        //Update the session info.
                        $candidate->m_nInstanceUserActionTimestamp = time();
                        $candidate->serializeNow(); //Store this now!!!
                    } catch (\Exception $ex) {
                        //Log this but keep going.
                        error_log('CONTEXTgetInstance::Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
                    }
                }
            }
        }
        
        if ($bLocalReset) {
            //Clear existing context except for any user login info.
            $tempUID = $user->uid;
            $candidate = new \raptor\Context($tempUID);
            if(isset($_SESSION[CONST_NM_RAPTOR_CONTEXT]))
            {
                //Preserve existing credientials.
                $current=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
                error_log('CONTEXTgetInstance::Clearing cache except login credentials for '
                        . 'EHR User ID=' . $current->m_sVistaUserID);
                $candidate->m_sVistaUserID = $current->m_sVistaUserID;  //20140609
                $candidate->m_sVAPassword = $current->m_sVAPassword;    //20140609
            }
            $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($candidate);
            $candidate=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
            Context::debugDrupalMsg('Created new context at ' + $candidate->m_nInstanceTimestamp);
        } else {
            Context::debugDrupalMsg('[' . $candidate->m_nInstanceTimestamp . '] Got context from cache! UID='.$candidate->getUID());
        }

        if($user->uid > 0)
        {
            $useridleseconds = intval($candidate->getUserIdleSeconds());
            $max_idle = USER_TIMEOUT_SECONDS 
                    + USER_TIMEOUT_GRACE_SECONDS 
                    + USER_ALIVE_INTERVAL_SECONDS
                    + KICKOUT_DIRTYPADDING;
            if($useridleseconds > $max_idle)
            {
                $bContextDetectIdleTooLong = TRUE;
            }
        }
        
        //Now trigger logout if account conflict was detected.
        if($bAccountConflictDetected || $bContextDetectIdleTooLong)
        {
            //Don't kick out an administrator in a protected URL
            $is_protected_adminuser = \raptor\UserInfo::is_protected_adminuser();
            if(!$is_protected_adminuser)
            {
                //Don't get stuck in an infinite loop.
                if(substr($candidate->m_sVistaUserID,0,8) !== 'kickout_')
                {
                    //Prevent duplicate user messages.
                    if(!isset($candidate->m_aForceLogoutReason))
                    {
                        //Not already set, so set it now.
                        if($bContextDetectIdleTooLong)
                        {
                            $useridleseconds = intval($candidate->getUserIdleSeconds());
                            $usermsg = 'You are kicked out because context has detected excessive'
                                    . " idle time of $useridleseconds seconds";
                            $errorcode = ERRORCODE_KICKOUT_TIMEOUT;
                            $kickoutlabel = 'TIMEOUT';
                        } else {
                            if($candidate->m_sVistaUserID > '')
                            {
                                $usermsg = 'You are kicked out because another workstation has'
                                        . ' logged in as the same'
                                        . ' RAPTOR user account "'
                                        . $candidate->m_sVistaUserID.'"';
                                $errorcode = ERRORCODE_KICKOUT_ACCOUNTCONFLICT;
                                $kickoutlabel = 'ACCOUNT CONFLICT';
                            } else {
                                //This can happen to a NON VISTA admin user for timeout and things like that.
                                $usermsg = 'Your admin account has timed out';
                                $errorcode = ERRORCODE_KICKOUT_TIMEOUT;
                                $kickoutlabel = 'TIMEOUT CONFLICT';
                            }
                        }
                        drupal_set_message($usermsg, 'error');
                        $candidate->m_aForceLogoutReason = array();
                        $candidate->m_aForceLogoutReason['code'] = $errorcode;
                        $candidate->m_aForceLogoutReason['text'] = $usermsg;
                        $candidate->m_sVistaUserID = 'kickout_' . $candidate->m_sVistaUserID;
                        $candidate->m_sVAPassword = NULL;
                    }

                    $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($candidate); //Store this NOW!!!
                    error_log("CONTEXT KICKOUT $kickoutlabel DETECTED ON [" 
                            . $candidate->m_sVistaUserID . '] >>> ' 
                            . time() 
                            . "\n\tSESSION>>>>" . print_r($_SESSION,TRUE));

                    $candidate->forceSessionRefresh(0);  //Invalidate any current form data now!
                }
            }
        }
        
        if (!isset($candidate->m_oEhrDao))
        {
            if($candidate == NULL)
            {
                error_log('CONTEXTgetInstance::WORKFLOWDEBUG>>>getInstance has candidate instance from '. $_SERVER['REMOTE_ADDR']);
            } else {
                if(!isset($candidate->m_nUID) || $candidate->m_nUID < 1)
                {
                    error_log('CONTEXTgetInstance::WORKFLOWDEBUG>>>getInstance has NO existing Vista connection for ' 
                            . $candidate->m_sVistaUserID . ' from ' 
                            . $_SERVER['REMOTE_ADDR'] 
                            . ' in ' 
                            . $candidate->m_nInstanceTimestamp);
                }
            }
        }
        
        $candidate->getEhrDao();    //Side effect of setting the context in the dao
        return $candidate;
    }
    
    public function hasForceLogoutReason()
    {
        return ($this->m_aForceLogoutReason !== NULL);
    }
    
    public function getForceLogoutReason()
    {
        return $this->m_aForceLogoutReason;
    }
    
    public function clearForceLogoutReason()
    {
        $this->m_sForceLogoutReason = NULL;
        $this->serializeNow(); //Store this now!!!
    }
    
    /**
     * For the 2014 release, site ID is a constant for the entire installation.
     * @return the site ID of this installation
     */
    function getSiteID()
    {
        return VISTA_SITE;
    }
    
    function getFullyQualifiedTicketID($ticketIEN)
    {
        return VISTA_SITE . '-' . trim($ticketIEN);
    }
    
    function getUID()
    {
        return $this->m_nUID;
    }
    
    function getVistaUserID()
    {
        return $this->m_sVistaUserID;
    }

    function getVixDao()
    {
        module_load_include('php', 'raptor_imageviewing', 'core/VixDao');
        if($this->m_oVixDao == NULL)
        {
            $this->m_oVixDao = new \raptor\VixDao($this->m_sVistaUserID,$this->m_sVAPassword);
        }
        return $this->m_oVixDao;
    }

    function getUserInfo($bFailIfNoUser=TRUE)
    {
        if($bFailIfNoUser && ($this->m_nUID == '' || $this->m_nUID < 1))
        {
            error_log('Did NOT find a valid UID!!!');
            global $base_url;
            die('<h1>Expired RAPTOR session</h1>'
                    . '<p>Did NOT find a valid user instance!<p>'
                    . '<p>TIP: <a href="'.$base_url.'/user/login">login</a></p>');
        }
        $oUserInfo = new \raptor\UserInfo($this->m_nUID);
        return $oUserInfo;
    }
    
    public function getWorklistMode()
    {
        if ($this->m_sWorklistMode == NULL)
        {
            $this->setWorklistMode('P');
        }
        return $this->m_sWorklistMode;
    } 
    
    /**
     * P=Protocol *DEFAULT*
     * E=Examination
     * I=Interpretation
     * Q=QA
     * 
     * @param type $sWMODE 
     */
    public function setWorklistMode($sWMODE)
    {
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        if($this->m_sWorklistMode != $sWMODE)
        {
            if(!in_array($sWMODE, array('P', 'E', 'I', 'Q', 'S')))
            {
                die("Invalid WorklistMode='$sWMODE'!!!");
            }
            $this->m_sWorklistMode = $sWMODE;
            $this->serializeNow();        
        }
    }
    
    public function clearSelectedTrackingID($bSaveSession=TRUE)
    {
        Context::debugDrupalMsg('called clearSelectedTrackingID');
        $this->m_sCurrentTicketID = NULL;
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        if($bSaveSession)
        {
            $this->serializeNow();        
        }
    }

    /**
     * @return boolean TRUE if a tracking id is currently selected
     */
    public function hasSelectedTrackingID()
    {
        return $this->getSelectedTrackingID() !== NULL;
    }

    /**
     * @return NULL or the currently selected tracking ID
     */
    public function getSelectedTrackingID()
    {
        $candidate = Context::getInstance();    //Important that we ALWAYS pull it from persistence layer here!
        $candidate->m_sPersonalBatchStackMessage = NULL;
        if(!isset($candidate->m_sCurrentTicketID) || $candidate->m_sCurrentTicketID == NULL)
        {
            //If there is anything in the personal batch stack, grab it now.
            $candidate->m_sCurrentTicketID = $candidate->popPersonalBatchStack(FALSE);
            if($candidate->m_sCurrentTicketID !== NULL)
            {
                $pbsize = $candidate->getPersonalBatchStackSize();
                if($pbsize === 1)
                {
                    $candidate->m_sPersonalBatchStackMessage = 'You have 1 remaining personal batch selection.';
                } else if($pbsize > 1){
                    $candidate->m_sPersonalBatchStackMessage = 'You have ' . $pbsize . ' remaining personal batch selections.';
                }
            }
            $candidate->serializeNow();        
        }
        return $candidate->m_sCurrentTicketID;
    }
    
    /**
     * @return type True if specified parameters, concatenated, are matching tracking ID store in the context
     */
    public function isDataMatchingTrackingID($key)
    {
        return $this->getSelectedTrackingID() == $key;  //20140604
    }

    function setSelectedTrackingID($sTrackingID, $bClearPersonalBatchStack=FALSE)
    {
        Context::debugDrupalMsg('setSelectedTrackingID with ['.$sTrackingID.']');
        if($bClearPersonalBatchStack)   //20140619
        {
            $this->clearPersonalBatchStack();
        }
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        $aParts = explode('-',$sTrackingID);    //Allow for older type ticket format
        if(count($aParts) == 1)
        {
            $nIEN = $aParts[0];
        } else {
            $nIEN = $aParts[1];
        }
        $this->m_sCurrentTicketID = $sTrackingID;
        
        $oMC = $this->getEhrDao();
        $sPatientID = $this->checkLocalCache($sTrackingID);
        if($sPatientID == NULL)
        {
            $sPatientID = $oMC->getPatientIDFromTrackingID($sTrackingID);
            $this->updateLocalCache($sTrackingID, $sPatientID);
        }
        $oMC->setPatientID($sPatientID);

        //Now is the perfect time to reset the session to invalidate other windows!!!
        $SHORT_DELAY_REFRESH_OVERRIDE = 120;    //Must be at least 2 minutes
        $this->serializeNow('', TRUE, $SHORT_DELAY_REFRESH_OVERRIDE);
    }

    
    /**
     * @param array $aPBatch array representing stack of tracking IDs, last one in array is top of the stack
     */
    function setPersonalBatchStack($aPBatch)
    {
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        $this->m_sPersonalBatchStackMessage = NULL;
        $this->m_aPersonalBatchStack = $aPBatch;
        $this->m_sCurrentTicketID = NULL;   //Clear it so we pop from the stack on request
        $this->serializeNow();        
    }

    /**
     * Clear the stack.
     */
    function clearPersonalBatchStack()
    {
        Context::debugDrupalMsg('<h1>called clearPersonalBatchStack</h1>');
        $this->m_aPersonalBatchStack = NULL;
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        $this->serializeNow();        
    }

    /**
     * @return true if there are tickets in the stack
     */
    function hasPersonalBatchStack()
    {
        return (isset($this->m_aPersonalBatchStack) && is_array($this->m_aPersonalBatchStack));
    }

    function debugPersonalBatchStack()
    {
        return print_r($this->m_aPersonalBatchStack, true);
    }

    /**
     * @return null if nothing is on the stack.
     */
    function popPersonalBatchStack($serializeNow=TRUE)
    {
        if(!$this->hasPersonalBatchStack())
        {
            Context::debugDrupalMsg("<h1>Popped nothing off the stack </h1>");
            return null;
        }
        $nTID = array_pop($this->m_aPersonalBatchStack);
        Context::debugDrupalMsg("<h1>Popped $nTID off the stack ". print_r($this->m_aPersonalBatchStack,TRUE)  ."</h1>");
        if($serializeNow)
        {
            $this->serializeNow();        
        }
        return $nTID;
    }
    
    function getPersonalBatchStackSize()
    {
        if(!$this->hasPersonalBatchStack())
        {
            return 0;
        }
        return count($this->m_aPersonalBatchStack);
    }
    
    /**
     * Show this to the user so they know they are in a personal batch mode
     * @return string or null
     */
    function getPersonalBatchStackMessage()
    {
        return $this->m_sPersonalBatchStackMessage;
    }

    /**
     * Returns empty string if authenticated OK, else associative array with following keys: ERRNUM, ERRSUMMARY, ERRDETAIL 
     */
    public function authenticateSubsystems($sVistaUserID, $sVAPassword) {
        
        $this->m_sVistaUserID = $sVistaUserID;  //Cache this for later re-authentications.
        $this->m_sVAPassword = $sVAPassword;    //Cache this for later re-authentications.
        $this->serializeNow();        
        $result = $this->authenticateEhrSubsystem($sVistaUserID, $sVAPassword);
        $updated_dt = date("Y-m-d H:i:s", time());
        global $user;
        $tempUID = $user->uid;  
        if($tempUID != NULL && $tempUID != 0)
        {
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
                    //Write the recent activity to the single record that tracks it too.
                    db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$tempUID,
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
                error_log('Trouble updating raptor_user_activity_tracking>>>'
                        .print_r($ex,TRUE));
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
        return $result;
    }
    
    private function authenticateEhrSubsystem($sVistaUserID, $sVAPassword) 
    {
        error_log('WORKFLOWDEBUG>>>Called authenticateEhrSubsystem for ' 
                . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR']  
                . ' in ' . $this->m_nInstanceTimestamp);
        try 
        {
            // NOTE - hardcoded vista site per config.php->VISTA_SITE
            $oEhrDao = $this->getEhrDao();
            //error_log("LOOK before login $oEhrDao");
            $loginResult = $this->getEhrDao()->connectAndLogin(VISTA_SITE, $sVistaUserID, $sVAPassword);
            //error_log("LOOK after login $oEhrDao");
            $this->clearForceLogoutReason();    //Important that we clear it now otherwise can be stuck in kickout mode.
            return ''; // per data functions doc - return empty string on success
        }  catch (\Exception $ex) {
            error_log('Failed to log into EHR because '.$ex->getMessage());
            throw new \Exception('Failed to log into EHR',99765,$ex);
        }
    }
    
    /**
     * @return type TRUE/FALSE
     */
    public function isAuthenticatedInSubsystem() {
        return $this->isAuthenticatedInEhrSubsystem();
    }
    
    private function isAuthenticatedInEhrSubsystem() {
        return $this->getEhrDao()->isAuthenticated();
    }

    private function clearAllContext()
    {
        $this->logoutEhrSubsystem();
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        $this->m_nInstanceClearedTimestamp = microtime(TRUE);
        $this->m_nInstanceTimestamp = null;
        $this->m_sCurrentTicketID = null;
        $this->m_aPersonalBatchStack = null;
        $this->m_nUID = 0;
        $this->m_sVistaUserID = null;
        $this->m_sVAPassword = null;
        Context::debugDrupalMsg('cleared all context of instance [' . $this->m_nInstanceTimestamp . '] at [' . microtime(TRUE) . ']');
        return '';
    }
    
    /**
     * @return empty string if no error, else returns else associative array with following keys: ERRNUM, ERRSUMMARY, ERRDETAIL
     */
    public function logoutSubsystems() 
    {
        if($this->m_nUID != NULL && $this->m_nUID > 0)
        {
            try
            {
                $updated_dt = date("Y-m-d H:i:s", time());
                db_insert('raptor_user_activity_tracking')
                ->fields(array(
                        'uid'=>$this->m_nUID,
                        'action_cd' => UATC_LOGOUT,
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
                        'sessionid' => session_id(),
                        'updated_dt'=>$updated_dt,
                    ))
                    ->execute();
                    //Write the recent activity to the single record that tracks it too.
                /*
                db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$this->m_nUID,
                        'ipaddress'=>$_SERVER['REMOTE_ADDR'],
                        'sessionid' => session_id(),
                        ))
                    ->fields(array(
                            'uid'=>$this->m_nUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            'most_recent_logout_dt'=>$updated_dt,
                            'most_recent_action_dt'=>$updated_dt,
                            'most_recent_action_cd' => UATC_LOGOUT,
                        ))
                        ->execute();
                 */
                db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$this->m_nUID))
                    ->fields(array(
                            'uid'=>$this->m_nUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'sessionid' => session_id(),
                            'most_recent_logout_dt'=>$updated_dt,
                            'most_recent_action_dt'=>$updated_dt,
                            'most_recent_action_cd' => UATC_LOGOUT,
                        ))
                        ->execute();
            } catch (\Exception $ex) {
                error_log('Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
            }
        }
        $this->clearAllContext();
        $this->serializeNow();
        return '';  //TODO
    }
    
    private function logoutEhrSubsystem() 
    {
        try {
            $this->serializeNow('Logging out of EHR',FALSE);
            $this->getEhrDao()->disconnect();
            return '';
        } catch (\Exception $ex) {
            //Log it and continue
            error_log('Failed logout of EHR system because '.$ex);
        }
    }

    /**
     * Call this once when user is logging in so age of login page is not issue.
     */
    private function serializeForNewLogin($logMsg = ''
            , $bSystemDrivenAction=TRUE
            , $nSessionRefreshDelayOverride=NULL)
    {
        error_log('CONTEXT called serializeForNewLogin');
        $_SESSION['CREATED'] = time();  //Created as of now!
        
        //Help prevent long lived configuration errors.
        $maxlimit = USER_TIMEOUT_SECONDS+USER_TIMEOUT_GRACE_SECONDS+KICKOUT_DIRTYPADDING;
        if($maxlimit < USER_ALIVE_INTERVAL_SECONDS)
        {
            error_log('Config ERROR detected: '."$maxlimit < USER_ALIVE_INTERVAL_SECONDS");
            drupal_set_message("Session may timeout without warning.  Contact administrator to correct the interval settings!","error");
        }
        
        //Now serialize it.
        $this->serializeNow($logMsg,$bSystemDrivenAction,$nSessionRefreshDelayOverride,FALSE);
    }
    
    /**
     * We call this whenever we change something significant in the instance.
     */
    private function serializeNow($logMsg = ''
            , $bSystemDrivenAction=TRUE
            , $nSessionRefreshDelayOverride=NULL
            , $checkSessionTimeout=TRUE)
    {
        if($bSystemDrivenAction)
        {
            $this->m_nInstanceSystemActionTimestamp = time(); //Capture the time whenever we serialize.
        } else {
            $this->m_nInstanceUserActionTimestamp = time(); //Capture the time whenever we serialize.
        }
        $this->bNeedsSave=FALSE;    //Because now we are saving it now.
        if(!isset($this->m_nInstanceTimestamp))
        {
            $this->m_nInstanceTimestamp = microtime(TRUE);
        }
        if($nSessionRefreshDelayOverride !== NULL)
        {
            $SESSION_REFRESH_DELAY = $nSessionRefreshDelayOverride;
        } else {
            $SESSION_REFRESH_DELAY = SESSION_KEY_TIMEOUT_SECONDS;
        }
        $this->forceSessionRefresh($SESSION_REFRESH_DELAY);
        
        $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($this);
        //error_log('WORKFLOWDEBUG>>>Serialized context at ' . microtime(TRUE));  
    }    
    
    /**
     * This is not a graceful kickout.
     */
    private function forceKickoutNow($reason=NULL
            ,$reasoncode=0
            ,$candidate=NULL)
    {
        if($candidate == NULL)
        {
            error_log("Unserializing now for kickout processing!");
            $candidate=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
            if($candidate == '')
            {
                error_log("No existing context instance found, creating a temporary one now for kickout processing!");
                $candidate = new \raptor\Context(-1);
            }
        }
        if($reason === NULL)
        {
            $reason = 'No reason given';
            $showusermsgmarkup = '';
        } else {
            $showusermsgmarkup = "<h2>".$reason."</h2>";
        }
        $candidate->m_aForceLogoutReason = array();
        $candidate->m_aForceLogoutReason['code'] = $reasoncode;
        $candidate->m_aForceLogoutReason['text'] = $reason;
        error_log('CONTEXT KICKOUT ACCOUNT AT ' . time() . "\n\tSESSION>>>>" 
                . print_r($_SESSION,TRUE));
        $candidate->m_sVistaUserID = 'kickout_' . $candidate->m_sVistaUserID;
        $candidate->m_sVAPassword = NULL;
        
        //Store this now!!!
        $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($candidate);
        unset($_SESSION['CREATED']);
        die("<h1>Your RAPTOR session has ended</h1>$showusermsgmarkup"
                . "<a href='".RAPTOR_ROOT_URL."'>Log back in</a>");
    }

    /**
     * When the session is refreshed all existing form data is invalid.
     * We need to refresh the session at least before the server invalidates it.
     * @param type $grace_seconds do not refresh if newer than this
     */
    public function forceSessionRefresh($grace_seconds=-1)
    {
        if(!user_is_logged_in() || $this->getUID() == 0)
        {
            //Never time out if no one is logged in anyways.
             $_SESSION['CREATED'] = time();  // update creation time
        } else {
            if($grace_seconds < 0)
            {
                //Use the configured default.
                $grace_seconds = SESSION_KEY_TIMEOUT_SECONDS;
            }
            if ((!isset($_SESSION['CREATED']) 
                || (time() - $_SESSION['CREATED']) > $grace_seconds))
            {
                $currentpath = current_path();
                // session started more than SESSION_REFRESH_DELAY seconds ago
                error_log('WORKFLOWDEBUG>>>Session key timeout of '
                        .$grace_seconds
                        .' seconds (grace seconds) reached so generated new key for uid='.$this->getUID()
                        ."\nURL at key timeout = ".$currentpath);
                session_regenerate_id(FALSE);   // change session ID for the current session and invalidate old session ID
                $_SESSION['CREATED'] = time();  // update creation time
            }
        }
    }

    /**
     * Interface to the EHR
     */
    public function getEhrDao()
    {
        if (!isset($this->m_oEhrDao)) 
        {
            $this->m_oEhrDao = new \raptor\EhrDao();
        }
        //error_log("LOOK DAO from context is >>>".$this->m_oEhrDao);
        return $this->m_oEhrDao;
    }
    
    /**
     * Returns NULL if no cache handler is available.
     */
    public function getRuntimeResultFlexCacheHandler($groupname,$embedUID=TRUE)
    {
        $handler = NULL;
        $uid = $this->getUID();
        if($uid > 0)
        {
            if($embedUID)
            {
                $groupname = "u:{$uid}_g:{$groupname}";
            }
            if(!isset($this->m_oRuntimeResultFlexCacheHandler[$groupname]))
            {
                $this->m_oRuntimeResultFlexCacheHandler[$groupname] = \raptor\RuntimeResultFlexCache::getInstance($groupname);
            }
            $handler = $this->m_oRuntimeResultFlexCacheHandler[$groupname];
        } else {
            if(!$embedUID)
            {
                if(isset($this->m_oRuntimeResultFlexCacheHandler[$groupname]))
                {
                    $handler = $this->m_oRuntimeResultFlexCacheHandler[$groupname];
                }
            }
        }
        return $handler;
    }
}
