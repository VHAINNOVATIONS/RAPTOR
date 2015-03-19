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
 * Instance of a measure item.
 */
class SREMeasure
{
    private $m_measure_info = NULL;
    
    /**
     * @param type $measure_info associative array of one record
     */
    public function __construct($measure_info)
    {
        $this->m_measure_info=$measure_info;
    }
    
    public function __toString()
    {
        if(!isset($this->m_measure_info['measure_nm']))
        {
            return NULL;
        }
        return $this->m_measure_info['measure_nm'] . '=(' . $this->m_measure_info['criteria_tx'] . ')';
    }
    
    public function getAllInfo()
    {
        return $this->m_measure_info;
    }
}
