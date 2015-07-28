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
class GetDaoRuntimeMetrics
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
            throw new \Exception("Failed constructor GetDaoRuntimeMetrics because $ex",99876,$ex);
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
                    ,'params'=>array('$tid')
                );
        return array($callfunctions);
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
                foreach($functionstocall as $key=>$details)
                {
                    drupal_set_message("LOOK $key=>$details".print_r($details,TRUE));
                    $oneitem = array();
                    $oneitem['start_ts'] = microtime();
                    try
                    {
                        $oneitem['metadata'] = $details;
                        $namespace = $details['namespace'];
                        $classname = $details['classname'];
                        $methodname = $details['methodname'];
                        $params = array();
                        foreach($details['params'] as $oneparam)
                        {
                            $oneparamvalue = eval($oneparam);
                            $params[] = $oneparamvalue;
                        }
                        $oneitem['paramvalues'] = $params;
                        $class = "\\$namespace\\$classname";
                        $implclass = new $class();
                        if(count($params > 0))
                        {
                            $callresult = $implclass->$methodname($params);
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
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
