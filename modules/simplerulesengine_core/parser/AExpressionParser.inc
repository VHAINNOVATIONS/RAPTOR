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

require_once 'ITreeNode.inc';
require_once 'ATreeNode.inc';
require_once 'TNOperator.inc';
require_once 'TNOBoolean.inc';
require_once 'TNONegate.inc';
require_once 'TNONumeric.inc';
require_once 'TNOUnary.inc';
require_once 'TNOAnyTrue.inc';
require_once 'TNOAllTrue.inc';
require_once 'TNOAllFalse.inc';
require_once 'TNOAllNull.inc';
require_once 'TNVariable.inc';
require_once 'TNConstant.inc';
require_once 'TNText.inc';
require_once 'TNArray.inc';
require_once 'IExpressionParser.inc';

/**
 * Parser for expressions
 *
 * @author Frank Font
 */
abstract class AExpressionParser implements IExpressionParser
{
    private $m_var_map = NULL;   //Key is the variable name, value is the value of the variable
    private $m_expression = NULL;
    private $m_tokens_array = NULL;
    private $m_root_node = NULL;
    
    
    /**
     * See if any names are missing from the map.
     * @param type $ref_var_map
     * @param type $check_names_array
     */
    public static function getMissingKeys($ref_var_map,$check_names_array)
    {
        $missing_key_array = array();
        foreach($check_names_array as $name)
        {
            if(!array_key_exists($name, $ref_var_map))
            {
                $missing_key_array[] = $name;
            }
        }
        return $missing_key_array;
    }
    
    /**
     * Get an array (key,value) of all the variables in the branch.
     * @param type $root_node
     */
    public function getAllVariableNames($root_node)
    {
        $merged_array = array();
        if(get_class($root_node) == 'simplerulesengine\TNVariable')
        {
            $name = $root_node->getName();
            $merged_array = array($name => $name); //Support key and value searches
        } else
        if(is_subclass_of($root_node, 'simplerulesengine\TNOperator'))
        {
            $a = $this->getAllVariableNames($root_node->getLeftChild());
            if(is_array($a))
            {
                $merged_array = $a;
            }
            $a = $this->getAllVariableNames($root_node->getRightChild());
            if(is_array($a))
            {
                $merged_array = array_merge($merged_array,$a);
            }
            //drupal_set_message('aaaaaa LOOK operator class='.get_class($root_node));
        } else
        if(is_subclass_of($root_node, 'simplerulesengine\TNOUnary'))
        {
            $a = $root_node->getParameters();
            foreach($a as $oVar)
            {
                $name = $oVar->getName();
                $merged_array[$name] = $name;
            }
        }
        return $merged_array;
    }
    
    /**
     * Get an array (just values) of all the operators in the branch.
     */
    public function getAllOperatorTokens($root_node)
    {
        if(is_subclass_of($root_node, 'simplerulesengine\TNOperator'))
        {
            $merged_array = array($root_node->getOperator());
            $a = $this->getAllOperatorTokens($root_node->getLeftChild());
            if(is_array($a))
            {
                $merged_array = $a;
            }
            $a = $this->getAllOperatorTokens($root_node->getRightChild());
            if(is_array($a))
            {
                $merged_array = array_merge($merged_array,$a);
            }
        } else {
            $merged_array = NULL;
        }
        return $merged_array;
    }

    /**
     * Get an array of all non-blank symbols and terms
     */
    public static function getTokens($expression)
    {
        $len = strlen($expression);
        $tokens_array = array();
        $startpos = 0;
        $havechars = FALSE;
        $in_quote = FALSE;  //True when we are inside quote area.
        for($i=0; $i<$len; $i++)
        {
            $char = $expression[$i];
            if(strpos(' ()"',$char) !== FALSE)
            {
                if($havechars)
                {
                    //We are at the end of a token
                    $token = trim(substr($expression,$startpos,$i-$startpos));
                    $tokens_array[] = $token;
                    $havechars = FALSE;
                    $startpos = $i; //Start here
                } else {
                    $startpos = $i; //Keep moving it back
                }
                if(strpos('()',$char) !== FALSE)
                {
                    $tokens_array[] = $char;
                    $startpos = $i+1; //Start here
                } else
                if('"'==$char)
                {
                    if($in_quote)
                    {
                        $in_quote = FALSE;
                    } else {
                        $in_quote = TRUE;
                    }
                    $tokens_array[] = $char;
                    $startpos = $i+1; //Start here
                }
            } else {
                if(!$in_quote && (',' == $char))
                {
                    if($havechars)
                    {
                        //We are at the end of a token
                        $token = trim(substr($expression,$startpos,$i-$startpos));
                        $tokens_array[] = $token;
                        $havechars = FALSE;
                    } else {
                        $startpos = $i; //Keep moving it back
                    }
                    //Comma delimiter.
                    $tokens_array[] = $char;
                    $startpos = $i+1; //Start here
                } else {
                    $havechars = TRUE;
                }
            }
        }
        if($havechars)
        {
            $token = trim(substr($expression,$startpos,$i-$startpos));
            $tokens_array[] = $token;
        }
        return $tokens_array;
    }
    
    /**
     * Return the next null and the next offset or NULL if done.
     * @param array $tokens_array
     * @param int $new_offset
     * @param int $level_num nesting level
     * @return root node or NULL
     */
    public static function getNextNode($tokens_array, &$new_offset, $level_num, &$var_map)
    {
        $level_num++;
        $negation = FALSE;
        $i = $new_offset;
        $left_term=NULL;
        $operator_tx=NULL;
        $right_term=NULL;
        $token_count = count($tokens_array);
        $original_offset = $new_offset;
        while($i < $token_count)
        {
            $token_tx = $tokens_array[$i];
            //drupal_set_message("LOOK level $level_num INITIAL TOKEN=[$token_tx]");
            if($token_tx == '(')
            {
                //Inside of a parenthetical
                $i++;
                $left_term = AExpressionParser::getNextNode($tokens_array, $i, $level_num, $var_map);
                if($left_term == NULL)
                {
                    throw new \Exception('Expected a node but instead got NULL at offset '.($original_offset+1).'  in level ' . $level_num . ' of ' . print_r($tokens_array,TRUE));
                }
                if($i < $token_count)
                {
                    $token_tx = $tokens_array[$i];
                    if($token_tx == ')')
                    {
                        //Done, unwind.
                        //drupal_set_message("LOOK level $level_num only ONE TERM: 1[$left_term]@$i " . ' of ' . print_r($tokens_array,TRUE));
                    } else {
                        //This term is part of a tuple.
                        $operator_tx = $tokens_array[$i];
                        $i++;
                        $right_term = AExpressionParser::getNextNode($tokens_array, $i, $level_num, $var_map);
                        if($right_term == NULL)
                        {
                            throw new \Exception("Ran out of text in nesting level $level_num but a term " .print_r($tokens_array,TRUE));
                        }
                        //drupal_set_message("LOOK level $level_num ALL THREE TERMS: 1[$left_term] 2[$operator_tx] 3[$right_term] @$i" . ' of ' . print_r($tokens_array,TRUE));
                        if($i < 0)
                        {
                            throw new \Exception("Ran out of text in nesting level $level_num but expected closing parenthesize in " .print_r($tokens_array,TRUE));
                        }
                        $token_tx = $tokens_array[$i];
                        if($token_tx !== ')')
                        {
                            throw new \Exception("In nesting level $level_num expected closing parenthesize at offset $i of " .print_r($tokens_array,TRUE));
                        }
                    }
                    $i++;
                }
                break;  //Unwind!
                //drupal_set_message('A2 token in paren [' . $left_term . '] next Offset is ' . $i . ' :Expecting op at next pos and then after that more ' . print_r($tokens_array,TRUE),'warning');
            } else if($token_tx == 'not') {
                //This contains a negation
                $negation = TRUE;
                $i++;
                $left_term = AExpressionParser::getNextNode($tokens_array, $i, $level_num, $var_map);
                break; 
            } else {
                //drupal_set_message("!!!LOOK level $level_num ELSE GOT [".$token_tx ."] at $i ".' of token array ' . print_r($tokens_array,TRUE) . '!');
                if($token_tx != ')')
                {
                    //We expect this to be a single term.
                    if($token_tx == '"')
                    {
                        //This contains a text literal term
                        $i++;
                        $left_term = new \simplerulesengine\TNText($tokens_array[$i]);
                        $i++;
                        if($tokens_array[$i] != '"')
                        {
                            throw new \Exception('Expected a closing quote at position '.$i.' of token array ' . print_r($tokens_array,TRUE) . '!');
                        }
                    } else if($token_tx == 'items') {
                        //This contains an array of items
                        $i++;
                        if($tokens_array[$i] != '(')
                        {
                            throw new \Exception('Expected a starting parenthesize at position '.$i.' of token array ' . print_r($tokens_array,TRUE) . '!');
                        }
                        $item_array = array();
                        while(TRUE)
                        {
                            $i++;
                            $token_tx = $tokens_array[$i];
                            if($token_tx == '"')
                            {
                                //This contains a text literal term
                                $i++;
                                $item_tx = $tokens_array[$i];
                                $item_array[$item_tx] = $item_tx;
                                $i++;
                                if($tokens_array[$i] != '"')
                                {
                                    throw new \Exception('Expected a closing quote at position '.$i.' of token array ' . print_r($tokens_array,TRUE) . '!');
                                }
                            } else if($token_tx == ',') {
                                //Good, just keep going.
                            } else if($token_tx == ')') {
                                break;
                            } else {
                                //Only supporting text in arrays.
                                throw new \Exception('Expected a text item at position '.$i.' of token array ' . print_r($tokens_array,TRUE) . '!');
                            }
                        }
                        $left_term = new \simplerulesengine\TNArray($item_array);
                    } else {
                        if(is_numeric($token_tx))
                        {
                            $left_term = new \simplerulesengine\TNConstant($token_tx);
                        } else {
                            $left_term = new \simplerulesengine\TNVariable($token_tx, $var_map);
                        }
                    }
                }
                $i++;
                break;
            }
        }
        $new_offset = $i;
        if($left_term == NULL)
        {
            //Nothing.
            $node_obj = NULL;
            $new_offset = -1;
        } else
        if($operator_tx == NULL)
        {
            //One simple node.
            $node_obj = $left_term;
        } else {
            //A node with two children.
            if(get_class($left_term) == 'TNText')
            {
                //This can only be a boolean operation.
                $node_obj = new \simplerulesengine\TNOBoolean($left_term,$operator_tx,$right_term);
            } else {
                //Might be math operation.
                if(MeasureExpressionParser::isNumericOperator($operator_tx))
                {
                    $node_obj = new \simplerulesengine\TNONumeric($left_term,$operator_tx,$right_term);
                } else {
                    $node_obj = new \simplerulesengine\TNOBoolean($left_term,$operator_tx,$right_term);
                }
            }
        }
        if($negation)
        {
            //Negate the result of the node.
            $node_obj = new \simplerulesengine\TNONegate($node_obj);
        }
        return $node_obj;
    }
    
    public static function isNumericOperator($operator_tx)
    {
        return (strpos('*/+-',$operator_tx) !== FALSE);
    }
}
