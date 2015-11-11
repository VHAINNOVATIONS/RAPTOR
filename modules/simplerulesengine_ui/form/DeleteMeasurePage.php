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
class DeleteMeasurePage
{
    protected $m_measure_nm     = NULL;
    protected $m_oSREngine      = NULL;
    protected $m_oSREContext    = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_sMeasureclassname = NULL;
    
    function __construct($measure_nm, $oSREngine, $urls_arr, $sMeasureclassname=NULL)
    {
        if (!isset($measure_nm) || is_numeric($measure_nm)) 
        {
            throw new \Exception("Missing or invalid measure_nm value = " 
                    . $measure_nm);
        }
        $this->m_measure_nm     = $measure_nm;
        $this->m_oSREngine = $oSREngine;
        $this->m_oSREContext = $oSREngine->getSREContext();
        $this->m_urls_arr = $urls_arr;
        $this->m_sMeasureclassname = $sMeasureclassname;
        $this->m_oPageHelper = new \simplerulesengine\MeasurePageHelper($oSREngine, $urls_arr, $sMeasureclassname);
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_measure_nm);
    }

    /**
     * Remove the record IF there are no records referencing this user.
     */
    function updateDatabase($form, $myvalues)
    {
        $measure_nm = $myvalues['measure_nm'];
        $num_deleted = 0;
        try
        {
            $tablename = $this->m_oSREContext->getMeasureTablename();
            $num_deleted = db_delete($tablename)
              ->condition('measure_nm', $measure_nm)
              ->execute();            
        } catch (\Exception $ex) {
            error_log("Failed to delete measure '$measure_nm' from database!\n" . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            drupal_set_message(t('Failed to delete measure '.$measure_nm.' because ' . $ex->getMessage()));
            return 0;
        }

        //Success?  
        if($num_deleted == 1)
        {
            if($this->m_sMeasureclassname !== NULL)
            {
                $feedback = 'Deleted '.$this->m_sMeasureclassname.' ' . $measure_nm;
            } else {
                $feedback = 'Deleted '.$measure_nm;
            }
            drupal_set_message($feedback);
            return 1;
        } else {
            //We are here because we failed.
            if($this->m_sMeasureclassname !== NULL)
            {
                $feedback = 'Trouble deleting '.$this->m_sMeasureclassname.' ' . $measure_nm;
            } else {
                $feedback = 'Trouble deleting '.$measure_nm;
            }
            error_log($feedback . ' delete reported ' . $num_deleted);
            drupal_set_message($feedback, 'warning');
            return 0;
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
        $disabled = TRUE;
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Replace the buttons
        if($this->m_sMeasureclassname !== NULL)
        {
            $buttontext = 'Delete '.$this->m_sMeasureclassname.' Measure From System';
        } else {
            $buttontext = 'Delete Measure From System';
        }
        $form["data_entry_area1"]['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-buttons']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
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
