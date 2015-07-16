<?php

/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */

namespace raptor_mdwsvista;

require_once 'IMdwsDao.php';
require_once 'MdwsUtils.php';
require_once 'MdwsNewOrderUtils.php';
require_once 'WorklistData.php';
require_once 'ProtocolSupportingData.php';

class MdwsDao implements \raptor_mdwsvista\IMdwsDao
{

    private $m_groupname = 'MdwsDaoGroup';
    private $m_oPS = NULL;
    private $instanceTimestamp;
    private $authenticationTimestamp;
    private $errorCount;
    private $mdwsClient;
    private $isAuthenticated;
    private $currentFacade;
    // these need to be cached for re-try purposes
    private $userSiteId;
    private $userAccessCode;
    private $userVerifyCode;
    private $duz;
    private $selectedPatient;

    public function __construct()
    {
        //Load relevant modules
        module_load_include('php', 'raptor_glue', 'core/Config');
        module_load_include('php', 'raptor_datalayer', 'core/data_context');
        module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');
        $this->instanceTimestamp = microtime();
        //error_log('LOOK Created MdwsDao instance ' . $this->instanceTimestamp);
        $this->errorCount = 0; // initializing for clarity
        $this->initClient();
    }

    public function getIntegrationInfo()
    {
        return "MDWS v2.5"; //TODO get real runtime version number
    }

    /**
     * Make it simpler to output details about this instance.
     * @return text
     */
    public function __toString()
    {
        try {
            return 'MdwsDao instance created at ' . $this->instanceTimestamp
                    . ' current error count=[' . $this->errorCount . ']'
                    . ' isAuthenticated=[' . $this->isAuthenticated . ']'
                    . ' selectedPatient=[' . $this->selectedPatient . ']'
                    . ' duz=[' 
                    . $this->duz 
                    . ']';
        } catch (\Exception $ex) {
            return 'Cannot get toString of MdwsDao because ' . $ex;
        }
    }

    public function initClient()
    {
        //we'll use the EmrSvc facade for initialization but this may change when a SOAP call is executed
        $this->currentFacade = EMRSERVICE_URL;
        $this->mdwsClient = MdwsDaoFactory::getSoapClientByFacade($this->currentFacade);
        //error_log(print_r($this->mdwsClient, true));
        // $this->currentSoapClientFunctions = $this->mdwsClient->__getFunctions();        
    }

    public function disconnect()
    {
        //error_log('Called MdwsDao disconnect!!!!!');
        $this->errorCount = 0;
        $this->isAuthenticated = FALSE;
        try {
            $this->mdwsClient->disconnect();
        } catch (\Exception $e) {
            // just swallow - generally don't care if this errored
        }
    }

    public function makeQuery($functionToInvoke, $args)
    { //, $retryLimit = 1) {
        if (!$this->isAuthenticated())
        {
            global $base_url;
            drupal_set_message('TIP: <a href="' . $base_url . '/user/logout">Logout</a> and <a href="' . $base_url . '/user/login">log back in</a></a>');
            throw new \Exception('Not authenticated in MdwsDao instance '
            . $this->instanceTimestamp
            . '(previous authentication was '
            . $this->authenticationTimestamp . ')'
            . ": Must authenticate before requesting data>>>" . \raptor\Context::debugGetCallerInfo(2, 10));
        }
        //error_log('TODO:makeQuery  --- about to do stuff in makeQuery for '.$functionToInvoke.'...');
        //  if ($retryLimit < 0) {
        //      die('Retry limit exceeded in MdwsDao->makeQuery for '.$functionToInvoke.' with args: '.print_r($args));
        //  }
        try 
        {
            // use the DAO factory to obtain the correct SOAP client
            // use the previous SOAP request/response headers to set the ASP.NET_SessionID header if the facde has changed
            //error_log('About to get getFacadeNameByFunction '.microtime());
            $wsdlForFunction = MdwsDaoFactory::getFacadeNameByFunction($functionToInvoke);
            //error_log('Done getting getFacadeNameByFunction '.microtime());
            //if ($wsdlForFunction != $this->currentFacade) {   //Serialization issue with PHP SOAP will try to get new one eacvh time
            //error_log('About got set properties of MDWS SOAP CLIENT '.microtime());
            $this->currentFacade = $wsdlForFunction;
            $cookie = $this->mdwsClient->_cookies["ASP.NET_SessionId"][0];

            $this->mdwsClient = MdwsDaoFactory::getSoapClientByFunction($functionToInvoke);
            $this->mdwsClient->__setCookie("ASP.NET_SessionId", $cookie);
            //error_log(print_r($this->mdwsClient, true));
            //error_log('Done setting properties of MDWS SOAP CLIENT '.microtime());
            //}
            // functionToInvoke is the name of the SOAP call, args is the list of arguments
            // PHP seems to like this format (using the functionToInvoke string as the SOAP name) just fine!
            //error_log('TODO:makeQuery  --- soap client looks like this>>>' . print_r($this->mdwsClient,TRUE));
            $soapResult = $this->mdwsClient->$functionToInvoke($args);
            // TO object is always stored in "soapCallResult". e.g. select result stored in 'selectResult'
            $resultVarName = strval($functionToInvoke) . "Result";
            // this block of code before the return $soapResult statement is error checking/auto-re-authentication
            if (isset($soapResult->$resultVarName)) //20140723 JAM why would this ever not be set?? ->  //20140707 FJF prevent missing property error message
            {
                $TOResult = $soapResult->$resultVarName;
                // error_log('TODO:makeQuery  --- soapResult in makeQuery okay? >>>' . isset($TOResult->fault));
                if (isset($TOResult->fault))
                {
                    // TODO:makeQuery  - haven't tested this auto-reconnect code atl all. need to write tests
                    // we received a fault - might be a session timeout in which case we want to handle gracefully
                    error_log('Encounted a fault in makeQuery >>>' . $TOResult->fault->message);
                    if (strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_1) !== FALSE ||
                            strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_2) !== FALSE ||
                            strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_3) !== FALSE ||
                            strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_4) !== FALSE)
                    {
                        $this->initClient();
                        error_log('makeQuery  --- getting the credentials for fault resolution now>>>' . $TOResult->fault->message);
                        $this->connectAndLogin($this->userSiteId, $this->userAccessCode, $this->userVerifyCode);
                        return $this->makeQuery($functionToInvoke, $args); //, $retryLimit-1);
                    } else
                    {
                        $stacktrace = \raptor\Context::debugGetCallerInfo(10);
                        error_log('Found a fault in makeQuery>>>'
                                . print_r($TOResult, TRUE)
                                . "Stack trace... " . $stacktrace);
                        return $soapResult;
                    }
                } else
                {
                    //error_log('TODO:makeQuery Good news --- no fault in makeQuery>>>'.$functionToInvoke);
                }
            } else
            {
                $soapinfo = isset($TOResult->fault->message) ? ' soapfault='.$TOResult->fault->message : '';
                $synopsis="Did NOT find value for soapResult->{$resultVarName}";
                $stacktrace = \raptor\Context::debugGetCallerInfo(10);
                error_log("Unexpected fault in makeQuery($functionToInvoke)"
                        . "\n\tError summary=$synopsis"
                        . "\n\tInput args=" . print_r($args, TRUE)
                        . "\n\tRawSoap=".print_r($soapResult,TRUE)        
                        . "\nStack trace... " . $stacktrace);
                throw new \Exception("$synopsis in makeQuery($functionToInvoke)"
                . $soapinfo
                . " RawSoap=".print_r($soapResult,TRUE)        
                . "<br>Stack trace..." . $stacktrace);
            }

            return $soapResult;
        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), "connection was forcibly closed") !== FALSE)
            {
                error_log("Exception in makeQuery($functionToInvoke) --- connection was closed makeQuery>>>" 
                        . $ex->getMessage());
                $this->initClient();
                $this->connectAndLogin($this->userSiteId, $this->userAccessCode, $this->userVerifyCode);
                return $this->makeQuery($functionToInvoke, $args); //, $retryLimit-1);
            }
            // any other exceptions that may be related to timeout? add here as found
            else
            {
                error_log("Exception in makeQuery($functionToInvoke) --- about to throw exception in makeQuery/else>>>" 
                        . "\nError Message=".$ex->getMessage() 
                        . "\n\t$resultVarName=" . print_r($resultVarName, TRUE));
                throw $ex;
            }
        }
    }

    public function connectAndLogin($siteCode, $username, $password)
    {
        //drupal_set_message('About to login to MDWS as ' . $username);
        error_log('Starting connectAndLogin at ' . microtime());
        try {
            $connectResult = $this->mdwsClient->connect(array("sitelist" => $siteCode))->connectResult;
            if (isset($connectResult->fault))
            {
                if ($this->errorCount > MDWS_CONNECT_MAX_ATTEMPTS)
                {
                    throw new \Exception($connectResult->fault->message);
                }
                // erroneous error message - re-try connect for configured # of re-tries
                if (strpos($connectResult->fault->message, "XUS SIGNON SETUP is not registered to the option XUS SIGNON") || strpos($connectResult->fault->message, "XUS INTRO MSG is not registered to the option XUS SIGNON"))
                {
                    $this->errorCount++;
                    // first sleep for a short configurable time...
                    usleep(MDWS_QUERY_RETRY_WAIT_INTERVAL_MS * 1000);
                    return $this->connectAndLogin($siteCode, $username, $password);
                } else
                {
                    throw new \Exception($connectResult->fault->message);
                }
            }

            // successfully connected! now let's login'
            $loginResult = $this->mdwsClient->login(array("username" => $username, "pwd" => $password, "context" => MDWS_CONTEXT));
            if (isset($loginResult->loginResult))    //20140707 FJF prevent missing property msg
            {
                $TOResult = $loginResult->loginResult;
                if (isset($TOResult->fault))
                {
                    throw new \Exception($TOResult->fault->message);
                }
            }
            $this->errorCount = 0; // reset on success
            $this->isAuthenticated = TRUE;
            $this->authenticationTimestamp = microtime();
            // cache for transparent re-authentication on MDWS-Vista timeout
            $this->userSiteId = $siteCode;
            $this->userAccessCode = $username;
            $this->userVerifyCode = $password;
            $this->duz = $TOResult->DUZ;

            error_log("Authenticated in MdwsDao as duz={$this->duz} " 
                    . $this->instanceTimestamp 
                    . ' at ' 
                    . $this->authenticationTimestamp);

            // transparently re-select last selected patient
            if (isset($this->selectedPatient) && $this->selectedPatient != '')
            {
                error_log('Transparently re-selecting patient ID>>>[' . $this->selectedPatient . ']');
                $this->makeQuery('select', array('DFN' => $this->selectedPatient));
            }

            error_log("Finished connectAndLogin duz={$this->duz} at " . microtime());
            return $loginResult;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function connectRemoteSites($applicationPassword)
    {
        throw new \Exception("This function has not been implemented");
    }

    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * @deprecated use getEHRUserID instead!
     */
    function getDUZ($fail_if_missing=TRUE)
    {
        return $this->getEHRUserID($fail_if_missing);
    }
    
    public function getEHRUserID($fail_if_missing=TRUE)
    {
        if (!$this->isAuthenticated)
        {
            throw new \Exception('Not authenticated');
        }
        if($fail_if_missing && trim($this->duz) == '')
        {
            throw new \Exception("Did NOT find an EHR user id value in ".$this);
        }
        return $this->duz;
    }

    /**
     * When context changes this has to change.
     * @param type $sPatientID
     */
    public function setPatientID($sPatientID)
    {
        $this->selectedPatient = $sPatientID;
    }

    /**
     * Context needs simple way of getting patient ID.
     * @return the patientid associated with an order
     */
    public function getPatientIDFromTrackingID($sTrackingID)
    {

        //$debugmsg = 'LOOK getPatientIDFromTrackingID for ['.$sTrackingID.']';
        //error_log($debugmsg);
        //Get the IEN from the tracking ID
        $aParts = (explode('-', $sTrackingID));
        if (count($aParts) == 2)
        {
            $nIEN = $aParts[1]; //siteid-IEN
        } else
        if (count($aParts) == 1)
        {
            $nIEN = $aParts[0]; //Just IEN
        } else
        {
            $sMsg = 'Did NOT recognize format of tracking id [' . $sTrackingID . '] expected SiteID-IEN format!';
            error_log($sMsg);
            throw new \Exception($sMsg);
        }
        //$debugmsg = 'LOOK getPatientIDFromTrackingID for IEN=['.$nIEN.']';
        //error_log($debugmsg);

        $pid = MdwsUtils::getVariableValue($this, '$P(^RAO(75.1,' . $sTrackingID . ',0),U,1)');
        //$debugmsg = 'LOOK Found PID as ['.$pid.']';
        if ($pid == NULL)
        {
            $msg = 'Expected to find a PID but did not find one for ticket [' . $sTrackingID . '] '
                    . '<br>Details...' . print_r($aParts, TRUE)
                    . '<br>Soapresult>>>' . print_r($serviceResponse, TRUE);
            throw new \Exception($msg);
        }
        //error_log($debugmsg);
        return $pid;
    }

    public function getVistaAccountKeyProblems()
    {
        $userDuz = $this->getDUZ();
        return \raptor_mdwsvista\MdwsUserUtils::getVistaAccountKeyProblems($this, $userDuz);
    }

    /**
     * Gets dashboard details for the currently selected ticket of the session
     * 
     * !!! IMPORTANT TODO --- MAKE THE OVERRIDE NOT STATEFUL so we can precache!!!!!!
     */
    function getDashboardDetailsMap($override_tracking_id = NULL)
    {
        error_log("LOOK START getDashboardDetailsMap");
        $aResult = array();
        $oContext = \raptor\Context::getInstance();
        if ($oContext != NULL)
        {
            if ($override_tracking_id == NULL)
            {
                $tid = $oContext->getSelectedTrackingID();
            } else
            {
                $tid = $override_tracking_id;
            }
            $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                $sThisResultName = "{$tid}_getDashboardDetailsMap";
                $aCachedResult = $oRuntimeResultFlexCacheHandler->checkCache($sThisResultName);
                if ($aCachedResult !== NULL)
                {
                    //Found it in the cache!
                    return $aCachedResult;
                }
            }

            //Create it now and add it to the cache
            $oWL = new \raptor_mdwsvista\WorklistData($oContext);
            $aResult = $oWL->getDashboardMap();
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                try {
                    $oRuntimeResultFlexCacheHandler->addToCache($sThisResultName, $aResult, CACHE_AGE_SITEVALUES);
                } catch (\Exception $ex) {
                    error_log("Failed to cache $sThisResultName result because " . $ex->getMessage());
                }
            }
        }
        return $aResult;
    }

    public function getWorklistDetailsMap()
    {
        try 
        {
            $aResult = array();
            $oContext = \raptor\Context::getInstance();
            if ($oContext != NULL)
            {
                $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
                $sThisResultName = 'getWorklistDetailsMapData';
                if ($oRuntimeResultFlexCacheHandler != NULL)
                {
                    $aCachedResult = $oRuntimeResultFlexCacheHandler->checkCache($sThisResultName);
                    if ($aCachedResult !== NULL)
                    {
                        //Found it in the cache!
                        return $aCachedResult;
                    }
                }

                //Create it now and add it to the cache
                $oWL = new \raptor_mdwsvista\WorklistData($oContext);
                $aResult = $oWL->getWorklistRows();
                if ($oRuntimeResultFlexCacheHandler != NULL)
                {
                    try {
                        $oRuntimeResultFlexCacheHandler->addToCache($sThisResultName, $aResult, CACHE_AGE_SITEVALUES);
                    } catch (\Exception $ex) {
                        error_log("Failed to cache $sThisResultName result because " . $ex->getMessage());
                    }
                }
            }
            return $aResult;
        } catch (\Exception $ex) {
            throw new \Exception("Failed to getWorklistDetailsMap", 99876, $ex);
        }
    }

    private function getProtocolSupportingDataNoCache($oContext, $function_name, $args = NULL)
    {
        $aResult = array();
        if ($this->m_oPS == NULL)
        {
            $this->m_oPS = new \raptor_mdwsvista\ProtocolSupportingData($oContext);
        }
        if ($args != NULL)
        {
            $aResult = $this->m_oPS->$function_name($this);
        } else {
            $aResult = $this->m_oPS->$function_name();
        }
        return $aResult;
    }
    
    private function getProtocolSupportingData($function_name, $args = NULL, $cache_item_name=NULL)
    {
        //error_log("LOOK TEMP getProtocolSupportingData($function_name,$args)");
        try 
        {
            $oContext = \raptor\Context::getInstance();
            if($oContext == NULL)
            {
                throw new \Exception("Cannot execute $function_name without a valid context!");
            }
            $aResult = array();
            if($cache_item_name == NULL)
            {
                //Simply call it, no cache.
                $aResult = $this->getProtocolSupportingDataNoCache($oContext, $function_name, $args);
            } else {
                //Utilize the cache.
                $sThisResultName = $cache_item_name;
                $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
                if ($oRuntimeResultFlexCacheHandler != NULL)
                {
                    $aCachedResult = $oRuntimeResultFlexCacheHandler->checkCache($sThisResultName);
                    if ($aCachedResult !== NULL)
                    {
                        //Found it in the cache!
                        return $aCachedResult;
                    }
                }

                //Create it now and add it to the cache
                $aResult = $this->getProtocolSupportingDataNoCache($oContext, $function_name, $args);
                if ($oRuntimeResultFlexCacheHandler != NULL)
                {
                    try {
                        $oRuntimeResultFlexCacheHandler->addToCache($sThisResultName, $aResult, CACHE_AGE_SITEVALUES);
                    } catch (\Exception $ex) {
                        error_log("Failed to cache $sThisResultName result because " . $ex->getMessage());
                    }
                }
            }
            return $aResult;
        } catch (\Exception $ex) {
            throw new \Exception("Failed to $sThisResultName", 99876, $ex);
        }
    }

    public function getAllHospitalLocationsMap()
    {
        $args = array($this);
        return $this->getProtocolSupportingData('getAllHospitalLocations', $args);
    }

    public function getAllergiesDetailMap()
    {
        return $this->getProtocolSupportingData('getAllergiesDetail');
    }

    public function getOrderOverviewMap()
    {
        return $this->getProtocolSupportingData('getOrderOverview');
    }

    public function getVitalsSummaryMap()
    {
        return $this->getProtocolSupportingData('getVitalsSummary');
    }

    public function getVitalsDetailMap()
    {
        return $this->getProtocolSupportingData('getVitalsDetail');
    }

    public function getProcedureLabsDetailMap()
    {
        return $this->getProtocolSupportingData('getProcedureLabsDetail');
    }

    public function getDiagnosticLabsDetailMap()
    {
        return $this->getProtocolSupportingData('getDiagnosticLabsDetail');
    }

    public function getPathologyReportsDetailMap()
    {
        return $this->getProtocolSupportingData('getPathologyReportsDetail');
    }

    public function getSurgeryReportsDetailMap()
    {
        return $this->getProtocolSupportingData('getSurgeryReportsDetail');
    }

    public function getProblemsListDetailMap()
    {
        return $this->getProtocolSupportingData('getProblemsListDetail');
    }

    public function getRadiologyReportsDetailMap()
    {
        return $this->getProtocolSupportingData('getRadiologyReportsDetail');
    }

    public function getMedicationsDetailMap($atriskmeds = NULL)
    {
        $args = array($atriskmeds);
        return $this->getProtocolSupportingData('getMedicationsDetail', $args);
    }

    public function getNotesDetailMap()
    {
        return $this->getProtocolSupportingData('getNotesDetail');
    }

    public function getVitalsDetailOnlyLatestMap()
    {
        return $this->getProtocolSupportingData('getVitalsDetailOnlyLatest');
    }

    public function getEGFRDetailMap()
    {
        return $this->getProtocolSupportingData('getEGFRDetail');
    }

    public function getPatientDashboardMap()
    {
        return $this->getProtocolSupportingData('getPatientDashboard');
    }

    public function getPendingOrdersMap()
    {
        return $this->getProtocolSupportingData('getPendingOrdersMap'); //Yes, has Map suffix
    }

    public function getRawVitalSignsMap()
    {
        return $this->getProtocolSupportingData('getRawVitalSigns');
    }

    public function getImagingTypesMap()
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::getImagingTypes($this);
    }

    public function createNewRadiologyOrder($orderChecks, $args)
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::createNewRadiologyOrder($this, $orderChecks, $args);
    }

    public function createUnsignedRadiologyOrder($orderChecks, $args)
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::createUnsignedRadiologyOrder($this, $orderChecks, $args);
    }

    public function getOrderableItems($imagingTypeId)
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::getOrderableItems($this, $imagingTypeId);
    }

    public function getRadiologyOrderChecks($args)
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::getRadiologyOrderChecks($this, $args);
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        return \raptor_mdwsvista\MdwsNewOrderUtils::getRadiologyOrderDialog($this, $imagingTypeId, $patientId);
    }

    public function getProviders($neworderprovider_name)
    {
        return \raptor_mdwsvista\MdwsUserUtils::getProviders($this, $neworderprovider_name);
    }

    public function getUserSecurityKeys()
    {
        $userDuz = $this->getDUZ();
        return \raptor_mdwsvista\MdwsUserUtils::getUserSecurityKeys($this, $userDuz);
    }

    public function isProvider()
    {
        $userDuz = $this->getDUZ();
        return \raptor_mdwsvista\MdwsUserUtils::isProvider($this, $userDuz);
    }

    public function userHasKeyOREMAS()
    {
        $userDuz = $this->getDUZ();
        return \raptor_mdwsvista\MdwsUserUtils::userHasKeyOREMAS($this, $userDuz);
    }

    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig)
    {
        return \raptor_mdwsvista\MdwsUtils::cancelRadiologyOrder($this, $patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig);
    }

    public function convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs=1000)
    {
        return \raptor_mdwsvista\MdwsUtils::convertSoapLabsToGraph($patientInfo, $egfrFormula, $allLabs, $limitMaxLabs);
    }

    public function convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates=5)
    {
        return \raptor_mdwsvista\MdwsUtils::convertSoapVitalsToGraph($vitalsdata, $soapResult, $max_dates);
    }

    public function getChemHemLabs()
    {
        return \raptor_mdwsvista\MdwsUtils::getChemHemLabs($this);
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        return \raptor_mdwsvista\MdwsUtils::getEncounterStringFromVisit($vistitTo);
    }

    public function getHospitalLocations($startingitem)
    {
        return \raptor_mdwsvista\MdwsUtils::getHospitalLocations($this, $startingitem);
    }

    public function getOrderDetails($myIEN)
    {
        return \raptor_mdwsvista\MdwsUtils::getOrderDetails($this, $myIEN);
    }

    public function getRadiologyCancellationReasons()
    {
        return \raptor_mdwsvista\MdwsUtils::getRadiologyCancellationReasons($this);
    }

    public function getVisits()
    {
        return \raptor_mdwsvista\MdwsUtils::getVisits($this);
    }

    public function signNote($newNoteIen, $eSig)
    {
        $userDuz = $this->getDUZ();
        return \raptor_mdwsvista\MdwsUtils::signNote($this, $newNoteIen, $userDuz, $eSig);
    }

    public function validateEsig($eSig)
    {
        return \raptor_mdwsvista\MdwsUtils::validateEsig($this, $eSig);
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        return \raptor_mdwsvista\MdwsUtils::verifyNoteTitleMapping($this, $checkVistaNoteIEN, $checkVistaNoteTitle);
    }

    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ)
    {
        return \raptor_mdwsvista\MdwsUtils::writeRaptorGeneralNote($this, $noteTextArray, $encounterString, $cosignerDUZ);
    }

    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ)
    {
        return \raptor_mdwsvista\MdwsUtils::writeRaptorSafetyChecklist($this, $aChecklistData, $encounterString, $cosignerDUZ);
    }

    public function getImplementationInstance()
    {
        return $this;
    }
}
