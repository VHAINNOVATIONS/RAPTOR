<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2014 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.gnu.org/copyleft/gpl.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

require_once 'QuestionsPageHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class AskQuestionsPage
{
    protected $m_measure_name_ar    = NULL;
    protected $m_question_tables_ar = NULL;
    protected $m_oSREngine          = NULL;
    protected $m_oSREContext        = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    
    function __construct($measure_name_ar
            , $question_tables_ar
            , $oSREngine
            , $urls_arr)
    {
        if (!isset($measure_name_ar) || is_array($measure_name_ar)) 
        {
            throw new \Exception("Missing or invalid measure_name array = " 
                    . print_r($measure_name_ar,TRUE));
        }
        $this->m_measure_name_ar    = $measure_names_ar;
        if($question_tables_ar = NULL)
        {
            $question_tables_ar['main'] = 'simplerulesengine_measure_question';
            $question_tables_ar['choices'] = 'simplerulesengine_measure_question_choices';
            $question_tables_ar['validation'] = 'simplerulesengine_measure_question_validaton';
        }
        $this->m_question_tables_ar = $question_tables_ar;
        $this->m_oSREngine   = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr       = $urls_arr;
        $this->m_oPageHelper = new \simplerulesengine\QuestionsPageHelper($question_tables_ar, $oSREngine, $urls_arr);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_rule_nm);
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        return TRUE;
    }
    
    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled, $myvalues
            , $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['action-buttons'] = 'action-buttons';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        $disabled = FALSE; //They can edit the fields.
        
        $form = $this->m_oPageHelper->getForm($this->m_measure_name_ar
                , $form, $form_state
                , $disabled, $myvalues, $html_classname_overrides);
        

        $rule_nm = $myvalues['rule_nm'];

        //Hidden values for key fields
        $form['hiddenthings']['rule_nm'] 
            = array('#type' => 'hidden', '#value' => $rule_nm, '#disabled' => FALSE);        
        $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
        $form['hiddenthings']['version'] 
            = array('#type' => 'hidden'
                , '#value' => $newversionnumber, '#disabled' => FALSE);        
        $showfieldname = 'show_rule_nm';


        //Do NOT let the user edit these values...
        $form["data_entry_area1"][$showfieldname]     = array(
            '#type' => 'textfield',
            '#title' => t('Rule Name'),
            '#value' => $rule_nm,
            '#size' => 40,
            '#maxlength' => 40,
            '#required' => TRUE,
            '#description' => t('Must be unique'),
            '#disabled' => TRUE
        );
        $newversionnumber = 
                (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
        $form["data_entry_area1"]['show_version']     = array(
            '#type' => 'textfield',
            '#title' => t('Version Number'),
            '#value' => $newversionnumber,
            '#size' => 4,
            '#maxlength' => 4,
            '#required' => TRUE,
            '#description' => t('Increases each time change is saved'),
            '#disabled' => TRUE
        );

        
        
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item',
            '#prefix' => '<div class='
                . '"'.$html_classname_overrides['action-buttons'].'"'
                . '>',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array($html_classname_overrides['action-button'])
            ),
            '#value' => t('Save Rule Updates'),
            '#disabled' => FALSE
        );

        global $base_url;
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $base_url . '/'. $this->m_urls_arr['return'];
            $form['data_entry_area1']['action_buttons']['manage'] 
                    = array('#type' => 'item'
                    , '#markup' => 
                        '<a class='
                        . '"'.$html_classname_overrides['action-button'].'"'
                        . ' href="'.$returnURL.'" >'.t('Cancel').'</a>');
        }
        
        return $form;
    }
}
