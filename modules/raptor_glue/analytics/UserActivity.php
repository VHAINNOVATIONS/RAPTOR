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

require_once 'TicketActivity.php';

/**
 * This class is used to manage the ticket tracking information.
 *
 * @author Frank Font of SAN Business Consultants
 */
class UserActivity 
{

    private $m_oWF = NULL;
    private $m_oTA = NULL;
    
    function __construct()
    {
        $this->m_oTA = new \raptor\TicketActivity();
        $loaded = module_load_include('php', 'raptor_workflow', 'core/Transitions');
        if(!$loaded)
        {
            $msg = 'Failed to load the Workflow Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oWF = new \raptor\Transitions();
    }    
    
    /**
     * Row key is MODALITY, USER, and DAY in that order.
     */
    public function getActivityByModalityAndDay($nSiteID, $startdatetime=NULL, $enddatetime=NULL)
    {
        $activity = array();
        $summary = array();
        try
        {
            $aDTH = $this->m_oTA->getDetailedTrackingHistory($nSiteID, $startdatetime, $enddatetime);
            $allusers = $aDTH['relevant_users'];
            $tickets = $aDTH['tickets'];            
            $summary = $aDTH['count_events'];
            foreach($allusers as $uid=>$uad)
            {
                //TODO
                $activity[$uid] = $uad;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        $bundle = array();
        $bundle['summary'] = $summary;
        $bundle['activity'] = $activity;
        return $bundle;
    }
}
