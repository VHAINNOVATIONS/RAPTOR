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
require_once 'FormHelper.php';
require_once 'UserPageHelper.php';
require_once 'ChildEditBasePage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewUserPage extends \raptor\ChildEditBasePage
{
    private $m_oPageHelper = NULL;
    private $m_nUID = NULL;
    private $m_oContext = NULL;
    
     //Call same function as in EditUserPage here!
    function __construct($nUID)
    {
        if(!isset($nUID) || !is_numeric($nUID))
        {
            die("Missing or invalid uid value = " . $nUID);
        }
        $this->m_nUID = $nUID;
        $this->m_oPageHelper = new \raptor\UserPageHelper();
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oPageHelper->checkAllowedToViewUser($this->m_oContext, $this->m_nUID);
        
        //Set the default gobackto url now
        global $base_url;
        $this->setGobacktoURL($base_url.'/raptor/manageusers');
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_nUID);
        $myvalues['formmode'] = 'V';
        return $myvalues;
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
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form = $this->m_oPageHelper->getForm($form, $form_state, TRUE, $myvalues);

        //Replace the intro blurb
        $form['data_entry_area1']['introblurb'] = NULL;        
        
        //Replace the username input
        $form['data_entry_area1']['leftpart']['username'] = array(
          '#type' => 'textfield', 
          '#title' => t('Login Name'), 
          '#default_value' => $myvalues['username'], 
          '#size' => 40, 
          '#maxlength' => 128, 
          '#required' => TRUE,
          '#description' => t('The login name of the user.  This must match their VISTA login name.'),
          '#disabled' => TRUE,
        );        
        
        //Replace the buttons
        $form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        //Replace the buttons
        $goback = $this->getGobacktoFullURL();
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Exit"'
                . ' data-redirect="'.$goback.'">');
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = FormHelper::getExitButtonMarkup($goback);
        return $form;
    }
}
