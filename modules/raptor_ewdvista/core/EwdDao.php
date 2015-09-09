<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, Alex Podlesny, et al
 * EWD Integration and VISTA collaboration: Joel Mewton, Rob Tweed
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */ 

namespace raptor_ewdvista;

require_once 'IEwdDao.php';
require_once 'EwdUtils.php';
require_once 'WebServices.php';
require_once 'WorklistHelper.php';
require_once 'DashboardHelper.php';
require_once 'NotesHelper.php';
require_once 'VitalsHelper.php';
require_once 'MedicationHelper.php';
require_once 'LabsHelper.php';
require_once 'AllergyHelper.php';
require_once 'SurgeryReportHelper.php';
require_once 'ProblemsListHelper.php';
require_once 'PathologyReportHelper.php';
require_once 'RadiologyReportHelper.php';

defined('VERSION_INFO_RAPTOR_EWDDAO')
    or define('VERSION_INFO_RAPTOR_EWDDAO', 'EWD VISTA EHR Integration 20150904.3');

defined('REDAO_CACHE_NM_WORKLIST')
    or define('REDAO_CACHE_NM_WORKLIST', 'getWorklistDetailsMapData');
defined('REDAO_CACHE_NM_SUFFIX_DASHBOARD')
    or define('REDAO_CACHE_NM_SUFFIX_DASHBOARD', '_getDashboardDetailsMapEWD');
defined('REDAO_CACHE_NM_SUFFIX_VITALS')
    or define('REDAO_CACHE_NM_SUFFIX_VITALS', '_getRawVitalSignsMapEWD');

/**
 * This is the primary interface implementation to VistA using EWDJS
 *
 * @author Frank Font of SAN Business Consultants
 */
class EwdDao implements \raptor_ewdvista\IEwdDao
{
    private $m_groupname = 'EwdDaoGroup';
    private $m_createdtimestamp = NULL;
    private $m_oWebServices = NULL;
    private $m_worklistHelper = NULL;
    private $m_dashboardHelper = NULL;
    private $m_info_message = NULL;
    private $m_session_key_prefix = NULL;
    
    public function __construct($session_key_prefix='EWDDAO')
    {
        $this->m_session_key_prefix = $session_key_prefix;
        
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');
        $this->m_createdtimestamp = microtime();        
        $this->m_oWebServices = new \raptor_ewdvista\WebServices();
        $this->m_worklistHelper = new \raptor_ewdvista\WorklistHelper();
        $this->m_dashboardHelper = new \raptor_ewdvista\DashboardHelper();
        $this->initClient();
    }

    public function getIntegrationInfo()
    {
        return VERSION_INFO_RAPTOR_EWDDAO;
    }

    /**
     * Set the instance info message.  
     */
    public function setCustomInfoMessage($msg)
    {
        $this->m_info_message = $msg;
    }
    
    /**
     * Get the instance info message.
     */
    public function getCustomInfoMessage()
    {
        return $this->m_info_message;
    }
    
    /**
     * We can only pre-cache order data if the DAO implementation is not statefully
     * remembering the last selected order as the current order.
     * 
     * Returns TRUE if critical functions support tracking ID override for precache purposes.
     */
    public function getSupportsPreCacheOrderData()
    {
        return TRUE;    //We have implemented an override for the tracking ID
    }
    
    /**
     * We can only pre-cache patient data if the DAO implementation is not statefully
     * remembering the last selected order as the current order.
     * 
     * Returns TRUE if critical functions support patientId override for precache purposes.
     */
    public function getSupportsPreCachePatientData()
    {
        return TRUE;    //We are implementing an override for the patientId
    }
    
    private function endsWith($string, $test) 
    {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) 
        {
            return FALSE;
        }
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }
    
    /**
     * Return the site specific fully qualified URL for the service.
     */
    private function getURL($servicename,$args=NULL)
    {
        $base_ewdfed_url = trim(EWDFED_BASE_URL);
        if(!$this->endsWith($base_ewdfed_url,'/'))
        {
           error_log("TUNING TIP: Add missing '/' at the end of the EWDFED_BASE_URL declaration (Currently declared as '$base_ewdfed_url')");
           $base_ewdfed_url .= '/';
        }
        if($args === NULL)
        {
            return $base_ewdfed_url . "$servicename";
        } else {
            $argtext = '';
            foreach($args as $k=>$v)
            {
                if($argtext > '')
                {
                    $argtext .= '&';
                }
                $encoded = urlencode($v);
                $argtext .= "$k=$encoded";
            }
            return $base_ewdfed_url . "$servicename?{$argtext}";
        }
    }
    
    /**
     * Initialize the DAO client session
     */
    private function initClient()
    {
        try
        {
            error_log('Starting EWD initClient at ' . microtime(TRUE));
            $this->disconnect();    //Clear all session variables
            $servicename = 'initiate';
            $url = $this->getURL($servicename);
            //$json_string = $this->m_oWebServices->callAPI($servicename, $url);
            $json_string = $this->m_oWebServices->callAPI('GET', $url);
            $json_array = json_decode($json_string, TRUE);
            $this->setSessionVariable('authorization',trim($json_array["Authorization"]));
            $this->setSessionVariable('init_key',trim($json_array["key"]));
            $authorization = $this->getSessionVariable('authorization');
            if($authorization == '')
            {
                throw new \Exception("Missing authorization value in result! [URL: $url]"
                        . "\n >>> array result=".print_r($json_array,TRUE) 
                        . "\n >>> raw JSON=".print_r($json_string,TRUE)
                        . "\n >>> urlencoded JSON=".  urlencode($json_string)
                        . "\n");    //So that the rest of the exception is not blanded into this line!
            }
            $init_key = $this->getSessionVariable('init_key');
            if($init_key == '')
            {
                throw new \Exception("Missing init key value in result! [URL: $url]"
                        . "\n >>> array result=".print_r($json_array,TRUE) 
                        . "\n >>> raw JSON=".print_r($json_string,TRUE)
                        . "\n >>> urlencoded JSON=".  urlencode($json_string)
                        . "\n");    //So that the rest of the exception is not blanded into this line!
            }
            error_log('EWD initClient is DONE at ' . microtime(TRUE));
        } catch (\Exception $ex) {
            throw new \Exception('Trouble in initClient because ' . $ex , 99876 , $ex);
        }
    }

    /**
     * Return TRUE if already authenticated
     */
    public function isAuthenticated() 
    {
        $userduz = $this->getSessionVariable('userduz');
        return ($userduz != NULL);
    }

    private function setSessionVariable($name,$value)
    {
        $fullname = "{$this->m_session_key_prefix}_$name";
        $_SESSION[$fullname] = $value;
    }

    private function getSessionVariable($name)
    {
        $fullname = "{$this->m_session_key_prefix}_$name";
        if(isset($_SESSION[$fullname]) 
                && $_SESSION[$fullname] > '')
        {
            return $_SESSION[$fullname];
        }
        return NULL;
    }
    
    /**
     * Disconnect this DAO from a session
     */
    public function disconnect() 
    {
        $this->setSessionVariable('userduz',NULL);
        $this->setSessionVariable('authorization',NULL);
        $this->setSessionVariable('init_key', NULL);
        $this->setSessionVariable('credentials', NULL);
        $this->setSessionVariable('dt', NULL);
        $this->setSessionVariable('displayname', NULL);
        $this->setSessionVariable('fullname', NULL);
        $this->setSessionVariable('greeting', NULL);
        $this->setSessionVariable('securitykeys', NULL);
        $this->setPatientID(NULL);
    }

    /**
     * Attempt to login and mark the user authenticated
     */
    public function connectAndLogin($siteCode, $username, $password) 
    {
        try
        {
            error_log('Starting EWD connectAndLogin at ' . microtime());
            $errorMessage = "";
            
            //Are we already logged in?
            if($this->isAuthenticated())
            {
                //Log out before we try again!
                $this->disconnect();
            }
            
            //Have we already initialized the client?
            $authorization = $this->getSessionVariable('authorization');
            if($authorization == NULL)
            {
                //Initialize it now
                error_log("Calling init from connectAndLogin for $this");
                $this->initClient();
                $authorization = $this->getSessionVariable('authorization');
            }
            $init_key = $this->getSessionVariable('init_key');
            if($init_key == NULL)
            {
                throw new \Exception("No initialization key has been set!");
            }
            module_load_include('php', 'raptor_ewdvista', 'core/Encryption');
            $encryption = new \raptor_ewdvista\Encryption();
            $credentials = $encryption->getEncryptedCredentials($init_key, $username, $password);
            $this->setSessionVariable('credentials', $credentials);

            $method = 'login';
            //http://localhost:8081/RaptorEwdVista/raptor/login?credentials=
            $url = $this->getURL($method) . "?credentials=" . $credentials;
            $header["Authorization"]=$authorization;
            
            //error_log("LOOK header>>>".print_r($header,TRUE));
            //error_log("LOOK url>>>".print_r($url,TRUE));
            
            $json_string = $this->m_oWebServices->callAPI('GET', $url, FALSE, $header);            
            $json_array = json_decode($json_string, TRUE);
            
            if (array_key_exists("DUZ", $json_array))
            {
                $userduz = trim($json_array['DUZ']);
                $this->setSessionVariable('dt',trim($json_array['DT']));
                $this->setSessionVariable('userduz',$userduz);
                $this->setSessionVariable('displayname',trim($json_array['displayName']));
                $this->setSessionVariable('fullname',trim($json_array['username']));
                $this->setSessionVariable('greeting',trim($json_array['greeting']));
                $securitykeys = $this->getSecurityKeysForUser($userduz);
                $this->setSessionVariable('securitykeys',$securitykeys);
            }
            else {
                $errorMessage = "Unable to LOGIN because missing DUZ in " . print_r($json_array, TRUE);
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $ex) {
            $thecreds = $this->getSessionVariable('credentials');
            $this->disconnect();
            throw new \Exception("Trouble in connectAndLogin at $siteCode as $username with cred={$thecreds} because ".$ex,99876,$ex);
        }
    }

    /**
     * Return the raw result from the restful service.
     */
    private function getServiceRelatedData($serviceName,$args=NULL)
    {
        try
        {
            //error_log("Starting EWD $serviceName at " . microtime(TRUE));
            $url = $this->getURL($serviceName,$args);
            $authorization = $this->getSessionVariable('authorization');
            if($authorization == NULL)
            {
                throw new \Exception("Missing the authorization string in call to $serviceName");
            }
            $header["Authorization"]=$authorization;
            
            $json_string = $this->m_oWebServices->callAPI('GET', $url, FALSE, $header);            
        error_log("LOOK JSON DATA for GET@URL=$url has result = " . print_r($json_string, TRUE));
            $php_array = json_decode($json_string, TRUE);
            
            //error_log("Finish EWD $serviceName at " . microtime(TRUE));
            return $php_array;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble with $serviceName($args) because $ex", 99876, $ex);;
        }
    }
    
    /**
    * http://stackoverflow.com/questions/190421/caller-function-in-php-5
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
                {
                    $str .= " From Class {$caller['class']}";
                }
            }
        }
        else
        {
            //$caller=$trace[2];  20150812 Not safe to hardcode key as 2; does not always work!
            $breakatnext = FALSE;
            foreach($trace as $key=>$caller)
            {
                $functionName = "".$caller['function'];
                if($breakatnext)
                {
                    break;
                } else
                if($functionName == 'getCallingFunctionName')
                {
                    $breakatnext = TRUE;
                }
            }
            if(!$breakatnext)
            {
                throw new \Exception("Failed to find the calling function name in ".print_r($trace,TRUE));
            }
            $functionName = "".$caller['function'];
            $str = "Called by {$functionName}";
            if (isset($caller['class']))
            {
                $str .= " From Class {$caller['class']}";
            }
        }
        //error_log("LOOK getCallingFunctionName: " . $str);
        return $functionName;
    }
    
    /**
     * Returns array of arrays the way RAPTOR expects it.
     */
    public function getWorklistDetailsMap($max_rows_one_call = 1500, $start_with_IEN=NULL)
    {
        try
        {
            $args = array();
            $serviceName = $this->getCallingFunctionName();
            if($start_with_IEN == NULL)
            {
                $start_from_IEN = '';
            } else {
                if(!is_numeric($start_with_IEN))
                {
                    throw new \Exception("The starting IEN declaration must be numeric but instead we got ".print_r($start_with_IEN,TRUE));
                }
                $start_from_IEN = intval($start_with_IEN) + 1; //So we really start there
            }
            $maxpages=1;
            $pages=0;
            $matching_offset=NULL;
            $getmorepages = TRUE;
            $show_rows = array();
            $pending_orders_map = array();
            $args['max'] = $max_rows_one_call;
            $row_bundles = array();
            while($getmorepages)
            {
                $pages++;
                $args['from'] = $start_from_IEN;    //VistA starts from this value -1!!!!!
                $rawdatarows = $this->getServiceRelatedData($serviceName, $args);
//error_log("LOOK raw data rows for worklist>>>>".print_r($rawdatarows, TRUE));            
                $bundle = $this->m_worklistHelper->getFormatWorklistRows($rawdatarows);
                $formated_datarows = $bundle['all_rows'];
                $rowcount = count($formated_datarows);
                if($rowcount == 0 || !isset($bundle['last_ien']))
                {
                    $getmorepages = FALSE;    
                } else {
                    $getmorepages = ($pages <= $maxpages);    
                }
                $start_from_IEN = $bundle['last_ien'];
                if($bundle['matching_offset'] != NULL)
                {
                    $matching_offset = count($show_rows) + $bundle['matching_offset'];
                }
                $pending_orders_map = array_merge($pending_orders_map, $bundle['pending_orders_map']);
                $row_bundles[] = $formated_datarows;
//error_log("LOOK at page $pages getting more pages? ($getmorepages) >>>".print_r($row_bundles,TRUE));
            }
            $show_rows = $row_bundles[0];   //TODO FIX ARRAY MERGE!!!!! array_merge($row_bundles);
            $aResult = array('Pages'=>$pages
                            ,'Page'=>1
                            ,'RowsPerPage'=>$max_rows_one_call
                            ,'DataRows'=>$show_rows
                            ,'matching_offset' => $matching_offset
                            ,'pending_orders_map' => $pending_orders_map
                );
error_log("LOOK worklist maxrows=$max_rows_one_call result>>>".print_r($aResult,TRUE));
            return $aResult;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return array of valuse from the indicated action
     * This is good for developers to check results
     */
    function getPrivateValue($keynames)
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
        try 
        {
            $infomsg = $this->getCustomInfoMessage();
            if($infomsg > '')
            {
                $infomsg_txt = "\n\tCustom info message=$infomsg";
            } else {
                $infomsg_txt = '';
            }
            $spid = $this->getSelectedPatientID();
            $is_authenticated = $this->isAuthenticated() ? 'YES' : 'NO';
            $displayname = $this->getSessionVariable('displayname');
            return 'EwdDao instance created at ' . $this->m_createdtimestamp
                    . ' isAuthenticated=[' . $is_authenticated . ']'
                    . ' selectedPatient=[' . $spid . ']'
                    . ' displayname=[' . $displayname . ']'
                    . $infomsg_txt;
        } catch (\Exception $ex) {
            return 'Cannot get toString of EwdDao because ' . $ex;
        }
    }

    public function getNotesDetailMap()
    {
        try
        {
            $myhelper = new \raptor_ewdvista\NotesHelper();
            $serviceName = $this->getCallingFunctionName();
            $pid = $this->getSelectedPatientID();
            if($pid == '')
            {
                throw new \Exception('Cannot get notes detail without a patient ID!');
            }

            //Get the notes data from EWD services
            $args = array();
            $args['patientId'] = $pid;
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $notesdetail = $myhelper->getFormattedNotes($rawresult);
            return $notesdetail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function setPatientID($sPatientID)
    {
        try
        {
            $this->setSessionVariable('selectedPatient',$sPatientID);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getEHRUserID($fail_if_missing = TRUE)
    {
        try
        {
            $userduz = $this->getSessionVariable('userduz');
            if($userduz == NULL && $fail_if_missing)
            {
                throw new \Exception('No user is currently authenticated!');
            }
            return $userduz;
        } catch (\Exception $ex) {
            throw $ex;
        }
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

    /**
     * TODO: Confirm with Joel and Rob that intermittent 'target' param issue is resolved.
     *       See email threads from 8/14 on the topic.
     */
    public function getAllHospitalLocationsMap()
    {
        try
        {
            $serviceName = 'getHospitalLocationsMap';   //Only gets 44 at a time
            $callservice = TRUE;
            $callcount=0;
            $maxcalls = 50;
            $prevend = ' ';
            $formatted = array();
            while($callservice)
            {
                $callcount++;
                $args = array();
                $args['target'] = $prevend;   //Start at the start
                $rawresult = $this->getServiceRelatedData($serviceName, $args);
                if(!isset($rawresult['value']))
                {
                    error_log("WARNING callcount=$callcount QUIT $serviceName ITERATIONS because NON-ARRAY RESULT prev=[$prevend] last=[$lastitem]"); 
                    $callservice = FALSE;
                } else {
                    $rawdatarows = $rawresult['value'];
                    $lastrawitem = end($rawdatarows);
                    $last_ar = explode('^',$lastrawitem);
                    $lastitem = $last_ar[1];
                    $moreformatted = array();
                    foreach($rawdatarows as $key=>$onerow)
                    {
                        $one_ar = explode('^',$onerow);
                        $newkey = $one_ar[0];
                        $moreformatted[$newkey] = $one_ar[1];
                    }
                    if(is_array($rawdatarows) && count($rawdatarows) > 0 && strcasecmp($prevend, $lastitem) < 0)
                    {
                        $prevend = $lastitem;
                        $callservice = TRUE;
                    } else {
                        $callservice = FALSE;
                    }
                    $formatted = $formatted + $moreformatted;
                }
                if($callcount >= $maxcalls)
                {
                    error_log("WARNING: TOO MANY ITERATIONS(hit $callcount with item $lastitem and max is $maxcalls) in getAllHospitalLocationsMap");
                    $formatted['GETMORE'] = "TOO MANY LOCATIONS";
                    $callservice = FALSE;
                }
            }
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getAllergiesDetailMap($override_patientId = NULL)
    {
        try
        {
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            if($pid == '')
            {
                throw new \Exception('Cannot get allergy detail without a patient ID!');
            }
            $myhelper = new \raptor_ewdvista\AllergyHelper();
            $serviceName = $this->getCallingFunctionName();

            //Get the medication data from EWD services
            $args = array();
            $args['patientId'] = $pid;
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
//error_log("LOOK getAllergiesDetailMap args>>>".print_r($args,TRUE));            
//error_log("LOOK getAllergiesDetailMap raw result>>>".print_r($rawresult,TRUE));            
            $formatted_detail = $myhelper->getFormattedAllergyDetail($rawresult);
//error_log("LOOK getAllergiesDetailMap formatted result>>>".print_r($formatted_detail,TRUE));            
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getChemHemLabs($override_patientId = NULL)
    {
        try
        {
            $oContext = \raptor\Context::getInstance();
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            $myhelper = new \raptor_ewdvista\LabsHelper($oContext, $pid);
            $serviceName = $this->getCallingFunctionName();
            if($pid == '')
            {
                throw new \Exception('Cannot get chem labs detail without a patient ID!');
            }
            $args = array();
            $args['patientId'] = $pid;
            $args['fromDate'] = EwdUtils::getVistaDate(-1 * DEFAULT_GET_LABS_DAYS);
            $args['toDate'] = EwdUtils::getVistaDate(0);
            
            //$rawresult = $this->getServiceRelatedData($serviceName, $args);
//error_log("LOOK getChemHemLabs args>>>" . print_r($args,TRUE));        
            $rawresult_ar = $this->getServiceRelatedData($serviceName, $args);;
//error_log("LOOK getChemHemLabs raw result>>>" . print_r($rawresult_ar,TRUE));        
            $formatted_detail = $myhelper->getFormattedChemHemLabsDetail($rawresult_ar);
//error_log("LOOK getChemHemLabs formatted result>>>" . print_r($formatted_detail,TRUE));        
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getEGFRDetailMap($override_patientId = NULL)
    {
error_log("LOOK starting getEGFRDetailMap($override_patientId)");        
        try
        {
            $oContext = \raptor\Context::getInstance();
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            $myhelper = new \raptor_ewdvista\LabsHelper($oContext, $pid);
            $alldata = $myhelper->getLabsDetailData($pid);
            $clean_result = $alldata[1];
error_log("LOOK done getEGFRDetailMap($pid)>>>".print_r($clean_result,TRUE));        
            return $clean_result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getDiagnosticLabsDetailMap($override_patientId = NULL)
    {
        return array();
        
        try
        {
            $oContext = \raptor\Context::getInstance();
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            $myhelper = new \raptor_ewdvista\LabsHelper($oContext, $pid);
            $alldata = $myhelper->getLabsDetailData($pid);
            $clean_result = $alldata[0];
error_log("LOOK result from getDiagnosticLabsDetailMap>>>" . print_r($clean_result,TRUE));
            return $clean_result;
        } catch (\Exception $ex) {
            throw $ex;
        }
        /*
         * [10-Aug-2015 14:59:47 America/New_York] LOOK data format returned for 'getDiagnosticLabsDetail' is >>>Array
(
    [0] => Array
        (
            [DiagDate] => 03/16/2010 10:23 am
            [Creatinine] => 1.3 mg/dL
            [eGFR] => 56  mL/min/1.73 m^2
            [eGFR_Health] => warn
            [Ref] => (eGFR calculated) .9 - 1.4
        )

    [1] => Array
        (
            [DiagDate] => 03/16/2010 10:21 am
            [Creatinine] => 1.1 mg/dL
            [eGFR] => 68  mL/min/1.73 m^2
            [eGFR_Health] => good
            [Ref] => (eGFR calculated) .9 - 1.4
        )

    [2] => Array
        (
            [DiagDate] => 03/16/2010 10:20 am
            [Creatinine] => 1.3 mg/dL
            [eGFR] => 56  mL/min/1.73 m^2
            [eGFR_Health] => warn
            [Ref] => (eGFR calculated) .9 - 1.4
        )

    [3] => Array
        (
            [DiagDate] => 03/16/2010 10:18 am
            [Creatinine] => <span class='medical-value-danger'>!! 1.5 mg/dL !!</span>
            [eGFR] => 48  mL/min/1.73 m^2
            [eGFR_Health] => warn
            [Ref] => (eGFR calculated) .9 - 1.4
        )

    [4] => Array
        (
            [DiagDate] => 03/16/2010 10:17 am
            [Creatinine] => 1.2 mg/dL
            [eGFR] => 62  mL/min/1.73 m^2
            [eGFR_Health] => good
            [Ref] => (eGFR calculated) .9 - 1.4
        )

)

         */
    }

    public function getProcedureLabsDetailMap($override_patientId = NULL)
    {
        try
        {
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            //TODO!!!!! Do NOT call EWD for this one
            return array();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * If override_tracking_id is provided, then return dashboard for that order
     * instead of the currently selected order.
     */
    public function getDashboardDetailsMap($override_tracking_id = NULL)
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $oContext = \raptor\Context::getInstance();
            if ($override_tracking_id == NULL)
            {
                $tid = $oContext->getSelectedTrackingID();
            } else {
                $tid = trim($override_tracking_id);
            }
            if($tid == '')
            {
                throw new \Exception('Cannot get dashboard without a tracking ID!');
            }

            if ($oContext != NULL)
            {
                //Utilize the cache.
                $sThisResultName = "{$tid}" . REDAO_CACHE_NM_SUFFIX_DASHBOARD;
                $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
                if($oRuntimeResultFlexCacheHandler != NULL)
                {
                    $aCachedResult = $oRuntimeResultFlexCacheHandler->checkCache($sThisResultName);
                    if($aCachedResult !== NULL)
                    {
                        //Found it in the cache!
    //error_log("LOOK final bundle getDashboardDetailsMap PULLED FROM CACHE >>> ".print_r($aCachedResult, TRUE));  
                        return $aCachedResult;
                    }
                }
            } else {
                $oRuntimeResultFlexCacheHandler = NULL;
            }

            //Get the dashboard data from EWD services
            $namedparts = $this->getTrackingIDNamedParts($tid);
            $order_IEN = $namedparts['ien'];
            $onerow = NULL; //We MUST declare it here, else not set after the try block
            $therow = array();
            try
            {
                $onerow = $this->getWorklistDetailsMap(1,$order_IEN);
                if(!is_array($onerow) || !isset($onerow['DataRows']))
                {
                    throw new \Exception("Failed to get worklist row for $order_IEN >>>" . print_r($onerow,TRUE));
                }
            } catch (\Exception $ex) {
                throw new \Exception("Failed to get worklist row for $order_IEN because $ex",99876,$ex);
            }
            $datarows = $onerow['DataRows'];
            if(count($datarows) < 1)    //Do NOT check for exactly 1 because result returns ONE extra row sometimes! (Thats okay)
            {
                $rownum = 0;
                $errmsg = "Expected 1 data row for $order_IEN (got ".count($datarows).")";
                foreach($datarows as $onedatarow)
                {
                    $rownum++;
                    $errmsg .= "\n\tData Row #$rownum) ".print_r($onedatarow,TRUE);
                }
                throw new \Exception($errmsg);
            }
            foreach($datarows as $key=>$therow)
            {
                break;  //Only want to get the first row.
            }
            $args = array();
            $args['ien'] = $order_IEN;
            $result = $this->getServiceRelatedData($serviceName, $args);
            if(!is_array($result['radiologyOrder']))
            {
                throw new \Exception("Did not find array of radiologyOrder in ".print_r($result,TRUE));
            }
            if(!is_array($result['order']))
            {
                throw new \Exception("Did not find array of order in ".print_r($result,TRUE));
            }
            $radiologyOrder = $result['radiologyOrder'];
            $orderFileRec = $result['order'];
            $pid = $therow[\raptor\WorklistColumnMap::WLIDX_PATIENTID];
            $oPatientData = $this->getPatientMap($pid);
            if($oPatientData == NULL)
            {
                $msg = 'Did not get patient data of pid='.$pid
                        .' for trackingID=['.$tid.']';
                error_log($msg.">>>instance details=".print_r($this, TRUE));
                throw new \Exception($msg);
            }
            $dashboard = $this->m_dashboardHelper->getFormatted($tid, $pid, $radiologyOrder, $orderFileRec, $therow, $oPatientData);

            //Put it into the cache if we one
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                try 
                {
    //error_log("LOOK getDashboardDetailsMap WENT INTO CACHE dashboard=".print_r($dashboard,TRUE));        
                    $oRuntimeResultFlexCacheHandler->addToCache($sThisResultName, $dashboard, CACHE_AGE_LABS);
                } catch (\Exception $ex) {
                    error_log("Failed to cache $sThisResultName result because " . $ex->getMessage());
                }
            }
            return $dashboard;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * A tracking ID can be an IEN or an SITE-IEN so
     * use this function instead of coding everywhere.
     */
    private function getTrackingIDNamedParts($tid)
    {
        $namedparts = array();
        $parts = explode('-',trim($tid));
        if(count($parts) == 1)
        {
            $namedparts['site'] = NULL; //Not specified in tid
            $namedparts['ien'] = trim($tid);
        } else {
            $namedparts['site'] = trim($parts[0]);
            $namedparts['ien'] = trim($parts[1]);
        }
        return $namedparts;
    }

    public function getEncounterStringFromVisit($vistitTo)
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getHospitalLocationsMap($startingitem)
    {
        $serviceName = $this->getCallingFunctionName();
        return $this->getServiceRelatedData($serviceName);
    }

    public function getImagingTypesMap()
    {
        //Returns results like this...
        //$result['37'] = 'ANGIO/NEURO/INTERVENTIONAL';
        //$result['5'] = 'MRI';
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $rawresult = $this->getServiceRelatedData($serviceName);
            $rawdata = $rawresult['value'];
            $formatted = array();
            foreach($rawdata as $key=>$onerow)
            {
                $one_ar = explode('^',$onerow);
                $newkey = $one_ar[3];
                $formatted[$newkey] = $one_ar[1];
            }
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getMedicationsDetailMap($atriskmeds = NULL)
    {
        try
        {
            $myhelper = new \raptor_ewdvista\MedicationHelper();
            $serviceName = $this->getCallingFunctionName();
            $pid = $this->getSelectedPatientID();
            if($pid == '')
            {
                throw new \Exception('Cannot get medication detail without a patient ID!');
            }

            //Get the medication data from EWD services
            $args = array();
            $args['patientId'] = $pid;
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $formatted_detail = $myhelper->getFormattedMedicationsDetail($rawresult, $atriskmeds);
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getOrderOverviewMap()
    {
        /*
         * [10-Aug-2015 14:59:47 America/New_York] LOOK data format returned for 'getOrderOverview' is >>>Array
(
    [RqstBy] => ZZLABTECH,FORTYEIGHT
    [PCP] => Unknown
    [AtP] => Unknown
    [RqstStdy] => CT ABDOMEN W/O CONT
    [RsnStdy] => TEST
)

         */
        $serviceName = $this->getCallingFunctionName();
	return $this->getServiceRelatedData($serviceName);
    }

    public function getOrderableItems($imagingTypeId)
    {
        try
        {
            $args = array();
            $args['dialogId'] = $imagingTypeId;
            $serviceName = $this->getCallingFunctionName();
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $value_ar = $rawresult['value'];
            $formatted_detail = array();
            foreach($value_ar as $onerawrow)
            {
                $parts = explode('^',$onerawrow);
                if(count($parts)>1)
                {
                    $id = $parts[0];
                    $name = $parts[1];
                    if(count($parts)>3)
                    {
                        $requiresApproval = $parts[3];
                    } else {
                        $requiresApproval = '';
                    }
                    $formatted_detail[$id] = array('name'=>$name, 'requiresApproval'=>$requiresApproval);
                }
            }
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getPathologyReportsDetailMap($override_patientId = NULL)
    {
        try
        {
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            if($pid == '')
            {
                throw new \Exception('Cannot get chem labs detail without a patient ID!');
            }
            $myhelper = new \raptor_ewdvista\PathologyReportHelper();
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $pid;
            $args['fromDate'] = EwdUtils::getVistaDate(-1 * DEFAULT_GET_LABS_DAYS);
            $args['toDate'] = EwdUtils::getVistaDate(0);
            $args['nRpts'] = 1000;
            $rawresult_ar = $this->getServiceRelatedData($serviceName, $args);
            $formatted_detail = $myhelper->getFormattedPathologyReportHelperDetail($rawresult_ar);
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getPatientIDFromTrackingID($sTrackingID)
    {
        $serviceName = $this->getCallingFunctionName();
error_log("Look about to call service $serviceName($sTrackingID) ...");        
        
        $tid = trim($sTrackingID);
        if($tid == '')
        {
            throw new \Exception("Cannot get patient ID without a tracking ID!");
        }
        $namedparts = $this->getTrackingIDNamedParts($tid);
        $args['ien'] = $namedparts['ien'];
        $result = $this->getServiceRelatedData($serviceName, $args);
error_log("LOOK EWD DAO $serviceName($sTrackingID) result = ".print_r($result,TRUE));
        if(!isset($result['result']))
        {
            throw new \Exception("Missing patient ID result from tracking ID value $sTrackingID: ".print_r($result,TRUE));
        }
        $patientID = $result['result'];
        return $patientID;
    }

    public function getPendingOrdersMap()
    {
        /*
         * [10-Aug-2015 14:59:48 America/New_York] LOOK data format returned for 'getPendingOrdersMap' is >>>Array
(
    [2005] => Array
        (
            [0] => 2005
            [1] => CT
            [2] => CT ABDOMEN W/O CONT
        )

    [2006] => Array
        (
            [0] => 2006
            [1] => CT
            [2] => CT ABDOMEN W/O CONT
        )

    [2009] => Array
        (
            [0] => 2009
            [1] => CT
            [2] => CT ABDOMEN W/O CONT
        )

)

         */
        $serviceName = $this->getCallingFunctionName();
	return $this->getServiceRelatedData($serviceName);
    }

    public function getProblemsListDetailMap()
    {
        try
        {
            $myhelper = new \raptor_ewdvista\ProblemsListHelper();
            $serviceName = $this->getCallingFunctionName();
            $pid = $this->getSelectedPatientID();
            if($pid == '')
            {
                throw new \Exception('Cannot get problems detail without a patient ID!');
            }

            //Get the medication data from EWD services
            $args = array();
            $args['patientId'] = $pid;
            $args['type'] = 'A';    //Only return the active ones
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $formatted_detail = $myhelper->getFormattedProblemsDetail($rawresult);
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProviders($neworderprovider_name)
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $args['target'] = $neworderprovider_name;
            $raw_result = $this->getServiceRelatedData($serviceName, $args);
            if(!$raw_result['value'])
            {
                error_log("Missing the expected value key in this>>>>" . print_r($raw_result,TRUE));
                throw new \Exception('Missing the expected value key!');
            }
            $values = $raw_result['value'];
            $formatted_ar = array();
            foreach($values as $oneprovider_raw)
            {
                $parts = explode('^', $oneprovider_raw);
                if(count($parts) > 1)
                {
                    $key = $parts[0];
                    $formatted_ar[$key] = $parts[1];
                }
            }
            return $formatted_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getRadiologyCancellationReasons()
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $raw_result = $this->getServiceRelatedData($serviceName);
            if(!is_array($raw_result) || !isset($raw_result['value']))
            {
                error_log("Failed to get cancellation reasons in correct format; got this>>>>" . print_r($raw_result,TRUE));
                throw new \Exception("Did NOT get cancellation reasons in expected format!");
            }
            $value_ar = $raw_result['value'];
            $formatted = array();
            foreach($value_ar as $rawrow)
            {
                $parts = explode('^',$rawrow);
                if(count($parts) > 1)
                {
                    $rawkey = $parts[0];
                    if($rawkey[0] != 'i')
                    {
                        throw new \Exception("Expected i prefix on raw key but instead got row like this '$rawrow'");
                    }
                    $key = substr($rawkey,1);   //Skip the i prefix
                    $formatted[$key] = $parts[1];
                }
            }
            return $formatted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getRadiologyOrderChecks($args)
    {
        $serviceName = $this->getCallingFunctionName();
	return $this->getServiceRelatedData($serviceName);
    }

    public function getRadiologyOrderDialog($imagingTypeId, $patientId)
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $patientId;
            $args['dialogId'] = $imagingTypeId;
            $rawresult_ar = $this->getServiceRelatedData($serviceName, $args);
            error_log("LOOK getRadiologyOrderDialog raw >>>>".print_r($rawresult_ar, TRUE)); 

            $raw_commonProcedures = $rawresult_ar['commonProcedures'];
            $clean_commonProcedures = array();
            foreach($raw_commonProcedures as $onerawset)
            {
                $id = $onerawset['id'];
                $name = $onerawset['name'];
                $clean_commonProcedures[$id] = $name;
            }
            $rawresult_ar['commonProcedures'] = $clean_commonProcedures;
            
            $raw_contractOptions = $rawresult_ar['contractOptions'];
            $clean_contractOptions = array();
            foreach($raw_contractOptions as $onerawset)
            {
                $key = $onerawset['key'];
                $value = $onerawset['value'];
                $clean_contractOptions[$key] = $value;
            }
            $rawresult_ar['contractOptions'] = $clean_contractOptions;
            
            $raw_sharingOptions = $rawresult_ar['sharingOptions'];
            $clean_sharingOptions = array();
            foreach($raw_sharingOptions as $onerawset)
            {
                $key = $onerawset['key'];
                $value = $onerawset['value'];
                $clean_sharingOptions[$key] = $value;
            }
            $rawresult_ar['sharingOptions'] = $clean_sharingOptions;
            
            error_log("LOOK getRadiologyOrderDialog clean >>>>".print_r($rawresult_ar, TRUE)); 
            return $rawresult_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getRadiologyReportsDetailMap($override_patientId = NULL)
    {
        try
        {
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            if($pid == '')
            {
                throw new \Exception('Cannot get Radiology Reports detail without a patient ID!');
            }
            $myhelper = new \raptor_ewdvista\RadiologyReportHelper();
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $pid;
            $args['fromDate'] = EwdUtils::getVistaDate(-1 * DEFAULT_GET_LABS_DAYS);
            $args['toDate'] = EwdUtils::getVistaDate(0);
            $args['nRpts'] = 1000;
            $rawresult_ar = $this->getServiceRelatedData($serviceName, $args);
            
error_log("LOOK RAW getRadiologyReportsDetailMap args>>>" . print_r($args, TRUE));            
error_log("LOOK RAW getRadiologyReportsDetailMap>>>" . print_r($rawresult_ar, TRUE));            
            
            $formatted_detail = $myhelper->getFormattedRadiologyReportHelperDetail($rawresult_ar);
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getRawVitalSignsMap($override_patientId = NULL)
    {
        if($override_patientId != NULL)
        {
            error_log("LOOK TODO --- build in support for optional param in getRawVitalSignsMap!");
            return FALSE;   //Optional param support is NOT yet implemented
        }
        try
        {
            $pid = $this->getSelectedPatientID();
            if($pid == NULL)
            {
                throw new \Exception('Cannot return vitals when there is no selected patient!');
            }
            $oContext = \raptor\Context::getInstance();
            if ($oContext != NULL)
            {
                //Utilize the cache.
                $sThisResultName = "{$pid}" . REDAO_CACHE_NM_SUFFIX_VITALS;
                $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
                if ($oRuntimeResultFlexCacheHandler != NULL)
                {
                    $aCachedResult = $oRuntimeResultFlexCacheHandler->checkCache($sThisResultName);
                    if ($aCachedResult !== NULL)
                    {
                        //Found it in the cache!
//error_log("LOOK final bundle getRawVitalSignsMap PULLED FROM CACHE >>> ".print_r($aCachedResult, TRUE));  
                        return $aCachedResult;
                    }
                }
            } else {
                $oRuntimeResultFlexCacheHandler = NULL;
            }
            
            $myhelper = new \raptor_ewdvista\VitalsHelper();
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $pid;
            $rawresult = array();
            $rawresult['result'] = $this->getServiceRelatedData($serviceName, $args);
            $bundle = $myhelper->getFormattedSuperset($rawresult);
            
//error_log("LOOK final bundle getRawVitalSignsMap ".print_r($bundle, TRUE));  
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                try 
                {
//error_log("LOOK final bundle getRawVitalSignsMap WENT INTO CACHE!!!");  
                    $oRuntimeResultFlexCacheHandler->addToCache($sThisResultName, $bundle, CACHE_AGE_LABS);
                } catch (\Exception $ex) {
                    error_log("Failed to cache $sThisResultName result because " . $ex->getMessage());
                }
            }
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getSurgeryReportsDetailMap($override_patientId = NULL)
    {
        try
        {
            if($override_patientId != NULL)
            {
                $pid = $override_patientId;
            } else {
                $pid = $this->getSelectedPatientID();
            }
            if($pid == '')
            {
                throw new \Exception('Cannot get surgery detail without a patient ID!');
            }
            $myhelper = new \raptor_ewdvista\SurgeryReportHelper();
            $serviceName = $this->getCallingFunctionName();

            //Get the medication data from EWD services
            $args = array();
            $args['patientId'] = $pid;
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $formatted_detail = $myhelper->getFormattedSurgeryReportDetail($rawresult);
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getSecurityKeysForUser($userduz)
    {
        try
        {
            $serviceName = 'getUserSecurityKeys';
            $args = array();
            $args['uid'] = $userduz;
            $formatted_detail = array();
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
//error_log("LOOK raw security thing>>>>" . print_r($rawresult,TRUE));
            $warnings = array();
            if(is_array($rawresult))
            {
                foreach($rawresult as $oneblock)
                {
                    $id = $oneblock['permissionId'];
                    $name = trim($oneblock['name']);
                    if($name > '')
                    {
                        $formatted_detail[$id] = $name;
                    } else {
                        $warnings[] = $id;
                    }
                }
            }
            if(count($warnings) > 0)
            {
                error_log("WARNING: For user DUZ=$userduz we did NOT find a security key name for the following IDs:" . implode(", ",$warnings));
            }
//error_log("LOOK formatted security thing>>>>" . print_r($formatted_detail,TRUE));            
            return $formatted_detail;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getUserSecurityKeys()
    {
        try
        {
            $securitykeys = $this->getSessionVariable('securitykeys');
            return $securitykeys;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getVisits()
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $this->getSelectedPatientID();
            $args['fromDate'] = EwdUtils::getVistaDate(-1 * DEFAULT_GET_VISIT_DAYS);
            $args['toDate'] = EwdUtils::getVistaDate(0);

            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $visitAry = $rawresult['value'];

            foreach ($visitAry as $visit) 
            {
                $a = explode('^', $visit);
                $l = explode(';', $a[0]); //first field is an array "location name;visit timestamp;locationID"
                $aryItem = array(
                    //'raw' => $visit,
                    'locationName' => $l[0],
                    'locationId' => $l[2],
                    'visitTimestamp' => EwdUtils::convertVistaDateToYYYYMMDD($a[1]), //same as $l[1]
                    'visitTO' => $a[2]
                );
                $result[] = $aryItem;   //Already acending
            }
            $aSorted = array_reverse($result); //Now this is descrnding.
            return $aSorted;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return NULL if no problems.
     */
    public function getVistaAccountKeyProblems()
    {
        try
        {
            $missingkeys = array();
            $mykeys = $this->getSessionVariable('securitykeys');
            $has_superkey = in_array('XUPROGMODE', $mykeys);
            if(!$has_superkey)
            {
                $minSecondaryOptions = array('DVBA CAPRI GUI'); //'OR CPRS GUI CHART'
                foreach($minSecondaryOptions as $keyName)
                {
                    $haskey = in_array($keyName, $mykeys);
                    if(!$haskey)
                    {
                        $missingkeys[] = $keyName;
                    }
                }
            }
            $errormsg = NULL;
            if(count($missingkeys) > 0)
            {
               $keystext = implode(', ',$missingkeys);
               $missingkeycount = count($missingkeys);
               $errormsg = "The VistA user account does not have access to ($missingkeycount keys): $keystext!";
               error_log("PRIVILEGES WARNING: " . $errormsg . ' >>> ' . $this);
            }
            return $errormsg;            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getVitalsDetailMap()
    {
        $vitalsbundle = $this->getRawVitalSignsMap();
        if(isset($vitalsbundle[0]))
        {
            //error_log("LOOK getVitalsDetailMap >>> ".print_r($vitalsbundle[0],TRUE));
            return $vitalsbundle[0];
        }
        //Return an empty array.
        return array(); 
    }

    public function getVitalsDetailOnlyLatestMap()
    {
        $vitalsbundle = $this->getRawVitalSignsMap();
        if(isset($vitalsbundle[2]))
        {
            //error_log("LOOK getVitalsDetailOnlyLatestMap >>> ".print_r($vitalsbundle[2],TRUE));
            return $vitalsbundle[2];
        }
        //Return an empty array.
        return array(); 
    }

    public function getVitalsSummaryMap()
    {
        /*
         * [10-Aug-2015 14:59:47 America/New_York] LOOK data format returned for 'getVitalsSummary' is >>>Array
(
    [Temperature] => Array
        (
            [Date of Measurement] => 08/17/2010 04:03 pm
            [Measurement Value] => 99.5 F
        )

    [Heart Rate] => Array
        (
            [Date of Measurement] => 
            [Measurement Value] => None Found
        )

    [Blood Pressure] => Array
        (
            [Date of Measurement] => 08/17/2010 04:03 pm
            [Measurement Value] => 190/85 mmHg
        )

    [Height] => Array
        (
            [Date of Measurement] => 06/10/2010 08:11 am
            [Measurement Value] => 71 in (180.3 cms)
        )

    [Weight] => Array
        (
            [Date of Measurement] => 06/10/2010 08:11 am
            [Measurement Value] => 175 lb (79.4 kgs)
        )

    [Body Mass Index] => Array
        (
            [Date of Measurement] => 06/10/2010 08:11 am
            [Measurement Value] => 24 
        )

)

         */
        try
        {
            $vitalsbundle = $this->getRawVitalSignsMap();
            $myhelper = new \raptor_ewdvista\VitalsHelper();
            $summary = $myhelper->getVitalsSummary($vitalsbundle);
error_log("LOOK final VitalsSummary ".print_r($summary, TRUE));  
            return $summary;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function isProvider()
    {
        try
        {
            $securitykeys = $this->getSessionVariable('securitykeys');
            return in_array('PROVIDER', $securitykeys);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function signNote($newNoteIen, $eSig)
    {
        $serviceName = $this->getCallingFunctionName();
	return $this->getServiceRelatedData($serviceName);
    }

    public function userHasKeyOREMAS()
    {
        try
        {
            $securitykeys = $this->getSessionVariable('securitykeys');
            return in_array('OREMAS', $securitykeys);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function validateEsig($eSig)
    {
        $serviceName = $this->getCallingFunctionName();
	return $this->getServiceRelatedData($serviceName);
    }

    public function verifyNoteTitleMapping($checkVistaNoteIEN, $checkVistaNoteTitle)
    {
        $titlemap = $this->getNoteTitles($checkVistaNoteTitle);
        if(is_array($titlemap) && isset($titlemap[$checkVistaNoteIEN]))
        {
            foreach($titlemap[$checkVistaNoteIEN] as $onetitle)
            {
                if($checkVistaNoteTitle == $onetitle)
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function getNoteTitles($startingitem)
    {
        try
        {
            $args = array();
            $args['target'] = $startingitem;
            $serviceName = $this->getCallingFunctionName();
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            return $rawresult;
        } catch (Exception $ex) {
            throw $ex;
        }
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

    public function invalidateCacheForEverything()
    {
        try
        {
            $oContext = \raptor\Context::getInstance();
            $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                $oRuntimeResultFlexCacheHandler->invalidateRaptorCacheAllDataAndFlags();
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function invalidateCacheForOrder($tid)
    {
        try
        {
            $oContext = \raptor\Context::getInstance();
            $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                $oRuntimeResultFlexCacheHandler->invalidateRaptorCacheData("{$tid}" . REDAO_CACHE_NM_SUFFIX_DASHBOARD);
                $oRuntimeResultFlexCacheHandler->invalidateRaptorCacheData(REDAO_CACHE_NM_WORKLIST);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function invalidateCacheForPatient($pid)
    {
        try
        {
            $sThisResultName = "{$pid}" . REDAO_CACHE_NM_SUFFIX_VITALS;
            $oContext = \raptor\Context::getInstance();
            $oRuntimeResultFlexCacheHandler = $oContext->getRuntimeResultFlexCacheHandler($this->m_groupname);
            if ($oRuntimeResultFlexCacheHandler != NULL)
            {
                $oRuntimeResultFlexCacheHandler->invalidateRaptorCacheData($sThisResultName);
                $oRuntimeResultFlexCacheHandler->invalidateRaptorCacheData(REDAO_CACHE_NM_WORKLIST);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getSelectedPatientID()
    {
        return $this->getSessionVariable('selectedPatient');
    }

    public function getPatientMap($sPatientID)
    {
        try
        {
            $serviceName = $this->getCallingFunctionName();
            $args = array();
            $args['patientId'] = $sPatientID;
            $rawresult = $this->getServiceRelatedData($serviceName, $args);
            $a = explode('^', $rawresult['value']);
//error_log("LOOK raw getPatientMap in>>>>".print_r($a,TRUE));

            $result = array();
            if(isset($a[2]) && $a[2] > '')
            {
                $vista_dob = trim($a[2]);
                $dob = \raptor_ewdvista\EwdUtils::convertVistaDateTimeToDate($vista_dob);
            } else {
                $dob = '';
            }
            if(isset($a[14]))
            {
                $age = intval($a[14]);
            } else {
                //TODO --- compute from today - dob;
                $age = 0;
            }
            $result['patientName']  			= $a[0];
            $result['ssn']          			= $a[3];
            $result['gender']       			= $a[1];
            $result['dob']          			= $dob;
            $result['ethnicity']    			= "todo";
            $result['age']          			= $age;
            $result['maritalStatus']			= "todo";
            $result['mpiPid']       			= "todo";
            $result['mpiChecksum']  			= "todo";
            $result['localPid']     			= "todo";
            $result['sitePids']     			= "todo";
            $result['vendorPid']    			= "todo";
            $result['location'] 			= "Room:todo / Bed:todo ";
            $result['cwad'] 				= "todo";
            $result['restricted'] 			= "todo";
            $result['admitTimestamp'] 			= date("m/d/Y h:i a", strtotime("01/01/1950 01:01 a")); //TODO
            $result['serviceConnected']                 = "todo";
            $result['scPercent'] 			= "todo";
            $result['inpatient'] 			= "todo";
            $result['deceasedDate'] 			= "todo";
            $result['confidentiality'] 			= "todo";
            $result['needsMeansTest'] 			= "todo";
            $result['patientFlags'] 			= "todo";
            $result['cmorSiteId']	 		= "todo";
            $result['activeInsurance'] 			= "todo";
            $result['isTestPatient'] 			= "todo";
            $result['currentMeansStatus']               = "todo";
            $result['hasInsurance'] 			= "todo";
            $result['preferredFacility']                = "todo";
            $result['patientType'] 			= "todo";
            $result['isVeteran'] 			= "todo";
            $result['isLocallyAssignedMpiPid']          = "todo";
            $result['sites'] 				= "todo";
            $result['teamID'] 				= "todo";
            $result['teamName'] 			= "todo-Unknown";
            $result['teamPcpName'] 			= "todo-Unknown";
            $result['teamAttendingName']                = "todo-Unknown";
            $result['mpiPid'] 				= "todo-Unknown";
            $result['mpiChecksum'] 			= "todo-Unknown";
//error_log("LOOK raw getPatientMap out>>>>".print_r($result,TRUE));
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
