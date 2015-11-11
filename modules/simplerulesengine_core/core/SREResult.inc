<?php
/**
 * @file
 * ----------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font(ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano(acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  
 * Contact SAN to share updates.
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

 * ----------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

require_once 'SRESourceItem.inc';

/**
 * One instance of a rule.  A rule instance can be triggered by more than 
 * one rule source.  
 * Multiple sources are grouped together if they have same values in common:
 *  identical category name,
 *  identical summary message text,
 *  identical require confirmation value.  
 * For example, if there are two rules with different criteria that both 
 * return the text 'Contrast contraindicated because low eGFR' 
 * and both require confirmation and are in same category then both 
 * those sources would be associated with one result instance.
 * 
 * @author Frank Font of SAN Business Consultants
 */
class SREResult 
{
    private $m_id_tx=NULL;               //Unique ID for this result
    private $m_result_source_array=NULL; //Array of specific result causes
    private $m_summary_msg_override_tx=NULL;
    
    /**
     * Create an instance of a result.
     */
    public function __construct($result_source_array
            ,$summary_msg_override_tx=NULL
            ,$id_tx=NULL)
    {
        if(!is_array($result_source_array) 
                || get_class($result_source_array[0]) 
                    !== 'simplerulesengine\SRESourceItem')
        {
            $error = 'Must provide an array of SRESourceItem objects '
                    . 'to create a SRERule instance! '
                    . 'INSTEAD GOT>>>' . print_r($result_source_array,TRUE);
            throw new \Exception($error);
        }
        $this->m_result_source_array=$result_source_array;
        $this->m_summary_msg_override_tx=$summary_msg_override_tx;
        if($id_tx == NULL)
        {
            //$extrapart = date("Y-m-d_H:i:s", time());
            $extrapart = count($result_source_array);
            $this->m_id_tx=count($result_source_array) . '_0_' 
                    . $result_source_array[0]->getRuleName() 
                    . '__' . $extrapart;
        } else {
            $this->m_id_tx=$id_tx;
        }
    }
    
    /**
     * Each rule must have a unique ID so the application can track
     * views and acknowledgements from users. 
     * 
     * These IDs must map back to a mapping of the ID to the requirements that
     * caused the rule result. 
     * 
     * @return text 
     */
    public function getUniqueID()
    {
        return $this->m_id_tx;
    }
    
    /**
     * Medicine, Procedure
     * @return text 
     */
    public function getRuleCategoryName()
    {
        return $this->m_result_source_array[0]->getRuleCategoryName();
    }

    /**
     * The specific message
     * @return text 
     */
    public function getSummaryMessage()
    {
        if($this->m_summary_msg_override_tx !== NULL)
        {
            return $this->m_summary_msg_override_tx;
        }
        return $this->m_result_source_array[0]->getSummaryMessage();
    }

    /**
     * The array of specific items at the root of the rule result
     * @return array of ContraSourceItem instances 
     */
    public function getResultSource()
    {
        return $this->m_result_source_array;
    }
    
    /**
     * @return TRUE if any of the items require confirmation
     */
    public function isConfirmationRequired()
    {
        foreach($this->m_result_source_array as $csi)
        {
            if($csi->isConfirmationRequired())
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    private static function __isMatch($value1,$value2,$null_is_wildcard=TRUE)
    {
        if($null_is_wildcard)
        {
            if($value1 === NULL)
            {
                return TRUE;
            }
        }
        return ($value1 === $value2);
    }
    
    /**
     * Compares two instances of result
     * @return boolean True if all checked attributes match, else false
     */
    public function isMatch($match_ci,$match_null_is_wildcard=TRUE)
    {
        if($match_ci===NULL)
        {
            die("Did NOT provide a valid instance of Result for isMatch!");
        }
        
        if(!SREResult::__isMatch($match_ci->getImpactedItemCategory()
                , $this->getImpactedItemCategory(),$match_null_is_wildcard))
        {
            return FALSE;
        }
        if(!SREResult::__isMatch($match_ci->isConfirmationRequired()
                , $this->isConfirmationRequired(),$match_null_is_wildcard))
        {
            return FALSE;
        }
        if(!SREResult::__isMatch($match_ci->getUniqueID()
                , $this->getUniqueID(),$match_null_is_wildcard))
        {
            return FALSE;
        }
        //We are here because we did not reject the match.
        return TRUE;
    }

    /**
     * Is considered a match if ANY of the CSI match the criteria.
     * @param type $match_csi
     * @param type $match_null_is_wildcard
     * @return boolean TRUE if match, else FALSE.
     */
    public function isMatchOnCSI($match_csi,$match_null_is_wildcard=true)
    {
        foreach($this->m_result_source_array as $oCSI)
        {
            if($oCSI->isMatch($match_csi,$match_null_is_wildcard))
            {
                //At least one of the CSI matched, so return true
                return TRUE;
            }
        }
        //We are here because we did NOT find any matches.
        return FALSE;
    }
}
