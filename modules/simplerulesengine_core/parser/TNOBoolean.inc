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
 * Boolean operator class for parser tree
 *
 * @author Frank Font
 */
class TNOBoolean extends \simplerulesengine\TNOperator
{
    public function __construct($left_obj,$operator_tx,$right_obj)
    {
        //drupal_set_message('TNOB Look L O R ==<br>(((' . print_r($left_obj,TRUE) . ')))<br>' . $operator_tx . '<br>(((' . print_r($right_obj,TRUE) . ')))', 'warning');
        parent::__construct($left_obj,$operator_tx,$right_obj);
        $this->m_bBoolean = TRUE;
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        //Evaluate an expression.
        if($this->getLeftChild() !== NULL)
        {
            $left = $this->getLeftChild()->getValue($bVerboseDiagnostic);   //Do NOT trim these because converts to string!!!
        } else {
            $left = NULL;
        }
        if($this->getRightChild() !== NULL)
        {
            $right = $this->getRightChild()->getValue($bVerboseDiagnostic);   //Do NOT trim these because converts to string!!!
        } else {
            $right = NULL;
        }
        if($this->m_operator_tx !== 'or' && (($right === NULL) 
            || ($this->m_operator_tx !== 'not' && $left === NULL)))
        {
            //Evaluate as NULL with no further processing.
            $result = NULL;    
        } else {
            if($this->m_operator_tx == 'somematch')
            {
                //First part of word must match without regard to upper/lowercase
                $result = FALSE;   //Assume no match.
                if(!is_array($left) || !is_array($right))
                {
                    throw new \Exception('Expected arrays but did not get them>>>' . $this);
                }
                foreach($left as $litem)
                {
                    $leftword = trim(strtoupper($litem));
                    foreach($right as $ritem)
                    {
                        $rightword = trim(strtoupper($ritem));
                        $pos = strpos($rightword, $leftword);
                        if($pos !== FALSE && $pos == 0)
                        {
                            $result = TRUE;
                            break 2;    //Exit both loops!
                        }
                        $pos = strpos($leftword, $rightword);  //Try reverse too
                        if($pos !== FALSE && $pos == 0)
                        {
                            $result = TRUE;
                            break 2;    //Exit both loops!
                        }
                    }
                }
            } else if($this->m_operator_tx == 'somefullmatch') {
                //Full word must match without regard to upper/lowercase
                $result = FALSE;   //Assume no match.
                if(!is_array($left) || !is_array($right))
                {
                    throw new \Exception('Expected arrays but did not get them>>>' . $this);
                }
                foreach($left as $litem)
                {
                    $leftword = trim(strtoupper($litem));
                    foreach($right as $ritem)
                    {
                        if(trim($leftword) == trim(strtoupper($ritem)))
                        {
                            $result = TRUE;
                            break 2;    //Exit both loops!
                        }
                    }
                }
            } else if($this->m_operator_tx == 'someexactmatch') {
                //Full word must match and be same upper/lowercase
                $result = FALSE;   //Assume no match.
                if(!is_array($left) || !is_array($right))
                {
                    throw new \Exception('Expected arrays but did not get them>>>' . $this);
                }
                foreach($left as $item)
                {
                    if(array_key_exists(trim($item),$right))
                    {
                        $result = TRUE;
                        break;
                    }
                }
            } else {
                if($this->m_operator_tx != 'and' && $this->m_operator_tx != 'or' && $this->m_operator_tx != 'not'
                        && !is_numeric($left) && !is_numeric($right) 
                        && $right !== FALSE && $right !== TRUE)
                {
                    //This is a text comparison
                    $right = trim($right);  //Do this now that we know it is NOT a NULL or FALSE
                    $left = trim($left);    //Do this now that we know it is NOT a NULL or FALSE
                    if($this->m_operator_tx == '=')
                    {
                        //Case insensitive equality check
                        $result = (strtoupper($left) == strtoupper($right));
                    } else if($this->m_operator_tx == '<>') {
                        //Case insensitive inequality check
                        $result = (strtoupper($left) !== strtoupper($right));
                    } else if($this->m_operator_tx == '!==') {
                        //Case sensative inequality check
                        $result = ($left !== $right);
                    } else if($this->m_operator_tx == '==') {
                        //Case sensitive equality check
                        $result = ($left == $right);
                    } else {
                        if($left === NULL)
                        {
                            $lefttxt = 'NULL';
                        } else if($left === FALSE) {
                            $lefttxt = 'FALSE';
                        } else if($left === TRUE) {
                            $lefttxt = 'TRUE';
                        } else {
                            $lefttxt = $left;
                        }
                        if($right === NULL)
                        {
                            $righttxt = 'NULL';
                        } else if($right === FALSE) {
                            $righttxt = 'FALSE';
                        } else if($right === TRUE) {
                            $righttxt = 'TRUE';
                        } else {
                            $righttxt = $right;
                        }
                        throw new \Exception('Cannot process evaluation for text operator [' . $this->m_operator_tx . '] on values [' . $lefttxt . '] and [' . $righttxt . ']');
                    }
                } else {
                    //This is a non-text comparison
                    if($this->m_operator_tx == 'and')
                    {
                        $result = ($left && $right);
                    } else if($this->m_operator_tx == 'or') {
                        //Might have NULL in case of OR, so check.
                        if(($left === NULL && $right === NULL)
                                || ($right === NULL && $left === FALSE)
                                || ($left === NULL && $right === FALSE)
                                )
                        {
                            $result = NULL;
                        } else {
                            $result = ($left || $right);
                        }
                    } else if($this->m_operator_tx == '>') {
                        $result = ($left > $right);
                    } else if($this->m_operator_tx == '<') {
                        $result = ($left < $right);
                    } else if($this->m_operator_tx == '<=') {
                        $result = ($left <= $right);
                    } else if($this->m_operator_tx == '>=') {
                        $result = ($left >= $right);
                    } else if($this->m_operator_tx == '<>') {
                        $result = ($left != $right);
                    } else if($this->m_operator_tx == '!==') {
                        //Same as simple '<>'
                        $result = ($left != $right);
                    } else if($this->m_operator_tx == '=') {
                        $result = ($left == $right);
                    } else if($this->m_operator_tx == '==') {
                        //Same as simple '='
                        $result = ($left == $right);
                    } else if($this->m_operator_tx == 'not') {
                        $result = (!$right);
                    } else {
                        throw new \Exception('Cannot process evaluation for operator boolean [' . $this->m_operator_tx . '] on values [' . $left . '] and [' . $right . ']');
                    }
                }
            }
        }
        if($bVerboseDiagnostic)
        {
            drupal_set_message(t('Verbose boolean result: (' . $left . ' ' . $this->m_operator_tx . ' ' . $right . ') is ' . ($result ? 'TRUE' : 'FALSE')));
        }
        return $result;
    }
}
