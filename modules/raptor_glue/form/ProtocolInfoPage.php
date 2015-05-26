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

module_load_include('php', 'raptor_workflow', 'core/AllowedActions');
module_load_include('php', 'raptor_formulas', 'core/LanguageInference');

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');

require_once 'ProtocolPageUtils.inc';
require_once 'ProtocolInfoUtility.php';
require_once 'FormHelper.php';

/**
 * This class returns the Protocol Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ProtocolInfoPage extends \raptor\ASimpleFormPage
{
    private $m_oContext = NULL;
    private $m_oUtility = NULL;
    private $m_oCIE = NULL;
    private $m_tid = NULL;
    private $m_oTT = NULL;
    private $m_oLI = NULL;
    
    /**
     * Create an instance of the procotol info page.
     * @param \raptor\ContraIndEngine $oCIE
     * @throws \Exception
     */
    function __construct($tid = NULL)
    {
        $loaded = module_load_include('inc','raptor_contraindications','core/ContraIndEngine');
        if(!$loaded)
        {
            $msg = 'Failed to load the Contraindication Engine';
            throw new \Exception($msg);      //This is fatal, so stop everything now.
        }
        
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_tid = $tid;
        $this->m_oCIE = NULL;
        $this->m_oUtility = new \raptor\ProtocolInfoUtility();
        $this->m_oTT = new \raptor\TicketTrackingData();
        $this->m_oLI = new \raptor_formulas\LanguageInference();
        
    }

    /**
     * Set all key values of the myvalues array as NULL/empty
     * @param array $myvalues for ALL values
     */
    static function setAllValuesNull(&$myvalues)
    {
        //Protocol relevant values
        $myvalues['protocol1_nm'] = NULL;
        $myvalues['protocol2_nm'] = NULL;
        
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
        
        //Exam relevant values
        $myvalues['exam_data_from_database'] = FALSE;
        $myvalues['exam_data_created_dt'] = NULL;
        $myvalues['exam_author_uid'] = NULL;

        $myvalues['exam_hydration_oral_tx'] = NULL;
        $myvalues['exam_hydration_iv_tx'] = NULL;
        $myvalues['exam_sedation_oral_tx'] = NULL;
        $myvalues['exam_sedation_iv_tx'] = NULL;
        $myvalues['exam_contrast_enteric_tx'] = NULL;
        $myvalues['exam_contrast_iv_tx'] = NULL;
        $myvalues['exam_radioisotope_enteric_tx'] = NULL;
        $myvalues['exam_radioisotope_iv_tx'] = NULL;
        $myvalues['exam_consent_received_kw'] = NULL;

        $myvalues['exam_notes_tx'] = NULL;        
    }

    /**
     * Load the myvalues array with all the protocol values
     */
    private function loadProtocolFieldValues($nSiteID,$nIEN,&$myvalues
            , $newerthan_dt=NULL
            , $protocol_lib_map=NULL)
    {
        try
        {
            if($protocol_lib_map == NULL)
            {
                $protocol_lib_map = $this->getProtocolLibMap();
            }
            $query = db_select('raptor_ticket_protocol_settings', 'n');
            $query->fields('n')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
            if($newerthan_dt != NULL)
            {
                $query->condition('created_dt', $newerthan_dt,'>');
            }
            $result = $query->execute();
            if($result->rowCount() > 0)
            {
                if($result->rowCount() > 1)
                {
                    //Critical this should NEVER be allowed to happen!
                    throw new \Exception('Too many protocol records for ' . $nIEN . '!');
                }
                
                $myvalues['protocol_data_from_database'] = TRUE;
                
                $record = $result->fetchAssoc();
                
                $myvalues['protocol_data_created_dt'] = $record['created_dt'];
                $myvalues['protocol_approval_author_uid'] = $record['author_uid'];
                
                $shortname1 = $record['primary_protocol_shortname'];
                $metadata1 = $protocol_lib_map[$shortname1];
                $myvalues['protocol1_nm'] = $shortname1;
                $myvalues['protocol1_fullname'] = $metadata1['name'];
                $myvalues['protocol1_modality_abbr'] = $metadata1['modality_abbr'];
                if(isset($record['secondary_protocol_shortname']) && $record['secondary_protocol_shortname'] != NULL)
                {
                    $shortname2 = $record['secondary_protocol_shortname'];
                    $metadata2 = $protocol_lib_map[$shortname2];
                    $myvalues['protocol2_nm'] = $shortname2;
                    $myvalues['protocol2_fullname'] = $metadata2['name'];
                    $myvalues['protocol2_modality_abbr'] = $metadata2['modality_abbr'];
                } else {
                    $myvalues['protocol2_nm'] = NULL;
                    $myvalues['protocol2_fullname'] = NULL;
                    $myvalues['protocol2_modality_abbr'] = NULL;
                }
                if($record['hydration_none_yn'] == 1)
                {
                    $myvalues['hydration_radio_cd'] = '';
                } else {
                    if(isset($record['hydration_oral_tx']))
                    {
                        $myvalues['hydration_radio_cd'] = 'oral';
                        $myvalues['hydration_oral_id'] = $record['hydration_oral_tx'];
                    } else if(isset($record['hydration_iv_tx'])) {
                        $myvalues['hydration_radio_cd'] = 'iv';
                        $myvalues['hydration_iv_id'] = $record['hydration_iv_tx'];
                    } else {
                        $myvalues['hydration_radio_cd'] = '';
                    }
                }
                $myvalues['hydration_oral_customtx'] = $record['hydration_oral_tx'];
                $myvalues['hydration_iv_customtx'] = $record['hydration_iv_tx'];

                $myvalues['sedation_none_yn'] = $record['sedation_none_yn'];
                if($record['sedation_none_yn'] == 1)
                {
                    $myvalues['sedation_radio_cd'] = '';
                } else {
                    if(isset($record['sedation_oral_tx']))
                    {
                        $myvalues['sedation_radio_cd'] = 'oral';
                        $myvalues['sedation_oral_id'] = $record['sedation_oral_tx'];
                    } else if(isset($record['sedation_iv_tx'])) {
                        $myvalues['sedation_radio_cd'] = 'iv';
                        $myvalues['sedation_iv_id'] = $record['sedation_iv_tx'];
                    } else {
                        $myvalues['sedation_radio_cd'] = '';
                    }
                }
                $myvalues['sedation_oral_customtx'] = $record['sedation_oral_tx'];
                $myvalues['sedation_iv_customtx'] = $record['sedation_iv_tx'];

                $myvalues['contrast_cd'] = array('none'=>0,'enteric'=>0,'iv'=>0);
                $myvalues['contrast_none_yn'] = $record['contrast_none_yn'];
                $contrast_count=0;
                if(isset($record['contrast_enteric_tx']))
                {
                    $contrast_count++;
                    $myvalues['contrast_cd']['enteric'] = 'enteric';
                    $myvalues['contrast_enteric_id'] = $record['contrast_enteric_tx'];
                } 
                if(isset($record['contrast_iv_tx'])) {
                    $contrast_count++;
                    $myvalues['contrast_cd']['iv'] = 'iv';
                    $myvalues['contrast_iv_id'] = $record['contrast_iv_tx'];
                }
                if($contrast_count == 0)
                {
                    $myvalues['contrast_cd']['none'] = 'none';
                }
                $myvalues['contrast_enteric_customtx'] = $record['contrast_enteric_tx'];
                $myvalues['contrast_iv_customtx'] = $record['contrast_iv_tx'];

                $myvalues['radioisotope_cd'] = array('none'=>0,'enteric'=>0,'iv'=>0);
                $myvalues['radioisotope_none_yn'] = $record['radioisotope_none_yn'];
                $radioisotope_count=0;
                if(isset($record['radioisotope_enteric_tx']))
                {
                    $radioisotope_count++;
                    $myvalues['radioisotope_cd']['enteric'] = 'enteric';
                    $myvalues['radioisotope_enteric_id'] = $record['radioisotope_enteric_tx'];
                } 
                if(isset($record['radioisotope_iv_tx'])) {
                    $radioisotope_count++;
                    $myvalues['radioisotope_cd']['iv'] = 'iv';
                    $myvalues['radioisotope_iv_id'] = $record['radioisotope_iv_tx'];
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }
                if($radioisotope_count == 0)
                {
                    $myvalues['radioisotope_cd']['none'] = 'none';
                }
                $myvalues['radioisotope_enteric_customtx'] = $record['radioisotope_enteric_tx'];
                $myvalues['radioisotope_iv_customtx'] = $record['radioisotope_iv_tx'];

                if($record['allergy_kw'] == NULL || $record['allergy_kw'] == 'unknown')
                {
                    $myvalues['allergy_cd'] = 'unknown';
                } else if($record['allergy_kw'] == 'yes') {
                    $myvalues['allergy_cd'] = 'yes';
                } else if($record['allergy_kw'] == 'no') {
                    $myvalues['allergy_cd'] = 'no';
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }

                if($record['claustrophobic_kw'] == NULL || $record['claustrophobic_kw'] == 'unknown')
                {
                    $myvalues['claustrophobic_cd'] = 'unknown';
                } else if($record['claustrophobic_kw'] == 'yes') {
                    $myvalues['claustrophobic_cd'] = 'yes';
                } else if($record['claustrophobic_kw'] == 'no') {
                    $myvalues['claustrophobic_cd'] = 'no';
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }

                if($record['consent_req_kw'] == NULL || $record['consent_req_kw'] == 'unknown')
                {
                    $myvalues['consentreq_radio_cd'] = 'unknown';
                } else if($record['consent_req_kw'] == 'yes') {
                    $myvalues['consentreq_radio_cd'] = 'yes';
                } else if($record['consent_req_kw'] == 'no') {
                    $myvalues['consentreq_radio_cd'] = 'no';
                } else {
                    //die('Data corruption of profile data for IEN ' . $nIEN);
                }

            } else {
                //No saved data, simply provide initial values.
                $myvalues['protocol_data_from_database'] = FALSE;
                $myvalues['protocol_data_created_dt'] = NULL;
                $myvalues['protocol_approval_author_uid'] = NULL;
                
                $myvalues['protocol1_nm'] = NULL;
                $myvalues['protocol2_nm'] = NULL;
                $myvalues['hydration_radio_cd'] = NULL;
                $myvalues['contrast_cd'] = NULL;
                $myvalues['sedation_radio_cd'] = NULL;
                $myvalues['radioisotope_cd'] = NULL;
                $myvalues['allergy_cd'] = NULL;
                $myvalues['claustrophobic_cd'] = NULL;
                $myvalues['consentreq_radio_cd'] = NULL;
                
                $myvalues['hydration_oral_customtx'] = NULL;
                $myvalues['hydration_iv_customtx'] = NULL;
                $myvalues['sedation_oral_customtx'] = NULL;
                $myvalues['sedation_iv_customtx'] = NULL;
                $myvalues['contrast_enteric_customtx'] = NULL;
                $myvalues['contrast_iv_customtx'] = NULL;
                $myvalues['radioisotope_enteric_customtx'] = NULL;
                $myvalues['radioisotope_iv_customtx'] = NULL;
                $myvalues['allergy_cd'] = NULL;
                $myvalues['claustrophobic_cd'] = NULL;
                $myvalues['consentreq_radio_cd'] = NULL;
            }
        } catch (\Exception $ex) {
            $msg = 'Unable to load protocol information for ticket ['.$nSiteID.'-'.$nIEN.']';
            throw new \Exception($msg,ERRORCODE_UNABLE_LOAD_PROTOCOLDATA,$ex);
        }
    }
    
    /**
     * Load the myvalues array with all the exam values
     * NOTE: The protocl1_nm might be empty.  This can happen if some exam data
     * was already collected and ticket was sent back to protocol
     * We do NOT want to lose the exam data entered so far, so keep it.
     */
    private function loadExamFieldValues($nSiteID,$nIEN,&$myvalues,$newerthan_dt=NULL)
    {
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        $load_sofar_tables = ($sCWFS == 'PA');  //No exam values are final yet
        $relevant_protocol_shortname = isset($myvalues['protocol1_nm']) ? $myvalues['protocol1_nm'] : NULL;
        try
        {
            $query = db_select('raptor_ticket_exam_settings', 'n');
            $query->fields('n')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
            if($newerthan_dt != NULL)
            {
                $query->condition('created_dt', $newerthan_dt,'>');
            }
            $result = $query->execute();
            if($result->rowCount() > 0)
            {
                if($result->rowCount() > 1)
                {
                    //Critical this should NEVER be allowed to happen!
                    throw new \Exception('Too many exam records for ' . $nIEN . '!');
                }
                $initial_population = FALSE;
                
                $myvalues['exam_data_from_database'] = TRUE;
                
                $record = $result->fetchAssoc();
                
                $myvalues['exam_data_created_dt'] = $record['created_dt'];
                $myvalues['exam_author_uid'] = $record['author_uid'];
                
                $sFieldName = 'hydration_oral_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                $sFieldName = 'hydration_iv_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                
                $sFieldName = 'sedation_oral_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                $sFieldName = 'sedation_iv_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                
                $sFieldName = 'contrast_enteric_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                $sFieldName = 'contrast_iv_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                
                $sFieldName = 'radioisotope_enteric_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                $sFieldName = 'radioisotope_iv_tx';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];
                
                $sFieldName = 'consent_received_kw';
                $myvalues['exam_'.$sFieldName] = $record[$sFieldName];

            } else {
                //No saved data, simply provide initial values.
                $initial_population = TRUE;
                $myvalues['exam_data_from_database'] = FALSE;
                $myvalues['exam_data_created_dt'] = NULL;
                $myvalues['exam_author_uid'] = NULL;
                
                $myvalues['exam_hydration_oral_tx'] = NULL;
                $myvalues['exam_hydration_iv_tx'] = NULL;
                $myvalues['exam_sedation_oral_tx'] = NULL;
                $myvalues['exam_sedation_iv_tx'] = NULL;
                $myvalues['exam_contrast_enteric_tx'] = NULL;
                $myvalues['exam_contrast_iv_tx'] = NULL;
                $myvalues['exam_radioisotope_enteric_tx'] = NULL;
                $myvalues['exam_radioisotope_iv_tx'] = NULL;
                $myvalues['exam_consent_received_kw'] = NULL;
            }
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam information for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }

        try
        {
            $myvalues['exam_notes_tx'] = $this->getExamNotesText($nSiteID, $nIEN
                    , $relevant_protocol_shortname);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam notes radiation dose information for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
                
        //Get the CTDIvol radiation dose information.
        try
        {
            $dose_source_cd = 'C';
            $myvalues['exam_ctdivol_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam CTDIvol radiation dose information for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        
        //Get the DLP  radiation dose information.
        try
        {
            $dose_source_cd = 'D';
            $myvalues['exam_dlp_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam CTDIvol radiation dose information for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        
        //Get the other equipment radiation dose information.
        try
        {
            $dose_source_cd = 'E';
            $myvalues['exam_other_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam machine-produced radiation exposure data for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        
        //Get the radioisotope radiation dose information.
        try
        {
            $dose_source_cd = 'R';
            $myvalues['exam_radioisotope_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam radioisotope radiation dose information for ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        
        try
        {
            $dose_source_cd = 'Q';
            $littlename='fluoroQ';
            $myvalues['exam_'.$littlename.'_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam '.$littlename
                    .' radiation dose information for '
                    . 'ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        try
        {
            $dose_source_cd = 'S';
            $littlename='fluoroS';
            $myvalues['exam_'.$littlename.'_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam '.$littlename
                    .' radiation dose information for '
                    . 'ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        try
        {
            $dose_source_cd = 'T';
            $littlename='fluoroT';
            $myvalues['exam_'.$littlename.'_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam '.$littlename
                    .' radiation dose information for '
                    . 'ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
        try
        {
            $dose_source_cd = 'H';
            $littlename='fluoroH';
            $myvalues['exam_'.$littlename.'_radiation_dose_map'] 
                    = $this->getRadiationDoseMap($nSiteID, $nIEN, $dose_source_cd
                            , $newerthan_dt
                            , $load_sofar_tables);
        } catch (\Exception $ex) {
            $msg = 'Unable to load exam '.$littlename
                    .' radiation dose information for '
                    . 'ticket ['.$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            error_log($msg);
            drupal_set_message($msg,'error');
            throw $ex;
        }
    }

    /**
     * Just return the text that the user can edit.
     */
    function getExamNotesText($nSiteID,$nIEN,$protocol_shortname=NULL)
    {
        $notes_tx = '';
        //Treat this as an initial population?
        $query = db_select('raptor_ticket_exam_notes_sofar', 'n');
        $query->fields('n')
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=');
        $result = $query->execute();
        if($result->rowCount() > 0)
        {
            //Return anything that has been saved sofar without commit
            $query = db_select('raptor_ticket_exam_notes_sofar', 'n');
            $query->fields('n')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
            $result = $query->execute();
            if($result->rowCount() > 0)
            {
                if($result->rowCount() > 1)
                {
                    //Critical this should NEVER be allowed to happen!
                    throw new \Exception('Too many exam note "sofar" records for ' . $nSiteID . '-' . $nIEN . '!');
                }
                $record = $result->fetchAssoc();
                $notes_tx = $record['notes_tx'];
            }
        } else {
            //Nothing has been saved yet.
            if($protocol_shortname == NULL)
            {
                //Special case where no template was used.
                $notes_tx = '';
            } else {
                //Populate with whatever was in the protocol template
                $oPLPH = new \raptor\ProtocolLibPageHelper();
                $map = $oPLPH->getTemplateMap($protocol_shortname);
                $notes_tx = $map['examnotes_tx'];
            }
        }
        
        return $notes_tx;
    }
    
    /**
     * Get the records for one ticket.
     */
    function getRadiationDoseMap($nSiteID,$nIEN
            ,$dose_source_cd=NULL
            ,$newerthan_dt=NULL
            ,$use_sofar=FALSE)
    {
        if($use_sofar)
        {
            $sourcetablename = 'raptor_ticket_exam_radiation_dose_sofar';
        } else {
            $sourcetablename = 'raptor_ticket_exam_radiation_dose';
        }
        $dose_map = array();
        try
        {
            $query = db_select($sourcetablename, 'n');
            $query->fields('n')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
            if($dose_source_cd != NULL)
            {
                $query->condition('dose_source_cd', $dose_source_cd,'=');
            }
            if($newerthan_dt != NULL)
            {
                $query->condition('created_dt', $newerthan_dt,'>');
            }
            $query->orderBy('sequence_position')
                  ->orderBy('uom');
            $result = $query->execute();
            if($result->rowCount() > 0)
            {
                while($record = $result->fetchAssoc())
                {
                    $uom = $record['uom'];
                    $sp = $record['sequence_position'];
                    $dose_map[$uom][$sp] = $record;
                }
            }
        } catch (\Exception $ex) {
            $msg = 'Trouble getting '.$sourcetablename.' map information for ticket ['
                    .$nSiteID.'-'.$nIEN.'] because '.$ex->getMessage();
            throw new \Exception($msg);
        }
        return $dose_map;
    }

    /**
     * Provide a method that returns instance of CI engine.
     * @return contraindication engine
     */
    function getCIE()
    {
        if($this->m_oCIE == NULL)
        {
            $tid = $this->m_oContext->getSelectedTrackingID();
            $oWL = new \raptor\WorklistData($this->m_oContext);
            $aOneRow = $oWL->getDashboardMap();    //$tid);

            $oDD = new \raptor\DashboardData($this->m_oContext);
            $aDD = $oDD->getDashboardDetails();        
            $oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
            $aLatestVitals = $oPSD->getVitalsDetailOnlyLatest();
            $aEGFR = $oPSD->getEGFRDetail();

            $aPatientInfoForCIE = array();    
            $aPatientInfoForCIE['GENDER'] = $aDD['PatientGender'];
            $aPatientInfoForCIE['AGE'] = $aDD['PatientAge'];
            $aPatientInfoForCIE['WEIGHT_KG'] = isset($aLatestVitals['Weight']) ? $aLatestVitals['Weight'] : NULL;
            $aPatientInfoForCIE['LATEST_EGFR'] = $aEGFR['LATEST_EGFR'];
            $aPatientInfoForCIE['MIN_EGFR_10DAYS'] = $aEGFR['MIN_EGFR_10DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_15DAYS'] = $aEGFR['MIN_EGFR_15DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_30DAYS'] = $aEGFR['MIN_EGFR_30DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_45DAYS'] = $aEGFR['MIN_EGFR_45DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_60DAYS'] = $aEGFR['MIN_EGFR_60DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_90DAYS'] = $aEGFR['MIN_EGFR_90DAYS'];
            try
            {
                $oCIE = new \raptor\ContraIndEngine($aPatientInfoForCIE);        
            } catch (\Exception $ex) {
                $oCIE = NULL;
                drupal_set_message('Failed to create the contraindications engine because ' . $ex->getMessage(), 'error');
            }
            $this->m_oCIE = $oCIE;
        }
        return $this->m_oCIE;
    }
    
    /**
     * Get the values to populate the form.
     * @param type $tid the tracking ID
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $tid = $this->m_tid;
        if($tid == NULL)
        {
            throw new \Exception('Cannot get field values when tid is NULL!');
        }
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getDashboardMap($tid);
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();

        $oDD = new \raptor\DashboardData($this->m_oContext);
        $aDD = $oDD->getDashboardDetails();     //TODO REDUNDANT WITH $aOneRow????????????????      
        $oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
        
        if($this->m_oCIE == NULL)
        {
            $aLatestVitals = $oPSD->getVitalsDetailOnlyLatest();
            $aEGFR = $oPSD->getEGFRDetail();
        
            $aPatientInfoForCIE = array();    //TODO move this code elsewhere
            $aPatientInfoForCIE['GENDER'] = $aDD['PatientGender'];
            $aPatientInfoForCIE['AGE'] = $aDD['PatientAge'];
            $aPatientInfoForCIE['WEIGHT_KG'] = isset($aLatestVitals['Weight']) ? $aLatestVitals['Weight'] : NULL;
            $aPatientInfoForCIE['LATEST_EGFR'] = $aEGFR['LATEST_EGFR'];
            $aPatientInfoForCIE['MIN_EGFR_10DAYS'] = $aEGFR['MIN_EGFR_10DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_15DAYS'] = $aEGFR['MIN_EGFR_15DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_30DAYS'] = $aEGFR['MIN_EGFR_30DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_45DAYS'] = $aEGFR['MIN_EGFR_45DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_60DAYS'] = $aEGFR['MIN_EGFR_60DAYS'];
            $aPatientInfoForCIE['MIN_EGFR_90DAYS'] = $aEGFR['MIN_EGFR_90DAYS'];
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
        $prev_protocolnotes_tx = $this->m_oUtility->
                getSchedulerNotesMarkup($nSiteID,$nIEN);
        $prev_protocolnotes_tx .= $this->m_oUtility->
                getCollaborationNotesMarkup($nSiteID,$nIEN);
        $prev_protocolnotes_tx .= $this->m_oUtility->
                getPreviousNotesMarkup('raptor_ticket_protocol_notes'
                        ,$nSiteID,$nIEN,'core-protocol-note');
        $prev_exam_notes_tx = $this->m_oUtility->
                getPreviousNotesMarkup('raptor_ticket_exam_notes'
                        ,$nSiteID,$nIEN,'exam-note');
        $prev_interpret_notes_tx = $this->m_oUtility->
                getPreviousNotesMarkup('raptor_ticket_interpret_notes'
                        ,$nSiteID,$nIEN,'interpret-note');
        $prev_qa_notes_tx = $this->m_oUtility->
                getPreviousQANotesMarkup($nSiteID,$nIEN,'qa-note');
        $prev_suspend_notes_tx = $this->m_oUtility->
                getPreviousNotesMarkup('raptor_ticket_suspend_notes'
                        ,$nSiteID,$nIEN,'suspend-note');
        $prev_unsuspend_notes_tx = $this->m_oUtility->
                getPreviousNotesMarkup('raptor_ticket_unsuspend_notes'
                        ,$nSiteID,$nIEN,'unsuspend-note');
        
        //Get app existing protocol data
        $myvalues = array();
        ProtocolInfoPage::setAllValuesNull($myvalues);  //Initialize all values to NULL first.
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];  //TODO REDUNDANT WITH $aDD?????
        $myvalues['prev_protocolnotes_tx'] = $prev_protocolnotes_tx;
        $myvalues['prev_exam_notes_tx'] = $prev_exam_notes_tx;
        $myvalues['prev_interpret_notes_tx'] = $prev_interpret_notes_tx;
        $myvalues['prev_qa_notes_tx'] = $prev_qa_notes_tx;
        $myvalues['prev_suspend_notes_tx'] = $prev_suspend_notes_tx;
        $myvalues['DefaultValues'] = null;
        $myvalues['protocolnotes_tx'] = '';
        $myvalues['exam_notes_tx'] = '';
        $myvalues['qa_notes_tx'] = '';
        
        $this->loadProtocolFieldValues($nSiteID,$nIEN,$myvalues);
        $this->loadExamFieldValues($nSiteID,$nIEN,$myvalues);
        
        //Get existing checklist data.
        $myvalues['questions'] = array();
        $query = db_select('raptor_ticket_checklist', 't');
        $query->leftJoin('raptor_checklist_question', 'q', 't.question_shortname = q.question_shortname');
        $query->fields('t');
        $query->fields('q', array('relative_position','type_cd'))
            ->condition('q.type_cd', 'SC','=')
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->orderBy('t.author_uid', 'ASC')
            ->orderBy('q.relative_position', 'ASC');
        
            //->condition('author_uid', $nUID,'=')

        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $shortname = $record['question_shortname'];
            if($record['author_uid'] == $nUID)  //20140810
            {
                //Answer from this user.
                $myvalues['questions']['thisuser'][$shortname] = array();
                $myvalues['questions']['thisuser'][$shortname]['response'] = $record['answer_tx'];
                $myvalues['questions']['thisuser'][$shortname]['comment'] = $record['comment_tx'];
            } else {
                //Answer from a different user.
                $author = $record['author_uid'];
                $myvalues['questions']['otheruser'][$author][$shortname] = array();
                $myvalues['questions']['otheruser'][$author][$shortname]['response'] = $record['answer_tx'];
                $myvalues['questions']['otheruser'][$author][$shortname]['comment'] = $record['comment_tx'];
            }
            //drupal_set_message('LOOK GOT CHECLIST Q'.$record['question_shortname'].'>>>'.print_r($myvalues['questions'][$shortname],TRUE));
        }
        
        //drupal_set_message('LOOK>>>'.print_r($myvalues,TRUE));
        
        return $myvalues;
    }

    /**
     * Get all the questions regardless of who answered them
     * @return array of all questions and answers collected for them
     */
    function getAllSavedSafetyChecklistTicketData($nSiteID,$nIEN,$oAllUsers,$prev_commit_dt=NULL)
    {
        //Get existing checklist data.
        $myvalues['questions'] = array();
        $query = db_select('raptor_ticket_checklist', 't');
        $query->leftJoin('raptor_checklist_question', 'q', 't.question_shortname = q.question_shortname');
        $query->fields('t');
        $query->fields('q', array('relative_position','type_cd'))
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->condition('q.type_cd', 'SC','=');
        if($prev_commit_dt != NULL)
        {
            $query->condition('t.created_dt', $prev_commit_dt,'>');    
        }
        $query->orderBy('t.author_uid', 'ASC')
            ->orderBy('q.relative_position', 'ASC');
        $result = $query->execute();
        $aMetadata = array();
        if($result->rowCount()!==0)
        {
            $aQuestions = array();
            $nQuestion = 0;
            $aAuthors = array();
            while($record = $result->fetchAssoc())
            {
                if($record['answer_tx'] !== NULL)
                {
                    $nQuestion++;   //Same question may have been asked multiple times.
                    $date = $record['created_dt'];
                    if(!isset($aMetadata['last_date']) || $date > $aMetadata['last_date'])
                    {
                       $aMetadata['last_date'] = $date;
                    }
                    $author_uid = $record['author_uid'];
                    $userinfo = $oAllUsers->getByUID($author_uid);
                    $aAuthors[$author_uid] = $userinfo->getFullName();
                    $aQuestions[$nQuestion] = $record;
                }
            }
            $aMetadata['authors'] = $aAuthors;
            $aMetadata['questions'] = $aQuestions;
        }
        return $aMetadata;
    }
    
    /**
     * Writes the contraindications or stops all processing if fails.
     */
    private function writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues)
    {
        //Do we have any responses to process?
        if(isset($myvalues['ci_responses']))
        {
            //Yes, process them.
            try 
            {
                //Process the CI acknowledgements.
                $updated_dt = date("Y-m-d H:i:s", time());
                $aCI_Ack = $myvalues['ci_responses'];

                //die('Look at $aCI_Ack>>>'.print_r($aCI_Ack,TRUE));

                foreach($aCI_Ack as $sSummaryMsg=>$aOneCI_AckGrp)
                {
                    $bChecked = (isset($aOneCI_AckGrp['chk_ack']) && $aOneCI_AckGrp['chk_ack'] == 1);
                    if($bChecked)
                    {
                        $aCI_Items = $aOneCI_AckGrp['ci_rules'];   //Can be more than one associated with the summary message.
                        foreach($aCI_Items as $sCI_RULE_NM)
                        {
                            //Write each acknowledgement to the database.
                            $oInsert = db_insert('raptor_ticket_contraindication')
                                    ->fields(array(
                                        'siteid' => $nSiteID,
                                        'IEN' => $nIEN,
                                        'rule_nm' => $sCI_RULE_NM,
                                        'acknowledged_yn' => 1,
                                        'author_uid' => $nUID,
                                        'created_dt' => $updated_dt,
                                    ))
                                    ->execute();
                        }
                    }
                }
            } catch (\Exception $ex) {
                $emsg = 'Unable to write CI acknowledgements because ' . $ex->getMessage() . ' Details>>>'. print_r($myvalues,TRUE);
                error_log($emsg);
                die($emsg); //Stop everything now right here!
            }
        }
    }
    
    /**
     * Sets messages and returns FALSE if there were errors.
     */
    private function validateChecklist($aAnswers)
    {
        $bGood = TRUE;
        $aBad = array();
        if(is_array($aAnswers))
        {
            foreach($aAnswers as $aQuestion)
            {
                $shortname = $aQuestion['shortname'];
                $response = $aQuestion['response'];
                if(trim($response) == '')
                {
                    $aBad[] = $shortname;
                    $bGood = FALSE;
                }
            }
        }
        if(!$bGood)
        {
            if(count($aBad) > 1)
            {
                form_set_error('page_checklist_area1','Must complete all Safety Checklist questions ('.count($aBad).' are blank)');
            } else {
                form_set_error('page_checklist_area1','Must complete all Safety Checklist questions (1 is blank)');
            }
        }
        return $bGood;
    }
    
    /**
     * Sets messages and returns FALSE if there were errors.
     */
    private function validateDefaultValueAcknowledgements($myvalues)
    {
        $bGood = TRUE;
        $sections = array(
             'contrast'=>'Contrast Section'
            ,'consentreq'=>'Consent Required Section'
            ,'hydration'=>'Hydration Section'
            ,'sedation'=>'Sedation Section'
            ,'radioisotope'=>'Radionuclide Section'
            ,'protocolnotes'=>'Protocol Notes Section'
            );
        foreach($sections as $sectionname=>$title)
        {
            $indicatorname = 'require_acknowledgement_for_'.$sectionname;
            if(isset($myvalues[$indicatorname]) && $myvalues[$indicatorname] == 'yes')
            {
                //Acknowledgement is required, did we get it?
                $name = 'acknowledge_'.$sectionname;
                if(!(isset($myvalues[$name]) && $myvalues[$name] == 1))
                {
                    form_set_error($name, 'Acknowledgement is required for '.$title);
                    $bGood = FALSE;
                }
            }
        }
        return $bGood;
    }
    
    /**
     * Sets messages and returns FALSE if there were errors.
     */
    private function validateContraindicationAcknowledgements($myvalues)
    {
        $bGood = TRUE;
        //Do we have acknowledgements to process?
        if(isset($myvalues['ci_responses']))
        {
            //Process the CI acknowledgements.
            $aCI_Ack = $myvalues['ci_responses'];
            if(!is_array($aCI_Ack))
            {
                //Should never happen but if it does, stop everything and leave some clues.
                $emsg = ('Expected array of CI acknowlegements did not get it!  See details>>>'.print_r($myvalues,TRUE)); 
                error_log($emsg);
                die($emsg);
            }
            foreach($aCI_Ack as $sSummaryMsg=>$aOneCI_AckGrp)
            {
                $bChecked = (isset($aOneCI_AckGrp['chk_ack']) && $aOneCI_AckGrp['chk_ack'] == 1);
                if(!$bChecked)
                {
                    form_set_error($sSummaryMsg,'Must acknowledge '.$sSummaryMsg);
                    $bGood = FALSE;
                }
            }
        }
        return $bGood;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param array $form
     * @param array $form_state
     * @return TRUE or FALSE
     */
    function looksValidFormState($form, &$form_state)
    {
        $bGood = TRUE;
        $myvalues = $form_state['values'];
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        $oAA = new \raptor\AllowedActions();    //Leverage workflow dependences from special class
        $clickedbutton = $form_state['clicked_button'];
        $clickedvalue = $clickedbutton['#value'];
 //drupal_set_message('111 LOOK myvalues at clicked=['.$form_state['clicked_button']['#value'].']');        
        
        if(isset($myvalues['collaboration_uid']) && trim($myvalues['collaboration_uid']) > '')
        {
            if(!is_numeric($myvalues['collaboration_uid']))
            {
                //Stop right now if this happens.
                $msg = 'The collaboration_uid=['.$myvalues['collaboration_uid'].'] value is not valid!  Expected a numeric ID! ';
                die($msg . ' >>>'.print_r($myvalues,TRUE));
            }
            //Handle it this way because simple javascript submit seems to assume Approve button otherwise.
            $clickedvalue = 'Collaborate';
        } elseif(isset($myvalues['commit_esig']) 
                && trim($myvalues['commit_esig'])>'' 
                && trim($myvalues['commit_esig'])!='CANCEL' 
                && (substr($clickedvalue,0,2) == 'QA' 
                        || substr($clickedvalue,0,3) == 'Ack' 
                        || substr($clickedvalue,0,4) == 'Exam' 
                        || substr($clickedvalue,0,2) == 'In' ))  {
            //Change the clicked button value to COMMIT!!! This triggers VISTA Commit elsewhere!
            $aParts = explode(' ', $clickedvalue);
            $clickedvalue = 'Commit to Vista from ' . $aParts[0]; 
            $form_state['clicked_button']['#value'] = $clickedvalue;
        }

        //Special checks here
        $feedback='';
        if(substr($clickedvalue,0,7) == 'Approve')
        {
            //################
            // APPROVAL BLOCK
            //################

            //Make sure we are okay to approve this ticket.
            if(!$oAA->allowApproveProtocol($sCWFS, $feedback))
            {
                form_set_error('protocol1_nm', $feedback);
                $bGood = FALSE;
            }
            
            $check = substr(trim($myvalues['protocol1_nm']),0,1);
            if($check === '-' || $check == '')
            {
                form_set_error('protocol1_nm','Must make a primary protocol selection.');
                $bGood = FALSE;
            }
            
            //Process the default value acknowledgements.
            if(!$this->validateDefaultValueAcknowledgements($myvalues))
            {
                $bGood = FALSE;
            }
            
            //Process the CI acknowledgements.
            if(!$this->validateContraindicationAcknowledgements($myvalues))
            {
                $bGood = FALSE;
            }

            $check = $myvalues['sedation_radio_cd'];
            if($check === NULL || trim($check) == '')
            {
                form_set_error('sedation_radio_cd','Must make a sedation selection.');
                $bGood = FALSE;
            }
            $check = $myvalues['consentreq_radio_cd'];
            if($check === NULL || trim($check) == '')
            {
                form_set_error('consentreq_radio_cd','Must make a consent selection.');
                $bGood = FALSE;
            }
            $check = $myvalues['hydration_radio_cd'];
            if($check === NULL || trim($check) == '')
            {
                form_set_error('hydration_radio_cd','Must make a hydration selection.');
                $bGood = FALSE;
            }
            $check = $myvalues['allergy_cd'];
            if($check === NULL || trim($check) == '')
            {
                form_set_error('allergy_cd','Must make a allergy selection.');
                $bGood = FALSE;
            }
            $check = $myvalues['claustrophobic_cd'];
            if($check === NULL || trim($check) == '')
            {
                form_set_error('claustrophobic_cd','Must make a claustrophobic selection.');
                $bGood = FALSE;
            }
            
            if(!$bGood)
            {
                //Make sure all the sections with data are NOT collapsed.
                //TODO
            }
            
        } else
        if(substr($clickedvalue,0,7) == 'Request')
        {
            //#######################
            // REQUEST APPROVAL BLOCK
            //#######################
            //
            //Make sure we are okay to approve this ticket.
            if(!$oAA->allowRequestApproveProtocol($sCWFS, $feedback))
            {
                form_set_error('protocol1_nm', $feedback);
                $bGood = FALSE;
            }
            $check = substr(trim($myvalues['protocol1_nm']),0,1);
            if($check === '-' || $check == '')  //20140811
            {
                form_set_error('protocol1_nm','Must make a primary protocol selection.');
                $bGood = FALSE;
            }
            
            
        } else
        if(substr($clickedvalue,0,9) == 'Unapprove')
        {
            //#######################
            // UNAPPROVAL BLOCK
            //#######################
            //
            //Make sure we are okay to approve this ticket.
            if(!$oAA->allowUnapproveProtocol($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
        } else
        if(substr($clickedvalue,0,11) == 'Acknowledge')
        {
            //##################
            // ACKNOWLEDGE BLOCK
            //##################

            if(!$oAA->allowAcknowledgeProtocol($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            //Process the CI acknowledgements.
            if(!$this->validateContraindicationAcknowledgements($myvalues))
            {
                $bGood = FALSE;
            }

            //Process the checklist.
            $aAnswers = isset($myvalues['questions']['thisuser']) ? $myvalues['questions']['thisuser'] : NULL;
            if(!$this->validateChecklist($aAnswers))
            {
                $bGood = FALSE;
            }

        } else
        if(substr($clickedvalue,0,13) == 'Unacknowledge')
        {
            //####################
            // UNACKNOWLEDGE BLOCK
            //####################

            if(!$oAA->allowUnacknowledgeProtocol($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }

        } else
        if(substr($clickedvalue,0,9) == 'Save Exam')
        {
            //#################
            // SAVE EXAM SO FAR
            //#################

            if(!$oAA->allowExamComplete($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }

        } else
        if(substr($clickedvalue,0,4) == 'Exam')
        {
            //####################
            // EXAM COMPLETE BLOCK
            //####################

            if(!$oAA->allowExamComplete($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            //Process the CI acknowledgements.
            if(!$this->validateContraindicationAcknowledgements($myvalues))
            {
                $bGood = FALSE;
            }
            
            if(!isset($myvalues['exam_consent_received_kw']) || trim($myvalues['exam_consent_received_kw']) == '')
            {
                form_set_error('exam_consent_received_kw','Cannot leave consent question blank.');
                $bGood = FALSE;
            }

            $dose_fieldname = 'exam_ctdivol_radiation_dose_tx';
            $uom_fieldname = 'exam_ctdivol_radiation_dose_uom_tx';
            $value_type_fieldname = 'exam_ctdivol_radiation_dose_type_cd';
            $radiation_dose_tx = isset($myvalues[$dose_fieldname]) ? trim($myvalues[$dose_fieldname]) : '';
            if($radiation_dose_tx > '')
            {
                $uom = isset($myvalues[$uom_fieldname]) ? trim($myvalues[$uom_fieldname]) : '';
                $value_type_cd = isset($myvalues[$value_type_fieldname]) ? trim($myvalues[$value_type_fieldname]) : '';
                $src_type_name = 'Device CTDIvol';
                $bGood = $this->validateRadiationDoseInputs($bGood, $radiation_dose_tx, $uom, $value_type_cd, $src_type_name, $dose_fieldname, $uom_fieldname, $value_type_fieldname);            
            }
            $dose_fieldname = 'exam_dlp_radiation_dose_tx';
            $uom_fieldname = 'exam_dlp_radiation_dose_uom_tx';
            $value_type_fieldname = 'exam_dlp_radiation_dose_type_cd';
            $radiation_dose_tx = isset($myvalues[$dose_fieldname]) ? trim($myvalues[$dose_fieldname]) : '';
            if($radiation_dose_tx > '')
            {
                $uom = isset($myvalues[$uom_fieldname]) ? trim($myvalues[$uom_fieldname]) : '';
                $value_type_cd = isset($myvalues[$value_type_fieldname]) ? trim($myvalues[$value_type_fieldname]) : '';
                $src_type_name = 'Device DLP';
                $bGood = $this->validateRadiationDoseInputs($bGood, $radiation_dose_tx, $uom, $value_type_cd, $src_type_name, $dose_fieldname, $uom_fieldname, $value_type_fieldname);            
            }
            $dose_fieldname = 'exam_other_radiation_dose_tx';
            $uom_fieldname = 'exam_other_radiation_dose_uom_tx';
            $value_type_fieldname = 'exam_other_radiation_dose_type_cd';
            $radiation_dose_tx = isset($myvalues[$dose_fieldname]) ? trim($myvalues[$dose_fieldname]) : '';
            if($radiation_dose_tx > '')
            {
                $uom = isset($myvalues[$uom_fieldname]) ? trim($myvalues[$uom_fieldname]) : '';
                $value_type_cd = isset($myvalues[$value_type_fieldname]) ? trim($myvalues[$value_type_fieldname]) : '';
                $src_type_name = 'Device Other';
                $bGood = $this->validateRadiationDoseInputs($bGood, $radiation_dose_tx, $uom, $value_type_cd, $src_type_name, $dose_fieldname, $uom_fieldname, $value_type_fieldname);            
            }

            
            $dose_fieldname = 'exam_radioisotope_radiation_dose_tx';
            $uom_fieldname = 'exam_radioisotope_radiation_dose_uom_tx';
            $value_type_fieldname = 'exam_radioisotope_radiation_dose_type_cd';
            $radiation_dose_tx = isset($myvalues[$dose_fieldname]) ? trim($myvalues[$dose_fieldname]) : '';
            if($radiation_dose_tx > '')
            {
                $uom = isset($myvalues[$uom_fieldname]) ? trim($myvalues[$uom_fieldname]) : '';
                $value_type_cd = isset($myvalues[$value_type_fieldname]) ? trim($myvalues[$value_type_fieldname]) : '';
                $src_type_name = 'radioisotope';
                $bGood = $this->validateRadiationDoseInputs($bGood, $radiation_dose_tx, $uom, $value_type_cd, $src_type_name, $dose_fieldname, $uom_fieldname, $value_type_fieldname);            
            }
        } else
        if(substr($clickedvalue,0,14) == 'Interpretation')
        {
            //################################
            // INTERPRETATION COMPLETE BLOCK
            //################################
            
            if(!$oAA->allowInterpretationComplete($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            if(strpos($clickedvalue,'Commit') == FALSE)
            {
                if(!isset($myvalues['interpret_notes_tx']) || trim($myvalues['interpret_notes_tx']) == '')
                {
                    //Allow them to leave it blank!
                    drupal_set_message('No interpretation comment was provided.','warn');   //20150221
                    //form_set_error('interpret_notes_tx','Cannot leave the interpretation notes blank');
                    //$bGood = FALSE;
                }
            }
        } else
        if(substr($clickedvalue,0,2) == 'QA')
        {
            //####################
            // QA COMPLETE BLOCK
            //####################
            if(!$oAA->allowQAComplete($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            if(strpos($clickedvalue,'Commit') == FALSE)
            {
                if(!isset($myvalues['qa_notes_tx']) || trim($myvalues['qa_notes_tx']) == '')
                {
                    form_set_error('qa_notes_tx','Cannot leave the QA notes blank');
                    $bGood = FALSE;
                }
            }
        } else
        if(strpos($clickedvalue,'Commit') !== FALSE)
        {
            //######################
            // COMMIT TO VISTA BLOCK
            //######################
            if(!$oAA->allowCommitNotesToVista($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            if(!isset($myvalues['selected_vid']) || trim($myvalues['selected_vid']) == '')
            {
                drupal_set_message('Cannot commit to Vista without a selected visit','error');
                $bGood = FALSE;
            } else if(!isset($myvalues['commit_esig']) || trim($myvalues['commit_esig']) == '') {
                drupal_set_message('Cannot commit to Vista without an electronic signature','error');
                $bGood = FALSE;
            } else {
                try
                {
                    $oMdwsDao = $this->m_oContext->getMdwsClient();
                    $userDuz = $oMdwsDao->getDUZ();
                    $eSig = $myvalues['commit_esig'];
                    $bValidESig = MdwsUtils::validateEsig($oMdwsDao, $eSig);
                    if(!$bValidESig)
                    {
                        $bGood = FALSE;
                        drupal_set_message('Invalid Vista electronic signature provided','error');
                        $bGood = FALSE;
                    }
                } catch (\Exception $ex) {
                    $msg = 'Failed validation of electronic signature of userDUZ=['.$userDuz.'] because ' . $ex->getMessage(); 
                    error_log($msg);
                    drupal_set_message('Unable to validate the provided Vista electronic signature provided','error');
                    $bGood = FALSE;
                }
            }
        } else
        if(substr($clickedvalue,0,12) == 'Cancel Order')
        {
            //####################
            // CANCEL ORDER BLOCK
            //####################
            
            if(!$oAA->allowCancelOrder($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }

            if(!isset($myvalues['notes_tx']) || trim($myvalues['notes_tx']) == '')
            {
                if(!isset($myvalues['notes_tx']))
                {
                    drupal_set_message('Cannot cancel an order without an explanation.','error');
                } else {
                    form_set_error('notes_tx','Cannot suspend a ticket without an explanation.');
                }
                $bGood = FALSE;
            }
            
        } else
        if(substr($clickedvalue,0,7) == 'Suspend' || substr($clickedvalue,0,6) == 'Remove')
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
        if(substr($clickedvalue,0,9) == 'Unsuspend')
        {
            //################
            // UNSUSPEND BLOCK
            //################

            //drupal_set_message('TODO -- placeholder for unsuspend feature','warning');
            //$bGood = FALSE;
            
        } else
        if(substr($clickedvalue,0,7) == 'Reserve')
        {
            //#############################
            // RESERVE BLOCK
            //#############################

            if(!$oAA->allowReserveTicket($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            //Make sure we are okay to reserve this ticket.
            if($sCWFS !== 'AC' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
            {
                form_set_error('protocol1_nm','Only tickets in the active or review or collaborate status can be reserved!  This one is in the [' .$sCWFS. '] state!');
                $bGood = FALSE;
            }
        } else
        if(substr($clickedvalue,0,11) == 'Collaborate')
        {
            //#############################
            // COLLABORATE BLOCK
            //#############################

            if(!$oAA->allowCollaborateTicket($sCWFS, $feedback))
            {
                drupal_set_message($feedback,'error');
                $bGood = FALSE;
            }
            
            //Collaborate with the selected user.
            if(!isset($myvalues['collaboration_uid']) || !is_numeric($myvalues['collaboration_uid']))
            {
                form_set_error('collaboration_uid','The selected collaborator user id value ['.$myvalues['collaboration_uid'].'] is not valid!');
                $bGood = FALSE;
            }
            if(!isset($myvalues['collaboration_note_tx']) || strlen($myvalues['collaboration_note_tx']) < 1)
            {
                form_set_error('collaboration_note_tx','The collaboration request must include a note!');
                $bGood = FALSE;
            }
            
            //Make sure we are okay to reserve this ticket.
            if($sCWFS !== 'AC' && $sCWFS !== 'CO' && $sCWFS !== 'RV')
            {
                form_set_error('protocol1_nm','Only tickets in the active or review or collaborate status can be collaborated!  This one is in the [' .$sCWFS. '] state!');
                $bGood = FALSE;
            }
        } else {
            //Did not recognize the button STOP EVERYTHING!
            $diemsg = ('Did NOT recognize the ['.$clickedvalue.'] button pressed in looksValid function!!!>>>'.print_r($myvalues,TRUE));
            error_log($diemsg);
            throw new \Exception($diemsg);
        }
        
        if(!$bGood)
        {
            //Important if we do not set form_set_error then Drupal ignores result of this function!!!!
            form_set_error('form','Form needs corrections');
        }
        return $bGood;
    }    

    /**
     * Set details and return false if radiation dose information is not correct.
     */
    function validateRadiationDoseInputs($bGood
            , $radiation_dose_tx
            , $uom, $value_type_cd, $src_type_name
            , $dose_fieldname
            , $uom_fieldname
            , $value_type_fieldname)
    {
        if($radiation_dose_tx > '')
        {
            if($uom == '')
            {
                    form_set_error($dose_fieldname,'Cannot leave '.$src_type_name.' radiation exposure unit of measure blank when dose values were provided');
                    $bGood = FALSE;
            } elseif(strpos($uom,'?') !== FALSE) {
                    form_set_error($uom_fieldname,'Cannot have question marks in '.$src_type_name.' radiation exposure unit of measure');
                    $bGood = FALSE;
            } else {
                    $dose_values = explode(',', $radiation_dose_tx);
                    $sequence_num = 0;
                    foreach($dose_values as $dose)
                    {
                            $sequence_num++;
                            $cleandose = trim($dose);
                            if($cleandose == '')
                            {
                                    form_set_error($dose_fieldname,'Cannot have blank '.$src_type_name.' radiation dose values in a list (see position '.$sequence_num.')');
                                    $bGood = FALSE;
                                    break;
                            }
                            if(!is_numeric($cleandose))
                            {
                                    form_set_error($dose_fieldname,'Cannot have non-numeric '.$src_type_name.' radiation dose values in a list (see "'.$cleandose.'" at position '.$sequence_num.')');
                                    $bGood = FALSE;
                                    break;
                            }
                    }
                    if($sequence_num > 0 && $value_type_cd == '')
                    {
                        form_set_error($value_type_fieldname,'Must provide '.$src_type_name.' radiation dose value type');
                        $bGood = FALSE;
                    }
            }
        }
        return $bGood;
    }
    
    
    private function saveChecklist($nSiteID,$nIEN,$nUID,$updated_dt,$aAnswers)
    {
        $bSuccess = TRUE;
        if(is_array($aAnswers))
        {
            foreach($aAnswers as $aQuestion)
            {
                $shortname = $aQuestion['shortname'];
                $response = $aQuestion['response'];
                $comment = $aQuestion['comment'];
                
                //See if this response is already recorded for this user.
                $result = db_select('raptor_ticket_checklist','t')
                        ->fields('t')
                        ->condition('question_shortname',$shortname,'=')
                        ->condition('siteid',$nSiteID,'=')
                        ->condition('IEN',$nIEN,'=')
                        ->condition('author_uid',$nUID,'=')
                        ->execute();
                $nRows = $result->rowCount();
                if($nRows > 0)
                {
                    //Move the existing record to the raptor_ticket_checklist table first
                    $item = $result->fetch();
                    $oInsert = NULL;
                    try
                    {
                        $oInsert = db_insert('raptor_ticket_checklist_replaced')
                                ->fields(array(
                                    'siteid' => $nSiteID,
                                    'IEN' => $nIEN,
                                    'question_shortname' => $item->question_shortname,
                                    'question_tx' => $item->question_tx,
                                    'answer_tx' => $item->answer_tx,
                                    'comment_prompt_tx' => $item->comment_prompt_tx,
                                    'comment_tx' => $item->comment_tx,
                                    'author_uid' => $nUID,
                                    'created_dt' => $updated_dt,
                                    'replaced_dt' => $updated_dt,
                                ))
                                ->execute();
                        $result = db_delete('raptor_ticket_checklist')
                                ->condition('question_shortname',$shortname,'=')
                                ->condition('siteid',$nSiteID,'=')
                                ->condition('IEN',$nIEN,'=')
                                ->condition('author_uid',$nUID,'=')
                                ->execute();
                    }
                    catch(\Exception $e)
                    {
                        error_log('Failed to create raptor_ticket_checklist_replaced: ' . $e . "\nDetails..." . print_r($oInsert,TRUE));
                        drupal_set_message('Unable to properly save replace a record because '.$e->getMessage(),'error');
                        $bSuccess = FALSE;
                    }
                    try
                    {
                        db_delete('raptor_ticket_checklist')
                                ->condition('question_shortname',$shortname,'=')
                                ->condition('siteid',$nSiteID,'=')
                                ->condition('IEN',$nIEN,'=')
                                ->condition('author_uid',$nUID,'=')
                                ->execute();
                    }
                    catch(\Exception $e)
                    {
                        error_log('Failed to delete existing raptor_ticket_checklist: ' . $e);
                        drupal_set_message('Unable to properly save replace a record because '.$e->getMessage(),'error');
                        $bSuccess = FALSE;
                    }
                }
                //Record this response even if they have already answered it before.
                $result = db_select('raptor_checklist_question','q')
                        ->fields('q')
                        ->condition('question_shortname',$shortname,'=')
                        ->execute();
                $item = $result->fetch();
                $question_tx = $item->question_tx;
                $comment_prompt_tx = $item->comment_prompt_tx;
                try
                {
                    $oInsert = db_insert('raptor_ticket_checklist')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'question_shortname' => $shortname,
                                'question_tx' => $question_tx,
                                'answer_tx' => $response,
                                'comment_prompt_tx' => $comment_prompt_tx,
                                'comment_tx' => $comment,
                                'author_uid' => $nUID,
                                'created_dt' => $updated_dt,
                            ))
                            ->execute();
                }
                catch(\Exception $e)
                {
                    error_log('Failed to create raptor_ticket_checklist: ' . $e . "\nDetails..." . print_r($oInsert,true));
                    drupal_set_message('Unable to properly save this record because '.$e->getMessage(),'error');
                    $bSuccess = FALSE;
                }
            }
        }
        return $bSuccess;
    }
    
    
    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase($clickedbutton, $myvalues)
    {
        $bSuccess = TRUE;   //Assume happy case.
        $successMsg = NULL;
        $removeTicketLock = TRUE;
        
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);
        $oAA = new \raptor\AllowedActions();    //Leverage workflow dependences from special class
        $sTrackingID = $this->m_oTT->getTrackingID($nSiteID, $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());
        
        $sFullTicketID = $nSiteID . '-' . $nIEN;

        if(isset($myvalues['collaboration_uid']) 
                && is_numeric($myvalues['collaboration_uid']))
        {
            //Handle it this way because simple javascript submit seems to assume Approve button otherwise.
            $clickedvalue = 'Collaborate';
        } else {
            $clickedvalue = $clickedbutton['#value'];
        }
        
        //die('>>>>clicked['.$clickedbutton["#value"].'] values>>>>'.print_r($myvalues,TRUE));
        if($bSuccess)
        {
            if(substr($clickedvalue,0,7) == 'Approve')
            {
                //################
                // APPROVAL BLOCK
                //################

                $sNewWFS = 'AP';
                $bSuccess = $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);
                if($bSuccess)
                {
                    $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                    
                    //Write success message
                    $successMsg = ('Approved ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(substr($clickedvalue,0,9) == 'Unapprove')
            {
                //#################
                // UNAPPROVAL BLOCK
                //#################

                try
                {
                    $sNewWFS = 'AC';
                    $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
                
                    //Make a success message
                    $successMsg = ('Changed workflow back to un-approved ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');

                    //Add a comment into the ticket
                    $this->m_oUtility->createSimpleProtocolNotesRecord($nSiteID, $nIEN, $successMsg, $nUID, $updated_dt);
                } catch (\Exception $ex) {
                    $errmsg = "Failed to unapprove because ".$ex->getMessage();
                    error_log($errmsg);
                    drupal_set_message($errmsg,'error');
                    $bSuccess = FALSE;
                }
            } else
            if(substr($clickedvalue,0,7) == 'Request')
            {
                //#######################
                // REQUEST APPROVAL BLOCK
                //#######################

                $sNewWFS = 'RV';
                $bSuccess = $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);
                if($bSuccess)
                {
                    $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                    
                    //Write success message
                    $successMsg = ('Requested approval for ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(substr($clickedvalue,0,11) == 'Acknowledge')
            {
                //##################
                // ACKNOWLEDGE BLOCK
                //##################

                //Save checklist settings.
                $aAnswers = isset($myvalues['questions']['thisuser']) ? $myvalues['questions']['thisuser'] : NULL;
                
                $bSuccess = $this->saveChecklist($nSiteID,$nIEN,$nUID,$updated_dt,$aAnswers);
                if($bSuccess)
                {
                    $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                
                    $sNewWFS = 'PA';
                    $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

                    //Write success message
                    $successMsg = ('Acknowledged ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(substr($clickedvalue,0,13) == 'Unacknowledge')
            {
                //####################
                // UNACKNOWLEDGE BLOCK
                //####################

                try
                {
                    $sNewWFS = 'AP';
                    $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
                
                    //Make a success message
                    $successMsg = ('Changed workflow back to unacknowledged ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');

                    //Add a comment into the ticket
                    $this->m_oUtility->createSimpleProtocolNotesRecord($nSiteID, $nIEN, $successMsg, $nUID, $updated_dt);
                } catch (\Exception $ex) {
                    $errmsg = "Failed to un-acknowledged because ".$ex->getMessage();
                    error_log($errmsg);
                    drupal_set_message($errmsg,'error');
                    $bSuccess = FALSE;
                }
            } else
            if(substr($clickedvalue,0,4) == 'Exam')
            {
                //####################
                // EXAM COMPLETE BLOCK
                //####################

                $sNewWFS = 'EC';
                $bSuccess = $this->m_oUtility->saveAllExamFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                if($bSuccess)
                {
                    $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                    
                    //Write success message
                    $successMsg = ('Examined patient for ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(substr($clickedvalue,0,9) == 'Save Exam')
            {
                //#####################
                // SAVE EXAM DATA BLOCK
                //#####################

                $removeTicketLock = FALSE;
                $sNewWFS = 'SAVE_SOFAR';    //Special keyword for function
                $bSuccess = $this->m_oUtility->
                        saveAllExamFieldValues($nSiteID, $nIEN, $nUID
                                , $sCWFS
                                , $sNewWFS
                                , $updated_dt
                                , $myvalues);
                if($bSuccess)
                {
                    $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                    
                    //Write success message
                    $successMsg = ('Saved current exam values ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(substr($clickedvalue,0,5) == 'Inter')
            {
                //######################################################
                // INTERPRETATION COMPLETE BLOCK with NO commit to Vista
                //######################################################
                $sNewWFS = 'QA';
                $bSuccess = $this->m_oUtility->saveAllInterpretationFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                if($bSuccess)
                {
                    //Also save QA fields in case they filled them in.
                    $bSuccess = $this->m_oUtility->saveAllQAFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                    if($bSuccess)
                    {
                        $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

                        //Write success message
                        $successMsg = ('Interpretation completed ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                    }
                }
            } else
            if(substr($clickedvalue,0,2) == 'QA')
            {
                //##########################################
                // QA COMPLETE BLOCK with NO commit to Vista
                //##########################################
                $sNewWFS = 'QA';    //Stays in QA forever
                $bSuccess = $this->m_oUtility->saveAllQAFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                if($bSuccess)
                {
                    $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
                    
                    //Write success message
                    $successMsg = ('QA completed ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                }
            } else
            if(strpos($clickedvalue,'Commit') !== FALSE)
            {
                //NOTE: The 'Commit' button text is added by the looksValidFormState function!!!
                //######################
                // COMMIT TO VISTA BLOCK
                //######################
                
                $sNewWFS = 'QA';    //Stays in QA forever
                if(strpos($clickedvalue,'Interpret') !== FALSE) 
                {
                    $bSuccess = $this->m_oUtility->
                            saveAllInterpretationFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                } elseif(strpos($clickedvalue,'QA') !== FALSE) {
                    $bSuccess = $this->m_oUtility->
                            saveAllQAFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                } elseif(strpos($clickedvalue,'Exam') !== FALSE) {
                    $bSuccess = $this->m_oUtility->
                            saveAllExamFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues);
                } elseif(strpos($clickedvalue,'Acknowledge') !== FALSE) {
                    $aAnswers = isset($myvalues['questions']['thisuser']) ? $myvalues['questions']['thisuser'] : NULL;
                    $bSuccess = $this->saveChecklist($nSiteID,$nIEN,$nUID,$updated_dt,$aAnswers);
                    if($bSuccess)
                    {
                        $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                    }
                } else {
                    throw new \Exception('Did not recognize button click value ['.$clickedvalue.']');
                }
                if($bSuccess)
                {
                    $bSuccess = $this->commitDataToVista($nSiteID, $nIEN, $nUID, $sCWFS, $myvalues);
                    if($bSuccess)
                    {
                        $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
                        if(substr($clickedvalue,0,5) == 'Inter') {
                            //We were in interpret mode so write the QA message
                            $successMsg = ('Interpretation completed and updated VistA for ' 
                                    . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                        } elseif(strpos($clickedvalue,'Exam') !== FALSE) {
                            $successMsg = ('Examined patient and updated VistA for ' 
                                    . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                        } elseif(strpos($clickedvalue,'Acknowledge') !== FALSE) {
                            $successMsg = ('Acknowledged and updated VistA for ' 
                                    . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                        } else {
                            //Write success message for QA mode
                            $successMsg = ('QA completed and updated VistA for ' 
                                    . $sFullTicketID . ' (' . $myvalues['procName'] .')');
                        }
                    }
                }
            } else
            if(substr($clickedvalue,0,12) == 'Cancel Order')
            {
                //####################
                // CANCEL ORDER BLOCK
                //####################

                //Cancel the order now
                try
                {
                    
                    $is_okay = TRUE;
                    $orderIENs = $this->getSelectedOrderIENs($myvalues);
                    $reasonCode = $myvalues['cancelreason'];
                    $cancelcomment = $myvalues['cancelcomment'];

                    die('TODO CANCEL THESE IENS >>>>'.print_r($orderIENs,TRUE) 
                            . '<br>For reason="'.$reasonCode.'"'
                            . '<br>comment="'.$cancelcomment.'"'
                            . '<br>Hit the back button to test again');

                    
                    /*
                    $oInsert = db_insert('raptor_ticket_suspend_notes')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'notes_tx' => $myvalues['notes_tx'],
                                'author_uid' => $nUID,
                                'created_dt' => $updated_dt,
                            ))
                            ->execute();
                     * 
                     */
                }
                catch(\Exception $e)
                {
                    error_log('Failed to create raptor_ticket_suspend_notes: ' . $e . "\nDetails..." . print_r($myvalues,true));
                    form_set_error('suspend_notes_tx','Failed to save notes for this ticket!');
                    $bSuccess = FALSE;
                }

                $sNewWFS = 'IA';
                $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

                //Write success message
                $successMsg = ('Canceled Order ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
            } else
            if(substr($clickedvalue,0,7) == 'Suspend' || substr($clickedvalue,0,6) == 'Remove')
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
                catch(\Exception $e)
                {
                    error_log('Failed to create raptor_ticket_suspend_notes: ' . $e . "\nDetails..." . print_r($myvalues,true));
                    form_set_error('suspend_notes_tx','Failed to save notes for this ticket!');
                    $bSuccess = FALSE;
                }

                $sNewWFS = 'IA';
                $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

                //Write success message
                $successMsg = ('Suspended ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
            } else
            if(substr($clickedvalue,0,9) == 'Unsuspend')
            {
                //################
                // UNSUSPEND BLOCK
                //################

                //Create the raptor_ticket_unsuspend_notes record now
                try
                {
                    $oInsert = db_insert('raptor_ticket_unsuspend_notes')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'notes_tx' => 'Unsuspended by user', //$myvalues['unsuspend_notes_tx'],
                                'author_uid' => $nUID,
                                'created_dt' => $updated_dt,
                            ))
                            ->execute();
                }
                catch(\Exception $e)
                {
                    error_log('Failed to create raptor_ticket_unsuspend_notes: ' . $e . "\nDetails..." . print_r($myvalues,true));
                    form_set_error('unsuspend_notes_tx','Failed to save notes for this ticket!');
                    $bSuccess = FALSE;
                }

                $sNewWFS = 'AC';
                $this->m_oUtility->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

                //Write success message
                $successMsg = ('Unsuspended ' . $sFullTicketID . ' (' . $myvalues['procName'] .')');
            } else
            if(substr($clickedvalue,0,7) == 'Reserve' || substr($clickedvalue,0,11) == 'Collaborate')
            {
                //#############################
                // RESERVE or COLLABORATE BLOCK
                //#############################
                $this->writeContraindicationAcknowledgements($nSiteID, $nIEN, $nUID, $myvalues);
                
                $sMode = NULL;  //Indicate which of the two modes. C or R
                if(substr($clickedvalue,0,11) == 'Collaborate')
                {
                    //Collaborate with the selected user.
                    $sMode = 'C';
                    $collaboration_uid = $myvalues['collaboration_uid'];
                    $collaboration_note_tx = $myvalues['collaboration_note_tx'];
                } else {
                    //Collaborate with yourself.
                    $sMode = 'R';
                    $collaboration_uid = $nUID;
                    $collaboration_note_tx = 'Reserving for myself.';
                }

                //Create the raptor_ticket_collaboration record now
                try
                {
                    $result = db_select('raptor_ticket_collaboration','p')
                            ->fields('p')
                            ->condition('siteid',$nSiteID,'=')
                            ->condition('IEN',$nIEN,'=')
                            ->condition('collaborator_uid',$collaboration_uid,'=')
                            ->condition('active_yn',1,'=')
                            ->execute();
                    $nRows = $result->rowCount();
                    if($nRows > 0 && $sMode == 'R')
                    {
                        //No need to write records for a reservation if same user.  Note: not same thing as collaborate case!
                        $successMsg = 'Already reserved ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .') by the same user.';
                    } else {
                        //Deactivate any existing collaboration records
                        try
                        {
                            $updated = db_update('raptor_ticket_collaboration')
                                    ->fields(array('active_yn' => 0))
                                    ->condition('siteid',$nSiteID,'=')
                                    ->condition('IEN',$nIEN,'=')
                                    ->condition('active_yn',1,'=')
                                    ->execute();
                        } catch (\Exception $ex) {
                            $showmsg = 'Unable deactivate existing collaboration settings!';
                            error_log($showmsg . '  Failed to reserve because failed update: ' . $ex . "\nDetails..." . print_r($myvalues,true));
                            drupal_set_message($showmsg,'error');
                            return 0;
                        }
                        try
                        {
                            $oInsert = db_insert('raptor_ticket_collaboration')
                                    ->fields(array(
                                        'siteid' => $nSiteID,
                                        'IEN' => $nIEN,
                                        'requester_uid' => $nUID,
                                        'requested_dt' => $updated_dt,
                                        'requester_notes_tx' => $collaboration_note_tx,
                                        'collaborator_uid' => $collaboration_uid,
                                        'active_yn' => 1,
                                    ))
                                    ->execute();
                        } catch (\Exception $ex) {
                            $showmsg = 'Unable insert collaboration record!';
                            error_log($showmsg.' Failed to collaborate because failed raptor_ticket_collaboration insert: ' . $ex . "\nDetails..." . print_r($myvalues,true));
                            drupal_set_message($showmsg,'error');
                            return 0;
                        }
                    }
                }
                catch(\Exception $ex)
                {
                    error_log('Failed to create raptor_ticket_collaboration: ' . $ex . "\nDetails..." . print_r($myvalues,true));
                    form_set_error('protocol1_nm','Failed to reserve this ticket!');
                    $bSuccess = FALSE;
                }

                $sNewWFS = 'CO'; 
                $this->m_oUtility->saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt,$myvalues);

                //Write success message
                if($sMode == 'C')
                {
                    $oOtherUser = new \raptor\UserInfo($collaboration_uid);
                    $sFullname = $oOtherUser->getFullName();
                    $successMsg = 'Collaboration with '.$sFullname.' set for '. $sFullTicketID . ' (' . $myvalues['procName'] .')';
                } else {
                    $successMsg = 'Reserved '. $sFullTicketID . ' (' . $myvalues['procName'] .')';
                }
            } else {
                //Did not recognize the button STOP EVERYTHING!
                $diemsg = ('Did NOT recognize the ['.$clickedvalue.'] button pressed in updateDatabase!!!>>>'.print_r($myvalues,TRUE));
                error_log($diemsg);
                die($diemsg);
            }
        }
        if(!$bSuccess)
        {
            drupal_set_message('Trouble processing the page.','error');
        } else {
            if($successMsg == NULL || strlen(trim($successMsg)) == 0)
            {
                //If this happens, help us debug.
                drupal_set_message('Missing success message','warning');
            } else {
                drupal_set_message($successMsg);
            }
        }
        
        if($removeTicketLock)
        {
            //Remove any lock if we had one.
            $this->m_oTT->markTicketUnlocked($sTrackingID, $nUID);
        }
        
        return $bSuccess;
    }
    
    function isValidEsig($eSig,$oMdwsDao=NULL)
    {
        if($oMdwsDao == NULL)
        {
            $oMdwsDao = $this->m_oContext->getMdwsClient();
        }
        return MdwsUtils::validateEsig($oMdwsDao, $eSig);
    }
    
    /**
     * Write all the RAPTOR data of curent ticket into VISTA.
     * - Safety Checklist information
     * - General notes
     * @return boolean TRUE if success, else FALSE
     */
    function commitDataToVista($nSiteID,$nIEN,$nUID,$sCWFS,$myvalues,$encounterString=NULL)
    {
        $bSuccess = TRUE;
        $errormsg = NULL;
        error_log('Starting commitDataToVista('.$nSiteID.','.$nIEN.') at '.microtime());

        $commit_dt = date("Y-m-d H:i:s", time());
        $prev_commit_dt = $this->getDateMostRecentVistaCommitDate($nSiteID,$nIEN);
        
        //Verify the electronic sigature
        $eSig = $myvalues['commit_esig'];
        $oMdwsDao = $this->m_oContext->getMdwsClient();
        //$bValidESig = MdwsUtils::validateEsig($oMdwsDao, $eSig);
        $bValidESig = $this->isValidEsig($eSig, $oMdwsDao);
        if(!$bValidESig)
        {
            $errormsg = ('Trouble committing ticket '.$nSiteID.'-'.$nIEN.' Safety Checklist note to Vista because invalid electronic signature');
            $bSuccess = FALSE;
        }
         
        if($bSuccess)
        {
            module_load_include('php', 'raptor_datalayer', 'core/AllUsers');
            $oAllUsers = new \raptor\AllUsers();

            try
            {
                $oMdwsDao = $this->m_oContext->getMdwsClient();
                if($encounterString == NULL)
                {
                    $aVisits = \raptor\MdwsUtils::getVisits($oMdwsDao);
                    if(is_array($aVisits) && count($aVisits) > 0)
                    {
                        if(isset($myvalues['selected_vid']) && $myvalues['selected_vid'] != '')
                        {
                            $selected_vid = $myvalues['selected_vid'];
                            //vid_<LOCATIONID>_<TIMESTAMP>
                            $vidparts = explode('_',$selected_vid);
                            $locationId = $vidparts[1];
                            $visitTimestamp = $vidparts[2];
                            foreach($aVisits as $aVisit)
                            {
                                if($aVisit['locationId'] == $locationId && $aVisit['visitTimestamp'] == $visitTimestamp)
                                {
                                    $encounterString = \raptor\MdwsUtils::getEncounterStringFromVisit($aVisit['visitTO']);   //TODO ask the user to pick one!!!
                                }
                            }
                            if($encounterString == NULL)
                            {
                                throw new \Exception('Did NOT find an encounter string for $selected_vid=['.$selected_vid.'] in '.print_r($aVisits,TRUE));
                            }
                        } else {
                            throw new \Exception('Did not find any selected visit for the VISTA writeback!');
                            //drupal_set_message('TODO remove automatic selection of first visit for writeback.  Used for writing this record!','warning');
                            //error_log('commitChecklistToVista got visits='.print_r($aVisits,TRUE).'');                
                            //$encounterString = \raptor\MdwsUtils::getEncounterStringFromVisit($aVisits[0]['visitTO']);   //TODO ask the user to pick one!!!
                            //error_log('commitChecklistToVista got most recent visit on ticket '.$nSiteID.'-'.$nIEN.' as encounterString=['.$encounterString.']');                
                        }
                    } else {
                       drupal_set_message('Did NOT find any visits to which we can commit a note!','error'); 
                       $bSuccess = FALSE;
                    }
                }

                //Write the note(s).
                $newNoteIen = NULL;
                try
                {
                    $userDuz = $oMdwsDao->getDUZ();

                    //Pull values from database that have not yet been committed to VISTA
                    $aChecklistData = array();
                    $this->addUncommittedChecklistDetailsToNotesArray($nSiteID, $nIEN, $oAllUsers, $prev_commit_dt, $aChecklistData);
                    if(count($aChecklistData)>0)
                    {
                        //Write the checklist note
                        $newNoteIen = \raptor\MdwsUtils::writeRaptorSafetyChecklist($oMdwsDao,$aChecklistData,$encounterString,NULL);
                        MdwsUtils::signNote($oMdwsDao, $newNoteIen, $userDuz, $eSig);
                    }

                    //Pull values from database that have not yet been committed to VISTA
                    $noteTextArray = array();
                    $this->addUncommittedDetailsToNotesArray($nSiteID, $nIEN, $oAllUsers, $prev_commit_dt, $noteTextArray);
                    if(count($noteTextArray)>0)
                    {
                        //Yes, write the general note.
                        $newGeneralNoteIen = \raptor\MdwsUtils::writeRaptorGeneralNote($oMdwsDao, $noteTextArray, $encounterString, NULL); 
                        MdwsUtils::signNote($oMdwsDao, $newGeneralNoteIen, $userDuz, $eSig);
                    }
                } catch (\Exception $ex) {
                    drupal_set_message('Trouble in commit because ' . $ex->getMessage(),'error');
                    throw $ex;
                }
                if($newNoteIen != NULL)
                {
                    error_log('commitDataToVista got newNoteIen=['.$newNoteIen.'] for encounter string='.$encounterString);   
                }

            } catch (\Exception $ex) {

                $errormsg = ('Trouble committing ticket '.$nSiteID.'-'.$nIEN.' Safety Checklist note to Vista because ' . $ex->getMessage());
                throw $ex;
            }
        }

        if($bSuccess)
        {
            //Okay, record that we successfully committed.
            try
            {
                db_insert('raptor_ticket_commit_tracking')
                        ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'workflow_state' => $sCWFS,
                        'author_uid' => $nUID,
                        'commit_dt' => $commit_dt,
                  ))->execute();
            } catch (\Exception $ex) {
                $errormsg = ('Trouble committing ticket '.$nSiteID.'-'.$nIEN.' to raptor_ticket_commit_tracking because ' . $ex->getMessage());
                throw $ex;
            }
        }        
        
        if($bSuccess)
        {
            drupal_set_message('Committed patient data to VistA');
        } else {
            if($errormsg != NULL)
            {
                error_log('failed commit to vista>>> '.$errormsg);
                drupal_set_message($errormsg,'error');
            } else {
                drupal_set_message('Trouble committing patient data to VistA','error');
            }
        }
        error_log('Finished commitDataToVista on ticket '.$nSiteID.'-'.$nIEN.' at '.microtime());
        return $bSuccess;
    }

    
    function addUncommittedChecklistDetailsToNotesArray($nSiteID, $nIEN, $oAllUsers, $prev_commit_dt, &$noteTextArray)
    {
        $tid = $nSiteID.'-'.$nIEN;
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOrderInfo = $oWL->getDashboardMap();
        
        $aQuestionsMetadata = $this->getAllSavedSafetyChecklistTicketData($nSiteID,$nIEN,$oAllUsers,$prev_commit_dt);
        if(count($aQuestionsMetadata)>0)
        {
            //We have some un-committed checklist questions.
            $aQuestions = $aQuestionsMetadata['questions'];
            if(!is_array($aQuestions) || count($aQuestions) < 1)
            {
                $emsg = 'Did NOT find checklist in commitChecklistToVista('.$nSiteID.','.$nIEN.') at '.microtime();
                throw new \Exception($emsg);
            }
            $aAuthors = $aQuestionsMetadata['authors'];
            $dLastDate = $aQuestionsMetadata['last_date'];

            $noteTextArray = array();
            $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Title',$aOrderInfo,'Procedure');
            $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Created Date/Time',$aOrderInfo,'RequestedDate');
            $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Embedded Due Date',$aOrderInfo,'DesiredDate');
            $this->addFormattedVistaNoteRow($noteTextArray,'Tracking ID',$tid);
            $this->addFormattedVistaNoteRow($noteTextArray,'Checklist Type','Safety Checklist');
            $this->addFormattedVistaNoteRow($noteTextArray,'Completion Date',$dLastDate);
            $this->addFormattedVistaNoteRow($noteTextArray,'Site ID',$nSiteID);
            $this->addFormattedVistaNoteRow($noteTextArray,'Ticket IEN',$nIEN);
            $this->addFormattedVistaNoteRow($noteTextArray,'Total Responses',count($aQuestions));
            $noteTextArray[] = '';
            foreach($aQuestions as $aQuestion)
            {
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Shortname',$aQuestion['question_shortname']);
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Text',$aQuestion['question_tx']);
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Answer','"'.$aQuestion['answer_tx'].'"');
                $nAuthorUID = $aQuestion['author_uid'];
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Answer Author',$aAuthors[$nAuthorUID]);
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Comment Prompt','"'.$aQuestion['comment_prompt_tx'].'"');
                $this->addFormattedVistaNoteRow($noteTextArray,'Question Comment Answer','"'.$aQuestion['comment_tx'].'"');
                $noteTextArray[] = '';
            }
            $this->addFormattedVistaNoteRow($noteTextArray,'Total Authors',count($aAuthors));
        }
    }
    
    /**
     * Add rows to the array of all uncommitted notes items
     */
    function addUncommittedDetailsToNotesArray($nSiteID, $nIEN, $oAllUsers, $prev_commit_dt, &$noteTextArray)
    {
        //Get all the VISTA baseline information.
        $tid = $nSiteID.'-'.$nIEN;
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOrderInfo = $oWL->getDashboardMap();
        $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Title',$aOrderInfo,'Procedure');
        $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Created Date/Time',$aOrderInfo,'RequestedDate');
        $this->addFormattedVistaNoteRow($noteTextArray,'Order CPRS Embedded Due Date',$aOrderInfo,'DesiredDate');
        $this->addFormattedVistaNoteRow($noteTextArray,'Tracking ID',$tid);
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        
        //Get all the scheduler information.
        if(isset($aOrderInfo['ScheduledDate']) && $aOrderInfo['ScheduledDate'] > '')
        {
            $scheduled_dt = $aOrderInfo['ScheduledDate'];
            $this->addFormattedVistaNoteRow($noteTextArray,'Scheduled Date/Time',$scheduled_dt);
        }
        $getvalues = array();
        $prevnotes = $this->getSchedulerNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        foreach($prevnotes as $prevnoteinfo)
        {
            if(count($noteTextArray)>0)
            {
                $noteTextArray[] = '';
            }
            $author_uid = $prevnoteinfo['author_uid'];
            $created_dt = $prevnoteinfo['created_dt'];
            $notes_tx = $prevnoteinfo['notes_tx'];
            $notes_critical_yn = $prevnoteinfo['notes_critical_yn'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            if($userinfo == NULL)
            {
                $fullname = 'RAPTOR user '.$author_uid;
            } else {
                $fullname = $userinfo->getFullName();
            }
            if(trim($notes_tx) == '')
            {
                $notes_tx = 'BLANK';
            }
            if($notes_critical_yn == 1)
            {
                $notetype = 'Critical';
            } else {
                $notetype = 'Standard';
            }
            $this->addFormattedVistaNoteRow($noteTextArray,'Scheduler '.$notetype.' Note Date',$created_dt);
            $this->addFormattedVistaNoteRow($noteTextArray,'Scheduler '.$notetype.' Note Author',$fullname);
            $this->addFormattedVistaNoteRow($noteTextArray,'Scheduler '.$notetype.' Note Text',$notes_tx);
        }
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        
        //Get all the protocol settings
        $relevant_protocol_shortname = NULL;
        $this->loadProtocolFieldValues($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        if($getvalues['protocol_data_from_database'])
        {
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Settings Approved Date',$getvalues,'protocol_data_created_dt');
            $author_uid = $getvalues['protocol_approval_author_uid'];
            $relevant_protocol_shortname = $getvalues['protocol1_nm'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            $fullname = $userinfo->getFullName();
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Settings Approved By',$fullname);

            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Primary Selection ID',$getvalues,'protocol1_nm');
            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Primary Selection NAME',$getvalues,'protocol1_fullname');
            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Primary Selection MODALITY',$getvalues,'protocol1_modality_abbr');
            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Secondary Selection ID',$getvalues,'protocol2_nm');
            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Secondary Selection NAME',$getvalues,'protocol2_fullname');
            $this->addFormattedVistaNoteRow($noteTextArray
                    ,'Protocol Secondary Selection MODALITY',$getvalues,'protocol2_modality_abbr');
            
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Oral Hydration',$getvalues,'hydration_oral_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note IV Hydration',$getvalues,'hydration_iv_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Oral Sedation',$getvalues,'sedation_oral_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note IV Sedation',$getvalues,'sedation_iv_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Enteric Contrast',$getvalues,'contrast_enteric_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note IV Contrast',$getvalues,'contrast_iv_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Enteric Radionuclide',$getvalues,'radioisotope_enteric_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note IV Radionuclide',$getvalues,'radioisotope_iv_customtx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Allergy',$getvalues,'allergy_kw');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Claustrophobic',$getvalues,'claustrophobic_kw');
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol Note Consent Required',$getvalues,'consent_req_kw');
        }
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }

        //Collect the protocol notes.
        $getvalues = array();
        $prevnotes = $this->getProtocolNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        foreach($prevnotes as $prevnoteinfo)
        {
            if(count($noteTextArray)>0)
            {
                $noteTextArray[] = '';
            }
            $author_uid = $prevnoteinfo['author_uid'];
            $created_dt = $prevnoteinfo['created_dt'];
            $notes_tx = $prevnoteinfo['notes_tx'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            if($userinfo == NULL)
            {
                $fullname = 'RAPTOR user '.$author_uid;
            } else {
                $fullname = $userinfo->getFullName();
            }
            if(trim($notes_tx) == '')
            {
                $notes_tx = 'BLANK';
            }
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol General Note Date',$created_dt);
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol General Note Author',$fullname);
            $this->addFormattedVistaNoteRow($noteTextArray,'Protocol General Note Text',$notes_tx);
        }
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        
        //raptor_ticket_contraindication
        $sRowLabel = '[Contraindication Acknowledgement';
        $query=db_select('raptor_ticket_contraindication', 't')
                ->fields('t')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
                if($prev_commit_dt != NULL)
                {
                    $query->condition('created_dt', $prev_commit_dt,'>');
                }
                $query->orderBy('rule_nm', 'DESC');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $acknowledged_dt = $record['created_dt'];
            $rule_nm = $record['rule_nm'];
            $author_uid = $record['author_uid'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            $fullname = $userinfo->getFullName();
            $noteTextArray[] = $sRowLabel . $rule_nm . ' acknowledged by ' . $fullname . ' on '. $acknowledged_dt;
        }
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        
        //Collect the exam data.
        $getvalues = array();
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        $getvalues['protocol1_nm'] = $relevant_protocol_shortname;  //Because need for exam collection
        $this->loadExamFieldValues($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        if($getvalues['exam_data_from_database'])
        {
            $author_uid = $getvalues['exam_author_uid'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            $fullname = $userinfo->getFullName();
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Author',$fullname);
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Date',$getvalues,'exam_data_created_dt');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Oral Hydration',$getvalues,'exam_hydration_oral_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note IV Hydration',$getvalues,'exam_hydration_iv_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Oral Sedation',$getvalues,'exam_sedation_oral_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note IV Sedation',$getvalues,'exam_sedation_iv_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Enteric Contrast',$getvalues,'exam_contrast_enteric_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note IV Contrast',$getvalues,'exam_contrast_iv_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Enteric Radionuclide',$getvalues,'exam_radioisotope_enteric_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note IV Radionuclide',$getvalues,'exam_radioisotope_iv_tx');
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Consent Received',$getvalues,'exam_consent_received_kw');
            
            /*
            $dose_details = $getvalues['exam_radioisotope_radiation_dose_map'];
            if(is_array($dose_details))
            {
                foreach($dose_details as $uom=>$values)
                {
                    $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Radionuclide Radiation UoM',$uom);
                    foreach($values as $dose_record)
                    {
                        $dose = $dose_record['dose'];
                        $qcd = $dose_record['dose_type_cd'];
                        $qterm = ($qcd == 'E' ? ' (Estimate)' : ($qcd == 'A' ? ' (Actual)' : (trim($qcd) > '' ? ' ('.$qcd.')' : '' )));
                        $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Radionuclide Radiation Dose',$dose.' '.$uom.$qterm);
                    }
                }
            }
            $dose_details = $getvalues['exam_other_radiation_dose_map'];
            if(is_array($dose_details))
            {
                foreach($dose_details as $uom=>$values)
                {
                    $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Machine-Produced Radiation Exposure UoM',$uom);
                    foreach($values as $dose_record)
                    {
                        $dose = $dose_record['dose'];
                        $qcd = $dose_record['dose_type_cd'];
                        $qterm = ($qcd == 'E' ? ' (Estimate)' : ($qcd == 'A' ? ' (Actual)' : (trim($qcd) > '' ? ' ('.$qcd.')' : '' )));
                        $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note Machine-Produced Radiation Exposure Data',$dose.' '.$uom.$qterm);
                    }
                }
            }
            */
            
            //Process ALL the possible radiation dose input areas.
            $littlename_map = RadiationDoseHelper::getDoseSourceLittlenameMap();
            foreach($littlename_map as $dose_source_code=>$littlename)
            {
                $dose_details = $getvalues['exam_'.$littlename.'_radiation_dose_map'];
                if(is_array($dose_details))
                {
                    $category_term=RadiationDoseHelper::getDefaultTermForDoseSource($dose_source_code);
                    foreach($dose_details as $uom=>$values)
                    {
                        $this->addFormattedVistaNoteRow($noteTextArray
                                , 'Exam Note '
                                    . $category_term
                                    . ' Radiation Exposure UoM',$uom);
                        foreach($values as $dose_record)
                        {
                            $dose = $dose_record['dose'];
                            $qcd = $dose_record['dose_type_cd'];
                            $qterm = RadiationDoseHelper::getDoseTypeTermMap($qcd);
                            $this->addFormattedVistaNoteRow($noteTextArray,'Exam Note '.$category_term.' Radiation Exposure Data',$dose.' '.$uom.$qterm);
                        }
                    }
                }
            }
            
        }
        $prevnotes = $this->getExamNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        foreach($prevnotes as $prevnoteinfo)
        {
            if(count($noteTextArray)>0)
            {
                $noteTextArray[] = '';
            }
            $author_uid = $prevnoteinfo['author_uid'];
            $created_dt = $prevnoteinfo['created_dt'];
            $notes_tx = $prevnoteinfo['notes_tx'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            if($userinfo == NULL)
            {
                $fullname = 'RAPTOR user '.$author_uid;
            } else {
                $fullname = $userinfo->getFullName();
            }
            if(trim($notes_tx) == '')
            {
                $notes_tx = 'BLANK';
            }
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam General Note Date',$created_dt);
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam General Note Author',$fullname);
            $this->addFormattedVistaNoteRow($noteTextArray,'Exam General Note Text',$notes_tx);
        }
        if(count($noteTextArray)>0)
        {
            $noteTextArray[] = '';
        }
        
        //Get raptor_ticket_interpret_notes
        $getvalues = array();
        $prevnotes = $this->getInterpretationNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt);
        foreach($prevnotes as $prevnoteinfo)
        {
            $author_uid = $prevnoteinfo['author_uid'];
            $created_dt = $prevnoteinfo['created_dt'];
            $notes_tx = $prevnoteinfo['notes_tx'];
            $userinfo = $oAllUsers->getByUID($author_uid);
            if($userinfo == NULL)
            {
                $fullname = 'RAPTOR user '.$author_uid;
            } else {
                $fullname = $userinfo->getFullName();
            }
            if(trim($notes_tx) == '')
            {
                $notes_tx = 'BLANK';
            }
            $this->addFormattedVistaNoteRow($noteTextArray,'Interpretation General Note Date',$created_dt);
            $this->addFormattedVistaNoteRow($noteTextArray,'Interpretation General Note Author',$fullname);
            $this->addFormattedVistaNoteRow($noteTextArray,'Interpretation General Note Text',$notes_tx);
        }
    }

    function getInterpretationNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt)
    {
        return $this->getNotesFromTable('raptor_ticket_interpret_notes', $nSiteID, $nIEN, $getvalues, $prev_commit_dt);
    }

    function getSchedulerNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt)
    {
        return $this->getNotesFromTable('raptor_schedule_track', $nSiteID, $nIEN, $getvalues, $prev_commit_dt);
    }

    function getProtocolNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt)
    {
        return $this->getNotesFromTable('raptor_ticket_protocol_notes', $nSiteID, $nIEN, $getvalues, $prev_commit_dt);
    }
    
    function getExamNotes($nSiteID,$nIEN,$getvalues,$prev_commit_dt)
    {
        return $this->getNotesFromTable('raptor_ticket_exam_notes', $nSiteID, $nIEN, $getvalues, $prev_commit_dt);
    }
    
    private function getNotesFromTable($tablename, $nSiteID,$nIEN,$getvalues,$prev_commit_dt)
    {
        $details_array = array();
        $query=db_select($tablename, 't')
                ->fields('t')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
                if($prev_commit_dt != NULL)
                {
                    $query->condition('created_dt', $prev_commit_dt,'>');
                }
                $query->orderBy('created_dt', 'DESC');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $details_array[] = $record;
        }
        return $details_array;
    }
    
    function addFormattedVistaNoteRow(&$noterows,$label_for_row,$value_container,$key=NULL)
    {
        if(!is_array($value_container))
        {
            if($value_container > '')
            {
                $noterows[] = '[' . $label_for_row .'] ::= ' . $value_container;
            }
        } else {
            if(isset($value_container[$key]) && $value_container[$key] > '')
            {
                $noterows[] = '[' . $label_for_row .'] ::= ' . $value_container[$key];
            }
        }
    }

    /**
     * Only call this function when ticket state is approved or later.
     */
    function hasUncommittedData($nSiteID,$nIEN,$newerthan_dt=NULL)
    {
        $has_uncommited = FALSE;
        $mostrecent_dt = $this->getDateMostRecentVistaCommitDate($nSiteID,$nIEN);
        if($mostrecent_dt == NULL)
        {
            //We have never committed data.
            return TRUE;
        }
        if($mostrecent_dt >= $newerthan_dt)
        {
            //Our record keeping says we havecommitted all worth committing.
            return FALSE;
        }
        
        //Lets see if there are records have been created and need to be committed.
        $tablenames = array(
            'raptor_ticket_protocol_notes','raptor_ticket_exam_notes', 'raptor_ticket_interpret_notes'
        );
        foreach($tablenames as $tablename)
        {
            $query = db_select($tablename, 'n');
            $query->fields('n')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=');
            if($newerthan_dt != NULL)
            {
                $query->condition('created_dt', $newerthan_dt,'>');
            }
            $result = $query->execute();
            if($result->rowCount() > 0)
            {
                return TRUE; 
            }
        }
        
        //If we are here, then there is nothing uncommitted.
        return FALSE;
    }
    
    function getDateMostRecentVistaCommitDate($nSiteID,$nIEN)
    {
        $query=db_select('raptor_ticket_commit_tracking', 't')
                ->fields('t')
                ->condition('siteid', $nSiteID,'=')
                ->condition('IEN', $nIEN,'=')
                ->orderBy('commit_dt', 'DESC');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            error_log('Prev commit to Vista was '.$record['commit_dt']);
            return $record['commit_dt'];
        }
        error_log('No prev commit to Vista!');
        return NULL;
    }
    
    function getAllOptions()
    {
        //LEAVE EMPTY
    }

    private function getProtocolLibMap()
    {
        $aMap = array();
        $query = db_select('raptor_protocol_lib','p')
                ->fields('p');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $key = $record['protocol_shortname'];
            $aMap[$key] = $record;
        }
        return $aMap;
    }
    
    
    /**
     * Return all properties of a protocol from the library.
     * @param type $protocol_shortname
     * @return array with all the properties of the selected protocol
     */
    function getPropertiesFromProtocolName($protocol_shortname)
    {
        if($protocol_shortname > '')
        {
            //Look up the protocol in the library.
            $result = db_select('raptor_protocol_lib','p')
                    ->fields('p')
                    ->condition('protocol_shortname',$protocol_shortname,'=')
                    ->execute();
            if($record = $result->fetchAssoc())
            {
                return $record; //['modality_abbr'];
            }
            drupal_set_message('Expected to find a protocol library entry for "'.$protocol_shortname.'" this name but did not!','warning');
        }
        //Found nothing.
        return array(
            'protocol_shortname' => NULL,
            'name' => NULL,
            'modality_abbr' => NULL,
            'image_guided_yn' => NULL,
        );    
    }
    
    /**
     * Return a map of all acknowledged contraindications for a ticket.
     * @param type $nSiteID
     * @param type $nIEN
     */
    public function getAllAcknowledgedContraindicationsMap($nSiteID, $nIEN)
    {
        $aMap = array();
        $query = db_select('raptor_ticket_contraindication', 'n');
        $query->join('raptor_user_profile', 'u', 'n.author_uid = u.uid');
        $query->fields('n', array('created_dt', 'rule_nm','acknowledged_yn'));
        $query->fields('u', array('username','role_nm','usernametitle','firstname','lastname','suffix','prefemail','prefphone'))
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->condition('acknowledged_yn', 1, '=')
            ->orderBy('created_dt', 'ASC');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $sRuleName = $record['rule_nm'];
            $fullname = trim($record['usernametitle'] . ' ' . $record['firstname'] . ' ' . $record['lastname'] . ' ' . $record['suffix']);
            $aMap[$sRuleName] = array(
                'acknowledged_dt'=>$record['created_dt'],
                'fullname'=>$fullname,
            );
        }
        return $aMap;
    }
    
    private function arrayKeysHaveValues($array, $keys)
    {
        foreach($keys as $key)
        {
            if(isset($array[$key]) && $array[$key] > '')
            {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    private function arrayKeysAllHaveSpecificValues($array, $pairs)
    {
        foreach($pairs as $key=>$value)
        {
            if(!isset($array[$key]) || $array[$key] !== $value)
            {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    private function arrayValueExistsAndNotBlank($array, $key)
    {
        return (isset($array[$key]) && $array[$key] > '');
    }
    
    /**
     * Get the markup for contraindications
     * @return map of results
     */
    function getContraindicationFormMarkup($nSiteID, $nIEN, $myvalues, $protocolValues, $oPSD, $aMapCI_AlreadyAcknowledged)
    {
        $aResultMap = array();
        
        if($this->m_oCIE == NULL)
        {
            $this->m_oCIE = $this->getCIE();
        }
        $oCIE = $this->m_oCIE;
        
        //Flag as possible duplicate order if there is more than one active order for the same modality as this one.
        $modality = $protocolValues['modality_abbr'];   //Might be unknown or blank.
        $pendingMap = $oPSD->getPendingOrdersMap();
        if(count($pendingMap) > 1)
        {
            if($modality == '' || $modality == 'Unknown')
            {
                //This will happen if no modality was specified in the protocol template.
                //error_log('No modality was specified in the protocol template for '.print_r($protocolValues,TRUE));
                $possibleDups = TRUE;   //Assume we could have duplicates. 
            } else {
                $modalitycount = 0;
                foreach($pendingMap as $key=>$values)
                {
                    if($values[1] == $modality || $values[1] == 'Unknown')
                    {
                        $modalitycount++;
                    }
                }
                $possibleDups = $modalitycount > 1;
            }
        } else {
            $possibleDups = FALSE;
        }
        $aCandidateData = array();  
        $aCandidateData['IS_DIAGNOSTIC_EXAM'] = NULL;   //TODO -- get from $protocolValues
        $aCandidateData['IS_POSSIBLE_DUP_PROC']         = $possibleDups;
        $aCandidateData['IS_IMG_GUIDED_EXAM']           = $protocolValues['image_guided_yn'];
        $aCandidateData['PROC_NM']                      = $myvalues['procName'];
        $aCandidateData['MODALITY']                     = $protocolValues['modality_abbr'];
        $aCandidateData['GIVE_HYDRATION_ORAL']          = $this->arrayKeysHaveValues($myvalues,array('hydration_oral_customtx','hydration_oral_id'));
        $aCandidateData['GIVE_HYDRATION_IV']            = $this->arrayKeysHaveValues($myvalues,array('hydration_iv_customtx','hydration_iv_id'));
        $aCandidateData['GIVE_CONTRAST_ENTERIC']        = $this->arrayKeysHaveValues($myvalues,array('contrast_enteric_customtx','contrast_enteric_id'));
        $aCandidateData['GIVE_CONTRAST_IV']             = $this->arrayKeysHaveValues($myvalues,array('contrast_iv_customtx','contrast_iv_id'));
        $aCandidateData['GIVE_SEDATION_ORAL']           = $this->arrayKeysHaveValues($myvalues,array('sedation_oral_customtx','sedation_oral_id'));
        $aCandidateData['GIVE_SEDATION_IV']             = $this->arrayKeysHaveValues($myvalues,array('sedation_iv_customtx','sedation_iv_id'));
        $aCandidateData['GIVE_RADIOISOTOPE_ENTERIC']    = $this->arrayKeysHaveValues($myvalues,array('radioisotope_enteric_customtx','radioisotope_enteric_id'));
        $aCandidateData['GIVE_RADIOISOTOPE_IV']         = $this->arrayKeysHaveValues($myvalues,array('radioisotope_iv_customtx','radioisotope_iv_id'));
        $aCandidateData['IS_CLAUSTROPHOBIC']            = $this->arrayKeysAllHaveSpecificValues($myvalues,array('claustrophobic_cd'=>'yes'));
        $aCandidateData['HAS_ALLERGY']                  = $this->arrayKeysAllHaveSpecificValues($myvalues,array('allergy_cd'=>'yes'));

        //Get allergies to pass in.
        $aAllergies = array();
        $aDetails = $oPSD->getAllergiesDetail();
        foreach($aDetails as $aItem)
        {
            $aAllergies[] = $aItem['Item'];
            //drupal_set_message('Look>>>'.print_r($aItem,TRUE));
        }
        $aCandidateData['CURRENT_ALLERGIES'] = $aAllergies;

        $aCandidateData['KWL_RARE_CONTRAST'] = $oPSD->getRareContrastKeywords();
        $aCandidateData['KWL_RARE_RADIOISOTOPE'] = $oPSD->getRareRadioisotopeKeywords();
        $aCandidateData['KWL_BLOOD_THINNER'] = $oPSD->getBloodThinnerKeywords();
        $aCandidateData['KWL_CONTRAST_ALLERGY_INDICATOR'] = $oPSD->getAllergyContrastKeywords();
        
        //Get meds to pass in.
        $aMeds = array();
        $aMedDetail = $oPSD->getMedicationsDetail();
        foreach($aMedDetail as $aMedItem)
        {
            $aMeds[] = $aMedItem['Med'];
        }
        $aCandidateData['CURRENT_MEDS'] = $aMeds;
        $aCandidateData['CURRENT_CONTRASTS'] = array();
        if($this->arrayValueExistsAndNotBlank($myvalues,'contrast_enteric_customtx'))
        {
            $name = $myvalues['contrast_enteric_customtx'];
            $aCandidateData['CURRENT_CONTRASTS'][] = $name;
        } else {
            if($this->arrayValueExistsAndNotBlank($myvalues,'contrast_enteric_id'))
            {
                $name = $myvalues['contrast_enteric_id'];
                $aCandidateData['CURRENT_CONTRASTS'][] = $name;
            }
        }
        if($this->arrayValueExistsAndNotBlank($myvalues,'contrast_iv_customtx'))
        {
            $name = $myvalues['contrast_iv_customtx'];
            $aCandidateData['CURRENT_CONTRASTS'][] = $name;
        } else {
            if($this->arrayValueExistsAndNotBlank($myvalues,'contrast_iv_id'))
            {
                $name = $myvalues['contrast_iv_id'];
                $aCandidateData['CURRENT_CONTRASTS'][] = $name;
            }
        }
        $aCandidateData['CURRENT_RADIOISOTOPES'] = array(); 
        if($this->arrayValueExistsAndNotBlank($myvalues,'radioisotope_enteric_customtx'))
        {
            $name = $myvalues['radioisotope_enteric_customtx'];
            $aCandidateData['CURRENT_RADIOISOTOPES'][] = $name;
        } else {
            if($this->arrayValueExistsAndNotBlank($myvalues,'radioisotope_enteric_id'))
            {
                $name = $myvalues['radioisotope_enteric_id'];
                $aCandidateData['CURRENT_RADIOISOTOPES'][] = $name;
            }
        }
        if($this->arrayValueExistsAndNotBlank($myvalues,'radioisotope_iv_customtx'))
        {
            $name = $myvalues['radioisotope_iv_customtx'];
            $aCandidateData['CURRENT_RADIOISOTOPES'][] = $name;
        } else {
            if($this->arrayValueExistsAndNotBlank($myvalues,'radioisotope_iv_id'))
            {
                $name = $myvalues['radioisotope_iv_id'];
                $aCandidateData['CURRENT_RADIOISOTOPES'][] = $name;
            }
        }
        //Now invoke the contraindication engine.
        try
        {
            $oCI = $oCIE->getResults($aCandidateData);
            $aContraindications = $oCI->getAll();
        } catch (\Exception $ex) {
            $aContraindications = array();
            drupal_set_message('Failed to run the contraindications engine because ' 
                    . $ex->getMessage(),'error');
        }

        //Get the contraindications markup and details for markup.
        $sStaticWarningMsgsHTML = NULL;
        $aAllCIWarnings=array();    //Content to display in static warning area.
        $aCI_AlreadyAcknowledgedMarkup = array();
        $aCI_AcknowledgeMarkup = array();
        $aCI_AcknowledgeMarkup['#tree'] = TRUE;
        $nCI_Acknowledge = 0;
        $nCI_AlreadyAcknowledged = 0;
        $aCI_NoAcknowledgeMarkup = array();
        $aCI_NoAcknowledgeMarkup['#tree'] = TRUE;
        $nCI_NoAcknowledge = 0;
        $nRuleExplainID = 1231000;  //Start with a large number unlikely to be used elsewhere on the form.
        $nItem=0;
        $aCI_AlreadyAcknowledgedMarkup[] = array('#markup' => '<ul>');
        foreach($aContraindications as $oCI)
        {
            $nItem++;
            $sID = $oCI->getUniqueID();
            $sSummaryMsg = $oCI->getSummaryMessage();
            $aCIS = $oCI->getResultSource();
            $bReqAck = $oCI->isConfirmationRequired();
            if(!isset($aAllCIWarnings[$sSummaryMsg]))
            {
                //So we don't display more than once.
                $aAllCIWarnings[$sSummaryMsg] = $sSummaryMsg;   
                $sStaticWarningMsgsHTML .= "\n<li id='static_{$sID}'>" . $sSummaryMsg;
            }
            //There can be multiple CI associated with the same summary text.
            $aCI_2Ack = array();
            $aCI_2NoAck = array();
            foreach($aCIS as $oCIS)
            {
                $sRuleName = $oCIS->getRuleName();  //Each rule name appears only once.
                $nRuleExplainID++;  //Had to go numeric because trouble with quotes in the generated html.
                if($oCI->isConfirmationRequired())
                {
                    //This one requires confirmation
                    if(isset($aMapCI_AlreadyAcknowledged[$sRuleName]))
                    {
                        //This one was already acknowledged.
                        $nCI_AlreadyAcknowledged++;
                        $aDetails = $aMapCI_AlreadyAcknowledged[$sRuleName];
                        $blurb = 'Acknowledged on '.$aDetails['acknowledged_dt']
                                .' by '.$aDetails['fullname'];
                        $aCI_AlreadyAcknowledgedMarkup[][] 
                                = array('#markup' 
                                    => "\n<li>"
                                    . "\n<a href='javascript:showContraIndicationsExplanationPopup($nRuleExplainID);'>" 
                                    . $oCIS->getMessage() 
                                    . "</a>"
                                    . "<data-explanation hidden id='$nRuleExplainID'>".$oCIS->getExplanation()
                                ."</data-explanation>"
                                . " ($blurb)"
                                . "</li>");
                    } else {
                        //Not yet acknowledged.
                        $nCI_Acknowledge++;
                        $aCI_2Ack['ci_rules'][$sRuleName] = array('#type' => 'hidden'
                            , '#value' => $sRuleName);   //So we know what to record in database.
                        $aCI_2Ack['sources'][] = array('#markup' 
                            => "\n<li>"
                                . "\n<a href='javascript:showContraIndicationsExplanationPopup($nRuleExplainID);'>" 
                                .$oCIS->getMessage() 
                                ."</a><data-explanation hidden id='$nRuleExplainID'>" 
                                .$oCIS->getExplanation() 
                                ."</data-explanation></li>");
                    }
                } else {
                    //This one does not require confirmation.
                    $nCI_NoAcknowledge++;
                    $aCI_2NoAck['ci_rules'][$sRuleName] = array('#type' => 'hidden'
                        , '#value' => $sRuleName);   //So we know what to record in database.
                    $aCI_2NoAck['sources'][] = array('#markup' 
                        => "\n<li>\n<a href='javascript:showContraIndicationsExplanationPopup($nRuleExplainID);'>" 
                            .$oCIS->getMessage() 
                            ."</a><data-explanation hidden id='$nRuleExplainID'>" 
                            .$oCIS->getExplanation() 
                            ."</data-explanation></li>");
                }
            }
            if(count($aCI_2NoAck) > 0)
            {
                if(!isset($aCI_NoAcknowledgeMarkup[$sSummaryMsg]))
                {
                    //There is only one checkbox per summary text.
                    $aCI_NoAcknowledgeMarkup[$sSummaryMsg] = array();
                    $aCI_NoAcknowledgeMarkup[$sSummaryMsg]['chk_ack'] 
                            = array('#markup' 
                                => '<p class="raptor-ci-noack-notice">' 
                                . t("Notification: $sSummaryMsg") 
                                . '</p>',
                        );
                }
                foreach($aCI_2NoAck['ci_rules'] as $k=>$v)
                {
                    $aCI_NoAcknowledgeMarkup[$sSummaryMsg]['ci_rules'][$k] = $v;
                }
                $aCI_NoAcknowledgeMarkup[$sSummaryMsg]['sources'][] 
                        = array('#markup' => '<ul>');
                foreach($aCI_2NoAck['sources'] as $v)
                {
                    $aCI_NoAcknowledgeMarkup[$sSummaryMsg]['sources'][] = $v;
                }
                $aCI_NoAcknowledgeMarkup[$sSummaryMsg]['sources'][] = array('#markup' => '</ul>');
            }
            if(count($aCI_2Ack) > 0)
            {
                if(!isset($aCI_AcknowledgeMarkup[$sSummaryMsg]))
                {
                    //There is only one checkbox per summary text.
                    $aCI_AcknowledgeMarkup[$sSummaryMsg] = array();
                    $aCI_AcknowledgeMarkup[$sSummaryMsg]['chk_ack'] = array('#type' => 'checkbox',    
                        '#title' => t("Acknowledgement of $sSummaryMsg"),
                        );
                }
                foreach($aCI_2Ack['ci_rules'] as $k=>$v)
                {
                    $aCI_AcknowledgeMarkup[$sSummaryMsg]['ci_rules'][$k] = $v;
                }
                $aCI_AcknowledgeMarkup[$sSummaryMsg]['sources'][] = array('#markup' => '<ul>');
                foreach($aCI_2Ack['sources'] as $v)
                {
                    $aCI_AcknowledgeMarkup[$sSummaryMsg]['sources'][] = $v;
                }
                $aCI_AcknowledgeMarkup[$sSummaryMsg]['sources'][] = array('#markup' => '</ul>');
            }
        }
        $aCI_AlreadyAcknowledgedMarkup[] = array('#markup' => '</ul>');
        if($sStaticWarningMsgsHTML !== NULL)
        {
            $sStaticWarningMsgsHTML = "\n<ul>" . $sStaticWarningMsgsHTML . "\n</ul>";
        }

        //Populate the result map.
        $aResultMap['StaticWarningMsgsHTML'] = $sStaticWarningMsgsHTML;
        $aResultMap['CI_AcknowledgeMarkup'] = $aCI_AcknowledgeMarkup;
        $aResultMap['CI_Acknowledge'] = $nCI_Acknowledge;
        $aResultMap['CI_NoAcknowledgeMarkup'] = $aCI_NoAcknowledgeMarkup;
        $aResultMap['CI_NoAcknowledge'] = $nCI_NoAcknowledge;
        $aResultMap['CI_AlreadyAcknowledgedMarkup'] = $aCI_AlreadyAcknowledgedMarkup;
        $aResultMap['CI_AlreadyAcknowledged'] = $nCI_AlreadyAcknowledged;
        $aResultMap['AllCIWarnings'] = $aAllCIWarnings;
        
        $aResultMap['DEBUGINFO'] = $aCandidateData; //TODO remove this
        
        return $aResultMap;
    }
    
    /**
     * Setup all the global variables that are part of the form context.
     */
    function setupFormContext() 
    {
        global $raptor_protocoldashboard;
        global $raptor_protocol_content;
        global $raptor_context;

        $userinfo = $this->m_oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $raptor_context = $this->m_oContext;

        if($userinfo->getUserID() < 0 || $userinfo->getUserID() == NULL)
        {
            //This is not a valid user session, this can happen on kickout and things like that.
            $errormsg = 'Did not have a valid user id (' . $userinfo->getUserID() . ')';
            error_log($errormsg);
            die($errormsg);
        }

        module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
        module_load_include('php', 'raptor_datalayer', 'core/data_protocolsupport');
        module_load_include('php', 'raptor_datalayer', 'core/data_listoptions');
        module_load_include('php', 'raptor_graph', 'core/GraphData');

        if(!$this->m_oContext->hasSelectedTrackingID())
        {
            //This can happen when we are done with a personal batch or somethning like that.
            die('Did NOT find a selected Tracking ID.  Go back to the worklist.');
        }

        //Set all the Protocol page values
        $oDD = new \raptor\DashboardData($this->m_oContext);
        $raptor_protocoldashboard = $oDD->getDashboardDetails();
        $oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
        $oGD = new \raptor\GraphData($this->m_oContext);
        $oLO = new \raptor\ListOptions();

        $raptor_protocol_content = array();
        //$formContent = raptor_glue_protocolinfo_form_inputarea();
        $raptor_protocol_content['Input']['Protocol'] = '<h1>THIS GLOBAL ENTRY HAS BEEN DEPRECATED!</h1>'; //drupal_render($formContent);
        $raptor_protocol_content['Reference']['OrderOverview'] = $oPSD->getOrderOverview();
        $raptor_protocol_content['Reference']['VitalsSummary'] = $oPSD->getVitalsSummary();
        $raptor_protocol_content['Reference']['MedicationsDetail'] = $oPSD->getMedicationsDetail();
        $raptor_protocol_content['Reference']['VitalsDetail'] = $oPSD->getVitalsDetail();
        $raptor_protocol_content['Reference']['AllergiesDetail'] = $oPSD->getAllergiesDetail();
        $raptor_protocol_content['Reference']['ProcedureLabsDetail'] = $oPSD->getProcedureLabsDetail();
        $raptor_protocol_content['Reference']['DiagnosticLabsDetail'] = $oPSD->getDiagnosticLabsDetail();
        //not used $raptor_protocol_content['Reference']['DoseHxDetail'] = $oPSD->getDoseHxDetail();
        $raptor_protocol_content['Reference']['PathologyReportsDetail'] = $oPSD->getPathologyReportsDetail();
        $raptor_protocol_content['Reference']['SurgeryReportsDetail'] = $oPSD->getSurgeryReportsDetail();
        $raptor_protocol_content['Reference']['ProblemsListDetail'] = $oPSD->getProblemsListDetail();
        //deprecated 20150524 $raptor_protocol_content['Reference']['NotesDetail'] = $oPSD->getNotesDetail();
        $raptor_protocol_content['Reference']['RadiologyReportsDetail'] = $oPSD->getRadiologyReportsDetail();
        $raptor_protocol_content['Reference']['Graph']['Thumbnail'] = $oGD->getThumbnailGraphValues();
        $raptor_protocol_content['Reference']['Graph']['Labs'] = $oGD->getLabsGraphValues();
        $raptor_protocol_content['Reference']['Graph']['Vitals'] = $oGD->getVitalsGraphValues();
        $raptor_protocol_content['AtRiskMeds'] = $oLO->getAtRiskMedsKeywords();
    }
    
    
    /**
     * Get all the protocol form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues_override=NULL)
    {
        //drupal_set_message('>>>> LOOK GETTING FORM AT '.microtime().'<br>values='.print_r($myvalues,TRUE));
        if(isset($form_state['values']) && is_array($form_state['values']))
        {
            $myvalues = $form_state['values'];
        } else {
            $myvalues = array();
        }        
        if(is_array($myvalues_override))
        {
            $myvalues = array_merge($myvalues, $myvalues_override);
        }
        if(isset($form_state['ajax_values']) && is_array($form_state['ajax_values']))
        {
            $myvalues = array_merge($myvalues, $form_state['ajax_values']);
            $form_state['ajax_values'] = NULL;  //Now clear these.
        }
        
        if(!isset($form_state['setup_formcontext']) || $form_state['setup_formcontext']==TRUE)
        {
            $this->setupFormContext();
        } else {
            error_log('Skipping form context setup!');
        }
        
        $userinfo = $this->m_oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $sTrackingID = $this->m_oTT->getTrackingID($nSiteID, $nIEN);
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oUtility->getCurrentWorkflowState($nSiteID, $nIEN);

        //$mdwsDao = $this->m_oContext->getMdwsClient();
        //$orderDetails = MdwsUtils::getOrderDetails($mdwsDao, $nIEN);
        //$orderFileStatus = $orderDetails['orderFileStatus'];
        
        if(!$disabled)
        {
            //Handle the locking
            $locrec = $this->m_oTT->getTicketLockDetails($sTrackingID);
            if($locrec != NULL)
            {
                if($locrec['locked_by_uid'] != $nUID)
                {
                    //See if the lock is stale.
                    $this->m_oTT->deleteAllStaleTicketLocks(VISTA_SITE,'Retry from getForm for '.$nUID);
                    $locrec = $this->m_oTT->getTicketLockDetails($sTrackingID);
                    if($locrec != NULL)
                    {
                        //Looks like it was not stale.
                        if($locrec['locked_by_uid'] != $nUID)
                        {
                            module_load_include('php', 'raptor_datalayer', 'core/data_user');
                            $otheruser = new \raptor\UserInfo($locrec['locked_by_uid']);
                            drupal_set_message('Ticket already edit locked by '
                                    .$otheruser->getFullName()
                                    .' since '.$locrec['lock_started_dt']
                                    , 'warning');
                            $disabled = TRUE;   //Do not allow edits on the page.
                        }
                    }
                }
            }
        }
        if(!$disabled)
        {
            //Since still not disabled, go ahead and mark as locked.
            $this->m_oTT->markTicketEditLocked($sTrackingID, $nUID);
        }
        
        $oPSD = new \raptor\ProtocolSupportingData($this->m_oContext);
  
        $protocolValues = $this->getPropertiesFromProtocolName($myvalues['protocol1_nm']);
        
        $modality_abbr = $protocolValues['modality_abbr'];
        $protocol_shortname = $protocolValues['protocol_shortname'];

        //drupal_set_message('LOOK pn and m and p>>> '.$myvalues['procName']." [$modality_abbr][$protocol_shortname]");
        $aMapCI_AlreadyAcknowledged = $this->getAllAcknowledgedContraindicationsMap($nSiteID, $nIEN);
        $aCIResultMap = $this->getContraindicationFormMarkup($nSiteID
                , $nIEN
                , $myvalues
                , $protocolValues
                , $oPSD
                , $aMapCI_AlreadyAcknowledged);
        $sStaticWarningMsgsHTML = $aCIResultMap['StaticWarningMsgsHTML'];
        $aCI_AcknowledgeMarkup = $aCIResultMap['CI_AcknowledgeMarkup'];
        $nCI_Acknowledge = $aCIResultMap['CI_Acknowledge'];
        $aCI_NoAcknowledgeMarkup = $aCIResultMap['CI_NoAcknowledgeMarkup'];
        $nCI_NoAcknowledge = $aCIResultMap['CI_NoAcknowledge'];
        $aCI_AlreadyAcknowledgedMarkup = $aCIResultMap['CI_AlreadyAcknowledgedMarkup'];
        $nCI_AlreadyAcknowledged = $aCIResultMap['CI_AlreadyAcknowledged'];
        $aAllCIWarnings = $aCIResultMap['AllCIWarnings'];
        if($sCWFS == 'EC' || $sCWFS == 'QA' || $sCWFS == 'IA')
        {
            $nCI_Acknowledge=0;
            $aCI_AcknowledgeMarkup = array();   //No new acknowledgements expected.
        }

        $protocolInputDisable = $disabled 
                || (
                ($sCWFS !== 'AC') 
                    && ($sCWFS !== 'RV') && ($sCWFS !== 'CO')
                );  //20140821
        
        $form["data_entry_area1"]    = array(
            '#prefix' => "\n<section id='input-right-side' class='right-side'>\n",
            '#suffix' => "\n</section>\n",
        );

        //Set the hidden fields.
        $form['hidden_constant_things']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hidden_constant_things']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        $form['hidden_volatile_things']['modality_abbr'] = array('#type' => 'hidden', '#default_value' => $modality_abbr);
        $form['hidden_volatile_things']['selected_vid'] = array('#type' => 'hidden'); //DO NOT SET A DEFAULT VALUE!!! Set with javascript later!!!
        $form['hidden_volatile_things']['commit_esig'] = array('#type' => 'hidden'); //DO NOT SET A DEFAULT VALUE!!! Set with javascript later!!!
        $form['hidden_volatile_things']['collaboration_uid'] = array('#type' => 'hidden'); //DO NOT SET A DEFAULT VALUE!!! Set with javascript later!!!
        $form['hidden_volatile_things']['collaboration_note_tx'] = array('#type' => 'hidden'); //DO NOT SET A DEFAULT VALUE!!! Set with javascript later!!!
        
        //PROTOCOL MODE
        $cluesmap = $this->m_oLI->getProtocolMatchCluesMap($myvalues['procName']);
        $form['data_entry_area1'][]  = $this->m_oUtility
                ->getOverallProtocolDataEntryArea1($sCWFS, $form_state
                        , $protocolInputDisable
                        , $myvalues
                        , NULL
                        , $cluesmap);
        $form['data_entry_area2']    = array(
            '#prefix' => "\n<section id='input-bottom-protocol' class='bottom-protocol'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form['data_entry_area2']['#after_build'][] = 'raptor_glue_protcolinfo_after_build';
        $form['data_entry_area2'][]  
                = $this->m_oUtility->getOverallProtocolDataEntryArea2($sCWFS
                        , $form_state
                        , $protocolInputDisable, $myvalues);
        $form['data_entry_area2'][]  
                = $this->m_oUtility->getOverallExamDataEntryArea($sCWFS
                        , $protocolValues
                        , $form_state, $disabled, $myvalues);

        if(count($aAllCIWarnings) > 0) //$sStaticWarningMsgsHTML !== NULL)
        {
            if($sStaticWarningMsgsHTML !== NULL)
            {
                //Populate the static warning area.
                $form["static_warnings_area"]   = array(
                    '#prefix' => "\n<section id='static-warnings' class='read-only'>\n",
                    '#suffix' => "\n</section>\n",
                );
                $form["static_warnings_area"][] = array('#markup' => $sStaticWarningMsgsHTML);
            }
            
            if($nCI_AlreadyAcknowledged > 0)
            {
                //Populate the acknowledgement already responsed area.
                $form['data_entry_area2']['contraindication_aa']  = array(
                        '#type'     => 'fieldset',
                        '#title'    => t('Contraindications Already Acknowledged'),
                        '#disabled' => $disabled,
                        '#prefix' => "\n".'<div id="ci-already-acknowledged">'."\n",
                        '#suffix' => "\n".'</div>'."\n",
                    );
                $form['data_entry_area2']['contraindication_aa']['#attributes'] = array();
                $form['data_entry_area2']['contraindication_aa']['#attributes']['class'] = array('contraindication');
                $form['data_entry_area2']['contraindication_aa']['ci_alreadyacknowledged'] = $aCI_AlreadyAcknowledgedMarkup;
            } else {
                $form['data_entry_area2']['contraindication_aa']  = array(
                        '#markup' => "\n".'<div id="ci-already-acknowledged"><!-- None --></div>',
                    );
            }

            if($nCI_NoAcknowledge > 0)
            {
                //Populate the acknowledgement response area.
                $form['data_entry_area2']['contraindication_nora']  = array(
                        '#type'     => 'fieldset',
                        '#title'    => FormHelper::getTitleAsUnrequiredField('Contraindications Not Requiring Acknowledgement'),
                        '#disabled' => $disabled,
                        '#prefix' => "\n".'<div id="ci-nora">'."\n",
                        '#suffix' => "\n".'</div>'."\n",
                    );
                $form['data_entry_area2']['contraindication_nora']['#attributes'] = array();
                $form['data_entry_area2']['contraindication_nora']['#attributes']['class'] = array('contraindication');
                $form['data_entry_area2']['contraindication_nora']['ci_justshow'] = $aCI_NoAcknowledgeMarkup;
            } else {
                $form['data_entry_area2']['contraindication_ra']  = array(
                        '#markup' => "\n".'<div id="ci-nora"><!-- None --></div>',
                    );
            }
            
            if($nCI_Acknowledge > 0)
            {
                //Populate the acknowledgement response area.
                $form['data_entry_area2']['contraindication_ra']  = array(
                        '#type'     => 'fieldset',
                        '#title'    => FormHelper::getTitleAsRequiredField('Contraindications Requiring Acknowledgement'),
                        '#disabled' => $disabled,
                        '#prefix' => "\n".'<div id="ci-not-acknowledged">'."\n",
                        '#suffix' => "\n".'</div>'."\n",
                    );
                $form['data_entry_area2']['contraindication_ra']['#attributes'] = array();
                $form['data_entry_area2']['contraindication_ra']['#attributes']['class'] = array('contraindication');
                $form['data_entry_area2']['contraindication_ra']['ci_responses'] = $aCI_AcknowledgeMarkup;
            } else {
                $form['data_entry_area2']['contraindication_ra']  = array(
                        '#markup' => "\n".'<div id="ci-not-acknowledged"><!-- None --></div>',
                    );
            }
        }

        $form['data_entry_area2'][]  = $this->m_oUtility->getOverallInterpretationDataEntryArea($sCWFS, $form_state, $disabled, $myvalues);
        $form['data_entry_area2'][]  = $this->m_oUtility->getOverallQADataEntryArea($sCWFS, $form_state, $disabled, $myvalues);
        
        
        //Now populate the button area.
        $form["page_button_area1"]   = array(
            '#prefix' => "\n<section class='page-action'>\n",
            '#suffix' => "\n</section>\n",
        );
        $newerthan_dt = $this->getDateMostRecentVistaCommitDate($nSiteID, $nIEN);
        $has_uncommitted_data = $this->hasUncommittedData($nSiteID, $nIEN, $newerthan_dt);
        $form['page_button_area1'][] = $this->m_oUtility->getPageActionButtonsArea($form_state, $disabled, $myvalues, $has_uncommitted_data, $newerthan_dt);
        
        return $form;
    }
}



