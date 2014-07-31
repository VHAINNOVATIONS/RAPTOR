<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once('FormHelper.php');

/**
 * Utilities for the ProtocolInfo form content.
 *
 * @author FrankWin7VM
 */
class ProtocolInfoUtility
{
    private $m_oContext = null;
    private $m_oTT = null;
    
    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }
    
    /**
     * All the notes tables have the same structure
     * @param $tablename the table name
     */
    public function getPreviousNotesMarkup($tablename,$nSiteID,$nIEN,$extraClassname='')
    {
        
        $prev_notes_tx = NULL;

        //Get app existing protcol notes
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
     * All the notes tables have the same structure
     * @param $tablename the table name
     */
    public function getSchedulerNotesMarkup($nSiteID,$nIEN)
    {
        
        $scheduler_notes = NULL;

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
            $scheduler_notes = '<div class="existing-note '.$sClassText.'">'
                    . '<span class="datetime">' . $record['created_dt'] . '</span> ' 
                    . '<span class="author-name">'.$fullname.'</span> ' 
                    . '<span class="scheduled-time-details">'.$details.'</span> ' 
                    . '<div class="note-text">' . $record['notes_tx'] . '</div> '  
                    . '</div>';
        }
        
        return $scheduler_notes;
    }
    
    
    function getDataEntryArea1(&$form_state, $disabled, $myvalues)
    {
        //PROTOCOL
        $form[] = array('#markup' => '<div id="protocol-template-data"><div style="visibility:hidden" id="json-default-values-all-sections"></div></div>');
        $form[]                                         = $this->getProtocolSelectionElement($form_state, $disabled, $myvalues, TRUE, 'protocol1',"A standard protocol from the hospital's radiology notebook.", TRUE, TRUE);
        $form[]                                         = $this->getProtocolSelectionElement($form_state, $disabled, $myvalues, FALSE, 'protocol2',"Select a second protocol only if more than one is needed for this study.", FALSE, FALSE);
        $form["Pre-medication Admistration Grouping"][] = $this->raptor_form_get_hydration($form_state, $disabled, $myvalues, null, null);
        $form["Pre-medication Admistration Grouping"][] = $this->raptor_form_get_sedation($form_state, $disabled, $myvalues, null, null);
        $form[]                                         = $this->raptor_form_get_radioisotope($form_state, $disabled, $myvalues, null, null);
        $form[]                                         = $this->raptor_form_get_contrast($form_state, $disabled, $myvalues, null, null);
        $form[]                                         = $this->raptor_form_get_allergy($form_state, $disabled, $myvalues, null, null);
        $form[]                                         = $this->raptor_form_get_claustrophobic($form_state, $disabled, $myvalues, null, null);
        $form[]                                         = $this->raptor_form_get_consent($form_state, $disabled, $myvalues, null, null);

        return $form;
    }

    function getDataEntryArea2(&$form_state, $disabled, $myvalues)
    {

        //activate this during examination $form["DataEntryArea2"][] = $this->raptor_form_get_exam_info($form_state, $disabled, $myvalues);

        $form["PrevSuspendNotes"] = array(
            '#prefix' => "\n<div class='prev-suspend-notes'>\n",
            '#markup' => $myvalues['prev_suspend_notes_tx'],
            '#suffix' => "\n</div>\n",
        );
        $form["PrevProtocolNotes"] = array(
            '#prefix' => "\n<div class='prev-protocolnotes'>\n",
            '#markup' => $myvalues['prev_protocolnotes_tx'],
            '#suffix' => "\n</div>\n",
        );
        $form["PrevExamNotes"] = array(
            '#prefix' => "\n<div class='prev-exam-notes'>\n",
            '#markup' => $myvalues['prev_exam_notes_tx'],
            '#suffix' => "\n</div>\n",
        );
        $form["PrevQANotes"] = array(
            '#prefix' => "\n<div class='prev-qa-notes'>\n",
            '#markup' => $myvalues['prev_qa_notes_tx'],
            '#suffix' => "\n</div>\n",
        );

        //$form["ProtocolNotes"] = array('#markup' => "<h1>TODO Boilerstuff</h1>");
        $form['ProtocolNotes'] = $this->raptor_form_get_protocol_notes($form_state, $disabled, $myvalues);

        //TODO -- exam notes if in that mode
        
        return $form;
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
        $root['default_values_'.$section_name] = array(
            '#type' => 'container',
            '#attributes' => array(
                'class' => array(
                    'default-value-acknowledgement'
                )
            )
        );
        //Hide this if they make any selections of their own, restore if they hit reset
        $root['default_values_'.$section_name]['acknowledge_'.$section_name.'_group'] = array(
            '#type' => 'container',
            '#attributes' => array(
                'id' => 'acknowledge_'.$section_name.'_group',
                'class' => array('acknowledge-default-values-group'),
                'style' => array($shownow ? 'display:inline' : 'display:none' ),
            )
        );
        $root['default_values_'.$section_name]['acknowledge_'.$section_name."_group"]['acknowledge_'.$section_name] = array(
            '#type'    => 'checkbox',
            '#title' => t('Acknowledge Selected Values'),
            '#description' => t('You are being asked to acknowledge these values because they are currently the default values.'),
            '#disabled' => $disabled,
        );
        //TODO export some markup that contains the values that javascript can restore!

        return $root;
    }

    /**
     * Get the block of default value controls for a section
     * These sections will show/hide at runtime using AJAX.
     * @param type $section_name
     * @param type $disabled
     * @param type $defaultvalues
     * @return string
     */
    function getDefaultValueControls($section_name, $disabled, $defaultvalues)
    {
        $root = array();
        //Are there currently default values for the section?
        $shownow = FALSE;
        if(isset($defaultvalues) && isset($defaultvalues[$section_name]))
        {
            //Yes, there are default values.
            $shownow = TRUE;
        }
        //Always create the markup, but show it only if there are default values.
        $root[] = $this->getDefaultValueSubSection($section_name, $disabled, $defaultvalues, null);
        $root[]['require_acknowledgement_for_'.$section_name] = array(
            '#type' => 'hidden', 
            '#default_value' => ($shownow ? 'yes' : 'no'),
        );
        return $root;
    }
    
    function getPageActionButtonsArea(&$form_state, $disabled, $myvalues)
    {
        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo();
        $userprivs = $userinfo->getSystemPrivileges();
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $sCWFS = $this->getCurrentWorkflowState($nSiteID, $nIEN);
        
        $acknowledgeTip = 'Acknowledge the presented protocol so the exam can begin.';
        $examcompletionTip = 'Save all current settings and mark the examination as completed.';
        $qaTip = 'Save QA notes.';
        if($oContext->hasPersonalBatchStack())
        {
            $sRequestApproveTip = 'Save this order as ready for review and continue with next available personal batch selection.';
            $releaseTip = 'Release this order without saving changes and continue with next available personal batch selection.';
            $reserveTip = 'Assign this order to yourself with current edits saved and continue with the next available personal batch selection.';
            $collaborateTip = 'Assign this order a specialist with current edits saved and continue with the next available personal batch selection.';
            $approveTip = 'Save this order as approved and continue with the next available personal batch selection.';
            $suspendTip = 'Suspend this order without saving edits and continue with the next available personal batch selection.';
        } else {
            $sRequestApproveTip = 'Save this order as ready for review and return to the worklist.';
            $releaseTip = 'Release this order without saving changes and return to the worklist.';
            $reserveTip = 'Assign this order to yourself with current edits saved and return to the worklist.';
            $collaborateTip = 'Assign this order to a specialist with current edits saved and return to the worklist.';
            $approveTip = 'Save this order as approved and return to the worklist.';
            $suspendTip = 'Suspend this order without saving changes and return to the worklist.';
        }
        if($sCWFS == 'AP') 
        {
            $form[]['acknowledge_button'] = array('#type' => 'submit'
                , '#value' => t('Acknowledge Protocol')
                , '#attributes' => array('title' => $acknowledgeTip)
                , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                );
        } else if($sCWFS == 'PA') {
            $form[]['examcompleted_button'] = array('#type' => 'submit'
                , '#value' => t('Exam Completed')
                , '#attributes' => array('title' => $examcompletionTip)
                , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                );
        } else if($sCWFS == 'EC') {
            $form[]['qa_button'] = array('#type' => 'submit'
                , '#value' => t('QA Complete')
                , '#attributes' => array('title' => $qaTip)
                , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                );
        }
        if($sCWFS == 'AC' || $sCWFS == 'CO' || $sCWFS == 'AP' || $sCWFS == 'RV')
        {
            if($userprivs['PWI1'] == 1)
            {
                if($userprivs['APWI1'] == 1)
                {
                    $form[]['approve_button'] = array('#type' => 'submit'
                        , '#value' => t('Approve')
                        , '#attributes' => array('title' => $approveTip)
                        , '#validate' => array('raptor_datalayer_protocolinfo_form_builder_customvalidate')
                        , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                        );
                } else {
                    $form[]['request_approve_button'] = array('#type' => 'submit'
                        , '#value' => t('Request Approval')
                        , '#attributes' => array('title' => $sRequestApproveTip)
                        , '#validate' => array('raptor_datalayer_protocolinfo_form_builder_customvalidate')
                        , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                        );
                }
                $form[]['collaborate_button'] = array('#markup' => '<input id="raptor-protocol-collaborate" type="button" value="Collaborate" title="'.$collaborateTip.'">');
            }
        }
        $form[]['release_button'] = array('#type' => 'button'
            , '#value' => t('Release back to Worklist without Saving')
            , '#attributes' => array('onclick' => 'javascript:window.location.href="/drupal/protocol?pbatch=CONTINUE&releasedticket=TRUE";return false;'
                    ,'title' => $releaseTip)
            , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
            );
        if($sCWFS == 'AC' || $sCWFS == 'CO' || $sCWFS == 'AP' || $sCWFS == 'RV')
        {
            if($sCWFS == 'CO')
            {
                $form[]['reserve_button'] = array('#type' => 'submit'
                    , '#value' => t('Reserve (already reserved by someone)')
                    , '#attributes' => array('title' => $reserveTip)
                    , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                    );
            } else {
                $form[]['reserve_button'] = array('#type' => 'submit'
                    , '#value' => t('Reserve')
                    , '#attributes' => array('title' => $reserveTip)
                    , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                    );
            }
        }
        if($userprivs['SUWI1'] == 1)
        {
            $form[]['suspend_button'] = array('#type' => 'submit'
                , '#value' => t('Suspend')
                , '#attributes' => array('title' => $suspendTip)
                , '#submit' => array('raptor_datalayer_protocolinfo_form_builder_customsubmit')
                );
        }
        
        return $form;
    }

    function getStaticWarningsArea(&$form_state, $disabled, $myvalues)
    {
        $form[] = array(
            '#markup' => "<ol>"
            . "\n<li>Placeholder for Contraindication Alert</li>"
            . "\n<li>Placeholder for Contraindication Alert</li>"
            . "</ol>"
        );
        return $form;
    }
    
    /**
     * Get the list of protocol choices.
     * @param type $sDefaultChoiceOverrideID
     * @param type $procName    procedure name
     * @param string $sFirstElementText FALSE=nothing, else first element is this.
     * @return array of ProtocolChoice instances
     */
    private function getProtocolChoices($procName=NULL,$sFirstElementText='')
    {
        if($procName !== NULL)
        {
            //TODO --- derive some criteria to match from analysys of $procName
        }
        $result = db_select('raptor_protocol_lib','p')
                ->fields('p')
                ->orderBy('modality_abbr', 'ASC')
                ->orderBy('name', 'ASC')
                ->execute();
        $aCombinedList=array();
        //Create this as the first thing in the list.
        //$oC = new raptor_datalayer_Choice(
        $oC = new \raptor\FormControlChoiceItem(
                $sFirstElementText
                ,NULL
                ,NULL
                ,FALSE);
        $aCombinedList[] = $oC;
        while($record = $result->fetchAssoc()) 
        {
            //$oC = new raptor_datalayer_Choice(
            $oC = new \raptor\FormControlChoiceItem(
                    $record['name']
                    ,$record['protocol_shortname']
                    ,$record['modality_abbr']
                    ,FALSE);
            $aCombinedList[] = $oC;
        }
        return $aCombinedList;
    }

    /**
     * 
     * @param type $form_state
     * @param type $disabled
     * @param type $myvalues
     * @param type $bFindMatch
     * @param type $sBaseName
     * @param type $sDescription
     * @param type $bUseAjax
     * @param type $bRequireValue
     * @return string
     */
    function getProtocolSelectionElement(&$form_state, $disabled, $myvalues,$bFindMatch=TRUE,$sBaseName='protocol1', $sDescription=NULL, $bUseAjax=FALSE, $bRequireValue=FALSE)
    {

        $oPPU = new ProtocolPageUtils();

        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t('Protocol Name'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );

        if($bFindMatch)
        {
            $choices  = $this->getProtocolChoices($myvalues['procName'],"- Select -");
        } else {
            $choices  = $this->getProtocolChoices("");
        }
        //drupal_set_message('>>>choices>>>'.print_r($choices,TRUE));
        $element  = array(
            '#type'        => 'select',
            '#description' => $sDescription,
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
                $element['#required']      = $bRequireValue;
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
     * Return panel with radios and custom value selection controls
     * @param type $section_name
     * @param type $titleoverride
     * @param type $aOralChoices
     * @param type $aIVChoices
     * @param type $form_state
     * @param type $disabled
     * @param type $myvalues
     * @param type $titleoverride
     * @param type $containerstates
     * @param type $supportEditMode TODO REMOVE THIS 
     * @param array $aCustomOverride 'oral=>'customtx' and 'iv'=>'customtx'
     * @return associative array of controls
     */
    function getSectionCheckboxType($section_name, $titleoverride, $aEntericChoices, $aIVChoices, &$form_state, $disabled, $myvalues, $containerstates, $supportEditMode=TRUE, $aCustomOverride=NULL)
    {
        if($aCustomOverride == NULL)
        {
            $aCustomOverride = array();
        }
        $bShowCustomEnteric = (isset($aCustomOverride['enteric']));
        $bShowCustomIV = (isset($aCustomOverride['iv']));
        $bLockedReadonly = $disabled && !$supportEditMode;
        
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t($titleoverride),
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
        $default_values = is_array($myvalues[$section_name . '_cd']) ? $myvalues[$section_name . '_cd'] : array('none'=>'none');
        $root[$section_name.'_fieldset_col1'][$section_name . '_cd'] = array(
            '#type'          => 'checkboxes',
            '#options'       => $options,
            '#attributes' => $bLockedReadonly ? array() : array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")'),
            '#default_value' => $default_values,
            '#disabled' => $disabled,
        );
        $radio_nm = $section_name . '_cd';
        if (isset($myvalues[$radio_nm]))
        {
            $root[$section_name.'_fieldset_col1'][$radio_nm]['#default_value'] = $myvalues[$radio_nm];
        }
        $root[$section_name.'_fieldset_col2'][$section_name . '_markup1'] = array(
            '#markup' => '<div class="v-spacer-select">&nbsp;</div>',
        );

        $sListRootName                           = $section_name . '_enteric_';
        $sListboxName                            = $sListRootName.'id';
        $aStatesEntry                           = array(
            'enabled' => array(
                ':input[name="'.$section_name.'_cd[enteric]"]' => array('checked' => TRUE),
                ':input[name="'.$section_name.'_cd[none]"]'    => array('checked' => FALSE),
        ));
        $sInlineName = 'inline_enteric';
        $root[$section_name.'_fieldset_col2'][$sInlineName] = array(
            '#type'       => 'fieldset',
            '#attributes' => array('class' => array('container-inline')),
            '#disabled' => $disabled,
        );
        $element = FormHelper::createCustomSelectPanel($section_name, $sListRootName, $aEntericChoices, $disabled, $aStatesEntry, $myvalues, $bShowCustomEnteric);
        $root[$section_name.'_fieldset_col2'][$sInlineName]['TEST_'.$sListboxName] =  $element;       

        $sListRootName                           = $section_name . '_iv_';
        $sListboxName                            = $sListRootName.'id';
        $aStatesEntry                           = array(
            'enabled' => array(
                ':input[name="'.$section_name.'_cd[iv]"]'   => array('checked' => TRUE),
                ':input[name="'.$section_name.'_cd[none]"]' => array('checked' => FALSE),
        ));
        $sInlineName = 'inline_iv';
        $root[$section_name.'_fieldset_col2'][$sInlineName] = array(
            '#type'       => 'fieldset',
            '#attributes' => array('class' => array('container-inline')),
            '#disabled' => $disabled,
        );
        $element = FormHelper::createCustomSelectPanel($section_name, $sListRootName, $aIVChoices, $disabled, $aStatesEntry, $myvalues, $bShowCustomIV);
        $root[$section_name.'_fieldset_col2'][$sInlineName]['TEST_'.$sListboxName] =  $element;       
        
        //Create the acknowledgement control
        $root[$section_name.'_fieldset_row2']['default_value_acknowledgement'] = $this->getDefaultValueControls($section_name, $disabled, $myvalues['DefaultValues'][$section_name]);
        //Always show this in each section that can have default values!
        if(!$disabled)
        {
            $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                '#markup' => "\n".'<div class="reset-values-button-container" name="reset-section-values"><a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default values will be restored">RESET</a></div>', 
                '#disabled' => $disabled,
            );
        }

        $form['main_fieldset_left'][$section_name . '_fieldset'] = &$root;

        return $form;
    }    
    
    /**
     * Return panel with radios and custom value selection controls
     * @param type $section_name
     * @param type $titleoverride
     * @param type $aOralChoices
     * @param type $aIVChoices
     * @param type $form_state
     * @param type $disabled
     * @param type $myvalues
     * @param type $containerstates
     * @param type $supportEditMode TODO REMOVE THIS 
     * @param array $aCustomOverride 'oral=>'customtx' and 'iv'=>'customtx'
     * @return associative array of controls
     */
    function getSectionRadioType($section_name, $titleoverride, $aOralChoices, $aIVChoices, &$form_state, $disabled, $myvalues, $containerstates, $supportEditMode=TRUE, $aCustomOverride=NULL)
    {
        if($aCustomOverride == NULL)
        {
            $aCustomOverride = array();
        }
        $bShowCustomOral = (isset($aCustomOverride['oral']));
        $bShowCustomIV = (isset($aCustomOverride['iv']));
        $bLockedReadonly = $disabled && !$supportEditMode;
        
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t($titleoverride),
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
            'oral' => t('Oral'),
            'iv'   => t('IV')
        );
        if(!isset($myvalues[$section_name . '_cd']))
        {
            //No default value.
            $defaultvalue = NULL;
        } else {
            $defaultvalue = $myvalues[$section_name . '_cd'];
        }
        $root[$section_name.'_fieldset_col1'][$section_name . '_cd'] = array(
            '#type'    => 'radios',
            '#options' => $options,
            '#attributes' => $bLockedReadonly ? array() : array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")'),
            '#default_value' => $defaultvalue,
            '#disabled' => $disabled,
        );
        $radio_nm = $section_name . '_cd';
        if (isset($myvalues[$radio_nm]))
        {
            $root[$section_name.'_fieldset_col1'][$radio_nm]['#default_value'] = $myvalues[$radio_nm];
        }
        $root[$section_name.'_fieldset_col2'][$section_name . '_markup1'] = array(
            '#markup' => '<div class="v-spacer-select">&nbsp;</div>',
        );

        $sListRootName                           = $section_name . '_oral_';
        $sListboxName                            = $sListRootName.'id';
        $aStatesEntry  = array(
            'enabled' => array(
                ':input[name="'.$radio_nm.'"]' => array('value' => 'oral'),
        ));
        $sInlineName = 'inline_oral';
        $root[$section_name.'_fieldset_col2'][$sInlineName] = array(
            '#type'       => 'fieldset',
            '#attributes' => array('class' => array('container-inline')),
            '#disabled' => $disabled,
        );
        $element = FormHelper::createCustomSelectPanel($section_name, $sListRootName, $aOralChoices, $disabled, $aStatesEntry, $myvalues, $bShowCustomOral);
        $root[$section_name.'_fieldset_col2'][$sInlineName]['TEST_'.$sListboxName] =  $element;       

        $sListRootName                           = $section_name . '_iv_';
        $sListboxName                            = $sListRootName.'id';
        $aStatesEntry                            = array(
            'enabled' => array(
                ':input[name="'.$radio_nm.'"]' => array('value' => 'iv'),
        ));
        $sInlineName = 'inline_iv';
        $root[$section_name.'_fieldset_col2'][$sInlineName] = array(
            '#type'       => 'fieldset',
            '#attributes' => array('class' => array('container-inline')),
            '#disabled' => $disabled,
        );
        $element = FormHelper::createCustomSelectPanel($section_name, $sListRootName, $aIVChoices, $disabled, $aStatesEntry, $myvalues, $bShowCustomIV);
        $root[$section_name.'_fieldset_col2'][$sInlineName]['TEST_'.$sListboxName] =  $element;       
        
        //Create the acknowledgement control
        $root[$section_name.'_fieldset_row2']['default_value_acknowledgement'] = $this->getDefaultValueControls($section_name, $disabled, $myvalues['DefaultValues'][$section_name]);
        //Always show this in each section that can have default values!
        if(!$disabled)
        {
            $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                '#markup' => "\n".'<div class="reset-values-button-container" name="reset-section-values"><a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default values will be restored">RESET</a></div>', 
                '#disabled' => $disabled,
            );
        }

        $form['main_fieldset_left'][$section_name . '_fieldset'] = &$root;

        return $form;
    }
    
    function raptor_form_get_hydration(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {
        $section_name  = 'hydration';
        $titleoverride = 'Hydration'; 
        $oChoices      = new raptor_datalayer_Choices();    
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        $oral_tx = isset($myvalues['hydration_oral_customtx']) ? $myvalues['hydration_oral_customtx'] : '';
        $aOralChoices  = $oChoices->getOralHydrationData($oral_tx, $bFoundInList);
        if($oral_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['oral'] = $oral_tx;
        }
        $iv_tx = isset($myvalues['hydration_iv_customtx']) ? $myvalues['hydration_iv_customtx'] : '';
        $aIVChoices    = $oChoices->getIVHydrationData($iv_tx, $bFoundInList);
        if($iv_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['iv'] = $iv_tx;
        }
        return $this->getSectionRadioType($section_name, $titleoverride, $aOralChoices, $aIVChoices, $form_state, $disabled, $myvalues, $containerstates, $supportEditMode, $aControlOverrides);
    }

    function raptor_form_get_sedation(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {
        $section_name  = 'sedation';
        $titleoverride = 'Sedation'; 
        $oChoices      = new raptor_datalayer_Choices();    
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        $oral_tx = isset($myvalues['sedation_oral_customtx']) ? $myvalues['sedation_oral_customtx'] : '';
        $aOralChoices  = $oChoices->getOralSedationData($oral_tx, $bFoundInList);
        if($oral_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['oral'] = $oral_tx;
        }
        $iv_tx = isset($myvalues['sedation_iv_customtx']) ? $myvalues['sedation_iv_customtx'] : '';
        $aIVChoices    = $oChoices->getIVSedationData($iv_tx, $bFoundInList);
        if($iv_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['iv'] = $iv_tx;
        }
        return $this->getSectionRadioType($section_name, $titleoverride, $aOralChoices, $aIVChoices, $form_state, $disabled, $myvalues, $containerstates, $supportEditMode, $aControlOverrides);
    }
    
    
    function raptor_form_get_contrast(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {
        $section_name  = 'contrast';
        $titleoverride = 'Contrast'; 
        $oChoices      = new raptor_datalayer_Choices();    
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        $enteric_tx = isset($myvalues['contrast_enteric_customtx']) ? $myvalues['contrast_enteric_customtx'] : '';
        $aEntericChoices  = $oChoices->getEntericContrastData($enteric_tx, $bFoundInList);
        if($enteric_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['enteric'] = $enteric_tx;
        }
        $iv_tx = isset($myvalues['contrast_iv_customtx']) ? $myvalues['contrast_iv_customtx'] : '';
        $aIVChoices    = $oChoices->getIVContrastData($iv_tx, $bFoundInList);
        if($iv_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['iv'] = $iv_tx;
        }
        return $this->getSectionCheckboxType($section_name, $titleoverride, $aEntericChoices, $aIVChoices, $form_state, $disabled, $myvalues, $containerstates, $supportEditMode, $aControlOverrides);
    }

    function raptor_form_get_radioisotope(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {
        $section_name  = 'radioisotope';
        $titleoverride = 'Radioisotope'; 
        $oChoices      = new raptor_datalayer_Choices();    
        $bFoundInList = FALSE;  //Initialize
        $aControlOverrides = array();   //Initialize
        $enteric_tx = isset($myvalues['radioisotope_enteric_customtx']) ? $myvalues['radioisotope_enteric_customtx'] : '';
        $aEntericChoices  = $oChoices->getEntericRadioisotopeData($enteric_tx, $bFoundInList);
        if($enteric_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['enteric'] = $enteric_tx;
        }
        $iv_tx = isset($myvalues['radioisotope_iv_customtx']) ? $myvalues['radioisotope_iv_customtx'] : '';
        $aIVChoices    = $oChoices->getIVRadioisotopeData($iv_tx, $bFoundInList);
        if($iv_tx > '' && !$bFoundInList)
        {
            $aControlOverrides['iv'] = $iv_tx;
        }
        return $this->getSectionCheckboxType($section_name, $titleoverride, $aEntericChoices, $aIVChoices, $form_state, $disabled, $myvalues, $containerstates, $supportEditMode, $aControlOverrides);
    }
    
    function raptor_form_get_allergy(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {

        // allergy information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t('Allergy'),
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

        $root['allergy_fieldset_col1'] = array(
            '#type' => 'fieldset',
                //'#title' => t('COL1'),
        );

        $options                                     = array(
            'unknown' => t('Unknown'),
            'no'      => t('No'),
            'yes'     => t('Yes'),
        );
        $root['allergy_fieldset_col1']['allergy_cd'] = array(
            '#type'    => 'radios',
            '#options' => $options,
            '#default_value' => 'unknown',
                //'#required' => true,
                //'#default_value' => 'yes',
        );
        if (isset($myvalues['allergy_cd']))
        {
            $root['allergy_fieldset_col1']['allergy_cd']['#default_value'] = $myvalues['allergy_cd'];
        }

        $form['main_fieldset_left']['allergy_fieldset'] = &$root;
        return $form;
    }

    function raptor_form_get_claustrophobic(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {

        // claustrophobic information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t('claustrophobic'),
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

        $root['claustrophobic_fieldset_col1'] = array(
            '#type' => 'fieldset',
                //'#title' => t('COL1'),
        );

        $options                                                   = array(
            'unknown' => t('Unknown'),
            'no'      => t('No'),
            'yes'     => t('Yes'),
        );
        $root['claustrophobic_fieldset_col1']['claustrophobic_cd'] = array(
            '#type'    => 'radios',
            '#options' => $options,
                //'#required' => true,
            '#default_value' => 'unknown',
        );
        if (isset($myvalues['claustrophobic_cd']))
        {
            $root['claustrophobic_fieldset_col1']['claustrophobic_cd']['#default_value'] = $myvalues['claustrophobic_cd'];
        }

        $form['main_fieldset_left']['claustrophobic_fieldset'] = &$root;
        return $form;
    }

    
    
    function raptor_form_get_consent(&$form_state, $disabled, $myvalues, $titleoverride, $containerstates, $supportEditMode=TRUE)
    {

        $section_name = 'consentreq';
        if($titleoverride == null)
        {
            $titleoverride = 'Consent Required';
        }
        
        // consent information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t($titleoverride),
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

        $root['consentreq_fieldset_col1'] = array(
            '#type' => 'fieldset',
                //'#title' => t('COL1'),
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
        $default_value = isset($myvalues['consentreq_cd']) ? $myvalues['consentreq_cd'] : '';
        
        //die('>>>LOOK=['.$default_value.']>>>'.print_r($myvalues,TRUE));
        
        $root['consentreq_fieldset_col1']['consentreq_cd'] = array(
            '#type'    => 'radios',
            '#options' => $options,
                //'#required' => true,
            '#default_value' => $default_value,
        );
        $root['consentreq_fieldset_col1']['consentreq_cd']['#attributes'] = array('onchange' => 'notDefaultValuesInSection("'.$section_name.'")');
        if (isset($myvalues['consentreq_cd']))
        {
            $root['consentreq_fieldset_col1']['consentreq_cd']['#default_value'] = $myvalues['consentreq_cd'];
        }

        $root[$section_name.'_fieldset_row2']['default_value_acknowledgement'] = $this->getDefaultValueControls($section_name, $disabled, $myvalues['DefaultValues'][$section_name]);
        if(!$disabled)
        {
            //Always show this in each section that can have default values!
            $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                '#markup' => "\n".'<div class="reset-values-button-container" name="reset-section-values"><a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default values will be restored">RESET</a></div>', 
                '#disabled' => $disabled,
            );
        }
        
        $form['main_fieldset_left']['consentreq_fieldset'] = &$root;
        return $form;
    }

    function raptor_form_get_protocol_notes(&$form_state, $disabled, $myvalues, $supportEditMode=TRUE)
    {
        $section_name = "protocolnotes";
        
        $root                                = array(
            '#type'     => 'fieldset',
            '#title'    => t('Protocol Notes'),
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
        if (isset($myvalues['protocolnotes_tx']))
        {
            $protocolnotes_tx = $myvalues['protocolnotes_tx'];
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
                $root[$section_name.'_fieldset_col1']['disabled_protocolnotes_tx'] = array(
                    '#type'          => 'textarea',
                    '#title'         => t('Protocol Notes'),
                    '#disabled'      => $disabled,
                    '#default_value' => $protocolnotes_tx,
                );
            }
            else
            {
                //$root['boilerplate_fieldset'] = $this->getDefaultValueControls($section_name, $disabled, $myvalues['DefaultValues'][$section_name]);

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
                                . " href='javascript:app2textareaByName(" . '"protocolnotes_tx"' . "," . '"' . $aItem[0] . '"' 
                                . ")'>$sName</a>";
                    }
                    $sBoilerplateHTML.="</ul>";
                }
                $sBoilerplateHTML.="</ul></div>";
                if ($nBoilerplate > 0)
                {
                    $root[$section_name.'_fieldset_col1']['boilerplate_fieldset']                = array(
                        '#type'  => 'fieldset',
                        '#title' => t('Protocol Notes Boilerplate Text Helpers'),
                    );
                    $root[$section_name.'_fieldset_col1']['boilerplate_fieldset']['boilerplate'] = array(
                        '#markup' => $sBoilerplateHTML,
                    );
                }

                //Create the note area.
                $root[$section_name.'_fieldset_col1']['protocolnotes_tx'] = array(
                    '#type'          => 'textarea',
                    '#title'         => t('Protocol Notes'),
                    '#disabled'      => $disabled,
                    '#default_value' => $protocolnotes_tx,
                    '#attributes' => array('oninput' => 'notDefaultValuesInSection("'.$section_name.'")'),
                );
                $root[$section_name.'_fieldset_row2']['default_value_acknowledgement'] = $this->getDefaultValueControls($section_name, $disabled, $myvalues['DefaultValues'][$section_name]);
                if(!$disabled)
                {
                    //Always show this in each section that can have default values!
                    $root[$section_name.'_fieldset_col3']['reset_'.$section_name] = array(
                        '#markup' => "\n".'<div class="reset-values-button-container" name="reset-section-values"><a href="javascript:setDefaultValuesInSection('."'".$section_name."',getTemplateDataJSON()".')" title="The default values for ' . $section_name . ' will be restored">RESET</a></div>', 
                        '#disabled' => $disabled,
                    );
                }
            }
        }
        return $root;
    }

    /**
     * The EXAM part of the form.
     */
    function raptor_form_get_exam_info($form, &$form_state, $disabled, $myvalues, $supportEditMode=TRUE)
    {

        // information.
        $root = array(
            '#type'     => 'fieldset',
            '#title'    => t('Examination Notes'),
            '#attributes' => array(
                'class' => array(
                    'data-entry2-area'
                )
             ),
            '#disabled' => $disabled,
        );

        $root['exam_fieldset_col1'] = array(
            '#type'     => 'fieldset',
            //'#title' => t('COL1'),
            '#disabled' => $disabled,
        );

        $root['exam_fieldset_col1']['exam_hydration_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => t('Hydration Administered'),
        );

        $ma                                                                                 = $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_oral_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Type'),
        );
        if (isset($myvalues['ex_hydration_oral_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_oral_type_tx']['#default_value'] = $myvalues['ex_hydration_oral_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_oral_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Volume (Please write units)'),
        );
        if (isset($myvalues['ex_hydration_oral_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_oral_vol_tx']['#default_value'] = $myvalues['ex_hydration_oral_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_iv_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
        );
        if (isset($myvalues['ex_hydration_iv_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_iv_type_tx']['#default_value'] = $myvalues['ex_hydration_iv_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_iv_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Volume (Please write units)'),
        );
        if (isset($myvalues['ex_hydration_iv_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_hydration_fieldset']['ex_hydration_iv_vol_tx']['#default_value'] = $myvalues['ex_hydration_iv_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => t('Contrast Administered'),
        );

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_enteric_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Enteric Type'),
        );
        if (isset($myvalues['ex_contrast_enteric_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_enteric_type_tx']['#default_value'] = $myvalues['ex_contrast_enteric_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_enteric_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Enteric Volume (Please write units)'),
        );
        if (isset($myvalues['ex_contrast_enteric_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_enteric_vol_tx']['#default_value'] = $myvalues['ex_contrast_enteric_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
        );
        if (isset($myvalues['ex_contrast_iv_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_type_tx']['#default_value'] = $myvalues['ex_contrast_iv_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Volume (Please write units)'),
        );
        if (isset($myvalues['ex_contrast_iv_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_vol_tx']['#default_value'] = $myvalues['ex_contrast_iv_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_rate_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Injection Rate'),
        );
        if (isset($myvalues['ex_contrast_iv_rate_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_rate_tx']['#default_value'] = $myvalues['ex_contrast_iv_rate_tx'];
        }

        $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_rate_units_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Injection Units'),
        );
        if (isset($myvalues['ex_contrast_iv_rate_units_tx']))
        {
            $root['exam_fieldset_col1']['exam_contrast_fieldset']['ex_contrast_iv_rate_units_tx']['#default_value'] = $myvalues['ex_contrast_iv_rate_units_tx'];
        }

        $root['exam_fieldset_col1']['exam_sedation_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => t('Sedation Administered'),
        );

        $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_oral_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Type'),
        );
        if (isset($myvalues['ex_sedation_oral_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_oral_type_tx']['#default_value'] = $myvalues['ex_sedation_oral_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_oral_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('Oral Volume (Please write units)'),
        );
        if (isset($myvalues['ex_sedation_oral_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_oral_vol_tx']['#default_value'] = $myvalues['ex_sedation_oral_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_iv_type_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Type'),
        );
        if (isset($myvalues['ex_sedation_iv_type_tx']))
        {
            $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_iv_type_tx']['#default_value'] = $myvalues['ex_sedation_iv_type_tx'];
        }

        $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_iv_vol_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('IV Volume (Please write units)'),
        );
        if (isset($myvalues['ex_sedation_iv_vol_tx']))
        {
            $root['exam_fieldset_col1']['exam_sedation_fieldset']['ex_sedation_iv_vol_tx']['#default_value'] = $myvalues['ex_sedation_iv_vol_tx'];
        }

        $root['exam_fieldset_col1']['exam_rdose_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => t('Radiation Exposure'),
        );

        $root['exam_fieldset_col1']['exam_rdose_fieldset']['ex_radiation_exp_ctdi_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('CTDIvol (mGy)'),
        );
        if (isset($myvalues['ex_radiation_exp_ctdi_tx']))
        {
            $root['exam_fieldset_col1']['exam_rdose_fieldset']['ex_radiation_exp_ctdi_tx']['#default_value'] = $myvalues['ex_radiation_exp_ctdi_tx'];
        }

        $root['exam_fieldset_col1']['exam_rdose_fieldset']['ex_radiation_exp_dlp_tx'] = array(
            '#type'  => 'textfield',
            '#title' => t('DLP (mGy cm)'),
        );
        if (isset($myvalues['ex_radiation_exp_dlp_tx']))
        {
            $root['exam_fieldset_col1']['exam_rdose_fieldset']['ex_radiation_exp_dlp_tx']['#default_value'] = $myvalues['ex_radiation_exp_dlp_tx'];
        }


        $root['exam_fieldset_col2'] = array(
            '#type'     => 'fieldset',
            //'#title' => t('COL2'),
            '#disabled' => $disabled,
        );

        if ($disabled)
        {
            #A hack to work-around CSS issue on coloring!
            $root['exam_fieldset_col2']['exam_notes_tx'] = array(
                '#type'     => 'textarea',
                '#title'    => t('Examination Notes'),
                '#disabled' => $disabled,
            );
        }
        else
        {
            //Create the boilerplate insertion buttons
            $nBoilerplate     = 0;
            $aBoilerplate     = ListUtils::getCategorizedLists("boilerplate-examnotes.cfg");
            $sBoilerplateHTML = "<div id='boilerplate'><ul>";
            foreach ($aBoilerplate as $sCategory => $aContent)
            {
                $sBoilerplateHTML.="<li class='category'>$sCategory<ul>";
                foreach ($aContent as $sName => $aItem)
                {
                    $nBoilerplate+=1;
                    $sTitle = $aItem[0];
                    $sBoilerplateHTML.="<li><a title='$sTitle' href='javascript:app2textareaByName(" . '"exam_notes_tx"' . "," . '"' . $aItem[0] . '"' . ")'>$sName</a>";
                }
                $sBoilerplateHTML.="</ul>";
            }
            $sBoilerplateHTML.="</ul></div>";
            if ($nBoilerplate > 0)
            {
                $root['exam_fieldset_col2']['boilerplate_fieldset']                = array(
                    '#type'  => 'fieldset',
                    '#title' => t('Exam Notes Boilerplate Text Helpers'),
                );
                $root['exam_fieldset_col2']['boilerplate_fieldset']['boilerplate'] = array(
                    '#markup' => $sBoilerplateHTML,
                );
            }

            $root['exam_fieldset_col2']['exam_notes_tx'] = array(
                '#type'     => 'textarea',
                '#rows'     => 20,
                '#title'    => t('Examination Notes'),
                '#disabled' => $disabled,
            );
        }
        if (isset($myvalues['exam_notes_tx']))
        {
            $root['exam_fieldset_col2']['exam_notes_tx']['#default_value'] = $myvalues['exam_notes_tx'];
        }

        $form['main_fieldset_bottom']['exam_fieldset'] = &$root;
        return $form;
    }

    /**
     * The QA stuff
     */
    function raptor_form_get_postexam_info($form, &$form_state, $disabled, $myvalues, $supportEditMode=TRUE)
    {

        // information.
        $root = array(
            '#type'  => 'fieldset',
            '#title' => t('Post-Examination Notes'),
            '#attributes' => array(
                'class' => array(
                    'data-entry2-area'
                )
             ),
        );

        //Show all the existing notes
        $q        = db_select('raptor_form_qa', 'a');
        $q->addField('a', 'rf_id');
        $q->addField('a', 'user_id');
        $q->addField('a', 'qa1_fl');
        $q->addField('a', 'qa2_fl');
        $q->addField('a', 'qa3_fl');
        $q->addField('a', 'qa4_fl');
        $q->addField('a', 'qa5_fl');
        $q->addField('a', 'qa_notes_tx');
        $q->addField('a', 'created_dt');
        $q->condition('rf_id', $myvalues['guid']);
        $rf       = $q->execute();
        $myvalues = array();
        if ($rf->rowCount() > 0)
        {
            $sHTML = "<table id='qa-notes'>";
            $sHTML.="<tr>";
            $sHTML.="<th width='20%'>Datetime</th>";
            $sHTML.="<th width='20%'>Author</th>";
            $sHTML.="<th>QA1</th>";
            $sHTML.="<th>QA2</th>";
            $sHTML.="<th>QA3</th>";
            $sHTML.="<th>QA4</th>";
            $sHTML.="<th>QA5</th>";
            $sHTML.="<th width='35%'>Notes</th>";
            $sHTML.="</tr>";
            $nROW  = 0;
            foreach ($rf as $row)
            {
                $nUID    = $row->user_id;
                //$oOtherUser=UserInfo::lookupUserByUID( $nUID );
                $theName = UserInfo::lookupUserNameByUID($nUID);
                $nROW+=1;
                $sHTML.="<tr>";
                $sHTML.="<td width='20%'>" . $row->created_dt . "</td>";
                $sHTML.="<td width='20%'>" . $theName . "</td>";
                //$sHTML.="<td>".$oOtherUser->name."<td>";
                $sHTML.="<td>" . ($row->qa1_fl == 1 ? '<b>Yes</b>' : 'No') . "</td>";
                $sHTML.="<td>" . ($row->qa2_fl == 1 ? '<b>Yes</b>' : 'No') . "</td>";
                $sHTML.="<td>" . ($row->qa3_fl == 1 ? '<b>Yes</b>' : 'No') . "</td>";
                $sHTML.="<td>" . ($row->qa4_fl == 1 ? '<b>Yes</b>' : 'No') . "</td>";
                $sHTML.="<td>" . ($row->qa5_fl == 1 ? '<b>Yes</b>' : 'No') . "</td>";
                $sHTML.="<td width='35%'>" . $row->qa_notes_tx . "</td>";
                $sHTML.="</tr>";
            }
            $sHTML.="</table>";
            $root['qa_notes' . $nROW] = array(
                '#markup' => $sHTML,
                    //'#markup' => '<p>HELLO TESTING</p>'."<br>ROW:".var_export($row,true),
            );
        }

        $oGrp1                          = $root['postexam_fieldset_col1'] = array(
            '#type' => 'fieldset',
                //'#title' => t('COL1'),
        );

        $root['postexam_fieldset_col1']['postexam_qa_fieldset'] = array(
            '#type'  => 'fieldset',
            '#title' => t('QA Issues'),
        );

        $root['postexam_fieldset_col1']['postexam_qa_fieldset']['postexam_qa_fl'] = array(
            '#type'    => 'checkboxes',
            '#options' => array(
                'QA1' => t('QA1'),
                'QA2' => t('QA2'),
                'QA3' => t('QA3'),
                'QA4' => t('QA4'),
                'QA5' => t('QA5'),
            ),
                //'#title' => t('contrast')
        );

        $oGrp2                          = $root['postexam_fieldset_col2'] = array(
            '#type' => 'fieldset',
                //'#title' => t('COL2'),
        );

        $root['postexam_fieldset_col2']['qa_notes_tx'] = array(
            '#type'     => 'textarea',
            '#title'    => t('Post-Examination Notes'),
            '#disabled' => $disabled
        );

        $form['main_fieldset_bottom']['postexam_fieldset'] = &$root;
        return $form;
    }
    
    public function saveAllProtocolFieldValues($nSiteID, $nIEN, $nUID, $sCWFS, $sNewWFS, $updated_dt, $myvalues)
    {
        //Create the raptor_ticket_protocol_settings record now
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

                            //TODO --- save all the fields from the record!!!
                            'hydration_none_yn' => $record['hydration_none_yn'],
                            'hydration_oral_tx' => $record['hydration_oral_tx'],
                            'hydration_iv_tx' => $record['hydration_iv_tx'],

                            'sedation_none_yn' => $record['sedation_none_yn'],
                            'sedation_oral_tx' => $record['sedation_oral_tx'],
                            'sedation_iv_tx' => $record['sedation_iv_tx'],
                            
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
            if($myvalues['hydration_cd'] == 'oral')
            {
                $hydration_oral_value = trim($myvalues['hydration_oral_customtx']);
                if($hydration_oral_value == NULL || $hydration_oral_value == '')
                {
                    $hydration_oral_value = trim($myvalues['hydration_oral_id']);   //Todo rename because not really an ID
                }
            } else {
                $hydration_oral_value = NULL;
            }
            if($myvalues['hydration_cd'] == 'iv')
            {
                $hydration_iv_value = trim($myvalues['hydration_iv_customtx']);
                if($hydration_iv_value == NULL || $hydration_iv_value == '')
                {
                    $hydration_iv_value = trim($myvalues['hydration_iv_id']);   //Todo rename because not really an ID
                }
            } else {
                $hydration_iv_value = NULL;
            }
            if($myvalues['sedation_cd'] == 'oral')
            {
                $sedation_oral_value = trim($myvalues['sedation_oral_customtx']);
                if($sedation_oral_value == NULL || $sedation_oral_value == '')
                {
                    $sedation_oral_value = trim($myvalues['sedation_oral_id']);   //Todo rename because not really an ID
                }
            } else {
                $sedation_oral_value = NULL;
            }
            if($myvalues['sedation_cd'] == 'iv')
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
            //drupal_set_message('LOOK>>>'.print_r($myarray,TRUE));
            //drupal_set_message('LOOK>>>'.print_r($myvalues,TRUE));
            if($myarray['none'] !== 0)
            {
                //No contrast selected.
            //drupal_set_message('LOOK NONE>>>'.print_r($myarray,TRUE));
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
            //drupal_set_message('LOOK ENTERIC>>>'.$contrast_enteric_value);
                }
                if($myarray['iv'] !== 0)
                {
                    $contrast_iv_value = (isset($myvalues['contrast_iv_customtx']) ? trim($myvalues['contrast_iv_customtx']) : '');
                    if($contrast_iv_value == NULL || $contrast_iv_value == '')
                    {
                        $contrast_iv_value = trim($myvalues['contrast_iv_id']);   //Todo rename because not really an ID
                    }
                }            
            //drupal_set_message('LOOK IV>>>'.$contrast_iv_value);
            }
            
            $radioisotope_enteric_value = NULL;
            $radioisotope_iv_value = NULL;
            $myarray = $myvalues['radioisotope_cd'];
            //drupal_set_message('LOOK>>>'.print_r($myarray,TRUE));
            //drupal_set_message('LOOK>>>'.print_r($myvalues,TRUE));
            if($myarray['none'] !== 0)
            {
                //No radioisotope selected.
            //drupal_set_message('LOOK NONE>>>'.print_r($myarray,TRUE));
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
            //drupal_set_message('LOOK ENTERIC>>>'.$radioisotope_enteric_value);
                }
                if($myarray['iv'] !== 0)
                {
                    $radioisotope_iv_value = (isset($myvalues['radioisotope_iv_customtx']) ? trim($myvalues['radioisotope_iv_customtx']) : '');
                    if($radioisotope_iv_value == NULL || $radioisotope_iv_value == '')
                    {
                        $radioisotope_iv_value = trim($myvalues['radioisotope_iv_id']);   //Todo rename because not really an ID
                    }
                }            
            //drupal_set_message('LOOK IV>>>'.$radioisotope_iv_value);
            }
            
            $claustrophobic_kw = isset($myvalues['claustrophobic_kw']) ? $myvalues['claustrophobic_kw'] : NULL;
            $consent_req_kw = isset($myvalues['consent_req_kw']) ? $myvalues['consent_req_kw'] : NULL;
            
            $oInsert = db_insert('raptor_ticket_protocol_settings')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'primary_protocol_shortname' => $myvalues['protocol1_nm'],
                        'secondary_protocol_shortname' => $myvalues['protocol2_nm'],
                        
                        'hydration_none_yn' => ($myvalues['hydration_cd'] == '' ? 1 : 0),
                        'hydration_oral_tx' => $hydration_oral_value,
                        'hydration_iv_tx' => $hydration_iv_value,
                        
                        'sedation_none_yn' => ($myvalues['sedation_cd'] == '' ? 1 : 0),
                        'sedation_oral_tx' => $sedation_oral_value,
                        'sedation_iv_tx' => $sedation_iv_value,
                        
                        'contrast_none_yn' => $contrast_none,
                        'contrast_enteric_tx' => $contrast_enteric_value,
                        'contrast_iv_tx' => $contrast_iv_value,

                        'radioisotope_none_yn' => $radioisotope_none,
                        'radioisotope_enteric_tx' => $radioisotope_enteric_value,
                        'radioisotope_iv_tx' => $radioisotope_iv_value,

                        'claustrophobic_kw' => $claustrophobic_kw,
                        'consent_req_kw' => $consent_req_kw,
                        
                        'current_workflow_state_cd' => $sCWFS,
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(PDOException $e)
        {
            error_log('Failed to create raptor_ticket_protocol_settings: ' . $e . "\nDetails..." . print_r($oInsert,true));
            form_set_error('protocol1_nm','Failed to save notes for this ticket!');
            return 0;
        }

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
        catch(PDOException $e)
        {
            error_log('Failed to create raptor_ticket_protocol_notes: ' . $e);
            form_set_error('protocol1_nm','Failed to save notes for this ticket!');
             return 0;
        }

        //Create the raptor_ticket_exam_notes record now
        try
        {
            if(isset($myvalues['examnotes_tx']) && trim($myvalues['examnotes_tx']) !== '')
            {
                $oInsert = db_insert('raptor_ticket_exam_notes')
                        ->fields(array(
                            'siteid' => $nSiteID,
                            'IEN' => $nIEN,
                            'notes_tx' => $myvalues['examnotes_tx'],
                            'author_uid' => $nUID,
                            'created_dt' => $updated_dt,
                        ))
                        ->execute();
            }
        }
        catch(PDOException $e)
        {
            error_log('Failed to create raptor_ticket_exam_notes: ' . $e);
            form_set_error('examnotes_tx','Failed to save notes for this ticket!');
             return 0;
        }
        
        $this->changeTicketWorkflowStatus($nSiteID, $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);
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