<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * MDWS Integration and VISTA collaboration: Joel Mewton
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
 */ 

namespace raptor_mdwsvista;

/**
 * This class provides methods to return filtered lists of rows for the worklist
 * and also a single worklist row when given a matching ID.
 *
 * @author SAN
 */
class WorklistData 
{
    private $m_oContext;
    
    //Worklist Vista Field Order
    const WLVFO_PatientID             = 1;
    const WLVFO_PatientName           = 2;
    const WLVFO_ExamCategory          = 3;
    const WLVFO_RequestingPhysician   = 4;
    const WLVFO_OrderedDate           = 5;
    const WLVFO_Procedure             = 6;
    const WLVFO_ImageType             = 7;
    const WLVFO_ExamLocation          = 8;
    const WLVFO_Urgency               = 9;
    const WLVFO_Nature                = 10;
    const WLVFO_Transport             = 11;
    const WLVFO_DesiredDate           = 12;
    const WLVFO_OrderFileIen           = 13;
    const WLVFO_RadOrderStatus           = 14;
    
    
    function __construct($oContext)
    {
        if($oContext == NULL)
        {
            throw new \Exception("Cannot get instance of Worklist without context!");
        }
        
        module_load_include('php', 'raptor_datalayer', 'core/WorklistColumnMap');
        module_load_include('php', 'raptor_glue', 'core/config');
        module_load_include('php', 'raptor_formulas', 'core/MatchOrderToUser');
        module_load_include('php', 'raptor_formulas', 'core/LanguageInference');

        $this->m_oContext = $oContext;
    }

    /**
     * Convert the code into a word
     * @param type $sWMODE
     * @return string the word associated with the code
     */
    static function getWorklistModeName($sWMODE)
    {
        if($sWMODE=='P'){
            $sName="Protocol";
        } elseif ($sWMODE=='E'){
            $sName="Examination";
        } elseif ($sWMODE=='I'){
            $sName="Interpretation";
        } elseif ($sWMODE=='Q'){
            $sName="QA";
        } else {
            die("Invalid WorklistMode='$sWMODE'!!!");
        }
        return $sName;
    }
    

    // simply create a "dictionary" organized by key field IEN
    private function parseSqlTicketTracking($sqlResult) {
        $result = array();
        
        if ($sqlResult->rowCount() === 0) {
            return $result;
        }
        
        foreach ($sqlResult as $row) {
            $key = $row->IEN;
            if(!isset($row->workflow_state) || $row->workflow_state == NULL)
            {
                $row->workflow_state = 'AC';    //Because NULL means AC.
            }
            $result[$key] = $row;
        }

        return $result;
    }
    
    private function getWorklistTrackingFromSQL() 
    {
        try
        {
            $sql = "SELECT * FROM raptor_ticket_tracking";
            $sqlResult = db_query($sql);
            $ticketTrackingResult = $this->parseSqlTicketTracking($sqlResult);

            //$sql = "SELECT * FROM raptor_ticket_collaboration WHERE active_yn";
            $sql = "SELECT c.IEN, c.collaborator_uid, c.requester_notes_tx, c.requested_dt, u.username, u.usernametitle, u.firstname, u.lastname, u.suffix FROM raptor_ticket_collaboration c left join raptor_user_profile u on c.collaborator_uid=u.uid WHERE active_yn";
            $sqlResult = db_query($sql);
            $ticketCollaborationResult = $this->parseSqlTicketTracking($sqlResult);

            $sql = "SELECT * FROM raptor_schedule_track";
            $sqlResult = db_query($sql);
            $scheduleTrackResult = $this->parseSqlTicketTracking($sqlResult);

            return array(
                "raptor_ticket_tracking" => $ticketTrackingResult,
                "raptor_ticket_collaboration" => $ticketCollaborationResult,
                "raptor_schedule_track" => $scheduleTrackResult);
        } catch (\Exception $ex) {
            error_log("FAILED getWorklistTrackingFromSQL ".$ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Gets each row of the worklist.
     * @return array with following keys ...
     *  'all_rows'              = array with integer index of all the worklist rows
     *  'pending_orders_map'   = array with patient id key and count of orders for that patient
     *  'matching_offset'       = offset of the row matching the match IEN
     */
    private function parseWorklist($MDWSResponse, $ticketTrackingDict, $match_IEN=NULL)
    {
        $sThisResultName = 'parseWorklist';
        $aPatientPendingOrderCount = array();
        $aPatientPendingOrderMap = array();
        $nOffsetMatchIEN = NULL;
        
        $ls=0;
                
        $oUserInfo = $this->m_oContext->getUserInfo();
        
        $match_order_to_user = new \raptor_formulas\MatchOrderToUser($oUserInfo);
        $language_infer = new \raptor_formulas\LanguageInference();
        
        $worklist = array();
        $numOrders = isset($MDWSResponse->ddrListerResult) ? $MDWSResponse->ddrListerResult->count : 0;
        if($numOrders == 0)
        {
            if(is_object($MDWSResponse))
            {
                if(isset($MDWSResponse->ddrListerResult))
                {
                    $showinfo = print_r($MDWSResponse->ddrListerResult,TRUE);
                } else {
                    $showinfo = '(No DDRLister results)';
                }
            } else {
                $showinfo = '(Non-object DDRLister Result)';
            }
            error_log("DID NOT FIND ANY DATA IN MDWS!  MDWSResponse Details START...\n" 
                    . print_r($MDWSResponse, TRUE) 
                    . "\n...MDWSResponse Details END!\nMDWSResponse->ddrListerResult Details Start...\n" 
                    . $showinfo . "\nMDWSResponse->ddrListerResult Details END...");
            return false;
        }
        $strings = isset($MDWSResponse->ddrListerResult->text->string) ? $MDWSResponse->ddrListerResult->text->string : array();
                
        $exploded = array();
        $t = array();
        $nFound=0;
        
        $ticketTrackingRslt = $ticketTrackingDict['raptor_ticket_tracking'];
        $ticketCollabRslt = $ticketTrackingDict['raptor_ticket_collaboration'];
        $scheduleTrackRslt = $ticketTrackingDict['raptor_schedule_track'];
        
        for ($i=0; $i<$numOrders; $i++)
        {
            $exploded = explode("^", $strings[$i]);
            
            $ienKey = $exploded[0];
            $sqlTicketTrackRow = array_key_exists($ienKey, $ticketTrackingRslt) ? $ticketTrackingRslt[$ienKey] : null; // use IEN from MDWS results as key
            $sqlTicketCollaborationRow = array_key_exists($ienKey, $ticketCollabRslt) ? $ticketCollabRslt[$ienKey] : null; // use IEN from MDWS results as key
            $sqlScheduleTrackRow = array_key_exists($ienKey, $scheduleTrackRslt) ? $scheduleTrackRslt[$ienKey] : null; // use IEN from MDWS results as key

            $patientID = $exploded[WorklistData::WLVFO_PatientID];
            $t[\raptor\WorklistColumnMap::WLIDX_TRACKINGID]  = $exploded[0];
            $t[\raptor\WorklistColumnMap::WLIDX_PATIENTID]   = $patientID; 
            $t[\raptor\WorklistColumnMap::WLIDX_PATIENTNAME] = $this->formatPatientName($exploded[WorklistData::WLVFO_PatientName]);
            $desired_date_raw = $exploded[WorklistData::WLVFO_DesiredDate];
            $t[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED] = $desired_date_raw;
            if(strpos($desired_date_raw, '@') !== FALSE)
            {
                $desired_date_parts=explode('@',$desired_date_raw);
                $desired_date_justdate=$desired_date_parts[0];
                $desired_date_justtime=$desired_date_parts[1];
                $desired_date_timestamp = strtotime($desired_date_justdate);  
                $desired_date_iso8601 = date('Y-m-d ',$desired_date_timestamp) . $desired_date_justtime;
            } else {
                $desired_date_justdate=$desired_date_raw;
                $desired_date_timestamp = strtotime($desired_date_justdate);  
                $desired_date_iso8601 = date('Y-m-d',$desired_date_timestamp);
            }
            $t[\raptor\WorklistColumnMap::WLIDX_ISO8601_DATETIMEDESIRED] = $desired_date_iso8601;
            
            if($exploded[WorklistData::WLVFO_OrderedDate] !== '' && ($last = strrpos($exploded[WorklistData::WLVFO_OrderedDate], ':')) !== FALSE)
            {
                //Remove the seconds from the time.
                $dateordered_raw = substr($exploded[WorklistData::WLVFO_OrderedDate], 0, $last);
            } else {
                //Assume there is no time portion.
                $dateordered_raw = $exploded[WorklistData::WLVFO_OrderedDate];
            }
            $t[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]     = $dateordered_raw;
            if(strpos($dateordered_raw, '@') !== FALSE)
            {
                $dateordered_parts=explode('@',$dateordered_raw);
                $dateordered_justdate=$dateordered_parts[0];
                $dateordered_justtime=$dateordered_parts[1];
                $dateordered_timestamp = strtotime($dateordered_justdate);  
                $dateordered_iso8601 = date('Y-m-d ',$dateordered_timestamp) . ' ' . $dateordered_justtime;
            } else {
                $dateordered_justdate=$dateordered_raw;
                $dateordered_timestamp = strtotime($dateordered_justdate);  
                $dateordered_iso8601 = date('Y-m-d',$dateordered_timestamp);
            }
            $t[\raptor\WorklistColumnMap::WLIDX_ISO8601_DATEORDERED] = $dateordered_iso8601;
            
            $t[\raptor\WorklistColumnMap::WLIDX_STUDY]       = $exploded[WorklistData::WLVFO_Procedure];
            $t[\raptor\WorklistColumnMap::WLIDX_URGENCY]     = $exploded[WorklistData::WLVFO_Urgency]; 
            switch(trim($exploded[WorklistData::WLVFO_Transport]))
            {
                case "a":
                    $t[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = "AMBULATORY";
                    break;
                case "p":
                    $t[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = "PORTABLE";
                    break;
                case "s":
                    $t[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = "STRETCHER";
                    break;
                case "w":
                    $t[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = "WHEEL CHAIR";
                    break;
                default:
                    $t[\raptor\WorklistColumnMap::WLIDX_TRANSPORT] = $exploded[WorklistData::WLVFO_Transport];
                    break;
            }
            $t[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION]     = $exploded[WorklistData::WLVFO_ExamCategory];
            $t[\raptor\WorklistColumnMap::WLIDX_ANATOMYIMAGESUBSPEC]         = 'TODO ANATOMY';   //Placeholder for anatomy keywords
            $t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS]              = (isset($sqlTicketTrackRow) ? $sqlTicketTrackRow->workflow_state : "AC"); // default to "AC"

            //Only show an assignment if ticket has not yet moved downstream in the workflow.
            if($t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'AC' 
                    || $t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'CO' || $t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'RV')
            {
                $t[\raptor\WorklistColumnMap::WLIDX_ASSIGNEDUSER] = (isset($sqlTicketCollaborationRow) ? array(
                                                          'uid'=>$sqlTicketCollaborationRow->collaborator_uid
                                                        , 'requester_notes_tx'=>$sqlTicketCollaborationRow->requester_notes_tx
                                                        , 'requested_dt'=>$sqlTicketCollaborationRow->requested_dt
                                                        , 'username'=>$sqlTicketCollaborationRow->username
                                                        , 'fullname'=>trim($sqlTicketCollaborationRow->usernametitle . ' ' .$sqlTicketCollaborationRow->firstname
                                                                . ' ' .$sqlTicketCollaborationRow->lastname. ' ' .$sqlTicketCollaborationRow->suffix )
                                                    ) : NULL);    
            } else {
                $t[\raptor\WorklistColumnMap::WLIDX_ASSIGNEDUSER] = '';
            }

            $t[\raptor\WorklistColumnMap::WLIDX_ORDERSTATUS]  = '?ORDER STATUS?';   //Placeholder for Order Status
                    
            $t[\raptor\WorklistColumnMap::WLIDX_EDITINGUSER]      = '';   //Placeholder for UID of user that is currently editing the record, if any. (check local database)

            $t[\raptor\WorklistColumnMap::WLIDX_EXAMLOCATION] = $exploded[WorklistData::WLVFO_ExamLocation];
            $t[\raptor\WorklistColumnMap::WLIDX_REQUESTINGPHYSICIAN] = $exploded[WorklistData::WLVFO_RequestingPhysician];
            $rfs = trim($exploded[WorklistData::WLVFO_Nature]);
            switch ($rfs)
            {
                case 'w' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "WRITTEN";
                    break;
                case 'v' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "VERBAL";
                    break;
                case 'p' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "TELEPHONED";
                    break;
                case 's' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "SERVICE CORRECTION";
                    break;
                case 'i' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "POLICY";
                    break;
                case 'e' :
                    $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "PHYSICIAN ENTERED";
                    break;
                default :
                    if(strlen($rfs)==0)
                    {
                        $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = "NOT ENTERED";
                    } else {
                        $t[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY] = $rfs;
                    }
                    break;
            }
                    
            // Pull schedule from raptor_schedule_track
            if($sqlScheduleTrackRow != null)
            {
                //If a record exists, then there is something to see.
                $showText = '';
                if(isset($sqlScheduleTrackRow->scheduled_dt))
                {
                    $phpdate = strtotime( $sqlScheduleTrackRow->scheduled_dt );
                    $sdt = date( 'Y-m-d H:i', $phpdate ); //Remove the seconds
                    if(isset($sqlScheduleTrackRow->confirmed_by_patient_dt))
                    {
                        if($showText > '')
                        {
                           $showText .= '<br>'; 
                        }
                        $showText .= 'Confirmed '.$sqlScheduleTrackRow->confirmed_by_patient_dt; 
                    }
                    if($showText > '')
                    {
                       $showText .= '<br>'; 
                    }
                    $showText .= 'For '. $sdt ;//$sqlScheduleTrackRow->scheduled_dt; 
                    if(isset($sqlScheduleTrackRow->location_tx))
                    {
                        if($showText > '')
                        {
                           $showText .= '<br>'; 
                        }
                        $showText .= 'In ' . $sqlScheduleTrackRow->location_tx; 
                    }
                }
                if(isset($sqlScheduleTrackRow->canceled_dt))
                {
                    //If we are here, clear everything before.
                    $showText = 'Cancel requested '.$sqlScheduleTrackRow->canceled_dt; 
                }
                if(trim($sqlScheduleTrackRow->notes_tx) > '')
                {
                    if($showText > '')
                    {
                       $showText .= '<br>'; 
                    }
                    $showText .= 'See Notes...'; 
                }
                if($showText == '')
                {
                    //Indicate there is someting to see in the form.
                    $showText = 'See details...';
                }
                $t[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO] = array(
                    'EventDT' => $sqlScheduleTrackRow->scheduled_dt,
                    'LocationTx' => $sqlScheduleTrackRow->location_tx,
                    'ConfirmedDT' => $sqlScheduleTrackRow->confirmed_by_patient_dt,
                    'CanceledDT' => $sqlScheduleTrackRow->canceled_dt,
                    'ShowTx' => $showText
                );
                print_r($sqlScheduleTrackRow, TRUE);
            } else {
                //No record exists yet.
                $t[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO] = array(
                    'EventDT' => NULL,
                    'LocationTx' => NULL,
                    'ConfirmedDT' => NULL,
                    'CanceledDT' => NULL,
                    'ShowTx' => 'Unknown'
                );
            }

            $t[\raptor\WorklistColumnMap::WLIDX_CPRSCODE]    = '';   //Placeholder for the CPRS code associated with this ticket
            $t[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE]   = $exploded[WorklistData::WLVFO_ImageType];   //Placeholder for Imaging Type - file 75.1, field 3
            $modality = $language_infer->inferModalityFromPhrase($t[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE]);
            if($modality == NULL)
            {
                $modality = $language_infer->inferModalityFromPhrase($t[\raptor\WorklistColumnMap::WLIDX_STUDY]);
            }

            $t[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] = -1;  //Important that we allocate something here, will replace later.
            
            $t[\raptor\WorklistColumnMap::WLIDX_MODALITY] = 'Unknown';
            
            if($modality != '')    //Do not return the row if we cannot determine the modality.  TODO --- Replace this approach!!!!
            {
                //Count this order as pending for the patient?
                if($t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'AC'
                        || $t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'CO'
                        || $t[\raptor\WorklistColumnMap::WLIDX_WORKFLOWSTATUS] == 'RV')
                {
                    $aPatientPendingOrderMap[$patientID][$ienKey] 
                            = array($ienKey
                                ,$modality
                                ,$t[\raptor\WorklistColumnMap::WLIDX_STUDY]);
                    if(isset($aPatientPendingOrderCount[$patientID]))
                    {
                        $aPatientPendingOrderCount[$patientID] +=  1; 
                    } else {
                        $aPatientPendingOrderCount[$patientID] =  1; 
                    }
                }
                
                $nFound++;
                $offset = $nFound;
                $t[\raptor\WorklistColumnMap::WLIDX_MODALITY] = $modality;

                //Compute the score for this row.
                $t[\raptor\WorklistColumnMap::WLIDX_RANKSCORE] = $match_order_to_user->getTicketRelevance($t);
                        //TicketMetrics::getTicketRelevance($oUserInfo, $t);
                
                $t[\raptor\WorklistColumnMap::WLIDX_ORDERFILEIEN] = $exploded[WorklistData::WLVFO_OrderFileIen]; 
                $t[\raptor\WorklistColumnMap::WLIDX_RADIOLOGYORDERSTATUS] = $exploded[WorklistData::WLVFO_RadOrderStatus]; 
                

                //Add this row to the worklist because modality not blank.
                $worklist[$offset] = $t;    
                if($ienKey == $match_IEN)
                {
                    $nOffsetMatchIEN = $offset;
                }   
                
            }
        }
        for($i=0;$i<count($worklist);$i++)
        {
            $t = &$worklist[$i];
            if(is_array($t))
            {
                //Yes, this is a real row.
                $patientID = $t[\raptor\WorklistColumnMap::WLIDX_PATIENTID];
                if(isset($aPatientPendingOrderMap[$patientID]))
                {
                    $t[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT] = $aPatientPendingOrderMap[$patientID];
                    $t[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] = $aPatientPendingOrderCount[$patientID];
                } else {
                    //Found no pending orders for this IEN
                    $t[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT] = array();;
                    $t[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT] = 0;
                }
            }
        }

        //Populate the array of results
        $result = array('all_rows'=>&$worklist
                        ,'pending_orders_map'=>&$aPatientPendingOrderMap
                        ,'matching_offset'=>$nOffsetMatchIEN);
        
        return $result;
    }
    
    /**
     * @return string of fields to get from VISTA
     */
    private function getWorklistVistaFieldArgumentString()
    {
        $requestedFields = array();
        $requestedFields[0] = null;
        $requestedFields[WorklistData::WLVFO_PatientID] = ".01I";       
        $requestedFields[WorklistData::WLVFO_PatientName] = ".01";
        $requestedFields[WorklistData::WLVFO_ExamCategory] = "4";
        $requestedFields[WorklistData::WLVFO_RequestingPhysician] = "14";
        $requestedFields[WorklistData::WLVFO_OrderedDate] = "16";
        $requestedFields[WorklistData::WLVFO_Procedure] = "2";
        $requestedFields[WorklistData::WLVFO_ImageType] = "3";
        $requestedFields[WorklistData::WLVFO_ExamLocation] = "20";
        $requestedFields[WorklistData::WLVFO_Urgency] = "6";
        $requestedFields[WorklistData::WLVFO_Nature] = "26";
        $requestedFields[WorklistData::WLVFO_Transport] = "19";
        $requestedFields[WorklistData::WLVFO_DesiredDate] = "21";
        $requestedFields[WorklistData::WLVFO_OrderFileIen] = "7I";
        $requestedFields[WorklistData::WLVFO_RadOrderStatus] = "5I";
        
        // Compose argument string by concatenating field codes into semicolon-delimited list
        $argument = "";
        foreach($requestedFields as $rf)
        {
            if ($argument != "") $argument .= ";";
            $argument .= $rf;
        }
        
        return $argument;
    }

    /**
     * @description Return result of web service call to MDWS, web method QueryService.ddrLister
     */
    private function getWorklistFromMDWS($startIEN='', $filterDiscontinued = true)
    {
        $MAXRECS = 1500;  //Starts grabbing from end of file
        
        //error_log('DEBUG called getWorklistFromMDWS start');
        $sThisResultName = 'getWorklistFromMDWS';
        $result = NULL;
        try{
            if(!isset($this->m_oContext))
            {
                throw new \Exception('getWorklistFromMDWS failed because Context object is not set!');
            }
//              // this is the spot where we determine whether to show this record in the worklist
//                '1' FOR DISCONTINUED;
//                '2' FOR COMPLETE;
//                '3' FOR HOLD;
//                '5' FOR PENDING;
//                '6' FOR ACTIVE;
//                '8' FOR SCHEDULED;
//                '11' FOR UNRELEASED;
            //$mdwsDao = $this->m_oContext->getMdwsClient();
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $result = $mdwsDao->makeQuery('ddrLister', array(
                'file'=>'75.1', 
                'iens'=>'',     //Only for sub files
                'fields'=>$this->getWorklistVistaFieldArgumentString(), 
                'flags'=>'PB',      //P=PACKED format, B=Back
                'maxrex'=>$MAXRECS,    //1780',   //20140926 Known issue with RPC if this number is too big need to look into fix
                'from'=>$startIEN,     //For pagination provide smallest IEN as startign point for new query
                'part'=>'',         //ignore
                'xref'=>'#',        //Leave as #
                'screen'=> ($filterDiscontinued ? "I (\$P(^(0),U,5)'=1)" : ''), //I ($P(^(0),U,5)=5)|($P(^(0),U,5)=6)',   //Server side filtering but APPLIED TO EACH RECORD ONE BY ONE VERY SLOW NO FILTERING BEFOREHAND
                'identifier'=>''    //Mumps code for filtering etc
                ));
            
        } catch (\Exception $ex) {
            $msg = 'Failed getting worklist because ' . $ex;
            error_log($msg);
            throw $ex;
        }
        //error_log('DEBUG LOOK called getWorklistFromMDWS done with result >>>'.print_r($result,TRUE));
        return $result;
    }
    
    private function getWorklistItemFromMDWS($sTrackingID)
    {
        try
        {
            //Get the IEN from the tracking ID
            $aParts = (explode('-',$sTrackingID));
            if(count($aParts) == 2)
            {
                $nIEN = $aParts[1]; //siteid-IEN
            } else 
            if(count($aParts) == 1)     
            {
                $nIEN = $aParts[0]; //Just IEN
            } else {
                $sMsg = 'Did NOT recognize format of tracking id ['.$sTrackingID.'] expected SiteID-IEN format!';
                error_log($sMsg);
                throw new \Exception($sMsg);
            }

            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $aResult = \raptor\MdwsUtils::parseDdrGetsEntryInternalAndExternal($mdwsDao->makeQuery("ddrGetsEntry", array(
                'file'=>'75.1', 
                'iens'=>($nIEN.','),
                'flds'=>'*', 
                'flags'=>'IEN'
                )));
            /*
            $aResult = \raptor\MdwsUtils::parseDdrGetsEntryInternalAndExternal($this->m_oContext->getMdwsClient()->makeQuery("ddrGetsEntry", array(
                'file'=>'75.1', 
                'iens'=>($nIEN.','),
                'flds'=>'*', 
                'flags'=>'IEN'
                )));
             */

            return $aResult;
        } catch (\Exception $ex) {
            error_log("Failed getWorklistItemFromMDWS because ".$ex->getMessage());
            throw $ex;
        }
    }
    
    public static function formatSSN($digits)
    {
        if($digits != NULL && strlen($digits) == 9)
        {
            return $digits[0] . $digits[1] . $digits[2] 
                    . '-' . $digits[3] . $digits[4] 
                    . '-' . $digits[5] . $digits[6] . $digits[7];
        }
        return $digits;
    }
    
    /**
     * @description return standard-formatted patient's name (lAST_NAME, FIRST_NAME), based on the specified full name
     */
    private function formatPatientName($fullName)
    {
        $nameArray = explode(',', $fullName);
        return $nameArray[0].", ".$nameArray[1];
    }
    
    /**
     * Get all the worklist rows for the provided context
     * @return type array of rows for the worklist page
     */
    public function getWorklistRows($startIEN='')
    {
        try
        {
            $sThisResultName = 'getWorklistRows';

            $mdwsResponse = $this->getWorklistFromMDWS($startIEN);
            $sqlResponse = $this->getWorklistTrackingFromSQL();

            $currentTrackingID = $this->m_oContext->getSelectedTrackingID();
            $parsedWorklist = $this->parseWorklist($mdwsResponse, $sqlResponse, $currentTrackingID);

            $dataRows = $parsedWorklist['all_rows'];
            $pending_orders_map = $parsedWorklist['pending_orders_map'];
            $matching_offset = $parsedWorklist['matching_offset'];

            $aResult = array('Pages'=>1
                            ,'Page'=>1
                            ,'RowsPerPage'=>9999
                            ,'DataRows'=>$dataRows
                            ,'matching_offset' => $matching_offset
                            ,'pending_orders_map' => $pending_orders_map
                );	
            return $aResult;
        } catch (\Exception $ex) {
            error_log("Failed getWorklistRows because ".$ex->getMessage());
            throw $ex;
        }
    }
  
    public function getPendingOrdersMap()
    {
        $aResult = $this->getWorklistRows();
        return $aResult['pending_orders_map'];
    }
    
    /**
     * Gets dashboard details for the currently selected ticket of the session
     */
    public function getDashboardMap($override_tracking_id=NULL)
    {
        try
        {
            if($override_tracking_id !== NULL)
            {
                //Potential enhancement note: Change this side-effect so override does NOT alter the context for the session.
                $this->m_oContext->setSelectedTrackingID($override_tracking_id);        
            }

            $aResult = $this->getWorklistRows();
            $offset = $aResult['matching_offset'];
            $all_rows = $aResult['DataRows'];
            $row = $all_rows[$offset];

            $currentTrackingID = $this->m_oContext->getSelectedTrackingID();
            if($currentTrackingID == '')
            {
                throw new \Exception('Cannot get dashboard data when '
                        . 'current tracking id is not set!  '
                        . 'Your session may have timed out.');
            }
            if(!isset($row[\raptor\WorklistColumnMap::WLIDX_TRACKINGID]))
            {
                $msg = 'Expected to get value in WLIDX_TRACKINGID of dashboard for trackingID=['
                        .$currentTrackingID.'] but did not!';
                error_log($msg.">>>row details=".print_r($row, TRUE));
                throw new \Exception($msg);
            }
            if($currentTrackingID != $row[\raptor\WorklistColumnMap::WLIDX_TRACKINGID])
            {
                $msg = 'Expected to get dashboard for trackingID=['
                        .$currentTrackingID.'] but got data for ['
                        .$row[\raptor\WorklistColumnMap::WLIDX_TRACKINGID].'] instead!';
                error_log($msg.">>>row details=".print_r($row, TRUE));
                throw new \Exception($msg);
            }

            $siteid = $this->m_oContext->getSiteID();
            $tid = $row[\raptor\WorklistColumnMap::WLIDX_TRACKINGID];
            $pid = $row[\raptor\WorklistColumnMap::WLIDX_PATIENTID];
            $oPatientData = $this->getPatient($pid);
            if($oPatientData == NULL)
            {
                $msg = 'Did not get patient data of pid='.$pid
                        .' for trackingID=['.$currentTrackingID.']';
                error_log($msg.">>>instance details=".print_r($this, TRUE));
                throw new \Exception($msg);
            }

            // use DDR GETS ENTRY to fetch CLINICAL Hx WP field
            $worklistItemDict = $this->getWorklistItemFromMDWS($tid);
            $orderFileIen = $worklistItemDict['7']['I'];
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            /*
            $orderFileRec = \raptor\MdwsUtils::parseDdrGetsEntryInternalAndExternal
               ($this->m_oContext->getMdwsClient()->makeQuery('ddrGetsEntry', array(
                   'file'=>'100', 
                   'iens'=>($orderFileIen.','),
                   'flds'=>'*', 
                   'flags'=>'IEN'
               )));
             */
            $orderFileRec = \raptor\MdwsUtils::parseDdrGetsEntryInternalAndExternal
               ($mdwsDao->makeQuery('ddrGetsEntry', array(
                   'file'=>'100', 
                   'iens'=>($orderFileIen.','),
                   'flds'=>'*', 
                   'flags'=>'IEN'
               )));

     //       $details = array();
            $t['orderingPhysicianDuz'] = $worklistItemDict['14']['I']; // get internal value of ordering provider field
            $t['orderFileStatus'] = $orderFileRec['5']['E'];
            // 3/11/15 JAM - helper boolean for cancelng order GUI
            // 5 => PENDING, 11 => UNRELEASED
            $t['canOrderBeDCd'] = $worklistItemDict['5']['I'] == '5' || $worklistItemDict['5']['I'] == '11';
            // end 3/11/15 JAM
            $t['orderActive'] = !key_exists('63', $orderFileRec); // field 63 in file 100 is discontinue date/time
            // may be more to return here in the future

            $t['Tracking ID']       = $siteid.'-'.$tid;
            $t['Procedure']         = $row[\raptor\WorklistColumnMap::WLIDX_STUDY];
            $t['Modality']          = $row[\raptor\WorklistColumnMap::WLIDX_MODALITY];

            $t['ExamCategory']      = $row[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            $t['PatientLocation']   = $row[\raptor\WorklistColumnMap::WLIDX_EXAMLOCATION]; //DEPRECATED 1/29/2015      
            $t['RequestedBy']       = $row[\raptor\WorklistColumnMap::WLIDX_REQUESTINGPHYSICIAN];

            // ATTENTION FRANK: new indices for requesting location and submit to location
            $t['RequestingLocation']= trim((isset($worklistItemDict['22']['I']) ? $worklistItemDict['22']['I'] : '') );
            $t['SubmitToLocation']  = trim((isset($worklistItemDict['20']['I']) ? $worklistItemDict['20']['I'] : '') );
            // END ATTN FRANK

            $aSchedInfo = $row[\raptor\WorklistColumnMap::WLIDX_SCHEDINFO];
            $t['SchedInfo']         = $aSchedInfo;
            $t['RequestedDate']     = $row[\raptor\WorklistColumnMap::WLIDX_DATEORDERED]; 
            $t['DesiredDate']       = $row[\raptor\WorklistColumnMap::WLIDX_DATETIMEDESIRED]; 
            $t['ScheduledDate']     = $aSchedInfo['EventDT'];

            $t['PatientCategory']   = $row[\raptor\WorklistColumnMap::WLIDX_PATIENTCATEGORYLOCATION];
            // changed reason for study to real RFS, added 'NatureOfOrderActivity' key 
            $t['ReasonForStudy']    = trim((isset($worklistItemDict['1.1']['I']) ? $worklistItemDict['1.1']['I'] : '') );
            $t['NatureOfOrderActivity'] = $row[\raptor\WorklistColumnMap::WLIDX_NATUREOFORDERACTIVITY];

            $t['RequestingLocation'] = trim((isset($worklistItemDict['22']['E']) ? $worklistItemDict['22']['E'] : '') );
            $t['RequestingLocationIen'] = trim((isset($worklistItemDict['22']['I']) ? $worklistItemDict['22']['I'] : '') );

            $t['ClinicalHistory']   = trim((isset($worklistItemDict['400']) ? $worklistItemDict['400'] : '') );
            $t['PatientID']         = $pid;
            $t['PatientSSN']        = WorklistData::formatSSN($oPatientData['ssn']);
            $t['Urgency']           = $row[\raptor\WorklistColumnMap::WLIDX_URGENCY];
            $t['Transport']         = $row[\raptor\WorklistColumnMap::WLIDX_TRANSPORT];
            $t['PatientName']       = $row[\raptor\WorklistColumnMap::WLIDX_PATIENTNAME];
            $t['PatientAge']        = $oPatientData['age'];
            $t['PatientDOB']        = $oPatientData['dob'];
            $t['PatientEthnicity']  = $oPatientData['ethnicity'];
            $t['PatientGender']     = $oPatientData['gender'];
            $t['ImageType']         = $row[\raptor\WorklistColumnMap::WLIDX_IMAGETYPE];
            $t['mpiPid']            = $oPatientData['mpiPid'];
            $t['mpiChecksum']       = $oPatientData['mpiChecksum'];
            $t['CountPendingOrders']   = $row[\raptor\WorklistColumnMap::WLIDX_COUNTPENDINGORDERSSAMEPATIENT];
            $t['MapPendingOrders']     = $row[\raptor\WorklistColumnMap::WLIDX_MAPPENDINGORDERSSAMEPATIENT];
            $t['OrderFileIen']          = $row[\raptor\WorklistColumnMap::WLIDX_ORDERFILEIEN];
            $t['RadiologyOrderStatus']  = $row[\raptor\WorklistColumnMap::WLIDX_RADIOLOGYORDERSTATUS];
            return $t;
            
        } catch (\Exception $ex) {
            error_log("Failed getDashboardMap because ".$ex->getMessage());
            throw $ex;
        }
    }
    
    /**
     * @description Call EMR web service method "select" passing as an argument PatientID stored in context
     * @return Array containing patient information 
     * This is an old function but appears to be in use; moved out of Context class on 5/21/2014
     */
    public function getPatient($pid)
    {
        if(!isset($pid) || $pid == NULL || $pid == '')
        {
            //Changed to throw exception on 20150605
            throw new \Exception('Missing required parameter value in call to getPatient!');
        }
        
        try
        {
            //$serviceResponse = $this->getEMRService()->select(array('DFN'=>$pid == null ? $this->getPatientID() : $pid));
            $mdwsDao = $this->m_oContext->getEhrDao()->getImplementationInstance();
            $serviceResponse = $mdwsDao->makeQuery("select", array('DFN'=>$pid));
            //$serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("select", array('DFN'=>$pid));
            //drupal_set_message('LOOK DFN RESULT>>>>' . print_r($serviceResponse, TRUE));

            $result = array();
            if(!isset($serviceResponse->selectResult))
                    return $result;

            $RptTO = $serviceResponse->selectResult;
            if(isset($RptTO->fault))
            { 
                return $result;
            }
            $result['patientName'] = isset($RptTO->name) ? $RptTO->name : " ";
            $result['ssn'] = isset($RptTO->ssn) ? $RptTO->ssn : " ";
            $result['gender'] = isset($RptTO->gender) ? $RptTO->gender : " ";
            $result['dob'] = isset($RptTO->dob) ? date("m/d/Y", strtotime($RptTO->dob)) : " ";
            $result['ethnicity'] = isset($RptTO->ethnicity) ? $RptTO->ethnicity : " ";
            $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
            $result['maritalStatus'] = isset($RptTO->maritalStatus) ? $RptTO->maritalStatus : " ";
            $result['age'] = isset($RptTO->age) ? $RptTO->age : " ";
            $result['mpiPid'] = isset($RptTO->mpiPid) ? $RptTO->mpiPid : " ";
            $result['mpiChecksum'] = isset($RptTO->mpiChecksum) ? $RptTO->mpiChecksum : " ";
            $result['localPid'] = isset($RptTO->localPid) ? $RptTO->localPid : " ";
            $result['sitePids'] = isset($RptTO->sitePids) ? $RptTO->sitePids : " ";
            $result['vendorPid'] = isset($RptTO->vendorPid) ? $RptTO->vendorPid : " ";
            if(isset($RptTO->location))
            {
                $aLocation = $RptTO->location;
                $room = "Room: ";
                $room .=isset($aLocation->room)? $aLocation->room : " ";
                $bed =  "Bed: ";
                $bed .= (isset($aLocation->bed) ? $aLocation->bed : " " );
                $result['location'] = $room." / ".$bed;
            }
            else
            {
                $result['location'] = "Room:? / Bed:? ";
            }
            $result['cwad'] = isset($RptTO->cwad) ? $RptTO->cwad : " ";
            $result['restricted'] = isset($RptTO->restricted) ? $RptTO->restricted : " ";

            $result['admitTimestamp'] = isset($RptTO->admitTimestamp) ? date("m/d/Y h:i a", strtotime($RptTO->admitTimestamp)) : " ";

            $result['serviceConnected'] = isset($RptTO->serviceConnected) ? $RptTO->serviceConnected : " ";
            $result['scPercent'] = isset($RptTO->scPercent) ? $RptTO->scPercent : " ";
            $result['inpatient'] = isset($RptTO->inpatient) ? $RptTO->inpatient : " ";
            $result['deceasedDate'] = isset($RptTO->deceasedDate) ? $RptTO->deceasedDate : " ";
            $result['confidentiality'] = isset($RptTO->confidentiality) ? $RptTO->confidentiality : " ";
            $result['needsMeansTest'] = isset($RptTO->needsMeansTest) ? $RptTO->needsMeansTest : " ";
            $result['patientFlags'] = isset($RptTO->patientFlags) ? $RptTO->patientFlags : " ";
            $result['cmorSiteId'] = isset($RptTO->cmorSiteId) ? $RptTO->cmorSiteId : " ";
            $result['activeInsurance'] = isset($RptTO->activeInsurance) ? $RptTO->activeInsurance : " ";
            $result['isTestPatient'] = isset($RptTO->isTestPatient) ? $RptTO->isTestPatient : " ";
            $result['currentMeansStatus'] = isset($RptTO->currentMeansStatus) ? $RptTO->currentMeansStatus : " ";
            $result['hasInsurance'] = isset($RptTO->hasInsurance) ? $RptTO->hasInsurance : " ";
            $result['preferredFacility'] = isset($RptTO->preferredFacility) ? $RptTO->preferredFacility : " ";
            $result['patientType'] = isset($RptTO->patientType) ? $RptTO->patientType : " ";
            $result['isVeteran'] = isset($RptTO->isVeteran) ? $RptTO->isVeteran : " ";
            $result['isLocallyAssignedMpiPid'] = isset($RptTO->isLocallyAssignedMpiPid) ? $RptTO->isLocallyAssignedMpiPid : " ";
            $result['sites'] = isset($RptTO->sites) ? $RptTO->sites : " ";
            $result['teamID'] = isset($RptTO->teamID) ? $RptTO->teamID : " ";
            $result['teamName'] = isset($RptTO->name) ? $RptTO->name : "Unknown";
            $result['teamPcpName'] = isset($RptTO->pcpName) ? $RptTO->pcpName : "Unknown";
            $result['teamAttendingName'] = isset($RptTO->attendingName) ? $RptTO->attendingName : "Unknown";
            $result['mpiPid'] = isset($RptTO->mpiPid) ? $RptTO->mpiPid : "Unknown";
            $result['mpiChecksum'] = isset($RptTO->mpiChecksum) ? $RptTO->mpiChecksum : "Unknown";

            return $result;
        } catch (\Exception $ex) {
            error_log("Failed getPatient because ".$ex->getMessage());
            throw $ex;
        }
    }
}

