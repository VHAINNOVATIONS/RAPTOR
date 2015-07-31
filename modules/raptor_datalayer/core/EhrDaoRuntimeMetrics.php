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
    
    private function getOneCallFunctionBlockForEhrDao($methodname, $params=array(), $membership=array('core','oneorder'))
    {
        return array('namespace'=>'raptor'
                    ,'membership'=>$membership
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>$methodname
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>$params
                );
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
                );
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getImplementationInstance',array(),array('core','setup'));

        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('isAuthenticated',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('isProvider',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('userHasKeyOREMAS',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getUserSecurityKeys',array(),array('core','user'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getVistaAccountKeyProblems',array(),array('core','user'));
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getIntegrationInfo',array(),array('core'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getWorklistDetailsMap',array(),array('core'));
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getDashboardDetailsMap',array('$tid'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getPatientIDFromTrackingID',array('$tid'));
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getAllHospitalLocationsMap',array(),array('core','dialog'));
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getAllergiesDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getChemHemLabs');
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getDiagnosticLabsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getEGFRDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getImagingTypesMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getNotesDetailMap');
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getOrderOverviewMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getPathologyReportsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getPendingOrdersMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getProblemsListDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getProcedureLabsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getRadiologyCancellationReasons');

        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getRadiologyReportsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getRawVitalSignsMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getSurgeryReportsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getVisits');
        
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getVitalsDetailMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getVitalsDetailOnlyLatestMap');
        $callfunctions[] = $this->getOneCallFunctionBlockForEhrDao('getVitalsSummaryMap');

        
        
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
                        $getinstanceliteral = isset($details['getinstanceliteral']) ? $details['getinstanceliteral'] : NULL;
                        $params = array();
                        foreach($details['params'] as $oneparam)
                        {
                            $oneparamvalue = eval("return {$oneparam};");
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
