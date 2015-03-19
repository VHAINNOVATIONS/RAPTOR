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

/**
 * Manage a collection of results
 *
 * @author Frank Font
 */
class SREResults 
{
    
    private $m_results_array=array();
    
    /**
     * Provide an array of results.
     * @param type $results_array 
     */
    public function __construct($results_array)
    {
        $this->m_results_array=$results_array;
    }
    
    /**
     * Get all the contra indications
     * @param type $category_tx
     * @return array of instances
     */
    public function getAll()
    {
        return $this->m_results_array;
    }
    
    /**
     * Get all the contra indications whree a confirmation is required.
     * @return type 
     */
    public function getAllConfirmationRequired()
    {
        $aJustCR=array();
        foreach($this->m_results_array as $oCI)
        {
            if($oCI->isConfirmationRequired())
            {
                $aJustCR[] = $oCI;   
            }
        }
        return $aJustCR;
    }
    

    /**
     * Returns all rule instances where any of the criteria match.
     * Leave an attibute value as NULL if you do not want to match on it.
     * 
     * @param Result $oMatchCI
     * @param ContraSourceItem $oMatchCSI 
     */
    public function getAllMatching($oMatchCI=null,$oMatchCSI=null)
    {
        $aMatched=array();
        foreach($this->m_results_array as $oCI)
        {
            if(($oMatchCI ? $oCI->isMatch($oMatchCI) : true) && ($oMatchCSI ? $oCI->isMatchOnCSI($oMatchCSI) : true))
            {
                $aMatched[]=$oCI;
            }
        }
        return $aMatched;
    }
}

