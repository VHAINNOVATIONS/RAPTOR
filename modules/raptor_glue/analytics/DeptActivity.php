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
class DeptActivity 
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
    
    
    private function getDateParts($wholedate_tx)
    {
        $dateparts = array();
        try
        {
            $wholedate_ts = strtotime($wholedate_tx);
            
            $rawparts = getdate($wholedate_ts);

            $curmonth = $rawparts['mon'];
            $curqtr = ceil($curmonth/3);
            $curdayofyear = $rawparts['yday'];
            $curweekofyear = date('W',$wholedate_ts);
            $curdayofweek_num = date('N',$wholedate_ts);
            $curdayofweek_tx = date('l',$wholedate_ts);
            
            $dateparts['datetime'] = $wholedate_tx;
            $dateparts['onlydate'] = date('Y-m-d',$wholedate_ts);
            $dateparts['onlytime'] = date('H:i:s',$wholedate_ts);
            $dateparts['year'] = $rawparts['year'];
            $dateparts['month'] = $curmonth;
            $dateparts['qtr'] = $curqtr;
            $dateparts['week'] = $curweekofyear;
            $dateparts['dow'] = $curdayofweek_num;
            $dateparts['dow_tx'] = $curdayofweek_tx;
            $dateparts['doy'] = $curdayofyear;
        } catch (\Exception $ex) {
            throw new \Exception("Failed to parse wholedate [$wholedate_tx]!",99654,$ex);
        }
        return $dateparts;
    }
    
    private function updateDurations($infokeys,$existingdurations,$addindurations)
    {
        foreach($infokeys as $name)
        {
            if(!key_exists($name, $addindurations))
            {
                $addamount = NULL;
            } else {
                $addamount = $addindurations[$name];
            }
            //Only factor it in IF there was really an event to factor in!
            if($addamount != NULL)
            {
                if(!key_exists($name, $existingdurations))
                {
                    //Just assign it.
                    $existingdurations[$name] = $addamount;
                    $existingdurations["max_$name"] = $addamount;
                    $existingdurations["min_$name"] = $addamount;
                    $existingdurations["avg_$name"] = $addamount;
                    $existingdurations["samplesize_avg_$name"] = 1;
                    //$existingdurations["DEBUG {$name}_1"] = $addamount;
                } else {
                    //Add to existing value
                    if($addamount > $existingdurations["max_$name"])
                    {
                        $existingdurations["max_$name"] = $addamount;
                    }
                    if($addamount < $existingdurations["min_$name"])
                    {
                        $existingdurations["min_$name"] = $addamount;
                    }
                    $samplesize = $existingdurations["samplesize_avg_$name"] + 1;
                    $newvalue = $existingdurations[$name] + $addamount;
                    $existingdurations[$name] = $newvalue;
                    //$existingdurations["DEBUG {$name}_{$samplesize}"] = $addamount;
                    $existingdurations["samplesize_avg_$name"] = $samplesize;
                    $newavg = $newvalue / $samplesize;
                    $existingdurations["avg_$name"] = $newavg;
                }
            }
        }
        return $existingdurations;
    }
    
    /**
     * Row key is MODALITY, USER, and DAY in that order.
     */
    public function getActivityByModalityAndDay($nSiteID, $startdatetime=NULL, $enddatetime=NULL)
    {
        $summary = array();
        $activity = array();
        try
        {
            $aDTH = $this->m_oTA->getDetailedTrackingHistory($nSiteID, $startdatetime, $enddatetime);
            $tickets = $aDTH['tickets'];            
            $summary = $aDTH['count_events'];
            foreach($tickets as $ien=>$tad)
            {
                if(!isset($tad['summary']['modality_abbr']))
                {
                    //We don't have a selected modality yet.
                    $modality_abbr = '--';
                } else {
                    $modality_abbr = $tad['summary']['modality_abbr'];
                }

                //Get stats for ticket state transitions
                if(isset($tad['transitions']))
                {
                    foreach($tad['transitions'] as $offset=>$onetransition)
                    {
                        $wfs = $onetransition['new_workflow_state'];
                        $relevantdate = $onetransition['created_dt'];
                        $durationkeys = array();
                        if($wfs == 'AP' || $wfs == 'EC')
                        {
                            $durationkeys['approved_to_examcompleted'] = 'approved_to_examcompleted';
                        } 
                        if($wfs == 'AP' || $wfs == 'PA')
                        {
                            $durationkeys['approved_to_acknowledged'] = 'approved_to_acknowledged';
                        } 
                        if($wfs == 'PA' || $wfs == 'EC')
                        {
                            $durationkeys['acknowledged_to_examcompleted'] = 'acknowledged_to_examcompleted';
                        }
                        if(count($durationkeys) > 0)
                        {
                            $dateparts = $this->getDateParts($relevantdate);

                            $year = $dateparts['year'];
                            $month = $dateparts['month'];
                            $dayinyear = $dateparts['doy'];
                            $dayinweek = $dateparts['dow'];
                            $key = "$modality_abbr:$year:$dayinyear";
                            if(!isset($activity['rowdetail'][$key]))
                            {
                                $activity['rowdetail'][$key] = array();
                                $activity['rowdetail'][$key]['modality_abbr'] = $modality_abbr;
                                $activity['rowdetail'][$key]['dateparts'] = $dateparts;
                                $activity['rowdetail'][$key]['durations'] = array();
                            }
                            if(!isset($activity['rowdetail'][$key]['count_events']))
                            {
                                $activity['rowdetail'][$key]['count_events'] = array();
                                $activity['rowdetail'][$key]['count_events']['into_states'] = array();
                            }
                            if(!isset($activity['rowdetail'][$key]['count_events']['into_states'][$wfs]))
                            {
                                $newcount = 1; 
                            } else {
                                $newcount = $activity['rowdetail'][$key]['count_events']['into_states'][$wfs] + 1;
                            }
                            $activity['rowdetail'][$key]['count_events']['into_states'][$wfs] = $newcount;
                            $existing = $activity['rowdetail'][$key]['durations'];
                            $newdurations = $tad['summary']['durations'];
                            $activity['rowdetail'][$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                        }
                    }
                }

                //Schedule stuff
                if(isset($tad['schedule']))
                {
                    foreach($tad['schedule'] as $itemnum=>$oneschedule)
                    {
                        $relevantdate = $oneschedule['created_dt'];
                        $duration = NULL;
                        $alldurationkeys = array('scheduled_to_approved','approved_to_scheduled');
                        foreach($alldurationkeys as $itemname)
                        {
                            if(isset($tad['summary']['durations'][$itemname]))
                            {
                                $duration = $tad['summary']['durations'][$itemname];
                            } else {
                                $itemname = 'approved_to_scheduled';
                                if(isset($tad['summary']['durations'][$itemname]))
                                {
                                    $duration = $tad['summary']['durations'][$itemname];
                                }
                            }
                            if($duration != NULL)
                            {
                                $newdurations = array($itemname => $duration);
                                $durationkeys = array($itemname);

                                //Get all the key parts and add the durations
                                $dateparts = $this->getDateParts($relevantdate);
                                $year = $dateparts['year'];
                                $month = $dateparts['month'];
                                $dayinyear = $dateparts['doy'];
                                $dayinweek = $dateparts['dow'];
                                $key = "$modality_abbr:$year:$dayinyear";
                                if(!isset($activity['rowdetail'][$key]))
                                {
                                    $activity['rowdetail'][$key] = array();
                                    $activity['rowdetail'][$key]['modality_abbr'] = $modality_abbr;
                                    $activity['rowdetail'][$key]['dateparts'] = $dateparts;
                                    $activity['rowdetail'][$key]['durations'] = array();
                                }
                                if(!isset($activity['rowdetail'][$key]['count_events']))
                                {
                                    $activity['rowdetail'][$key]['count_events'] = array();
                                }
                                if(!isset($activity['rowdetail'][$key]['count_events']['scheduled']))
                                {
                                    $newcount = 1; 
                                } else {
                                    $newcount = $activity['rowdetail'][$key]['count_events']['scheduled'] + 1;
                                }
                                $activity['rowdetail'][$key]['count_events']['scheduled'] = $newcount;
                                $existing = $activity['rowdetail'][$key]['durations'];
                                $activity['rowdetail'][$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                            }
                        }
                    }
                }

                //Collaboration
                if(isset($tad['collaboration']))
                {
                    foreach($tad['collaboration'] as $itemnum=>$onecollaboration)
                    {
                        $relevantdate = $onecollaboration['requested_dt'];
                        $requester_uid = $onecollaboration['requester_uid'];
                        $collaborator_uid = $onecollaboration['collaborator_uid'];
                        $duration = $onecollaboration['duration'];
                        $rec_type = $onecollaboration['rec_type'];
                        if($requester_uid != $collaborator_uid)
                        {
                            //This is a collaboration initiation
                            $itemname = 'collaboration_initiation';
                        } else {
                            $itemname = 'reserved';
                        }    
                        $newdurations = array($itemname => $duration);
                        $durationkeys = array($itemname);

                        //Get all the key parts and add the durations
                        $dateparts = $this->getDateParts($relevantdate);
                        $year = $dateparts['year'];
                        $month = $dateparts['month'];
                        $dayinyear = $dateparts['doy'];
                        $dayinweek = $dateparts['dow'];
                        $key = "$modality_abbr:$year:$dayinyear";
                        if(!isset($activity['rowdetail'][$key]))
                        {
                            $activity['rowdetail'][$key] = array();
                            $activity['rowdetail'][$key]['modality_abbr'] = $modality_abbr;
                            $activity['rowdetail'][$key]['dateparts'] = $dateparts;
                            $activity['rowdetail'][$key]['durations'] = array();
                        }
                        if(!isset($activity['rowdetail'][$key]['count_events']))
                        {
                            $activity['rowdetail'][$key]['count_events'] = array();
                        }
                        if(!isset($activity['rowdetail'][$key]['count_events'][$itemname]))
                        {
                            $newcount = 1; 
                        } else {
                            $newcount = $activity['rowdetail'][$key]['count_events'][$itemname] + 1;
                        }
                        $activity['rowdetail'][$key]['count_events'][$itemname] = $newcount;
                        $existing = $activity['rowdetail'][$key]['durations'];
                        $activity['rowdetail'][$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        $bundle = array();
        $bundle['summary'] = $summary;
        $bundle['dept_activity'] = $activity;
        //$bundle['debug_rawusers'] = $allusers;
        $bundle['debug_rawtickets'] = $tickets;
        return $bundle;
    }
}
