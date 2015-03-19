<?php
/**
 * @file
 * ----------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font(ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano(acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  
 * Contact SAN to share updates.
 *
 * Copyright 2014-2015 SAN Business Consultants
 * , a Maryland USA company (sanbusinessconsultants.com)
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
 * ----------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

require_once 'SREUtility.inc';
require_once 'SREMeasures.inc';
require_once 'SRERules.inc';
require_once 'SRESourceItem.inc';
require_once 'SREResult.inc';
require_once 'SREResults.inc';
require_once dirname(__FILE__) . '/../parser/MeasureExpressionParser.inc';
require_once dirname(__FILE__) . '/../parser/RuleExpressionParser.inc';

/**
 * Functions to determine what rules are relevant in the current context.
 *
 * @author Frank Font of SAN Business Consultants
 */
class SREngine 
{
    private $m_sre_context = NULL;
    
    private $m_measures_obj = NULL; 
    private $m_rules_obj = NULL;
    private $m_baselineinfo_array = NULL;      //Simple key map array
    private $m_candidate_values = NULL;  //Simple key map array
    
    /**
     *Initialize with all the core information this engine needs. 
     */        
    public function __construct($sre_context
            , $baselineinfo_array = NULL)
    {
        $this->m_sre_context = $sre_context;
        if($this->m_sre_context == NULL)
        {
            throw new \Exception('The SREContext cannot be NULL!');
        }
        if($baselineinfo_array !== NULL)
        {
            $this->validateBaselineInfo($baselineinfo_array);
        } else {
            $baselineinfo_array = array('DUMMYBASELINE'=>'DUMMYBASELINE');
        }
        $this->m_measures_obj = 
            new \simplerulesengine\SREMeasures($sre_context,$baselineinfo_array);
        $this->m_rules_obj = 
            new \simplerulesengine\SRERules($sre_context,$baselineinfo_array);
        $this->m_baselineinfo_array=$baselineinfo_array;
    }
    
    public function getSREContext()
    {
        return $this->m_sre_context;
    }
    
    /**
     * Does not evaluate a value but checks the syntax and names of variables.
     * @param string $expression rule criteria
     * $param array $disallowed_vars_array names of variables we will not allow
     * @return array with validation details (isokay=TRUE/FALSE,errors=array)
     */
    public function validateRuleExpression($expression,$disallowed_vars_array=NULL)
    {
        if($disallowed_vars_array == NULL)
        {
            $disallowed_vars_array = array();
        }
        $results_array = array();
        $error_array = array();
        $is_good = TRUE;

        //Run the compiler
        try
        {
            $var_map = $this->getAllCurrentVariableNames();
            $parserengine = new \simplerulesengine\RuleExpressionParser($var_map);
            $compiler_results_arr = $parserengine->compile($expression);
            foreach($compiler_results_arr['errors'] as $error_msg)
            {
                $error_array[] = $error_msg;
            }
            foreach($compiler_results_arr['dependencies'] as $var_name)
            {
                if(in_array($var_name,$disallowed_vars_array))
                {
                    $error_array[] = 'Not allowed to reference variable "' 
                            . $var_name . '" in the criteria';
                } else
                if(!in_array($var_name, $var_map))
                {
                    $error_array[] = 'Variable "' 
                            . $var_name . '" does not exist';
                }
            }
        } catch (\Exception $ex) {
            $error_array[] = $ex->getMessage();
        }
        
        //Organize the results and return them.
        $results_array['isokay'] = count($error_array) == 0;
        $results_array['errors'] = $error_array;
        //The top level rule supported functions with params
        $results_array['functions'] = $compiler_results_arr['functions'];
        return $results_array;
    }

    /**
     * Does not evaluate a value but checks the syntax and names of variables.
     * @param string $expression measure criteria
     * $param array $disallowed_vars_array names of variables we will not allow
     * @return array with validation details (isokay=TRUE/FALSE,errors=array)
     */
    public function validateMeasureExpression($expression,$disallowed_vars_array=NULL)
    {
        if($expression == 'INPUT' 
                || $expression == 'CODED' 
                || $expression == 'DERIVED' 
                ||  substr($expression,0,9) == 'TEXT FROM')
        {
            //The measure is input from external source.
            $error_array = array();
        } else {
            if($disallowed_vars_array == NULL)
            {
                $disallowed_vars_array = array();
            }
            $results_array = array();
            $error_array = array();
            $is_good = TRUE;

            //Run the compiler
            try
            {
                $var_map = $this->getAllCurrentVariableNames(FALSE);
                $parserengine = 
                        new \simplerulesengine\MeasureExpressionParser($var_map);
                $compiler_results_arr = $parserengine->compile($expression);
                foreach($compiler_results_arr['errors'] as $error_msg)
                {
                    $error_array[] = $error_msg;
                }
                foreach($compiler_results_arr['dependencies'] as $var_name)
                {
                    if(in_array($var_name,$disallowed_vars_array))
                    {
                        $error_array[] = 'Not allowed to reference variable "' 
                                . $var_name 
                                . '" in the criteria';
                    } else
                    if(!in_array($var_name, $var_map))
                    {
                        $error_array[] = 'Variable "' 
                                . $var_name 
                                . '" does not exist';
                    }
                }
            } catch (\Exception $ex) {
                $error_array[] = $ex->getMessage();
            }
        }
        
        //Organize the results and return them.
        $results_array['isokay'] = count($error_array) == 0;
        $results_array['errors'] = $error_array;
        return $results_array;
    }

    /**
     * If a name is not in this array then it is not a valid 
     * variable for a criteria formula
     * @return array of names
     */
    public function getAllCurrentVariableNames($include_rule_names=TRUE)
    {
        $var_map = array();
        $a1 = $this->getRequiredBaselineInfoKeys();
        $a2 = $this->getRequiredCandidateDataKeys();
        $a3 = $this->getActiveMeasureNames();
        SREUtility::mergeIntoExistingArray($var_map,$a1);
        SREUtility::mergeIntoExistingArray($var_map,$a2);
        SREUtility::mergeIntoExistingArray($var_map,$a3);
        if(!$include_rule_names)
        {
            $a4 = $this->getActiveRuleNames();
            SREUtility::mergeIntoExistingArray($var_map,$a4);
        }
        return $var_map;
    }
    
    /**
     * The constructor requires an array with these keys.
     * @return array of names
     */
    public function getRequiredBaselineInfoKeys()
    {
        return $this->m_sre_context->getRequiredBaselineInfoKeys();
    }
    
    /**
     * The evaluator requires an array with these keys.
     * @return array of names
     */
    public function getRequiredCandidateDataKeys()
    {
        return $this->m_sre_context->getRequiredCandidateDataKeys();
    }
    
    /**
     * The names of all available measures.
     * @return array of names
     */
    public function getActiveMeasureNames()
    {
        $names_array = array();
        $result = db_select($this->m_sre_context->getMeasureTablename(),'f')
                ->fields('f',array('measure_nm'))
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $names_array[] = $record['measure_nm'];
        }
        return $names_array;
    }
    
    /**
     * The names of all available rules.
     * @return array of names
     */
    public function getActiveRuleNames()
    {
        $names_array = array();
        $result = db_select($this->m_sre_context->getRuleTablename(),'f')
                ->fields('f',array('rule_nm'))
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $names_array[] = $record['rule_nm'];
        }
        return $names_array;
    }

    /**
     * Check for required keys
     * @param key/value array $baselineinfo_array 
     * @throws Exception if missing a required key
     */
    protected function validateBaselineInfo($baselineinfo_array)
    {
        $required_keys_array = $this->getRequiredBaselineInfoKeys();
        $missing_key_arrays = NULL;
        foreach($required_keys_array as $key)
        {
            if(!array_key_exists($key, $baselineinfo_array))
            {
                $missing_key_arrays[] = $key;
            }
        }
        if(is_array($missing_key_arrays))
        {
            throw new \Exception('Missing required baseline '
                    . 'information value(s) for [' 
                    . print_r($missing_key_arrays,TRUE) 
                    . '] ONLY GOT>>>' . print_r($baselineinfo_array, TRUE));
        }
    }

    /**
     * Check for required keys
     * @param key/value array $baselineinfo_array 
     * @throws Exception if missing a required key
     */
    protected function validateCandidateValues($candidate_values)
    {
        $required_keys_array = $this->getRequiredCandidateDataKeys();
        foreach($required_keys_array as $key)
        {
            if(!array_key_exists($key, $candidate_values))
            {
                throw new \Exception('Missing required exam '
                        . 'information value for [' . $key 
                        . '] ONLY GOT>>>' . print_r($candidate_values, TRUE));
            }
        }
    }

    /**
     * Compute all the results when this function is called.
     * @param array $candidate_values is an array of exam specific values
     * @return instance of Results 
     */
    public function getResults($candidate_values=NULL)
    {
        $this->validateCandidateValues($candidate_values);
        $this->m_candidate_values=$candidate_values;
        
        $all_var_map = NULL;
        try
        {
            $all_var_map = $this->m_measures_obj->evaluate($candidate_values);
        } catch (\Exception $ex) {
            throw new \Exception('Failed evaluation of measures because ' 
                    . $ex->getMessage());
        }

        try
        {
            $all_var_map = $this->m_rules_obj->evaluate($all_var_map);
        } catch (\Exception $ex) {
            throw new \Exception('Failed evaluation of rules because ' 
                    . $ex->getMessage());
        }

        try
        {
            $results_array = array();
            $rules_array = $this->m_rules_obj->getAllRules();
            foreach($rules_array as $key=>$rule_item)
            {
                if(array_key_exists($key,$all_var_map))
                {
                    $result = $all_var_map[$key];
                    if($result == TRUE)
                    {
                        $rule_array = $rule_item->getAllInfo();
                        $rule_name = $rule_array['rule_nm'];
                        $category_tx = $rule_array['category_nm'];
                        $summary_msg = $rule_array['summary_msg_tx'];
                        $msg_tx = $rule_array['msg_tx'];
                        $explanation_tx = $rule_array['explanation'];
                        $confirmation_required = ($rule_array['req_ack_yn'] == 1);
                        $source_obj = 
                          new \simplerulesengine\SRESourceItem($rule_name
                                        ,$category_tx
                                        ,$summary_msg
                                        ,$confirmation_required
                                        ,$msg_tx,$explanation_tx); 
                        $result_source_arr = array();
                        $result_source_arr[] = $source_obj;
                        $result = 
                          new \simplerulesengine\SREResult($result_source_arr);
                        $results_array[] = $result;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception('Failed creation of results because ' 
                    . $ex->getMessage());
        }
        
        //Now return all the results we found.
        return new \simplerulesengine\SREResults($results_array);
    }
}
