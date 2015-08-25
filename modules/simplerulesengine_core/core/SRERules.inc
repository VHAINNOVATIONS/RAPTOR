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

 * ----------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

require_once 'SRERule.inc';

/**
 * All instances of a rules from the database.
 *
 * @author Frank Font of SAN Business Consultants
 */
class SRERules 
{
    private $m_sre_context = NULL;

    private $m_snapshot_time = NULL;
    private $m_rules_array = NULL;
    private $m_baselineinfo_key_map = NULL; //Simple key value pairings
    private $m_parserengine = NULL;
    private $m_debug = 0;
    private $m_max_allowed_eval = 100;     //Some number we do not expect to hit.
    
    /**
     * Create an instance.
     */
    public function __construct($sre_context, $baselineinfo_key_map)
    {
        $this->m_sre_context = $sre_context;
        $this->m_baselineinfo_key_map = $baselineinfo_key_map;
    }
    
    /**
     * Recursively evaluate a rule and all its precondition rules as needed
     * @param type $name name of the rule to evaluate
     * @param type $compiled_result compilation details for the rule
     * @param type $all_var_map all currently computed variables
     * @return result for evaluation of one rule
     */
    private function evalOneRule($name,$compiled_result,&$all_var_map)
    {
        $this->m_debug++;
        if($this->m_debug > $this->m_max_allowed_eval)
        {
            //Indicates potential cycle in the graph.
            throw new \Exception('Too many evalOneRule iterations! ' 
                    . print_r($all_var_map,TRUE));
        }
        
        //Result already computed?
        if(array_key_exists($name, $all_var_map))
        {
            //Just return it.
            return $all_var_map[$name];
        }
        
        //Satisfy all the dependencies first.
        $dependencies_array = $compiled_result['dependencies'];
        foreach($dependencies_array as $dependency_name)
        {
            //Dependency already satisfied?
            if(!array_key_exists($dependency_name, $all_var_map))
            {
                
                //drupal_set_message('LOOK DID NOT FINISH THIS>>>>'.$dependency_name);
                
                $rule_item = $this->m_rules_array[$dependency_name];
                if($rule_item == NULL)
                {
                    throw new \Exception('Got NULL for dependency '
                            .$dependency_name.' details>>>' 
                            . print_r($all_var_map,TRUE));
                }
                try
                {
                    //Compute this dependency first.
                    $ruleinfo_array = $rule_item->getAllInfo();
                    $expression = $ruleinfo_array['criteria_tx'];
                    $dep_compiled_result = $this->m_parserengine->compile($expression);
                    $all_var_map[$dependency_name] 
                            = $this->evalOneRule($dependency_name
                                    , $dep_compiled_result
                                    , $all_var_map);
                } catch (\Exception $ex) {
                    throw new \Exception('Failed to compute dependency '
                            . $dependency_name
                            . ' details>>>' . print_r($rule_item,TRUE) 
                            . ' (' . $ex->getMessage() . ')');
                }
            }
        }
        
        //Now compute this rule.
        try
        {
            $root_node = $compiled_result['treeroot'];
            $result = $root_node->getValue();
            return $root_node->getValue();
        } catch (\Exception $ex) {
            throw new \Exception('Failed to compute rule '
                    . $name
                    . ' details>>>' 
                    . print_r($compiled_result,TRUE) 
                    . '<br>Exception msg='.$ex->getMessage());
        }
    }

    /**
     * Compute values for all the rules and return concatenated with existing key map data
     * @param type $current_key_map
     * @return array key value pairs
     */
    public function evaluate($current_key_map=NULL)
    {
        $this->m_debug = 0;
        
        if($this->m_rules_array == NULL)
        {
            $this->refresh();
        }
        //$all_var_map = array_merge($this->m_baselineinfo_key_map,$current_key_map);
        $all_var_map = array();
        SREUtility::mergeIntoExistingArray($all_var_map,$this->m_baselineinfo_key_map);
        SREUtility::mergeIntoExistingArray($all_var_map,$current_key_map);
        $this->m_parserengine = new \simplerulesengine\RuleExpressionParser($all_var_map);
        $key = NULL;
        $error_array = array();
        foreach($this->m_rules_array as $key=>$rule_item)
        {
            try
            {
                $ruleinfo_array = $rule_item->getAllInfo();
                $expression = $ruleinfo_array['trigger_crit'];
                if(trim($expression) == '')
                {
                    //Just procduce a warning instead of failing.
                    //drupal_set_message('Criteria is missing for rule ' . $key,'warning');
                } else {
                    if(!isset($current_key_map[$key]))
                    {
                        $compiled_result = 
                                $this->m_parserengine->compile($expression);
                        if($compiled_result['haserrors'])
                        {
                           $error_array[$key] = $compiled_result['errors'];
                        } else {
                            $all_var_map[$key] = $this->evalOneRule($key
                                    ,$compiled_result
                                    ,$all_var_map);
                        }
                    }
                }
            } catch (\Exception $ex) {
                throw new \Exception('Cannot evaluate rule '
                        .$key.' because '.$ex->getMessage());
            }
        }

        //Report the errors.
        if(count($error_array) > 0)
        {
            $msg = 'Found ' . count($error_array) 
                    . ' rule evaluation errors:<ol>';
            foreach($error_array as $key=>$value)
            {
                if(!is_array($value))
                {
                    $msg .= '<li>Rule "' 
                            . $key . '" fails because ' . $value;
                } else {
                    if(count($value) == 1)
                    {
                        foreach($value as $item)
                        {
                            $msg .= '<li>Rule "' 
                                    . $key . '" fails because ' . $item;
                        }
                    } else {
                        $msg .= '<li>Rule "' 
                                . $key . '" fails because of ' 
                                . count($value) . ' errors';
                        $msg .= '<ol>';
                        foreach($value as $item)
                        {
                            $msg .= '<li>Rule "' 
                                    . $key . '" fails because ' . $item;
                        }
                        $msg .= '</ol>';
                    }
                }
            }
            throw new \Exception($msg);
        }
        
        return $all_var_map;
    }

    public function getSnapshotTime()
    {
        return $this->m_snapshot_time;
    }
    
    /**
     * Refresh set of rules from the database.
     */
    public function refresh()
    {
        $this->loadAllRules();
    }
    
    /**
     * Get all the rules, from cache if already available.
     * @return type array of objects
     */
    public function getAllRules()
    {
        if($this->m_rules_array == NULL)
        {
            $this->refresh();
        }
        return $this->m_rules_array;
    }
    
    /**
     * Load cache from database.
     */
    private function loadAllRules()
    {
        $this->m_snapshot_time=time();
        $this->m_rules_array = array();
        $result = db_select($this->m_sre_context->getRuleTablename(),'r')
                ->fields('r')
                ->condition('active_yn',1,'=')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $rule_item = new \simplerulesengine\SRERule($record);
            $key_tx = $record['rule_nm'];
            $this->m_rules_array[$key_tx] = $rule_item;
        }
        if(count($this->m_rules_array) == 0)
        {
            $msg = 'No rules have been defined';
            drupal_set_message($msg,'warning');
            error_log($msg);
        }
    }
    
}
