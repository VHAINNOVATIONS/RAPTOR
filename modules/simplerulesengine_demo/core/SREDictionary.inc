<?php
/*
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

namespace simplerulesengine_demo;

$loaded = module_load_include('inc','simplerulesengine_core','core/ASREDictionary');
if(!$loaded)
{
    drupal_set_message(t('Failed to load the ASREDictionary class'), 'error');
}

/**
 * Metadata about the coded rule inputs.
 *
 * @author Frank Font
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
        $aAllInputs['WEIGHT_KG'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'number',
            'context'=>'patient',
            'category_nm'=>'General',
            'purpose_tx'=>'Indicate current weight in kg.',
        );
        
        $aAllInputs['IS_CLAUSTROPHOBIC'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Behavior',
            'purpose_tx'=>'True if the patient has been identified as claustrophobic.',
        );
        $aAllInputs['HAS_CONTRAST_ALLERGY'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Allergy',
            'purpose_tx'=>'True there is indication that the patient may have an allergy.  This flag does not indicate any specific type of allergy.',
        );
        $aAllInputs['CURRENT_MEDS'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'array of text',
            'context'=>'candidate',
            'category_nm'=>'Doses',
            'purpose_tx'=>'This is an array of all the medications associated with the patient.',
        );
        
        $aAllInputs['GIVE_XRAY'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if X-RAY procedure will be performed.',
        );
        
        $aAllInputs['GIVE_MRI'] = array(
            'source'=>'coded',
            'criteria_tx'=>'INPUT',
            'return'=>'boolean',
            'context'=>'candidate',
            'category_nm'=>'Exam',
            'purpose_tx'=>'True if MRI procedure will be performed.',
        );

        return $aAllInputs;
    }
    
}
