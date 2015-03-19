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
 * Text node for parser tree
 *
 * @author Frank Font
 */
class TNText extends \simplerulesengine\ATreeNode
{
    
    protected $m_literal_tx = NULL;

    public function __construct($sLiteral)
    {
        $this->m_is_constant = TRUE;
        $this->m_literal_tx = (string) $sLiteral;
    }
    
    public function __toString()
    {
        return '"'.$this->m_literal_tx.'"';
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if($bVerboseDiagnostic)
        {
            drupal_set_message(t('Verbose text literal: ("' . $this->m_literal_tx . '")'));
        }
        return $this->m_literal_tx;
    }
}
