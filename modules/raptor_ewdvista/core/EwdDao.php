<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor_ewdvista;

require_once 'IEwdDao.php';
require_once 'WebServices.php';

/**
 * This is the primary interface implementation to VistA using EWDJS
 *
 * @author Frank Font of SAN Business Consultants
 */
class EwdDao implements \raptor_ewdvista\IEwdDao
{
    private $m_createdtimestamp = NULL;
    private $m_authorization = NULL;
    private $m_init_key = NULL;
    private $m_credentials = NULL;          //Encrypted credentials value
    private $m_oWebServices = NULL;

    private $m_dt           = NULL;
    private $m_userduz      = NULL;         //Keep as NULL until authenticated
    private $m_displayname  = NULL;
    private $m_fullname     = NULL;
    private $m_greeting     = NULL;
    
    private $m_selectedPatient = NULL;      //The currently selected patient
    
    function __construct()
    {

        $this->m_createdtimestamp = microtime();        
        $this->m_oWebServices = new \raptor_ewdvista\WebServices();
        $this->initClient();
        
        error_log("LOOK constructed ".$this);
    }

    public function getIntegrationInfo()
    {
        return "EWD VISTA EHR Integration";
    }

    /**
     * Return the site specific fully qualified URL for the service.
     */
    private function getURL($servicename,$args=NULL)
    {
        //NOTE: assumption that EWDFED_BASE_URL already have slash "/" at the end
        //TODO: at some point refactor to use some sort of combine functionality 
        //      to makesure we are adding slash correctly something like http_build_url
        //      http://php.net/manual/en/function.http-build-url.php#114753
        if($args === NULL)
        {
            return EWDFED_BASE_URL . "$servicename";
        } else {
            $argtext = '';
            foreach($args as $k=>$v)
            {
                if($argtext > '')
                {
                    $argtext .= '&';
                }
                $argtext .= "$k=$v";
            }
            return EWDFED_BASE_URL . "$servicename?{$argtext}";
        }
    }
    
    /**
     * Initialize the DAO client session
     */
    private function initClient()
    {
        try
        {
            error_log('Starting EWD initClient at ' . microtime());
            $servicename = 'initiate';
            $url = $this->getURL($servicename);
            $json_string = $this->m_oWebServices->callAPI($servicename, $url);
            $json_array = json_decode($json_string, TRUE);
            $this->m_authorization = trim($json_array["Authorization"]);
            $this->m_init_key = trim($json_array["key"]);
            if($this->m_authorization == '')
            {
                throw new \Exception("Missing authorization value in result! URL: $url");
            }
            if($this->m_init_key == '')
            {
                throw new \Exception("Missing init key value in result! URL: $url");
            }
            error_log('EWD initClient is DONE at ' . microtime());
        } catch (\Exception $ex) {
            throw new \Exception("Trouble in initClient because ".$ex,99876,$ex);
        }
    }

    /**
     * Return TRUE if already authenticated
     */
    public function isAuthenticated() 
    {
        return ($this->m_userduz != NULL);
    }

    /**
     * Disconnect this DAO from a session
     */
    public function disconnect() 
    {
        $this->m_userduz = NULL;
        $this->m_authorization = NULL;
        $this->m_init_key = NULL;
        $this->m_credentials = NULL;
        $this->m_dt           = NULL;
        $this->m_displayname  = NULL;
        $this->m_fullname     = NULL;
        $this->m_greeting     = NULL;
        $this->m_selectedPatient = NULL;
    }

    /**
     * Attempt to login and mark the user authenticated
     */
    public function connectAndLogin($siteCode, $username, $password) 
    {
        error_log('Starting EWD connectAndLogin at ' . microtime());
        $errorMessage = "";
        try
        {
            //Have we already initialized the client?
            if($this->m_authorization == NULL)
            {
                //Initialize it now
                error_log("Calling init from connectAndLogin for $this");
                $this->initClient();
            }
            if($this->m_init_key == NULL)
            {
                throw new \Exception("No initialization key has been set!");
            }
            module_load_include('php', 'raptor_ewdvista', 'core/Encryption');
            $encryption = new \raptor_ewdvista\Encryption();
            $keytext = $this->m_init_key;
            $this->m_credentials = $encryption->getEncryptedCredentials($keytext, $username, $password);

            $method = 'login';
            //http://localhost:8081/RaptorEwdVista/raptor/login?credentials=
            $url = $this->getURL($method) . "?credentials=" . $this->m_credentials;
            $header["Authorization"]=$this->m_authorization;
            $json_string = $this->m_oWebServices->callAPI("GET", $url, FALSE, $header);            
            $json_array = json_decode($json_string, TRUE);
            
            if (array_key_exists("DUZ", $json_array))
            {
                $this->m_dt           = trim($json_array["DT"]);
                $this->m_userduz      = trim($json_array["DUZ"]);
                $this->m_displayname  = trim($json_array["displayName"]);
                $this->m_fullname     = trim($json_array["username"]);
                $this->m_greeting     = trim($json_array["greeting"]);
            }
            else {
                $errorMessage = "Unable to LOGIN " . print_r($json_array, TRUE);
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $ex) {
            $this->disconnect();
            throw new \Exception("Trouble in connectAndLogin as cred={$this->m_credentials} because ".$ex,99876,$ex);
        }
    }

    private function getServiceRelatedData($serviceName)
    {
        error_log("Starting EWD $serviceName at " . microtime());
        $errorMessage = "";
        try
        {
            $url = $this->getURL($serviceName);
            $header["Authorization"]=$this->m_authorization;
            
            $json_string = $this->m_oWebServices->callAPI("GET", $url, FALSE, $header);            
            $php_array = json_decode($json_string, TRUE);
            
            error_log("Finish EWD $serviceName at " . microtime());
            return $php_array;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble with $serviceName  because ".$ex,99876,$ex);;
        }
    }
    
    /*
    http://stackoverflow.com/questions/190421/caller-function-in-php-5
    */
    private function getCallingFunctionName($completeTrace=FALSE)
    {
        $trace=debug_backtrace();
        $functionName = "";
        if($completeTrace)
        {
            $str = '';
            foreach($trace as $caller)
            {
                //get the name, and we really interested in the last name in the wholepath 
                $functionName = "".$caller['function'];
                //get log information    
                $str .= " -- Called by {$caller['function']}";
                if (isset($caller['class']))
                    $str .= " From Class {$caller['class']}";
            }
        }
        else
        {
            $caller=$trace[2];
            $functionName = "".$caller['function'];
            $str = "Called by {$caller['function']}";
            if (isset($caller['class']))
                $str .= " From Class {$caller['class']}";
        }
        error_log("LOOK getCallingFunctionName: " . $str);
        return $functionName;
    }
    
    public function getWorklistDetailsMap()
    {
        //$serviceName = 'getWorklistDetailsMap';
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }
    
    /**
     * Return array of valuse from the indicated action
     * This is good for developers to check results
     */
    public function getPrivateValue($keynames)
    {
        try
        {
            if(!is_array($keynames))
            {
                $keynames_ar = array($keynames);
            } else {
                $keynames_ar = $keynames;
            }
            $result = array();
            foreach($keynames_ar as $keyname)
            {
                $varname = "m_{$keyname}";
                $result[$keyname] = $this->$varname;
            }
            return $result;
        } catch (\Exception $ex) {
            $msg = "Failed getting keynames because ".$ex;
            throw new \Exception($msg,99876,$ex);
        }
    }
    
    public function __toString()
    {
        $extrainfo = trim("auth={$this->m_authorization} and displayname=$this->m_displayname");
        return trim("EwdDao created {$this->m_createdtimestamp} $extrainfo");
    }

    public function getNotesDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function setPatientID($sPatientID)
    {
        $this->m_selectedPatient = $sPatientID;
    }

    public function getEHRUserID($fail_if_missing = TRUE)
    {
        if($this->m_userduz == NULL && $fail_if_missing)
        {
            throw new \Exception("No user is currently authenticated!");
        }
        return $this->m_userduz;
    }

    public function cancelRadiologyOrder($patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig)
    {
        throw new \Exception("Not implemented $patientid, $orderFileIen, $providerDUZ, $locationthing, $reasonCode, $cancelesig");
    }

    public function createNewRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented $orderChecks, $args");
    }

    public function createUnsignedRadiologyOrder($orderChecks, $args)
    {
        throw new \Exception("Not implemented $orderChecks, $args");
    }

    public function getAllHospitalLocationsMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getAllergiesDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getChemHemLabs()
    {
       $serviceName = $this->getCallingFunctionName();
       return $this->getServiceRelatedData($serviceName);
    }

    public function getDashboardDetailsMap($override_tracking_id = NULL)
    {
        //TODO: we need to implement $override_tracking_id
        error_log('TODO: we need to implement $override_tracking_id');
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getDiagnosticLabsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getEGFRDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getHospitalLocations($startingitem)
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getImagingTypesMap()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getImplementationInstance()
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getMedicationsDetailMap($atriskmeds = NULL)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getOrderDetails($myIEN)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getOrderOverviewMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getOrderableItems($imagingTypeId)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getPathologyReportsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getPatientDashboardMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getPatientIDFromTrackingID($sTrackingID)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getPendingOrdersMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getProblemsListDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getProcedureLabsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getProviders($neworderprovider_name)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getRadiologyCancellationReasons()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getRadiologyOrderChecks($args)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getRadiologyReportsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getRawVitalSignsMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getSurgeryReportsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getUserSecurityKeys()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getVisits()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getVistaAccountKeyProblems()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getVitalsDetailMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getVitalsDetailOnlyLatestMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function getVitalsSummaryMap()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function isProvider()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function signNote($newNoteIen, $eSig)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function userHasKeyOREMAS()
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function validateEsig($eSig)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function writeRaptorGeneralNote($noteTextArray, $encounterString, $cosignerDUZ)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

    public function writeRaptorSafetyChecklist($aChecklistData, $encounterString, $cosignerDUZ)
    {
        $serviceName = $this->getCallingFunctionName();
		return $this->getServiceRelatedData($serviceName);
    }

}
