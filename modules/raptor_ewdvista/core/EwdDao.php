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
    
    private $m_selectedPatientID = NULL;      //The currently selected patient
    
    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'core/Context');
        module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');
        $this->m_createdtimestamp = microtime();        
        $this->m_oWebServices = new \raptor_ewdvista\WebServices();
        $this->initClient();
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
        $this->m_selectedPatientID = NULL;
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
    
    public function getSelectedPatientID()
    {
        return $this->m_selectedPatientID;
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
            error_log("LOOK JSON DATA for $serviceName: " . print_r($json_string, TRUE));
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
    
    /**
     * Return the rows formatted the way RAPTOR expects to parse them.
     */
    private function getFormatWorklistRows($rawdatarows)
    {
        try
        {
            module_load_include('php', 'raptor_formulas', 'core/LanguageInference');
            module_load_include('php', 'raptor_formulas', 'core/MatchOrderToUser');
            module_load_include('php', 'raptor_datalayer', 'core/TicketTrackingData');
            $oTT = new \raptor\TicketTrackingData();
            $ticketTrackingDict = $oTT->getConsolidatedWorklistTracking();
            $ticketTrackingRslt = $ticketTrackingDict['raptor_ticket_tracking'];
            $ticketCollabRslt = $ticketTrackingDict['raptor_ticket_collaboration'];
            $scheduleTrackRslt = $ticketTrackingDict['raptor_schedule_track'];

            $aPatientPendingOrderCount = array();
            $aPatientPendingOrderMap = array();
            $nOffsetMatchIEN = NULL;
            
            $oContext = \raptor\Context::getInstance();
            $userinfo = $oContext->getUserInfo();

            $match_order_to_user = new \raptor_formulas\MatchOrderToUser($userinfo);
            $language_infer = new \raptor_formulas\LanguageInference();
            $unformatted_datarows = $rawdatarows['data'];
            //error_log("LOOK raw data for worklist>>>".print_r($unformatted_datarows,TRUE));
            if(is_array($unformatted_datarows))
            {
                $rowcount = count($unformatted_datarows);
            } else {
                $rowcount = 0;
            }
            //error_log("LOOK TODO implement reformat of $rowcount raw data rows");
            $formatted_datarows = array();
            $rownum = 0;
            foreach($unformatted_datarows as $onerow)
            {
                if(isset($onerow['PatientID']) && isset($onerow['Procedure']))
                {
                    $ienKey = $onerow['IEN'];
                    $patientID = $onerow['PatientID'];
                    $sqlTicketTrackRow = array_key_exists($ienKey, $ticketTrackingRslt) ? $ticketTrackingRslt[$ienKey] : NULL;
                    $sqlTicketCollaborationRow = array_key_exists($ienKey, $ticketCollabRslt) ? $ticketCollabRslt[$ienKey] : NULL;
                    $sqlScheduleTrackRow = array_key_exists($ienKey, $scheduleTrackRslt) ? $scheduleTrackRslt[$ienKey] : NULL;
                    $workflowstatus = (isset($sqlTicketTrackRow) ? $sqlTicketTrackRow->workflow_state : 'AC');
                    $studyname = $onerow['Procedure'];
                    $imagetype = $onerow['ImageType'];
                    $modality = $language_infer->inferModalityFromPhrase($imagetype);
                    if($modality == NULL)
                    {
                        $modality = $language_infer->inferModalityFromPhrase($studyname);
                    }
                    //Add the clean row to our collection of rows.
                    if($modality > '')
                    {
                        //We have usable data for this one.
                        $rownum++;
                        $cleanrow = array();
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_TRACKINGID] = $ienKey;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_PATIENTID] = $patientID;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_PATIENTNAME] = $onerow['PatientName'];
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED] = $onerow['DesiredDate'];
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_DATEORDERED] = $onerow['OrderedDate'];
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_MODALITY] = $modality;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_STUDY] = $studyname;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_URGENCY] = $onerow['Urgency'];
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = $onerow['Transport'];
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION ] = 'TODO_WLIDX_PATIENTCATEGORYLOCATION ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_ANATOMYIMAGESUBSPEC] = 'TODO_WLIDX_ANATOMYIMAGESUBSPEC ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] = $workflowstatus;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_ORDERSTATUS] = 'TODO_WLIDX_ORDERSTATUS ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_EDITINGUSER] = 'TODO_WLIDX_EDITINGUSER ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO] = 'TODO_WLIDX_SCHEDINFO ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_CPRSCODE] = 'TODO_WLIDX_CPRSCODE ';
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE] = $imagetype;

                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] = 123;    //TODO 
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT] = 20; 
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_EXAMLOCATION] = 21;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_REQUESTINGPHYSICIAN] = 22;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = 23;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_ORDERFILEIEN] = 24;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_RADIOLOGYORDERSTATUS] = 25;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_ISO8601_DATETIMEDESIRED] = 26;
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_ISO8601_DATEORDERED] = 27;

                        //Only show an assignment if ticket has not yet moved downstream in the workflow.
                        if($workflowstatus == 'AC' 
                            || $workflowstatus == 'CO' 
                            || $workflowstatus == 'RV')
                        {
                            $aPatientPendingOrderMap[$patientID][$ienKey] 
                                    = array($ienKey,$modality,$studyname);
                            if(isset($aPatientPendingOrderCount[$patientID]))
                            {
                                $aPatientPendingOrderCount[$patientID] +=  1; 
                            } else {
                                $aPatientPendingOrderCount[$patientID] =  1; 
                            }
                            $cleanrow[\raptor\WorklistColumnMap::WLIDX_ASSIGNEDUSER] = (isset($sqlTicketCollaborationRow) ? array(
                                                                      'uid'=>$sqlTicketCollaborationRow->collaborator_uid
                                                                    , 'requester_notes_tx'=>$sqlTicketCollaborationRow->requester_notes_tx
                                                                    , 'requested_dt'=>$sqlTicketCollaborationRow->requested_dt
                                                                    , 'username'=>$sqlTicketCollaborationRow->username
                                                                    , 'fullname'=>trim($sqlTicketCollaborationRow->usernametitle 
                                                                            . ' ' .$sqlTicketCollaborationRow->firstname
                                                                            . ' ' .$sqlTicketCollaborationRow->lastname. ' ' .$sqlTicketCollaborationRow->suffix )
                                                                ) : NULL);    
                        } else {
                            $cleanrow[\raptor\WorklistColumnMap::WLIDX_ASSIGNEDUSER] = '';
                        }

                        // Pull schedule from raptor_schedule_track
                        if($sqlScheduleTrackRow != null)
                        {
                            //If a record exists, then there is something to see.
                            $showText = '';
                            if(isset($sqlScheduleTrackRow->scheduled_dt))
                            {
                                $phpdate = strtotime( $sqlScheduleTrackRow->scheduled_dt );
                                $sdt = date( 'Y-m-d H:i', $phpdate ); //Remove the seconds
                                if(isset($sqlScheduleTrackRow->confirmed_by_patient_dt))
                                {
                                    if($showText > '')
                                    {
                                       $showText .= '<br>'; 
                                    }
                                    $showText .= 'Confirmed '.$sqlScheduleTrackRow->confirmed_by_patient_dt; 
                                }
                                if($showText > '')
                                {
                                   $showText .= '<br>'; 
                                }
                                $showText .= 'For '. $sdt ;//$sqlScheduleTrackRow->scheduled_dt; 
                                if(isset($sqlScheduleTrackRow->location_tx))
                                {
                                    if($showText > '')
                                    {
                                       $showText .= '<br>'; 
                                    }
                                    $showText .= 'In ' . $sqlScheduleTrackRow->location_tx; 
                                }
                            }
                            if(isset($sqlScheduleTrackRow->canceled_dt))
                            {
                                //If we are here, clear everything before.
                                $showText = 'Cancel requested '.$sqlScheduleTrackRow->canceled_dt; 
                            }
                            if(trim($sqlScheduleTrackRow->notes_tx) > '')
                            {
                                if($showText > '')
                                {
                                   $showText .= '<br>'; 
                                }
                                $showText .= 'See Notes...'; 
                            }
                            if($showText == '')
                            {
                                //Indicate there is someting to see in the form.
                                $showText = 'See details...';
                            }
                            $cleanrow[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO] = array(
                                'EventDT' => $sqlScheduleTrackRow->scheduled_dt,
                                'LocationTx' => $sqlScheduleTrackRow->location_tx,
                                'ConfirmedDT' => $sqlScheduleTrackRow->confirmed_by_patient_dt,
                                'CanceledDT' => $sqlScheduleTrackRow->canceled_dt,
                                'ShowTx' => $showText
                            );
                            print_r($sqlScheduleTrackRow, TRUE);
                        } else {
                            //No record exists yet.
                            $cleanrow[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO] = array(
                                'EventDT' => NULL,
                                'LocationTx' => NULL,
                                'ConfirmedDT' => NULL,
                                'CanceledDT' => NULL,
                                'ShowTx' => 'Unknown'
                            );
                        }
                        
                        //Compute the score AFTER all the other columns are set.
                        $rankscore = $match_order_to_user->getTicketRelevance($cleanrow);
                        $cleanrow[\raptor\WorklistColumnMap::WLIDX_RANKSCORE] = $rankscore;
                        
                        //Add the row to our collection
                        $formatted_datarows[$rownum] = $cleanrow;
                    }
                }
            }
            //Now walk through all the clean rows to update the pending order reference information
            for($i=0;$i<count($formatted_datarows);$i++)
            {
                $t = &$formatted_datarows[$i];
                if(is_array($t))
                {
                    //Yes, this is a real row.
                    $patientID = $t[\raptor\WorklistColumnMap::WLIDX_PATIENTID];
                    if(isset($aPatientPendingOrderMap[$patientID]))
                    {
                        $t[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT] 
                                = $aPatientPendingOrderMap[$patientID];
                        $t[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] 
                                = $aPatientPendingOrderCount[$patientID];
                    } else {
                        //Found no pending orders for this IEN
                        $t[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT] = array();;
                        $t[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] = 0;
                    }
                }
            }
            
            return $formatted_datarows;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns array of arrays the way RAPTOR expects it.
     */
    public function getWorklistDetailsMap()
    {
        //$serviceName = 'getWorklistDetailsMap';
        $serviceName = $this->getCallingFunctionName();
        $rawdatarows = $this->getServiceRelatedData($serviceName);
        $matching_offset = NULL;    //TODO
        $pending_orders_map = NULL; //TODO
        $formated_datarows = $this->getFormatWorklistRows($rawdatarows);
        //{"Pages":1,"Page":1,"RowsPerPage":9999,"DataRows":{"1":{"0":
        $aResult = array('Pages'=>1
                        ,'Page'=>1
                        ,'RowsPerPage'=>9999
                        ,'DataRows'=>$formated_datarows
                        ,'matching_offset' => $matching_offset
                        ,'pending_orders_map' => $pending_orders_map
            );
        error_log("LOOK worklist result>>>".print_r($aResult,TRUE));
        return $aResult;
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
        $this->m_selectedPatientID = $sPatientID;
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

    public function getHospitalLocationsMap($startingitem)
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

    public function invalidateCacheForOrder($tid)
    {
        try
        {
            //TODO clear all the cache entries!
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function invalidateCacheForPatient($pid)
    {
        try
        {
            //TODO clear all the cache entries!
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
