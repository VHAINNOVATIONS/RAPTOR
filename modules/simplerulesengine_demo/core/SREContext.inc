<?php
/*
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

require_once ('SREDictionary.inc');

$loaded = module_load_include('inc','simplerulesengine_core','core/ASREContext');
if(!$loaded)
{
    drupal_set_message(t('Failed to load the ASREContext class'), 'error');
}

/**
 * Context information for simple rules engine execution instance
 *
 * @author Frank Font
 */
class SREContext extends \simplerulesengine\ASREContext
{
    private $m_tablename_rule = 'simplerulesengine_demo_rule';
    private $m_tablename_measure = 'simplerulesengine_demo_measure';
    private $m_oDictionary = NULL;
    
    public function __construct()
    {
        $this->m_oDictionary = new \simplerulesengine_demo\SREDictionary($this);
    }
    
    /**
     * @return name of rule database table
     */
    public function getRuleTablename()
    {
        return $this->m_tablename_rule;
    }
    
    /**
     * @return name of measure database table
     */
    public function getMeasureTablename()
    {
        return $this->m_tablename_measure;
    }

    /**
     * The constructor requires an array with these keys.
     * @return array of names
     */
    public function getRequiredBaselineInfoKeys()
    {
        $aNames = array(
             'GENDER'
            ,'AGE'
            ,'WEIGHT_KG'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * The evaluator requires an array with these keys.
     * @return array of names
     */
    public function getRequiredCandidateDataKeys()
    {
        $aNames = array(
             'IS_CLAUSTROPHOBIC'
            ,'HAS_CONTRAST_ALLERGY'
            ,'CURRENT_MEDS'
            ,'GIVE_SEDATION'
            ,'GIVE_CONTRAST'
            ,'GIVE_XRAY'
            ,'GIVE_MRI'
            );
        foreach($aNames as $sName)
        {
            $aRequiredKeys[$sName] = $sName; 
        }
        return $aRequiredKeys;
    }
    
    /**
     * Return the dictionary instance that expains all of the coded inputs.
     */
    public function getDictionary()
    {
        return $this->m_oDictionary;
    }
}
