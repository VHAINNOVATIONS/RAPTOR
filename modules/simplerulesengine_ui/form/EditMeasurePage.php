<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
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
 * ------------------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

require_once 'MeasurePageHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditMeasurePage
{
    protected $m_measure_nm        = NULL;
    protected $m_oSREngine      = NULL;
    protected $m_oSREContext    = NULL;
    protected $m_urls_arr          = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_sMeasureclassname = NULL;
    
    function __construct($measure_nm, $oSREngine, $urls_arr, $sMeasureclassname=NULL)
    {
        if (!isset($measure_nm) || is_numeric($measure_nm)) 
        {
            throw new \Exception("Missing or invalid measure_nm value = " 
                    . $measure_nm);
        }
        $this->m_measure_nm = $measure_nm;
        $this->m_oSREngine = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr = $urls_arr;
        $this->m_sMeasureclassname = $sMeasureclassname;
        $this->m_oPageHelper = new \simplerulesengine\MeasurePageHelper($oSREngine, $urls_arr, $sMeasureclassname);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_measure_nm);
    }
    
    /**
     * Validate the proposed values.
     * @return true if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->looksValid($form, $myvalues, 'E');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $tablename = $this->m_oSREContext->getMeasureTablename();
            $updated_dt = date("Y-m-d H:i", time());
            try
            {
                if(!isset($myvalues['readonly_yn']))
                {
                    $myvalues['readonly_yn'] = 0;
                }
                $nUpdated = db_update($tablename)->fields(array(
                      'measure_nm' => strtoupper($myvalues['measure_nm']),
                      'category_nm' => $myvalues['category_nm'],
                      'version' => $myvalues['version'],
                      'active_yn' => $myvalues['active_yn'],
                      'purpose_tx' => $myvalues['purpose_tx'],
                      'return_type' => $myvalues['return_type'],
                      'readonly_yn' => $myvalues['readonly_yn'],
                      'criteria_tx' => trim($myvalues['criteria_tx']),
                      'created_dt' => $updated_dt,
                      'updated_dt' => $updated_dt,
                    ))
                        ->condition('measure_nm', $myvalues['measure_nm'],'=')
                        ->execute(); 
            } catch (\Exception $ex) {
                  error_log("Failed to add measure into database!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                  drupal_set_message(t('Failed to save edited measure because ' . $ex->getMessage()));
                  return 0;
            }
            if ($nUpdated !== 1) 
            {
                error_log("Failed to edit user back to database!\n" . var_dump($myvalues));
                drupal_set_message(t('Updated ' . $nUpdated . ' records instead of 1!'));
                return 0;
            }

            //Returns 1 if everything was okay.
            drupal_set_message(t('Saved update for ' . $myvalues['measure_nm']));
            return $nUpdated;
        } catch (\Exception $ex) {
            throw $ex;
        }
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
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
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
        
        $form = $this->m_oPageHelper->getForm('E',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);
        

        $measure_nm = $myvalues['measure_nm'];

        //Hidden values for key fields
        $form['hiddenthings']['measure_nm'] 
            = array('#type' => 'hidden', '#value' => $measure_nm, '#disabled' => FALSE);        
        $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
        $form['hiddenthings']['version'] 
            = array('#type' => 'hidden', '#value' => $newversionnumber, '#disabled' => FALSE);        
        $showfieldname = 'show_measure_nm';


        //Do NOT let the user edit these values...
        $form["data_entry_area1"][$showfieldname]     = array(
            '#type' => 'textfield',
            '#title' => t('Measure Name'),
            '#value' => $measure_nm,
            '#size' => 40,
            '#maxlength' => 40,
            '#required' => TRUE,
            '#description' => t('Must be unique'),
            '#disabled' => TRUE
        );
        $newversionnumber = (isset($myvalues['version']) ? $myvalues['version'] + 1 : 1);
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
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => '<div class="'.$html_classname_overrides['action-buttons'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array($html_classname_overrides['action-button'])
            ),
            '#value' => t('Save Measure Updates'),
            '#disabled' => FALSE
        );

        global $base_url;
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $base_url . '/'. $this->m_urls_arr['return'];
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => '<a class="'.$html_classname_overrides['action-button'].'" href="'.$returnURL.'" >'.t('Cancel').'</a>');
        }
        
        return $form;
    }
}
