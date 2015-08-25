<?php
/**
 * @file
 * ----------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font(ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano(acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  
 * Contact SAN to share updates.
 *
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ----------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

/**
 * This class helps create question forms
 *
 * @author Frank Font of SAN Business Consultants
 */
class QuestionsPageHelper
{
    protected $m_question_tables_ar = NULL;
    protected $m_oSREngine = NULL;
    protected $m_oSREContext = NULL;
    protected $m_urls_arr = NULL;
    
    public function __construct($question_tables_ar, $oSREngine, $urls_arr)
    {
        $this->m_question_tables_ar = $question_tables_ar;
        $this->m_oSREngine = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr = $urls_arr;
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues($measure_nm_ar)
    {
        //TODO
        $myvalues = array();
        return $myvalues;
    }
    
    function getQuestionsMarkup($allquestions_ar)
    {
        $form_markup = array();
        foreach($allquestions_ar as $measure_nm=>$question)
        {
            //TODO
            $form_markup[]['#markup'] = '<p>TODO input for '.$measure_nm.'</p>';
        }
        return $form_markup;
    }
    
    /**
     * Get the raw details needed to construct the questions.
     * @param array $measure_nm_ar
     * @return array
     */
    function getQuestionsDetail($measure_nm_ar)
    {
        
        $allquestions_ar = array();
        if(count($measure_nm_ar) > 0)
        {
            $measure_tn = $this->m_oSREContext->getMeasureTablename();
            $main_tn = $this->m_question_tables_ar['main'];
            $choices_tn = $this->m_question_tables_ar['choices'];
            $validation_tn = $this->m_question_tables_ar['validation'];
            
            $query = db_select($measure_tn,'r');
            $query->leftJoin($main_tn,'m','r.measure_nm = r.measure_nm');
            $query->leftJoin($validation_tn,'v','r.measure_nm = v.measure_nm');
            $query->fields('r',array('measure_nm'))
                   ->fields('m',array('question_significance'
                       ,'question_type_cd','question_tx','explanation_tx'
                       ,'answer_limit'))
                   ->fields('v',array('regex','maxfloat','minfloat'
                       ,'maxint','minint'))
                   ->condition('r.measure_nm',$measure_nm_ar,'IN')
                   ->orderBy('question_significance');
            $result = $query->execute();
            while($record = $result->fetchAssoc())
            {
                $name = $record['measure_nm'];
                $qa = array();
                $qa['question_significance'] = $record['question_significance'];
                $qa['question_type_cd'] = $record['question_type_cd'];
                $qa['question_tx'] = $record['question_tx'];
                $qa['explanation_tx'] = $record['explanation_tx'];
                $va = array();
                $va['answer_limit'] = $record['answer_limit'];
                $va['regex'] = $record['regex'];
                $va['maxfloat'] = $record['maxfloat'];
                $va['minfloat'] = $record['minfloat'];
                $va['maxint'] = $record['maxint'];
                $va['minint'] = $record['minint'];
                $qa['validation'] = $va;
                if($qa['question_type_cd'] == 'MC' || $qa['question_type_cd'] == 'SC')
                {
                    //Get all the choices
                    $qa['choices'] = array('TODO from '.$choices_tn);
                }
                $allquestions_ar[$name] = $qa;
            }
        }
        return $allquestions_ar;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        $aOptions = array();
        return $aOptions;
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function looksValid($form, &$myvalues, $formMode)
    {
        $bGood = TRUE;
        //TODO
        return $bGood;
    }
    
    /**
     * Get all the form contents for rendering
     * @param array $measure_name_ar
     * @param array $form
     * @param array $form_state
     * @param boolean $disabled
     * @param array $myvalues 
     * @param array $html_classname_overrides 
     * @return drupal renderable array
     * @throws \Exception
     */
    function getForm($measure_name_ar, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            $html_classname_overrides = array();
        }
        if(!isset($html_classname_overrides['data-entry-area1']))
        {
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        }
        if(!isset($html_classname_overrides['selectable-text']))
        {
            $html_classname_overrides['selectable-text'] = 'selectable-text';
        }
        $aOptions = $this->getAllOptions();
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );   
            
        $allquestions_ar = $this->getQuestionsDetail($measure_name_ar);
        $questions = $this->getQuestionsMarkup($allquestions_ar);
        $form['data_entry_area1']['questions'] = $questions;   
        
        return $form;
    }
}
