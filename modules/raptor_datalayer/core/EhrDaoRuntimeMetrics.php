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
     * Returns the list of functions to call on each ticket
     */
    private function getFunctionsToCall()
    {
        //Return stuff we need to call for each ticket
        $callfunctions = array();
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'classname'=>'Context'
                    ,'methodname'=>'setSelectedTrackingID'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/Context.php'
                    ,'getinstanceliteral'=>'\raptor\Context::getInstance()'
                    ,'params'=>array('$tid')
                );
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>'getWorklistDetailsMap'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>array()
                );
        $callfunctions[] 
                = array('namespace'=>'raptor'
                    ,'classname'=>'EhrDao'
                    ,'methodname'=>'getDashboardDetailsMap'
                    ,'sourcemodule'=>'raptor_datalayer'
                    ,'sourcefile'=>'core/EhrDao.php'
                    ,'getinstanceliteral'=>'$this->m_oContext->getEhrDao()'
                    ,'params'=>array('$tid')
                );
        return $callfunctions;
    }
    
    public function debug()
    {
        $ticketlist = array();
        $ticketlist[] = '2010';
        $ticketlist[] = '2007';
        $ticketlist[] = '2005';
        $result = $this->getPerformanceDetails($ticketlist);
        drupal_set_message("LOOK RESULT ".print_r($result,TRUE));
    }
    
    /**
     * Returns associative array of performance results
     */
    public function getPerformanceDetails($ticketlist)
    {
        if(!is_array($ticketlist))
        {
            throw new \Exception('Must provide an input list of tickets to process!');
        }
        try
        {
            $functionstocall = $this->getFunctionsToCall();
            $result = array();
            $info = array();
            $info['description'] = "{$this->m_ehrDao}"; 
            $result['DAO'] = $info;
            $metrics = array();
            foreach($ticketlist as $tid)
            {
                //TODO --- Clear all caches here
                foreach($functionstocall as $details)
                {
                    $oneitem = array();
                    $oneitem['start_ts'] = microtime();
                    $oneitem['tracking_id'] = $tid;
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
                        $oneitem['failed'] = $ex;
                    }
                    $oneitem['end_ts'] = microtime();
                    $metrics[] = $oneitem;
                }
            }
            $result['metrics'] = $metrics;
            error_log("LOOK metrics details>>>".print_r($metrics,TRUE));
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
