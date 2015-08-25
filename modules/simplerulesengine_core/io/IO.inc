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
 * Helpful input output utilities.
 *
 * @author Frank Font
 */
class IO 
{
    
    private $m_sre_context = NULL;
    
    /**
     *Initialize with all the core information this engine needs. 
     */        
    public function __construct($sre_context)
    {
        $this->m_sre_context = $sre_context;
        if($this->m_sre_context == NULL)
        {
            throw new \Exception('The SREContext cannot be NULL!');
        }
    }
    
    /**
     * Dump entire rulebase into an XML format.
     * @return XML string
     */
    public function getRulebaseAsXML($sRulebaseName
            ,$aExtraTopLevelAttribs=array()
            ,$sExtraContextNoteText=NULL)
    {
        $xml_markup = '';
        
        //Start with the engine input definitions.
        $all_inputs_array = $this->m_sre_context->getDictionary()->getActiveRuleInputs();
        $baseline_inputs_array = $this->m_sre_context->getRequiredBaselineInfoKeys();
        $candidate_inputs_array = $this->m_sre_context->getRequiredCandidateDataKeys();
        $baseline_indent = "    ";
        $inputs = '';
        foreach($all_inputs_array as $inputs_array)
        {
            $name = $inputs_array['name'];
            $inputs .= "\n$baseline_indent".'<DictionaryItem name="'.$name.'"'
                    . ' category_nm="'.$inputs_array['category_nm'].'"'
                    . ' logic_source="'.$inputs_array['source'].'">'
                    . "\n$baseline_indent".'  <Return>'.$inputs_array['return'].'</Return>'
                    . "\n$baseline_indent".'  <Purpose>'.htmlentities($inputs_array['purpose_tx']).'</Purpose>'
                    . "\n$baseline_indent".'  <Criteria>'.htmlentities($inputs_array['criteria_tx']).'</Criteria>'
                    . "\n$baseline_indent".'</DictionaryItem>';
        } 
        $baseline_indent = "  ";
        $xml_markup .= "\n$baseline_indent".'<Dictionary>'
                .$inputs
                ."\n$baseline_indent".'</Dictionary>';
        
        //Now add all the user level RULE definitions
        $tablename = $this->m_sre_context->getRuleTablename();
        $result = db_select($tablename, 'n')
                ->fields('n')
                ->condition('active_yn',1,'=')
                ->orderBy('rule_nm')
                ->execute();
        $defs = '';
        $baseline_indent = "    ";
        while($record = $result->fetchAssoc())
        {
            $name = $record['rule_nm'];
            $category_tx = $record['category_nm'];
            $version_num = $record['version'];
            $summary_msg_tx = $record['summary_msg_tx'];
            $msg_tx = $record['msg_tx'];
            $explanation_tx = $record['explanation'];
            $criteria_tx = $record['trigger_crit'];
            $req_ack_tx = $record['req_ack_yn'] == 1 ? 'Yes' : 'No';
            $defs .= "\n$baseline_indent".'<UserLevelRuleDefinition'
                    . ' name="'.$name.'"'
                    . ' category_nm="'.$category_tx.'"'
                    . ' version="'.$version_num.'"'
                    . ' require_acknowledgement="'.$req_ack_tx.'"'
                  . ' >';
            $defs .= "\n$baseline_indent".'  <SummaryMessage>'.htmlentities($summary_msg_tx).'</SummaryMessage>';
            $defs .= "\n$baseline_indent".'  <Message>'.htmlentities($msg_tx).'</Message>';
            $defs .= "\n$baseline_indent".'  <Explanation>'.htmlentities($explanation_tx).'</Explanation>';
            $defs .= "\n$baseline_indent".'  <Criteria>'.htmlentities($criteria_tx).'</Criteria>';
            $defs .= "\n$baseline_indent".'</UserLevelRuleDefinition>';
        }
        $baseline_indent = "  ";
        $xml_markup .= "\n$baseline_indent".'<UserLevelRuleDefinitions table_nm="'.$tablename.'">'
                .$defs
                ."\n$baseline_indent".'</UserLevelRuleDefinitions>';
        
        //Now add all the MEASURE definitions
        $tablename = $this->m_sre_context->getMeasureTablename();
        $result = db_select($tablename, 'n')
                ->fields('n')
                ->orderBy('measure_nm')
                ->condition('active_yn',1,'=')
                ->execute();
        $defs = '';
        $baseline_indent = "    ";
        while($record = $result->fetchAssoc())
        {
            $name = $record['measure_nm'];
            $version_num = $record['version'];
            $defs .= "\n$baseline_indent".'<MeasureDefinition'
                    . ' name="'.$name.'"'
                    . ' version="'.$version_num.'"'
                  . ' />';
        }
        $baseline_indent = "  ";
        $xml_markup .= "\n$baseline_indent".'<MeasureDefinitions table_nm="'.$tablename.'">'
                .$defs
                ."\n$baseline_indent".'</MeasureDefinitions>';

        //List all the engine initialization inputs.
        $inputs = '';
        $baseline_indent = "    ";
        foreach($baseline_inputs_array as $name)
        {
            $inputs .= "\n$baseline_indent".'<EngineInitializationTimeInputValue name="'.$name.'" />';
        } 
        $baseline_indent = "  ";
        $xml_markup .= "\n$baseline_indent".'<EngineInitializationTimeInputValues>'
                .$inputs
                ."\n$baseline_indent".'</EngineInitializationTimeInputValues>';
        
        //List all the engine evaluation inputs.
        $inputs = '';
        $baseline_indent = "    ";
        foreach($candidate_inputs_array as $name)
        {
            $inputs .= "\n$baseline_indent".'<EvaluationTimeInputValue name="'.$name.'" />';
        } 
        $baseline_indent = "  ";
        $xml_markup .= "\n$baseline_indent".'<EvaluationTimeInputValues>'
                . $inputs
                . "\n$baseline_indent".'</EvaluationTimeInputValues>';
        
        //Wrap it all up.
        $extraattribs = '';
        foreach($aExtraTopLevelAttribs as $attribname=>$attribvalue)
        {
           $extraattribs .= " $attribname=\"$attribvalue\"";
        }
        $dumpdate = date('c');
        $xmlastext = '<?xml version="1.0"?>'
                . "\n".'<SRERuleBase'
                . ' name="'.$sRulebaseName.'"'
                . ' export_dt="'.$dumpdate.'"'
                . $extraattribs
                . ' xmlns="http://sanbusinessconsultants.com/opensource/simplerulesengine/2014/1"'
                . '>';
        if($sExtraContextNoteText != NULL)
        {
            $xmlastext .= "\n<ContextNote>\n$sExtraContextNoteText\n</ContextNote>";
        }
        $xmlastext .= "\n$xml_markup\n</SRERuleBase>";
        return $xmlastext;
    }
}
