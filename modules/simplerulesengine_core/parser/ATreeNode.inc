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
 * Abstract class for parser tree
 *
 * @author Frank Font
 */
abstract class ATreeNode implements \simplerulesengine\ITreeNode
{
    protected $m_is_constant = FALSE;
    protected $m_is_variable = FALSE;
    protected $m_is_operator = FALSE;
    protected $m_bBoolean = FALSE;
    protected $m_bNumeric = FALSE;

    public function isConstant()
    {
        return $this->m_is_constant;
    }
    
    public function isVariable()
    {
        return $this->m_is_variable;
    }
    
    public function isOperator()
    {
        return $this->m_is_operator;
    }
    
    public function isBoolean()
    {
        return $this->m_bBoolean;
    }

    public function isNumeric()
    {
        return $this->m_bNumeric;
    }

    public function getLeftChild() 
    {
        return NULL;
    }
    
    public function getRightChild() 
    {
        return NULL;
    }
}
