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

require_once 'MeasureExpressionParser.inc';


/**
 * Parser cor rule expression
 *
 * @author Frank Font
 */
class RuleExpressionParser extends \simplerulesengine\MeasureExpressionParser
{
    protected $m_custom_unary_functions_map = array('AnyFlagTrue'=>'AnyFlagTrue'
                                        ,'AllFlagsTrue'=>'AllFlagsTrue'
                                        ,'AllFlagsFalse'=>'AllFlagsFalse'
                                        ,'AllFlagsNull'=>'AllFlagsNull');
    
    /**
     * Create an instance of the rule parser.
     * @param type $var_map important that is is by reference because values will change!
     */
    public function __construct(&$var_map)
    {
        if(!is_array($var_map))
        {
            throw new \Exception('You MUST pass in a variable name value mapping to constructor of rule parser!');
        }
        $this->m_var_map = &$var_map;
    }
    
    /**
     * Parses the string into a tree of nodes
     * @param type $expression
     * @return tree of nodes
     */
    public function parse($expression)
    {
        $this->m_expression = '('.$expression.')';    //IMPORTANT THAT WE ALWAYS WRAP IN OUTER PARENS SO TERMS ARE NOT LOST!
        if($expression !== NULL)
        {
            $this->m_tokens_array = RuleExpressionParser::getTokens($this->m_expression);
            $this->m_root_node = RuleExpressionParser::getTree($this->m_tokens_array);
        }
        return $this->m_root_node;
    }
    
    /**
     * Get an array (key,value) of all the top functions in the branch.
     * @param type $root_node
     */
    public function getTopFunctions($root_node)
    {
        $merged_array = array();
        if(is_subclass_of($root_node,'simplerulesengine\TNOUnary'))
        {
            $name = $root_node->getName();
            $merged_array[$name] = $root_node->getParameters();
            //drupal_set_message('aaaaaa LOOK VARIABLE class='.get_class($root_node) . ' >>> ' . $root_node);
        } else
        if(!is_subclass_of($root_node, 'simplerulesengine\TNOperator') || ($root_node->getOperator() != 'and'))
        {
            throw new \Exception('Rules only allow "and" operator between the top functions! Trouble with '.$root_node);
        } else 
        if(is_subclass_of($root_node, 'simplerulesengine\TNOperator')){
            $a = $this->getTopFunctions($root_node->getLeftChild());
            foreach($a as $key=>$value)
            {
                $merged_array[$key] = $value;
            }
            $a = $this->getTopFunctions($root_node->getRightChild());
            foreach($a as $key=>$value)
            {
                $merged_array[$key] = $value;
            }
        }
        //drupal_set_message('aaaaaa LOOK result for class '.get_class($root_node) . '=' . print_r($merged_array,TRUE));
        return $merged_array;
    }
    
    /**
     * Compile the expression to produce metadata
     * keys: dependencies, errors
     * @param type $expression
     * @return array of compilation details
     */
    public function compile($expression)
    {
        $root_node=NULL;
        $dependencies_array = array();   //Names of dependencies
        $aTopFunctions = array();   //Top level rule functions
        $error_array = array();         //Text of errors
        try
        {
            $root_node = $this->parse($expression);
            $dependencies_array = $this->getAllVariableNames($root_node);
            $aTopFunctions = $this->getTopFunctions($root_node);
        } catch (\Exception $ex) {
            $error_array[] = "Trouble compiling rule expression '$expression' because " . $ex->getMessage();
        }
        return array('haserrors'=>(count($error_array) > 0)
                ,'dependencies'=>$dependencies_array
                , 'errors'=>$error_array
                , 'treeroot'=>$root_node
                , 'functions'=>$aTopFunctions);
    }
    

    /**
     * Return the next null and the next offset or NULL if done.
     * @param array $tokens_array
     * @param int $new_offset
     * @param int $level_num nesting level
     * @return root node or NULL
     */
    private function getRuleNextNode($tokens_array,&$new_offset,$level_num)
    {
        
        //return AExpressionParser::getNextNode($tokens_array, $new_offset, $level_num, $this->m_var_map);
        
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
                $left_term = $this->getRuleNextNode($tokens_array, $i, $level_num);
                if($left_term == NULL)
                {
                    throw new \Exception('Expected a node but instead got NULL at offset '.($original_offset+1).'  in level ' . $level_num . ' of ' . print_r($tokens_array,TRUE));
                }
                if($i < $token_count)
                {
                    $token_tx = $tokens_array[$i];
                    if($token_tx !== ')')
                    {
                        while(TRUE)
                        {
                            //This term is part of a tuple.
                            $operator_tx = $token_tx;
                            if($operator_tx != 'and')
                            {
                                throw new \Exception('Not allowed to have "'.$operator_tx.'" because only operator allowed for Rules is "and" at position '.$i.' of tokens '.print_r($tokens_array,TRUE));
                            }
                            $i++;
                            $right_term = $this->getRuleNextNode($tokens_array, $i, $level_num);
                            if($right_term == NULL)
                            {
                                throw new \Exception("Ran out of tokens in nesting level $level_num but rule expected a term " .print_r($tokens_array,TRUE));
                            }
                            if($i < 0)
                            {
                                throw new \Exception("Ran out of text in nesting level $level_num but expected rule closing parenthesize in " .print_r($tokens_array,TRUE));
                            }
                            $token_tx = $tokens_array[$i];
                            if($token_tx == 'and')
                            {
                                //More to chain
                                $left_term = new \simplerulesengine\TNOBoolean($left_term,$operator_tx,$right_term);
                            } else 
                            if($token_tx == ')')
                            {
                                break;
                            }
                        }
                    }
                    $i++;
                }
                break;  //Unwind!
            } if($token_tx == 'AnyFlagTrue') {
                $sFunctionName = $token_tx;
                $i++;
                $aParams = $this->getArrayOfFlagParameters($sFunctionName, $i, $tokens_array);
                $left_term = new \simplerulesengine\TNOAnyTrue($sFunctionName,$aParams);
                break; 
            } if($token_tx == 'AllFlagsTrue') {
                $sFunctionName = $token_tx;
                $i++;
                $aParams = $this->getArrayOfFlagParameters($sFunctionName, $i, $tokens_array);
                $left_term = new \simplerulesengine\TNOAllTrue($sFunctionName,$aParams);
                break; 
            } if($token_tx == 'AllFlagsFalse') {
                $sFunctionName = $token_tx;
                $i++;
                $aParams = $this->getArrayOfFlagParameters($sFunctionName, $i, $tokens_array);
                $left_term = new \simplerulesengine\TNOAllFalse($sFunctionName,$aParams);
                break; 
            } if($token_tx == 'AllFlagsNull') {
                $sFunctionName = $token_tx;
                $i++;
                $aParams = $this->getArrayOfFlagParameters($sFunctionName, $i, $tokens_array);
                $left_term = new \simplerulesengine\TNOAllNull($sFunctionName,$aParams);
                break; 
            } else {
                throw new \Exception("In nesting level $level_num did not expect [$token_tx] at offset $i of " .print_r($tokens_array,TRUE));
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
            //This can only be a boolean operation for two children.
            $node_obj = new \simplerulesengine\TNOBoolean($left_term,$operator_tx,$right_term);
        }
        //drupal_set_message('>>>>LOOK>>> got node>>>' . $node_obj);
        return $node_obj;
    }
    
    /**
     * Return array of terms for a unary function.
     * @param type $sFunctionName
     * @param type $i
     * @param type $tokens_array
     * @return array of parameters
     * @throws \Exception
     */
    private function getArrayOfFlagParameters($sFunctionName, &$i, $tokens_array)
    {
        $aParams = array();
        $token_tx = $tokens_array[$i];
        if($token_tx != '(')
        {
            throw new \Exception('Expected starting parenthesis for the '.$sFunctionName. ' function at position '.$i.' of token array ' . print_r($tokens_array,TRUE));
        }
        $i++;   //Get to the first parameter.
        $nDelimPolice=1;
        try
        {
            while(TRUE)
            {
                $token_tx = $tokens_array[$i];
                if($token_tx == '(')
                {
                    throw new \Exception('Parenthesis are NOT allowed in parameter list for the '.$sFunctionName. ' function at position '.$i.' of token array ' . print_r($tokens_array,TRUE));
                } else if($token_tx == ')') {
                    break;
                } else if($token_tx != ',') {
                    if($nDelimPolice != 1)
                    {
                        throw new \Exception('Possible problem with commas did not expect parameter for the '.$sFunctionName. ' function at position '.$i.' of token array ' . print_r($tokens_array,TRUE));
                    }
                    $aParams[$token_tx] = new \simplerulesengine\TNVariable($token_tx,$this->m_var_map);
                    $nDelimPolice--;
                } else {
                    $nDelimPolice++;
                }
                $i++;
            }
        } catch (\Exception $ex) {
            throw new \Exception('Expected a enclosed parameters for the '
                    . $sFunctionName
                    . ' function of token array ' 
                    . print_r($tokens_array,TRUE) 
                    . ' but failed because ' . $ex->getMessage());
        }
        $i++;   //Leave on token that follows the closing paren.
        return $aParams;
    }
    
    private function getTree($tokens_array)
    {
        $nNewPosition = 0;
        $root_node = $this->getRuleNextNode($tokens_array,$nNewPosition,0);
        return $root_node;
    }
}
