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

require_once 'Context.php';

/**
 * Provide metrics on performance of underlying DAO calls.
 *
 * @author Frank Font of SAN Business Consultants
 */
class EhrDaoRuntimeMetrics
{
    private $m_instanceTimestamp = NULL;
    private $m_ehrDao = NULL;
    private $m_oContext = NULL;
    
    public function __construct()
    {
        try
        {
            $this->m_oContext = \raptor\Context::getInstance();
            $this->m_instanceTimestamp = microtime();
            $this->m_ehrDao = $this->m_oContext->getEhrDao();
        } catch (\Exception $ex) {
            throw new \Exception("Failed constructor EhrDaoRuntimeMetrics because $ex",99876,$ex);
        }
    }

    /**
     * Return real worklist tickets
     */
    public function getRealTickets($limit=100,$start_after=NULL,$fail_if_too_few=TRUE)
    {
        try
        {
            if($start_after == NULL)
            {
                $start_after = 0;
            }
            $real_tickets = array();
            $oContext = \raptor\Context::getInstance();
            $ehrDao = $oContext->getEhrDao();
            $worklistbundle = $ehrDao->getWorklistDetailsMap();
            $datarows = $worklistbundle['DataRows'];
            if(count($datarows) < 1)
            {
                //Empty result is not normal; check the user account.
                $logmsg = 'No order data was returned from VistA using your account';
                drupal_set_message($logmsg,'warning');
                error_log($logmsg);
            }
            $visited = 0;
            $added = 0;
            foreach($datarows as $onerow)
            {
                $visited++;
                if($visited > $start_after)
                {
                    $tid = $onerow[0];
                    $real_tickets[$tid] = $onerow;
                    $added++;
                    if($added >= $limit)
                    {
                        break;
                    }
                }
            }
            $found = count($real_tickets);
            if($fail_if_too_few && $found < $limit)
            {
                throw new \Exception("Only found $found but needed $limit after $start_after");
            }
            return $real_tickets;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getRealTickets($limit,$start_after)",99876,$ex);
        }
    }
    
    /**
     * These are the available filter options
     */
    public function getMetricFilterOptions()
    {
        return array(
            'core'      //All methods are members of this group
            ,'user'     //Only methods that are for ther user account are members of this group
            ,'worklist' //Only the worklist for special testing (not one order specific)
            ,'dialog'   //Only methods that are used in dialogs are members of this group
            ,'setup'    //Critical functions for setup are members of this group
            ,'oneorder' //Only methods that operate on ONE order are members of this group
            );
    }
    
    private function getOneCallFunctionDefForEhrDao($methodname
            , $params=array()
            , $membership=array('core','oneorder')
            , $store_result=array()
            , $expected_result=NULL //NULL or 0bytes or array or something or array(something)
            )
    {
        $def = array('namespace'=>'raptor'
                    ,'membership'=>$membership
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>$methodname
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>$params
                    ,'store_result'=>$store_result
                    ,'expected_result'=>$expected_result
                );
        return $def;
    }
    
    /**
     * Returns the filtered list of functions to call
     */
    private function getFunctionsToCall($membership_filter=array('core'))
    {
        $def_getinstanceliteral = '$this->m_oContext->getEhrDao()';
        
        //Create an array with entire sequence of available functions to call
        $callfunctions = array();
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'membership'=>$this->getMetricFilterOptions() //Always all for this one
                    ,'classname'=>'Context'
                    ,'methodname'=>'setSelectedTrackingID'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/Context.php'
                    ,'getinstanceliteral'=>'\raptor\Context::getInstance()'
                    ,'params'=>array('$tid')
                    ,'store_result'=>array()
                    ,'expected_result'=>NULL
                );
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getImplementationInstance'
                ,array()
                ,array('core','setup'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('isAuthenticated'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('isProvider'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('userHasKeyOREMAS'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getUserSecurityKeys'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVistaAccountKeyProblems'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getIntegrationInfo'
                ,array()
                ,array('core'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getWorklistDetailsMap'
                ,array()
                ,array('core','worklist')
                ,array()
                ,'something');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDashboardDetailsMap'
                ,array('$tid'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getPatientIDFromTrackingID'
                ,array('$tid')
                ,array('core','dialog','oneorder')
                ,array('$testres_patient_id')
                ,'something');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getAllHospitalLocationsMap'
                ,array()
                ,array('core','dialog')
                ,array('$testres_hosplocations'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getAllergiesDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getChemHemLabs');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDiagnosticLabsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getEGFRDetailMap');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getNotesDetailMap');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getOrderOverviewMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getPathologyReportsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getPendingOrdersMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProblemsListDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProcedureLabsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getRadiologyCancellationReasons');

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getRadiologyReportsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getRawVitalSignsMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getSurgeryReportsDetailMap');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsDetailOnlyLatestMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsSummaryMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getMedicationsDetailMap');

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getImagingTypesMap'
                ,array()
                ,array('core','dialog')
                ,array('$testres_imagetypes'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVisits'
                ,array('$tid')
                ,array('core','dialog')
                ,array('$testres_visits')
                ,'array(something)');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProviders'
                ,array('""')
                ,array('core','dialog')
                ,array()
                ,'array(something)');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getEncounterStringFromVisit'
                ,array('$this->getFirstVisitTO($testres_visits)')
                ,array('core','dialog'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getOrderableItems'
                ,array('$this->getFirstArrayKey($testres_imagetypes)')
                ,array('core','dialog')
                ,array('$testres_orderableitems')
                ,'array(something)');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getRadiologyOrderChecks'
                ,array('$this->getArgsForRadOrdCheck($testres_hosplocations,$testres_orderableitems,$testres_patient_id)')
                ,array('core','dialog'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getRadiologyOrderDialog'
                ,array('$this->getFirstArrayKey($testres_imagetypes)'
                     , '$testres_patient_id')
                ,array('core','dialog'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('verifyNoteTitleMapping'
                ,array(
                     '"'.VISTA_NOTE_TITLE_RAPTOR_GENERAL.'"'
                    ,'"'.VISTA_NOTEIEN_RAPTOR_GENERAL.'"')
                ,array('core','setup'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('setPatientID'
                ,array('$testres_patient_id')
                ,array('core','oneorder')
                );
        
        //Now filter out the functions we do not want to call.
        $filtered = array();
        foreach($callfunctions as $candidate)
        {
            $membership = $candidate['membership'];
            foreach($membership_filter as $onefilter)
            {
                if(in_array($onefilter, $membership))
                {
                    $filtered[] = $candidate;
                    break;
                }
            }
        }
        return $filtered;
    }
    
    /**
     * Used by METADATA COMMANDS not literal code!!!!!
     */
    private function getFirstVisitTO($testres_visits)
    {
        if(!isset($testres_visits[0]['visitTO']))
        {
            throw new \Exception("Did NOT find 'visitTO' at offset 0 in ".print_r($testres_visits,TRUE));
        }
        return $testres_visits[0]['visitTO'];
    }
    
    /**
     * Used by METADATA COMMANDS not literal code!!!!!
     */
    private function getFirstArrayValue($myarray,$error_if_empty=TRUE)
    {
        if(!is_array($myarray))
        {
            throw new \Exception("Expected an array!");
        }
        foreach($myarray as $value)
        {
            return $value;
        }
        if($error_if_empty)
        {
            throw new \Exception("Expected an array with at least one value!");
        }
        return NULL;
    }

    /**
     * Used by METADATA COMMANDS not literal code!!!!!
     */
    private function getFirstArrayKey($myarray,$error_if_empty=TRUE)
    {
        if(!is_array($myarray))
        {
            throw new \Exception("Expected an array!");
        }
        foreach($myarray as $key=>$value)
        {
            return $key;
        }
        if($error_if_empty)
        {
            throw new \Exception("Expected an array with at least one value!");
        }
        return NULL;
    }
    
    /**
     * Used by METADATA COMMANDS not literal code!!!!!
     */
    private function getArgsForRadOrdCheck($hosplocations,$orderableitems,$patient_id)
    {
        try
        {
            $location = $this->getFirstArrayKey($hosplocations);
            $orderitem = $this->getFirstArrayKey($orderableitems);
            $args = array();
            $date = new \DateTime();
            $args['startDateTime'] = $date->getTimestamp();
            $args['locationIEN'] = $location;
            $args['orderableItemId'] = $orderitem;
            $args['patientId'] = $patient_id;
            return $args;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getArgsForRadOrdCheck for p=" 
                    . print_r($hosplocations,TRUE) 
                    . " and p2=" 
                    . print_r($orderableitems,TRUE));
        }
    }
    
    /**
     * Returns associative array of performance results
     */
    public function getPerformanceDetails($ticketlist,$membership_filter=array('setup','oneorder'))
    {
        global $user;
        $usertxtinfo = "user {$user->uid} from vista site ".VISTA_SITE." at host {$user->hostname}";
        error_log("Started getPerformanceDetails for ticketlist=" . print_r($ticketlist,TRUE) 
                . " as $usertxtinfo"
                . "\n\tfilter = ".print_r($membership_filter,TRUE));   //This can take many resources so log it
        if(!is_array($ticketlist))
        {
            throw new \Exception('Must provide an input list of tickets to process!');
        }
        if(!is_array($membership_filter))
        {
            throw new \Exception('Must provide a valid membership_filter array instead of '.print_r($membership_filter,TRUE));
        }
        $ticketstats = array();
        try
        {
            $functionstocall = $this->getFunctionsToCall($membership_filter);
            $result = array();
            $info = array();
            $info['description'] = "{$this->m_ehrDao}"; 
            $result['DAO'] = $info;
            $metrics = array();
            //Process all the tickets
            foreach($ticketlist as $tid)
            {
                $ticketstats[$tid] = array();
                $ticketstats[$tid]['error_count'] = 0;
                $ticketstats[$tid]['start_ts'] = microtime(TRUE);
                //Initialize all the variables now and check syntax
                foreach($functionstocall as $details)
                {
                    $store_result = $details['store_result'];
                    if(is_array($store_result))
                    {
                        if(count($store_result) == 1)
                        {
                            $store_result_varname = $store_result[0];
                            $required_prefix = '$testres_';
                            if(strlen($store_result_varname) <= strlen($required_prefix))
                            {
                                throw new \Exception("Unsupported (too short) store_result value=".print_r($store_result));
                            }
                            if(substr($store_result_varname,0,9) != $required_prefix)
                            {
                                throw new \Exception("Unsupported (missing prefix '$required_prefix') store_result value=".print_r($store_result));
                            }
                            $eval_assign = "{$store_result_varname} = NULL;";   //Simply initialize so we avoid MISSING VARIABLE on errors later!
                            eval($eval_assign);
                        } else
                        if(count($store_result) > 1)
                        {
                            throw new \Exception("Unsupported store_result value=".print_r($store_result));
                        }
                    }
                }
                foreach($functionstocall as $details)
                {
                    $oneitem = array();
                    $oneitem['start_ts'] = microtime(TRUE);
                    $oneitem['tracking_id'] = $tid;
                    $callresult = NULL;
                    try
                    {
                        $oneitem['metadata'] = $details;
                        $namespace = $details['namespace'];
                        $classname = $details['classname'];
                        $methodname = $details['methodname'];
                        $store_result_varname = NULL;
                        $store_result = $details['store_result'];
                        $expected_result = $details['expected_result'];
                        if(is_array($store_result))
                        {
                            if(count($store_result) == 1)
                            {
                                $store_result_varname = $store_result[0];
                                $required_prefix = '$testres_';
                                if(strlen($store_result_varname) <= strlen($required_prefix))
                                {
                                    throw new \Exception("Unsupported (too short) store_result value=".print_r($store_result));
                                }
                                if(substr($store_result_varname,0,9) != $required_prefix)
                                {
                                    throw new \Exception("Unsupported (missing prefix '$required_prefix') store_result value=".print_r($store_result));
                                }
                            } else
                            if(count($store_result) > 1)
                            {
                                throw new \Exception("Unsupported store_result value=".print_r($store_result));
                            }
                        }
                        $getinstanceliteral = isset($details['getinstanceliteral']) ? $details['getinstanceliteral'] : NULL;
                        $params = array();
                        foreach($details['params'] as $oneparam)
                        {
                            $evalthis = "return ".$oneparam." ;";
                            $oneparamvalue = eval($evalthis);
                            $params[] = $oneparamvalue;
                        }
                        $oneitem['paramvalues'] = $params;
                        $class = "\\$namespace\\$classname";
                        if($getinstanceliteral != NULL)
                        {
                            //Call a method to get the instance
                            $implclass = eval("return {$getinstanceliteral};");
                        } else {
                            //Simply use the constructor
                            $implclass = new $class();
                        }
                        $num_params = count($params);
                        if($num_params == 0) {
                            $callresult = $implclass->$methodname();
                        } else if($num_params == 1) {
                            $callresult = $implclass->$methodname($params[0]);
                        } else if($num_params == 2) {
                            $callresult = $implclass->$methodname($params[0],$params[1]);
                        } else if($num_params == 3) {
                            $callresult = $implclass->$methodname($params[0],$params[1],$params[2]);
                        } else {
                            //If this happens, add another handler above.
                            throw new \Exception("Currently no support implemented to call with $num_params arguments!");
                        }
                        if($expected_result !== NULL)
                        {
                            //We defined an expected result, check it.
                            if($expected_result == '0bytes' || $expected_result == 'something')
                            {
                                $rsize = strlen(print_r($callresult,TRUE));
                                if($expected_result == '0bytes')
                                {
                                    if($rsize > 0)
                                    {
                                        throw new \Exception("Expected result as $expected_result but got something as ".print_r($callresult,TRUE));
                                    }
                                } else {
                                    if($rsize < 1)
                                    {
                                        throw new \Exception("Expected result as $expected_result but got 0 bytes ".print_r($callresult,TRUE));
                                    }
                                }
                            } else if($expected_result == 'array' || $expected_result == 'array(something)') {
                                if(!is_array($callresult))
                                {
                                    throw new \Exception("Expected result as $expected_result but got ".print_r($callresult,TRUE));
                                }
                                if($expected_result == 'array(something)')
                                {
                                    if(count($callresult) < 1)
                                    {
                                        throw new \Exception("Expected result as $expected_result but got 0 count in ".print_r($callresult,TRUE));
                                    }
                                }
                            } else {
                                throw new \Exception("Cannot parse '$expected_result' as an expected result");
                            }
                        }
                        if($store_result_varname != NULL)
                        {
                            $eval_assign = "{$store_result_varname} = " . '$callresult;';
                            eval($eval_assign);
                            $evalthis = "return {$store_result_varname};";
//$justlooking = eval($evalthis);
//error_log("LOOK eval assign( $eval_assign ) just looking >>>" . print_r($justlooking,TRUE));                            
                        }
                    } catch (\Exception $ex) {
                        //Continue with other items
                        $oneitem['failed_info'] = $ex;
                        $ticketstats[$tid]['error_count'] = $ticketstats[$tid]['error_count'] + 1;
                    }
                    $oneitem['end_ts'] = microtime(TRUE);
                    $resultsize = strlen(print_r($callresult,TRUE));
                    $oneitem['resultsize'] = $resultsize;
                    $metrics[] = $oneitem;
                }
                $ticketstats[$tid]['end_ts'] = microtime(TRUE);
                $ticketstats[$tid]['duration'] = $ticketstats[$tid]['end_ts'] - $ticketstats[$tid]['start_ts'];
            }
            $result['ticketstats'] = $ticketstats;
            $result['metrics'] = $metrics;
            error_log("Finished getPerformanceDetails for ticketlist=" 
                    . print_r($ticketlist,TRUE) 
                    . " as $usertxtinfo");   //This can take many resources so log it
            return $result;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting metrics",99876,$ex);
        }
    }
}
