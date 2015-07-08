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
 * This class returns the qa edit page
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditQAQuestionsPage
{
    
    private $m_gobackurl = NULL;

    public function __construct()
    {
        module_load_include('php','raptor_datalayer','core/data_context');
        module_load_include('php','raptor_glue','utility/TermMapping');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(FALSE && !$oUserInfo->isSiteAdministrator())
        {
            throw new \Exception('The user account does not have privileges for this page.');
        }
        global $base_url;
        $this->m_gobackurl = $base_url . '/raptor/managelists';
    }
    
    public function getGobacktoURL()
    {
        return $this->m_gobackurl;
    }
    
    public function getGobacktoURLParams()
    {
        return array();
    }

    private function getBlockMarkup($record, $disabled)
    {
        $qmarkup = array();
        
        $version = trim($record['version']);
        $position = trim($record['position']);
        $shortname = trim($record['shortname']);
        $question = trim($record['question']);
        $explanation = trim($record['explanation']);

        $position_input = array(
            '#type' => 'textfield', 
            '#title' => t('Position'), 
            '#default_value' => $position, 
            '#size' => 2, 
            '#maxlength' => 2, 
            '#disabled' => $disabled,
            '#description' => t("The relative position of this question.  Question 1 is shown before question 2 etc."),
        );        

        $shortname_input = array(
            '#type' => 'textfield', 
            '#title' => t('Shortname'), 
            '#default_value' => $shortname, 
            '#size' => 32, 
            '#maxlength' => 32, 
            '#disabled' => $disabled,
            '#description' => t("This is the unique key associated with the QA evaluation for the question."),
        );        

        $question_input = array(
            '#type' => 'textfield', 
            '#title' => t('Question'), 
            '#default_value' => $question, 
            '#size' => 256, 
            '#maxlength' => 256, 
            '#disabled' => $disabled,
            '#description' => t("This is the short criteria question text presented to the evaluator."),
        );        

        $explanation_input = array(
            '#type' => 'textarea', 
            '#title' => t('Explanation'), 
            '#default_value' => $explanation, 
            '#size' => 1024, 
            '#maxlength' => 2048, 
            '#disabled' => $disabled,
            '#description' => t("This is the detailed explanation of the criteria this question addresses."),
        );        

        $qmarkup['original_shortname'] =  array('#type' => 'hidden', '#value' => $shortname);
        $qmarkup['version'] =  array('#type' => 'hidden', '#value' => $version);
        $qmarkup['position'] = $position_input;
        $qmarkup['shortname'] = $shortname_input;
        $qmarkup['question'] = $question_input;
        $qmarkup['explanation'] = $explanation_input;

        return $qmarkup;
    }
    
    private function getQuestionMarkup($disabled,$myvalues)
    {
        try
        {
            $elementblocks = array();
            $qa_scores = \raptor\TermMapping::getQAScoreLanguageMapping();
            $result = db_select('raptor_qa_criteria', 'n')
                    ->fields('n')
                    ->condition('context_cd', 'T','=')
                    ->orderBy('position')
                    ->execute();
            $qnum=0;
            $qmarkup = array();
            $maxposition = 0;
            while($record = $result->fetchAssoc())
            {
                $qnum += 1;
                $position = trim($record['position']);
                if($position > $maxposition)
                {
                    $maxposition = $position;
                }
                $shortname = trim($record['shortname']);
                $qmarkup[$shortname] = $this->getBlockMarkup($record,$disabled);
            }
            //Always add an empty block for a new question to be created
            $blankrecord = array('position'=>$maxposition+1
                            , 'version'=>''
                            , 'shortname'=>''
                            , 'question'=>''
                            , 'explanation'=>''
                    );
            $qmarkup['_BLANK_'] = $this->getBlockMarkup($blankrecord,$disabled);
            if(count($qmarkup)>0)
            {
                foreach($qmarkup as $key=>$block)
                {
                    $elementblocks[$key] = $block;
                }
            }

            return $elementblocks;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function looksValidFormState($form, $form_state)
    {
        $bGood = TRUE;
        if(!isset($form_state['values']))
        {
            throw new \Exception("Expected form_state to contain values!");
        }
        $myvalues = $form_state['values'];
        if(!is_array($myvalues))
        {
            $msg = 'Failed because NOT find an array of values in form_state!';
            error_log("$msg>>>".print_r($myvalues,TRUE));
            throw new \Exception($msg);
        }
        if(!isset($myvalues['questions']))
        {
            $msg = 'Failed because NOT find any questions values!';
            error_log("$msg>>>".print_r($myvalues,TRUE));
            throw new \Exception($msg);
        }
        $questionblocks = $myvalues['questions'];
        $shortnamemap = array();
        $positionmap = array();
        foreach($questionblocks as $record)
        {
            $original_shortname = trim($record['original_shortname']);
            $shortname = trim($record['shortname']);
            $version = trim($record['version']);
            $position = trim($record['position']);
            $question = trim($record['question']);
            $explanation = trim($record['explanation']);
            
            if($shortname > '')
            {
                if(isset($shortnamemap[$shortname]))
                {
                    form_set_error("$original_shortname","Found duplication of shortname '$shortname'");
                    $bGood = FALSE;
                } else {
                    if(!is_numeric($position))
                    {
                        form_set_error("$original_shortname","The position value of $shortname must be an integer!");
                        $bGood = FALSE;
                    }
                    $position = intval($position);  //Convert it NOW before we check the map!!!
                    $shortnamemap[$shortname] = $position;
                    if(isset($positionmap[$position]))
                    {
                        $alreadytaken = $positionmap[$position];
                        form_set_error("$original_shortname","Found duplicate position '$position' for shortnames '$alreadytaken' and '$shortname'");
                        $bGood = FALSE;
                    } else {
                        $positionmap[$position] = $shortname;
                    }
                }
            }
            
            $allinputs = trim("$shortname$position$question$explanation");
            if($allinputs != '')
            {
                if($original_shortname > '')
                {
                    //They must ALL be filled if any were filled.
                    if($shortname == '')
                    {
                        form_set_error("$original_shortname","Missing some field values where shortname '$original_shortname' $allinputs used to be (blank them all to delete the question)");
                        $bGood = FALSE;
                    } else
                    if($position == ''
                            || $question == ''
                            || $explanation == '')
                    {
                        form_set_error("$original_shortname","Missing some field values for shortname '$shortname'");
                        $bGood = FALSE;
                    }
                 } else {
                     /*
                     if($allinputs != $position)
                     {
                        form_set_error("NEWFIELD","The new question at postion $position cannot be added if any fields are empty!");
                        $bGood = FALSE;
                     }
                      */
                 }
            }
        }
        if(!$bGood)
        {
            drupal_set_message("Form needs corrections","error");
        }
        return $bGood;
    }

    /**
     * Handle the database updates appropriately for one question
     */
    private function updateOneQuestion($questionblock)
    {
        try
        {
            $updated_dt = date("Y-m-d H:i:s", time());
            $successmsg = NULL;
            $original_shortname = trim($questionblock['original_shortname']);
            $shortname = strtoupper(trim($questionblock['shortname']));
            $version = trim($questionblock['version']);
            $position = trim($questionblock['position']);
            $question = trim($questionblock['question']);
            $explanation = trim($questionblock['explanation']);
            $allinputs = trim("$shortname$position$question$explanation");
            if($original_shortname > '')
            {
                //There was already a record
                $result = db_select('raptor_qa_criteria', 'n')
                        ->fields('n')
                        ->condition('context_cd', 'T','=')
                        ->condition('shortname', $original_shortname,'=')
                        ->orderBy('position')
                        ->execute();
                if($result->rowCount() != 1)
                {
                    throw new \Exception("Expected to find raptor_qa_criteria for '$original_shortname' but did not!");
                }
                $record = $result->fetchAssoc();
                $delta_shortname = ($shortname != $original_shortname);
                $delta_postion = ($position != $record['position']);
                $delta_question = ($question != $record['question']);
                $delta_explanation = ($explanation != $record['explanation']);
                $anythingchanged = ($delta_shortname || $delta_postion || $delta_question || $delta_explanation);
                if($allinputs == '')
                {
                    //Preserve existing record and create a new one
                    $oInsert = db_insert('raptor_qa_criteria_replaced')
                            ->fields(array(
                                'context_cd' => 'T',
                                'version' => $record['version'],
                                'position' => $record['position'],
                                'shortname' => $record['shortname'],
                                'question' => $record['question'],
                                'explanation' => $record['explanation'],
                                'updated_dt' => $record['updated_dt'],
                                'replacement_dt' => $updated_dt,
                            ))
                            ->execute();
                    
                    //Delete the existing question block
                    $result = db_delete('raptor_qa_criteria')
                                ->condition('shortname', $original_shortname, '<')
                                ->execute();                
                    $successmsg = "Deleted question $original_shortname";
                } else
                if($shortname > '' && $anythingchanged)
                {
                    $onlypositionchanged = (!$delta_shortname && !$delta_question && !$delta_explanation);
                    if($onlypositionchanged)
                    {
                        //Simple change the position of the existing record
                        db_merge('raptor_qa_criteria')
                            ->key(
                                    array('shortname'=>$original_shortname
                                ))
                            ->fields(array(
                                    'position'=>$position,
                                    'updated_dt'=>$updated_dt,
                                ))
                            ->execute();             
                        $successmsg = "Moved question $shortname to position $position";
                    } else {
                        //Preserve existing record and create a new one
                        $oInsert = db_insert('raptor_qa_criteria_replaced')
                                ->fields(array(
                                    'context_cd' => 'T',
                                    'version' => $record['version'],
                                    'position' => $record['position'],
                                    'shortname' => $record['shortname'],
                                    'question' => $record['question'],
                                    'explanation' => $record['explanation'],
                                    'updated_dt' => $record['updated_dt'],
                                    'replacement_dt' => $updated_dt,
                                ))
                                ->execute();
                        $newversion = $record['version'] + 1;
                        db_merge('raptor_qa_criteria')
                            ->key(
                                    array('shortname'=>$original_shortname
                                ))
                            ->fields(array(
                                    'version' => $newversion,
                                    'position' => $position,
                                    'shortname' => $shortname,
                                    'question' => $question,
                                    'explanation' => $explanation,
                                    'updated_dt'=>$updated_dt,
                                ))
                            ->execute();             
                        if($delta_shortname)
                        {
                            $successmsg = "Replaced question $original_shortname with $shortname";
                        } else {
                            $successmsg = "Changed contents of question $shortname";
                        }
                    }
                }
            } else {
                //Simply add the new question
                db_insert('raptor_qa_criteria')
                        ->fields(array(
                        'context_cd' => 'T',
                        'version' => 1,
                        'position' => $position,
                        'shortname' => $shortname,
                        'question' => $question,
                        'explanation' => $explanation,
                        'updated_dt' => $updated_dt,
                  ))->execute();               
                $successmsg = "Created question $shortname";
            }
            return $successmsg;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateDatabase($form, $myvalues)
    {
        try
        {
            $messages = array();
            $questionblocks = $myvalues['questions'];
            foreach($questionblocks as $record)
            {
                $successmsg = $this->updateOneQuestion($record);
                if($successmsg > '')
                {
                    $messages[] = $successmsg;
                }
            }
            $changecount = count($messages);
            if($changecount > 0)
            {
                $changesmarkup = "<ul>".implode('<li>',$messages)."</ul>";
                if($changecount > 1)
                {
                    drupal_set_message("Successfully saved $changecount QA question blocks$changesmarkup");
                } else {
                    drupal_set_message("Successfully saved 1 QA question block$changesmarkup");
                }
            } else {
                drupal_set_message("Nothing in QA questions blocks was changed",'warn');
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state
            , $disabled
            , $myvalues)
    {
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='raptor-list-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        
        $aGeneralHelpText[] = 'The QA questions are presented to authorized users once an exam has been completed in RAPTOR.';
        $aGeneralHelpText[] = '<b>If you are changing the meaning or character of a question it is important that you also change '
                . 'the "shortname" value of that question otherwise metrics for existing QA evaluations will be mischaracterized.</b>';
        
        $evaltermsblurb = 'When composing questions, bear in mind that the scoring is as follows:<ul>';
        $qa_scores_map = \raptor\TermMapping::getQAScoreLanguageMapping();
        foreach($qa_scores_map as $score=>$term)
        {
            $evaltermsblurb .= "<li>$score = $term";
        }
        $evaltermsblurb .= '</ul>';
        $aGeneralHelpText[] = $evaltermsblurb;
        $aGeneralHelpText[] = 'Blank fields are always added to the bottom of this page so you can create a new question.  '
                . 'To create multiple new questions, save this page multiple times, once for each new question you are adding.';
        $aGeneralHelpText[] = 'To DELETE a question from RAPTOR, delete ALL the field values and the question will be '
                . 'removed when you save the page.';
        
        $infonum=0;
        foreach($aGeneralHelpText as $oneitem)
        {
            $infonum++;
            $form['data_entry_area1']['instructions']['custominfo'.$infonum] = array(
                '#markup'         => "<p>$oneitem</p>",
            );        
        }

        $form['data_entry_area1']['qaconfig']['questions'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Replace existing QA Questions'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#tree' => TRUE,
            '#disabled' => $disabled,
        );
        
        //Get all the question markup now
        $questionmarkup = $this->getQuestionMarkup($disabled, $myvalues);
        foreach($questionmarkup as $key=>$elementblock)
        {
            $form['data_entry_area1']['qaconfig']['questions'][$key] = $elementblock;
        }
        
        //Action buttons now.
        $form['data_entry_area1']['qaconfig']['savechanges'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save QA Question Changes')
                , '#disabled' => $disabled
            );

        $goback = $this->getGobacktoURL();
        $form['data_entry_area1']['action_buttons']['cancel'] = FormHelper::getExitButtonMarkup($goback);
        return $form;
    }
    
 }
