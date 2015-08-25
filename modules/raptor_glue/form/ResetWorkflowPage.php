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
 */ 

namespace raptor;

module_load_include('php', 'raptor_datalayer', 'config/Choices');
//module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
require_once 'FormHelper.php';

/**
 * Implements the reset workflow page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ResetWorkflowPage extends \raptor\ASimpleFormPage
{
    private $m_oContext = null;
    private $m_oTT = null;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $tid = $this->m_oContext->getSelectedTrackingID();
        if($tid == NULL || trim($tid) == '' || trim($tid) == 0)
        {
            throw new \Exception('Missing selected ticket number!  (If using direct, try overridetid.)');
        }
        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();    //$tid);
        $ehrDao = $this->m_oContext->getEhrDao();
        $aOneRow = $ehrDao->getDashboardDetailsMap();
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        $myvalues['new_wfs'] = '';
        $myvalues['notes_tx'] = '';
        
        //TODO: Pre-populate values for display
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        //TODO
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        //TODO
        //Write success message
        drupal_set_message('TODO CHANGE WORKFLOW ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        
        return TRUE;
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        //Declare the hidden things
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        
        //Declare the non-hidden things
        $aResetOptions = array('active'=>'Needs protocol','protocoled'=>'Protocoled');
        $form['data_entry_area1']['toppart']['new_wfs'] = array(
            "#type" => "select",
            "#title" => t("New Workflow State"),
            "#options" => $aResetOptions,
            "#description" => t("Select the workflow state to which you want to set the ticket"),
            "#required" => TRUE,
            );        
        
        $form['data_entry_area1']['toppart']['notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Comments'),
            '#disabled'      => $disabled,
            '#default_value' => '',
        );
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['action_buttons']['resetworkflow'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Reset Workflow')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit with No Changes">');

//error_log("DEBUG LOOK getRadiologyOrderDialog...\n".print_r($form,TRUE));
//die('LOOK HERE2222>>>'.print_r($myvalues,TRUE));
        
        return $form;
    }
}

