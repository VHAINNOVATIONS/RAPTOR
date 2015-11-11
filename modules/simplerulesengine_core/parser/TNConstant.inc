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
 * Constant class for parser tree
 *
 * @author Frank Font
 */
class TNConstant extends \simplerulesengine\ATreeNode
{
    
    protected $m_nConstant = NULL;

    public function __construct($nConstant)
    {
        if($nConstant !== NULL && !is_numeric($nConstant))
        {
            throw new \Exception('The value "'.$nConstant.'" is not a valid numeric constant!');
        }
        $this->m_is_constant = TRUE;
        $this->m_nConstant = $nConstant;
    }
    
    public function __toString()
    {
        return (string) $this->m_nConstant; //MUST cast this into a string!
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if($bVerboseDiagnostic)
        {
            drupal_set_message(t('Verbose constant: (' . $this->m_nConstant . ')'));
        }
        return $this->m_nConstant;
    }
}
