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

module_load_include('php', 'raptor_glue', 'utility/TermMapping');
module_load_include('php', 'raptor_glue', 'utility/RadiationDoseHelper');
module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'config/ListUtils');
module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
module_load_include('php', 'raptor_datalayer', 'core/data_dashboard');
module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
module_load_include('php', 'raptor_datalayer', 'core/data_protocolsupport');
module_load_include('php', 'raptor_formulas', 'core/LanguageInference');
module_load_include('php', 'raptor_formulas', 'core/MatchOrderToProtocol');
module_load_include('php', 'raptor_workflow', 'core/AllowedActions');

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol_ajax.inc');
require_once 'FormHelper.php';
require_once 'ProtocolLibPageHelper.php';

/**
 * Utilities for the ProtocolInfo form content.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ProtocolInfoUtility
{
    private $m_oContext = NULL;
    private $m_oTT = NULL;
    private $m_oLI = NULL;
    private $m_oMOP = NULL;
    
    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
        $this->m_oLI = new \raptor_formulas\LanguageInference();
        $this->m_oMOP = new \raptor_formulas\MatchOrderToProtocol();
    }
    
    /**
     * Return markup containing all the notes associated 
     * with the provided search criteria
     */
    public function getPreviousNotesMarkup($tablename,$nSiteID,$nIEN,$extraClassname='')
    {
        
        $prev_notes_tx = NULL;

        //Get app existing notes
        $query = db_select($tablename, 'n');
        $query->join('raptor_user_profile', 'u', 'n.author_uid = u.uid');
        $query->fields('n', array('created_dt', 'notes_tx'));
        $query->fields('u', array('username','role_nm','usernametitle','firstname','lastname','suffix','prefemail','prefphone'))
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->orderBy('created_dt', 'ASC');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            //Create the markup.
            $fullname = trim($record['usernametitle'] . ' ' . $record['firstname'] . ' ' . $record['lastname'] . ' ' . $record['suffix']);
            $prev_notes_tx .= '<div class="existing-note '.$extraClassname.'">'
                    . '<span class="datetime">' . $record['created_dt'] . '</span> ' 
                    . '<span class="author-name">' . $fullname  . '</span> ' 
                    . '<span class="author-phone">' . $record['prefphone'] . '</span> ' 
                    . '<span class="author-email">' . $record['prefemail'] . '</span> '  
                    . '<div class="note-text">' . $record['notes_tx'] . '</div> '  
                    . '</div>';
        }
        
        return $prev_notes_tx;
    }
    
    /**
     * Return markup containing all the QA notes associated 
     * with the provided search criteria
     */
    public function getPreviousQANotesMarkup($nSiteID,$nIEN,$extraClassname='')
    {
        $sortable_result = array();
        
        $crit_tablename = 'raptor_qa_criteria';
        $eval_tablename = 'raptor_ticket_qa_evaluation';
        $notes_tablename = 'raptor_ticket_qa_notes';
        
        $prev_notes_tx = NULL;

        $oUser = $this->m_oContext->getUserInfo();
        $bCanSeeQA = $oUser->hasPrivilege('QA2');
        
        $evaldates = array();
        
        //Collect all the questions
        $crit_lookup = array();
        $crit_result = db_select($crit_tablename,'r')
                ->fields('r')
                ->execute();
        while($record = $crit_result->fetchAssoc())
        {
            $shortname = $record['shortname'];
            $crit_lookup[$shortname] = $record;
        }
            
        //Collect all the general notes first
        $note_query = db_select($notes_tablename, 'n');
        $note_query->join('raptor_user_profile', 'u', 'n.author_uid = u.uid');
        $note_query->fields('n', array('created_dt', 'notes_tx'));
        $note_query->fields('u', array('username','role_nm','usernametitle','firstname','lastname','suffix','prefemail','prefphone'))
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->orderBy('created_dt', 'ASC');
        $result = $note_query->execute();
        $general_notes = array();
        while($record = $result->fetchAssoc())
        {
            $thedate = $record['created_dt'];
            $evaldates[$thedate] = $record['username'];
            $now_key = $thedate . '_' . $record['username'];
            
            $details = array();
            $details['date'] = $record['created_dt'];
            $details['fullname'] = trim($record['usernametitle'] . ' ' 
                    . $record['firstname'] . ' ' 
                    . $record['lastname'] . ' ' 
                    . $record['suffix']);
            $details['prefphone'] = $record['prefphone'];
            $details['prefemail'] = $record['prefemail'];
            $details['comment'] = $record['notes_tx'];
            
            $general_notes[$now_key] = $details;
        }
        $shown_notes = array();
        
        //Get app existing eval markup
        $score_query = db_select($eval_tablename, 'n');
        $score_query->join('raptor_user_profile', 'u', 'n.author_uid = u.uid');
        $score_query->fields('n', array('evaluation_dt', 'comment', 'criteria_score', 'criteria_shortname'));
        $score_query->fields('u', array('username','role_nm','usernametitle','firstname','lastname','suffix','prefemail','prefphone'))
            ->condition('siteid', $nSiteID,'=')
            ->condition('IEN', $nIEN,'=')
            ->orderBy('evaluation_dt', 'ASC')
            ->orderBy('author_uid', 'ASC');
        $result = $score_query->execute();
        $prev_key = '';
        $mymarkup = '';
        while($record = $result->fetchAssoc())
        {
            //Create the markup.
            $thedate = $record['evaluation_dt'];
            $evaldates[$thedate] = $record['username'];
            $now_key = $thedate . '_' . $record['username'];
            if($now_key != $prev_key)
            {
                $fullname = trim($record['usernametitle'] . ' ' 
                        . $record['firstname'] . ' ' 
                        . $record['lastname'] . ' ' 
                        . $record['suffix']);
                if($prev_key > '')
                {
                    //End the previous markup
                    if(isset($general_notes[$prev_key]))
                    {
                        $details = $general_notes[$prev_key];
                        $mymarkup .= '<div class="note-text">' 
                                . 'Overall Comments: ' 
                                . $details['comment']
                                . '</div> ';
                        $shown_notes[$prev_key] = $prev_key;
                    }
                    $mymarkup .= '</div>';
                    $always_unique = $prev_key . "_" . count($sortable_result);
                    $sortable_result[$always_unique] = $mymarkup;
                    $mymarkup = ''; //Start again
                }
                //Start the markup
                $mymarkup .= '<div class="existing-note '.$extraClassname.'">'
                        . '<span class="datetime">' . $record['evaluation_dt'] 
                        . '</span> ' 
                        . '<span class="author-name">' . $fullname  
                        . '</span> ' 
                        . '<span class="author-phone">' . $record['prefphone'] 
                        . '</span> ' 
                        . '<span class="author-email">' . $record['prefemail'] 
                        . '</span> '  
                        . '<span class="note-type">QA Evaluation</span> ';
            }
            $shortname = $record['criteria_shortname'];
            $clup = $crit_lookup[$shortname];
            $score = $record['criteria_score'];
            $scoretext = \raptor\TermMapping::getQAScoreLanguage($score);
            $question = $clup['question']; 
            $mymarkup .= '<div class="note-text">' 
                    . $question 
                    . ' = <strong>' . $scoretext . '</strong>' 
                    . '</div> ';
            if($record['comment'] > '')
            {
                $mymarkup .= '<div class="note-text">' 
                        . $record['comment'] 
                        . '</div> ';
            }
            
            $prev_key = $now_key;
        }
        if($prev_key > '')
        {
            if(isset($general_notes[$prev_key]))
            {
                $details = $general_notes[$prev_key];
                $mymarkup .= '<div class="note-text">' 
                        . 'Overall Comments: ' 
                        . $details['comment']
                        . '</div> ';
                $shown_notes[$prev_key] = $prev_key;
            }
            //End the previous markup
            $mymarkup .= '</div>';
            $always_unique = $prev_key . "_" . count($sortable_result);
            $sortable_result[$always_unique] = $mymarkup;
            $mymarkup = ''; //Start again
        }
        
        //Now show general QA comments that did not have evaluation scores.
        foreach($general_notes as $key=>$value)
        {
            if(!isset($shown_notes[$key]))
            {
                $details = $general_notes[$key];
                $mymarkup = '<div class="existing-note '.$extraClassname.'">'
                        . '<span class="datetime">' . $details['date'] . '</span> ' 
                        . '<span class="author-name">' . $details['fullname']  . '</span> ' 
                        . '<span class="author-phone">' . $details['prefphone'] . '</span> ' 
                        . '<span class="author-email">' . $details['prefemail'] . '</span> '  
                        . '<span class="note-type">QA Overall Comment (No scores)</span> ';
                $mymarkup .= '<div class="note-text">' 
                        . $details['comment']
                        . '</div> ';
                $mymarkup .= '</div>';
                $always_unique = $prev_key . "_" . count($sortable_result);
                $sortable_result[$always_unique] = $mymarkup;
            }
        }
        
        //Compile the output.
        $prev_notes_tx = NULL;
        if(!$bCanSeeQA)
        {
            if(count($evaldates) > 0)
            {
                $mymarkup = '<div class="existing-note '.$extraClassname.'">'
                        . '<span class="note-type">QA Assements/Comments</span> ';
                $mymarkup .= '<div class="note-text">' 
                        . "Your account does not have access to the details of the assessments."
                        . '</div> ';
                $mymarkup .= '</div>';
                $prev_notes_tx = $mymarkup;
            }
        } else {
            //Now sort all the markup by key.
            ksort($sortable_result);
            foreach($sortable_result as $key=>$value)
            {
                $prev_notes_tx .= $value;
            }
        }
        
        //Return all the markup
        return $prev_notes_tx;
    }
    
    
    /**
     * Return markup containing all the scheduluer notes associated 
     * with the provided search criteria
     */
    public function getSchedulerNotesMarkup($nSiteID,$nIEN)
    {
        
        $scheduler_notes = NULL;

        $query = db_select('raptor_schedule_track', 'n');
        $query->fields('n');
        $query->condition('siteid',$nSiteID,'=');
        $query->condition('IEN',$nIEN,'=');
        $query->orderBy('scheduled_dt');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $scheduled_dt = $record['scheduled_dt'];
            if(isset($scheduled_dt))
            {
                $dt = new \DateTime($scheduled_dt);
                $event_date_tx = $dt->format('m/d/Y');
                $event_starttime_tx = $dt->format('H:i');
                $details = ' ('.$event_date_tx.'@'.$event_starttime_tx.')';
            } else {
                $details = '';
            }
            $fullname = 'Scheduler';
            $sClassText = 'existing-scheduler-note';
            if($record['notes_critical_yn'] == 1)
            {
                $sClassText .= ' critical-note';
            }
            $scheduler_notes .= "\n".'<div class="existing-note '.$sClassText.'">'
                    . '<span class="datetime">' . $record['created_dt'] . '</span> ' 
                    . '<span class="author-name">'.$fullname.'</span> ' 
                    . '<span class="scheduled-time-details">'.$details.'</span> ' 
                    . '<div class="note-text">' . $record['notes_tx'] . '</div> '  
                    . '</div>';
        }
        
        return $scheduler_notes;
    }
    
    /**
     * Return markup containing all the collaboration notes associated 
     * with the provided search criteria
     */
    public function getCollaborationNotesMarkup($nSiteID,$nIEN)
    {
        
        $sNotesMarkup = NULL;

        $query = db_select('raptor_ticket_collaboration', 'n');
        $query->join('raptor_user_profile','u','n.requester_uid = u.uid');
        $query->fields('n',array('requester_uid','requested_dt','requester_notes_tx','active_yn'));
        $query->fields('u',array('username', 'usernametitle', 'firstname', 'lastname', 'suffix'));
        $query->condition('n.siteid',$nSiteID,'=');
        $query->condition('n.IEN',$nIEN,'=');
        $query->orderBy('n.requested_dt');
        $result = $query->execute();
        while($record = $result->fetchAssoc())
        {
            $fullname = trim($record['usernametitle'] . ' ' . $record['firstname'] . ' ' . $record['lastname'] . ' ' . $record['suffix']);
            $sClassText = 'existing-collabrequest-note';
            if($record['active_yn'] == 1)
            {
                $sClassText .= ' active-note';
            }
            $sNotesMarkup .= '<div class="existing-note '.$sClassText.'">'
                    . '<span class="datetime">' . $record['requested_dt'] . '</span> ' 
                    . '<span class="context-indicator">Collaboration request from</span> ' 
                    . '<span class="author-name">'.$fullname.'</span> ' 
                    . '<div class="note-text">' . $record['requester_notes_tx'] . '</div> '  
                    . '</div>';
        }
        
        return $sNotesMarkup;
    }

    function getOverallProtocolDataEntryArea1($sCWFS, &$form_state, $disabled
            , $myvalues
            , $template_json=NULL
            , $cluesmap=NULL)
    {
        $disableAllInput = $disabled || (($sCWFS !== 'AC') && ($sCWFS !== 'RV') && ($sCWFS !== 'CO'));
        $disableChildInput = $disableAllInput 
                || (!isset($myvalues['protocol1_nm']) 
                || trim($myvalues['protocol1_nm']) == '');
        $form = array();
        
        //PROTOCOL 
        $modality_filter = array();
        if($template_json == NULL)
        {
            if(!$disableChildInput && isset($myvalues['protocol1_nm']) && trim($myvalues['protocol1_nm']) > '')
            {
                module_load_include('php', 'raptor_datalayer', 'core/data_protocolsettings');
                $oPS = new \raptor\ProtocolSettings();    //TODO cache it
                //$templatevalues = $oPS->getDefaultValuesStructured($myvalues['protocol1_nm']);
                $metainfo = $oPS->getProtocolMetaInformation($myvalues['protocol1_nm']);
                $templatevalues = $metainfo['defaultvalues'];
                $protocolattribs = $metainfo['attributes'];
                $modality_abbr = $protocolattribs['modality_abbr'];
                if($modality_abbr > '')
                {
                    $modality_filter[] = $modality_abbr;
                }
                $template_json = json_encode($templatevalues);
        //die("LOOK NOW TV=".print_r($metainfo,TRUE));
            } else {
                $template_json = '';    //Nothing needed.
            }
        }
        $hiddendatahtml = "\n<div id='protocol-template-data'>"
              . "\n<div id='json-default-values-all-sections'"
              . " style='visibility:hidden; height:0px;'>$template_json</div>\n"
              . "\n</div>";
        $form['hiddenptotocolstuff'] = array('#markup' 
            => $hiddendatahtml);
        
        //die("LOOK NOW=".print_r($templatevalues,TRUE));
        
        //Main protocol selection
        $form['protocolinput'][] = $this->getProtocolSelectionElement($form_state
                , $disableAllInput
                , $myvalues
                , TRUE
                , 'protocol1'
                , "A standard protocol from the hospital's radiology notebook."
                , TRUE
                , TRUE
                , $cluesmap);
        
        //Secondary protocol selection
        $form['protocolinput'][] = $this->getProtocolSelectionElement($form_state
                , $disableChildInput
                , $myvalues
                , FALSE
                , 'protocol2'
                , "Select a second protocol only if more than one is needed for this study."
                , FALSE, FALSE);
        
        //Contrast
        $shownow = $this->hasContrastValues($myvalues, $form_state);
        $contrastarea = $this->getOverallSectionCheckboxType($form_state
                , 'contrast', 'Contrast'
                , $disableChildInput
                , $myvalues
                , NULL
                , TRUE
                , $shownow
                , $shownow
                , $modality_filter); 
        $form['protocolinput'][] = $contrastarea;

        //Consent Required
        $shownow = !$disableChildInput;
        $consentarea = $this->getYesNoResetRadioTypeSection('consentreq', 'Consent Required'
            , $disableChildInput
            , $myvalues
            , NULL
            , TRUE
            , $shownow
            , $shownow);
        $form['protocolinput'][] = $consentarea;
        /*
        $form['protocolinput'][] = $this->raptor_form_get_consent($form_state
                , $disableChildInput, $myvalues, null, null);
        */
                
        //Hydration
        $shownow = $this->hasHydrationValues($myvalues);
        $hydrationarea = $this->getOverallSectionRadioType($form_state
                , 'hydration', 'Hydration'
                , $disableChildInput
                , $myvalues
                , NULL, TRUE
                , $shownow
                , $shownow
                , $modality_filter); 
        $form['protocolinput'][] = $hydrationarea;
        
        //Sedation
        $shownow = $this->hasSedationValues($myvalues);
        $sedationarea = $this->getOverallSectionRadioType($form_state
                , 'sedation', 'Sedation'
                , $disableChildInput
                , $myvalues
                , NULL, TRUE
            , $shownow
            , $shownow);
        $form['protocolinput'][] = $sedationarea;
        
        //Radioisotope
        $shownow = $this->hasRadioisotopeValues($myvalues);
        $radioisotopearea = $this->getOverallSectionCheckboxType($form_state
                , 'radioisotope', 'Radionuclide'
                , $disableChildInput
                , $myvalues
                , NULL
                , TRUE
                , $shownow
                , $shownow
                , $modality_filter); 
        $form['protocolinput'][] = $radioisotopearea;
        
        //Allergy
        $allergyarea = $this->getYesNoRadioTypeSection('allergy', 'Allergy (patient has)'
                    , $disableChildInput
                    , $myvalues
                    , NULL
                    , TRUE
                    , TRUE);
        $form['protocolinput'][] = $allergyarea;
        
        //Claustrophobic
        $claustrophobicarea = $this->getYesNoRadioTypeSection('claustrophobic', 'Claustrophobic (patient is)'
                    , $disableChildInput
                    , $myvalues
                    , NULL
                    , TRUE
                    , TRUE);
        $form['protocolinput'][] = $claustrophobicarea;

        return $form;
    }

    function getOverallProtocolDataEntryArea2($sCWFS, &$form_state, $disabled, $myvalues)
    {
        $disableAllInput = $disabled || (($sCWFS !== 'AC') && ($sCWFS !== 'RV') && ($sCWFS !== 'CO'));
        $disableChildInput = $disableAllInput 
                || (!isset($myvalues['protocol1_nm']) || isset($myvalues['protocol1_nm']) == '');
        
        $root = array();
        if(isset($myvalues['prev_protocolnotes_tx']))
        {
            $root["PrevProtocolNotes"] = array(
                '#prefix' => "\n<div class='prev-protocolnotes'>\n",
                '#markup' => $myvalues['prev_protocolnotes_tx'],
                '#suffix' => "\n</div>\n",
            );
        }
        if(isset($myvalues['prev_suspend_notes_tx']))
        {
            $root["PrevSuspendNotes"] = array(
                '#prefix' => "\n<div class='prev-suspend-notes'>\n",
                '#markup' => $myvalues['prev_suspend_notes_tx'],
                '#suffix' => "\n</div>\n",
            );
        }

        $root['ProtocolNotes'] 
                = $this->getNotesSectionMarkup('protocolnotes', 'Protocol Notes'
                , $disableChildInput, $myvalues);

        
        
        return $root;
    }

    function getOverallExamDataEntryArea($sCWFS
            , $protocolValues
            , &$form_state, $disabled, $myvalues)
    {
        $root = array();
        
        $modality_abbr = $protocolValues['modality_abbr'];
        $protocol_shortname = $protocolValues['protocol_shortname'];
        $disableExamInput = $disabled || ($sCWFS !== 'PA');

        if(!$disableExamInput)
        {
            //Always show previous notes first if input is NOT disabled.
            if(isset($myvalues['prev_exam_notes_tx']))
            {
                $root['PrevExamNotes'] = array(
                    '#prefix' => "\n<div class='prev-exam-notes'>\n",
                    '#markup' => $myvalues['prev_exam_notes_tx'],
                    '#suffix' => "\n</div>\n",
                );
            }
        }
        
        if($sCWFS == 'AP')
        {
            //Show the safety checklist in edit mode.
            $root['data_entry_area2']['page_checklist_area1'] 
                    = $this->getPageChecklistArea($form_state, $disabled, $myvalues,'Safety Checklist','SC',$modality_abbr,$protocol_shortname);
        } else if($sCWFS == 'PA' || $sCWFS == 'EC' || $sCWFS == 'QA') {
            //Show the safety checklist in disabled mode.  TODO --- force to answer if they have not answered some checklist items!!!!
            $root['data_entry_area2']['page_checklist_area1'] 
                    = $this->getPageChecklistArea($form_state, TRUE, $myvalues,'Safety Checklist','SC',$modality_abbr,$protocol_shortname);
            $root['exam_data_entry_area1'][]  
                    = $this->getExamDataEntryFields($form_state
                            , $disableExamInput
                            , $myvalues, $protocolValues);
        }

        if($disableExamInput)
        {
            //Always show previous notes LAST if input is disabled.
            if(isset($myvalues['prev_exam_notes_tx']))
            {
                $root['PrevExamNotes'] = array(
                    '#prefix' => "\n<div class='prev-exam-notes'>\n",
                    '#markup' => $myvalues['prev_exam_notes_tx'],
                    '#suffix' => "\n</div>\n",
                );
            }
        }
        
        return $root;
    }

    function getOverallInterpretationDataEntryArea($sCWFS, &$form_state, $disabled, $myvalues)
    {
        $root = array();
        if(isset($myvalues['prev_interpret_notes_tx']))
        {
            $root['PrevInterpretationNotes'] = array(
                '#prefix' => "\n<div class='prev-interpretation-notes'>\n",
                '#markup' => $myvalues['prev_interpret_notes_tx'],
                '#suffix' => "\n</div>\n",
            );
        }
        
        if($sCWFS == 'EC')
        {
            //Only show this when we are in EC mode.
            $disableInput = $disabled || ($sCWFS != 'EC');
            $root['interpretation_data_entry_area1']  = $this->getInterpretationDataEntryFields($form_state, $disableInput, $myvalues);
        }
        return $root;
    }
    
    function getOverallQADataEntryArea($sCWFS, &$form_state, $disabled, $myvalues)
    {
        $root = array();
        if(isset($myvalues['prev_qa_notes_tx']))
        {
            $root['PrevQANotes'] = array(
                '#prefix' => "\n<div class='prev-qa-notes'>\n",
                '#markup' => $myvalues['prev_qa_notes_tx'],
                '#suffix' => "\n</div>\n",
            );
        }
        
        if($sCWFS == 'QA' || $sCWFS == 'EC')
        {
            $root['qa_data_entry_area1']  = $this->getQADataEntryFields($form_state, $disabled, $myvalues);
        }
        return $root;
    }
    
    /**
     * We display this section if the section is pre-populated with default values.
     * @param string $section_name name of the section
     * @param boolean $disabled true if disabled, else enabled
     * @param array $defaultvalues the default values for the controls of the section
     * @return type section to add into the renderable array
     */
    function getDefaultValueSubSection($section_name, $disabled, $defaultvalues, $shownow)
    {
        $root = array();
        if(REQUIRE_ACKNOWLEDGE_DEFAULTS === FALSE)
        {
            $root['default_values_grp_'.$section_name.'']['acknowledge_'.$section_name] = array(
                '#type'  => 'hidden',
                '#value' => 'no',
            );
        } else {
            if($disabled)
            {
                //Do NOT show it if section is disabled.
                $shownow = FALSE;
            }
            $root['default_values_grp_'.$section_name.''] = array(
                '#type' => 'container',
                '#attributes' => array(
                        'class' => array('acknowledge-default-value'),
                        'style' => array($shownow ? 'display:inline' : 'display:none' ),
                    ),
            );
            $root['default_values_grp_'.$section_name.'']['acknowledge_'.$section_name] = array(
                '#type'    => 'checkbox',
                '#title' => FormHelper::getTitleAsUnrequiredField('Acknowledge Selected Values'),
                '#description' => t('You are being asked to acknowledge these values because they are currently the default values.'),
                '#disabled' => $disabled,
            );
        }

        return $root;
    }

    /**
     * Get the block of default value controls for a section
     * These sections will show/hide at runtime using AJAX.
     */
    function getDefaultValueControls($section_name, $disabled
            , $defaultvalues=NULL, $require_ack=FALSE)
    {
        $root = array();
        if(REQUIRE_ACKNOWLEDGE_DEFAULTS === FALSE)
        {
            $root[]['require_acknowledgement_for_'.$section_name] = array(
                '#type'  => 'hidden',
                '#value' => 'no',
            );
        } else {
            //Always create the markup, but show it only if there are default values.
            $shownow = $require_ack;
            $root[] = $this->getDefaultValueSubSection($section_name, $disabled, $defaultvalues, $shownow);

            //Create a hidden field with standard Drupal framework ID name so javascript can track required or not.
            $root[]['require_acknowledgement_for_'.$section_name] = array(
                '#type' => 'hidden', 
                '#attributes' => array('id' 
                    => 'edit-require-acknowledgement-for-'.$section_name ),
                '#default_value' => ($require_ack ? 'yes' : 'no'),
            );
        }
        return $root;
    }

    /**
     * Create an input area for a single checklist question.
     * @param array $myvalues
     * @param number $number
     * @param object $item
     * @param boolean $disabled
     * @param boolean $bRequireValue
     * @return renderable array element
     */
    private function getOneChecklistQuestion($myvalues,$title
            ,$number,$aOneQuestion,$disabled,$bRequireValue=TRUE)
    {
        if($title == NULL || trim($title) == '')
        {
            $title = 'Question '.$number;   //Must have a title otherwise validation feedback breaks.
        }
        
        $shortname = $aOneQuestion['question_shortname'];
        $question_tx = $aOneQuestion['question_tx'];
        $comment_prompt_tx = $aOneQuestion['comment_prompt_tx'];
        
        $ask_yes_yn = $aOneQuestion['ask_yes_yn'];
        $ask_no_yn = $aOneQuestion['ask_no_yn'];
        $ask_notsure_yn = $aOneQuestion['ask_notsure_yn'];
        $ask_notapplicable_yn = $aOneQuestion['ask_notapplicable_yn'];

        $trigger_comment_on_yes_yn = $aOneQuestion['trigger_comment_on_yes_yn'];
        $trigger_comment_on_no_yn = $aOneQuestion['trigger_comment_on_no_yn'];
        $trigger_comment_on_notsure_yn = $aOneQuestion['trigger_comment_on_notsure_yn'];
        $trigger_comment_on_notapplicable_yn = $aOneQuestion['trigger_comment_on_notapplicable_yn'];
        
        //drupal_set_message('LOOK>>>'.$number.') '.$question_tx);

        //Look for values associated with currently logged in user.
        $aQuestion = isset($myvalues['questions']['thisuser'][$shortname]) ? $myvalues['questions']['thisuser'][$shortname] : NULL;
        if(!is_array($aQuestion))
        {
            $default_response = NULL;   //IMPORTANT THIS MUST BE NULL instead of empty string else DRUPAL ERRORS!
            $default_comment = NULL;
        } else {
            $default_response = $aQuestion['response'];
            $default_comment = $aQuestion['comment'];
        }
                       
        $element = array();
        $aRadios = array();
        $aOptions = array();
        $showOnValues = '';
        //$showOnValues = '[no][notsure][notapplicable]';
        if($ask_yes_yn == 1)
        {
            $aOptions['yes'] = t('Yes');
            if($trigger_comment_on_yes_yn == 1)
            {
                $showOnValues .= '[yes]';
            }
        }
        if($ask_no_yn)
        {
            $aOptions['no'] = t('No');
            if($trigger_comment_on_no_yn == 1)
            {
                $showOnValues .= '[no]';
            }
        }
        if($ask_notsure_yn)
        {
            $aOptions['notsure'] = t('Not Sure');
            if($trigger_comment_on_notsure_yn == 1)
            {
                $showOnValues .= '[notsure]';
            }
        }
        if($ask_notapplicable_yn)
        {
            $aOptions['notapplicable'] = t('Not Applicable');
            if($trigger_comment_on_notapplicable_yn == 1)
            {
                $showOnValues .= '[notapplicable]';
            }
        }
        $hiddenshowcommentonvalues = 'showcommentonvalues';
        $hiddenshortname = 'shortname';
        $radiosDrupalName = 'response';//.$number;
        $commentDrupalName = 'comment';//$radiosName.'_comment';
        $commentHtmlTagName = 'questions[thisuser]['.$shortname.']['.$commentDrupalName.']'; //Because #tree structure!
        $sTextareaHtmlwrapperId = $commentHtmlTagName.'-wrapper';
        if($default_comment > '')
        {
            //We have comment text so show it.
            $shownow = TRUE;
        } else {
            //Show the comment box only if the buttons say we should.
            if(!isset($myvalues[$radiosDrupalName]))
            {
                $shownow = FALSE;
            } else {
                $value = $myvalues[$radiosDrupalName];
                if(strpos($showOnValues,'['.$value.']') !== FALSE)
                {
                    $shownow = TRUE;
                } else {
                    $shownow = FALSE;
                }
            }
        }
        $aHiddenShowOnValues = array('#type'=>'hidden','#value'=>$showOnValues);
        $aRadios = array(
            '#type' => 'radios',
            '#options' => $aOptions,
            //'#required' => TRUE, //$bRequireValue,
            '#disabled' => $disabled,
            '#attributes' => array(
                'onclick' => 'manageChecklistQuestionCommentByName(this.value,"'.$showOnValues.'","'.$commentHtmlTagName.'");',
             ),
             '#title' => FormHelper::getTitleAsRequiredField($title, $disabled),   //Important to have title otherwise required symbol not shown! 
             '#default_value' => $default_response,
        );
        $aRadios['#attributes']['class'][] = 'question-options';
        $aComment = array(
                    '#type'          => 'textarea',
                    '#prefix'        => "\n".'<div name="'.$sTextareaHtmlwrapperId.'" class="comment-wrapper"'
                                        .' style="' . ($shownow ? 'display:inline' : 'display:none') . '" >',
                    '#suffix'        => "\n".'</div> <!-- End of '.$sTextareaHtmlwrapperId.' -->',
                    '#title'         => FormHelper::getTitleAsUnrequiredField($comment_prompt_tx),
                    '#default_value' => $default_comment,
                    '#disabled'      => $disabled,
                    /*
                    '#attributes' => array(
                        'style' => array($shownow ? 'display:inline' : 'display:none' )
                        ),
                     */
                );
        
        $element[] = array('#markup' => "\n".'<div class="question-block">');
        $element[$hiddenshortname] = array('#type'=>'hidden','#value'=>$shortname);
        $element[$hiddenshowcommentonvalues] = $aHiddenShowOnValues;
        $element[$radiosDrupalName] = $aRadios;
        $element['question-text'] = array('#markup' => "\n".'<div class="question">'.$question_tx.'</div>');
        $element[$commentDrupalName] = $aComment;
        $element[] = array('#markup' => "\n".'</div>');
        return $element;
    }
    
    /**
     * Get all the questions as an array of arrays.
     * There are only three types of questions, progressive filtering ...
     * 1. No filter
     * 2. Filtered on modality
     * 3. Filtered down to one protocol (a protocol applies to only one modality already)
     */
    function getAllQuestions($sChecklistType='SC',$modality_abbr='',$protocol_shortname='')
    {
        $aQuestionRef = array();
        $result = db_select('raptor_checklist_question','q')
                ->fields('q')
                ->orderBy('relative_position')
                ->condition('q.type_cd',$sChecklistType,'=')
                ->condition('modality_abbr','','=')
                ->condition('protocol_shortname','','=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $shortname = $record['question_shortname'];
            $aQuestionRef[$shortname] = $record;
            $aQuestionRef[$shortname]['subtype'] = 'core';
        }
        if($modality_abbr > '')
        {
            //Now grab any questions specific to this modality.
            $result = db_select('raptor_checklist_question','q')
                    ->fields('q')
                    ->orderBy('relative_position')
                    ->condition('q.type_cd',$sChecklistType,'=')
                    ->condition('modality_abbr',$modality_abbr,'=')
                    ->condition('protocol_shortname','','=')
                    ->execute();
            while($record = $result->fetchAssoc())
            {
                $shortname = $record['question_shortname'];
                $aQuestionRef[$shortname] = $record;
                $aQuestionRef[$shortname]['subtype'] = 'modality';
            }
        }
        if($protocol_shortname > '')
        {
            //Now grab any questions specific to this protocol.
            $result = db_select('raptor_checklist_question','q')
                    ->fields('q')
                    ->orderBy('relative_position')
                    ->condition('q.type_cd',$sChecklistType,'=')
                    ->condition('modality_abbr','','=')
                    ->condition('protocol_shortname',$protocol_shortname,'=')
                    ->execute();
            while($record = $result->fetchAssoc())
            {
                $shortname = $record['question_shortname'];
                $aQuestionRef[$shortname] = $record;
                $aQuestionRef[$shortname]['subtype'] = 'protocol';
            }
        }
        return $aQuestionRef;
    }
    
    /**
     * The user has to respond to the checklist questions provided by this function.
     * @param type $form_state
     * @param type $disabled
     * @param type $myvalues
     * @param type $sSectionTitle
     * @param type $sChecklistType
     * @param type $modality_abbr
     * @param type $protocol_shortname
     * @return element for a renderable form array
     */
    function getPageChecklistArea(&$form_state, $disabled, $myvalues
            , $sSectionTitle, $sChecklistType
            , $modality_abbr, $protocol_shortname)
    {

        $root = array(
            '#type'     => 'fieldset',
            '#title'    => 
                FormHelper::getTitleAsRequiredField($sSectionTitle, $disabled),
            '#attributes' => array(
                'class' => array(
                    'checklist-dataentry-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $root[] = array('#markup' => '<div class="safety-checklist">');
        
        //die('LOOK all answers>>>'.print_r($myvalues['questions'],TRUE));      

        //Grab all the relevant questions.
        $aQuestionRef = $this->getAllQuestions($sChecklistType,$modality_abbr,$protocol_shortname);
        //First grab all the checklist questions already answered by other users.
        if(isset($myvalues['questions']['otheruser']))
        {
            $aAnsweredByOtherUsers = $myvalues['questions']['otheruser'];
            $otherusersarea = array();
            foreach($aAnsweredByOtherUsers as $nUID=>$aFromOneUser)
            {
                if(is_array($aFromOneUser))
                {
                    //die('Look now>>>'.$nUID.'>>>'.print_r($aFromOneUser,TRUE));
                    $oOtherUser = new \raptor\UserInfo($nUID,FALSE);
                    $username = $oOtherUser->getFullName();
                    $otherusersarea[$nUID] = array(
                        '#type'     => 'fieldset',
                        '#title'    => t('Answers from '. $username),
                        '#attributes' => array(
                            'class' => array(
                                'otheruser-safety-checklist-answers-area'
                            )
                         ),
                        '#disabled' => TRUE,
                    );
                    $element = array('#markup' => '<ol>' );
                    $otherusersarea[$nUID][] = $element;
                    $questionnumber = 0;
                    foreach($aFromOneUser as $sShortName=>$aOneQuestionAnswer)
                    {
                        $details = $aQuestionRef[$sShortName];
                        $question_tx = $details['question_tx'];
                        $comment_prompt_tx = $details['comment_prompt_tx'];
                        $response = $aOneQuestionAnswer['response'];
                        $comment = $aOneQuestionAnswer['comment'];
                        $element = array('#markup' => '<li>'.$question_tx.'<p>'.$response.'</p>');
                        $otherusersarea[$nUID][] = $element;
                        if(trim($comment) > '')
                        {
                            $element = array('#markup' => '<p class="comment-prompt">'.$comment_prompt_tx.'</p><p>'.$comment.'</p>');
                            $otherusersarea[$nUID][] = $element;
                        }
                    }
                    $element = array('#markup' => '</ol>' );
                    $otherusersarea[$nUID][] = $element;
                }
            }
            $root['other_answers'] = &$otherusersarea;
        }

        //Now show the content for this user.
        if(!$disabled || (isset($myvalues['questions']['thisuser']) 
                && is_array($myvalues['questions']['thisuser'])))
        {
            $questionnumber = 0;
            foreach($aQuestionRef as $aOneQuestion)
            {
                $questionnumber++;
                if($aOneQuestion['subtype'] == 'core')
                {
                    $title = 'Core Question '.$questionnumber;
                }
                if($aOneQuestion['subtype'] == 'modality')
                {
                    $title = 'Modality Specific Question '.$questionnumber;
                }
                if($aOneQuestion['subtype'] == 'protocol')
                {
                    $title = 'Protocol Specific Question '.$questionnumber;
                }
                $shortname = $aOneQuestion['question_shortname'];
                $element = $this->getOneChecklistQuestion($myvalues
                        ,$title,$questionnumber,$aOneQuestion,$disabled);
                $root['questions']['thisuser'][$shortname] = $element;
            }
            $root['questions']['#tree'] = TRUE;
            $root[] = array('#markup' => '</div><!-- end of safety checklist for modality=['.$modality_abbr.'] of protocol=['.$protocol_shortname.'] -->');
        }
        
        return $root;
    }    
    
    function getPageActionButtonsArea(&$form_state
            , $disabled
            , $myvalues
            , $has_uncommitted_data=FALSE
            , $commited_dt=NULL)
    {
        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $sCWFS = $this->getCurrentWorkflowState($nSiteID, $nIEN);
        $mdwsDao = $this->m_oContext->getMdwsClient();

        $configuredVistaCommit=TRUE;
        $checkVistaNoteTitle=VISTA_NOTE_TITLE_RAPTOR_GENERAL;
        $checkVistaNoteIEN=VISTA_NOTEIEN_RAPTOR_GENERAL;
        if(!MdwsUtils::verifyNoteTitleMapping($mdwsDao, $checkVistaNoteIEN, $checkVistaNoteTitle))
        {
            //Write to the log and continue.
            error_log("WARNING VISTA not configured for NOTE TITLE entry $checkVistaNoteIEN=$checkVistaNoteTitle!");
            $configuredVistaCommit = FALSE;
        }
        $checkVistaNoteTitle=VISTA_NOTE_TITLE_RAPTOR_SAFETY_CKLST;
        $checkVistaNoteIEN=VISTA_NOTEIEN_RAPTOR_SAFETY_CKLST;
        if(!MdwsUtils::verifyNoteTitleMapping($mdwsDao, $checkVistaNoteIEN, $checkVistaNoteTitle))
        {
            //Write to the log and continue.
            error_log("WARNING VISTA not configured for NOTE TITLE entry $checkVistaNoteIEN=$checkVistaNoteTitle!");
            $configuredVistaCommit = FALSE;
        }
        
        $acknowledgeTip = 'Acknowledge the presented protocol so the exam can begin.';
        $unapproveTip = 'Save this order as unapproved so protocol items can be edited.';
        $unacknowledgeTip = 'Unacknowledge the presented protocol so the exam cannot begin.  Warning: Unsaved entries, if any, will be discareded.';
        $examcompletionTip = 'Save all current settings and mark the examination as completed.';
        $interpretationTip = 'Save interpretation notes.';
        $qaTip = 'Save QA notes.';
        $saveSoFarTip = 'Save values of page so far and continue adding more values on this page';
        if($oContext->hasPersonalBatchStack())
        {
            $sRequestApproveTip = 'Save this order as ready for review and continue with next available personal batch selection.';
            $releaseTip = 'Release this order without saving changes and continue with next available personal batch selection.';
            $reserveTip = 'Assign this order to yourself with current edits saved and continue with the next available personal batch selection.';
            $collaborateTip = 'Assign this order a specialist with current edits saved and continue with the next available personal batch selection.';
            $approveTip = 'Save this order as approved and continue with the next available personal batch selection.';
            $suspendTip = 'Suspend this order without saving edits and continue with the next available personal batch selection.';
            $cancelOrderTip = 'Cancel this order in VistA and continue with the next available personal batch selection.';
            $sUnsuspendTip = 'Restore this order back to the worklist and continue with next available personal batch selection.';
            $replaceOrderTip = 'Replace this order in VistA with a new order and continue with the next available personal batch selection';
            $createNewOrderTip = 'Create a new order in VistA with continue with the next available personal batch selection';
            $ackproAndCommitTip = 'Mark workflow as finished and commit the details to Vista and continue with the next available personal batch selection';
            $examcompAndCommitTip = 'Mark workflow as finished and commit the details to Vista and continue with the next available personal batch selection';
            $interpretationAndCommitTip = 'Mark workflow as finished and commit the details to Vista and continue with the next available personal batch selection';
        } else {
            $sRequestApproveTip = 'Save this order as ready for review and return to the worklist.';
            $releaseTip = 'Release this order without saving changes and return to the worklist.';
            $reserveTip = 'Assign this order to yourself with current edits saved and return to the worklist.';
            $collaborateTip = 'Assign this order to a specialist with current edits saved and return to the worklist.';
            $approveTip = 'Save this order as approved and return to the worklist.';
            $cancelOrderTip = 'Cancel this order in VistA and return to the worklist.';
            $suspendTip = 'Suspend this order without saving changes and return to the worklist.';
            $sUnsuspendTip = 'Restore this order back to the worklist.';
            $replaceOrderTip = 'Replace this order in VistA with a new order';
            $createNewOrderTip = 'Create a new order in VistA';
            $ackproAndCommitTip = 'Mark workflow as finished and commit the details to VistA';
            $examcompAndCommitTip = 'Mark workflow as finished and commit the details to VistA';
            $interpretationAndCommitTip = 'Mark workflow as finished and commit the details to VistA';
        }
        if(!$configuredVistaCommit)
        {
            $ackproAndCommitTip = 'VistA not configured to support this!  Contact system admin.';
            $examcompAndCommitTip = 'VistA not configured to support this!  Contact system admin.';
            $interpretationAndCommitTip = 'VistA not configured to support this!  Contact system admin.';
        }
        
        $feedback = NULL;
        
        $form['page_action_buttons_area'] = array(
            '#type' => 'container',
            '#attributes' => array('class'=>array('form-action')),
        );

        //Leverage workflow dependences from special class
        $oAA = new \raptor\AllowedActions();
        
        //Only show these buttons if not disabled.
        if(!$disabled)
        {
            if($oAA->allowAcknowledgeProtocol($sCWFS))
            {
                $form['page_action_buttons_area']['acknowledge_button'] = array('#type' => 'submit'
                    , '#value' => t('Acknowledge Protocol')
                    , '#attributes' => array('title' => $acknowledgeTip)
                    );
            }
            if($oAA->allowExamComplete($sCWFS))
            {
                $form['page_action_buttons_area']['savesofar_button'] = array('#type' => 'submit'
                    , '#value' => t('Save Exam Values')
                    , '#attributes' => array('title' => $saveSoFarTip)
                    );
                $form['page_action_buttons_area']['examcompleted_button'] = array('#type' => 'submit'
                    , '#value' => t('Exam Completed')
                    , '#attributes' => array('title' => $examcompletionTip)
                    );
            }
            
            if($oAA->allowCommitNotesToVista($sCWFS))
            {
                //Check for special short circuit finish buttons
                if($sCWFS == 'AP')
                {
                    $form['page_action_buttons_area']['finish_ap_button_and_commit'] = array('#type' => 'submit'
                        , '#value' => t('Acknowledge Protocol and Commit Details to VistA')
                        , '#attributes' => array('title' => $ackproAndCommitTip)
                        , '#disabled' => !$configuredVistaCommit, 
                        );
                } else
                if($sCWFS == 'PA')
                {
                    $form['page_action_buttons_area']['finish_pa_button_and_commit'] = array('#type' => 'submit'
                        , '#value' => t('Exam Completed and Commit Details to VistA')
                        , '#attributes' => array('title' => $examcompAndCommitTip)
                        , '#disabled' => !$configuredVistaCommit, 
                        );
                }
            }
            
            if($oAA->allowInterpretationComplete($sCWFS))
            {
                $form['page_action_buttons_area']['interpret_button'] = array('#type' => 'submit'
                    , '#value' => t('Interpretation Complete')
                    , '#attributes' => array('title' => $interpretationTip)
                    );
                if($oAA->allowCommitNotesToVista($sCWFS))
                {
                    if($has_uncommitted_data)
                    {
                        $form['page_action_buttons_area']['interpret_button_and_commit'] = array('#type' => 'submit'
                            , '#value' => t('Interpretation Complete and Commit Details to VistA')
                            , '#attributes' => array('title' => $interpretationAndCommitTip)
                            , '#disabled' => !$configuredVistaCommit,
                            );
                    } else {
                        $feedback = 'All procedure data has been committed to VistA';
                        if($commited_dt != NULL)
                        {
                            $feedback .= ' as of ' . $commited_dt;
                        }
                    }
                }
            }
            if($oAA->allowQAComplete($sCWFS))
            {
                $form['page_action_buttons_area']['qa_button'] = array('#type' => 'submit'
                    , '#value' => t('QA Complete')
                    , '#attributes' => array('title' => $qaTip)
                    );
                if($oAA->allowCommitNotesToVista($sCWFS))
                {
                    if($has_uncommitted_data)
                    {
                        $form['page_action_buttons_area']['qa_button_and_commit'] = array('#type' => 'submit'
                            , '#value' => t('QA Complete and Commit Details to VistA')
                            , '#attributes' => array('title' => $qaTip)
                            , '#disabled' => !$configuredVistaCommit,  
                            );
                    } else {
                        $feedback = 'All procedure data has been committed to VistA';
                        if($commited_dt != NULL)
                        {
                            $feedback .= ' as of ' . $commited_dt;
                        }
                    }
                }
            }

            if($userprivs['PWI1'] == 1)
            {
                if($userprivs['APWI1'] == 1)
                {
                    if($oAA->allowApproveProtocol($sCWFS))
                    {
                        $form['page_action_buttons_area']['approve_button'] = array('#type' => 'submit'
                            , '#value' => t('Approve')
                            , '#attributes' => array('title' => $approveTip)
                            );
                    }
                } else {
                    if($oAA->allowRequestApproveProtocol($sCWFS))
                    {
                        $form['page_action_buttons_area']['request_approve_button'] = array('#type' => 'submit'
                            , '#value' => t('Request Approval')
                            , '#attributes' => array('title' => $sRequestApproveTip)
                            );
                    }
                }
                if($oAA->allowCollaborateTicket($sCWFS))
                {
                    $form['page_action_buttons_area']['collaborate_button'] 
                            = array('#markup' 
                                => '<input id="raptor-protocol-collaborate"'
                                . ' type="button"'
                                . ' value="Collaborate" title="'.$collaborateTip.'">');
                }
            }
        }

        //Always show this button no matter what.
        global $base_url;
        $form['page_action_buttons_area']['release_button'] = array('#type' => 'button'
            , '#value' => t('Release back to Worklist without Saving')
            , '#attributes' 
              => array('onclick' 
                 => 'javascript:window.location.href="'.$base_url.'/protocol?pbatch=CONTINUE&releasedticket=TRUE";return false;'
                    ,'title' => $releaseTip)
            //, '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
            );
        $form['page_action_buttons_area']['release_button']['#attributes']['class'][] = 'action-button';
        
        //Only show these buttons if not disabled.
        if(!$disabled)
        {
            if($oAA->allowCollaborateTicket($sCWFS))
            {
                if($sCWFS == 'CO')
                {
                    //This ticket is already in collaboration mode
                    $query = db_select('raptor_ticket_collaboration', 'n');
                    $query->join('raptor_user_profile','u','n.collaborator_uid = u.uid');
                    $query->fields('n',array('collaborator_uid','requested_dt','requester_notes_tx','active_yn'));
                    $query->fields('u',array('username', 'usernametitle', 'firstname', 'lastname', 'suffix'));
                    $query->condition('n.siteid',$nSiteID,'=');
                    $query->condition('n.IEN',$nIEN,'=');
                    $query->condition('n.active_yn',1,'=');
                    $result = $query->execute();
                    $record = $result->fetchAssoc();
                    if($record != NULL)
                    {
                        $fullname = trim($record['usernametitle'] . ' ' . $record['firstname'] . ' ' . $record['lastname'] . ' ' . $record['suffix']);
                        $assignmentBlurb = 'already assigned to '.$fullname;
                    } else {
                        //This should not happen but if it does leave a clue
                        $errMsg = ('Did NOT find name of user assigned for collaboration on ticket '.$nSiteID.'-'.$nIEN);
                        error_log($errMsg);
                        drupal_set_message($errMsg,'error');
                        $assignmentBlurb = 'already assigned';
                    }

                    $form['page_action_buttons_area']['reserve_button'] = array('#type' => 'submit'
                        , '#value' => t('Reserve ('.$assignmentBlurb.')')
                        , '#attributes' => array('title' => $reserveTip)
                        );
                } else {
                    //This ticket is not already in collaboration mode
                    $form['page_action_buttons_area']['reserve_button'] = array('#type' => 'submit'
                        , '#value' => t('Reserve')
                        , '#attributes' => array('title' => $reserveTip)
                        );
                }
            }

            if($userprivs['SUWI1'] == 1)
            {
                if($oAA->allowReplaceOrder($sCWFS))
                {
                    //Replace order only if allowed to cancel an order
                    $form['page_action_buttons_area']['replace_order_button'] 
                            = array('#markup' 
                                => '<input id="raptor-protocol-replace-order-button"'
                                . ' type="button"'
                                . ' value="Replace Order" title="'.$replaceOrderTip.'">');
                }
                if($oAA->allowCancelOrder($sCWFS))
                {
                    //Cancel an order only if allowed to cancel an order
                    $form['page_action_buttons_area']['cancelorder_button'] = array('#type' => 'submit'
                        , '#value' => t('Cancel Order')
                        , '#attributes' => array('title' => $cancelOrderTip)
                        );
                }
            }
        }
        
        //Show special workflow override buttons at the end
        if(!$disabled)
        {
            if($userprivs['APWI1'] == 1)
            {
                if($oAA->allowUnapproveProtocol($sCWFS))
                {
                    $form['page_action_buttons_area']['unapprove_button'] = array('#type' => 'submit'
                        , '#value' => t('Unapprove')
                        , '#attributes' => array('title' => $unapproveTip)
                        );
                }
            }
            if($oAA->allowUnacknowledgeProtocol($sCWFS))
            {
                $form['page_action_buttons_area']['unacknowledge_button'] = array('#type' => 'submit'
                    , '#value' => t('Unacknowledge Protocol')
                    , '#attributes' => array('title' => $unacknowledgeTip)
                    );
            }
        }
        
        if($feedback != NULL)
        {
            $form['page_action_buttons_area']['feedback'] = array(
                '#markup' => ' <span class="action-area-feedback">'.t($feedback).'</span>'
                );
        }

        $form['page_action_buttons_area']['bottom_filler'] = array(
            '#markup' => '<br><br><br><!-- Bottom gap -->',
        );
        
        return $form;
    }

    /**
     * @return int number of words that matched between the lists
     */
    public function countMatchingWords($list1,$list2)
    {
        $count = 0;
        if(is_array($list1) && is_array($list2))
        {
            foreach($list1 as $word1)
            {
                if(trim($word1) > '')
                {
                    foreach($list2 as $word2)
                    {
                        if($word1 == $word2)
                        {
                            $count++;
                        }
                    }
                }
            }
        }
        return $count;
    }

    /**
     * Return a map of keywords for all the protocols.
     */
    public function getKeywordMap()
    {
        $kwmap = array();
        $kwres = db_select('raptor_protocol_keywords','p')
            ->fields('p')
            ->orderBy('protocol_shortname', 'ASC')
            ->orderBy('weightgroup', 'ASC')
            ->execute();
        while($kwrec = $kwres->fetchAssoc()) 
        {
            $psn = $kwrec['protocol_shortname'];
            if(!isset($kwmap[$psn]))
            {
                $kwmap[$psn] = array();
            }
            $kw = trim($kwrec['keyword']);
            $wg = trim($kwrec['weightgroup']);
            $kwmap[$psn][$wg][] = $kw;
        }
        return $kwmap;
    }
    
    /**
     * Get the list of protocol choices, including the short list.
     * TODO -- CACHE THESE THINGS FOR BETTER PERFORMANCE
     */
    private function getProtocolChoices($procName=NULL
            ,$sFirstElementText=''
            ,$cluesmap=NULL)
    {
        
//drupal_set_message('getProtocolChoices>>>CLUES='.print_r($cluesmap,TRUE).'<br>Contrast clue='.($cluesmap['contrast'] === NULL ? 'NULL' : $cluesmap['contrast']));

        /*
        //Get a local structure of all the template keywords
        $kwmap = array();
        $kwres = db_select('raptor_protocol_keywords','p')
            ->fields('p')
            ->orderBy('protocol_shortname', 'ASC')
            ->orderBy('weightgroup', 'ASC')
            ->execute();
        while($kwrec = $kwres->fetchAssoc()) 
        {
            $psn = $kwrec['protocol_shortname'];
            if(!isset($kwmap[$psn]))
            {
                $kwmap[$psn] = array();
            }
            $kw = trim($kwrec['keyword']);
            $wg = trim($kwrec['weightgroup']);
            $kwmap[$psn][$wg][] = $kw;
        }
        */
        $kwmap = $this->getKeywordMap();
        //Get all the protocols from the library
        $result = db_select('raptor_protocol_lib','p')
                ->fields('p')
                ->orderBy('modality_abbr', 'ASC')
                ->orderBy('name', 'ASC')
                ->execute();
        $shortcount = 0;
        $scoretrack = array();
        $aShortList = array();
        $aCombinedList = array();
        while($record = $result->fetchAssoc()) 
        {
            $categoryname = trim($record['modality_abbr']).' List';
            $longname = $record['name'];
            $psn = $record['protocol_shortname'];
            if($categoryname == ' List')
            {
                //Put it on the shortlist
                $categoryname = 'Short List';
            }
            $oC = new \raptor\FormControlChoiceItem(
                    $longname
                    ,$psn
                    ,$categoryname
                    ,FALSE);
            $aCombinedList[] = $oC;

            if($categoryname != 'Short List')
            {
                //Does this also belong on the shortlist?
                $modality_abbr = $record['modality_abbr'];
                $contrast_yn = $record['contrast_yn'];
                $matchscore = $this->m_oMOP->getProtocolMatchScore($cluesmap
                        , $psn
                        , $longname
                        , $modality_abbr
                        , $contrast_yn
                        , $kwmap);                
                if($matchscore > 0)
                {
                    //Good enough on contrast check
                    $categoryname = 'Short List';
                    $oC = new \raptor\FormControlChoiceItem(
                            $longname
                            ,$psn
                            ,$categoryname
                            ,FALSE);
                    $oC->nScore = $matchscore;
                    $aShortList[$matchscore][] = $oC;
                    if($matchscore > 1)
                    {
                        if(!isset($scoretrack[$matchscore]))
                        {
                            $scoretrack[$matchscore] = 1;
                        } else {
                            $scoretrack[$matchscore] = $scoretrack[$matchscore] + 1;
                        }
                        $shortcount++;
                    }
                }
            }
        }
        $aFinalList = array();
        $oC = new \raptor\FormControlChoiceItem(
                $sFirstElementText
                ,NULL
                ,NULL
                ,FALSE);
        $aFinalList[] = $oC;
        krsort($aShortList);
        if($shortcount > 0)
        {
            krsort($scoretrack);
//drupal_set_message('SCORE TRACK>>>'.print_r($scoretrack,TRUE));
            $items = 0;
            $minscore = 2;  //Default min score
            foreach($scoretrack as $score=>$count)
            {
                $items += $count;
                if($items > 4)
                {
                    //Thats enough for our shortlist.
                    $minscore = $score;
                    break;
                }
            }
//drupal_set_message('$minscore>>>'.$minscore);
        } else {
            $minscore = 0;
        }
        foreach($aShortList as $k=>$list)
        {
            foreach($list as $oC)
            {
                if($oC->nScore >= $minscore)
                {
                    //$oC->sCategory = "DEBUG MINSCORE=".$minscore." THISSCORE=".$oC->nScore;
                    $aFinalList[] = $oC;
                }
            }
        }
        foreach($aCombinedList as $oC)
        {
            $aFinalList[] = $oC;
        }

        return $aFinalList;
    }

    /**
     * Get the the protocol selection section element
     */
    function getProtocolSelectionElement(&$form_state, $disabled
            , $myvalues,$bFindMatch=TRUE
            , $sBaseName='protocol1'
            , $sDescription=NULL
            , $bUseAjax=FALSE
            , $bRequireValue=FALSE
            , $cluesmap=NULL)
    {

        $oPPU = new ProtocolPageUtils();
        if($sBaseName == 'protocol1')
        {
            $title = 'Protocol Name';
        } else {
            $title = 'Secondary Protocol Name';
        }
        if($bRequireValue)
        {
            $title = FormHelper::getTitleAsRequiredField($title, $disabled);
        } else {
            $title = FormHelper::getTitleAsUnrequiredField($title, $disabled);
        }
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $title,
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        if($bFindMatch)
        {
            $choices  = $this->getProtocolChoices($myvalues['procName'],"- Select -", $cluesmap);
        } else {
            $choices  = $this->getProtocolChoices("");
        }
        //drupal_set_message('>>>choices>>>'.print_r($choices,TRUE));
        $element  = array(
            '#type'        => 'select',
            '#description' => $sDescription,
            '#attributes' => array(
                'class' => array(
                    'select2'
                )
             ),
            '#select2' => array(),
            );
        if (isset($myvalues[$sBaseName.'_nm']))
        {
            if ($disabled)
            {
                $element['#value']         = $myvalues[$sBaseName.'_nm'];
            }
            else
            {
                $element['#default_value'] = $myvalues[$sBaseName.'_nm'];
                //$element['#required']      = $bRequireValue;
            }
        }
        
        $element = $oPPU->getFAPI_select_options($element, $choices);
        if($bUseAjax)
        {
            $element['#ajax'] = array(
                'callback' => 'raptor_fetch_protocol_defaults',
                //'wrapper' => 'protocol-template-data',    //Using other commands in the callback instead
                //'method' => 'replace'
            );
        }

        $root[$sBaseName.'_nm']                             = $element;
        $form['main_fieldset_left'][$sBaseName.'_fieldset'] = &$root;

        return $form;
    }
    
    /**
     * Create subsection element based on vector
     */
    function getVectorBasedSubSectionMarkup(&$root,$vector,$myvalues,$section_name,$bShowCustom,$aChoices,$disabled)
    {
        $sListRootName = $section_name . '_'.$vector.'_';
        //$sListboxName  = $sListRootName.'id';
        $value_itemname = $sListRootName.'customtx';
        $aStatesEntry = NULL;
        $sInlineName = 'inline_'.$vector;
        $root[$section_name.'_fieldset_col1'][$sListRootName . '_inputmode'] = array(
            '#type'  => 'hidden',   //Needed at database update time to know what control to read!
        );
        $root[$section_name.'_fieldset_col2'][$sInlineName] = array(
            '#type'       => 'fieldset',
            '#attributes' => array('class' => array('container-inline')),
            '#disabled' => $disabled,
        );
        if(!isset($myvalues[$value_itemname]))
        {
            $mydefault_value = NULL;
        } else {
            $mydefault_value = $myvalues[$value_itemname];
        }
        $element = FormHelper::createCustomSelectPanel($section_name, $sListRootName, $aChoices, $disabled
                        , $aStatesEntry
                        , $myvalues
                        , $bShowCustom, $mydefault_value);
        $root[$section_name.'_fieldset_col2'][$sInlineName]['panel'] =  $element;     
    }
    
    /**
     * Return panel with radios and custom value selection controls
     * @param type $supportEditMode TODO REMOVE THIS 
     * @param array $aCustomOverride 'oral=>'customtx' and 'iv'=>'customtx'
     * @return associative array of controls
     */
    function getSectionCheckboxType($section_name, $titleoverride
            , $aEntericChoices, $aIVChoices, &$form_state, $disabled, $myvalues
            , $containerstates, $supportEditMode=TRUE
            , $aCustomOverride=NULL
            , $shownow=TRUE
            , $req_ack=FALSE
            , $requirevalue=TRUE)
    {
        if($aCustomOverride == NULL)
        {
            $aCustomOverride = array();
        }
        $bShowCustomEnteric = (isset($aCustomOverride['enteric']));
        $bShowCustomIV = (isset($aCustomOverride['iv']));
        $bLockedReadonly = $disabled && !$supportEditMode;
        $checkboxname= $section_name . '_cd';
        if($requirevalue && !$disabled)
        {
            $cleantitleoverride = FormHelper::getTitleAsRequiredField($titleoverride,$disabled);
        } else {
            $cleantitleoverride = FormHelper::getTitleAsUnrequiredField($titleoverride,$disabled);
        }
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $cleantitleoverride,
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        if($containerstates != null)
        {
            //$root['#states'] = $containerstates;
        }
        //Always declare as collapsible otherwise Drupal framework does NOT load javascript support
        $root['#collapsible'] = TRUE;
        if(!$shownow)
        {
            $root['#collapsed'] = TRUE;
            if($disabled)
            {
                //Never show it.
                $root['#attributes']['style'] = array('display:none');
            }
        } else {
            $root['#collapsible'] = TRUE;
            $root['#collapsed'] = FALSE;
        }

        $root[$section_name.'_fieldset_col1'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name.'_fieldset_col2'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name.'_fieldset_col3'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name.'_fieldset_row2'] = array(
            '#type' => 'fieldset',
        );
        $options                              = array(
            'none' => t('None'),
            'enteric' => t('Enteric'),
            'iv'   => t('IV')
        );
        $default_values = NULL;
        if(is_array($myvalues[$checkboxname]) 
                && count($myvalues[$checkboxname]) > 0)
        {
            $default_values = $myvalues[$checkboxname];
        } else {
            //$default_values = array('none'=>'none');
        }
        $root[$section_name.'_fieldset_col1'][$checkboxname] = array(
            '#type'          => 'checkboxes',
            '#options'       => $options,
            '#attributes' => $bLockedReadonly ? array() : array('onchange' 
                => 'notDefaultValuesInSectionAndSetCheckboxes("'.$section_name.'",this)'),
            '#disabled' => $disabled,
        );
        if ($default_values !== NULL)
        {
            $root[$section_name.'_fieldset_col1'][$checkboxname]['#default_value'] = $default_values;
        }
        $root[$section_name.'_fieldset_col2'][$section_name . '_markup1'] = array(
            '#markup' => '<div class="v-spacer-select">&nbsp;</div>',
        );

        //Create the two subsections.
        $this->getVectorBasedSubSectionMarkup($root,'enteric',$myvalues,$section_name,$bShowCustomEnteric,$aEntericChoices,$disabled);
        $this->getVectorBasedSubSectionMarkup($root,'iv',$myvalues,$section_name,$bShowCustomIV,$aIVChoices,$disabled);
        
        //Create the acknowledgement control
        $defaultvalue = isset($myvalues['DefaultValues'][$section_name]) ? $myvalues['DefaultValues'][$section_name] : NULL;
        $root[$section_name.'_fieldset_row2']['showconditionally'] 
                = $this->getDefaultValueControls($section_name, $disabled, $defaultvalue, $req_ack);
        //Always show this in each section that can have default values!
        if(!$disabled)
        {
            $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                '#markup' => "\n"
                . '<div class="reset-values-button-container" name="reset-section-values">'
                . '<a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default template values will be restored">'
                . 'RESET'
                . '</a>'
                . '</div>', 
                '#disabled' => $disabled,
            );
        }

        $form['main_fieldset_left'][$section_name . '_fieldset'] = &$root;
        return $form;
    }    

    
    function getYesNoRadioTypeSection($section_name, $titleoverride
            , $disabled
            , $myvalues
            , $containerstates
            , $supportEditMode=TRUE
            , $isrequired=FALSE)
    {

        $radioname = $section_name.'_cd';
        if($titleoverride == NULL)
        {
            $titleoverride = t($section_name);
        }
        if(!$disabled && $isrequired)
        {
            //$titleoverride = $section_name.' <span class="raptor-user-input-required">*</span>';
            $titleoverride = FormHelper::getTitleAsRequiredField($titleoverride,$disabled);
        } else {
            $titleoverride = FormHelper::getTitleAsUnrequiredField($titleoverride,$disabled);
        }
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $titleoverride,
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        if($containerstates != null)
        {
            $root['#states'] = $containerstates;
        }

        $options          = array(
            'unknown' => t('Unknown'),
            'no'      => t('No'),
            'yes'     => t('Yes'),
        );
        $root[$radioname] = array(
            '#type'    => 'radios',
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#options' => $options,
        );
        if(isset($myvalues[$radioname]))
        {
            $root[$radioname]['#default_value'] = $myvalues[$radioname];
        }

        $form['main_fieldset_left'][$section_name . '_fieldset'] = &$root;
        return $form;
    }
    
    
    
    /**
     * Return panel with radios and custom value selection controls
     * @param type $supportEditMode TODO REMOVE THIS 
     * @param array $aCustomOverride 'oral=>'customtx' and 'iv'=>'customtx'
     * @return associative array of controls
     */
    function getSectionRadioType($section_name, $titleoverride
            , $aOralChoices
            , $aIVChoices, &$form_state, $disabled, $myvalues
            , $containerstates
            , $supportEditMode=TRUE, $aCustomOverride=NULL
            , $shownow=TRUE
            , $req_ack=FALSE
            , $requirevalue=TRUE)
    {
        if($aCustomOverride == NULL)
        {
            $aCustomOverride = array();
        }
        $bShowCustomOral = (isset($aCustomOverride['oral']));
        $bShowCustomIV = (isset($aCustomOverride['iv']));
        $bLockedReadonly = $disabled && !$supportEditMode;
        if($requirevalue && !$disabled)
        {
            $cleantitleoverride = FormHelper::getTitleAsRequiredField($titleoverride,$disabled);
        } else {
            $cleantitleoverride = FormHelper::getTitleAsUnrequiredField($titleoverride,$disabled);
        }
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $cleantitleoverride,
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        if($containerstates != null)
        {
            //TODO re-enable this>>>>> $root['#states'] = $containerstates;
        }
        //Always declare as collapsible otherwise Drupal framework does NOT load javascript support
        $root['#collapsible'] = TRUE;
        if(!$shownow)
        {
            $root['#collapsed'] = TRUE;
            if($disabled)
            {
                //Never show it.
                $root['#attributes']['style'] = array('display:none');
            }
        } else {
            $root['#collapsible'] = TRUE;
            $root['#collapsed'] = FALSE;
        }

        $col1fieldsetname = $section_name.'_fieldset_col1';
        $col2fieldsetname = $section_name.'_fieldset_col2';
        $col3fieldsetname = $section_name.'_fieldset_col3';
        $row2fieldsetname = $section_name.'_fieldset_row2';
        $markup1name = $section_name . '_markup1';
        $root[$col1fieldsetname] = array(
            '#type' => 'fieldset',
        );
        $root[$col2fieldsetname] = array(
            '#type' => 'fieldset',
        );
        $root[$col3fieldsetname] = array(
            '#type' => 'fieldset',
        );
        $root[$row2fieldsetname] = array(
            '#type' => 'fieldset',
        );
        $radio_nm = $section_name . '_radio_cd';
        if(!isset($myvalues[$radio_nm]) || $myvalues[$radio_nm] == '')
        {
            //Hardwired default value.
            $defaultoptionvalue = NULL; //'none';
        } else {
            $defaultoptionvalue = $myvalues[$radio_nm];
        }
        $options                 = array(
            'none' => t('None'),
            'oral' => t('Oral'),
            'iv'   => t('IV'),
        );
        $root[$col1fieldsetname][$radio_nm] = array(
            '#type'    => 'radios',
            '#options' => $options,
            '#attributes' => $bLockedReadonly ? array() : array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")'),
            '#disabled' => $disabled,
        );
        if($defaultoptionvalue !== NULL)
        {
            $root[$col1fieldsetname][$radio_nm]['#default_value'] = $defaultoptionvalue;
        }
        $root[$col2fieldsetname][$markup1name] = array(
            '#markup' => '<div class="v-spacer-select">&nbsp;</div>',
        );

        //Create the two subsections.
        $this->getVectorBasedSubSectionMarkup($root,'oral',$myvalues,$section_name,$bShowCustomOral,$aOralChoices,$disabled);
        $this->getVectorBasedSubSectionMarkup($root,'iv',$myvalues,$section_name,$bShowCustomIV,$aIVChoices,$disabled);
        
        
        //Create the acknowledgement control
        $defaultoptionvalue = isset($myvalues['DefaultValues'][$section_name]) ? $myvalues['DefaultValues'][$section_name] : NULL;
        $root[$row2fieldsetname]['showconditionally'] 
                = $this->getDefaultValueControls($section_name, $disabled, $defaultoptionvalue, $req_ack);
        //Always show this in each section that can have default values!
        if(!$disabled)
        {
            $root[$col3fieldsetname]['reset_'.$section_name] = array(
                '#markup' => "\n".'<div class="reset-values-button-container" name="reset-section-values">'
                . '<a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default template values will be restored">'
                . 'RESET'
                . '</a>'
                . '</div>', 
                '#disabled' => $disabled,
            );
        }

        $form['main_fieldset_left'][$section_name . '_fieldset'] = &$root;
        return $form;
    }

    function getOverallSectionRadioType(&$form_state
            , $section_name
            , $titleoverride
            , $disabled
            , $myvalues
            , $containerstates=NULL
            , $supportEditMode=TRUE
            , $shownow=TRUE
            , $req_ack=FALSE
            , $modality_filter=NULL)
    {
        
        if($modality_filter == NULL || !is_array($modality_filter))
        {
            $modality_filter = array();
        }
        
        if($titleoverride == NULL)
        {
            $titleoverride = $section_name; 
        }
        $radioname = $section_name.'_radio_cd';
        $oChoices      = new \raptor\raptor_datalayer_Choices();    
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        
        //Updated 20150308
        $oral_tx = isset($myvalues[$section_name.'_oral_customtx']) ? $myvalues[$section_name.'_oral_customtx'] : '';
        $iv_tx = isset($myvalues[$section_name.'_iv_customtx']) ? $myvalues[$section_name.'_iv_customtx'] : '';
        if($section_name == 'hydration')
        {
            $aOralChoices  = $oChoices->getOralHydrationData($oral_tx, $bFoundInList, $modality_filter);
            $aIVChoices    = $oChoices->getIVHydrationData($iv_tx, $bFoundInList, $modality_filter);
        } else 
        if($section_name == 'sedation')
        {
            $aOralChoices  = $oChoices->getOralSedationData($oral_tx, $bFoundInList, $modality_filter);
            $aIVChoices    = $oChoices->getIVSedationData($iv_tx, $bFoundInList, $modality_filter);
        } else {
            throw new \Exception("Did not recognize SectionRadioType called [$section_name]!");
        }
        if($oral_tx > '')
        {
            $myvalues[$radioname] = 'oral';
            if(!$bFoundInList)
            {
                $aControlOverrides['oral'] = $oral_tx;
            }
        }

        if($iv_tx > '')
        {
            $myvalues[$radioname] = 'iv';
            if(!$bFoundInList)
            {
                $aControlOverrides['iv'] = $iv_tx;
            }
        }
        return $this->getSectionRadioType($section_name, $titleoverride
                , $aOralChoices, $aIVChoices, $form_state
                , $disabled, $myvalues, $containerstates
                , $supportEditMode, $aControlOverrides
                , $shownow
                , $req_ack);
    }
    
    /**
     * Return TRUE if we have hydration data.
     */
    function hasHydrationValues($myvalues)
    {
        $b1 = isset($myvalues['hydration_oral_customtx']) && $myvalues['hydration_oral_customtx'] > '';
        $b2 = isset($myvalues['hydration_iv_customtx']) && $myvalues['hydration_iv_customtx'] > '';
        return $b1 || $b2;
    }
    
    /**
     * Return TRUE if we have sedation data.
     */
    function hasSedationValues($myvalues)
    {
        $b1 = isset($myvalues['sedation_oral_customtx']) && $myvalues['sedation_oral_customtx'] > '';
        $b2 = isset($myvalues['sedation_iv_customtx']) && $myvalues['sedation_iv_customtx'] > '';
        return $b1 || $b2;
    }

    /**
     * Return TRUE if we have contrast data.
     */
    function hasContrastValues($myvalues, $form_state=NULL)
    {
        
        $sectionname = 'contrast';
        $b1 = isset($myvalues[$sectionname.'_enteric_customtx']) && $myvalues[$sectionname.'_enteric_customtx'] > '';
        $b2 = isset($myvalues[$sectionname.'_iv_customtx']) && $myvalues[$sectionname.'_iv_customtx'] > '';
        $foundvalues = $b1 || $b2;
        if(!$foundvalues && $form_state != NULL)
        {
            $b1 = isset($form_state['input'][$sectionname.'_enteric_customtx']) && $form_state['input'][$sectionname.'_enteric_customtx'] > '';
            $b2 = isset($form_state['input'][$sectionname.'_iv_customtx']) && $form_state['input'][$sectionname.'_iv_customtx'] > '';
            $foundvalues = $b1 || $b2;
        }
        
        //die('LOOK>>>>'.$sectionname.'='.$foundvalues.'>>>'.print_r($form_state['input'],TRUE));
        
        return $foundvalues;
    }

    /**
     * Return TRUE if we have radioisotope data.
     */
    function hasRadioisotopeValues($myvalues, $form_state=NULL)
    {
        $sectionname = 'radioisotope';
        $b1 = isset($myvalues[$sectionname.'_enteric_customtx']) && $myvalues[$sectionname.'_enteric_customtx'] > '';
        $b2 = isset($myvalues[$sectionname.'_iv_customtx']) && $myvalues[$sectionname.'_iv_customtx'] > '';
        $foundvalues = $b1 || $b2;
        if(!$foundvalues && $form_state != NULL)
        {
            $b1 = isset($form_state['input'][$sectionname.'_enteric_customtx']) && $form_state['input'][$sectionname.'_enteric_customtx'] > '';
            $b2 = isset($form_state['input'][$sectionname.'_iv_customtx']) && $form_state['input'][$sectionname.'_iv_customtx'] > '';
            $foundvalues = $b1 || $b2;
        }
        return $foundvalues;
    }
    
    function getOverallSectionCheckboxType(&$form_state
            , $section_name, $titleoverride
            , $disabled
            , $myvalues
            , $containerstates=NULL
            , $supportEditMode=TRUE
            , $shownow=TRUE
            , $req_ack=FALSE 
            , $modality_filter=NULL)
    {
        
        if($modality_filter == NULL || !is_array($modality_filter))
        {
            $modality_filter = array();
        }
        
        if($titleoverride == NULL)
        {
            $titleoverride = $section_name; 
        }
        $oChoices      = new \raptor\raptor_datalayer_Choices();
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        
        $enteric_tx = isset($myvalues[$section_name.'_enteric_customtx']) 
                ? $myvalues[$section_name.'_enteric_customtx'] : '';
        $iv_tx = isset($myvalues[$section_name.'_iv_customtx']) 
                ? $myvalues[$section_name.'_iv_customtx'] : '';
        
        if($section_name == 'contrast')
        {
            $aEntericChoices  = $oChoices
                    ->getEntericContrastData($enteric_tx, $bFoundInList, $modality_filter);
            $aIVChoices    = $oChoices->getIVContrastData($iv_tx, $bFoundInList, $modality_filter);
        } else 
        if($section_name == 'radioisotope')
        {
            $aEntericChoices  = $oChoices
                    ->getEntericRadioisotopeData($enteric_tx, $bFoundInList, $modality_filter);
            $aIVChoices    = $oChoices->getIVRadioisotopeData($iv_tx, $bFoundInList, $modality_filter);
        } else {
            throw new \Exception("Did not recognize SectionCheckboxType called [$section_name]!");
        }

        if($enteric_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['enteric'] = $enteric_tx;
        }
        if($iv_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['iv'] = $iv_tx;
        }
        
        return $this->getSectionCheckboxType($section_name, $titleoverride
                , $aEntericChoices
                , $aIVChoices
                , $form_state, $disabled, $myvalues, $containerstates
                , $supportEditMode
                , $aControlOverrides
                , $shownow
                , $req_ack);
    }
    
    function getYesNoResetRadioTypeSection($section_name, $titleoverride
            , $disabled
            , $myvalues
            , $containerstates
            , $supportEditMode=TRUE
            , $shownow=TRUE
            , $req_ack=FALSE
            , $requirevalue=TRUE)
    {
        $radioname = $section_name.'_radio_cd';
        if($titleoverride == null)
        {
            $titleoverride = $section_name;
        }
        if($requirevalue && !$disabled)
        {
            $cleantitleoverride = FormHelper::getTitleAsRequiredField($titleoverride,$disabled);
        } else {
            $cleantitleoverride = FormHelper::getTitleAsUnrequiredField($titleoverride,$disabled);
        }
        
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $cleantitleoverride,
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        if($containerstates != null)
        {
            $root['#states'] = $containerstates;
        }

        $root[$section_name.'_fieldset_col1'] = array(
            '#type' => 'fieldset',
        );
        //There is no "COL2" in this one by design.
        $root[$section_name.'_fieldset_col3'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name.'_fieldset_row2'] = array(
            '#type' => 'fieldset',
        );
        
        $options                                           = array(
            'unknown' => t('Unknown'),
            'no'      => t('No'),
            'yes'     => t('Yes'),
        );
        $default_value = isset($myvalues[$radioname]) ? $myvalues[$radioname] : 'unknown';
        
        $root[$section_name.'_fieldset_col1'][$radioname] = array(
            '#type'    => 'radios',
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#options' => $options,
            '#title' => NULL,
        );
        if($default_value !== NULL)
        {
            $root[$section_name.'_fieldset_col1'][$radioname]['#default_value'] = $default_value;
        }
        $root[$section_name.'_fieldset_col1'][$radioname]['#attributes'] 
                = array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")');

        $defaultvalue = isset($myvalues['DefaultValues'][$section_name]) ? $myvalues['DefaultValues'][$section_name] : NULL;
        $root[$section_name.'_fieldset_row2']['showconditionally'] 
                = $this->getDefaultValueControls($section_name, $disabled, $defaultvalue, $req_ack);
        if(!$disabled)
        {
            //Always show this in each section that can have default values!
            $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                '#markup' => "\n"
                    . '<div class="reset-values-button-container" name="reset-section-values">'
                    . '<a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default template values will be restored">RESET</a></div>', 
                '#disabled' => $disabled,
            );
        }
        
        $form['main_fieldset_left'][$section_name.'_fieldset'] = &$root;
        return $form;
    }
    

    function getNotesSectionMarkup($section_name, $titleoverride
            , $disabled
            , $myvalues
            , $supportEditMode=TRUE
            , $req_ack=FALSE
            , $requirevalue=FALSE)
    {
        //$section_name = 'protocolnotes';
        ///$titleoverride = 'Protocol Notes';
        $textfieldname = $section_name.'_tx';

        if($requirevalue && !$disabled)
        {
            $cleantitleoverride = FormHelper::getTitleAsRequiredField($titleoverride,$disabled);
        } else {
            $cleantitleoverride = FormHelper::getTitleAsUnrequiredField($titleoverride,$disabled);
        }
        
        $root                                = array(
            '#type'     => 'fieldset',
            '#title'    => $cleantitleoverride,
            '#attributes' => array(
                'class' => array(
                    'data-entry2-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $root[$section_name . '_fieldset_col1'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name . '_fieldset_col3'] = array(
            '#type' => 'fieldset',
        );
        $root[$section_name . '_fieldset_row2'] = array(
            '#type' => 'fieldset',
        );
        if (isset($myvalues[$textfieldname]))
        {
            $protocolnotes_tx = $myvalues[$textfieldname];
        }
        else
        {
            $protocolnotes_tx = '';
        }
        if($disabled && $protocolnotes_tx == '')    //20140714
        {
            //Disabled and empty, dont bother showing any control.
            $root = array();
        } else {
            if ($disabled)
            {
                #A hack to work-around CSS issue on coloring!
                $root[$section_name.'_fieldset_col1']['disabled_'.$textfieldname] = array(
                    '#type'          => 'textarea',
                    '#title'         => $cleantitleoverride,
                    '#disabled'      => $disabled,
                    '#default_value' => $protocolnotes_tx,
                );
            }
            else
            {
                //Create the boilerplate insertion buttons
                $nBoilerplate     = 0;
                $aBoilerplate     = ListUtils::getCategorizedLists("boilerplate-protocolnotes.cfg");
                $sBoilerplateHTML = "<div id='boilerplate'><ul>";
                foreach ($aBoilerplate as $sCategory => $aContent)
                {
                    $sBoilerplateHTML.="<li class='category'>$sCategory<ul>";
                    foreach ($aContent as $sName => $aItem)
                    {
                        $nBoilerplate+=1;
                        $sTitle = $aItem[0];
                        $sBoilerplateHTML.="<li><a title='$sTitle' onclick='notDefaultValuesInSection(".'"'.$section_name.'"'.")'"
                                . " href='javascript:app2textareaByName(" . '"'. $textfieldname .'"' . "," . '"' . $aItem[0] . '"' 
                                . ")'>$sName</a>";
                    }
                    $sBoilerplateHTML.="</ul>";
                }
                $sBoilerplateHTML.="</ul></div>";
                if ($nBoilerplate > 0)
                {
                    $root[$section_name.'_fieldset_col1']['boilerplate_fieldset']                = array(
                        '#type'  => 'fieldset',
                        '#title' => FormHelper::getTitleAsUnrequiredField($titleoverride.' Boilerplate Text Helpers'),
                    );
                    $root[$section_name.'_fieldset_col1']['boilerplate_fieldset']['boilerplate'] = array(
                        '#markup' => $sBoilerplateHTML,
                    );
                }

                //Create the note area.
                $myclassname = $disabled ? '' : 'raptor-active-field';
                $root[$section_name.'_fieldset_col1'][$textfieldname] = array(
                    '#type'          => 'textarea',
                    '#title'         => '<span class="fieldset-legend '.$myclassname.'">'.t('Protocol Notes').'</span>',
                    '#disabled'      => $disabled,
                    '#default_value' => $protocolnotes_tx,
                    '#attributes' => array('oninput' => 'notDefaultValuesInSection("'.$section_name.'")'),
                );
                $defaultvalue = isset($myvalues['DefaultValues'][$section_name]) ? $myvalues['DefaultValues'][$section_name] : NULL;
                $root[$section_name.'_fieldset_row2']['showconditionally'] 
                        = $this->getDefaultValueControls($section_name, $disabled, $defaultvalue, $req_ack);
                if(!$disabled)
                {
                    //Always show this in each section that can have default values!
                    $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                        '#markup' => "\n"
                        .'<div class="reset-values-button-container" name="reset-section-values"><a href="javascript:setDefaultValuesInSection('
                        ."'".$section_name."',getTemplateDataJSON()"
                        .')" title="The default values for ' . $section_name . ' will be restored">RESET</a></div>', 
                        '#disabled' => $disabled,
                    );
                }
            }
        }
        return $root;
    }

    static function getFirstAvailableValue($myvalues,$aNames,$sDefaultValue)
    {
        foreach($aNames as $sName)
        {
            if(isset($myvalues[$sName]))
            {
                return $myvalues[$sName];
            }
        }
        //Did not find the value already set.
        return $sDefaultValue;
    }
    
    function getConsentReceivedBlock(&$form_state, $disabled, $myvalues, $titleoverride=NULL, $containerstates=NULL)
    {
        if($titleoverride !== NULL)
        {
            $title = FormHelper::getTitleAsRequiredField($titleoverride, $disabled);
        } else {
            $title = FormHelper::getTitleAsRequiredField('Consent Received', $disabled);
        }
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => $title,
            '#description' => t('If consent was required, was it received?  If not required, mark as Not Applicable.'),
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#disabled' => $disabled,
        );
        if($containerstates != null)
        {
            $root['#states'] = $containerstates;
        }

        $options                                     = array(
            'no'    => t('No'),
            'yes'   => t('Yes'),
            'NA'    => t('Not Applicable'),
        );
        $root['exam_consent_received_kw'] = array(
            '#type'    => 'radios',
            '#options' => $options,
            '#title' => '',
        );
        if (isset($myvalues['exam_consent_received_kw']))
        {
            $root['exam_consent_received_kw']['#default_value'] = $myvalues['exam_consent_received_kw'];
        } else {
            //Mark as Not Applicable if protocol says consent is not required.
            if(isset($myvalues['consentreq_radio_cd']) && $myvalues['consentreq_radio_cd'] == 'no')
            {
                $root['exam_consent_received_kw']['#default_value'] = 'NA';
            }
        }

        return $root;
    }
    
    /**
     * The EXAM part of the form.
     */
    function getExamDataEntryFields(&$form_state, $disabled, $myvalues, $protocolValues)
    {
        
        $modality_abbr = $protocolValues['modality_abbr'];
        $protocol_shortname = $protocolValues['protocol_shortname'];
        
        // information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => FormHelper::getTitleAsUnrequiredField('Examination Notes'),
            '#attributes' => array(
                'class' => array(
                    'exam-data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $sFieldsetKeyName = 'exam_contrast_fieldset';
        $root['exam_contrast_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => FormHelper::getTitleAsUnrequiredField('Contrast Administered'),
            '#description' => t('Provide the actual contrast administered during the exam'),
        );
        if($this->hasContrastValues($myvalues))
        {
            $root[$sFieldsetKeyName]['#collapsible'] = FALSE;
            $root[$sFieldsetKeyName]['#collapsed'] = FALSE;
        } else {
            $root[$sFieldsetKeyName]['#collapsible'] = TRUE;
            $root[$sFieldsetKeyName]['#collapsed'] = TRUE;
        }
        $sName = 'exam_contrast_enteric_tx';
        $aNames = array($sName,'contrast_enteric_customtx','contrast_enteric_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_contrast_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('Enteric Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        $sName = 'exam_contrast_iv_tx';
        $aNames = array($sName,'contrast_iv_customtx','contrast_iv_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_contrast_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );

        $sFieldsetKeyName = 'exam_hydration_fieldset';
        $root[$sFieldsetKeyName] = array(
            '#type'  => 'fieldset',
            '#title' => FormHelper::getTitleAsUnrequiredField('Hydration Administered', $disabled),
            '#description' => t('Provide the actual hydration administered during the exam'),
        );
        if($this->hasHydrationValues($myvalues))
        {
            $root[$sFieldsetKeyName]['#collapsible'] = FALSE;
            $root[$sFieldsetKeyName]['#collapsed'] = FALSE;
        } else {
            $root[$sFieldsetKeyName]['#collapsible'] = TRUE;
            $root[$sFieldsetKeyName]['#collapsed'] = TRUE;
        }
        $sName = 'exam_hydration_oral_tx';
        $aNames = array($sName,'hydration_oral_customtx','hydration_oral_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_hydration_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        $sName = 'exam_hydration_iv_tx';
        $aNames = array($sName,'hydration_iv_customtx','hydration_iv_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_hydration_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        
        $sFieldsetKeyName = 'exam_sedation_fieldset';
        $root[$sFieldsetKeyName] = array(
            '#type'  => 'fieldset',
            '#title' => FormHelper::getTitleAsUnrequiredField('Sedation Administered', $disabled),
            '#description' => t('Provide the actual sedation administered during the exam'),
        );
        if($this->hasSedationValues($myvalues))
        {
            $root[$sFieldsetKeyName]['#collapsible'] = FALSE;
            $root[$sFieldsetKeyName]['#collapsed'] = FALSE;
        } else {
            $root[$sFieldsetKeyName]['#collapsible'] = TRUE;
            $root[$sFieldsetKeyName]['#collapsed'] = TRUE;
        }
        $sName = 'exam_sedation_oral_tx';
        $aNames = array($sName,'sedation_oral_customtx','sedation_oral_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_sedation_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        $sName = 'exam_sedation_iv_tx';
        $aNames = array($sName,'sedation_iv_customtx','sedation_iv_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root['exam_sedation_fieldset'][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );

        $bGivenRadioisotope = $this->hasRadioisotopeValues($myvalues);
        $bMachineEmitsRadiation = $modality_abbr == 'CT'
                    || $modality_abbr == 'FL'
                    || $modality_abbr == 'NM';
        
        $sFieldsetKeyName = 'exam_radioisotope_fieldset';
        $dose_source_cd = 'R';
        if($modality_abbr == 'NM')
        {
            $nmareatitle = FormHelper::getTitleAsUnrequiredField('Radionuclide Administered', $disabled);
        } else {
            $nmareatitle = FormHelper::getTitleAsUnrequiredField('Radionuclide Administered (if any)', $disabled);
        }
        $root[$sFieldsetKeyName] = array(
            '#type'  => 'fieldset',
            '#title' => $nmareatitle,
            '#description' => t('Provide the actual radionuclide administered during the exam'),
        );
        if($bGivenRadioisotope)
        {
            $root[$sFieldsetKeyName]['#collapsible'] = FALSE;
            $root[$sFieldsetKeyName]['#collapsed'] = FALSE;
        } else {
            $root[$sFieldsetKeyName]['#collapsible'] = TRUE;
            $root[$sFieldsetKeyName]['#collapsed'] = TRUE;
        }
        
        $sName = 'exam_radioisotope_enteric_tx';
        $aNames = array($sName,'radioisotope_enteric_customtx','radioisotope_enteric_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('Enteric Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        $sName = 'exam_radioisotope_iv_tx';
        $aNames = array($sName,'radioisotope_iv_customtx','radioisotope_iv_id');
        $default_value = ProtocolInfoUtility::getFirstAvailableValue($myvalues, $aNames, '');
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
            '#maxlength' => 100,
            '#default_value' => $default_value,
        );
        $tmpuom = RadiationDoseHelper::getDefaultUOMForDoseSource('R'); 
        if(isset($myvalues['exam_radioisotope_radiation_dose_tx']) 
                || isset($myvalues['exam_radioisotope_radiation_dose_uom_tx']))
        {
            //Use this value if we find it.
            $default_dose_value = isset($myvalues['exam_radioisotope_radiation_dose_tx']) ? $myvalues['exam_radioisotope_radiation_dose_tx'] : '';
            $default_dose_uom = isset($myvalues['exam_radioisotope_radiation_dose_uom_tx']) ? $myvalues['exam_radioisotope_radiation_dose_uom_tx'] : $tmpuom;
            $default_dose_value_type_cd = isset($myvalues['exam_radioisotope_radiation_dose_type_cd']) ? $myvalues['exam_radioisotope_radiation_dose_type_cd'] : '';
        } else {
            //Derive a default from the dose map.
            $default_dose_value = NULL;
            $default_dose_uom = NULL;
            $default_dose_value_type_cd = NULL;
            if(isset($myvalues['exam_radioisotope_radiation_dose_map']))
            {
                $dose_map = $myvalues['exam_radioisotope_radiation_dose_map'];
                $a = $this->getDefaultRadiationDoseValuesForForm($dose_map);
                $default_dose_uom = $a['uom'];
                $default_dose_value = $a['dose'];
                $default_dose_value_type_cd = $a['dose_type_cd'];
            }
        }
        if($default_dose_value == NULL)
        {
            $default_dose_value = '';
        }
        $sName = 'exam_radioisotope_radiation_dose_tx';
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => FormHelper::getTitleAsUnrequiredField('Radiation Dose Values'),
            '#size' => 100,
            '#maxlength' => 256,
            '#default_value' => $default_dose_value,
            '#description' => t('Provide the radionuclide radiation exposure during the exam.  If there are multiple doses, delimit each dose with a comma.'),
        );
        if($default_dose_uom == NULL)
        {
            $default_dose_uom = 'mGy';
        }
        $sName = 'exam_radioisotope_radiation_dose_uom_tx';
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t('Radiation Dose Units'),
            '#size' => 8,
            '#maxlength' => 32,
            '#default_value' => $default_dose_uom,
            '#description' => t('Provide the radionuclide radiation unit of measure for the dose(s) recorded here'),
        );
        $aDoseTypeOptions = array();
        $aDoseTypeOptions['A'] = t('Actual');
        $aDoseTypeOptions['E'] = t('Estimate');
	$sDoseTypeRadiosTitle = 'Dose Value Type';
        $sName = 'exam_radioisotope_radiation_dose_type_cd';
        $root[$sFieldsetKeyName][$sName] = array(
            '#type' => 'radios',
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#options' => $aDoseTypeOptions,
            '#disabled' => $disabled,
            '#title' => t($sDoseTypeRadiosTitle),   //Important to have title otherwise required symbol not shown! 
        );        
        if($default_dose_value_type_cd != '')
        {
            $root[$sFieldsetKeyName][$sName]['#default_value'] = $default_dose_value_type_cd;
        }
        
        
        //Get the inputs for device dose
        if($bMachineEmitsRadiation)
        {
            if($modality_abbr == 'CT')
            {
                //Specifically a CT device
                $littlename = 'ctdivol';
                $dose_source_cd = 'C';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_ct_dose_fieldset'
                        , 'Machine-Produced Radiation Dose CT'
                        , 'CTDIvol', 'CTDIvol'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd');
                $littlename = 'dlp';
                $dose_source_cd = 'D';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_ct_dose_fieldset'
                        , 'Machine-Produced Radiation Dose CT'
                        , 'DLP', 'DLP'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd');
            } else if($modality_abbr == 'FL') {
                $littlename = 'fluoroQ';
                $dose_source_cd = 'Q';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_fluoro_dose_fieldset'
                        , 'Machine-Produced Radiation Dose Fluoroscopy'
                        , 'Air Kerma', 'Air Kerma'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd');
                $littlename = 'fluoroS';
                $dose_source_cd = 'S';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_fluoro_dose_fieldset'
                        , 'Machine-Produced Radiation Dose Fluoroscopy'
                        , 'DAP', 'DAP'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd');
                $littlename = 'fluoroT';
                $dose_source_cd = 'T';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_fluoro_dose_fieldset'
                        , 'Machine-Produced Radiation Dose Fluoroscopy Time'
                        , 'Fluoroscopy Time', 'Fluoroscopy Time'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd');
                $littlename = 'fluoroH';
                $dose_source_cd = 'H';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_fluoro_dose_fieldset'
                        , 'Machine-Produced Radiation Dose Fluoroscopy Frame Rate'
                        , 'Fluoroscopy Frame Rate', 'Fluoroscopy Frame Rate'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_'.$littlename.'_radiation_dose_map'
                        , 'exam_'.$littlename.'_radiation_dose_tx'
                        , 'exam_'.$littlename.'_radiation_dose_uom_tx'
                        , 'exam_'.$littlename.'_radiation_dose_type_cd'
                        , TRUE, FALSE, FALSE);
            } else {
                //Other kind of device
                $dose_source_cd = 'E';
                $this->addFormDeviceRadiationDoseGroup($root, $myvalues, $disabled
                        , 'exam_other_dose_fieldset'
                        , 'Machine-Produced Radiation Dose Other'
                        , 'Other', 'other'
                        , RadiationDoseHelper::getDefaultUOMForDoseSource($dose_source_cd) 
                        , 'exam_other_radiation_dose_map'
                        , 'exam_other_radiation_dose_tx'
                        , 'exam_other_radiation_dose_uom_tx'
                        , 'exam_other_radiation_dose_type_cd');
            }
        }
        $root['exam_consent_received_fieldset'] = $this->getConsentReceivedBlock($form_state, $disabled, $myvalues);
        
        $sName = 'exam_notes_tx';
        $default_value = isset($myvalues[$sName]) ? $myvalues[$sName] : '';
        if ($disabled)
        {
            if(trim($default_value) > '')
            {
                //A hack to work-around CSS issue on coloring!
                $root['exam_summary'][$sName] = array(
                    '#type'     => 'textarea',
                    '#title'    => t('Examination Comments'),
                    '#default_value' => $default_value,
                    '#disabled' => $disabled,
                );
            }
        }
        else
        {
            $root['exam_summary'][$sName] = array(
                '#type'     => 'textarea',
                '#rows'     => 20,
                '#title'    => t('Examination Comments'),
                '#maxlength' => 1024,
                '#default_value' => $default_value,
                '#disabled' => $disabled,
            );
        }
        return $root;
    }
    
    /**
     * Add equipment dose radiation capture to the form markup array
     */
    function addFormDeviceRadiationDoseGroup(
              &$root
            , $myvalues
            , $disabled
            , $sFieldsetKeyName
            , $sectiontitle
            , $itemprefix_distinction
            , $inlinedistinction
            , $default_uom
            , $map_valuename
            , $dose_valuesname
            , $uom_valuename
            , $typecd_valuename
            , $is_collapsible=TRUE
            , $is_collapsed=FALSE
            , $allow_multiple_values=TRUE
            , $use_entirelycustomlabels=FALSE)
    {
        //Create the container
        if(!isset($root) || !array_key_exists($sFieldsetKeyName, $root))
        {
            $root[$sFieldsetKeyName] = array(
                '#type'  => 'fieldset',
                '#title' => FormHelper::getTitleAsUnrequiredField($sectiontitle, $disabled),   //'Equipment Radiation Dose Exposure'),
                '#collapsible' => $is_collapsible,
                '#collapsed' => $is_collapsed,
            );
        }
        if(isset($myvalues[$dose_valuesname]) || isset($myvalues[$uom_valuename]))
        {
            //Use this value if we find it.
            $default_dose_value = isset($myvalues[$dose_valuesname]) ? $myvalues[$dose_valuesname] : '';
            $default_dose_uom = isset($myvalues[$uom_valuename]) ? $myvalues[$uom_valuename] : '';
            $default_dose_value_type_cd 
                    = isset($myvalues[$typecd_valuename]) ? $myvalues[$typecd_valuename] : '';
        } else {
            //Derive a default from the dose map.
            $default_dose_value = NULL;
            $default_dose_uom = NULL;
            $default_dose_value_type_cd = NULL;
            if(isset($myvalues[$map_valuename]))
            {
                $dose_map = $myvalues[$map_valuename];
                $a = $this->getDefaultRadiationDoseValuesForForm($dose_map);
                $default_dose_uom = $a['uom'];
                $default_dose_value = $a['dose'];
                $default_dose_value_type_cd = $a['dose_type_cd'];
            }
        }
        
        if($default_dose_value == NULL)
        {
            $default_dose_value = '';
        }
        $sName = $dose_valuesname;
        if($allow_multiple_values)
        {
            $maindescription = 'Provide the actual '.$inlinedistinction.' machine-produced radiation exposure during the exam.  If there are multiple doses, delimit each dose with a comma.';
            $mainmaxlen = 256;
            $mainsize = 100;
            $maintitle = $itemprefix_distinction.' Radiation Dose Values';
        } else {
            $maindescription = 'Provide the single actual '.$inlinedistinction.' machine-produced radiation exposure during the exam.';
            $mainmaxlen = 20;
            $mainsize = 15;
            $maintitle = $itemprefix_distinction.' Radiation Dose Value';
        }
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t($maintitle),
            '#size' => $mainsize,
            '#maxlength' => $mainmaxlen,
            '#default_value' => $default_dose_value,
            '#description' => t($maindescription),
        );
        if($default_dose_uom == NULL)
        {
            $default_dose_uom = $default_uom;
        }
        $sName = $uom_valuename;
        $root[$sFieldsetKeyName][$sName] = array(
            '#type'  => 'textfield',
            '#title' => t($itemprefix_distinction.' Radiation Dose Units'),
            '#size' => 8,
            '#maxlength' => 32,
            '#default_value' => $default_dose_uom,
            '#description' => t('Provide the unit of measure for the '.$inlinedistinction.' equipment dose(s) recorded here'),
        );
        $aDoseTypeOptions = array();
        $aDoseTypeOptions['A'] = t('Actual');
        $aDoseTypeOptions['E'] = t('Estimate');
	$sDoseTypeRadiosTitle = 'Dose Value Type';
        $sName = $typecd_valuename;
        $root[$sFieldsetKeyName][$sName] = array(
            '#type' => 'radios',
            '#attributes' => array(
                'class' => array('container-inline'),
                ),
            '#options' => $aDoseTypeOptions,
            '#disabled' => $disabled,
            '#title' => t($sDoseTypeRadiosTitle),   //Important to have title otherwise required symbol not shown! 
        );        
        if($default_dose_value_type_cd != '')
        {
            $root[$sFieldsetKeyName][$sName]['#default_value'] = $default_dose_value_type_cd;
        }
    }

    /**
     * Construct the text fields for the form from the dose map array
     */
    function getDefaultRadiationDoseValuesForForm($dose_map)
    {                
        $uom_values = array();
        $dose_values = array();
        $dose_type_cd_values = array();
        if(!is_array($dose_map))
        {
            $default_dose_value = NULL;
            $default_dose_uom = NULL;
            $dose_type_cd = NULL;
        } else {
            foreach($dose_map as $uom=>$dose_records)
            {
                $uom_values[$uom] = $uom;
                foreach($dose_records as $dose_record)
                {
                    $dose = $dose_record['dose'];
                    $dose_values[] = $dose;
                    $type = $dose_record['dose_type_cd'];
                    $dose_type_cd_values[$type] = $type;
                }
            }
            if(count($uom_values)>0)
            {
                $default_dose_uom = implode('?', $uom_values);  //If we have multiple units add question mark!
            } else {
                $default_dose_uom = '';
            }
            if(count($dose_type_cd_values)>0)
            {
                $dose_type_cd = implode('?', $dose_type_cd_values);  //If we have multiple units add question mark!
            } else {
                $dose_type_cd = '';
            }
            if(count($dose_values)>0)
            {
                $default_dose_value = implode(',', $dose_values);   //List of values.
            } else {
                $default_dose_value = '';
            }
        }

        return array('dose'=>$default_dose_value, 'uom'=>$default_dose_uom
                , 'dose_type_cd'=>$dose_type_cd
                , 'dose_type_cd_values'=>$dose_type_cd_values);
    }    
    
    /**
     * The INTERPRETATION part of the form.
     */
    function getInterpretationDataEntryFields(&$form_state, $disabled, $myvalues)
    {
        // information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t('Interpretation Notes'),
            '#attributes' => array(
                'class' => array(
                    'interpretation-data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $sName = 'interpret_notes_tx';
        $default_value = isset($myvalues[$sName]) ? $myvalues[$sName] : '';
        $root['interpretation_summary'][$sName] = array(
            '#type'     => 'textarea',
            '#rows'     => 20,
            '#title'    => t('Interpretation Comments'),
            '#maxlength' => 1024,
            '#default_value' => $default_value,
            '#disabled' => $disabled,
        );
        return $root;
    }    

    /**
     * The QA part of the form.
     */
    function getQADataEntryFields(&$form_state, $disabled, $myvalues)
    {
        // information.
        $root['qa_summary_fieldset'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('QA Notes'),
            '#attributes' => array(
                'class' => array(
                    'qa-data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $qa_scores = \raptor\TermMapping::getQAScoreLanguageMapping();
        $result = db_select('raptor_qa_criteria', 'n')
                ->fields('n')
                ->condition('context_cd', 'T','=')
                ->orderBy('position')
                ->execute();
        $qmarkup = array();
        while($record = $result->fetchAssoc())
        {
            $version = trim($record['version']);
            $shortname = trim($record['shortname']);
            $question = trim($record['question']);
            $explanation = trim($record['explanation']);
            $myitem = array(
             '#type' => 'radios',
             '#title' => t($question),
             '#options' => $qa_scores,
             '#description' => t($explanation),
             '#attributes' => array(
                'class' => array('container-inline'),
                ),
            );   

            if(isset($myvalues[$shortname]['score']))
            {
                $myitem['#default_value'] = $myvalues[$shortname]['score'];
            }
            $mycomment = array(
                '#type' => 'textarea',
                '#rows'     => 1,
                '#maxlength' => 1024,
            );
            if(isset($myvalues[$shortname]['comment']))
            {
                $mycomment['#default_value'] = $myvalues[$shortname]['comment'];
            }

            $qmarkup[$shortname]['version'] =  array('#type' => 'hidden', '#value' => $version);
            $qmarkup[$shortname]['score'] = $myitem;
            $qmarkup[$shortname]['comment'] = $mycomment;
        }
        if(count($qmarkup)>0)
        {
            $root['qa_summary_fieldset']['evaluations'] = array(
                '#type'     => 'fieldset',
                '#title'    => t('Assessment Questions'),
                '#disabled' => $disabled,
                '#tree' => TRUE,
            );
            foreach($qmarkup as $key=>$value)
            {
                $root['qa_summary_fieldset']['evaluations'][$key] = $value;
            }
        }
        
        //Show the general comment input.
        $sName = 'qa_notes_tx';
        $default_value = isset($myvalues[$sName]) ? $myvalues[$sName] : '';
        $root['qa_summary_fieldset']['overall'][$sName] = array(
            '#type'     => 'textarea',
            '#rows'     => 10,
            '#title'    => t('Overall QA Comments'),
            '#maxlength' => 1024,
            '#default_value' => $default_value,
            '#disabled' => $disabled,
        );
        return $root;
    }    
    
    /**
     * Saves values when in protocol mode.
     */
    public function saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues)
    {
        //Create the raptor_ticket_protocol_settings record now
        $bSuccess = TRUE;
        try
        {
            //See if we already have a record of values.
            $result = db_select('raptor_ticket_protocol_settings','p')
                    ->fields('p')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->execute();
            $nRows = $result->rowCount();
            if($nRows > 0)
            {
                //Replace the record but save the values.
                error_log('Replacing protocol information SITEID=' . $nSiteID . ' IEN=' . $nIEN . ' UID=' . $nUID . ' CWFS=' . $sCWFS . ' NWFS=' . $sNewWFS);
                $record = $result->fetchAssoc();
                $oInsert = db_insert('raptor_ticket_protocol_settings_replaced')
                        ->fields(array(
                            'siteid' =>$record['siteid'],
                            'IEN' => $record['IEN'],
                            'primary_protocol_shortname' => $record['primary_protocol_shortname'],
                            'secondary_protocol_shortname' => $record['secondary_protocol_shortname'],
                            'current_workflow_state_cd' => $record['current_workflow_state_cd'],

                            'hydration_none_yn' => $record['hydration_none_yn'],
                            'hydration_oral_tx' => $record['hydration_oral_tx'],
                            'hydration_iv_tx' => $record['hydration_iv_tx'],

                            'sedation_none_yn' => $record['sedation_none_yn'],
                            'sedation_oral_tx' => $record['sedation_oral_tx'],
                            'sedation_iv_tx' => $record['sedation_iv_tx'],
                            
                            'contrast_none_yn' => $record['contrast_none_yn'],
                            'contrast_enteric_tx' => $record['contrast_enteric_tx'],
                            'contrast_iv_tx' => $record['contrast_iv_tx'],

                            'radioisotope_none_yn' => $record['radioisotope_none_yn'],
                            'radioisotope_enteric_tx' => $record['radioisotope_enteric_tx'],
                            'radioisotope_iv_tx' => $record['radioisotope_iv_tx'],

                            'author_uid' => $record['author_uid'],
                            'original_created_dt' => $record['created_dt'],
                            'replaced_dt' => $updated_dt,
                        ))
                        ->execute();
                $nDeleted = db_delete('raptor_ticket_protocol_settings')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->execute();
            }

            //die('LOOK>>>'. print_r($myvalues,TRUE)); 
            if($myvalues['hydration_radio_cd'] == 'oral')
            {
                $hydration_oral_value = trim($myvalues['hydration_oral_customtx']);
                if($hydration_oral_value == NULL || $hydration_oral_value == '')
                {
                    $hydration_oral_value = trim($myvalues['hydration_oral_id']);   //Todo rename because not really an ID
                }
            } else {
                $hydration_oral_value = NULL;
            }
            if($myvalues['hydration_radio_cd'] == 'iv')
            {
                $hydration_iv_value = trim($myvalues['hydration_iv_customtx']);
                if($hydration_iv_value == NULL || $hydration_iv_value == '')
                {
                    $hydration_iv_value = trim($myvalues['hydration_iv_id']);   //Todo rename because not really an ID
                }
            } else {
                $hydration_iv_value = NULL;
            }
            if($myvalues['sedation_radio_cd'] == 'oral')
            {
                $sedation_oral_value = trim($myvalues['sedation_oral_customtx']);
                if($sedation_oral_value == NULL || $sedation_oral_value == '')
                {
                    $sedation_oral_value = trim($myvalues['sedation_oral_id']);   //Todo rename because not really an ID
                }
            } else {
                $sedation_oral_value = NULL;
            }
            if($myvalues['sedation_radio_cd'] == 'iv')
            {
                $sedation_iv_value = trim($myvalues['sedation_iv_customtx']);
                if($sedation_iv_value == NULL || $sedation_iv_value == '')
                {
                    $sedation_iv_value = trim($myvalues['sedation_iv_id']);   //Todo rename because not really an ID
                }
            } else {
                $sedation_iv_value = NULL;
            }
            
            $contrast_enteric_value = NULL;
            $contrast_iv_value = NULL;
            $myarray = $myvalues['contrast_cd'];
            if($myarray['none'] !== 0)
            {
                //No contrast selected.
                $contrast_none = 1;
            } else {
                //Yes, contrast selected.
                $contrast_none = 0;
                if($myarray['enteric'] !== 0)
                {
                    $contrast_enteric_value = (isset($myvalues['contrast_enteric_customtx']) ? trim($myvalues['contrast_enteric_customtx']) : '');
                    if($contrast_enteric_value == NULL || $contrast_enteric_value == '')
                    {
                        $contrast_enteric_value = trim($myvalues['contrast_enteric_id']);   //Todo rename because not really an ID
                    }
                }
                if($myarray['iv'] !== 0)
                {
                    $contrast_iv_value = (isset($myvalues['contrast_iv_customtx']) ? trim($myvalues['contrast_iv_customtx']) : '');
                    if($contrast_iv_value == NULL || $contrast_iv_value == '')
                    {
                        $contrast_iv_value = trim($myvalues['contrast_iv_id']);   //Todo rename because not really an ID
                    }
                }            
            }
            
            $radioisotope_enteric_value = NULL;
            $radioisotope_iv_value = NULL;
            $myarray = $myvalues['radioisotope_cd'];
            if($myarray['none'] !== 0)
            {
                //No radioisotope selected.
                $radioisotope_none = 1;
            } else {
                //Yes, radioisotope selected.
                $radioisotope_none = 0;
                if($myarray['enteric'] !== 0)
                {
                    $radioisotope_enteric_value = (isset($myvalues['radioisotope_enteric_customtx']) ? trim($myvalues['radioisotope_enteric_customtx']) : '');
                    if($radioisotope_enteric_value == NULL || $radioisotope_enteric_value == '')
                    {
                        $radioisotope_enteric_value = trim($myvalues['radioisotope_enteric_id']);   //Todo rename because not really an ID
                    }
                }
                if($myarray['iv'] !== 0)
                {
                    $radioisotope_iv_value = (isset($myvalues['radioisotope_iv_customtx']) ? trim($myvalues['radioisotope_iv_customtx']) : '');
                    if($radioisotope_iv_value == NULL || $radioisotope_iv_value == '')
                    {
                        $radioisotope_iv_value = trim($myvalues['radioisotope_iv_id']);   //Todo rename because not really an ID
                    }
                }            
            }
            
            $allergy_kw = isset($myvalues['allergy_cd']) ? $myvalues['allergy_cd'] : NULL;
            $claustrophobic_kw = isset($myvalues['claustrophobic_cd']) ? $myvalues['claustrophobic_cd'] : NULL;
            $consent_req_kw = isset($myvalues['consentreq_radio_cd']) ? $myvalues['consentreq_radio_cd'] : NULL;
            
            $oInsert = db_insert('raptor_ticket_protocol_settings')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        
                        'primary_protocol_shortname' => $myvalues['protocol1_nm'],
                        'secondary_protocol_shortname' => $myvalues['protocol2_nm'],
                        
                        'hydration_none_yn' => ($myvalues['hydration_radio_cd'] == '' ? 1 : 0),
                        'hydration_oral_tx' => $hydration_oral_value,
                        'hydration_iv_tx' => $hydration_iv_value,
                        
                        'sedation_none_yn' => ($myvalues['sedation_radio_cd'] == '' ? 1 : 0),
                        'sedation_oral_tx' => $sedation_oral_value,
                        'sedation_iv_tx' => $sedation_iv_value,
                        
                        'contrast_none_yn' => $contrast_none,
                        'contrast_enteric_tx' => $contrast_enteric_value,
                        'contrast_iv_tx' => $contrast_iv_value,

                        'radioisotope_none_yn' => $radioisotope_none,
                        'radioisotope_enteric_tx' => $radioisotope_enteric_value,
                        'radioisotope_iv_tx' => $radioisotope_iv_value,

                        'allergy_kw' => $allergy_kw,
                        'claustrophobic_kw' => $claustrophobic_kw,
                        'consent_req_kw' => $consent_req_kw,
                        
                        'current_workflow_state_cd' => $sCWFS,
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(\Exception $e)
        {
            error_log('Failed to create raptor_ticket_protocol_settings: ' . print_r($e,TRUE));
            drupal_set_message('Failed to save information for this ticket because ' . $e->getMessage(),'error');
            $bSuccess = FALSE;
        }

        if($bSuccess)
        {
            //Create the raptor_ticket_protocol_notes record now
            try
            {
                if(isset($myvalues['protocolnotes_tx']) && trim($myvalues['protocolnotes_tx']) !== '')
                {
                    $oInsert = db_insert('raptor_ticket_protocol_notes')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'notes_tx' => $myvalues['protocolnotes_tx'],
                                'author_uid' => $nUID,
                                'created_dt' => $updated_dt,
                            ))
                            ->execute();
                }
            }
            catch(\Exception $e)
            {
                error_log('Failed to create raptor_ticket_protocol_notes: ' . $e);
                form_set_error('protocol1_nm','Failed to save notes for this ticket!');
                $bSuccess = FALSE;
            }
        }

        if($bSuccess && $sNewWFS != $sCWFS)
        {
            $this->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
        }
        return $bSuccess;
    }
    
    public function createSimpleProtocolNotesRecord($nSiteID,$nIEN,$notes_tx,$nUID,$updated_dt)
    {
        //Create the raptor_ticket_protocol_notes record now
        try
        {
            if(isset($notes_tx) && trim($notes_tx) !== '')
            {
                $oInsert = db_insert('raptor_ticket_protocol_notes')
                        ->fields(array(
                            'siteid' => $nSiteID,
                            'IEN' => $nIEN,
                            'notes_tx' => $notes_tx,
                            'author_uid' => $nUID,
                            'created_dt' => $updated_dt,
                        ))
                        ->execute();
            }
        }
        catch(\Exception $ex)
        {
            $message = "Failed to save note text to "
                    . "raptor_ticket_protocol_notes "
                    . "for ticket {$nSiteID}-{$nIEN} as UID=$nUID; note text=$notes_tx";
            throw \Exception($message, 99123, $ex);
        }
    }

    /**
     * Saves values when in exam mode.
     * @return boolean
     */
    public function saveAllExamFieldValues($nSiteID, $nIEN, $nUID
            , $sCWFS, $sNewWFS, $updated_dt, $myvalues)
    {
        $patientDFN = NULL;
        try
        {
            $oDD = new \raptor\DashboardData($this->m_oContext);
            $raptor_protocoldashboard = $oDD->getDashboardDetails();
            $patientDFN=$raptor_protocoldashboard['PatientID'];
        } catch (\Exception $ex) {
            throw new \Exception('Failed to get the dashboard to save exam fields',91111,$ex);
        }
        
        $use_sofar_tables = FALSE;
        if($sNewWFS == 'SAVE_SOFAR')
        {
            //We will use sofar table for notes.
            $use_sofar_tables = TRUE;   //Because this is just saving work sofar
            $sNewWFS = $sCWFS;          //Because we will NOT change the workflow
        }
            
        //Create the raptor_ticket_exam_settings record now
        $bSuccess = TRUE;
        try
        {
            //See if we already have a record of values.
            $result = db_select('raptor_ticket_exam_settings','p')
                    ->fields('p')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->execute();
            $nRows = $result->rowCount();
            if($nRows > 0)
            {
                //Replace the record but save the values.
                error_log('Replacing exam information SITEID=' . $nSiteID . ' IEN=' . $nIEN . ' UID=' . $nUID . ' CWFS=' . $sCWFS . ' NWFS=' . $sNewWFS);
                $record = $result->fetchAssoc();
                $oInsert = db_insert('raptor_ticket_exam_settings_replaced')
                        ->fields(array(
                            'siteid' =>$record['siteid'],
                            'IEN' => $record['IEN'],
                            'current_workflow_state_cd' => $record['current_workflow_state_cd'],

                            'hydration_none_yn' => $record['hydration_none_yn'],
                            'hydration_oral_tx' => $record['hydration_oral_tx'],
                            'hydration_iv_tx' => $record['hydration_iv_tx'],

                            'sedation_none_yn' => $record['sedation_none_yn'],
                            'sedation_oral_tx' => $record['sedation_oral_tx'],
                            'sedation_iv_tx' => $record['sedation_iv_tx'],
                            
                            'contrast_none_yn' => $record['contrast_none_yn'],
                            'contrast_enteric_tx' => $record['contrast_enteric_tx'],
                            'contrast_iv_tx' => $record['contrast_iv_tx'],

                            'radioisotope_none_yn' => $record['radioisotope_none_yn'],
                            'radioisotope_enteric_tx' => $record['radioisotope_enteric_tx'],
                            'radioisotope_iv_tx' => $record['radioisotope_iv_tx'],
                            
                            'consent_received_kw' => $record['consent_received_kw'],
                            
                            'author_uid' => $record['author_uid'],
                            'original_created_dt' => $record['created_dt'],
                            'replaced_dt' => $updated_dt,
                        ))
                        ->execute();
                $nDeleted = db_delete('raptor_ticket_exam_settings')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->execute();
            }

            $hydration_oral_value = trim($myvalues['exam_hydration_oral_tx']);
            $hydration_iv_value = trim($myvalues['exam_hydration_iv_tx']);
            $hydration_none_yn = ($hydration_oral_value == '' && $hydration_iv_value == '') ? 1 : 0;   
                
            $sedation_oral_value = trim($myvalues['exam_sedation_oral_tx']);
            $sedation_iv_value = trim($myvalues['exam_sedation_iv_tx']);
            $sedation_none_yn = ($sedation_oral_value == '' && $sedation_iv_value == '') ? 1 : 0;   

            $contrast_enteric_value = trim($myvalues['exam_contrast_enteric_tx']);
            $contrast_iv_value = trim($myvalues['exam_contrast_iv_tx']);
            $contrast_none_yn = ($contrast_enteric_value == '' && $contrast_iv_value == '') ? 1 : 0;   

            $radioisotope_enteric_value = trim($myvalues['exam_radioisotope_enteric_tx']);
            $radioisotope_iv_value = trim($myvalues['exam_radioisotope_iv_tx']);
            $radioisotope_none_yn = ($radioisotope_enteric_value == '' && $radioisotope_iv_value == '') ? 1 : 0;   

            $consent_received_kw = isset($myvalues['exam_consent_received_kw']) ? $myvalues['exam_consent_received_kw'] : NULL;
            
            $oInsert = db_insert('raptor_ticket_exam_settings')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        
                        'hydration_none_yn' => $hydration_none_yn,
                        'hydration_oral_tx' => $hydration_oral_value,
                        'hydration_iv_tx' => $hydration_iv_value,
                        
                        'sedation_none_yn' => $sedation_none_yn,
                        'sedation_oral_tx' => $sedation_oral_value,
                        'sedation_iv_tx' => $sedation_iv_value,
                        
                        'contrast_none_yn' => $contrast_none_yn,
                        'contrast_enteric_tx' => $contrast_enteric_value,
                        'contrast_iv_tx' => $contrast_iv_value,

                        'radioisotope_none_yn' => $radioisotope_none_yn,
                        'radioisotope_enteric_tx' => $radioisotope_enteric_value,
                        'radioisotope_iv_tx' => $radioisotope_iv_value,

                        'consent_received_kw' => $consent_received_kw,
                        
                        'current_workflow_state_cd' => $sCWFS,
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(\Exception $ex)
        {
            error_log('Failed to create raptor_ticket_exam_settings: ' . print_r($ex,TRUE));
            drupal_set_message('Failed to save exam information for this ticket because ' . $ex->getMessage(),'error');
            $bSuccess = FALSE;
        }
        
        if($bSuccess)
        {
            
            //Process ALL the possible radiation dose input areas.
            $littlename_map = RadiationDoseHelper::getDoseSourceLittlenameMap();
            foreach($littlename_map as $dose_source_code=>$littlename)
            {
                $radiation_dose_tx = isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
                $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
                $dose_type_cd = isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
                error_log("save 1 of 2 DEBUG LOOK DOSE INFO FOR source code=$dose_source_code ($radiation_dose_tx)");
                if($radiation_dose_tx != '')
                {
                    $nPatientID = $patientDFN;
                    $data_provider = 'tech during exam';
                    $dose_dt = $updated_dt;
                    error_log("save 2 of 2 DEBUG LOOK DOSE INFO FOR source code=$dose_source_code ($radiation_dose_tx) write!");
                    $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                            , $radiation_dose_tx
                            , $uom
                            , $dose_dt
                            , $dose_type_cd
                            , $dose_source_code
                            , $data_provider
                            , $updated_dt
                            , $use_sofar_tables);                
                }
            }
            /*
            
            //Now write the dose information.
            $radiation_dose_tx = isset($myvalues['exam_ctdivol_radiation_dose_tx']) ? trim($myvalues['exam_ctdivol_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_ctdivol_radiation_dose_uom_tx']) ? trim($myvalues['exam_ctdivol_radiation_dose_uom_tx']) : '';
            $dose_type_cd = isset($myvalues['exam_ctdivol_radiation_dose_type_cd']) ? trim($myvalues['exam_ctdivol_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = 'C';
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $radiation_dose_tx = isset($myvalues['exam_dlp_radiation_dose_tx']) ? trim($myvalues['exam_dlp_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_dlp_radiation_dose_uom_tx']) ? trim($myvalues['exam_dlp_radiation_dose_uom_tx']) : '';
            $dose_type_cd = isset($myvalues['exam_dlp_radiation_dose_type_cd']) ? trim($myvalues['exam_dlp_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = 'D';
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $radiation_dose_tx = isset($myvalues['exam_other_radiation_dose_tx']) ? trim($myvalues['exam_other_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_other_radiation_dose_uom_tx']) ? trim($myvalues['exam_other_radiation_dose_uom_tx']) : '';
            $dose_type_cd = isset($myvalues['exam_other_radiation_dose_type_cd']) ? trim($myvalues['exam_other_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = 'E';
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            
            $radiation_dose_tx = isset($myvalues['exam_radioisotope_radiation_dose_tx']) ? trim($myvalues['exam_radioisotope_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_radioisotope_radiation_dose_uom_tx']) ? trim($myvalues['exam_radioisotope_radiation_dose_uom_tx']) : '';
            $dose_type_cd = isset($myvalues['exam_radioisotope_radiation_dose_type_cd']) ? trim($myvalues['exam_radioisotope_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = 'R';
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            
            $littlename = 'fluoroQ';
            $littlecode = 'Q';
            $radiation_dose_tx = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
            $dose_type_cd = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = $littlecode;
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $littlename = 'fluoroS';
            $littlecode = 'S';
            $radiation_dose_tx = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
            $dose_type_cd = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = $littlecode;
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $littlename = 'fluoroT';
            $littlecode = 'T';
            $radiation_dose_tx = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
            $dose_type_cd = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = $littlecode;
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $littlename = 'fluoroH';
            $littlecode = 'H';
            $radiation_dose_tx = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
            $dose_type_cd = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = $littlecode;
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
            $littlename = 'fluoro5';
            $littlecode = 'U';
            $radiation_dose_tx = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_tx']) : '';
            $uom = isset($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_uom_tx']) : '';
            $dose_type_cd = 
                    isset($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) 
                    ? trim($myvalues['exam_'.$littlename.'_radiation_dose_type_cd']) : '';
            if($radiation_dose_tx != '')
            {
                $nPatientID = $patientDFN;
                $dose_type_cd = $dose_type_cd;
                $dose_source_cd = $littlecode;
                $data_provider = 'tech during exam';
                $dose_dt = $updated_dt;
                $this->writeRadiationDoseDetails($nSiteID, $nIEN, $nPatientID, $nUID
                        , $radiation_dose_tx,$uom
                        , $dose_dt
                        , $dose_type_cd
                        , $dose_source_cd
                        , $data_provider
                        , $updated_dt
                        , $use_sofar_tables);                
            }
             * 
             */
            
        }

        if($bSuccess)
        {
            //Create the raptor_ticket_exam_notes record now
            try
            {
                if($use_sofar_tables)
                {
                    //Create the record EVEN if there are no notes!
                    $oMerge = db_merge('raptor_ticket_exam_notes_sofar')
                            ->key(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'author_uid' => $nUID,
                            ))
                        ->fields(array(
                            'notes_tx' => $myvalues['exam_notes_tx'],
                            'created_dt' => $updated_dt,
                        ))
                        ->execute();
                } else {
                    //Only create the record if there are some notes.
                    if(isset($myvalues['exam_notes_tx']) 
                            && trim($myvalues['exam_notes_tx']) !== '')
                    {
                            $oInsert = db_insert('raptor_ticket_exam_notes')
                                ->fields(array(
                                    'siteid' => $nSiteID,
                                    'IEN' => $nIEN,
                                    'notes_tx' => $myvalues['exam_notes_tx'],
                                    'author_uid' => $nUID,
                                    'created_dt' => $updated_dt,
                                ))
                                ->execute();
                            //Now delete any 'so far' note entries
                            $nDeleted = db_delete('raptor_ticket_exam_notes_sofar')
                                ->condition('siteid',$nSiteID,'=')
                                ->condition('IEN',$nIEN,'=')
                                ->execute();
                    }
                }
            }
            catch(\Exception $e)
            {
                error_log('Failed to create raptor_ticket_exam_notes: ' . $e);
                form_set_error('exam_notes_tx','Failed to save notes for this ticket!');
                $bSuccess = FALSE;
            }
        }
        
        if($bSuccess && $sNewWFS != $sCWFS)
        {
            $this->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
        }
        return $bSuccess;
    }
    
    /**
     * Replace records if there were already some there.
     */
    function writeRadiationDoseDetails($nSiteID, $nIEN
            , $nPatientID, $nUID
            , $radiation_dose_tx
            , $uom,$dose_dt
            , $dose_type_cd
            , $dose_source_cd
            , $data_provider
            , $updated_dt
            , $write_to_sofar=FALSE)
    {
        error_log("a DEBUG LOOK writeRadiationDoseDetails 1 ($write_to_sofar)>>> $nSiteID-$nIEN :: $nPatientID, $nUID >> sourcecode=$dose_source_cd dose values=$radiation_dose_tx");
        if($write_to_sofar)
        {
            //We are only saving values so far.
            $targettablename = 'raptor_ticket_exam_radiation_dose_sofar';
        } else {
            //We are committing to the real table.
            try
            {
                //See if we already have a records of values.
                $result = db_select('raptor_ticket_exam_radiation_dose','p')
                        ->fields('p')
                        ->condition('siteid',$nSiteID,'=')
                        ->condition('IEN',$nIEN,'=')
                        ->condition('dose_source_cd',$dose_source_cd,'=')
                        ->execute();
                while($record = $result->fetchAssoc())
                {
                    //Replace the record but save the values.
                    $oInsert = db_insert('raptor_ticket_exam_radiation_dose_replaced')
                            ->fields(array(
                                'siteid' =>$record['siteid'],
                                'IEN' => $record['IEN'],
                                'patientid' => $record['patientid'],

                                'sequence_position' => $record['sequence_position'],
                                'dose' => $record['dose'],
                                'uom' => $record['uom'],

                                'dose_dt' => $record['dose_dt'],
                                'dose_type_cd' => $record['dose_type_cd'],
                                'dose_source_cd' => $record['dose_source_cd'],
                                'data_provider' => $record['data_provider'],

                                'author_uid' => $record['author_uid'],

                                'original_created_dt' => $record['created_dt'],
                                'replaced_dt' => $updated_dt,
                            ))
                            ->execute();
                }
                $nDeleted = db_delete('raptor_ticket_exam_radiation_dose')
                    ->condition('siteid',$nSiteID,'=')
                    ->condition('IEN',$nIEN,'=')
                    ->condition('dose_source_cd',$dose_source_cd,'=')
                    ->execute();
            } catch(\Exception $ex) {
                error_log('Failed to create raptor_ticket_exam_radiation_dose_replaced: ' . print_r($ex,TRUE));
                throw $ex;
            }
            $targettablename = 'raptor_ticket_exam_radiation_dose';
        }
        error_log("a DEBUG LOOK writeRadiationDoseDetails 2 table=$targettablename ($write_to_sofar)>>> $nSiteID-$nIEN :: $nPatientID, $nUID >> sourcecode=$dose_source_cd dose values=$radiation_dose_tx");
        
        //Do we have anything to write?
        if($radiation_dose_tx != '')
        {
            $dose_values = explode(',', $radiation_dose_tx);
            $sequence_num = 0;
            foreach($dose_values as $dose)
            {
                $sequence_num++;
                try
                {
                    $oInsert = db_insert($targettablename)
                            ->fields(array(
                                    'siteid' => $nSiteID,
                                    'IEN' => $nIEN,
                                    'patientid' => $nPatientID,

                                    'sequence_position' => $sequence_num,
                                    'dose' => $dose,
                                    'uom' => $uom,

                                    'dose_dt' => $dose_dt,
                                    'dose_type_cd' => $dose_type_cd,
                                    'dose_source_cd' => $dose_source_cd,
                                    'data_provider' => $data_provider,

                                    'author_uid' => $nUID,
                                    'created_dt' => $updated_dt,
                            ))
                            ->execute();
                } catch (\Exception $ex) {
                        error_log('Failed to create '.$targettablename.': ' . print_r($ex,TRUE));
                        drupal_set_message('Failed to save exam dose information for this ticket because ' 
                                . $ex->getMessage(),'error');
                        $bSuccess = FALSE;
                        throw $ex;
                }
            }
            error_log("a DEBUG LOOK writeRadiationDoseDetails 3 table=$targettablename inserted $sequence_num ($write_to_sofar)>>> $nSiteID-$nIEN :: $nPatientID, $nUID >> sourcecode=$dose_source_cd dose values=$radiation_dose_tx");
        }
    }

    
    public function saveAllQAFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues)
    {
        $bSuccess = TRUE;
        //Create the raptor_ticket_qa_notes record now
        try
        {
            
            if(isset($myvalues['evaluations']) && is_array($myvalues['evaluations']))
            {
                //Write the evaluation answers.
                foreach($myvalues['evaluations'] as $shortname=>$aDetails)
                {
                    if(isset($aDetails['score']) && is_numeric($aDetails['score']))
                    {
                        $criteria_score = $aDetails['score'];
                        $criteria_version = $aDetails['version'];
                        $comment = $aDetails['comment'];
                        $oInsert = db_insert('raptor_ticket_qa_evaluation')
                            ->fields(array(
                                'siteid' => $nSiteID,
                                'IEN' => $nIEN,
                                'criteria_shortname' => $shortname,
                                'criteria_version' => $criteria_version,
                                'criteria_score' => $criteria_score,
                                'comment' => $comment,
                                'workflow_state' => $sCWFS,
                                'author_uid' => $nUID,
                                'evaluation_dt' => $updated_dt,
                            ))
                            ->execute();
                    }
                }
            }
        }
        catch(\Exception $e)
        {
                error_log('Failed to create raptor_ticket_qa_evaluation: ' . $e);
                form_set_error('qa_notes_tx','Failed to save QA notes for this ticket!'.$e);
                $bSuccess = FALSE;
        }
           
        if($bSuccess)
        {
            try
            {
                if(isset($myvalues['qa_notes_tx']) && trim($myvalues['qa_notes_tx']) !== '')
                {
                        $oInsert = db_insert('raptor_ticket_qa_notes')
                                        ->fields(array(
                                                'siteid' => $nSiteID,
                                                'IEN' => $nIEN,
                                                'notes_tx' => $myvalues['qa_notes_tx'],
                                                'author_uid' => $nUID,
                                                'created_dt' => $updated_dt,
                                        ))
                                        ->execute();
                }
            }
            catch(\Exception $e)
            {
                    error_log('Failed to create raptor_ticket_qa_notes: ' . $e);
                    form_set_error('qa_notes_tx','Failed to save notes for this ticket!');
                    $bSuccess = FALSE;
            }
        }
        
        if($bSuccess && $sNewWFS != $sCWFS)
        {
            $this->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
        }
        return $bSuccess;
    }


    public function saveAllInterpretationFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues)
    {
        $bSuccess = TRUE;
        //Create the raptor_ticket_interpret_notes record now
        try
        {
                if(isset($myvalues['interpret_notes_tx']) && trim($myvalues['interpret_notes_tx']) !== '')
                {
                        $oInsert = db_insert('raptor_ticket_interpret_notes')
                                        ->fields(array(
                                                'siteid' => $nSiteID,
                                                'IEN' => $nIEN,
                                                'notes_tx' => $myvalues['interpret_notes_tx'],
                                                'author_uid' => $nUID,
                                                'created_dt' => $updated_dt,
                                        ))
                                        ->execute();
                }
        }
        catch(\Exception $e)
        {
                error_log('Failed to create raptor_ticket_interpret_notes: ' . $e);
                form_set_error('interpret_notes_tx','Failed to save notes for this ticket!');
                $bSuccess = FALSE;
        }
        
        if($bSuccess && $sNewWFS != $sCWFS)
        {
            $this->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
        }
        return $bSuccess;
    }
    
    /**
     * Alter the ticket workflow status
     * @param type $nSiteID
     * @param type $nIEN
     * @param type $nUID
     * @param type $sNewWFS
     * @param type $sCWFS
     * @param type $updated_dt
     * @return int
     */
    public function changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt)
    {
        return $this->m_oTT->setTicketWorkflowState($nSiteID.'-'.$nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
    }

    /**
     * Fetch the state directly from the database.
     * @param type $nSiteID
     * @param type $nIEN
     */
    public function getCurrentWorkflowState($nSiteID, $nIEN)
    {
        return $this->m_oTT->getTicketWorkflowState($nSiteID.'-'.$nIEN);
    }
}
