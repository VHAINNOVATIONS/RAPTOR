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

/**
 * This class is used to manage the ticket tracking information.
 *
 * @author Frank Font of SAN Business Consultants
 */
class TicketActivity 
{

    private $m_oWF = NULL;
    private $m_oTTD = NULL;
    
    function __construct()
    {
        $loaded = module_load_include('php', 'raptor_datalayer', 'core/data_user');
        $loaded = module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
        if(!$loaded)
        {
            $msg = 'Failed to load the Ticket Tracking Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oTTD = new \raptor\TicketTrackingData();
        $loaded = module_load_include('php', 'raptor_workflow', 'core/Transitions');
        if(!$loaded)
        {
            $msg = 'Failed to load the Workflow Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $this->m_oWF = new \raptor\Transitions();
    }    
    
    
    /**
     * Return all the ticket numbers and their current state.
     */
    public function getCoreTicketTrackingHistory($nSiteID, $startdatetime=NULL, $enddatetime=NULL)
    {
        $tickets = array();

        try
        {
            $modalitymap = $this->m_oTTD->getTicketModalityMap();
            
            //Ticket state completion dates
            $query_tt = db_select('raptor_ticket_tracking', 'n')
                ->fields('n')
                ->condition('siteid', $nSiteID,'=');
            if($startdatetime != NULL)
            {
                $query_tt->condition('updated_dt', $startdatetime, '>=');
            }
            if($enddatetime != NULL)
            {
                $query_tt->condition('updated_dt', $enddatetime, '<=');
            }
            $query_tt->orderBy('IEN');
            $query_tt->orderBy('updated_dt','DESC');
            $result_tt = $query_tt->execute();
            while($record = $result_tt->fetchAssoc())
            {
                $key = $record['IEN'];
                $tickets[$key] = $record;
                $mapkey = "TID$key";
                if(key_exists($mapkey, $modalitymap))
                {
                    $modalitydetails=$modalitymap[$mapkey];
                    $psn = $modalitydetails['protocol_shortname'];
                    $modality_abbr = $modalitydetails['modality_abbr'];
                    $tickets[$key]['modality_abbr'] = $modality_abbr;   
                    $tickets[$key]['protocol_shortname'] = $psn;   
                }
                if($record['workflow_state'] == NULL)
                {
                    $tickets[$key]['workflow_state'] = 'AC';   
                }
                $tickets[$key]['durations'] = array();
                if($record['approved_dt'] != NULL)
                {
                    $approved_ts = strtotime($record['approved_dt']);
                    $exam_completed_ts = NULL;
                    $interpret_completed_ts = NULL;
                    
                    $approved_to_acknowledged = 0;
                    $approved_to_examcompleted = 0;
                    $acknowledged_to_examcompleted = 0;
                    $approved_to_interpretcomplete = 0;
                    $examcompleted_to_QA = 0;
                    $approved_to_examvistacommit = 0;
                    
                    if($record['acknowledged_dt'] != NULL)
                    {
                        $acknowledged_ts = strtotime($record['acknowledged_dt']);
                        $approved_to_acknowledged = $acknowledged_ts - $approved_ts;
                    }
                    if($record['exam_completed_dt'] != NULL)
                    {
                        $exam_completed_ts = strtotime($record['exam_completed_dt']);
                        $approved_to_examcompleted = $exam_completed_ts - $approved_ts;
                    }
                    if($exam_completed_ts !== NULL && $record['acknowledged_dt'] != NULL)
                    {
                        $acknowledged_ts = strtotime($record['acknowledged_dt']);
                        $acknowledged_to_examcompleted = $exam_completed_ts - $acknowledged_ts;
                    }
                    if($exam_completed_ts !== NULL && $record['interpret_completed_dt'] != NULL)
                    {
                        $interpret_completed_ts = strtotime($record['interpret_completed_dt']);
                        $approved_to_interpretcomplete = $interpret_completed_ts - $exam_completed_ts;
                    }
                    if($exam_completed_ts !== NULL && $record['qa_completed_dt'] != NULL)
                    {
                        $qa_completed_ts = strtotime($record['qa_completed_dt']);
                        $examcompleted_to_QA = $qa_completed_ts - $exam_completed_ts;
                    }
                    if($approved_ts !== NULL && $record['exam_details_committed_dt'] != NULL)
                    {
                        $exam_details_committed_ts = strtotime($record['exam_details_committed_dt']);
                        $approved_to_examvistacommit = $exam_details_committed_ts - $approved_ts;
                    }
                    
                    $tickets[$key]['durations']['approved_to_examcompleted'] = $approved_to_examcompleted;
                    $tickets[$key]['durations']['approved_to_acknowledged'] = $approved_to_acknowledged;
                    $tickets[$key]['durations']['acknowledged_to_examcompleted'] = $acknowledged_to_examcompleted;
                    $tickets[$key]['durations']['approved_to_interpretcomplete'] = $approved_to_interpretcomplete;
                    $tickets[$key]['durations']['examcompleted_to_QA'] = $examcompleted_to_QA;
                    $tickets[$key]['durations']['approved_to_examvistacommit'] = $approved_to_examvistacommit;
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        
        return $tickets;
    }
    
    private function compilePartialCollaborationDetails(&$tickets, &$allusers, &$all_into_states
            , $nSiteID
            , $startdatetime=NULL, $enddatetime=NULL)
    {
        $bundle = array();
        $total_collaborations = 0;
        $total_reservations = 0;
        try
        {
            //Collaboration details
            $query_tc = db_select('raptor_ticket_collaboration', 'n')
                ->fields('n')
                ->condition('siteid', $nSiteID,'=');
            if($startdatetime != NULL)
            {
                $query_tc->condition('requested_dt', $startdatetime, '>=');
            }
            if($enddatetime != NULL)
            {
                $query_tc->condition('requested_dt', $enddatetime, '<=');
            }
            $query_tc->orderBy('IEN');
            $query_tc->orderBy('requested_dt');
            $collaborations = 0;
            $reservations = 0;
            $prevkey = NULL;
            $key = NULL;
            $recnum = -1;
            $result_tc = $query_tc->execute();
            while($record = $result_tc->fetchAssoc())
            {
                $recnum++;
                $key = $record['IEN'];
                $requested_dt = $record['requested_dt'];
                if($prevkey != $key)
                {
                    if($prevkey != NULL)
                    {
                        if(!isset($tickets[$prevkey]['summary']['counts']))
                        {
                            $tickets[$prevkey]['summary']['counts'] = array();
                        }
                        $tickets[$prevkey]['summary']['counts']['collaborations'] = $collaborations;
                        $tickets[$prevkey]['summary']['counts']['reservations'] = $reservations;

                        //Capture the duration of the last collaboration request if the ticket is NO longer in active state.
                        if($tickets[$prevkey]['summary']['workflow_state'] != 'AC')
                        {
                            //We have a real completion for collaboration, get it.
                            $competion_ts = strtotime($tickets[$prevkey]['summary']['approved_dt']);
                        } else {
                            //Still open so completion is always NOW!
                            $competion_ts = time();
                        }
                        $rightrecnum = $recnum - 1;
                        $rec_type = $tickets[$prevkey]['collaboration'][$rightrecnum]['rec_type'];
                        $started_ts = strtotime($tickets[$prevkey]['collaboration'][$rightrecnum]['requested_dt']);
                        $tickets[$prevkey]['collaboration'][$rightrecnum]["duration"] = $competion_ts - $started_ts;
                    }
                    $collaborations = 0;
                    $reservations = 0;
                    $recnum=0;
                    $prevkey = $key;
                }
                if(!key_exists($key,$tickets))
                {
                    $tickets[$key] = array();
                    $tickets[$key]['summary'] = array();
                }
                if($record['requester_uid'] == $record['collaborator_uid'])
                {
                    $collaborations++;
                    $total_reservations++;
                } else {
                    $reservations++;
                    $total_collaborations++;
                }
                $tickets[$key]['collaboration'][$recnum] = $record;
                if($recnum>0)
                {
                    //We can extract a duration for previous collaboration record
                    $prevrecnum = $recnum - 1;
                    $prevrec_ts = strtotime($tickets[$key]['collaboration'][$prevrecnum]['requested_dt']);
                    $this_ts =  strtotime($record['requested_dt']);
                    $tickets[$key]['collaboration'][$prevrecnum]['duration'] = $this_ts - $prevrec_ts;  //Duration between the changes
                }

                $uid = $record['requester_uid'];
                if(!isset($allusers[$uid]))
                {
                    $allusers[$uid] = array();
                }
                if(!isset($allusers[$uid]['tickets']))
                {
                    $allusers[$uid]['tickets'] = array();
                }
                if(!isset($allusers[$uid]['tickets'][$key]))
                {
                    $allusers[$uid]['tickets'][$key] = array();
                    $allusers[$uid]['tickets'][$key]['durations'] = array();
                    foreach($all_into_states as $wfs=>$ignore)
                    {
                        $allusers[$uid]['tickets'][$key]['count_events']['into_states'][$wfs] = 0;
                    }
                }
                if($record['requester_uid'] == $record['collaborator_uid'])
                {
                    //Reservation record
                    $tickets[$key]['collaboration'][$recnum]['rec_type'] = 'reservation';
                    if(isset($allusers[$uid]['tickets'][$key]['count_events']['reservation']))
                    {
                        $newcount = $allusers[$uid]['tickets'][$key]['count_events']['reservation'] + 1;
                    } else {
                        $newcount = 1;
                    }
                    $allusers[$uid]['tickets'][$key]['count_events']['reservation'] = $newcount;
                    $allusers[$uid]['tickets'][$key]['is_reserver'] = 'yes';
                    if(!isset($allusers[$uid]['tickets'][$key]['reservation_events']))
                    {
                        $allusers[$uid]['tickets'][$key]['reservation_events'] = array();
                    }
                    $allusers[$uid]['tickets'][$key]['reservation_events'][] = $requested_dt;
                } else {
                    //Collaboration record
                    $tickets[$key]['collaboration'][$recnum]['rec_type'] = 'collaboration';
                    if(isset($allusers[$uid]['tickets'][$key]['count_events']['collaboration']))
                    {
                        $newcount = $allusers[$uid]['tickets'][$key]['count_events']['collaboration'] + 1;
                    } else {
                        $newcount = 1;
                    }
                    $allusers[$uid]['tickets'][$key]['count_events']['collaboration'] = $newcount;
                    $allusers[$uid]['tickets'][$key]['is_collaboration_initiator'] = 'yes';
                    if(!isset($allusers[$uid]['tickets'][$key]['collaboration_init_events']))
                    {
                        $allusers[$uid]['tickets'][$key]['collaboration_init_events'] = array();
                    }
                    $allusers[$uid]['tickets'][$key]['collaboration_init_events'][] = $requested_dt;

                    //And the target too....
                    $collaborator_uid = $record['collaborator_uid'];
                    if(!isset($allusers[$collaborator_uid]['tickets'][$key]))
                    {
                        $allusers[$collaborator_uid]['tickets'][$key] = array();
                    }
                    $allusers[$collaborator_uid]['tickets'][$key]['is_collaboration_target'] = 'yes';
                    if(!isset($allusers[$collaborator_uid]['tickets'][$key]['collaboration_target_events']))
                    {
                        $allusers[$collaborator_uid]['tickets'][$key]['collaboration_target_events'] = array();
                    }
                    $allusers[$collaborator_uid]['tickets'][$key]['collaboration_target_events'][] = $requested_dt;
                }
            }  
            if($key != NULL)
            {
                if(!isset($tickets[$key]['summary']['counts']))
                {
                    $tickets[$key]['summary']['counts'] = array();
                }
                $tickets[$key]['summary']['counts']['collaborations'] = $collaborations;
                $tickets[$key]['summary']['counts']['reservations'] = $reservations;

                //Capture the duration of the last collaboration request if the ticket is NO longer in active state.
                if($tickets[$prevkey]['summary']['workflow_state'] != 'AC')
                {
                    //We have a real completion for collaboration, get it.
                    $competion_ts = strtotime($tickets[$key]['summary']['approved_dt']);
                } else {
                    //Still open so completion is always NOW!
                    $competion_ts = time();
                }
                $rec_type = $tickets[$key]['collaboration'][$recnum]['rec_type'];
                $started_ts = strtotime($tickets[$key]['collaboration'][$recnum]['requested_dt']);
                $tickets[$key]['collaboration'][$recnum]["duration"] = $competion_ts - $started_ts;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

        $bundle['total_collaborations'] = $total_collaborations;
        $bundle['total_reservations'] = $total_reservations;
        return $bundle;
    }
    
    private function compilePartialScheduleDetails(&$tickets, &$allusers, &$all_into_states
            , $nSiteID
            , $startdatetime=NULL, $enddatetime=NULL)
    {
        $bundle = array();
        $total_scheduled = 0;
        try
        {
            
            $query_st = db_select('raptor_schedule_track', 'n')
                ->fields('n')
                ->condition('siteid', $nSiteID,'=');
            if($startdatetime != NULL)
            {
                $query_st->condition('created_dt', $startdatetime, '>=');
            }
            if($enddatetime != NULL)
            {
                $query_st->condition('created_dt', $enddatetime, '<=');
            }
            $query_st->orderBy('IEN');
            $query_st->orderBy('created_dt');
            $scheduled = 0;
            $prevkey=NULL;
            $key=NULL;
            $recnum = -1;
            $result_st = $query_st->execute();    
            while($record = $result_st->fetchAssoc())
            {
                $recnum++;
                $key = $record['IEN'];
                $created_dt = $record['created_dt'];
                if($prevkey != $key)
                {
                    if($prevkey != NULL)
                    {
                        if(!isset($tickets[$prevkey]['summary']['counts']))
                        {
                            $tickets[$prevkey]['summary']['counts'] = array();
                        }
                        $tickets[$prevkey]['summary']['counts']['scheduled'] = $scheduled;
                    }
                    $scheduled = 0;
                    $prevkey = $key;
                    $recnum=0;
                }
                if(!key_exists($key,$tickets))
                {
                    $tickets[$key] = array();
                    $tickets[$key]['summary'] = array();
                }
                $scheduled++;
                $total_scheduled++;
                $tickets[$key]['schedule'][] = $record;

                $uid = $record['author_uid'];
                if(!isset($allusers[$uid]))
                {
                    $allusers[$uid] = array();
                }
                if(!isset($allusers[$uid]['tickets']))
                {
                    $allusers[$uid]['tickets'] = array();
                }
                if(!isset($allusers[$uid]['tickets']))
                {
                    $allusers[$uid]['tickets'] = array();
                }
                if(!isset($allusers[$uid]['tickets'][$key]))
                {
                    $allusers[$uid]['tickets'][$key] = array();
                    $allusers[$uid]['tickets'][$key]['durations'] = array();
                    foreach($all_into_states as $wfs=>$ignore)
                    {
                        $allusers[$uid]['tickets'][$key]['count_events']['into_states'][$wfs] = 0;
                    }
                }
                if(isset($allusers[$uid]['tickets'][$key]['count_events']['scheduled']))
                {
                    $newcount = $allusers[$uid]['tickets'][$key]['count_events']['scheduled'] + 1;
                } else {
                    $newcount = 1;
                }
                $allusers[$uid]['tickets'][$key]['count_events']['scheduled'] = $newcount;
                if(!isset($allusers[$uid]['tickets'][$key]['schedule_events']))
                {
                    $allusers[$uid]['tickets'][$key]['schedule_events'] = array();
                }
                $allusers[$uid]['tickets'][$key]['schedule_events'][] = $created_dt;
            }
            if($prevkey != NULL)
            {
                if(!isset($tickets[$prevkey]['summary']['counts']))
                {
                    $tickets[$prevkey]['summary']['counts'] = array();
                }
                $tickets[$prevkey]['summary']['counts']['scheduled'] = $scheduled;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

        $bundle['total_scheduled'] = $total_scheduled;
        return $bundle;
    }

    
    private function compilePartialVistaDetails(&$tickets, &$allusers, &$all_into_states
            , $nSiteID
            , $startdatetime=NULL, $enddatetime=NULL)
    {
        $bundle = array();
        $total_vistacommits = 0;
        try
        {
            $query_vista = db_select('raptor_ticket_commit_tracking', 'n')
                ->fields('n')
                ->condition('siteid', $nSiteID,'=');
            if($startdatetime != NULL)
            {
                $query_vista->condition('commit_dt', $startdatetime, '>=');
            }
            if($enddatetime != NULL)
            {
                $query_vista->condition('commit_dt', $enddatetime, '<=');
            }
            $query_vista->orderBy('IEN');
            $query_vista->orderBy('commit_dt');
            $result_vista = $query_vista->execute();    
            while($record = $result_vista->fetchAssoc())
            {
                $total_vistacommits++;
                $wfs = $record['workflow_state'];
                $key = $record['IEN'];
                if(!key_exists($key,$tickets))
                {
                    $tickets[$key] = array();;    
                }
                $vci = array();
                $vci['workflow_state'] = $record['workflow_state'];
                $vci['author_uid'] = $record['author_uid'];
                $vci['commit_dt'] = $record['commit_dt'];
                $tickets[$key]['vista_commit'][] = $vci;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

        $bundle['total_vistacommits'] = $total_vistacommits;
        return $bundle;
    }

    private function compilePartialStateTransitionDetails(&$tickets, &$allusers, &$all_into_states
            , $nSiteID
            , $startdatetime=NULL, $enddatetime=NULL)
    {
        $bundle = array();
        $total_statetransitions = 0;
        try
        {
            
            $query_wfh = db_select('raptor_ticket_workflow_history', 'n')
                ->fields('n')
                ->condition('siteid', $nSiteID,'=');
            if($startdatetime != NULL)
            {
                $query_wfh->condition('created_dt', $startdatetime, '>=');
            }
            if($enddatetime != NULL)
            {
                $query_wfh->condition('created_dt', $enddatetime, '<=');
            }
            $query_wfh->orderBy('IEN');
            $query_wfh->orderBy('created_dt');
            $result_wfh = $query_wfh->execute();    
            while($record = $result_wfh->fetchAssoc())
            {
                $total_statetransitions++;
                $wfs = $record['new_workflow_state'];
                $recorddate = $record['created_dt'];
                $key = $record['IEN'];
                if(!key_exists($key,$tickets))
                {
                    $tickets[$key] = array();;    
                }
                $tickets[$key]['transitions'][] = $record;
                if($wfs != 'AC')
                {
                    $all_into_states[$wfs] = $all_into_states[$wfs] + 1;
                }
                $uid = $record['initiating_uid'];
                if(!isset($allusers[$uid]))
                {
                    $allusers[$uid] = array();
                }
                if(!isset($allusers[$uid]['tickets']))
                {
                    $allusers[$uid]['tickets'] = array();
                }
                if(!isset($allusers[$uid]['tickets'][$key]))
                {
                    $allusers[$uid]['tickets'][$key] = array();
                    $allusers[$uid]['tickets'][$key]['durations'] = array();
                    $allusers[$uid]['tickets'][$key]['count_events'] = array();
                    $allusers[$uid]['tickets'][$key]['transitions'] = array();
                }
                if(!isset($allusers[$uid]['tickets'][$key]['count_events']['into_states']))
                {
                    $allusers[$uid]['tickets'][$key]['count_events']['into_states'] = array();
                    foreach($all_into_states as $localwfs=>$ignore)
                    {
                        $allusers[$uid]['tickets'][$key]['count_events']['into_states'][$localwfs] = 0;
                    }
                }
                if(!isset($allusers[$uid]['tickets'][$key]['transitions'][$wfs]))
                {
                    $allusers[$uid]['tickets'][$key]['transitions'][$wfs] = $recorddate;
                }

                if($wfs != 'AC')
                {
                    $newcount = $allusers[$uid]['tickets'][$key]['count_events']['into_states'][$wfs] + 1;
                    $allusers[$uid]['tickets'][$key]['count_events']['into_states'][$wfs] = $newcount;
                }
            }            
            
        } catch (\Exception $ex) {
            throw $ex;
        }

        $bundle['total_statetransitions'] = $total_statetransitions;
        return $bundle;
    }

    
    /**
     * Return all the ticket detail between the provided dates.
     */
    public function getDetailedTrackingHistory($nSiteID, $startdatetime=NULL, $enddatetime=NULL)
    {
        $bundle = array();
        $bundle['siteid'] = $nSiteID;
        $bundle['startdatetime'] = $startdatetime;
        $bundle['enddatetime'] = $enddatetime;
        
        $all_into_states = array();  //Count all tickets into each state
        foreach($this->m_oWF->getAllPossibleTicketStates() as $key=>$phrase)
        {
            if($key != 'AC')
            {
                $all_into_states[$key] = 0;  //Initialize
            }
        }
        $allusers = array();        //All users involved
        $tickets = array();
        $coreticketinfo = $this->getCoreTicketTrackingHistory($nSiteID, $startdatetime, $enddatetime);
        foreach($coreticketinfo as $key=>$oneticket)
        {
            $tickets[$key] = array();
            $tickets[$key]['summary'] = $oneticket;
        }
        
        try
        {
            //Factor in collaboration information
            $collabbundle = $this->compilePartialCollaborationDetails($tickets,$allusers,$all_into_states
                    , $nSiteID, $startdatetime, $enddatetime);
            $total_collaborations = $collabbundle['total_collaborations'];
            $total_reservations = $collabbundle['total_reservations'];
        
            //Factor in schedule details
            $schedulebundle = $this->compilePartialScheduleDetails($tickets,$allusers,$all_into_states
                    , $nSiteID, $startdatetime, $enddatetime);
            $total_scheduled = $schedulebundle['total_scheduled'];

            //Factor in commits to VISTA
            $vistabundle = $this->compilePartialVistaDetails($tickets,$allusers,$all_into_states
                    , $nSiteID, $startdatetime, $enddatetime);
            $total_vistacommits = $vistabundle['total_vistacommits'];
           
            //Factor in workflow STATE TRANSITIONS
            $transitionsbundle = $this->compilePartialStateTransitionDetails($tickets,$allusers,$all_into_states
                    , $nSiteID, $startdatetime, $enddatetime);
            $total_statetransitions = $transitionsbundle['total_statetransitions'];
            
            //Complete the summary entries for each ticket
            foreach($tickets as $ien=>$detail)
            {
                $ticket_approved_dt = $detail['summary']['approved_dt'];
                $exam_completed_dt = $detail['summary']['exam_completed_dt'];
                
                if(!isset($detail['summary']['workflow_state']))
                {
                    $detail['summary']['workflow_state'] = 'AC';
                }
                if(!isset($detail['summary']['counts']))
                {
                    $detail['summary']['counts'] = array();
                }
                if(!isset($detail['summary']['counts']['collaborations']))
                {
                    $detail['summary']['counts']['collaborations'] = 0;
                }
                if(!isset($detail['summary']['counts']['reservations']))
                {
                    $detail['summary']['counts']['reservations'] = 0;
                }
                if(!isset($detail['summary']['counts']['scheduled']))
                {
                    $detail['summary']['counts']['scheduled'] = 0;
                }
                
                //Compile the collaboration durations
                $collaboration_duration = 0;
                $reserved_duration = 0;
                if(isset($detail['collaboration']))
                {
                    foreach($detail['collaboration'] as $collabdetail)
                    {
                        $duration = $collabdetail['duration'];
                        if($collabdetail['rec_type'] == 'reservation')
                        {
                            $reserved_duration += $duration;
                        } else {
                            $collaboration_duration += $duration;
                        }
                    }
                }
                
                //Compile the scheduled durations
                $approved_to_scheduled = 0;
                $scheduled_to_approved = 0;
                if($ticket_approved_dt != NULL && isset($detail['schedule']))
                {
                    $scheduledetails = $detail['schedule'][0];  //First one IS the oldest one, thats what we want.
                    $sched_created_dt = $scheduledetails['created_dt'];
                    $sched_created_ts = strtotime($sched_created_dt);
                    $ticket_approved_ts = strtotime($ticket_approved_dt);
                    if($sched_created_ts > $ticket_approved_ts)
                    {
                        $approved_to_scheduled = $sched_created_ts - $ticket_approved_ts;
                    } else {
                        $scheduled_to_approved = $ticket_approved_ts - $sched_created_ts;
                    }
                }
                
                $detail['summary']['durations']['collaboration'] = $collaboration_duration;
                $detail['summary']['durations']['reserved'] = $reserved_duration;
                $detail['summary']['durations']['approved_to_scheduled'] = $approved_to_scheduled;
                $detail['summary']['durations']['scheduled_to_approved'] = $scheduled_to_approved;
                
                //Replace the detail
                $tickets[$ien] = $detail;
            }
            
            //Compute all the user level metrics
            foreach($allusers as $uid=>$details1)
            {
                foreach($tickets as $ien=>$ticketdetails)
                {
                    if(isset($allusers[$uid]['tickets'][$ien]))
                    {
                        //Prior logic already connected the user.
                        $user_participates = TRUE;
                    } else {
                        //Set to true if user touches ticket in some way we find here
                        $user_participates = FALSE; 
                    }
                    
                    $ticket_approved_dt = $ticketdetails['summary']['approved_dt'];
                    $exam_completed_dt = $ticketdetails['summary']['exam_completed_dt'];
                    $reserved = 0;
                    $collaboration_initiation = 0;
                    $collaboration_target = 0;
                    $approved_to_scheduled = 0;
                    $scheduled_to_approved = 0;
                    $approved_to_examcompleted = 0;
                    if(isset($ticketdetails['summary']['modality_abbr']))
                    {
                        $modality_abbr = $ticketdetails['summary']['modality_abbr'];
                        $psn = $ticketdetails['summary']['protocol_shortname'];   
                    } else {
                        $modality_abbr = NULL;
                        $psn = NULL;   
                    }
                    if($exam_completed_dt != NULL && $ticket_approved_dt != NULL)
                    {
                        $exam_completed_ts = strtotime($exam_completed_dt);
                        $ticket_approved_ts = strtotime($ticket_approved_dt);
                        $approved_to_examcompleted = $exam_completed_ts - $ticket_approved_ts;
                    }
                    $commitedinfo = array();
                    if(isset($ticketdetails['vista_commit']))
                    {
                        foreach($ticketdetails['vista_commit'] as $vista_commit)
                        {
                            if($vista_commit['author_uid'] == $uid)
                            {
                                //This user committed the info to VISTA
                                $vci = array();
                                $vci['workflow_state'] = $vista_commit['workflow_state'];
                                $vci['commit_dt'] = $vista_commit['commit_dt'];
                                $commitedinfo[] = $vci;
                            }
                        }
                    }
                    if(count($commitedinfo) > 0)
                    {
                        if(!isset($allusers[$uid]['tickets'][$ien]))
                        {
                            $allusers[$uid]['tickets'][$ien] = array();
                        }
                        $allusers[$uid]['tickets'][$ien]['vista_commit'] = $commitedinfo;
                    }
                    if(isset($ticketdetails['collaboration']))
                    {
                        //Compute the collaboration/reservation durations
                        foreach($ticketdetails['collaboration'] as $collabdetails)
                        {
                            $duration = $collabdetails['duration'];
                            if($collabdetails['rec_type'] == 'reservation')
                            {
                                //Reservation
                                if($uid == $collabdetails['requester_uid'])
                                {
                                    //Is requester
                                    $user_participates = TRUE;
                                    $reserved += $duration;
                                } 
                            } else {
                                //Collaboration
                                if($uid == $collabdetails['requester_uid'])
                                {
                                    //Is requester
                                    $user_participates = TRUE;
                                    $collaboration_initiation += $duration;
                                }  else
                                if($uid == $collabdetails['collaborator_uid'])
                                {
                                    //Is target
                                    $user_participates = TRUE;
                                    $collaboration_target += $duration;
                                } 
                            }
                            if($collabdetails['rec_type'] == 'reservation')
                            {
                                //Reservation
                                if($uid == $collabdetails['requester_uid'])
                                {
                                    //Is requester
                                    $user_participates = TRUE;
                                    $reserved += $duration;
                                } 
                            } else {
                                //Collaboration
                                if($uid == $collabdetails['requester_uid'])
                                {
                                    //Is requester
                                    $user_participates = TRUE;
                                    $collaboration_initiation += $duration;
                                }  else
                                if($uid == $collabdetails['collaborator_uid'])
                                {
                                    //Is target
                                    $user_participates = TRUE;
                                    $collaboration_target += $duration;
                                } 
                            }
                        }
                    }
                    if(isset($ticketdetails['schedule']))
                    {
                        //Compute the scheduled durations
                        foreach($ticketdetails['schedule'] as $scheduledetails)
                        {
                            if($ticket_approved_dt != NULL)
                            {
                                $sched_author_uid = $scheduledetails['author_uid'];
                                $sched_created_dt = $scheduledetails['created_dt'];
                                $ticket_approver_uid = NULL;
                                foreach($ticketdetails['transitions'] as $transitiondetail)
                                {
                                    if($transitiondetail['new_workflow_state'] == 'AP')
                                    {
                                        $ticket_approver_uid = $transitiondetail['initiating_uid'];
                                        break;
                                    }
                                }

                                $user_has_schedimpact = ($sched_author_uid == $uid || $ticket_approver_uid == $uid);
                                if($user_has_schedimpact)
                                {
                                    $user_participates = TRUE;
                                    $sched_created_ts = strtotime($sched_created_dt);
                                    if($ticket_approved_dt != NULL)
                                    {
                                        //Time is from approval until scheduled
                                        $ticket_approved_ts = strtotime($ticket_approved_dt);
                                        if($sched_created_ts > $ticket_approved_ts)
                                        {
                                            $approved_to_scheduled = $sched_created_ts - $ticket_approved_ts;
                                        } else {
                                            $scheduled_to_approved = $ticket_approved_ts - $sched_created_ts;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    //Only create the entry IF the user participates in factors.
                    if($user_participates)
                    {
                        $userlevelticketdurations = array();
                        $userlevelticketdurations['scheduled_to_approved'] = $scheduled_to_approved;
                        $userlevelticketdurations['approved_to_scheduled'] = $approved_to_scheduled;
                        $userlevelticketdurations['approved_to_examcompleted'] = $approved_to_examcompleted;
                        $userlevelticketdurations['collaboration_initiation'] = $collaboration_initiation;
                        $userlevelticketdurations['collaboration_target'] = $collaboration_target;
                        $userlevelticketdurations['reserved'] = $reserved;

                        //Add the durations to the structure.
                        if(!isset($allusers[$uid]['tickets'][$ien]))
                        {
                            $allusers[$uid]['tickets'][$ien] = array();
                        }
                        $allusers[$uid]['tickets'][$ien]['durations'] = $userlevelticketdurations;
                        
                        //Add metadata about the ticket
                        if($modality_abbr != NULL)
                        {
                            $allusers[$uid]['tickets'][$ien]['modality_abbr'] = $modality_abbr;
                            $allusers[$uid]['tickets'][$ien]['protocol_shortname'] = $psn;
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        $bundle['count_events'] = array();
        $bundle['count_events']['collaboration'] = $total_collaborations;
        $bundle['count_events']['reservations'] = $total_reservations;
        $bundle['count_events']['scheduled'] = $total_scheduled;
        $bundle['count_events']['vistacommits'] = $total_vistacommits;
        $bundle['count_events']['workflow_state_transitions'] = $total_statetransitions;
        $bundle['count_events']['into_state'] = array();
        foreach($all_into_states as $statekey=>$count)
        {
            $bundle['count_events']['into_state'][$statekey] = $count;
        }
        $bundle['relevant_users'] = $allusers;
        $bundle['tickets'] = $tickets;
        return $bundle;
    }
    
}
