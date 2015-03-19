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

require_once 'SREMeasure.inc';

/**
 * All instances of a measures from the database.
 * 
 * @author Frank Font of SAN Business Consultants
 */
class SREMeasures
{
    private $m_sre_context = NULL;
    
    private $m_snapshot_time = NULL;
    private $m_measures_array = NULL;
    private $m_baselineinfo_key_map = NULL; //Simple key value pairings
    private $m_parserengine = NULL;
    private $m_debug = 0;
    private $m_max_allowed_eval = 100;  //Some number we do not expect to hit.

    /**
     * Create an instance.
     */
    public function __construct($sre_context, $baselineinfo_key_map)
    {
        $this->m_sre_context = $sre_context;
        $this->m_baselineinfo_key_map = $baselineinfo_key_map;
    }
    
    /**
     * Recursively evaluate a measure and all its precondition measures as needed
     * @param type $name name of the measure to evaluate
     * @param type $compiled_result compilation details for the measure
     * @param type $all_var_map all currently computed variables
     * @return result for evaluation of one measure
     */
    private function evalOneMeasure($name,$compiled_result,&$all_var_map)
    {
        $this->m_debug++;
        if($this->m_debug > $this->m_max_allowed_eval)
        {
            //Indicates potential cycle in the graph.
            throw new \Exception('Too many evalOneMeasure iterations! ' . print_r($all_var_map,TRUE));
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
                $measure_item = $this->m_measures_array[$dependency_name];
                if($measure_item == NULL)
                {
                    throw new \Exception('Got NULL for dependency '
                            .$dependency_name.' details>>>' . print_r($all_var_map,TRUE));
                }
                try
                {
                    //Compute this dependency first.
                    $measure_info = $measure_item->getAllInfo();
                    $expression = $measure_info['criteria_tx'];
                    $dep_compiled_result = 
                            $this->m_parserengine->compile($expression);
                    $all_var_map[$dependency_name] = 
                            $this->evalOneMeasure($dependency_name
                                    , $dep_compiled_result
                                    , $all_var_map);
                } catch (\Exception $ex) {
                    throw new \Exception('Failed to compute dependency '
                            .$dependency_name.' details>>>' 
                            . print_r($measure_item,TRUE) 
                            . ' because (' . $ex->getMessage() . ')');
                }
            }
        }
        
        //Now compute this measure.
        try 
        {
            $root_node = $compiled_result['treeroot'];
            $result = $root_node->getValue();
            return $result;
        } catch (\Exception $ex) {
            throw new \Exception('Failed to compute measure '
                    .$name.' details>>>' 
                    . print_r($compiled_result,TRUE) 
                    . '<br>Exception msg='.$ex->getMessage());
        }
    }
    
    /**
     * Compute values for all the measures and return concatenated with existing key map data
     * @param type $current_key_map
     * @return array key value pairs
     */
    public function evaluate($current_key_map=NULL)
    {
        $this->m_debug = 0;
        
        if($this->m_measures_array == NULL)
        {
            $this->refresh();
        }
        //$all_var_map = array_merge($this->m_baselineinfo_key_map,$current_key_map);
        $all_var_map = array();
        SREUtility::mergeIntoExistingArray($all_var_map,$this->m_baselineinfo_key_map);
        SREUtility::mergeIntoExistingArray($all_var_map,$current_key_map);
        $this->m_parserengine = 
                new \simplerulesengine\MeasureExpressionParser($all_var_map);
        $key = NULL;
        $error_array = array();
        foreach($this->m_measures_array as $key=>$measure_item)
        {
            if(!array_key_exists($key, $all_var_map))
            {
                try
                {
                    $measure_info = $measure_item->getAllInfo();
                    $expression = $measure_info['criteria_tx'];
                    if(trim($expression) == '')
                    {
                        //Just produce a warning instead of failing.
                        drupal_set_message(t('Criteria is missing for measure ' . $key),'warning');
                    } else {
                        if(!isset($current_key_map[$key]))
                        {
                            $compiled_result = $this->m_parserengine->compile($expression);
                            if($compiled_result['haserrors'])
                            {
                               $error_array[$key] = $compiled_result['errors'];
                            } else {
                               $all_var_map[$key] = 
                                       $this->evalOneMeasure($key,$compiled_result,$all_var_map);
                            }
                        }
                    }
                } catch (\Exception $ex) {
                    throw new \Exception('Cannot evaluate measure '
                            .$key.' because '.$ex->getMessage() 
                            . '<br>varmap=' . print_r($all_var_map,TRUE) );
                }
            }
        }
       
        //Report the errors.
        if(count($error_array) > 0)
        {
            $msg = 'Found ' . count($error_array) 
                    . ' measure evaluation errors:<ol>';
            foreach($error_array as $key=>$value)
            {
                if(!is_array($value))
                {
                    $msg .= '<li>Measure "' . $key . '" fails because ' . $value;
                } else {
                    if(count($value) == 1)
                    {
                        foreach($value as $item)
                        {
                            $msg .= '<li>Measure "' 
                                    . $key 
                                    . '" fails because ' . $item;
                        }
                    } else {
                        $msg .= '<li>Measure "' 
                                . $key 
                                . '" fails because of ' . count($value) . ' errors';
                        $msg .= '<ol>';
                        foreach($value as $item)
                        {
                            $msg .= '<li>Measure "' 
                                    . $key 
                                    . '" fails because ' . $item;
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
        $this->loadAllMeasures();
    }
    
    /**
     * Get all the rules, from cache if already available.
     * @return type array of objects
     */
    public function getAllMeasures()
    {
        if($this->m_measures_array == NULL)
        {
            $this->refresh();
        }
        return $this->m_measures_array;
    }
    
    /**
     * Load cache from database.
     */
    private function loadAllMeasures()
    {
        //Load all the measures first
        $this->m_snapshot_time=time();
        $this->m_measures_array = array();
        $result = db_select($this->m_sre_context->getMeasureTablename(),'r')
                ->fields('r')
                ->condition('active_yn',1,'=')
                ->orderBy('measure_nm','DESC')
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $rule_item = new \simplerulesengine\SREMeasure($record);
            $key_tx = $record['measure_nm'];
            $this->m_measures_array[$key_tx] = $rule_item;
        }
        if(count($this->m_measures_array) == 0)
        {
            $msg = 'No measures have been defined';
            drupal_set_message($msg,'warning');
            error_log($msg);
        }
    }
}
