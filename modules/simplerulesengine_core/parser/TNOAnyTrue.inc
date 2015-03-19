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
 * Boolean unary operator class for parser tree
 *
 * @author Frank Font
 */
class TNOAnyTrue extends \simplerulesengine\TNOUnary
{
    public function __construct($sFunctionName,$aParams)
    {
        parent::__construct($sFunctionName,$aParams);
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        //Evaluate an expression.
        foreach($this->m_aParams as $node_obj)
        {
            if($node_obj->getValue() == TRUE)  //Important to NOT use === TRUE because might be string boolean!!!!
            {
                if($bVerboseDiagnostic)
                {
                    drupal_set_message(t('Verbose '.$this->m_sFunctionName.' is TRUE because of ['.$node_obj.'] for '.print_r($this->m_aParams)));
                }
                return TRUE;
            }
        }
        if($bVerboseDiagnostic)
        {
            drupal_set_message(t('Verbose '.$this->m_sFunctionName.' is FALSE for '.print_r($this->m_aParams,TRUE)));
        }
        return FALSE;
    }
}
