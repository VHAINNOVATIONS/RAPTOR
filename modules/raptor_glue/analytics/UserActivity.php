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
    private $m_aUserInfo = NULL;
    
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
        
        $loaded = module_load_include('php', 'raptor_datalayer', 'core/data_user');
        $this->m_aUserInfo = UserInfo::getUserInfoMap();
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
            $curweekofyear = ceil($curdayofyear / 7);
            
            $dateparts['datetime'] = $wholedate_tx;
            $dateparts['onlydate'] = date('Y-m-d',$wholedate_ts);
            $dateparts['onlytime'] = date('H:i:s',$wholedate_ts);
            $dateparts['year'] = $rawparts['year'];
            $dateparts['month'] = $curmonth;
            $dateparts['qtr'] = $curqtr;
            $dateparts['week'] = $curweekofyear;
            $dateparts['dow'] = $rawparts['wday'];
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
                $addamount = 0;
            } else {
                $addamount = $addindurations[$name];
            }
            if(!key_exists($name, $existingdurations))
            {
                //Just assign it.
                $existingdurations[$name] = $addamount;
            } else {
                //Add to existing value
                $newvalue = $existingdurations[$name] + $addamount;
                $existingdurations[$name] = $newvalue;
            }
        }
        drupal_set_message("LOOK updateddurations for ".print_r($infokeys,TRUE).">>>".print_r($existingdurations,TRUE));
        return $existingdurations;
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
                $userdetails = array();
                $oneuserinfo = $this->m_aUserInfo[$uid];
                $userdetails['username'] = $oneuserinfo['username'];
                $userdetails['role_nm'] = $oneuserinfo['role_nm'];
                $userdetails['most_recent_login_dt'] = $oneuserinfo['most_recent_login_dt'];
                $usertickets = $uad['tickets'];
                foreach($usertickets as $ien=>$tad)
                {
                    if(!isset($tad['modality_abbr']))
                    {
                        //We don't have a selected modality yet.
                        $modality_abbr = '--';
                    } else {
                        $modality_abbr = $tad['modality_abbr'];
                    }
                    
                    if(isset($tad['transitions']))
                    {
                        foreach($tad['transitions'] as $wfs=>$relevantdate)
                        {
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
                                if(!isset($userdetails[$key]))
                                {
                                    $userdetails[$key] = array();
                                    $userdetails[$key]['modality_abbr'] = $modality_abbr;
                                    $userdetails[$key]['dateparts'] = $dateparts;
                                    $userdetails[$key]['durations'] = array();
                                }
                                if(!isset($userdetails[$key]['count_events']))
                                {
                                    $userdetails[$key]['count_events'] = array();
                                    $userdetails[$key]['count_events']['into_states'] = array();
                                }
                                if(!isset($userdetails[$key]['count_events']['into_states'][$wfs]))
                                {
                                    $newcount = 1; 
                                } else {
                                    $newcount = $userdetails[$key]['count_events']['into_states'][$wfs] + 1;
                                }
                                $userdetails[$key]['count_events']['into_states'][$wfs] = $newcount;
                                $existing = $userdetails[$key]['durations'];
                                $newdurations = $tad['durations'];
                                $userdetails[$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                            }
                        }
                    }
                    
                    //Schedule stuff
                    if(isset($tad['schedule_events']))
                    {
                        foreach($tad['schedule_events'] as $itemnum=>$relevantdate)
                        {
                            $durationkeys = array('scheduled_to_approved','approved_to_scheduled');
                            $dateparts = $this->getDateParts($relevantdate);

                            $year = $dateparts['year'];
                            $month = $dateparts['month'];
                            $dayinyear = $dateparts['doy'];
                            $dayinweek = $dateparts['dow'];
                            $key = "$modality_abbr:$year:$dayinyear";
                            if(!isset($userdetails[$key]))
                            {
                                $userdetails[$key] = array();
                                $userdetails[$key]['modality_abbr'] = $modality_abbr;
                                $userdetails[$key]['dateparts'] = $dateparts;
                                $userdetails[$key]['durations'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']))
                            {
                                $userdetails[$key]['count_events'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']['scheduled']))
                            {
                                $newcount = 1; 
                            } else {
                                $newcount = $userdetails[$key]['count_events']['scheduled'] + 1;
                            }
                            $userdetails[$key]['count_events']['scheduled'] = $newcount;
                            $existing = $userdetails[$key]['durations'];
                            $newdurations = $tad['durations'];
                            $userdetails[$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                        }
                    }
                    
                    //Collaboration target
                    if(isset($tad['collaboration_target_events']))
                    {
                        foreach($tad['collaboration_target_events'] as $itemnum=>$relevantdate)
                        {
                            $durationkeys = array('collaboration_target');
                            $dateparts = $this->getDateParts($relevantdate);

                            $year = $dateparts['year'];
                            $month = $dateparts['month'];
                            $dayinyear = $dateparts['doy'];
                            $dayinweek = $dateparts['dow'];
                            $key = "$modality_abbr:$year:$dayinyear";
                            if(!isset($userdetails[$key]))
                            {
                                $userdetails[$key] = array();
                                $userdetails[$key]['modality_abbr'] = $modality_abbr;
                                $userdetails[$key]['dateparts'] = $dateparts;
                                $userdetails[$key]['durations'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']))
                            {
                                $userdetails[$key]['count_events'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']['collaboration_target']))
                            {
                                $newcount = 1; 
                            } else {
                                $newcount = $userdetails[$key]['count_events']['collaboration_target'] + 1;
                            }
                            $userdetails[$key]['count_events']['collaboration_target'] = $newcount;
                            $existing = $userdetails[$key]['durations'];
                            $newdurations = $tad['durations'];
                            $userdetails[$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                        }
                    }

                    //Collaboration init
                    if(isset($tad['collaboration_init_events']))
                    {
                        foreach($tad['collaboration_init_events'] as $itemnum=>$relevantdate)
                        {
                            $durationkeys = array('collaboration_initiation');
                            $dateparts = $this->getDateParts($relevantdate);

                            $year = $dateparts['year'];
                            $month = $dateparts['month'];
                            $dayinyear = $dateparts['doy'];
                            $dayinweek = $dateparts['dow'];
                            $key = "$modality_abbr:$year:$dayinyear";
                            if(!isset($userdetails[$key]))
                            {
                                $userdetails[$key] = array();
                                $userdetails[$key]['modality_abbr'] = $modality_abbr;
                                $userdetails[$key]['dateparts'] = $dateparts;
                                $userdetails[$key]['durations'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']['collaboration_initiation']))
                            {
                                $newcount = 1; 
                            } else {
                                $newcount = $userdetails[$key]['count_events']['collaboration_initiation'] + 1;
                            }
                            $userdetails[$key]['count_events']['collaboration_initiation'] = $newcount;
                            $existing = $userdetails[$key]['durations'];
                            $newdurations = $tad['durations'];
                            $userdetails[$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                        }
                    }

                    //Reservations
                    if(isset($tad['reservation_events']))
                    {
                        foreach($tad['reservation_events'] as $itemnum=>$relevantdate)
                        {
                            $durationkeys = array('reserved');
                            $dateparts = $this->getDateParts($relevantdate);

                            $year = $dateparts['year'];
                            $month = $dateparts['month'];
                            $dayinyear = $dateparts['doy'];
                            $dayinweek = $dateparts['dow'];
                            $key = "$modality_abbr:$year:$dayinyear";
                            if(!isset($userdetails[$key]))
                            {
                                $userdetails[$key] = array();
                                $userdetails[$key]['modality_abbr'] = $modality_abbr;
                                $userdetails[$key]['dateparts'] = $dateparts;
                                $userdetails[$key]['durations'] = array();
                            }
                            if(!isset($userdetails[$key]['count_events']['reservation']))
                            {
                                $newcount = 1; 
                            } else {
                                $newcount = $userdetails[$key]['count_events']['reservation'] + 1;
                            }
                            $userdetails[$key]['count_events']['reservation'] = $newcount;
                            $existing = $userdetails[$key]['durations'];
                            $newdurations = $tad['durations'];
                            $userdetails[$key]['durations'] = $this->updateDurations($durationkeys,$existing,$newdurations);
                        }
                    }
                    
                }
                $activity[$uid] = $userdetails;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        $bundle = array();
        $bundle['summary'] = $summary;
        $bundle['activity'] = $activity;
        $bundle['debug_rawusers'] = $allusers;
        $bundle['debug_rawtickets'] = $tickets;
        return $bundle;
    }
}
