<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * Updated 20140601
 */

namespace raptor;

require_once("config.php");
require_once("MdwsUtils.php");
require_once("TicketMetrics.php");
/**
 * This class provides methods to return filtered lists of rows for the worklist
 * and also a single worklist row when given a matching ID.
 *
 * @author SAN
 */
class WorklistData 
{
    private $m_oContext;
    
    const WLIDX_TRACKINGID = 0;
    const WLIDX_PATIENTID = 1;
    const WLIDX_PATIENTNAME = 2;
    const WLIDX_DATETIMEDESIRED = 3;
    const WLIDX_DATEORDERED = 4;
    const WLIDX_MODALITY = 5;
    const WLIDX_STUDY = 6;
    const WLIDX_URGENCY = 7;
    const WLIDX_TRANSPORT = 8;
    const WLIDX_PATIENTCATEGORYLOCATION = 9;
    const WLIDX_ANATOMYIMAGESUBSPEC = 10;
    const WLIDX_WORKFLOWSTATUS = 11;
    const WLIDX_ASSIGNEDUSER = 12;
    const WLIDX_ORDERSTATUS = 13;
    const WLIDX_EDITINGUSER = 14;
    const WLIDX_SCHEDINFO = 15;
    const WLIDX_CPRSCODE = 16;
    const WLIDX_IMAGETYPE = 17;
    const WLIDX_RANKSCORE = 18;
    
    
    function __construct($oContext)
    {
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
            $result[$key] = $row;
        }

        return $result;
    }
    
    private function getWorklistTrackingFromSQL() {
        // question - will there be only one record per IEN in each of these tables?
        // is some additional filtering needed to obtain the current active record?
        
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
    }

    private function parseWorklist($MDWSResponse, $ticketTrackingDict)
    {
        
        $ls=0;
                
        $oUserInfo = $this->m_oContext->getUserInfo();
        $worklist = array();
        $numOrders = isset($MDWSResponse->ddrListerResult) ? $MDWSResponse->ddrListerResult->count : 0;
        if($numOrders == 0)
        {
            if(is_object($MDWSResponse))
            {
                if(isset($MDWSResponse->ddrListerResult))
                {
                    $showinfo = $MDWSResponse->ddrListerResult;
                } else {
                    $showinfo = '(No DDRLister results)';
                }
            } else {
                $showinfo = '(Non-object DDRLister Result)';
            }
            error_log("DID NOT FIND ANY DATA IN MDWS!  MDWSResponse Details START...\n" . print_r($MDWSResponse, true) . "\n...MDWSResponse Details END!\nMDWSResponse->ddrListerResult Details Start...\n" 
                    . $showinfo . "\nMDWSResponse->ddrListerResult Details END...");
            return false;
        }
        $strings = isset($MDWSResponse->ddrListerResult->text->string) ? $MDWSResponse->ddrListerResult->text->string : array();
        //print_r($strings);
                
        $exploded = array();
        $t = array();
        $nFound=0;
        
        $ticketTrackingRslt = $ticketTrackingDict["raptor_ticket_tracking"];
        $ticketCollabRslt = $ticketTrackingDict["raptor_ticket_collaboration"];
        $scheduleTrackRslt = $ticketTrackingDict["raptor_schedule_track"];
        
        for ($i=0; $i<$numOrders; $i++)
        {
            $exploded = explode("^", $strings[$i]);
            
            $ienKey = $exploded[0];
            $sqlTicketTrackRow = array_key_exists($ienKey, $ticketTrackingRslt) ? $ticketTrackingRslt[$ienKey] : null; // use IEN from MDWS results as key
            $sqlTicketCollaborationRow = array_key_exists($ienKey, $ticketCollabRslt) ? $ticketCollabRslt[$ienKey] : null; // use IEN from MDWS results as key
            $sqlScheduleTrackRow = array_key_exists($ienKey, $scheduleTrackRslt) ? $scheduleTrackRslt[$ienKey] : null; // use IEN from MDWS results as key

            $t[WorklistData::WLIDX_TRACKINGID]  = $exploded[0];
            $t[WorklistData::WLIDX_PATIENTID]   = $exploded[FieldOrder::PatientID]; 
            $t[WorklistData::WLIDX_PATIENTNAME] = $this->formatPatientName($exploded[FieldOrder::PatientName]);
            $t[WorklistData::WLIDX_DATETIMEDESIRED] = $exploded[FieldOrder::DesiredDate];
            
            if($exploded[FieldOrder::OrderedDate] !== '' && ($last = strrpos($exploded[FieldOrder::OrderedDate], ':')) !== FALSE)
            {
                //Remove the seconds from the time.
                $dateordered = substr($exploded[FieldOrder::OrderedDate], 0, $last);
            } else {
                //Assume there is no time portion.
                $dateordered = $exploded[FieldOrder::OrderedDate];
            }
            $t[WorklistData::WLIDX_DATEORDERED]     = $dateordered;
            
            
            //$t[5]                   = "CT??"; //Field ??   - Example: CT
            $t[WorklistData::WLIDX_STUDY]       = $exploded[FieldOrder::Procedure];
            $t[WorklistData::WLIDX_URGENCY]     = $exploded[FieldOrder::Urgency]; 
            switch(trim($exploded[FieldOrder::Transport]))
            {
                case "a":
                    $t[WorklistData::WLIDX_TRANSPORT] = "AMBULATORY";
                    break;
                case "p":
                    $t[WorklistData::WLIDX_TRANSPORT] = "PORTABLE";
                    break;
                case "s":
                    $t[WorklistData::WLIDX_TRANSPORT] = "STRETCHER";
                    break;
                case "w":
                    $t[WorklistData::WLIDX_TRANSPORT] = "WHEEL CHAIR";
                    break;
                default:
                    $t[WorklistData::WLIDX_TRANSPORT] = " ";
                    break;
            }
            $t[WorklistData::WLIDX_PATIENTCATEGORYLOCATION]     = $exploded[FieldOrder::ExamCategory];
            $t[WorklistData::WLIDX_ANATOMYIMAGESUBSPEC]         = 'TODO ANATOMY';   //Placeholder for anatomy keywords
            $t[WorklistData::WLIDX_WORKFLOWSTATUS]              = (isset($sqlTicketTrackRow) ? $sqlTicketTrackRow->workflow_state : "AC"); // default to "AC"

            $t[WorklistData::WLIDX_ASSIGNEDUSER]                = (isset($sqlTicketCollaborationRow) ? array('uid'=>$sqlTicketCollaborationRow->collaborator_uid
                                                    , 'requester_notes_tx'=>$sqlTicketCollaborationRow->requester_notes_tx
                                                    , 'requested_dt'=>$sqlTicketCollaborationRow->requested_dt
                                                    , 'username'=>$sqlTicketCollaborationRow->username
                                                    , 'fullname'=>trim($sqlTicketCollaborationRow->usernametitle . ' ' .$sqlTicketCollaborationRow->firstname
                                                            . ' ' .$sqlTicketCollaborationRow->lastname. ' ' .$sqlTicketCollaborationRow->suffix )
                                                ) : NULL);    // This is the user that is currently editing the ticket
            
            $t[WorklistData::WLIDX_ORDERSTATUS]  = 'TODO ORDER STATUS';   //Placeholder for Order Status
                    
            $t[WorklistData::WLIDX_EDITINGUSER]      = '';   //Placeholder for UID of user that is currently editing the record, if any. (check local database)

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
                    $showText = 'Canceled '.$sqlScheduleTrackRow->canceled_dt; 
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
                $t[WorklistData::WLIDX_SCHEDINFO] = array(
                    'EventDT' => $sqlScheduleTrackRow->scheduled_dt,
                    'LocationTx' => $sqlScheduleTrackRow->location_tx,
                    'ConfirmedDT' => $sqlScheduleTrackRow->confirmed_by_patient_dt,
                    'CanceledDT' => $sqlScheduleTrackRow->canceled_dt,
                    'ShowTx' => $showText
                );
                print_r($sqlScheduleTrackRow, TRUE);
            } else {
                //No record exists yet.
                $t[WorklistData::WLIDX_SCHEDINFO] = array(
                    'EventDT' => NULL,
                    'LocationTx' => NULL,
                    'ConfirmedDT' => NULL,
                    'CanceledDT' => NULL,
                    'ShowTx' => 'Unknown'
                );
            }

            $t[WorklistData::WLIDX_CPRSCODE]    = '';   //Placeholder for the CPRS code associated with this ticket
            $t[WorklistData::WLIDX_IMAGETYPE]   = $exploded[FieldOrder::ImageType];   //Placeholder for Imaging Type - file 75.1, field 3

            $modality = $this->getImpliedModality($t[WorklistData::WLIDX_STUDY]);   //20140603
            $t[WorklistData::WLIDX_MODALITY] = 'xy';
            if($modality !== '')				
            {
                $nFound++;
                $offset = $nFound;
                $t[WorklistData::WLIDX_MODALITY] = $modality;

                //Compute the score for this row.
                $t[WorklistData::WLIDX_RANKSCORE] = TicketMetrics::getTicketRelevance($oUserInfo, $t);         
                
                //Add this row to the worklist because modality not blank.
                $worklist[$offset] = $t;    
            }

        }
        return $worklist;
    }
    
    private function getRequestedFieldArgumentString(){
        $requestedFields = array();
        $requestedFields[0] = null;
        $requestedFields[FieldOrder::PatientID] = ".01I";       
        $requestedFields[FieldOrder::PatientName] = ".01";
        $requestedFields[FieldOrder::ExamCategory] = "4";
        $requestedFields[FieldOrder::RequestingPhysician] = "14";
        $requestedFields[FieldOrder::OrderedDate] = "16";
        $requestedFields[FieldOrder::Procedure] = "2";
        $requestedFields[FieldOrder::ImageType] = "3";
        $requestedFields[FieldOrder::ExamLocation] = "20";
        $requestedFields[FieldOrder::Urgency] = "6";
        $requestedFields[FieldOrder::Nature] = "26";
        $requestedFields[FieldOrder::Transport] = "19";
        $requestedFields[FieldOrder::DesiredDate] = "21";
        
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
    private function getWorklistFromMDWS()
    {
        $result = NULL;
        try{
            if(!isset($this->m_oContext))
            {
                throw new \Exception('getWorklistFromMDWS failed because Context object is not set!');
            }
            $result = $this->m_oContext->getMdwsClient()->makeQuery("ddrLister", array(
                'file'=>'75.1', 
                'iens'=>'',
    //            'fields'=>'.01I;21;16;2;6;4;3;5;20;1.1;22;14;26;.01;19I', 
                'fields'=>$this->getRequestedFieldArgumentString(), 
                'flags'=>'P',
                'maxrex'=>'', 
                'from'=>'',
                'part'=>'',
                'xref'=>'#',
                'screen'=>'',
                'identifier'=>''
                ));
        } catch (\Exception $ex) {
            $msg = 'Trouble getting worklist because '.$ex;
            error_log($msg);
            throw $ex;
        }
        return $result;
        
//        return $this->m_oContext->getQueryService()->ddrLister(array(
//            'file'=>'75.1', 
//            'iens'=>'',
////            'fields'=>'.01I;21;16;2;6;4;3;5;20;1.1;22;14;26;.01;19I', 
//            'fields'=>$this->getRequestedFieldArgumentString(), 
//            'flags'=>'P',
//            'maxrex'=>'', 
//            'from'=>'',
//            'part'=>'',
//            'xref'=>'#',
//            'screen'=>'',
//            'identifier'=>''
//            ));
    }
    
    private function getWorklistItemFromMDWS($sTrackingId){
        return MdwsUtils::parseDdrGetsEntry($this->m_oContext->getMdwsClient()->makeQuery("ddrGetsEntry", array(
            'file'=>'75.1', 
            'iens'=>($sTrackingId.','),
            'flds'=>'*', 
            'flags'=>'IEN'
            )));
//        return $this->m_oContext->getQueryService()->ddrGetsEntry(array(
//            'file'=>'75.1', 
//            'iens'=>'35,',
//            'flds'=>'.01I;21;16;2;6;4;3;5;20;1.1;22;14;26;.01;19I', 
//            'flags'=>'P'
//            ));
    }
    
    public static function formatSSN($digits)
    {
        if($digits != NULL && strlen($digits) == 9)
        {
            return $digits[0] . $digits[1] . $digits[2] . '-' . $digits[3] . $digits[4] . '-' . $digits[5] . $digits[6] . $digits[7];
        }
        return $digits;
    }
    
    /**
     * @abstract Return dashboard data based on Tracking ID
     * 
     */
    function getDashboardData() 
    {
        $serviceResponse = $this->getWorklistFromMDWS();
        
        $worklist = array();
        
        $numOrders = isset($serviceResponse->ddrListerResult) ? $serviceResponse->ddrListerResult->count : 0;        
        if($numOrders == 0)
            return false;

        $strings = isset($serviceResponse->ddrListerResult->text->string) ? $serviceResponse->ddrListerResult->text->string : array();
        //print_r($strings);
        
        $exploded = array();
        $t = array();

        $worklist = null;
        for ($i=0; $i<$numOrders; $i++)
        {
            $exploded = explode("^", $strings[$i]);
            
            if ($this->m_oContext->getSelectedTrackingID() == null || 
                $this->m_oContext->isDataMatchingTrackingID($exploded[0]))
            {
                // Lookup additional patient information using EmrService call (from context object)
                $pid = $exploded[FieldOrder::PatientID];
                $oPatientData = $this->getPatient($pid);
                if($oPatientData == null)
                {
                    die('Did NOT get patient data for ' . print_r($exploded, true));
                }
                // use DDR GETS ENTRY to fetch CLINICAL Hx WP field
                $worklistItemDict = $this->getWorklistItemFromMDWS($exploded[0]);
                        
                $t['Tracking ID']        = $exploded[0];
                $t['CaseID']            = "Not Implemented";        // ??? NO IDEA ???
                $t['Procedure']         = $exploded[FieldOrder::Procedure];
                $t['Modality']          = $this->getImpliedModality($t['Procedure']); //20140621
                
                $t['ExamCategory']      = $exploded[FieldOrder::ExamCategory]; 
                $t['PatientLocation']   = $exploded[FieldOrder::ExamLocation];
                $t['RequestedBy']       = $exploded[FieldOrder::RequestingPhysician];
                $t['RequestedDate']     = $exploded[FieldOrder::DesiredDate];        //Field 21 - ??? IS THIS DATE DESIRED ???
                $t['ScheduledDate']     = $exploded[FieldOrder::OrderedDate];        //Field 16 - ????? IS THIS AN ORDERED DATE ????
                
                // should PatientCategory be in/out patient? using the 'category' field from 75.1 seems wrong regardless
                //$t['PatientCategory']   = $exploded[FieldOrder::PatientCategory];
                // ugly! inpatient status is being parsed so really only way for GUI I could think of
                $t['PatientCategory']   = ($oPatientData['location'] === "Room:? / Bed:? ") ? "Outpatient" : "Inpatient";
                
                $t['ReasonForStudy']    = $exploded[FieldOrder::Nature];
                $t['ClinicalHistory']   = trim(   (isset($worklistItemDict["400"]) ? $worklistItemDict["400"] : '') );
                //$t['ClinicalHistory']   = "Not Implemented";    // WHAT IS THE FORMAT??? IS IT SAME AS PROBLEMS ??? PROBLEMS HAVE COMPLEX STRUCTURE
                $t['PatientID']         = $pid;
                $t['PatientSSN']        = WorklistData::formatSSN($oPatientData['ssn']);
                $t['Urgency']           = $exploded[FieldOrder::Urgency];
                switch(trim($exploded[FieldOrder::Transport]))
                {
                    case "a":
                        $t['Transport'] = "AMBULATORY";
                        break;
                    case "p":
                        $t['Transport'] = "PORTABLE";
                        break;
                    case "s":
                        $t['Transport'] = "STRETCHER";
                        break;
                    case "w":
                        $t['Transport'] = "WHEEL CHAIR";
                        break;
                    default:
                        $t['Transport'] = " ";
                        break;
                }
                $t['PatientName']       = $this->formatPatientName($exploded[FieldOrder::PatientName]);
                $t['PatientAge']        = $oPatientData['age'];
                $t['PatientDOB']        = $oPatientData['dob'];
                $t['PatientEthnicity']  = $oPatientData['ethnicity'];
                $t['PatientGender']     = $oPatientData['gender'];
                $t['ImageType']         = $exploded[FieldOrder::ImageType];
                $t['mpiPid']            = $oPatientData['mpiPid'];
                $t['mpiChecksum']       = $oPatientData['mpiChecksum'];
                
                //NO OFFSET BECAUSE ONLY ONE ARRAY
                $worklist = $t;
                break;
            }
        }
        return $worklist;
    }


    /**
     * Derive the modality from the text if we can.
     * @param type $sProcedureText
     * @return modality abbreviation if derivable, else empty string.
     */
    private function getImpliedModality($sProcedureText)
    {
        //Make sure we only show rows that start with modality. 20140320
        $modality = strtoupper(substr($sProcedureText,0,2));
        $real_modality_pos = strpos("MR CT NM", $modality);
        if($real_modality_pos !== FALSE)
        {
            $okpos = TRUE;
        } else {
            //Temporary hardcoded logic for modality detection
            $okpos = strpos($sProcedureText, 'NUC');
            if($okpos !== FALSE)				
            {
                $modality = 'NM';
            } else {
                $okpos = strpos($sProcedureText, 'MAGNETIC');
                if($okpos !== FALSE)				
                {
                    $modality = 'MR';
                } else {
                    $okpos = (strpos($sProcedureText, 'FLUORO') !== FALSE || strpos($sProcedureText, 'ARTHROGRAM'));
                    if($okpos !== FALSE)				
                    {
                        $modality = 'FL';
                    } else {
                        $okpos = (strpos($sProcedureText, 'ECHOGRAM') !== FALSE || strpos($sProcedureText, 'ULTRASOUND'));
                        if($okpos !== FALSE)				
                        {
                            $modality = 'US';
                        } else {
                            $okpos = strpos($sProcedureText, 'BONE');
                            if($okpos !== FALSE)				
                            {
                                $modality = 'NM';
                            } else {
                                $modality = ''; //Leave modality blank we do not know what it is.
                            }
                        }
                    }
                }
            }
        }
        return $modality;
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
     * @description Return records matching search criteria contained in Context object, passed to the constructor
     * @return Return response of web method QueryService.ddrLister, with fields relevant to ProtocolSupport
     */
    function getWorklistForProtocolSupport(){
        
        $serviceResponse = $this->getWorklistFromMDWS();
        $worklist = array();      
        $numOrders = isset($serviceResponse->ddrListerResult) ? $serviceResponse->ddrListerResult->count : 0;        
        if($numOrders == 0) return $worklist;
        $strings = isset($serviceResponse->ddrListerResult->text->string) ? $serviceResponse->ddrListerResult->text->string : array();
        
        $exploded = array();
        $t = array();
        for ($i=0; $i < $numOrders; $i++)
        {
            $exploded = explode("^", $strings[$i]);
            if ($this->m_oContext->getSelectedTrackingID() == null || 
                $this->m_oContext->isDataMatchingTrackingID($exploded[0]))
            {
                $t["Tracking ID"]        = $exploded[0];
                $t["PatientID"]         = $exploded[FieldOrder::PatientID];	
                $t["RqstStdy"]          = $exploded[FieldOrder::Procedure];
                switch (trim($exploded[FieldOrder::Nature])){
                        case 'w' :
                            $t["RsnStdy"] = "WRITTEN";
                            break;
                        case 'v' :
                            $t["RsnStdy"] = "VERBAL";
                            break;
                        case 'p' :
                            $t["RsnStdy"] = "TELEPHONED";
                            break;
                        case 's' :
                            $t["RsnStdy"] = "SERVICE CORRECTION";
                            break;
                        case 'i' :
                            $t["RsnStdy"] = "POLICY";
                            break;
                        case 'e' :
                            $t["RsnStdy"] = "PHYSICIAN ENTERED";
                            break;
                        default :
                            $t["RsnStdy"] = "NOT ENTERED";
                            break;
                    }
                $t["RqstBy"]              = $exploded[FieldOrder::RequestingPhysician];
                $worklist[]               = $t;
            }
        }
        return $worklist;
    }

    /**
     * Get all the worklist rows for the provided context
     * @param type $oContext
     * @return type array of rows for the worklist page
     */
    public function getWorklistRows($oContext){
        //TODO filter based on context details
        //TODO Finalize Array Row Structure
        //Array Row Structure (best guess): 
        // "",      "987654111", "Doe5, John", "2013-08-10 @1530", "2013-08-05",    "CT",       "CT GUIDANCE FOR CYST ASPIRATION S&I", "STAT", "Transport", "Inpatient",    "Cervical Spine ", "Active",            "Open", "2013-05-15@1500"
        // Case ID  PatientID    Patient Name  Requested Date      ScheduledDate?   ????        Procedure?                             ????     ????        PatientCategory ????               Status?(of What?)    ???     ?????
        $mdwsResponse = $this->getWorklistFromMDWS();
        $sqlResponse = $this->getWorklistTrackingFromSQL();
        $dataRows = $this->parseWorklist($mdwsResponse, $sqlResponse);	//20140301

        return array("Pages"=>1
                    ,"Page"=>1
                    ,"RowsPerPage"=>9999
                    ,"DataRows"=>$dataRows
            );	
        
    }
    
    /**
     * Get the row of data for the provided tracking ID
     * @param type $sTrackingID
     * @return type array
     */
    function getOneWorklistRow($sTrackingID)
    {
        if($sTrackingID == null)
        {
            die('Did NOT get a tracking ID at getOneWorklistRow function!');
        }
        $this->m_oContext->setSelectedTrackingID($sTrackingID);
        return $this->getDashboardData();
        //TODO get the data just for the requested row
        //return array("ABC12345","Smith,Jack","1/5/2014@15:00","1/5/2014","CT","Knee","STAT");
    }
    
    /**
     * @description Call EMR web service method "select" passing as an argument PatientID stored in context
     * @return Array containing patient information 
     * This is an old function but appears to be in use; moved out of Context class on 5/21/2014
     */
    public function getPatient($pid)
    {
        if(!isset($pid) || $pid == null || $pid == '')
        {
            error_log('Cannot get patient if pid is not provided!');
            return null;
        }
        
        //$serviceResponse = $this->getEMRService()->select(array('DFN'=>$pid == null ? $this->getPatientID() : $pid));
        $serviceResponse = $this->m_oContext->getMdwsClient()->makeQuery("select", array('DFN'=>$pid));
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
    }

    
}



/**
 * @abstract Enumerate field order in the argument sent towards WDMS Query Service method DDRLister
 */
abstract class FieldOrder
{
    const PatientID             = 1;    //Is this the IEN or the patient ID?
    const PatientName           = 2;
    const ExamCategory          = 3;
    const RequestingPhysician   = 4;
    const OrderedDate           = 5;
    const Procedure             = 6;
    const ImageType             = 7;
    const ExamLocation         = 8;
    const Urgency               = 9;
    const Nature                = 10;
    const Transport             = 11;
    const DesiredDate           = 12;
}