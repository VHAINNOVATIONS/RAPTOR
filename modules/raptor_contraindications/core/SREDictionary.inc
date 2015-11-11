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
 * The Contraindications Engine takes input about the patient, the ticket, and current 
 * values selections to determine what, if any, contraindications should be reported.
 * 
 */

namespace raptor_ci;

$loaded = module_load_include('inc','simplerulesengine_core','core/ASREDictionary');
if(!$loaded)
{
    drupal_set_message('Failed to load the ASREDictionary class', 'error');
}

/**
 * Metadata about the coded rule inputs.
 *
 * @author Frank Font of SAN Business Consultants
 */
class SREDictionary extends \simplerulesengine\ASREDictionary
{

    /**
     * Return array of descriptions for each programmer coded input.
     */
    public function getDefinitionOfAllCodedInputs()
    {
        $aAllInputs = array();
        
        //These are provided when the parser is instantiated.
        $aAllInputs['GENDER'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'single letter',
            'context'=>'patient',
            'category_nm'=>'Demographics',
            'purpose_tx'=>'Indicate gender as M for mail and F for female.',
        );
        $aAllInputs['AGE'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Demographics',
            'purpose_tx'=>'Indicate current age in years.',
        );
        $aAllInputs['WEIGHT_KG'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'General',
            'purpose_tx'=>'Indicate current weight in kg.',
        );
        $aAllInputs['LATEST_EGFR'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'Most recent available eGFR value regardless of date measured.',
        );
        $aAllInputs['MIN_EGFR_10DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 10 days.',
        );
        $aAllInputs['MIN_EGFR_10DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 10 days.',
        );
        $aAllInputs['MIN_EGFR_15DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 15 days.',
        );
        $aAllInputs['MIN_EGFR_30DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 30 days.',
        );
        $aAllInputs['MIN_EGFR_45DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 45 days.',
        );
        $aAllInputs['MIN_EGFR_60DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 60 days.',
        );
        $aAllInputs['MIN_EGFR_90DAYS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'Renal',
            'purpose_tx'=>'The lowest eGFR measured within the last 90 days.',
        );
        
        //These are provided for every evaluation.
        $aAllInputs['IS_DIAGNOSTIC_EXAM'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if the selected protocol is for a diagnostic exam, else False.',
        );

        $aAllInputs['IS_INPATIENT'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if INPATIENT status, else False.',
        );
        $aAllInputs['IS_OUTPATIENT'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if OUTPATIENT status, else False.',
        );
        $aAllInputs['IS_IMG_GUIDED_EXAM'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if the selected protocol is for an image guided exam, else False.',
        );
        $aAllInputs['IS_POSSIBLE_DUP_PROC'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if the selected order is a possible duplicate of another order, else False.',
        );
        $aAllInputs['PROC_NM'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'text',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'This is the procedure name associated with the order.',
        );
        $aAllInputs['MODALITY'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'two letters',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'This is the modality abbreviation associated with the selected protocol.',
        );
        $aAllInputs['GIVE_HYDRATION_ORAL'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if oral hydration has been selected.',
        );
        $aAllInputs['GIVE_HYDRATION_IV'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if IV hydration has been selected.',
        );
        $aAllInputs['GIVE_CONTRAST_ENTERIC'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if enteric contrast has been selected.',
        );
        $aAllInputs['GIVE_CONTRAST_IV'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if IV contrast has been selected.',
        );
        $aAllInputs['GIVE_SEDATION_ORAL'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if oral sedation has been selected.',
        );
        $aAllInputs['GIVE_SEDATION_IV'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if IV sedation has been selected.',
        );
        $aAllInputs['GIVE_RADIOISOTOPE_ENTERIC'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if enteric radioisotope has been selected.',
        );
        $aAllInputs['GIVE_RADIOISOTOPE_IV'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if IV radioisotope has been selected.',
        );
        $aAllInputs['IS_CLAUSTROPHOBIC'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Behavior',
            'purpose_tx'=>'True if the patient has been identified as claustrophobic.',
        );
        $aAllInputs['HAS_ALLERGY'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Allergy',
            'purpose_tx'=>'True there is indication that the patient may have an allergy.  This flag does not indicate any specific type of allergy.',
        );
        $aAllInputs['CURRENT_MEDS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'array of text',
            'context'=>'candidate',
            'category_nm'=>'Doses',
            'purpose_tx'=>'This is an array of all the medications associated with the patient.',
        );
        $aAllInputs['CURRENT_ALLERGIES'] = array(
            'source'=>'coded',
            'criteria_tx'=>'DERIVED',
            'return'=>'array of text',
            'context'=>'candidate',
            'category_nm'=>'Allergy',
            'purpose_tx'=>'This is an array of all the allergies associated with the patient.',
        );
        $aAllInputs['CURRENT_RADIOISOTOPES'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'array of text',
            'context'=>'candidate',
            'category_nm'=>'Doses',
            'purpose_tx'=>'This is an array of all the radioisotopes selected for the patient.',
        );
        $aAllInputs['CURRENT_CONTRASTS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'array of text',
            'context'=>'candidate',
            'category_nm'=>'Doses',
            'purpose_tx'=>'This is an array of all the contrasts selected for the patient.',
        );
        
        return $aAllInputs;
    }
    
}
