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

require_once 'AExpressionParser.inc';


/**
 * Parser for measure expression
 *
 * @author Frank Font
 */
class MeasureExpressionParser extends \simplerulesengine\AExpressionParser
{
    /**
     * Create an instance of the parser.
     * @param type $var_map important that is is by reference because values will change!
     */
    public function __construct(&$var_map)
    {
        if(!is_array($var_map))
        {
            throw new \Exception('You MUST pass in an variable name value mapping to constructor of parser!');
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
            $this->m_tokens_array = MeasureExpressionParser::getTokens($this->m_expression);
            $this->m_root_node = MeasureExpressionParser::getTree($this->m_tokens_array);
        }
        return $this->m_root_node;
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
        $error_array = array();         //Text of errors
        try
        {
            $root_node = $this->parse($expression);
            $dependencies_array = $this->getAllVariableNames($root_node);
        } catch (\Exception $ex) {
            $error_array[] = "Trouble compiling measure expression '$expression' because " . $ex->getMessage();
        }
        return array('haserrors'=>(count($error_array) > 0),'dependencies'=>$dependencies_array, 'errors'=>$error_array, 'treeroot'=>$root_node);
    }
    
    /**
     * Return the next node and the next offset or NULL if done.
     * @param array $tokens_array
     * @param int $new_offset
     * @param int $level_num nesting level
     * @return root node or NULL
     */
    private function getTheNextNode($tokens_array,&$new_offset,$level_num)
    {
        return AExpressionParser::getNextNode($tokens_array, $new_offset, $level_num, $this->m_var_map);
    }
    
    private function getTree($tokens_array)
    {
        $nNewPosition = 0;
        $root_node = $this->getTheNextNode($tokens_array,$nNewPosition,0);
        return $root_node;
    }
}
