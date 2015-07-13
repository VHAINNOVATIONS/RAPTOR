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


namespace raptor_sched;

module_load_include('php', 'raptor_datalayer', 'core/data_context');
module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
module_load_include('php', 'raptor_datalayer', 'core/vista_integration');
//module_load_include('php', 'raptor_datalayer', 'core/MdwsDao');
//module_load_include('php', 'raptor_datalayer', 'core/MdwsDaoFactory');
//module_load_include('php', 'raptor_datalayer', 'core/MdwsUtils');

/**
 * Implements the schedule ticket/pass box page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ScheduleTicketPage
{
    private $m_oContext = null;
    private $m_oTT = null;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $tid = $this->m_oContext->getSelectedTrackingID();
        
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getDashboardMap();
        //$aOneRow = $oWL->getOneWorklistRow();   //$tid);
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        $sTrackingID = $this->m_oTT->getTrackingID($nSiteID, $nIEN);
        $sCWFS = $this->m_oTT->getTicketWorkflowState($sTrackingID);
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        $myvalues['PatientName'] = $aOneRow['PatientName'];
        $myvalues['PatientDOB'] = $aOneRow['PatientDOB'];
        $myvalues['RequestedDate'] = $aOneRow['RequestedDate'];
        $myvalues['Urgency'] = $aOneRow['Urgency'];

        //Get all the available locations.
        $query = db_select('raptor_schedule_location', 'n');
        $query->fields('n')
              ->orderBy('location_tx', 'ASC');//ORDER BY created
        $result = $query->execute();
        $rooms = array();
        while($record = $result->fetchAssoc()) 
        {
            $rooms[] = array($record['location_tx'], $record['description_tx']);
        }        
        $myvalues['rooms'] = $rooms;

        try
        {
            //Get all the available durations.
            $query = db_select('raptor_schedule_duration', 'n');
            $query->fields('n')
                  ->orderBy('minutes', 'ASC');//ORDER BY created
            $result = $query->execute();
            $durations = array();
            while($record = $result->fetchAssoc()) 
            {
                $durations[] = $record['minutes'];
            }        
            $myvalues['durations'] = $durations;
        } catch (\Exception $ex) {
            //For now keep going
            $myvalues['durations'] = NULL;
        }
        
        //Get all the available users.
        $query = db_select('raptor_user_profile', 'n');
        $query->fields('n')
              ->orderBy('lastname', 'ASC')
              ->orderBy('firstname', 'ASC');
        $query->condition('SWI1',1,'=');
        $or = db_or();
        $or->condition('SP1',1,'=');
        $or->condition('SUWI1',1,'=');
        $or->condition('PWI1',1,'=');
        $query->condition($or);
        $query->condition('accountactive_yn',1,'=');
        $result = $query->execute();
        $options = array();
        $options[''] = '';
        while($record = $result->fetchAssoc()) 
        {
            if(substr($record['role_nm'],0,1) == 'R')
            {
                $displayinfo = $record['lastname'] . ', ' . $record['firstname'] 
                        . ($record['usernametitle'] > '' ? ' / ' . $record['usernametitle'] : '')
                        . ($record['suffix'] > '' ? ' / ' . $record['suffix'] : '')
                        . ($record['role_nm'] > '' ? ' / ' . $record['role_nm'] : '');
                $options[$record['uid']] = $displayinfo;
            }
        }        
        $myvalues['assignment_options'] = $options;

        $collabinfo = $this->m_oTT->getCollaborationInfo($sTrackingID);
        if(is_array($collabinfo))
        {
            $myvalues['suggested_uid'] = $collabinfo['collaborator_uid'];
            $myvalues['suggested_alreadyset_uid'] = $myvalues['suggested_uid'];
            $myvalues['suggested_alreadyset_note'] = $collabinfo['requester_notes_tx'];
        } else {
            $myvalues['suggested_uid'] = NULL;
            $myvalues['suggested_alreadyset_uid'] = NULL;
            $myvalues['suggested_alreadyset_note'] = NULL;
        }
        $myvalues['show_collaboration_options'] = ($sCWFS == 'AC' || $sCWFS == 'CO');
        
        $query = db_select('raptor_schedule_track', 'n');
        $query->fields('n');
        $query->condition('siteid',$nSiteID,'=');
        $query->condition('IEN',$nIEN,'=');
        $result = $query->execute();
        if($result->rowCount() == 1)
        {
            $record = $result->fetchAssoc();
            $scheduled_dt = $record['scheduled_dt'];
            
            if(isset($scheduled_dt))
            {
                $dt = new \DateTime($scheduled_dt);
                $event_date_tx = $dt->format('m/d/Y');
                $event_starttime_tx = $dt->format('H:i');
                $myvalues['event_date_tx'] = $event_date_tx;
                $myvalues['event_starttime_tx'] = $event_starttime_tx;
            } else {
                $myvalues['event_date_tx'] = NULL;
                $myvalues['event_starttime_tx'] = NULL;
            }
            $myvalues['duration_am'] = $record['duration_am'];
            $myvalues['location_tx'] = $record['location_tx'];
            $myvalues['notes_tx'] = $record['notes_tx'];
            $myvalues['notes_critical_yn'] = $record['notes_critical_yn'];
            $myvalues['confirmed_by_patient_dt'] = $record['confirmed_by_patient_dt'];
            $myvalues['canceled_reason_tx'] = $record['canceled_reason_tx'];
            $myvalues['canceled_dt'] = $record['canceled_dt'];
            
            $aPrevNotes = array();
            
            //TODO -- Query the replaced table for all previous notes.
            
            $myvalues['prev_notes'] = $aPrevNotes;
        } else {
            $myvalues['event_date_tx'] = NULL;
            $myvalues['event_starttime_tx'] = NULL;
            $myvalues['duration_am'] = NULL;
            $myvalues['location_tx'] = NULL;
            $myvalues['notes_tx'] = NULL;
            $myvalues['notes_critical_yn'] = NULL;
            $myvalues['confirmed_by_patient_dt'] = NULL;
            $myvalues['canceled_reason_tx'] = NULL;
            $myvalues['canceled_dt'] = NULL;
            $myvalues['prev_notes'] = array();
        }
        
        return $myvalues;
    }
    
    function validateDate($date, $format = 'm/d/Y')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    function validateTime($time, $format = 'H:i')
    {
        $d = \DateTime::createFromFormat($format, $time);
        $trimmed = trim($time);
        if(strlen($trimmed) == 5)
        {
            //No padding needed
            return $d && $d->format($format) == $trimmed;
        } else {
            //Pad with a leading zero because PHP will pad the formatted one.
            return $d && $d->format($format) == '0'.$trimmed;
        }
    }

    function getDateTime($MMDDYYYY, $HHMM, $format = 'm/d/Y H:i')
    {
        return \DateTime::createFromFormat($format, $MMDDYYYY . ' ' . $HHMM);
    }

    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        $bHaveGoodDate = FALSE;
        $bHaveGoodTime = FALSE;
        $bHaveGoodDuration = FALSE;
        $bLooksLikeCancelRequest = FALSE;
        $bLooksLikeCollabRequest = FALSE;

        //Clean up some values.
        $myvalues['location_tx']    = trim($myvalues['location_tx']);
        $myvalues['duration_am']    = trim($myvalues['duration_am']);
        $myvalues['notes_tx']       = trim($myvalues['notes_tx']);
        $myvalues['canceled_reason_tx'] = trim($myvalues['canceled_reason_tx']);
        
        if($myvalues['canceled_reason_tx'] > '')
        {
            $bLooksLikeCancelRequest = FALSE;
        }

        
        if($myvalues['duration_am'] > '')
        {
            $bHaveGoodDuration = is_numeric($myvalues['duration_am']);
            if(!$bHaveGoodDuration)
            {
                form_set_error('duration_am', 'The value "'.$myvalues['duration_am'].'" is not a valid amount of minutes');
                $bGood = FALSE;
            }
        }

        if($myvalues['notes_tx'] == '' && $myvalues['notes_critical_yn'] == 1)
        {
            form_set_error('notes_critical_yn', 'Cannot indicate critical notes when there are no notes.');
            $bGood = FALSE;
        }
        
        $myvalues['event_date_tx'] = trim($myvalues['event_date_tx']);
        $myvalues['event_starttime_tx'] = trim($myvalues['event_starttime_tx']);
        if($myvalues['event_date_tx'] > '')
        {
            $bHaveGoodDate = $this->validateDate($myvalues['event_date_tx']);
            if(!$bHaveGoodDate)
            {
                form_set_error('event_date_tx', 'The value "'.$myvalues['event_date_tx'].'" is not a MM/DD/YYYY date');
                $bGood = FALSE;
            }
            if($myvalues['event_starttime_tx'] > '')
            {
                $bHaveGoodTime = $this->validateTime($myvalues['event_starttime_tx']);
                if(!$bHaveGoodTime)
                {
                    form_set_error('event_starttime_tx', 'The value "'.$myvalues['event_starttime_tx'].'" is not a valid HH:MM 24 hour time');
                    $bGood = FALSE;
                }
            }
            if($myvalues['confirmed_by_patient_yn'] == 1 && $myvalues['event_starttime_tx'] == '')
            {
                form_set_error('event_starttime_tx', 'Confirmation cannot be set without a date and time');
                $bGood = FALSE;
            }
            if($myvalues['location_tx'] > '' && $bHaveGoodDate && $bHaveGoodTime)
            {
                //Make sure this does not overlap an existing appointment.
                $duration = ($bHaveGoodDuration ? $myvalues['duration_am'] : 0);
                $nSiteID = $this->m_oContext->getSiteID();
                $nIEN = $myvalues['tid'];
                $dt = $this->getDateTime($myvalues['event_date_tx'],$myvalues['event_starttime_tx']);
                $start_scheduled_dt = $dt->format("Y-m-d H:i:00");
                $dt = $dt->add(new \DateInterval('PT' . $duration .'M'));
                $end_scheduled_dt = $dt->format("Y-m-d H:i:00");
                $query = db_select('raptor_schedule_track', 'n');
                $query->fields('n');
                $query->condition('siteid',$nSiteID,'=');
                $query->condition('IEN',$nIEN,'<>');
                $query->isNull('canceled_dt');
                $query->isNotNull('duration_am');
                $query->condition('location_tx',$myvalues['location_tx'],'=');
                $or1 = db_or();
                //$and1 = db_and();
                //$and1->condition('scheduled_dt',$start_scheduled_dt,'>=');
                //$and1->condition('scheduled_dt',$end_scheduled_dt,'<');
                //$or1->condition($and1);
                $or1->where('DATE_ADD(`scheduled_dt`,INTERVAL `duration_am` MINUTE) > ' . "'". $start_scheduled_dt . "'". ' AND DATE_ADD(`scheduled_dt`, INTERVAL `duration_am` MINUTE) <= ' . "'". $end_scheduled_dt. "'");
                $or1->where('`scheduled_dt` <= ' . "'". $start_scheduled_dt . "'". ' AND DATE_ADD(`scheduled_dt`, INTERVAL `duration_am` MINUTE) >= ' . "'". $start_scheduled_dt. "'");
                $or1->where('`scheduled_dt` < ' . "'". $end_scheduled_dt . "'". ' AND DATE_ADD(`scheduled_dt`, INTERVAL `duration_am` MINUTE) > ' . "'". $end_scheduled_dt. "'");
                $or1->where('`scheduled_dt` >= ' . "'". $start_scheduled_dt . "'". ' AND DATE_ADD(`scheduled_dt`, INTERVAL `duration_am` MINUTE) <= ' . "'". $end_scheduled_dt. "'");
                $query->condition($or1);
                //$query->condition('scheduled_dt',$start_scheduled_dt,'>=');
                //$query->condition('scheduled_dt',$end_scheduled_dt,'<=');
                $result = $query->execute();
                if($result->rowCount() > 0)
                {
                    $record = $result->fetchAssoc();
                    form_set_error('location_tx', $myvalues['location_tx'] . ' already planned for exam ' . $record['IEN'] . ' (starts ' . $record['scheduled_dt'] . ' with duration of '.$record['duration_am'].' minutes)');
                    $bGood = FALSE;
                }
                
                //drupal_set_message('Look room=['.$myvalues['location_tx'].'] start>>> ['. print_r($start_scheduled_dt,TRUE) .'] and end>>>[' . print_r($end_scheduled_dt,TRUE) . ']');
            }
        } else {
            //Not date has been set.
            if($myvalues['event_starttime_tx'] > '')
            {
                form_set_error('event_date_tx', 'Date is missing but time of "'.$myvalues['event_starttime_tx'].'" was provided');
                $bGood = FALSE;
            }
            if($myvalues['confirmed_by_patient_yn'] == 1)
            {
                form_set_error('event_date_tx', 'Confirmation cannot be set without a date and time');
                $bGood = FALSE;
            }
        }
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        //Try to create the record now
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $sTrackingID = $this->m_oTT->getTrackingID($nSiteID, $nIEN);
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i", time());
        
        if(!$this->looksValid($form, $myvalues))
        {
            throw new \Exception("Cannot save values because form is not valid!");
        }
     
        //Declare flags
        $flag_collab = FALSE;
        $flag_cancel = FALSE;
        
        //Get the datetime formatted for saving into the database.
        $scheduled_dt = NULL;
        if(trim($myvalues['event_date_tx']) > '')
        {
            $scheduled_dt = \DateTime::createFromFormat('m/d/Y',$myvalues['event_date_tx']);
        }
        if($scheduled_dt !== NULL && trim($myvalues['event_starttime_tx']) > '')
        {
            $a = explode(':', $myvalues['event_starttime_tx']);
            $scheduled_dt->setTime($a[0],$a[1]);
        }
        if($scheduled_dt !== NULL)
        {
            $save_scheduled_dt = $scheduled_dt->format("Y-m-d H:i:00");
        } else {
            $save_scheduled_dt = NULL;
        }
        
        $original_cancel_dt = NULL;
        $original_confirmation_dt = NULL;
        $original_canceled_reason_tx = NULL;
        try
        {
            //If we are here, make sure we end up with a raptor_ticket_tracking record too.
            db_merge('raptor_ticket_tracking')
                ->key(
                        array('siteid'=>$nSiteID
                                ,'IEN' => $nIEN,
                    ))
                ->fields(array(
                        'updated_dt'=>$updated_dt,
                    ))
                ->execute();    
            
            //Do we already have a record to replace?
            $query = db_select('raptor_schedule_track', 'n');
            $query->fields('n');
            $query->condition('siteid',$nSiteID,'=');
            $query->condition('IEN',$nIEN,'=');
            $result = $query->execute();
            if($result->rowCount() == 1)
            {
                //Yes, move this record into replaced table.
                $record = $result->fetchAssoc();
                db_insert('raptor_schedule_track_replaced')
                    ->fields(array(
                            'siteid' => $nSiteID,
                            'IEN' => $nIEN,
                            'scheduled_dt' => $record['scheduled_dt'],
                            'duration_am' => $record['duration_am'],
                            'location_tx' => $record['location_tx'],
                            'notes_tx' => $record['notes_tx'],
                            'notes_critical_yn' => $record['notes_critical_yn'],
                            'confirmed_by_patient_dt' => $record['confirmed_by_patient_dt'],
                            'canceled_reason_tx' => $record['canceled_reason_tx'],
                            'canceled_dt' => $record['canceled_dt'],
                            'author_uid' => $record['author_uid'],
                            'original_created_dt' => $record['created_dt'],
                            'replaced_dt' => $updated_dt,
                    ))
                    ->execute();

                //Grab original values so we dont keep changing the dates on every save.
                $original_confirmation_dt = $record['confirmed_by_patient_dt'];
                $original_canceled_dt = $record['canceled_dt'];
                $original_canceled_reason_tx = $record['canceled_reason_tx'];
            }
        } catch (\Exception $ex) {
            error_log("Failed to schedule ticket $nSiteID - $nIEN because ".$ex->getMessage());
            throw $ex;
        }
        
        //Suggested an assignment?
        if($myvalues['suggested_uid'] == '')
        {
            $myvalues['suggested_uid'] = NULL;
            $suggested_uid = '';
        } else {
            $suggested_uid = $myvalues['suggested_uid'];
        }
        if(!isset($myvalues['suggested_alreadyset_uid']))
        {
            $myvalues['suggested_alreadyset_uid'] = NULL;
            $suggested_alreadyset_uid = '';
        } else {
            $suggested_alreadyset_uid = $myvalues['suggested_alreadyset_uid'];
        }
        
        $bCond1 = ($suggested_uid !== $suggested_alreadyset_uid);
        if( $bCond1 ) 
        {
            //Yes, assign the ticket to the selected user.
            if($myvalues['suggested_uid'] == NULL)
            {
                $this->m_oTT->setCollaborationUser($sTrackingID, $nUID, 'Scheduler clearing', NULL);
            } else {
                $this->m_oTT->setCollaborationUser($sTrackingID, $nUID, 'Needs Attention', $myvalues['suggested_uid']);
            }
            $flag_collab = TRUE;
        }
        
        //Create the schedule track record now
        try
        {
            if(trim($myvalues['canceled_reason_tx']) > '')
            {
                if($myvalues['canceled_reason_tx'] !== $original_canceled_reason_tx)
                {
                    //Use a new date.
                    $candidate_canceled_dt = $updated_dt;
                    $flag_cancel = TRUE;
                } else {
                    //Use original date.
                    $candidate_canceled_dt = $original_canceled_dt;
                }
            } else {
                //No cancelation.
                $candidate_canceled_dt = NULL;
            }
            
            if($flag_cancel)
            {
                $newcomment = 'Requested cancel/replace';
            } else if($flag_collab) {
                $newcomment = 'Assigned a suggested collaborator';
            } else {
                $newcomment = 'Updated scheduling information';
            }
            $newcomment = $newcomment."\n".$myvalues['notes_tx'];
            
            db_merge('raptor_schedule_track')
                ->key(array('siteid' => $nSiteID,
                        'IEN' => $nIEN,))
                ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'scheduled_dt' => $save_scheduled_dt,
                        'duration_am' => (is_numeric($myvalues['duration_am']) && $myvalues['duration_am'] !== '0' ? $myvalues['duration_am'] : NULL ),
                        'location_tx' => $myvalues['location_tx'],
                        'notes_tx' => $newcomment,
                        'notes_critical_yn' => $myvalues['notes_critical_yn'],
                        'confirmed_by_patient_dt' => ($myvalues['confirmed_by_patient_yn'] == 1 ? ($original_confirmation_dt == NULL ? $updated_dt : $original_confirmation_dt) : NULL) ,
                        'canceled_reason_tx' => $myvalues['canceled_reason_tx'],
                        'canceled_dt' => $candidate_canceled_dt,
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                ))
                ->execute();
        }
        catch(\Exception $e)
        {
            error_log('Failed to create raptor_schedule_track: ' . $e . "\nDetails..." . print_r($oInsert,true));
            //form_set_error('notes_tx','Failed to save notes for this ticket!');
            throw new \Exception('Failed to save notes for this ticket!');
        }

        if($flag_cancel)
        {
            //If we made it here, go ahead and mark the order as inactive.
            $sNewWFS = 'IA';
            $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN
                    , $nUID, $sNewWFS, $sCWFS, $updated_dt);
        } else {
            if($sCWFS == 'IA')
            {
                //Move it back to active state.
                $sNewWFS = 'AC';
                $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN
                        , $nUID, $sNewWFS, $sCWFS, $updated_dt);
            }
        }
        
        //Write success message
        if($flag_cancel)
        {
            $usermsg = 'Requested cancel/replace';
        } else if($flag_collab) {
            $usermsg = 'Assigned a suggested collaborator';
        } else {
            if($sCWFS == 'IA' && $sNewWFS = 'AC')
            {
                $usermsg = 'Now active instead of cancel/replace';
            } else {
                $sforinfo=trim($save_scheduled_dt . ($myvalues['location_tx'] > '' ? ' in ' . $myvalues['location_tx'] : ''));
                if($sforinfo != NULL && $sforinfo != '')
                {
                    $sforinfo = ' for ' . $sforinfo;
                }
                $usermsg = 'Updated scheduling information'.$sforinfo;
            }
        }
        drupal_set_message('Pass Box settings saved for ' . $sTrackingID . ' (' . $myvalues['procName'] .') ' . $usermsg); 
        
        return 1;
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $oUser = $this->m_oContext->getUserInfo();
        $localmsg = NULL;
        if(!$oUser->getPrivilegeSetting('SP1'))
        {
            $disabled = TRUE;   //Do not let this user edit the schedule information.
            $localmsg = 'Your account does not have privileges to edit the schedule information.';
        }

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section id='raptor-admin-container' class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        //Hidden values
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        $form['hiddenthings']['suggested_alreadyset_uid'] = array('#type' => 'hidden', '#value' => $myvalues['suggested_alreadyset_uid']);
        $form['data_entry_area1']['toppart']['heading'] = array(
            '#markup' => '<table id="raptor-schedule-table" width="100%" ><tbody>'
                . '<tr>'
                . '<th>Tracking ID</th><td>' . $this->m_oContext->getFullyQualifiedTicketID($myvalues['tid']) . '&nbsp;</td>'
                . '<th>Patient Name</th><td>' . $myvalues['PatientName']  . '</td></tr>'
                . '<tr>'
                . '<th>Study</th><td>' . $myvalues['procName']  . '&nbsp;</td>'
                . '<th>Patient DOB</th><td>' . $myvalues['PatientDOB'] . '</td></tr>'
                . '<tr>'
                . '<th>Requested Date</th><td>' . $myvalues['RequestedDate'] . '&nbsp;</td>'
                . '<th>Urgency</th><td>' . $myvalues['Urgency'] . '</td>'
                . '</tr>'
                . '</tbody></table>',
        );

        $form['data_entry_area1']['toppart']['schedulingdetails'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Scheduling details (Not integrated with enterprise scheduling system)'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $form['data_entry_area1']['toppart']['schedulingdetails']['location_tx'] = array(
            '#type' => 'textfield',
            '#title' => t('Location'),
            '#default_value' => $myvalues['location_tx'],
            '#size' => 20,
            '#maxlength' => 20,
            '#required' => FALSE,
        );        

        if($disabled || !is_array($myvalues['rooms']))
        {
            
        } else {
            $htmlroomoptions = '<ul>';
            $aRooms = $myvalues['rooms'];
            foreach($aRooms as $aRoom)
            {
                $htmlroomoptions .= '<li><span class="location-option selectable-text"><a href="javascript:setScheduleTextboxByName(' 
                        . "'location_tx','" . $aRoom[0] . "' )" . '"'
                        . ' title="'.$aRoom[1].'" >'.$aRoom[0].'</a></span>';
            }
            $htmlroomoptions .= '</ul>';
            $form['data_entry_area1']['toppart']['schedulingdetails']['rooms'] = array(
                '#markup' => $htmlroomoptions,
            );        
        }
        
        
        
        $form['data_entry_area1']['toppart']['schedulingdetails']['event_date_tx'] = array(
            '#type' => 'textfield',
            '#title' => t('Event Date'),
            '#default_value' => $myvalues['event_date_tx'],
            '#size' => 20,
            '#maxlength' => 20,
            '#required' => FALSE,
            '#attributes' => array('class' => array('datepicker'))
        );        
        
        $form['data_entry_area1']['toppart']['schedulingdetails']['event_starttime_tx'] = array(
            '#type' => 'textfield',
            '#title' => t('Start Time'),
            '#default_value' => $myvalues['event_starttime_tx'],
            '#size' => 20,
            '#maxlength' => 20,
            '#required' => FALSE,
        );        

        if(!$disabled)
        {
            $htmlmarkup = 
                '<a id="raptor-schedule-time" href="#"'
                . ' title="Use current time as the start time">Use Current Time</a>';
            $form['data_entry_area1']['toppart']['schedulingdetails']['event_starttime_now'] = array(
                '#markup' => $htmlmarkup,
            );        
        }
        
        $form['data_entry_area1']['toppart']['schedulingdetails']['duration_am'] = array(
            '#type' => 'textfield',
            '#title' => t('Duration (minutes)'),
            '#default_value' => $myvalues['duration_am'],
            '#size' => 3,
            '#maxlength' => 3,
            '#required' => FALSE,
        );        
        
        //Provide clickable duration options on same row as duration field
        if($disabled)
        {
            $myvalues['durations'] = array();   //Empty list of minutes
        } else {
            if(!isset($myvalues['durations']) 
                    || !is_array($myvalues['durations'])
                    || count($myvalues['durations']) == 0)
            {
                //Default hardcoded list.
                $myvalues['durations'] = array('10','15','20','30', '45', '60');
            }
        }
        $htmldurations = '<ul>';
        $aDurations = $myvalues['durations'];
        foreach($aDurations as $aDuration)
        {
            $htmldurations .= '<li><span class="duration-option selectable-text"><a href="javascript:setScheduleTextboxByName(' 
                    . "'duration_am','" . $aDuration . "' )" . '"'
                    . ' title="'.$aDuration.' minutes" >'.$aDuration.'</a></span>';
        }
        $htmldurations .= '</ul>';
        $form['data_entry_area1']['toppart']['schedulingdetails']['durations'] = array(
            '#markup' => $htmldurations,
        );        
        
        $optionsConfirmed = array(0 => t('No') 
            , 1 => t('Yes' . (isset($myvalues['confirmed_by_patient_dt']) 
                ? ' ('.$myvalues['confirmed_by_patient_dt'].')' : '')  ));
        $form['data_entry_area1']['toppart']['schedulingdetails']['confirmed_by_patient_yn'] = array(
            '#type' => 'radios',
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#title' => t('Confirmed by patient'),
            '#default_value' => isset($myvalues['confirmed_by_patient_dt']) ? 1 : 0,
            '#options' => $optionsConfirmed,
            '#description' => t('Has the patient confirmed this appointment?'),
        );

        $form['data_entry_area1']['middlepart']['rqstalterticket'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Request Cancel or Replace (Moves in RAPTOR but does not change VISTA attributes)'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $aCancelOptions = \raptor\MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $fulloptionlist = array();
        $fulloptionlist[''] = '';   //Important that the key is empty string not just empty brackets!!!!
        foreach($aCancelOptions as $option)
        {
            $fulloptionlist[] = $option;
        }
        $form['data_entry_area1']['middlepart']['rqstalterticket']['canceled_reason_tx'] = array(
            "#type" => "select",
            "#title" => t("Reason for cancellation or replace order request"),
            "#options" => $fulloptionlist,
            /*
            "#options" => array(
                '' => t(''),
                'Patient requested' => t('Patient requested'),
                'VA requested' => t('VA requested'),
                'Other' => t('Other'),
            ),
             */
            '#default_value' => $myvalues['canceled_reason_tx'],
            "#description" => t("Select reason for requestiong cancellation of this procedure." . (isset($myvalues['canceled_dt']) ? ' ( Canceled '.$myvalues['canceled_dt'].' )' : '')),
            "#required" => FALSE,
            );        
        
        if(!$myvalues['show_collaboration_options'])
        {
            $form['hiddenthings']['suggested_uid'] = array('#type' => 'hidden', '#value' => $myvalues['suggested_uid']);
        } else {
            if(is_array($myvalues['assignment_options']))
            {
                $options = $myvalues['assignment_options'];
            } else {
                $options[''] = '';
            }
            $form['data_entry_area1']['middlepart']['suggested_uid'] = array(
                "#type" => "select",
                "#title" => t("Protocol attention of"),
                "#options" => $options,
                '#default_value' => $myvalues['suggested_uid'],
                "#description" => t('Suggested for the attention of a specific Resident or Radiologist' 
                        . ($myvalues['suggested_alreadyset_note'] != NULL ? ' (' . $myvalues['suggested_alreadyset_note'] . ')' : '')),
                "#required" => FALSE,
                );        

        }
        $form['data_entry_area1']['bottom']['notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Notes'),
            '#rows'          => 3,
            '#disabled'      => $disabled,
            '#default_value' => $myvalues['notes_tx'],
        );

        $form['hiddenthings']['notes_critical_yn'] = array('#type' => 'hidden', '#value' => 0); //Else errors later
        /*$optionsConfirmed = array(0 => t('No') , 1 => t('Yes'));
        $form['data_entry_area1']['bottom']['notes_critical_yn'] = array(
            '#type' => 'radios',
            '#title' => t('Notes contain patient care information?'),
            '#default_value' => isset($myvalues['notes_critical_yn']) ? $myvalues['notes_critical_yn'] : 0,
            '#options' => $optionsConfirmed,
            '#description' => t('Should system ask Radiologist and Technologist to confirm reading the note?'),
        );*/
        
        if($localmsg != NULL)
        {
            $form['data_entry_area1']['bottom']['buttonmsg'] = array('#markup' => '<p class="action-button-message">'.$localmsg.'</p>');
        }
        if(!$disabled)
        {
            $form['data_entry_area1']['action_buttons']['schedule'] = array('#type' => 'submit'
                    , '#attributes' => array('class' => array('simple-action-button'))
                    , '#value' => t('Save Settings')
                    , '#disabled' => $disabled
            );
            
            /*$form['data_entry_area1']['action_buttons']['addnewschedule'] = array('#type' => 'submit'
                    , '#attributes' => array('class' => array('simple-action-button'))
                    , '#value' => t('Save these Settings and Add Another Schedule Event for Same Ticket')
                    , '#disabled' => $disabled
            );*/
        }
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Close without Saving Settings">');
        
        return $form;
    }
}