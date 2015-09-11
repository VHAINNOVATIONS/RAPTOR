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
            , $expected_result=NULL //NULL or 0bytes or array or something or array(something) or mustmatch(varname)
            , $call_only_if=NULL    //NULL means always call this method
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
                    ,'call_only_if'=>$call_only_if
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
                    ,'call_only_if'=>NULL
                );
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getImplementationInstance'
                ,array()
                ,array('core','setup'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('isAuthenticated'
                ,array()
                ,array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getSelectedPatientID'
                ,array()
                ,array('core','dialog','oneorder')
                ,array('$testres_patient_id')
                ,'something');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getPatientIDFromTrackingID'
                ,array('$tid')
                ,array('core','dialog','oneorder')
                ,array('$testres_patient_id_for_ticket')
                ,'mustmatch($testres_patient_id)');
        
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
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDashboardDetailsMap');

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDashboardDetailsMap'
                ,array('$tid') 
                ,array('core'));
        
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
        //deprecated 20150911 $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProcedureLabsDetailMap');
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
                ,array()
                ,array('core','dialog')
                ,array('$testres_visits')
                ,'array');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProviders'
                ,array('""')
                ,array('core','dialog')
                ,array()
                ,'array(something)');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getEncounterStringFromVisit'
                ,array('$this->getFirstVisitTO($testres_visits)')
                ,array('core','dialog')
                ,array()
                ,'something'
                ,'$this->isArrayWithData($testres_visits)');

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

        //Test this one last because it is REDUNDANT with the first command in the core chain!!!!
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('setPatientID' 
                ,array('$testres_patient_id')   //Set it to the value we already got
                ,array('core')
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
    private function isArrayWithData($candidate)
    {
        if(!is_array($candidate) || count($candidate) < 1)
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Used by METADATA COMMANDS not literal code!!!!!
     */
    private function getFirstVisitTO($testres_visits,$fail_if_empty=TRUE)
    {
        if(!is_array($testres_visits))
        {
            throw new \Exception("Expected array of visits but got this instead = ".print_r($testres_visits,TRUE));
        }
        foreach($testres_visits as $onevisit)
        {
            //Just return the first one and we are done!
            if(!isset($onevisit['visitTO']))
            {
                throw new \Exception("Did NOT find 'visitTO' in ".print_r($onevisit,TRUE));
            }
            return $onevisit['visitTO'];
        }
        if($fail_if_empty)
        {
            throw new \Exception("Did NOT find 'visitTO' at offset 0 in ".print_r($testres_visits,TRUE));
        }
        return FALSE;
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
                                throw new \Exception("Unsupported (too short) store_result value=".print_r($store_result,TRUE));
                            }
                            if(substr($store_result_varname,0,9) != $required_prefix)
                            {
                                throw new \Exception("Unsupported (missing prefix '$required_prefix') store_result value=".print_r($store_result,TRUE));
                            }
                            $eval_assign = "{$store_result_varname} = NULL;";   //Simply initialize so we avoid MISSING VARIABLE on errors later!
                            eval($eval_assign);
                        } else
                        if(count($store_result) > 1)
                        {
                            throw new \Exception("Unsupported store_result value=".print_r($store_result,TRUE));
                        }
                    }
                }
                //Call each of the functions on the current ticket.
                foreach($functionstocall as $details)
                {
                    $oneitem = array();
                    $oneitem['start_ts'] = microtime(TRUE);
                    $oneitem['tracking_id'] = $tid;
                    $oneitem['paramvalues'] = NULL; //Initialize now so the key exists.
                    $callresult = NULL;
                    try
                    {
                        $oneitem['metadata'] = $details;
                        $namespace = $details['namespace'];
                        $classname = $details['classname'];
                        $methodname = $details['methodname'];
                        $call_only_if = $details['call_only_if'];
                        if($call_only_if != NULL)
                        {
                           $test_this_method = eval("return ".$call_only_if." ;");  
                        } else {
                            $test_this_method = TRUE;
                        }
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
                                    throw new \Exception("Unsupported (too short) store_result value=".print_r($store_result,TRUE));
                                }
                                if(substr($store_result_varname,0,9) != $required_prefix)
                                {
                                    throw new \Exception("Unsupported (missing prefix '$required_prefix') store_result value=".print_r($store_result,TRUE));
                                }
                            } else
                            if(count($store_result,TRUE) > 1)
                            {
                                throw new \Exception("Unsupported store_result value=".print_r($store_result,TRUE));
                            }
                        }
                        $getinstanceliteral = isset($details['getinstanceliteral']) ? $details['getinstanceliteral'] : NULL;
                        $paramvalues = array();
                        if(!$test_this_method)
                        {
                            //Just assume a NULL result
                            $implclass = NULL;
                            $callresult = NULL;
                            $oneitem['skipped_info'] = "FALSE from ".print_r($call_only_if,TRUE);   //20150826
                            $oneitem['paramvalues'] = $paramvalues;
                        } else {
                            //Actually test the method
                            foreach($details['params'] as $oneparam)
                            {
                                $evalthis = "return ".$oneparam." ;";
                                $oneparamvalue = eval($evalthis);
                                $paramvalues[] = $oneparamvalue;
                            }
                            $oneitem['paramvalues'] = $paramvalues;
                            $class = "\\$namespace\\$classname";
                            try
                            {
                                if($getinstanceliteral != NULL)
                                {
                                    //Call a method to get the instance
                                    $implclass = eval("return {$getinstanceliteral};");
                                } else {
                                    //Simply use the constructor
                                    $implclass = new $class();
                                }
                            } catch (\Exception $ex) {
                                //Provide something meaningful for debugging
                                if($getinstanceliteral != NULL)
                                {
                                    $valueaddedtext = "Failed to get instance of"
                                            . " implementing class"
                                            . " from literal cmd '$getinstanceliteral' because $ex";
                                    $implclass = "[Failed literal {$getinstanceliteral}]";
                                } else {
                                    $valueaddedtext = "Failed to get instance of"
                                            . " implementing class"
                                            . " called '$class' because $ex";
                                    $implclass = "[Failed load class {$class}]";
                                }
                                throw new \Exception($valueaddedtext,99765,$ex);
                            }
                            $num_params = count($paramvalues);
                            if($num_params == 0) {
                                $callresult = $implclass->$methodname();
                            } else if($num_params == 1) {
                                $callresult = $implclass->$methodname($paramvalues[0]);
                            } else if($num_params == 2) {
                                $callresult = $implclass->$methodname($paramvalues[0],$paramvalues[1]);
                            } else if($num_params == 3) {
                                $callresult = $implclass->$methodname($paramvalues[0],$paramvalues[1],$paramvalues[2]);
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
                                } else if(substr($expected_result,0,11) == 'mustmatch($') {
                                    $firstpart_match_varname = substr($expected_result,10);
                                    $match_varname = substr($firstpart_match_varname,0,strlen($firstpart_match_varname)-1);
                                    $evalthis = "return {$match_varname};";
                                    $match_value = eval($evalthis);
                                    $txt_match_value = print_r($match_value,TRUE);
                                    $size_txt_match_value = strlen($txt_match_value);
                                    if($callresult === NULL && $match_value !== NULL)
                                    {
                                        $match_failed = TRUE;
                                        $match_failed_msg = ("expected result to match variable $match_varname but NULL result " 
                                                . " does not match " 
                                                . print_r($match_value,TRUE));
                                    } else
                                    if($callresult !== NULL && $match_value === NULL)
                                    {
                                        $match_failed = TRUE;
                                        $match_failed_msg = ("expected result to match variable $match_varname but result " 
                                                . print_r($callresult,TRUE) 
                                                . " does not match NULL value");
                                    } else
                                    if($callresult !== $match_value)
                                    {
                                        $match_failed = TRUE;
                                        $match_failed_msg = ("expected result to match variable $match_varname but result " 
                                                . print_r($callresult,TRUE) 
                                                . " does not match value (len=$size_txt_match_value) "  
                                                . $txt_match_value);
                                    } else {
                                        $match_failed = FALSE;
                                    }
                                    if($match_failed)
                                    {
                                        if(count($paramvalues) == 0)
                                        {
                                            throw new \Exception("Method with NO parameters failed because " 
                                                    . $match_failed_msg);
                                        } else {
                                            $params_txt = implode(',',$paramvalues);
                                            throw new \Exception("Method with " 
                                                    . count($paramvalues) 
                                                    . " parameters=$params_txt failed because " 
                                                    . $match_failed_msg);
                                        }
                                    }
                                } else {
                                    throw new \Exception("Cannot parse '$expected_result' as an expected result");
                                }
                            }
                        }
                        if($store_result_varname != NULL)
                        {
                            $eval_assign = "{$store_result_varname} = " . '$callresult;';
                            eval($eval_assign);
                            $evalthis = "return {$store_result_varname};";
                        }
                    } catch (\Exception $ex) {
                        //Continue with other items
                        $oneitem['failed_info'] = $ex;
                        $newerrorcount = $ticketstats[$tid]['error_count'] + 1;
                        $ticketstats[$tid]['error_count'] = $newerrorcount;
                        $exception_txt_tolog = print_r($ex,TRUE);
                        if($newerrorcount > 5)
                        {
                            //Prevent overwhelming the log file!
                            if(strlen($exception_txt_tolog) > 500)
                            {
                                $exception_txt_tolog = '(TRUNCATED exception text) '.substr($exception_txt_tolog,0,500);
                            }
                        }
                        if(trim($tid) > '')
                        {
                            $pidonfail = $this->m_ehrDao->getPatientIDFromTrackingID($tid);
                        } else {
                            $pidonfail = 'UNKNOWN';
                        }
                        error_log("Error#{$newerrorcount} for test on tid=$tid (patient=$pidonfail)"
                                . " of Dao method={$methodname} using DAO=" . $this->m_ehrDao
                                . "\n\tClass instance on fail=".print_r($implclass,TRUE)
                                . "\n\tException Details=$exception_txt_tolog");
                    }
                    $oneitem['end_ts'] = microtime(TRUE);
                    $oneitem['patient_id'] = $this->m_ehrDao->getSelectedPatientID();
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
