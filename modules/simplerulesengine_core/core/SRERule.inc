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


namespace simplerulesengine;

/**
 * Instance of a rule from database.
 */
class SRERule 
{
    private $m_ruleinfo_array = NULL;
    
    /**
     * @param type $ruleinfo_array associative array of one record
     */
    public function __construct($ruleinfo_array)
    {
        $this->m_ruleinfo_array=$ruleinfo_array;
    }
    
    public function __toString()
    {
        if(!isset($this->m_ruleinfo_array['rule_nm']))
        {
            return NULL;
        }
        return $this->$m_ruleinfo_array['rule_nm'] . '=(' . $this->$m_ruleinfo_array['trigger_crit'] . ')';
    }

    public function getAllInfo()
    {
        return $this->m_ruleinfo_array;
    }
}
