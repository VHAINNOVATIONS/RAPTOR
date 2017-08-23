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

/**
 * This class returns the configuration edit page
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditGlueConfigPage
{

    public function __construct()
    {
        module_load_include('php','raptor_datalayer','core/Context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->isSiteAdministrator())
        {
            throw new \Exception('The user account does not have privileges for this page.');
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
        
        $aGeneralHelpText[] = 'This module connects the collection of RAPTOR modules together.';
        
        $infonum=0;
        foreach($aGeneralHelpText as $oneitem)
        {
            $infonum++;
            $form['data_entry_area1']['instructions']['custominfo'.$infonum] = array(
                '#markup'         => "<p>$oneitem</p>",
            );        
        }

        $form['data_entry_area1']['downloadexistingusers'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Download existing user accounts'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['downloadexistingusers']['info'] = array(
            '#markup' => "<p>Downloading the existing user accounts does NOT change any of the accounts in RAPTOR.  "
            . "Save the downloaded user accounts in a safe place.</p>"
            . "",
        );        
        
        $form['data_entry_area1']['downloadexistingusers']['link'] = array(
            '#prefix' => "<ul>",
            '#suffix' => "<ul>",
            );        
        
        global $base_url;
        $exportxml = "$base_url/raptor/users/exportxml";
        $form['data_entry_area1']['downloadexistingusers']['link']['xml'] = array(
            '#markup' => "<li><a href='$exportxml'>Download existing user accounts as an XML data file</a>",
        );        
        
        $exportraw = "$base_url/raptor/users/exportdata";
        $form['data_entry_area1']['downloadexistingusers']['link']['raw'] = array(
            '#markup' => "<li><a href='$exportraw'>Download existing user accounts as a RAW data file</a>",
        );        
        
        /*
        $form['data_entry_area1']['replaceusers'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Replace existing users'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $form['data_entry_area1']['replaceusers']['warning'] = array(
            '#markup' => "<p>Be careful replacing or resetting the contraindication users.  "
                        . "This should ONLY be done after all stakeholders "
                        . "have been consulted and proper procedures followed.</p>",
        );        
        
        $form['data_entry_area1']['replaceusers']['content'] = array(
            '#name' => 'files[replacementusers]',
            '#type' => 'file', 
            '#title' => t('Choose a replacement users model'),
            '#size' => 60,
            '#description' => t('Upload a replacement to the current contraindication users'), 
            );        
        
        $form['data_entry_area1']['replaceusers']['savechanges'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save Contraindication Rule Changes')
                , '#validate' => array('raptor_contraindications_usersreplace_customvalidate')
                , '#disabled' => $disabled
            );
        */
        
        return $form;
    }
    
 }
