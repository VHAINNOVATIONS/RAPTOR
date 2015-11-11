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

namespace simplerulesengine_demo;

require_once (dirname(__FILE__) . '/../core/EvalEngine.inc');

/**
 * This is a demonstration page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class DemoEvaluatePage
{
    
    function getFieldValues()
    {
        $myvalues = array();    
        $myvalues['GENDER'] = NULL;
        $myvalues['AGE'] = NULL;
        $myvalues['WEIGHT_KG'] = NULL;
        $myvalues['IS_CLAUSTROPHOBIC'] = NULL;
        $myvalues['HAS_CONTRAST_ALLERGY'] = NULL;
        $myvalues['CURRENT_MEDS'] = array();
        $myvalues['GIVE_SEDATION'] = NULL;
        $myvalues['GIVE_CONTRAST'] = NULL;
        $myvalues['GIVE_XRAY'] = NULL;
        $myvalues['GIVE_MRI'] = NULL;
        return $myvalues;
    }
    
    function looksValid($form, $myvalues)
    {
        return TRUE;
    }

    /**
     * Return html markup of the source information
     */
    private function getSourcesMarkup($aResultSrc, &$bRequireConfirm)
    {
        $html = '';
        foreach($aResultSrc as $oSrc)
        {
            $html .= '<li>'.$oSrc->getExplanation();
            if($oSrc->isConfirmationRequired())
            {
                $bRequireConfirm = TRUE;
            }
        }
        return $html;
    }
    
    /**
     * Evaluate the rules against the values.
     */
    function evaluate($form, $myvalues)
    {
        $aBaselineInfo = array();    
        $aBaselineInfo['GENDER'] = $myvalues['GENDER'];
        $aBaselineInfo['AGE'] = $myvalues['AGE'];
        $aBaselineInfo['WEIGHT_KG'] = $myvalues['WEIGHT_KG'];
        try
        {
            $oSRE = new \simplerulesengine_demo\EvalEngine($aBaselineInfo);        
        } catch (\Exception $ex) {
            $oSRE = NULL;
            drupal_set_message(t('Failed to create the simple rules engine because ' . $ex->getMessage()), 'error');
        }
        if($oSRE != NULL)
        {
            $nWarnings = 0;
            $aCandidateData = array();  
            $aCandidateData['IS_CLAUSTROPHOBIC'] = $myvalues['IS_CLAUSTROPHOBIC'];
            $aCandidateData['HAS_CONTRAST_ALLERGY'] = $myvalues['HAS_CONTRAST_ALLERGY'];
            $aCandidateData['CURRENT_MEDS'] = $myvalues['CURRENT_MEDS'];
            $aCandidateData['GIVE_SEDATION'] = $myvalues['GIVE_SEDATION'];
            $aCandidateData['GIVE_CONTRAST'] = $myvalues['GIVE_CONTRAST'];
            $aCandidateData['GIVE_XRAY'] = $myvalues['GIVE_XRAY'];
            $aCandidateData['GIVE_MRI'] = $myvalues['GIVE_MRI'];
            
            //drupal_set_message('>>> CANDIDATE DATA>>> '.print_r($aCandidateData,TRUE));          
            //Now invoke the rules engine.
            try
            {
                $oResults = $oSRE->getResults($aCandidateData);
                $aResults = $oResults->getAll();
                //Group identical summary messages together.
                $aGroupedResults = array();
                foreach($aResults as $oOneResult)
                {
                    $sSummaryMsg = $oOneResult->getSummaryMessage();
                    if(!isset($aGroupedResults[$sSummaryMsg]))
                    {
                        $aGroupedResults[$sSummaryMsg] = array();
                    }
                    $aGroupedResults[$sSummaryMsg][] = $oOneResult;
                    $nWarnings++;
                }
                foreach($aGroupedResults as $key=>$aOneResultGroup)
                {
                    $sFormattedMsg = $key;
                    $sFormattedMsg .= '<ul>';
                    $bRequireConfirm = FALSE;
                    foreach($aOneResultGroup as $oOneResult)
                    {
                        $aResultSrc = $oOneResult->getResultSource();
                        $sSrcHtml = $this->getSourcesMarkup($aResultSrc,$bRequireConfirm);
                        $sFormattedMsg .= $sSrcHtml;
                    }
                    if($bRequireConfirm)
                    {
                        //Just demonstrate confirmation element.
                        $sFormattedMsg .= '<li><input type="checkbox" name="demo-confirmed-'.$key.'" value="Confirmation"> Confirmation';
                    }
                    $sFormattedMsg .= '</ul>';
                    drupal_set_message($sFormattedMsg,'warning');
                }
                if($nWarnings == 0)
                {
                    drupal_set_message(t('No warnings detected'));
                }
                
            } catch (\Exception $ex) {
                $aResults = array();
                drupal_set_message(t('Failed to run the simple rules engine because ' . $ex->getMessage()),'error');
            }
        }
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
        
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );        

        $form["data_entry_area1"]['baselineinfo'] = array(
            '#type' => 'fieldset', 
            '#title' => t('Baseline Information'),
            '#collapsible' => TRUE, 
            '#collapsed' => FALSE, 
        );

        
        $aGender = array('M' => t('Male'), 'F' => t('Female'));
        $form['data_entry_area1']['baselineinfo']['GENDER'] = array(
            '#type' => 'radios',
            '#title' => t('Gender'),
            '#default_value' => $myvalues['GENDER'],
            '#options' => $aGender,
            '#description' => t('Gender of the patient.'),
            '#required' => TRUE,
           );
        $form['data_entry_area1']['baselineinfo']['AGE'] = array(
            '#type' => 'textfield', 
            '#title' => t('Age'), 
            '#default_value' => $myvalues['AGE'],
            '#size' => 3, 
            '#maxlength' => 3, 
            '#description' => t('Age of the patient.'),
            '#required' => TRUE,
            );
        $form['data_entry_area1']['baselineinfo']['WEIGHT_KG'] = array(
            '#type' => 'textfield', 
            '#title' => t('Weight (kg)'), 
            '#default_value' => $myvalues['WEIGHT_KG'],
            '#size' => 3, 
            '#maxlength' => 4, 
            '#description' => t('Weight of the patient in kg.'),
            '#required' => TRUE,
            );
        
        
        $form["data_entry_area1"]['candidateinfo'] = array(
            '#type' => 'fieldset', 
            '#title' => t('Second Level Information'),
            '#collapsible' => TRUE, 
            '#collapsed' => FALSE, 
        );
        
        $aYesNo = array(TRUE => t('Yes'), FALSE => t('No'));
        $form['data_entry_area1']['candidateinfo']['IS_CLAUSTROPHOBIC'] = array(
            '#type' => 'radios',
            '#title' => t('Is Claustrophobic'),
            '#default_value' => $myvalues['IS_CLAUSTROPHOBIC'],
            '#options' => $aYesNo,
            '#description' => t('Is the patient claustrophobic?'),
            '#required' => FALSE,
           );
        $form['data_entry_area1']['candidateinfo']['HAS_CONTRAST_ALLERGY'] = array(
            '#type' => 'radios',
            '#title' => t('Has Allergy to Contrast'),
            '#default_value' => $myvalues['HAS_CONTRAST_ALLERGY'],
            '#options' => $aYesNo,
            '#description' => t('Does the patient allergy to contrast?'),
            '#required' => FALSE,
           );
        $form['data_entry_area1']['candidateinfo']['CURRENT_MEDS'] = array(
            '#type' => 'checkboxes',
            '#title' => t('Current Medications'), 
            '#options' => drupal_map_assoc(array(('ASPIRIN'), ('WARFARIN'), ('HEPARIN'))),
            '#default_value' => $myvalues['CURRENT_MEDS'],
            '#required' => FALSE,
            );
        $form['data_entry_area1']['candidateinfo']['GIVE_SEDATION'] = array(
            '#type' => 'radios',
            '#title' => t('Give Sedation'),
            '#default_value' => $myvalues['GIVE_SEDATION'],
            '#options' => $aYesNo,
            '#description' => t('Administer sedation to the patient'),
            '#required' => FALSE,
           );
        $form['data_entry_area1']['candidateinfo']['GIVE_CONTRAST'] = array(
            '#type' => 'radios',
            '#title' => t('Give Contrast'),
            '#default_value' => $myvalues['GIVE_CONTRAST'],
            '#options' => $aYesNo,
            '#description' => t('Administer contrast to the patient'),
            '#required' => FALSE,
           );
        $form['data_entry_area1']['candidateinfo']['GIVE_XRAY'] = array(
            '#type' => 'radios',
            '#title' => t('X-RAY Procedure'),
            '#default_value' => $myvalues['GIVE_XRAY'],
            '#options' => $aYesNo,
            '#description' => t('Administer X-RAY exam to the patient'),
            '#required' => FALSE,
           );
        $form['data_entry_area1']['candidateinfo']['GIVE_MRI'] = array(
            '#type' => 'radios',
            '#title' => t('MRI Procedure'),
            '#default_value' => $myvalues['GIVE_MRI'],
            '#options' => $aYesNo,
            '#description' => t('Administer MRI exam to the patient'),
            '#required' => FALSE,
           );
     
        
        

       $form["data_entry_area1"]['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['action-buttons'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
       global $base_url;
       /*
       $form['data_entry_area1']['action_buttons']['viewinputs'] = array('#type' => 'item'
                , '#markup' => '<a href="'.$base_url.'/simplerulesengine_demo/viewinputs" >See All Measures</a>');
       $form['data_entry_area1']['action_buttons']['reportrules'] = array('#type' => 'item'
                , '#markup' => '<a href="'.$base_url.'/simplerulesengine_demo/reportrules" >See All Rules</a>');
       */
       $form['data_entry_area1']['action_buttons']['downloadxmlrulebase'] = array('#type' => 'item'
                , '#markup' => '<a href="'.$base_url.'/simplerulesengine_demo/exportxml" >Download Rulebase as XML</a>');
       
       $form['data_entry_area1']['action_buttons']['managemeasures'] = array('#type' => 'item'
                , '#markup' => '<a href="'.$base_url.'/simplerulesengine_demo/managemeasures" >Manage Measures</a>');
       
       $form['data_entry_area1']['action_buttons']['managerules'] = array('#type' => 'item'
                , '#markup' => '<a href="'.$base_url.'/simplerulesengine_demo/managerules" >Manage Rules</a>');
       
       $form['data_entry_area1']['action_buttons']['evaluate'] = array('#type' => 'submit', '#value' => t('Evaluate'));

       return $form;
    }
}
