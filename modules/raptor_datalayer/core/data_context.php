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
 */ 

namespace raptor;

require_once 'data_utility.php';
require_once 'data_user.php';
require_once 'MdwsDaoFactory.php';

defined('CONST_NM_RAPTOR_CONTEXT')
    or define('CONST_NM_RAPTOR_CONTEXT', 'RAPTOR141006A');

defined("DISABLE_CONTEXT_DEBUG")
    or define("DISABLE_CONTEXT_DEBUG", TRUE);

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

    private $m_sCurrentTicketID = NULL;
    private $m_aPersonalBatchStack = NULL;
    private $m_sPersonalBatchStackMessage = NULL;
    
    private $m_nUID = NULL;
    private $m_sVistaUserID = NULL;
    private $m_sVAPassword = NULL;

    private $m_aForceLogoutReason = NULL;   //If not NULL, then we should force a logout.
    
    private $m_oVixDao = NULL;      //20140718 

    private $m_mdwsClient=NULL;     //20140718
    
    private $m_sWorklistMode=NULL;  

    private $m_aLocalCache = array();
    
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
            //Trim the stale stuff out of the cache.
            error_log('TODO -- Trim the local cache because too big now!!!');
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
        error_log('WORKFLOWDEBUG>>>Called constructor for Context'
                . "\tInstance ts     = ".$this->m_nInstanceTimestamp
                . "\tUserAction ts   = ".$this->m_nInstanceUserActionTimestamp
                . "\tSystemAction ts = ".$this->m_nInstanceSystemActionTimestamp);
        if(!is_numeric($nUID))
        {
            die('The UID passed into contructor of Context must be numeric, but instead got "'.$nUID.'"');
        }

        $this->m_nUID = $nUID;
        $this->m_nInstanceTimestamp = microtime(TRUE);  //Capture the time this instance was created.
        $this->m_nLastUpdateTimestamp = microtime(TRUE);  
        $this->m_nInstanceUserActionTimestamp = time();
        $this->m_nInstanceSystemActionTimestamp = time();
        Context::debugDrupalMsg('<h1>Hi from CONSTRUCTOR('.$nUID.') Context: New instance created at [' . microtime(TRUE) . ']</h1>' . $this->getContextHtmlDebugInfo(),'status');
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
                Context::debugDrupalMsg('<h3>Hi from Context: Using existing instance from [' 
                        . $candidate->m_nInstanceTimestamp 
                        . '] at [' . microtime(TRUE) 
                        . "]</h3> ". $candidate->getContextHtmlDebugInfo());
               if(!isset($candidate->m_sVistaUserID))         
               {
                   Context::debugDrupalMsg(microtime(TRUE) . ') DID NOT FIND USER IN EXISTING SESSION!!!!->' . print_r($candidate,TRUE),'error');
               }
            }
            
        } else {
            //No session already exists, so we will create a new one.
            error_log('CONTEXTgetInstance::WORKFLOWDEBUG'
                    . '>>>NO EXISTING SESSION!!! '
                    . 'Not using an existing session: '
                    . 'bSessionResetFlagDetected='.$bSessionResetFlagDetected 
                    . ' from '. $_SERVER['REMOTE_ADDR'] 
                    . " CALLER==> " . Context::debugGetCallerInfo(10));
            Context::debugDrupalMsg('Not using an existing session: '
                    . 'bSessionResetFlagDetected='.$bSessionResetFlagDetected);
            $bLocalReset=TRUE;
            $candidate=NULL;
            $wmodeParam='P';    //Hardcode assumption for now.
        }
        
        $bAccountConflictDetected = FALSE;      //Set to true if something funny is going on.
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
                            $resultMe = db_select('raptor_user_recent_activity_tracking','u')
                                    ->fields('u')
                                    ->condition('uid',$tempUID,'=')
                                    ->condition('ipaddress',$_SERVER['REMOTE_ADDR'],'=')
                                    ->condition('sessionid', $mysessionid ,'=')
                                    ->orderBy('most_recent_action_dt','DESC')
                                    ->execute();
                            if($resultMe->rowCount() > 0)
                            {
                                $other = $resultOther->fetchAssoc();
                                $me = $resultMe->fetchAssoc();
                                if($other['ipaddress'] == $me['ipaddress'])
                                {
                                    //This is on same machine.
                                    if (!isset($_SESSION['CREATED']) 
                                            || (time() - $_SESSION['CREATED']) < CONFLICT_CHECK_DELAY_SECONDS)
                                    {
                                        //Allow for possibility that the session ID has changed for a single user
                                        $bAccountConflictDetected = FALSE;
                                    } else {
                                        //Possible the user has two browsers open with same account.
                                        $bAccountConflictDetected 
                                            = $other['most_recent_action_dt'] >= $me['most_recent_action_dt'];
                                    }
                                } else {
                                    //Simple check
                                    $bAccountConflictDetected 
                                            = $other['most_recent_action_dt'] >= $me['most_recent_action_dt'];
                                }
                                if($bAccountConflictDetected)
                                {
                                    error_log('CONTEXTgetInstance::Account conflict has '
                                            . 'been detected for UID=['.$tempUID.'] this '
                                            . 'user at '.$_SERVER['REMOTE_ADDR']
                                            . ' other user at '.$other['ipaddress'] 
                                            . 'user sessionid ['.$mysessionid.']'
                                            . ' other sessionid ['.$other['sessionid'].']' 
                                            . '>>> TIMES = other[' 
                                            . $other['most_recent_action_dt'] 
                                            . '] vs this['
                                            . $me['most_recent_action_dt'] . ']');
                                } else {
                                    error_log('CONTEXTgetInstance::No account conflict detected '
                                            . 'on check (es='.$nElapsedSeconds.') for UID=['.$tempUID.'] '
                                            . 'this user at '.$_SERVER['REMOTE_ADDR']. ' other '
                                            . 'user at '.$other['ipaddress'] 
                                            . '>>> TIMES = other[' 
                                            . $other['most_recent_action_dt'] 
                                            . '] vs this['
                                            . $me['most_recent_action_dt'] . ']');
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
                                'ipaddress'=>$_SERVER['REMOTE_ADDR'],
                                'sessionid' => session_id(),
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
                        /*
                        error_log('WORKFLOWDEBUG>>Updating user activity...'
                                . "\n\tInstance ts     = ".$candidate->m_nInstanceTimestamp
                                . "\n\tUserAction ts   = ".$candidate->m_nInstanceUserActionTimestamp
                                . "\n\tSystemAction ts = ".$candidate->m_nInstanceSystemActionTimestamp
                                . "\n\tuseridleseconds = ".$candidate->getUserIdleSeconds() . '\t(Allowed='.USER_TIMEOUT_SECONDS.')'
                                );
                         */
                        //Update the session info.
                        $candidate->m_nInstanceUserActionTimestamp = time();
                        error_log('DEBUGINFO storing m_nInstanceUserActionTimestamp as '.$candidate->m_nInstanceUserActionTimestamp);
                        $candidate->serializeNow(); //Store this now!!!
                    } catch (\Exception $ex) {
                        error_log('CONTEXTgetInstance::Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
                    }
                }
            }
        }
        
        if ($bLocalReset) {
            //Clear existing context except for any user login info.
            global $user;
            $tempUID = $user->uid;
            $candidate = new \raptor\Context($tempUID);
            if(isset($_SESSION[CONST_NM_RAPTOR_CONTEXT]))
            {
                //Preserve existing credientials.
                $current=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
                error_context_log('CONTEXTgetInstance::Clearing cache except login credentials for VistaUserID=' . $current->m_sVistaUserID);
                $candidate->m_sVistaUserID = $current->m_sVistaUserID;  //20140609
                $candidate->m_sVAPassword = $current->m_sVAPassword;    //20140609
            }
            $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($candidate);
            $candidate=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
            Context::debugDrupalMsg('Created new context at ' + $candidate->m_nInstanceTimestamp);
        } else {
            Context::debugDrupalMsg('[' . $candidate->m_nInstanceTimestamp . '] Got context from cache! UID='.$candidate->getUID());
        }

        //Now trigger logout if account conflict was detected.
        if($bAccountConflictDetected)
        {
            //Don't get stuck in an infinite loop.
            if(substr($candidate->m_sVistaUserID,0,8) !== 'kickout_')
            {
                //Prevent duplicate user messages.
                if(!isset($candidate->m_aForceLogoutReason))
                {
                    //Not already set, so set it now.
                    $usermsg = 'You are kicked out because another workstation has'
                            . ' logged in as the same'
                            . ' RAPTOR user account "'
                            . $candidate->m_sVistaUserID.'"';
                    drupal_set_message($usermsg, 'error');
                    $errorcode = ERRORCODE_KICKOUT_ACCOUNTCONFLICT;
                    $candidate->m_aForceLogoutReason = array();
                    $candidate->m_aForceLogoutReason['code'] = $errorcode;
                    $candidate->m_aForceLogoutReason['text'] = $usermsg;
                    $candidate->m_sVistaUserID = 'kickout_' . $candidate->m_sVistaUserID;
                    $candidate->m_sVAPassword = NULL;
                    //$candidate->serializeNow(); //Store this now!!!
                }

                $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($candidate); //Store this NOW!!!
                error_log('CONTEXT KICKOUT ACCOUNT CONFLICT DETECTED ON ['.$candidate->m_sVistaUserID.'] >>> ' 
                        . time() . "\n\tSESSION>>>>" . print_r($_SESSION,TRUE));
                
                $candidate->forceSessionRefresh(0);  //Invalidate any current form data now!
                //$this->forceKickoutNow($usermsg,$errorcode,$candidate);
            }
        }
        
        if (!isset($candidate->m_mdwsClient))
        {
            if($candidate == NULL)
            {
                error_log('CONTEXTgetInstance::WORKFLOWDEBUG>>>getInstance has candidate instance from '. $_SERVER['REMOTE_ADDR']);
            } else {
                error_log('CONTEXTgetInstance::WORKFLOWDEBUG>>>getInstance has NO existing Mdws connection for ' . $candidate->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $candidate->m_nInstanceTimestamp);
            }
        }
        
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
        $this->m_sCurrentTicketID = $sTrackingID;
        
        //TODO -- make sure the MdwsDao is using the Patient ID for the currently selected sTrackingID
        $oMC = $this->getMdwsClient();
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
        $this->m_sCurrentTicketID = null;   //Clear it so we pop from the stack on request
        $this->serializeNow();        
    }

    /**
     * Clear the stack.
     */
    function clearPersonalBatchStack()
    {
        Context::debugDrupalMsg('<h1>called clearPersonalBatchStack</h1>');
        $this->m_aPersonalBatchStack = null;
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
        $result = $this->authenticateMdwsSubsystem($sVistaUserID, $sVAPassword);
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
    
    private function authenticateMdwsSubsystem($sVistaUserID, $sVAPassword) 
    {
        error_log('WORKFLOWDEBUG>>>Called authenticateMdwsSubsystem for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
        try 
        {
            // NOTE - hardcoded vista site per config.php->VISTA_SITE
            $loginResult = $this->getMdwsClient()->connectAndLogin(VISTA_SITE, $sVistaUserID, $sVAPassword);
            // NOTE - this code as-is does not save any of the UserTO attributes from the login - may be important (e.g. DUZ)
            
            //drupal_set_message('>>>> login details>>>'.print_r($loginResult, TRUE));
            
            $this->clearForceLogoutReason();    //Important that we clear it now otherwise can be stuck in kickout mode.
            return ""; // per data functions doc - return empty string on success
        }  catch (\Exception $ex) {
            // TBD - do we want better error numbers for connect and login issues? static MdwsUtils method returns constant 1
            error_log('Failed to log into MDWS because '.$ex->getMessage());
            return array(
                "ERRNUM"=>MdwsUtils::getErrorNumberForException($ex), 
                "ERRSUMMARY"=>"Error on connect/login", 
                "ERRDETAIL"=>$ex);
        }
    }
    
    /**
     * @return type TRUE/FALSE
     */
    public function isAuthenticatedInSubsystem() {
        return $this->isAuthenticatedInMdwsSubsystem();
    }
    
    private function isAuthenticatedInMdwsSubsystem() {
        return $this->getMdwsClient()->isAuthenticated();
    }

    private function getContextHtmlDebugInfo()
    {
        return 'CONTEXT INFORMATION-><ol>'
                . '<li>Created=' . $this->m_nInstanceTimestamp 
                . "<li>"
                . 'Cleared=' . $this->m_nInstanceClearedTimestamp
                . "<li>"
                . 'UID=' . $this->m_nUID
                . "<li>"
                . 'UserName=' . $this->m_sVistaUserID
                . "<li>"
                . 'CTID=' . $this->m_sCurrentTicketID
                . "<li>"
                . 'PBS=' . print_r($this->m_aPersonalBatchStack,TRUE)
                . "</ol>";
    }
    
    private function clearAllContext()
    {
        $this->logoutMdwsSubsystem();
        $this->m_nLastUpdateTimestamp = microtime(TRUE);
        $this->m_nInstanceClearedTimestamp = microtime(TRUE);
        $this->m_nInstanceTimestamp = null;
        $this->m_sCurrentTicketID = null;
        $this->m_aPersonalBatchStack = null;
        $this->m_nUID = 0;
        $this->m_sVistaUserID = null;
        $this->m_sVAPassword = null;
        /*
        $this->m_mdwsClient=NULL; // MdwsDao
         * 
         */
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
            } catch (\Exception $ex) {
                error_log('Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
            }
        }
        $this->clearAllContext();
        $this->serializeNow();
        return '';  //TODO
    }
    
    private function logoutMdwsSubsystem() 
    {
        try {
            $this->serializeNow('Logging out of MDWS',FALSE);
            $this->getMdwsClient()->disconnect();
            return "";
        } catch (\Exception $ex) {
            return array(
                "ERRNUM"=>MdwsUtils::getErrorNumberForException($ex), 
                "ERRSUMMARY"=>"Error on disconnect", 
                "ERRDETAIL"=>$ex);
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
    public function forceSessionRefresh($grace_seconds
            , $ignore_session_timeout=FALSE)
    {
        if(!user_is_logged_in() || $this->getUID() == 0)
        {
            //Never time out if no one is logged in anyways.
             $_SESSION['CREATED'] = time();  // update creation time
        } else {
            $kickout = FALSE;
            $currentpath = current_path();
            if(!$ignore_session_timeout)
            {
                $limit_max = USER_TIMEOUT_SECONDS 
                        + USER_TIMEOUT_GRACE_SECONDS 
                        + KICKOUT_DIRTYPADDING;
                if (!isset($_SESSION['CREATED']) 
                        || (time() - $_SESSION['CREATED']) > $limit_max) 
                {
                    error_log('WORKFLOWDEBUG>>>Session key timeout of '
                            .$limit_max
                            .' seconds reached so kickout activated for uid='
                            .$this->getUID()
                            ."\nURL at limit timeout = ".$currentpath);
                    $usermsg = 'Session canceled because '
                            . 'there was no user activity for over '
                            . $limit_max . ' seconds.';
                    $this->forceKickoutNow($usermsg,ERRORCODE_KICKOUT_TIMEOUT,$this);
                    $kickout=TRUE;
                }
            }
            if (!$kickout 
                    && (!isset($_SESSION['CREATED']) 
                            || (time() - $_SESSION['CREATED']) > $grace_seconds))
            {
                // session started more than SESSION_REFRESH_DELAY seconds ago
                error_log('WORKFLOWDEBUG>>>Session key timeout of '
                        .$grace_seconds
                        .' seconds reached so generated new key for uid='.$this->getUID()
                        ."\nURL at key timeout = ".$currentpath);
                session_regenerate_id(FALSE);   // change session ID for the current session and invalidate old session ID
                $_SESSION['CREATED'] = time();  // update creation time
            }
        }
    }
    
    /*
     * @description Return the session's MDWS client DAO
     * @return IMdwsDao
     */
    public function getMdwsClient($bRefreshConnection=FALSE) // TODO - remove arg from function
    {
        if (!isset($this->m_mdwsClient)) {
            $this->m_mdwsClient = MdwsDaoFactory::getMdwsDao(MDWS_EMR_FACADE);
        }
        return $this->m_mdwsClient;
        
        //error_log('WORKFLOWDEBUG>>>Called getMdwsClient for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
        if($bRefreshConnection && isset($this->m_mdwsClient))
        {
            error_log('WORKFLOWDEBUG>>>Refreshing the existing Mdws connection for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
            $this->m_mdwsClient->disconnect();
            unset($this->m_mdwsClient);
        }
        if (isset($this->m_mdwsClient)) 
        {
            error_log('WORKFLOWDEBUG>>>Using existing Mdws connection for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
        } else {
            error_log('WORKFLOWDEBUG>>>Creating NEW Mdws connection for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] 
                    .' in ' . $this->m_nInstanceTimestamp . " CALLER==> " . Context::debugGetCallerInfo(4));
            try 
            {
                $this->m_mdwsClient = MdwsDaoFactory::getMdwsDao(MDWS_EMR_FACADE);
                if(isset($this->m_sVistaUserID))
                {
                    //Since we have credentials, go ahead and authenticate now
                    $this->m_mdwsClient->connectAndLogin(VISTA_SITE, $this->m_sVistaUserID, $this->m_sVAPassword);
                    $this->serializeNow();   //20140701     
                } else {
                    //drupal_set_message('Did NOT hav a user id!', 'warning');
                }
            } catch (\Exception $ex) {
                $sMsg = "Error connecting to EMR service as [{$this->m_sVistaUserID}] -> " . $ex;
                if(FALSE !== strpos($ex, 'Timeout waiting for response from VistA'))
                {
                    global $user;
                    $tempUID = $user->uid;    
                    try
                    {
                        //TODO send an email too
                        $updated_dt = date("Y-m-d H:i:s", time());
                        db_insert('raptor_user_activity_tracking')
                        ->fields(array(
                                'uid'=>$tempUID,
                                'action_cd' => ERRORCODE_VISTATIMEOUT,
                                'ipaddress' => $_SERVER['REMOTE_ADDR'],
                                'updated_dt'=>$updated_dt,
                            ))
                            ->execute();
                    } catch (\Exception $ex) {
                        error_log('Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
                    }
                }
                error_context_log($sMsg);
                //die($sMsg);
            }
        }
        return $this->m_mdwsClient;
    }
}
