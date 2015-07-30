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
        return array('core','setup','oneorder');
    }
    
    /**
     * Returns the list of functions to call on each ticket
     */
    private function getFunctionsToCall($membership_filter)
    {
        //Return stuff we need to call for each ticket
        $callfunctions = array();
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'membership'=>array('core','setup')
                    ,'classname'=>'Context'
                    ,'methodname'=>'setSelectedTrackingID'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/Context.php'
                    ,'getinstanceliteral'=>'\raptor\Context::getInstance()'
                    ,'params'=>array('$tid')
                );
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'membership'=>array('core')
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>'getWorklistDetailsMap'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>array()
                );
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'membership'=>array('core','oneorder')
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>'getDashboardDetailsMap'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>array('$tid')
                );
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
