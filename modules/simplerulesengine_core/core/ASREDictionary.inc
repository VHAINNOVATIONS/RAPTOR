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
 * Document the coded rule inputs here.
 *
 * @author Frank Font
 */
abstract class ASREDictionary
{
    protected $m_sre_context = NULL;
    
    public function __construct($sre_context)
    {
        $this->m_sre_context = $sre_context;
    }
    
    /**
     * Get normalized set of columns from the table.
     */
    private function getInputsFromTable($tablename,$namefield,$defsource='database')
    {
        $all_inputs_array = array();
        $result = db_select($tablename,'f')
                ->fields('f')
                ->condition('active_yn',1,'=')
                ->orderBy($namefield)
                ->execute();
        while($record = $result->fetchAssoc())
        {
            $name = $record[$namefield];
            $inputs_array = array();
            $inputs_array['source'] = $defsource;
            $inputs_array['locked'] = $record['readonly_yn'];
            $inputs_array['category_nm'] = $record['category_nm'];
            $inputs_array['name'] = $name;
            if($namefield == 'rule_nm')
            {
                $inputs_array['purpose_tx'] = $record['explanation'];
                $inputs_array['criteria_tx'] = $record['trigger_crit'];
                $inputs_array['return']='boolean';
            } else {
                $inputs_array['purpose_tx'] = $record['purpose_tx'];
                $inputs_array['criteria_tx'] = $record['criteria_tx'];
                if($namefield == 'measure_nm')
                {
                    $inputs_array['return']=$record['return_type'];
                } else {
                    $inputs_array['return']='boolean';
                }
            }
            $all_inputs_array[$name] = $inputs_array;
        }
        return $all_inputs_array;
    }
    
    /**
     * Returns array of all the valid inputs for rules.
     */
    public function getActiveRuleInputs()
    {
        $measuretablename = $this->m_sre_context->getMeasureTablename();
        $all_measure_inputs = $this->getInputsFromTable($measuretablename,'measure_nm','db:measure');
        
        $all_inputs_array = $all_measure_inputs;

        $all_coded_array = $this->getDefinitionOfAllCodedInputs();
        
        $a = $this->m_sre_context->getRequiredBaselineInfoKeys();
        foreach($a as $name)
        {
            if(!isset($all_inputs_array[$name]))
            {
                $inputs_array = array();
                if(!isset($all_coded_array[$name]))
                {
                    drupal_set_message('Did not find a definition for '.$name,'warning');
                    $inputs_array['return']      = 'UNDEFINED';
                    $inputs_array['category_nm'] = 'UNDEFINED';
                    $inputs_array['purpose_tx']  = 'UNDEFINED';
                    $inputs_array['criteria_tx'] = 'UNDEFINED';
                } else {
                    $aDefinition = $all_coded_array[$name];
                    $inputs_array['return']      = $aDefinition['return'];
                    $inputs_array['category_nm'] = $aDefinition['category_nm'];
                    $inputs_array['purpose_tx']  = $aDefinition['purpose_tx'];
                    $inputs_array['criteria_tx'] = $aDefinition['criteria_tx'];
                }                
                $inputs_array['name'] = $name;
                $inputs_array['source'] = 'coded';
                $inputs_array['locked'] = 1;
                $all_inputs_array[$name] = $inputs_array;
            }
        }
        $a = $this->m_sre_context->getRequiredCandidateDataKeys();
        foreach($a as $name)
        {
            if(!isset($all_inputs_array[$name]))
            {
                $inputs_array = array();
                if(!isset($all_coded_array[$name]))
                {
                    drupal_set_message('Did not find a definition for '.$name,'warning');
                    $inputs_array['return']      = 'UNDEFINED';
                    $inputs_array['category_nm'] = 'UNDEFINED';
                    $inputs_array['purpose_tx']  = 'UNDEFINED';
                    $inputs_array['criteria_tx'] = 'UNDEFINED';
                } else {
                    $aDefinition = $all_coded_array[$name];
                    $inputs_array['return']      = $aDefinition['return'];
                    $inputs_array['category_nm'] = $aDefinition['category_nm'];
                    $inputs_array['purpose_tx']  = $aDefinition['purpose_tx'];
                    $inputs_array['criteria_tx'] = $aDefinition['criteria_tx'];
                }
                $inputs_array['name'] = $name;
                $inputs_array['source'] = 'coded';
                $inputs_array['locked'] = 1;
                $all_inputs_array[$name] = $inputs_array;
            }
        }
        return $all_inputs_array;
    }

    /**
     * Returns array of all the rules
     */
    public function getActiveRules()
    {
        $ruletablename = $this->m_sre_context->getRuleTablename();
        $all_rule_inputs = $this->getInputsFromTable($ruletablename,'rule_nm','db:rule');
        return $all_rule_inputs;
    }

    /**
     * Returns array of all the valid dictionary items
     */
    public function getActiveDictionaryItems()
    {
        $measuretablename = $this->m_sre_context->getMeasureTablename();
        $all_measure_inputs = $this->getInputsFromTable($measuretablename,'measure_nm','db:measure');
        
        $ruletablename = $this->m_sre_context->getRuleTablename();
        $all_rule_inputs = $this->getInputsFromTable($ruletablename,'rule_nm','db:rule');
        $all_coded_array = $this->getDefinitionOfAllCodedInputs();
        
        $all_inputs_array = array_merge($all_measure_inputs,$all_rule_inputs,$all_coded_array);
        return $all_inputs_array;
    }
    
    /**
     * Return array of descriptions for each programmer coded input.
     */
    abstract public function getDefinitionOfAllCodedInputs();
    
}
