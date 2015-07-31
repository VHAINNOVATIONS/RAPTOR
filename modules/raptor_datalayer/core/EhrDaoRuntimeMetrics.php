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
     * These are the available filter options
     */
    public function getMetricFilterOptions()
    {
        return array(
            'core'      //All methods are members of this group
            ,'user'     //Only methods that are for ther user account are members of this group
            ,'dialog'   //Only methods that are used in dialogs are members of this group
            ,'setup'    //Critical functions for setup are members of this group
            ,'oneorder' //Only methods that operate on ONE order are members of this group
            );
    }
    
    private function getOneCallFunctionDefForEhrDao($methodname
            , $params=array()
            , $membership=array('core','oneorder')
            , $store_result=array())
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
                );
        return $def;
    }
    
    /**
     * Returns the list of functions to call on each ticket
     */
    private function getFunctionsToCall($membership_filter)
    {
        $def_getinstanceliteral = '$this->m_oContext->getEhrDao()';
        
        //Return stuff we need to call for each ticket
        $callfunctions = array();
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'membership'=>array('core','setup','oneorder')
                    ,'classname'=>'Context'
                    ,'methodname'=>'setSelectedTrackingID'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/Context.php'
                    ,'getinstanceliteral'=>'\raptor\Context::getInstance()'
                    ,'params'=>array('$tid')
                    ,'store_result'=>array()
                );
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getImplementationInstance'
                ,array()
                ,array('core','setup'));

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('isAuthenticated',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('isProvider',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('userHasKeyOREMAS',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getUserSecurityKeys',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVistaAccountKeyProblems',array(),array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getIntegrationInfo'
                ,array()
                ,array('core'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getWorklistDetailsMap'
                ,array()
                ,array('core'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDashboardDetailsMap'
                ,array('$tid'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getPatientIDFromTrackingID'
                ,array('$tid')
                ,array('core','oneorder')
                ,array('$testres_patient_id'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getAllHospitalLocationsMap'
                ,array()
                ,array('core','dialog'));
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getAllergiesDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getChemHemLabs');
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getDiagnosticLabsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getEGFRDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getImagingTypesMap');
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
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVisits'
                ,array('$tid')
                ,array('core','oneorder')
                ,array('$testres_visits'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsDetailOnlyLatestMap');
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getVitalsSummaryMap');

        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('setPatientID'
                ,array('$testres_patient_id'));
        
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getProviders'
                ,array('""')
                ,array('core','dialog')
                );
        $callfunctions[] = $this->getOneCallFunctionDefForEhrDao('getEncounterStringFromVisit'
                ,array('$testres_visits[0]["visitTO"]')
                ,array('core','dialog'));
        
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
     * Returns associative array of performance results
     */
    public function getPerformanceDetails($ticketlist,$membership_filter=array('setup','oneorder'))
    {
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
            foreach($ticketlist as $tid)
            {
                $ticketstats[$tid] = array();
                $ticketstats[$tid]['error_count'] = 0;
                $ticketstats[$tid]['start_ts'] = microtime(TRUE);
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
error_log("LOOK about to call $methodname on ticket $tid");                            
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
                            $evalthis = "return {$oneparam};";
error_log("LOOK about to eval this for param [[[ $evalthis ]]]");                            
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
error_log("LOOK about to call $methodname on ticket $tid params=".print_r($params,TRUE));                            
                        if(count($params > 0))
                        {
                            if(count($params) == 1)
                            {
                                $callresult = $implclass->$methodname($params[0]);
                            } else {
                                $callresult = $implclass->$methodname($params);
                            }
                        } else {
                            $callresult = $implclass->$methodname();
                        }
                        if($store_result_varname != NULL)
                        {
                            $eval_assign = "{$store_result_varname} = " . '$callresult;';
error_log("LOOK 1 about to eval this assignment [[[ $eval_assign ]]]");                            
                            eval($eval_assign);
                            $evalthis = "return {$store_result_varname};";
error_log("LOOK 2 about to eval this as a test [[[ $evalthis ]]]");                            
                            $justlooking = eval($evalthis);
error_log("LOOK 3 about to eval this as a test>>> ".print_r($justlooking,TRUE)); 
if(is_array($justlooking))
{
    error_log("LOOK 4 is array get item " . print_r($justlooking[0]['visitTO'],TRUE));                            
}
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
            return $result;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting metrics",99876,$ex);
        }
    }
}
