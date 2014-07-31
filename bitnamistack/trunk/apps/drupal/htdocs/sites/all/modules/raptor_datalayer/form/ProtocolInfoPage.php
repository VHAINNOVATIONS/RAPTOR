<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("ProtocolPageUtils.inc");
require_once("ProtocolInfoUtility.php");
require_once("FormHelper.php");

/**
 * This class returns the Protocol Information input content
 *
 * @author FrankWin7VM
 */
class ProtocolInfoPage
{
    private $m_oContext = null;
    private $m_oUtility = null;
    private $m_oCIE = NULL;
    
    /**
     * Create an instance of the procotol info page.
     * @param \raptor\ContraIndEngine $oCIE
     * @throws \Exception
     */
    function __construct($oCIE = NULL)
    {
        $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindication Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $loaded = module_load_include('inc','raptor_contraindications','core/Contraindication');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindication';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $loaded = module_load_include('inc','raptor_contraindications','core/Contraindications');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindications';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        $loaded = module_load_include('inc','raptor_contraindications','core/ContraSourceItem');
        if(!$loaded)
        {
            $msg = 'Failed to load the ContraSourceItem';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }

        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oUtility = new \raptor\ProtocolInfoUtility();
        $this->m_oCIE = NULL;
        
    }

    /**
     * Set all key values of the myvalues array as null
     * @param array $myvalues for ALL values
     */
    static function setAllValuesNull(&$myvalues)
    {
        $myvalues['hydration_oral_id'] = NULL;
        $myvalues['hydration_iv_id'] = NULL;
        $myvalues['hydration_oral_customtx'] = NULL;
        $myvalues['hydration_iv_customtx'] = NULL;

        $myvalues['sedation_oral_id'] = NULL;
        $myvalues['sedation_iv_id'] = NULL;
        $myvalues['sedation_oral_customtx'] = NULL;
        $myvalues['sedation_iv_customtx'] = NULL;
        
        $myvalues['contrast_enteric_id'] = NULL;
        $myvalues['contrast_iv_id'] = NULL;
        $myvalues['contrast_enteric_customtx'] = NULL;
        $myvalues['contrast_iv_customtx'] = NULL;

        $myvalues['radioisotope_enteric_id'] = NULL;
        $myvalues['radioisotope_iv_id'] = NULL;
        $myvalues['radioisotope_enteric_customtx'] = NULL;
        $myvalues['radioisotope_iv_customtx'] = NULL;
        //MORE TODO
    }

    /**
     * Get the values to populate the form.
     * @param type $tid the tracking ID
     * @return type result of the queries as an array
     */
    function getFieldValues($tid)
    {
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getOneWorklistRow($tid);
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        //$sModality = $aOneRow[5];

        $oDD = new \raptor\DashboardData();
        $aDD = $oDD->getDashboardDetails($this->m_oContext);        
        
        //die('LOOKNOW>>>>' . print_r($aDD,TRUE));
        
        if($this->m_oCIE == NULL)
        {
            $aPatientInfoForCIE = array();    //TODO move this code elsewhere
            $aPatientInfoForCIE['GENDER'] = $aDD['PatientGender'];
            $aPatientInfoForCIE['AGE'] = $aDD['PatientAge'];
            $aPatientInfoForCIE['WEIGHT_KG'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_10DAYS'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_15DAYS'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_30DAYS'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_45DAYS'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_60DAYS'] = NULL;
            $aPatientInfoForCIE['MIN_EGFR_90DAYS'] = NULL;
            try
            {
                $oCIE = new \raptor\ContraIndEngine($aPatientInfoForCIE);        
            } catch (\Exception $ex) {
                $oCIE = NULL;
                drupal_set_message('Failed to create the contraindications engine because ' . $ex->getMessage(), 'error');
            }
            $this->m_oCIE = $oCIE;
        }
        
        
        //drupal_set_message(print_r($aOneRow,TRUE),'warning');
        $prev_protocolnotes_tx = $this->m_oUtility->getSchedulerNotesMarkup($nSiteID,$nIEN);
        $prev_protocolnotes_tx .= $this->m_oUtility->getPreviousNotesMarkup('raptor_ticket_protocol_notes',$nSiteID,$nIEN,'core-protocol-note');
        $prev_exam_notes_tx = $this->m_oUtility->getPreviousNotesMarkup('raptor_ticket_exam_notes',$nSiteID,$nIEN,'exam-note');
        $prev_qa_notes_tx = $this->m_oUtility->getPreviousNotesMarkup('raptor_ticket_qa_notes',$nSiteID,$nIEN,'qa-note');
        $prev_suspend_notes_tx = $this->m_oUtility->getPreviousNotesMarkup('raptor_ticket_suspend_notes',$nSiteID,$nIEN,'suspend-note');
        
        //drupal_set_message('abcPREVNOTES>>>> <ol>' . $prev_protocolnotes_tx . '</ol>');
        
        //Get app existing protocol data
        $myvalues = array();
        ProtocolInfoPage::setAllValuesNull($myvalues);  //Initialize all values to NULL first.
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        $myvalues['prev_protocolnotes_tx'] = $prev_protocolnotes_tx;
        $myvalues['prev_exam_notes_tx'] = $prev_exam_notes_tx;
        $myvalues['prev_qa_notes_tx'] = $prev_qa_notes_tx;
        $myvalues['prev_suspend_notes_tx'] = $prev_suspend_notes_tx;
        $myvalues['DefaultValues'] = null;
        $myvalues['protocolnotes_tx'] = '';
        $myvalues['exam_notes_tx'] = '';
        $myvalues['qa_notes_tx'] = '';
        
        //More values in database?
        $query = db_select('raptor_ticket_protocol_settings', 'n');
        $query->fields('n')
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=');
        $result = $query->execute();
        if($result->rowCount() > 0)
        {
            if($result->rowCount() > 1)
            {
                //Critical this should NEVER be allowed to happen!
                die('Too many protocol records for ' . $nIEN . '!');
            }
            $record = $result->fetchAssoc();
            $myvalues['protocol1_nm'] = $record['primary_protocol_shortname'];
            $myvalues['protocol2_nm'] = $record['secondary_protocol_shortname'];
            if($record['hydration_none_yn'] == 1)
            {
                $myvalues['hydration_cd'] = '';
            } else {
                if(isset($record['hydration_oral_tx']))
                {
                    $myvalues['hydration_cd'] = 'oral';
                    $myvalues['hydration_oral_id'] = $record['hydration_oral_tx'];
                } else if(isset($record['hydration_iv_tx'])) {
                    $myvalues['hydration_cd'] = 'iv';
                    $myvalues['hydration_iv_id'] = $record['hydration_iv_tx'];
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }
            }
            $myvalues['hydration_oral_customtx'] = $record['hydration_oral_tx'];
            $myvalues['hydration_iv_customtx'] = $record['hydration_iv_tx'];
            
            $myvalues['sedation_none_yn'] = $record['sedation_none_yn'];
            if($record['sedation_none_yn'] == 1)
            {
                $myvalues['sedation_cd'] = '';
            } else {
                if(isset($record['sedation_oral_tx']))
                {
                    $myvalues['sedation_cd'] = 'oral';
                    $myvalues['sedation_oral_id'] = $record['sedation_oral_tx'];
                } else if(isset($record['sedation_iv_tx'])) {
                    $myvalues['sedation_cd'] = 'iv';
                    $myvalues['sedation_iv_id'] = $record['sedation_iv_tx'];
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }
            }
            $myvalues['sedation_oral_customtx'] = $record['sedation_oral_tx'];
            $myvalues['sedation_iv_customtx'] = $record['sedation_iv_tx'];
            
            $myvalues['contrast_cd'] = array('none'=>0,'enteric'=>0,'iv'=>0);
            $myvalues['contrast_none_yn'] = $record['contrast_none_yn'];
            if($record['contrast_none_yn'] == 1)
            {
                $myvalues['contrast_cd']['none'] = 'none';
            } else {
                if(isset($record['contrast_enteric_tx']))
                {
                    $myvalues['contrast_cd']['enteric'] = 'enteric';
                    $myvalues['contrast_enteric_id'] = $record['contrast_enteric_tx'];
                } 
                if(isset($record['contrast_iv_tx'])) {
                    $myvalues['contrast_cd']['iv'] = 'iv';
                    $myvalues['contrast_iv_id'] = $record['contrast_iv_tx'];
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }
            }
            $myvalues['contrast_enteric_customtx'] = $record['contrast_enteric_tx'];
            $myvalues['contrast_iv_customtx'] = $record['contrast_iv_tx'];
            
            $myvalues['radioisotope_cd'] = array('none'=>0,'enteric'=>0,'iv'=>0);
            $myvalues['radioisotope_none_yn'] = $record['radioisotope_none_yn'];
            if($record['radioisotope_none_yn'] == 1)
            {
                $myvalues['radioisotope_cd']['none'] = 'none';
            } else {
                if(isset($record['radioisotope_enteric_tx']))
                {
                    $myvalues['radioisotope_cd']['enteric'] = 'enteric';
                    $myvalues['radioisotope_enteric_id'] = $record['radioisotope_enteric_tx'];
                } 
                if(isset($record['radioisotope_iv_tx'])) {
                    $myvalues['radioisotope_cd']['iv'] = 'iv';
                    $myvalues['radioisotope_iv_id'] = $record['radioisotope_iv_tx'];
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }
            }
            $myvalues['radioisotope_enteric_customtx'] = $record['radioisotope_enteric_tx'];
            $myvalues['radioisotope_iv_customtx'] = $record['radioisotope_iv_tx'];

            if($record['consent_req_kw'] == NULL || $record['consent_req_kw'] == 'unknown')
            {
                $myvalues['consentreq_cd'] = 'unknown';
            } else if($record['consent_req_kw'] == 'yes') {
                $myvalues['consentreq_cd'] = 'yes';
            } else if($record['consent_req_kw'] == 'no') {
                $myvalues['consentreq_cd'] = 'no';
            } else {
                //die('Data corruption of profile data for IEN ' . $nIEN);
            }
            
        } else {
            //No saved data, simply provide initial values.
            $myvalues['protocol1_nm'] = NULL;
            $myvalues['protocol2_nm'] = NULL;
            $myvalues['hydration_cd'] = NULL;
            $myvalues['contrast_cd'] = NULL;
            $myvalues['sedation_cd'] = NULL;
            $myvalues['radioisotope_cd'] = NULL;
            $myvalues['consentreq_cd'] = NULL;
        }
        
        //drupal_set_message('LOOK>>>'.print_r($myvalues,TRUE));
        
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, &$form_state)
    {
        $bGood = TRUE;
        $myvalues = $form_state['values'];
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        $clickedbutton = $form_state['clicked_button'];
        $clickedvalue = $clickedbutton['#value'];
        
        //TODO - special checks here
        if(substr($clickedbutton["#value"],0,7) == 'Approve')
        {
            //################
            // APPROVAL BLOCK
            //################
            //
            //Make sure we are okay to approve this ticket.
            if($sCWFS !== 'AC' && $sCWFS !== 'AP' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
            {
                form_set_error('protocol1_nm','Only tickets in the active/approved/collaborate/review states can be approved!  This one is in the ' .$sWFS. ' state!');
                $bGood = FALSE;
            }
            $check = substr(trim($myvalues['protocol1_nm']),0,1);
            if($check === '-' || $check == '')
            {
                form_set_error('protocol1_nm','Must make a primary protocol selection.');
                $bGood = FALSE;
            }
        } else
        if(substr($clickedbutton["#value"],0,7) == 'Request')
        {
            //#######################
            // REQUEST APPROVAL BLOCK
            //#######################
            //
            //Make sure we are okay to approve this ticket.
            if($sCWFS !== 'AC' && $sCWFS !== 'AP' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
            {
                form_set_error('protocol1_nm','Only tickets in the active/approved/collaborate/review states can be approved!  This one is in the ' .$sWFS. ' state!');
                $bGood = FALSE;
            }
            $check = substr(trim($myvalues['protocol1_nm']),0,1);
            if($check === '-')
            {
                form_set_error('protocol1_nm','Must make a primary protocol selection.');
                $bGood = FALSE;
            }
        } else
        if(substr($clickedbutton["#value"],0,11) == 'Acknowledge')
        {
            //##################
            // ACKNOWLEDGE BLOCK
            //##################

        } else
        if(substr($clickedbutton["#value"],0,4) == 'Exam')
        {
            //####################
            // EXAM COMPLETE BLOCK
            //####################

        } else
        if(substr($clickedbutton["#value"],0,2) == 'QA')
        {
            //####################
            // QA COMPLETE BLOCK
            //####################

        } else
        if(substr($clickedbutton["#value"],0,7) == 'Suspend' || substr($clickedbutton["#value"],0,6) == 'Remove')
        {
            //################
            // SUSPEND BLOCK
            //################

            if(!isset($myvalues['suspend_notes_tx']) || trim($myvalues['suspend_notes_tx']) == '')
            {
                if(!isset($myvalues['suspend_notes_tx']))
                {
                    drupal_set_message('Cannot suspend a ticket without an explanation.','error');
                } else {
                    form_set_error('suspend_notes_tx','Cannot suspend a ticket without an explanation.');
                }
                $bGood = FALSE;
            }
            
        } else
        if(substr($clickedbutton["#value"],0,7) == 'Reserve')
        {
            //################
            // RESERVE BLOCK
            //################
            
            //Make sure we are okay to reserve this ticket.
            if($sCWFS !== 'AC' && $sCWFS !== 'AP' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
            {
                form_set_error('protocol1_nm','Only tickets in the active or approved or collaborate status can be reserved!  This one is in the [' .$sCWFS. '] state!');
                $bGood = FALSE;
            }
            
        }
        
        if(!$bGood)
        {
            drupal_set_message('Form needs corrections','warning');
        }
        return $bGood;
    }    
    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase($clickedbutton, $myvalues)
    {
        $bSuccess = TRUE;   //Assume happy case.
        
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());

        if(substr($clickedbutton["#value"],0,7) == 'Approve')
        {
            //################
            // APPROVAL BLOCK
            //################
            
            $sNewWFS = 'AP';
            $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);
            
            //3 Write success message
            drupal_set_message('Approved ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,7) == 'Request')
        {
            //#######################
            // REQUEST APPROVAL BLOCK
            //#######################

            $sNewWFS = 'RV';
            $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);
            
            //3 Write success message
            drupal_set_message('Requested approval for ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,11) == 'Acknowledge')
        {
            //##################
            // ACKNOWLEDGE BLOCK
            //##################

            $sNewWFS = 'PA';
            $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
            
            //Write success message
            drupal_set_message('Acknowledged ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,4) == 'Exam')
        {
            //####################
            // EXAM COMPLETE BLOCK
            //####################

            $sNewWFS = 'EC';
            $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
            
            //Write success message
            drupal_set_message('Exam completed ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,2) == 'QA')
        {
            //####################
            // QA COMPLETE BLOCK
            //####################

            $sNewWFS = 'QA';
            $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
            
            //Write success message
            drupal_set_message('QA completed ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,7) == 'Suspend' || substr($clickedbutton["#value"],0,6) == 'Remove')
        {
            //################
            // SUSPEND BLOCK
            //################

            //Create the raptor_ticket_suspend_notes record now
            try
            {
                $oInsert = db_insert('raptor_ticket_suspend_notes')
                        ->fields(array(
                            'siteid' => $nSiteID,
                            'IEN' => $nIEN,
                            'notes_tx' => $myvalues['suspend_notes_tx'],
                            'author_uid' => $nUID,
                            'created_dt' => $updated_dt,
                        ))
                        ->execute();
            }
            catch(PDOException $e)
            {
                error_log('Failed to create raptor_ticket_suspend_notes: ' . $e . "\nDetails..." . print_r($oInsert,true));
                form_set_error('suspend_notes_tx','Failed to save notes for this ticket!');
                $bSuccess = FALSE;
            }

            $sNewWFS = 'IA';
            $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
            
            //Write success message
            drupal_set_message('Suspended ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else
        if(substr($clickedbutton["#value"],0,7) == 'Reserve')
        {
            //################
            // RESERVE BLOCK
            //################
            $successMsg = null;
            
            //Create the raptor_ticket_collaboration record now
            try
            {
                $result = db_select('raptor_ticket_collaboration','p')
                        ->fields('p')
                        ->condition('siteid',$nSiteID,'=')
                        ->condition('IEN',$nIEN,'=')
                        ->condition('collaborator_uid',$nUID,'=')
                        ->execute();
                $nRows = $result->rowCount();
                if($nRows > 0)
                {
                    $successMsg = 'Already reserved ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')';
                } else {
                    $result = db_select('raptor_ticket_collaboration','p')
                            ->fields('p')
                            ->condition('siteid',$nSiteID,'=')
                            ->condition('IEN',$nIEN,'=')
                            ->condition('collaborator_uid',$nUID,'<>')
                            ->execute();
                    $nRows = $result->rowCount();
                    if($nRows > 0)
                    {
                        $successMsg = 'Reserved (by you and ' . $nRows . ' other users) ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')';
                    }
                    $oInsert = db_insert('raptor_ticket_collaboration')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'requester_uid' => $nUID,
                                'requested_dt' => $updated_dt,
                                'requester_notes_tx' => 'Reserving for myself.',
                                'collaborator_uid' => $nUID,
                                'active_yn' => 1,
                            ))
                            ->execute();
                }
            }
            catch(PDOException $e)
            {
                error_log('Failed to create raptor_ticket_collaboration: ' . $e . "\nDetails..." . print_r($oInsert,true));
                form_set_error('protocol1_nm','Failed to reserve this ticket!');
                $bSuccess = FALSE;
            }

            $sNewWFS = 'CO'; 
            $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);

            //Write success message
            if($successMsg == null)
            {
                $successMsg = 'Reserved ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')';
            }
            drupal_set_message($successMsg);
        }
        //error_log("clicked button -> " . print_r($clickedbutton, true));
        return $bSuccess;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        //TODO
    }
    
    /**
     * Return all properties of a protocol.
     * @param type $protocolname
     * @return array with all the properties of the selected protocol
     */
    function getPropertiesFromProtocolName($protocolname)
    {
        if($protocolname != NULL)
        {
            //Look up the protocol in the library.
            $result = db_select('raptor_protocol_lib','p')
                    ->fields('p')
                    ->condition('name',$protocolname,'=')
                    ->execute();
            if($record = $result->fetchAssoc())
            {
                return $record; //['modality_abbr'];
            }
        }
        //Found nothing.
        return array(
            'modality_abbr' => NULL,
            'image_guided_yn' => NULL,
        );    
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        
        $userinfo = $this->m_oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        
        $protocolValues = $this->getPropertiesFromProtocolName($myvalues['procName']);
        $aCandidateData = array();  //TODO move this logic elsewhere and update via AJAX
        $aCandidateData['IS_DIAGNOSTIC_EXAM'] = NULL;
        $aCandidateData['IS_POSSIBLE_DUP_PROC'] = NULL;
        $aCandidateData['IS_IMG_GUIDED_EXAM'] = $protocolValues['image_guided_yn'];
        $aCandidateData['PROC_NM'] = $myvalues['procName'];
        $aCandidateData['MODALITY'] = $protocolValues['modality_abbr'];
        $aCandidateData['GIVE_HYDRATION_ORAL'] = NULL;
        $aCandidateData['GIVE_HYDRATION_IV'] = NULL;
        $aCandidateData['GIVE_CONTRAST_ORAL'] = NULL;
        $aCandidateData['GIVE_CONTRAST_ENTERIC'] = NULL;
        $aCandidateData['GIVE_SEDATION_ORAL'] = NULL;
        $aCandidateData['GIVE_SEDATION_IV'] = NULL;
        $aCandidateData['GIVE_RADIOISOTOPE_ORAL'] = NULL;
        $aCandidateData['GIVE_RADIOISOTOPE_ENTERIC'] = NULL;
        $aCandidateData['IS_CLAUSTROPHOBIC'] = NULL;
        try
        {
            $aContraindications = $this->m_oCIE->getContraindications($aCandidateData)->getAll();
        } catch (\Exception $ex) {
            $aContraindications = array();
            drupal_set_message('Failed to run the contraindications engine because ' . $ex->getMessage(),'error');
        }
        $sStaticWarningMsgsHTML = NULL;
        //$sAcknowledgeContraindicationsHTML = NULL;
        $aCI_Acknowledge = array();
        $nItem=0;
        foreach($aContraindications as $oCI)
        {
            $nItem++;
            $sID = $oCI->getUniqueID();
            $sSummaryMsg = $oCI->getSummaryMessage();
            $aCIS = $oCI->getContraindicationSource();
            $sStaticWarningMsgsHTML .= "\n<li id='static_{$sID}'>" . $sSummaryMsg;
            $aCI_Acknowledge[$sID]['title'.$nItem] = array('#type' => 'checkbox',
                '#title' => t("Acknowledgement of $sSummaryMsg"),
                );
            //$sAcknowledgeContraindicationsHTML .= "\n<input id='input_{$sID}' type='checkbox' name='ci_ack_req' value='test1'>Acknowledgement of $sSummaryMsg</input>"
            //    . "\n<ul>";
            $aCI_Acknowledge[$sID]['sources'.$nItem][] = array('#markup' => '<ul>');
            $nRuleExplainID = 1231000;
            foreach($aCIS as $oCIS)
            {
                $nRuleExplainID++;  //Had to go numeric because trouble with quotes in the generated html.
                $aCI_Acknowledge[$sID]['sources'.$nItem][] = array('#markup' => "\n<li>\n<a href='javascript:showContraIndicationsExplanationPopup($nRuleExplainID);'>".$oCIS->getMessage()."</a><data-explanation hidden id='$nRuleExplainID'>".$oCIS->getExplanation()."</data-explanation></li>");
            }
            $aCI_Acknowledge[$sID]['sources'.$nItem][] = array('#markup' => '</ul>');
            //$sAcknowledgeContraindicationsHTML .= "\n</ul>";
            //die('LOOK 111111111 >>>>>' . print_r($aContraindications,TRUE) . "\n<br>aCIS=" . print_r($aCIS,TRUE));
      }
        if($sStaticWarningMsgsHTML !== NULL)
        {
            $sStaticWarningMsgsHTML = "\n<ul>" . $sStaticWarningMsgsHTML . "\n</ul>";
        }
        
        //die('LOOK>>>>>' . count($aContraindications) . print_r($aContraindications,TRUE));
        
        $protocolInputDisable = ($sCWFS == 'AP' || $sCWFS == 'EC' || $sCWFS == 'PA' || $sCWFS == 'IA' );
        
        $form["data_entry_area1"]    = array(
            '#prefix' => "\n<section class='right-side'>\n",
            '#suffix' => "\n</section>\n",
        );
        
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        
        //PROTOCOL MODE
        $form["data_entry_area1"][]  = $this->m_oUtility->getDataEntryArea1($form_state, $protocolInputDisable, $myvalues);

        $form['data_entry_area2']    = array(
            '#prefix' => "\n<section class='bottom-protocol'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area2"][]  = $this->m_oUtility->getDataEntryArea2($form_state, $protocolInputDisable, $myvalues);

        if($sCWFS == 'PA' || $sCWFS == 'EC') 
        {
            $disableExamInput = ($sCWFS == 'EC');
            
            //EXAM MODE
            $form["exam_data_entry_area1"]   = array(
                '#prefix' => "\n<section class='page-exam-content'>\n",
                '#markup' => "\n<h2>Placeholder area for exam fields</h2>\n",
                '#suffix' => "\n</section>\n",
            );
        }
        
        if(count($aContraindications) > 0) //$sStaticWarningMsgsHTML !== NULL)
        {
            //Populate the static warning area.
            $form["static_warnings_area"]   = array(
                '#prefix' => "\n<section id='static-warnings'>\n",
                '#suffix' => "\n</section>\n",
            );
            $form["static_warnings_area"][] = array('#markup' => $sStaticWarningMsgsHTML);  // $this->m_oUtility->getStaticWarningsArea($form_state, $disabled, $myvalues); 
            
            //Populate the acknowledgement warning area.
            $form['data_entry_area2']['contraindication']  = array(
                    '#type'     => 'fieldset',
                    '#title'    => t('Contraindications Requiring Confirmation'),
                    '#disabled' => $disabled,
                );
            $form['data_entry_area2']['contraindication']['ContraIndicationAcknowlegeArea'][] = $aCI_Acknowledge;
        }
        
        //Now populate the button area.
        $form["page_button_area1"]   = array(
            '#prefix' => "\n<section class='page-action'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["page_button_area1"][] = $this->m_oUtility->getPageActionButtonsArea($form_state, $disabled, $myvalues);
        
        
        return $form;
    }
}
