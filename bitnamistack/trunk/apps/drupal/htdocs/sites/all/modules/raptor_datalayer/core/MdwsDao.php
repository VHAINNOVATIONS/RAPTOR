<?php namespace raptor;
require_once("config.php");
require_once("IMdwsDao.php");

class MdwsDao implements IMdwsDao {
    private $instanceTimestamp;
    private $authenticationTimestamp;
    private $errorCount;
    private $mdwsClient;
    private $isAuthenticated;
    private $currentSoapClientFunctions;
    private $currentFacade;
    // these need to be cached for re-try purposes
    private $userSiteId;
    private $userAccessCode;
    private $userVerifyCode;
    private $duz;
    private $selectedPatient;

    public function __construct() {
        $this->instanceTimestamp = microtime();
        error_log('Created MdwsDao instance ' . $this->instanceTimestamp);
        $this->errorCount = 0; // initializing for clarity
        $this->initClient();
    }
    
    public function initClient() {
        //we'll use the EmrSvc facade for initialization but this may change when a SOAP call is executed
        $this->mdwsClient = new \SoapClient(EMRSERVICE_URL, array("trace" => 1, "exceptions" => 0));
        $this->currentFacade = EMRSERVICE_URL;
        $this->currentSoapClientFunctions = $this->mdwsClient->__getFunctions();        
    }


    public function disconnect() {
        error_log('Called MdwsDao disconnect!!!!!');
        $this->errorCount = 0;
        $this->isAuthenticated = FALSE;
        try {
            $this->mdwsClient->disconnect();
        }
        catch (\Exception $e) {
            // just swallow - generally don't care if this errored
        }
        
    }

    public function makeQuery($functionToInvoke, $args) {
        if (!$this->isAuthenticated()) {
            drupal_set_message('TIP: <a href="/drupal/user/logout">Logout</a> and <a href="/drupal/user/login">log back in</a></a>');
            throw new \Exception('Not authenticated in MdwsDao instance ' . $this->instanceTimestamp .  '(previous authentication was '.$this->authenticationTimestamp.')' . ": Must authenticate before requesting data>>>" . Context::debugGetCallerInfo(2,10));
        }
        try {
            // use the DAO factory to obtain the correct SOAP client
            // use the previous SOAP request/response headers to set the ASP.NET_SessionID header if the facde has changed
            $wsdlForFunction = MdwsDaoFactory::getFacadeNameByFunction($functionToInvoke);
            if ($wsdlForFunction != $this->currentFacade) {
                $this->currentFacade = $wsdlForFunction;
                $cookie = $this->mdwsClient->_cookies["ASP.NET_SessionId"][0];
                $this->mdwsClient = MdwsDaoFactory::getSoapClientByFunction($functionToInvoke);
                $this->mdwsClient->__setCookie("ASP.NET_SessionId", $cookie);
            }
            
            // functionToInvoke is the name of the SOAP call, args is the list of arguments
            // PHP seems to like this format (using the functionToInvoke string as the SOAP name) just fine!
            $soapResult = $this->mdwsClient->$functionToInvoke($args);
            // TO object is always stored in "soapCallResult". e.g. select result stored in 'selectResult'
            $resultVarName = strval($functionToInvoke)."Result";
            // this block of code before the return $soapResult statement is error checking/auto-re-authentication
            if(isset($soapResult->$resultVarName)) //20140723 JAM why would this ever not be set?? ->  //20140707 FJF prevent missing property error message
            {
                $TOResult = $soapResult->$resultVarName;
                if (isset($TOResult->fault)) {
                    // TODO - haven't tested this auto-reconnect code atl all. need to write tests
                    // we received a fault - might be a session timeout in which case we want to handle gracefully
                    if (strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_1) === FALSE ||
                            strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_2) === FALSE ||
                            strpos($TOResult->fault->message, MDWS_CXN_TIMEOUT_ERROR_MSG_3) === FALSE) {
                        // TODO - determine where the creds will be stored - these vars are undefined
                        $this->initClient();
                        error_log('TODO --- get the credentials now???>>>' . $TOResult->fault->message);
                        $this->connectAndLogin($this->userSiteId, $this->userAccessCode, $this->userVerifyCode);
                        return $this->makeQuery($functionToInvoke, $args);
                    } // TODO - may need to add more else if statements here to catch other recoverable timeout conditions
                    else {
                        throw new \Exception('MdwsDao->makeQuery unhandled exception: '.$TOResult->fault->message);
                    }
                    //return NULL;    //20140707 - JAM: why is this returning null??
                }
            }
            
            // check if call was "select" - cache selected patient ID, if so
            if ($functionToInvoke == 'select') {
                $this->selectedPatient = $soapResult->selectResult->localPid;
            }
            
            return $soapResult;
        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), "connection was forcibly closed")) {
                $this->initClient();
                $this->connectAndLogin($this->userSiteId, $this->userAccessCode, $this->userVerifyCode);
                return $this->makeQuery($functionToInvoke, $args);
            }
            if (strpos($ex->getMessage(), "No VistA listener")) {
                // TODO - move to connect or connectAndLogin code
                // TODO - retry up to MDWS_VISTA_UNAVAILABLE_MAX_ATTEMPTS
            }
            // any other exceptions that may be related to timeout? add here as found
            else {
                throw $ex;
            }
        }
    }

    // TODO - need to test!
    public function makeStatelessQuery($siteCode, $username, $password, $patientId, $functionToInvoke, $args, $multiSiteFlag, $appPwd) {
        $this->connectAndLogin($siteCode, $username, $password);
        if (isset($patientId)) {
            $this->makeQuery("select", array("DFN"=>$patientId));
        }
        if ($multiSiteFlag) {
            $this->makeQuery("setupMultiSiteQuery", array("appPwd"=>$appPwd));
        }
        $result = $this->makeQuery($functionToInvoke, $args);
        $this->disconnect();
        return $result;
    }

    public function connectAndLogin($siteCode, $username, $password) {
        //drupal_set_message('About to login to MDWS as ' . $username);
        try {
            $connectResult = $this->mdwsClient->connect(array("sitelist"=>$siteCode))->connectResult;
            if (isset($connectResult->fault)) {                
                if ($this->errorCount > MDWS_CONNECT_MAX_ATTEMPTS) {
                    throw new \Exception($connectResult->fault->message);
                }
                // erroneous error message - re-try connect for configured # of re-tries
                if (strpos($connectResult->fault->message, "XUS SIGNON SETUP is not registered to the option XUS SIGNON")
                        || strpos($connectResult->fault->message, "XUS INTRO MSG is not registered to the option XUS SIGNON")) {
                    $this->errorCount++;
                    // first sleep for a short configurable time...
                    usleep(MDWS_QUERY_RETRY_WAIT_INTERVAL_MS * 1000);
                    return $this->connectAndLogin($siteCode, $username, $password);
                }
                else {
                    throw new \Exception($connectResult->fault->message);
                }
            }
            
            // successfully connected! now let's login'
            $loginResult = $this->mdwsClient->login(array("username"=>$username, "pwd"=>$password, "context"=>""));
            if(isset($loginResult->loginResult))    //20140707 FJF prevent missing property msg
            {
                $TOResult = $loginResult->loginResult;
                if(isset($TOResult->fault)) {
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
            
            error_log('Authenticated in MdwsDao ' . $this->instanceTimestamp . ' at ' . $this->authenticationTimestamp);
            
            // transparently re-select last selected patient
            if (isset($this->selectedPatient) && $this->selectedPatient != '') {
                $this->makeQuery('select', array('DFN' =>  $this->selectedPatient));
            }
            
            return $loginResult;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function connectRemoteSites($applicationPassword) {
        throw new \Exception("This function has not been implemented");
    }

    public function isAuthenticated() {
        return $this->isAuthenticated;
    }
    
    public function getDUZ() {
        if (!$this->isAuthenticated) {
            throw new \Exception('Not authenticated');
        }
        return $this->duz;
    }
}
