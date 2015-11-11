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

require_once 'TNVariable.inc';
require_once 'TNConstant.inc';

/**
 * Operator class for parser tree
 *
 * @author Frank Font
 */
abstract class TNOperator extends \simplerulesengine\ATreeNode
{
    
    protected $m_operator_tx = NULL;
    protected $m_left_child_obj = NULL;
    protected $m_right_child_obj = NULL;

    public function __construct($left_obj,$operator_tx,$right_obj)
    {
        //drupal_set_message('OP Look L O R ==<br>(((' . print_r($left_obj,TRUE) . ')))<br>' . $operator_tx . '<br>(((' . print_r($right_obj,TRUE) . ')))', 'warning');
        
        $this->m_is_operator = TRUE;
        $this->m_operator_tx = $operator_tx;
        $this->m_left_child_obj = $left_obj;
        $this->m_right_child_obj = $right_obj;
    }
    
    public function __toString()
    {
        try 
        {
            $left = $this->m_left_child_obj;
            $right = $this->m_right_child_obj;
            if($left != NULL)
            {
                return '(' . $left . ' ' . $this->m_operator_tx . ' ' . $right . ')' ;
            } else {
                return $this->m_operator_tx . '(' . $right . ')';
            }
        } catch (\Exception $ex) {
            drupal_set_message(t('Cannot print because ' . $ex->getMessage()),'error');
        }
    }
    
    public function getOperator()
    {
        return $this->m_operator_tx;
    }
    
    public function getLeftChild()
    {
        return $this->m_left_child_obj;
    }
    
    public function getRightChild()
    {
        return $this->m_right_child_obj;
    }
}
