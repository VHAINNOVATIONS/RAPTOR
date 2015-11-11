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

namespace raptor_formulas;

/**
 * Logic for computing results from lab values
 *
 * @author Frank Font of SAN Business Consultants
 */
class Labs 
{
    
    /**
     * eGFR (mL/min/1.73 m^2) = 186 * [Serum Creat (mg/dL)]^-1.154 * [Age (years)]^-0.203 * F * (1.212 if African American)
     * [F = 1 if male, F = 0.742 if female]
     */
    public function calc_eGFR($creatininevalue, $age, $is_female, $is_african_american)
    {
        $F = $is_female ? 0.742 : 1;
        $ethnicityCorrection = $is_african_american ? 1.212 : 1;
        $eGFR = round(186 * pow($creatininevalue, -1.154) * pow($age, -0.203) * $F * $ethnicityCorrection, 0);
        
        return $eGFR;
    }
    
    /**
     * Return a keyword indicating the health assessment of the provideded eGFR value
     */
    public function get_eGFR_Health($value)
    {
        $EGFR_ALERT_WARN_END_LEVEL = LAB_EGFR_ALERT_WARN_END_LEVEL;
        $EGFR_ALERT_BAD_END_LEVEL = LAB_EGFR_ALERT_BAD_END_LEVEL;
        if($value > '')
        {
             if($value < $EGFR_ALERT_BAD_END_LEVEL)
             {
                 return 'bad';
             } else if($value < $EGFR_ALERT_WARN_END_LEVEL) {
                 return 'warn';
             } else {
                 return 'good';
             }
        }
        return '';
    }
    
}
