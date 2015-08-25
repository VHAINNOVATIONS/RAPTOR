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

module_load_include('inc','raptor_contraindications','core/ContraIndEngine');

/**
 * This class returns the configuration edit page
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditContraIndicationsConfigPage
{

    public function __construct()
    {
        module_load_include('php','raptor_datalayer','core/Context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->isSiteAdministrator() || !$oUserInfo->hasPrivilege('ECIR1'))
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
        
        $aGeneralHelpText[] = 'The contraindication rules control when and if patient care users of RAPTOR are notified or warned of potential patient safety issues.';
        
        $infonum=0;
        foreach($aGeneralHelpText as $oneitem)
        {
            $infonum++;
            $form['data_entry_area1']['instructions']['custominfo'.$infonum] = array(
                '#markup'         => "<p>$oneitem</p>",
            );        
        }

        $form['data_entry_area1']['downloadexistingrules'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Download existing rules'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['downloadexistingrules']['info'] = array(
            '#markup' => "<p>Downloading the existing rules does NOT change any of the rules in RAPTOR.  "
            . "Save the downloaded rules in a safe place.</p>"
            . "",
        );        
        
        $form['data_entry_area1']['downloadexistingrules']['link'] = array(
            '#prefix' => "<ul>",
            '#suffix' => "<ul>",
            );        
        
        global $base_url;
        $exportxml = "$base_url/raptor/contraindications/exportxml";
        $form['data_entry_area1']['downloadexistingrules']['link']['xml'] = array(
            '#markup' => "<li><a href='$exportxml'>Download existing rules model as an XML data file</a>",
        );        
        
        $exportraw = "$base_url/raptor/contraindications/exportdata";
        $form['data_entry_area1']['downloadexistingrules']['link']['raw'] = array(
            '#markup' => "<li><a href='$exportraw'>Download existing rules model as a RAW data file</a>",
        );        
        
        /*
        $form['data_entry_area1']['replacerules'] = array(
            '#type'     => 'fieldset',
            '#title'    => t('Replace existing rules'),
            '#attributes' => array(
                'class' => array(
                    'data-entry1-area'
                )
             ),
            '#disabled' => $disabled,
        );
        
        $form['data_entry_area1']['replacerules']['warning'] = array(
            '#markup' => "<p>Be careful replacing or resetting the contraindication rules.  "
                        . "This should ONLY be done after all stakeholders "
                        . "have been consulted and proper procedures followed.</p>",
        );        
        
        $form['data_entry_area1']['replacerules']['content'] = array(
            '#name' => 'files[replacementrules]',
            '#type' => 'file', 
            '#title' => t('Choose a replacement rules model'),
            '#size' => 60,
            '#description' => t('Upload a replacement to the current contraindication rules'), 
            );        
        
        $form['data_entry_area1']['replacerules']['savechanges'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save Contraindication Rule Changes')
                , '#validate' => array('raptor_contraindications_rulesreplace_customvalidate')
                , '#disabled' => $disabled
            );
        */
        
        return $form;
    }
    
 }
