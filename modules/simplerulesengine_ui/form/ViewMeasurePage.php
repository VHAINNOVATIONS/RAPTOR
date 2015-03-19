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

require_once 'MeasurePageHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewMeasurePage
{
    protected $m_measure_nm        = NULL;
    protected $m_oSREngine      = NULL;
    protected $m_oSREContext    = NULL;
    protected $m_urls_arr          = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_sMeasureclassname = NULL;
    
    function __construct($measure_nm, $oSREngine, $urls_arr, $sMeasureclassname=NULL)
    {
        if (!isset($measure_nm) || is_numeric($measure_nm)) {
            die("Missing or invalid measure_nm value = " . $measure_nm);
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
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('V',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);
        
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => '<div class="'.$html_classname_overrides['action-buttons'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        if(isset($this->m_urls_arr['return']))
        {
            global $base_url;
            $returnURL = $base_url . '/'. $this->m_urls_arr['return'];
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => '<a class="'.$html_classname_overrides['action-button'].'" href="'.$returnURL.'" >'.t('Cancel').'</a>');
        }
        return $form;
    }
}
