<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140702 
 */

namespace raptor;

require_once("data_utility.php");
require_once("data_user.php");
require_once("MdwsDaoFactory.php");
require_once('VixDao.php');

defined('CONST_NM_RAPTOR_CONTEXT')
    or define('CONST_NM_RAPTOR_CONTEXT', 'RAPTOR_140718A');

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
    private $m_nInstanceTimestamp = NULL;
    private $m_nInstanceClearedTimestamp = NULL;
    private $m_nInstanceActionTimestamp = NULL;     //Periodically update to check for timeout

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

    public static function debugGetCallerInfo($nAncestry=3,$nShowLevelCount=1,$bReturnArray=FALSE)
    {
        $aResult = array();
        $aResult['StartAncestryLevel'] = $nAncestry;
        $aResult['ShowLevels'] = $nShowLevelCount;
        $trace=debug_backtrace();
        $nLastAncestry = $nAncestry + $nShowLevelCount - 1;
        for($nAncestry = 1; $nAncestry <= $nLastAncestry; $nAncestry++  )
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
            return print_r($aResult,TRUE);
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
        error_log('WORKFLOWDEBUG>>>Called constructor for Context!');
        if(!is_numeric($nUID))
        {
            die('The UID passed into contructor of Context must be numeric, but instead got "'.$nUID.'"');
        }

        $this->m_nUID = $nUID;
        $this->m_nInstanceTimestamp = microtime(TRUE);  //Capture the time this instance was created.
        $this->m_nInstanceActionTimestamp = time();
        Context::debugDrupalMsg('<h1>Hi from CONSTRUCTOR('.$nUID.') Context: New instance created at [' . microtime(TRUE) . ']</h1>' . $this->getContextHtmlDebugInfo(),'status');
    }    

    public function getInstanceTimestamp()
    {
        return $this->m_nInstanceTimestamp;
    }

    /**
     * Factory implements session singleton.
     * @return \rapto\Context 
     */
    public static function getInstance($forceReset=FALSE)
    {
        if(!isset($_SESSION)) 
        { 
            session_start();                    //This must be dones at least once.
            drupal_session_started(TRUE);       //If we dont do this we risk warning messages elsewhere.
        }
        global $user;
        $bLocalReset = FALSE;
        if(user_is_logged_in())
        {
            $tempUID = $user->uid;   
        } else {
            $tempUID = 0;
        }
        $bSessionResetFlagDetected = ($forceReset || (isset($_GET['reset_session']) && $_GET['reset_session'] == 'YES'));
        if(isset($_SESSION[CONST_NM_RAPTOR_CONTEXT]) && !$bSessionResetFlagDetected)
        {
            //We will return this instance unless ...
            $candidate=unserialize($_SESSION[CONST_NM_RAPTOR_CONTEXT]);
            /*
            error_log('TOP1 CONTEXT NOW >>> ' . $candidate->m_nUID . 'vs' . $tempUID 
                    . ' of ' . $candidate->m_nInstanceTimestamp 
                    . ' with ats=' . $candidate->m_nInstanceActionTimestamp 
                    . ' at ' . time());
            */
            $wmodeParam = $candidate->getWorklistMode();
            $candidateUID = $candidate->getUID();
            if($candidateUID == 0 && $tempUID != 0 && !$candidate->hasForceLogoutReason())
            {
                //Convert this session instance into instance for the UID, normal occurrence to do this after a login.
                $candidate->m_nUID = $tempUID;
                $candidate->serializeNow('Set the uid to the user->uid');
            } else 
            if($candidate->m_nUID > -1 && $candidate->getUID() !== $tempUID)
            {
                //This can happen if a user left without proper logout.
                drupal_set_message('Must reset because candidate UID['.$candidate->getUID().'] != current UID['.$tempUID.']', 'error');
                $bLocalReset=TRUE;
                $candidate=NULL;
                $wmodeParam='P';    //Hardcode assumption for now.
            } else {
                $bLocalReset=FALSE;
                //drupal_set_message('<h2>LOOKNOW HERE --> </h2> ' . print_r($candidate,TRUE));
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
            error_log('WORKFLOWDEBUG>>>NO EXISTING SESSION!!! Not using an existing session: bSessionResetFlagDetected='.$bSessionResetFlagDetected . ' from '. $_SERVER['REMOTE_ADDR'] 
                    . " CALLER==> " . Context::debugGetCallerInfo(10));
            Context::debugDrupalMsg('Not using an existing session: bSessionResetFlagDetected='.$bSessionResetFlagDetected);
            $bLocalReset=TRUE;
            $candidate=NULL;
            $wmodeParam='P';    //Hardcode assumption for now.
        }
        
        $bAccountConflictDetected = FALSE;      //Set to true if something funny is going on.
        if($candidate !== NULL)
        {
            $nElapsedSeconds = time() - $candidate->m_nInstanceActionTimestamp;
        } else {
            $nElapsedSeconds = 0;
        }
        if(isset($tempUID) && $tempUID !== 0 && ($nElapsedSeconds > 10))
        {
            try
            {
                //First make sure no one else is logged in as same UID
                $resultOther = db_select('raptor_user_recent_activity_tracking','u')
                        ->fields('u')
                        ->condition('uid',$tempUID,'=')
                        ->condition('ipaddress',$_SERVER['REMOTE_ADDR'],'<>')
                        ->orderBy('most_recent_action_dt','DESC')
                        ->execute();
                if($resultOther->rowCount() > 0)
                {
                    $resultMe = db_select('raptor_user_recent_activity_tracking','u')
                            ->fields('u')
                            ->condition('uid',$tempUID,'=')
                            ->condition('ipaddress',$_SERVER['REMOTE_ADDR'],'=')
                            ->execute();
                    if($resultMe->rowCount() > 0)
                    {
                        $other = $resultOther->fetchAssoc();
                        $me = $resultMe->fetchAssoc();
        		$bAccountConflictDetected = $other['most_recent_action_dt'] >= $me['most_recent_action_dt'];
                        if($bAccountConflictDetected)
                        {
                            error_log('Account conflict has been detected for UID=['.$tempUID.'] this user at '.$_SERVER['REMOTE_ADDR']. ' other user at '.$other['ipaddress'] . '>>> TIMES = other[' . $other['most_recent_action_dt'] .'] vs this['. $me['most_recent_action_dt'] . ']');
                        } else {
                            error_log('No account conflict detected on check (es='.$nElapsedSeconds.') for UID=['.$tempUID.'] this user at '.$_SERVER['REMOTE_ADDR']. ' other user at '.$other['ipaddress'] . '>>> TIMES = other[' . $other['most_recent_action_dt'] .'] vs this['. $me['most_recent_action_dt'] . ']');
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
                            'updated_dt'=>$updated_dt,
                        ))
                        ->execute();
                    $updated_dt = date("Y-m-d H:i:s", time());

                    //Write the recent activity to the single record that tracks it too.
                    db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$tempUID,'ipaddress'=>$_SERVER['REMOTE_ADDR'],))
                    ->fields(array(
                            'uid'=>$tempUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
                            'most_recent_action_dt'=>$updated_dt,
                            'most_recent_action_cd' => UATC_GENERAL,
                        ))
                        ->execute();
                }
                //Update the session info.
                $candidate->m_nInstanceActionTimestamp = time();
                $candidate->serializeNow(); //Store this now!!!
            } catch (\Exception $ex) {
                error_log('Trouble updating raptor_user_activity_tracking>>>'.print_r($ex,TRUE));
            }
        }
        
        //TODO FOR PROD VERSION -- Timeout the session object after a period of time
        if($candidate==NULL)    // $nElapsedSeconds > MAXINACTIVITYSECONDS 
        {
            $bLocalReset=TRUE;
        } else {
            
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
                error_context_log('Clearing cache except login credentials for VistaUserID=' . $current->m_sVistaUserID);
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
                $candidate->m_aForceLogoutReason = array();
                $candidate->m_aForceLogoutReason['code'] = 101;
                $candidate->m_aForceLogoutReason['text'] = 'Another workstation has logged in as the same RAPTOR user account "'. $candidate->m_sVistaUserID.'"';
                $msg = 'You are kicked out because another workstation has logged in as the same RAPTOR user account "'. $candidate->m_sVistaUserID.'"';
                error_log('CONTEXT KICKOUT ACCOUNT CONFLICT DETECTED ON ['.$candidate->m_sVistaUserID.'] >>> ' . time() . "\n\tSESSION>>>>" . print_r($_SESSION,TRUE));
                $candidate->m_sVistaUserID = 'kickout_' . $candidate->m_sVistaUserID;
                $candidate->m_sVAPassword = NULL;
                $candidate->serializeNow(); //Store this now!!!
            }
        }
        
        if (!isset($candidate->m_mdwsClient))
        {
            error_log('WORKFLOWDEBUG>>>getInstance has NO existing Mdws connection for ' . $candidate->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $candidate->m_nInstanceTimestamp);
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
        if($this->m_oVixDao == NULL)
        {
            $this->m_oVixDao = new \raptor\VixDao($this->m_sVistaUserID,$this->m_sVAPassword);
        }
        return $this->m_oVixDao;
    }

    function getUserInfo()
    {
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
        $this->m_sCurrentTicketID = $sTrackingID;
        $this->serializeNow();        
    }

    /**
     * @param array $aPBatch array representing stack of tracking IDs, last one in array is top of the stack
     */
    function setPersonalBatchStack($aPBatch)
    {
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
                        'updated_dt'=>$updated_dt,
                    ))
                    ->execute();
                    //Write the recent activity to the single record that tracks it too.
                    db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$tempUID,'ipaddress'=>$_SERVER['REMOTE_ADDR'],))
                    ->fields(array(
                            'uid'=>$tempUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
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
                        'action_cd' => UATC_ERR_AUTHENTICATION,
                        'ipaddress' => $_SERVER['REMOTE_ADDR'],
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
                        'updated_dt'=>$updated_dt,
                    ))
                    ->execute();
                    //Write the recent activity to the single record that tracks it too.
                db_merge('raptor_user_recent_activity_tracking')
                    ->key(array('uid'=>$this->m_nUID,'ipaddress'=>$_SERVER['REMOTE_ADDR'],))
                    ->fields(array(
                            'uid'=>$this->m_nUID,
                            'ipaddress' => $_SERVER['REMOTE_ADDR'],
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
    
    private function logoutMdwsSubsystem() {
        try {
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
     * We call this whenever we change something significant in the instance.
     */
    private function serializeNow($logMsg = '')
    {
        if (!isset($this->m_mdwsClient))
        {
            error_log('WORKFLOWDEBUG>>>serializeNow has NO existing Mdws connection for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
        } else {
            error_log('WORKFLOWDEBUG>>>serializeNow HAS HAPPY existing Mdws connection for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
        }
        $this->m_nInstanceActionTimestamp = time(); //Capture the time whever we serialize.
        error_log('WORKFLOWDEBUG>>>Processing serializeNow(' .$logMsg. ') from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp . ' (actiontimestamp=' . $this->m_nInstanceActionTimestamp . ')' );
        $this->bNeedsSave=FALSE;    //Because now we are saving it.
        if(!isset($this->m_nInstanceTimestamp))
        {
            $this->m_nInstanceTimestamp = microtime(TRUE);
        }
        $_SESSION[CONST_NM_RAPTOR_CONTEXT] = serialize($this);
        error_log('WORKFLOWDEBUG>>>Serialized context at ' . microtime(TRUE) . ' for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] 
                .' in ' . $this->m_nInstanceTimestamp . " CALLER==> " . Context::debugGetCallerInfo(3));
        
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
        
        error_log('WORKFLOWDEBUG>>>Called getMdwsClient for ' . $this->m_sVistaUserID . ' from '. $_SERVER['REMOTE_ADDR'] .' in ' . $this->m_nInstanceTimestamp);
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
                                'action_cd' => UATC_ERR_VISTATIMEOUT,
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
