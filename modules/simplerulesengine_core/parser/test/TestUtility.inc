<?php
/**
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

require_once dirname(__FILE__) . '/../MeasureExpressionParser.inc';
require_once dirname(__FILE__) . '/../ITreeNode.inc';
require_once dirname(__FILE__) . '/../ATreeNode.inc';
require_once dirname(__FILE__) . '/../TNConstant.inc';
require_once dirname(__FILE__) . '/../TNOperator.inc';
require_once dirname(__FILE__) . '/../TNOBoolean.inc';
require_once dirname(__FILE__) . '/../TNONumeric.inc';
require_once dirname(__FILE__) . '/../TNVariable.inc';

/**
 * Utilities for the parser tests
 *
 * @author Frank Font
 */
class TestUtility
{
    public static function runOneTreeCompleteTest($test_num,$is_expected_result, $var_map, $expression,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        $is_failed=FALSE;
        try
        {
            $parserengine = new \simplerulesengine\MeasureExpressionParser($var_map);
            $root_node = $parserengine->parse($expression);
        } catch (\Exception $ex) {
            //Cannot assume that root is okay.
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': "' . $expression . '" = Caught error ' . $ex->getMessage() );
            $is_failed=TRUE;
            //die('LOOK NOW' . print_r($test_detail_array,TRUE));
        }
        if(!$is_failed)
        {
            $is_failed = TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
            if($is_failed)
            {
                //Add more debug info.
                $test_detail_array[count($test_detail_array)-1] .= '<br>NOTE Test'.$test_num . ' was for expression = "' . $expression . '"';
            }
        }
        return $is_failed;
    }

    
    public static function runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        TestUtility::runOneCompilerTestAnyType('Measure',$test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
    }
    
    public static function runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        TestUtility::runOneCompilerTestAnyType('Rule',$test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
    }
    
    private static function runOneCompilerTestAnyType($sType,$test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        $is_failed=FALSE;
        $sResultMsg = NULL;
        $bCompilerErrors=FALSE;
        try
        {
            if($sType=='Measure')
            {
                $parserengine = new \simplerulesengine\MeasureExpressionParser($var_map);
            } else if($sType=='Rule') {
                $parserengine = new \simplerulesengine\RuleExpressionParser($var_map);
            } else {
                throw new \Exception('Unrecognized expression declared as type [' . $sType . ']');
            }
            $results_array = $parserengine->compile($expression);
            $bCompilerErrors = $results_array['haserrors'];
            if(!$bExpectSytaxError && $bCompilerErrors)
            {
                $sDetails = implode('<li>',$results_array['errors']);
                $sResultMsg = ('Failed Compiler Test' . $test_num . ': "' . $expression . '"<ol><li>' . $sDetails  .'</ol>');
                $is_failed=TRUE;
            }
        } catch (\Exception $ex) {
            if($bExpectSytaxError)
            {
                $sResultMsg = ('Okay Test' . $test_num . ':[' . $expression . '] has expected parser error; details->' . $ex->getMessage());
            } else {
                $sResultMsg = ('Failed Compiler Test' . $test_num . ': "' . $expression . '" = Caught error ' . $ex->getMessage() );
                $is_failed=TRUE;
            }
        }
        if(!$bExpectSytaxError && !$is_failed)
        {
            $aDepVars = $results_array['dependencies'];
            $aMissing = array();
            foreach($aDepVars as $var_name)
            {
                if(!in_array($var_name, $aExpectedVars))
                {
                    $aMissing[$var_name] = 'Invalid ' . $var_name;
                }
            }
            foreach($aExpectedVars as $var_name)
            {
                if(!in_array($var_name, $aDepVars))
                {
                    $aMissing[$var_name] = 'Expected ' . $var_name;
                }
            }
            if(count($aMissing) > 0)
            {
                $is_failed=TRUE;
                $sResultMsg = ('Failed Test' . $test_num . ':[' . $expression . '] has parsing issue for these '.count($aMissing).' variable names ' . print_r($aMissing,TRUE));
            } else {
                $sResultMsg = ('Okay Test' . $test_num . ':[' . $expression . '] has ' . count($aDepVars) . ' vars expected ' . count($aExpectedVars) . ' vars');
            }
        }
        if($is_failed)
        {
            $failed_count++;
            if($sResultMsg == NULL)
            {
                $sResultMsg = ('Failed Test' . $test_num . ':[' . $expression . '] compiler result = '. print_r($results_array,TRUE));
            }
        }
        if($sResultMsg == NULL)
        {
            $tf1 = $bExpectSytaxError ? 'Compiler Error' : 'Compiler Success';
            $tf2 = $bCompilerErrors ? 'Compiler Error' : 'Compiler Success';
            $sResultMsg = ('Okay Test' . $test_num . ':[' . $expression . '] expected "'.$tf1. '" and got "'.$tf2.'"');
        }
        $test_detail_array[] = $sResultMsg;        
        return $is_failed;
    }

    public static function runOneTreeEvalTest($test_num,$is_expected_result,$root_node,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        $is_failed=FALSE;
        try
        {
            $bEvalResult = $root_node->getValue();
            if($bEvalResult === NULL)
            {
                $sEvalResultText = 'NULL';
            } else {
                $sEvalResultText = ($bEvalResult ? 'TRUE' : 'FALSE');
            }
            if($is_expected_result === NULL)
            {
                $sExpectedResultTxt = 'NULL';
            } else {
                $sExpectedResultTxt = ($is_expected_result ? 'TRUE' : 'FALSE');
            }
            if($is_expected_result !== $bEvalResult && !($is_expected_result === NULL && $bEvalResult === NULL))
            {
                $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
                $is_failed=TRUE;
            } else {
                $test_detail_array[] = ('Okay Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
            }
            if($bEvalResult !== NULL)
            {
                $nodes_array[$bEvalResult][] = $root_node;
            }
        } catch (\Exception $ex) {
            $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . 'Caught error ' . $ex->getMessage() );
            $is_failed=TRUE;
        }
        if($is_failed)
        {
            $failed_count++;
        }
        return $is_failed;
    }

    public static function runOneTreeNumericEvalTest($test_num,$nExpectedResult,$root_node,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        $is_failed=FALSE;
        try
        {
            $nEvalResult = $root_node->getValue();
            if($nEvalResult === NULL)
            {
                $sEvalResultText = 'NULL';
            } else {
                $sEvalResultText = $nEvalResult;
            }
            if($nExpectedResult === NULL)
            {
                $sExpectedResultTxt = 'NULL';
            } else {
                $sExpectedResultTxt = $nExpectedResult;
            }
            if($nExpectedResult !== $nEvalResult && !($nExpectedResult === NULL && $nEvalResult === NULL))
            {
                $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
                $is_failed=TRUE;
            } else {
                $test_detail_array[] = ('Okay Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
            }
            if($nEvalResult != NULL)
            {
                $nodes_array[$nEvalResult][] = $root_node;
            }
        } catch (\Exception $ex) {
            $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . 'Caught error ' . $ex->getMessage() );
            $is_failed=TRUE;
        }
        if($is_failed)
        {
            $failed_count++;
        }
        return $is_failed;
    }
    
    public static function runOneTreeTextEvalTest($test_num,$is_expected_result,$root_node,&$test_detail_array,&$failed_count,&$nodes_array)
    {
        $is_failed=FALSE;
        try
        {
            $bEvalResult = $root_node->getValue();
            if($bEvalResult === NULL)
            {
                $sEvalResultText = 'NULL';
            } else {
                $sEvalResultText = ($bEvalResult ? 'TRUE' : 'FALSE');
            }
            if($is_expected_result === NULL)
            {
                $sExpectedResultTxt = 'NULL';
            } else {
                $sExpectedResultTxt = ($is_expected_result ? 'TRUE' : 'FALSE');
            }
            if($is_expected_result !== $bEvalResult && !($is_expected_result === NULL && $bEvalResult === NULL))
            {
                $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
                $is_failed=TRUE;
            } else {
                $test_detail_array[] = ('Okay Test' . $test_num . ':' . $root_node . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
            }
            if($bEvalResult != NULL)
            {
                $nodes_array[$bEvalResult][] = $root_node;
            }
        } catch (\Exception $ex) {
            $test_detail_array[] = ('Failed Test' . $test_num . ':' . $root_node . '=' . 'Caught error ' . $ex->getMessage() );
            $is_failed=TRUE;
        }
        if($is_failed)
        {
            $failed_count++;
        }
        return $is_failed;
    }

    public static function createNode_OCC($leftConstant, $operator_tx, $rightConstant)
    {
        $left_obj = new \simplerulesengine\TNConstant($leftConstant);
        $right_obj = new \simplerulesengine\TNConstant($rightConstant);
        if(strpos('*/+-', $operator_tx) === FALSE)
        {
            $root_node = new \simplerulesengine\TNOBoolean($left_obj,$operator_tx,$right_obj);
        } else {
            $root_node = new \simplerulesengine\TNONumeric($left_obj,$operator_tx,$right_obj);
        }
        return $root_node;
    }
    
    public static function createNode_OTT($leftText, $operator_tx, $rightText)
    {
        $left_obj = new \simplerulesengine\TNText($leftText);
        $right_obj = new \simplerulesengine\TNText($rightText);
        $root_node = new \simplerulesengine\TNOBoolean($left_obj,$operator_tx,$right_obj);
        return $root_node;
    }

    public static function createNode_OVV($leftVarname, $operator_tx, $rightVarname, $var_map)
    {
        $left_obj = new \simplerulesengine\TNVariable($leftVarname,$var_map);
        $right_obj = new \simplerulesengine\TNVariable($rightVarname,$var_map);
        return TestUtility::createNode_ONN($left_obj,$operator_tx,$right_obj);
    }

    public static function createNode_OVT($leftVarname, $operator_tx, $rightText, $var_map)
    {
        $left_obj = new \simplerulesengine\TNVariable($leftVarname,$var_map);
        $right_obj = new \simplerulesengine\TNText($rightText);
        return TestUtility::createNode_ONN($left_obj,$operator_tx,$right_obj);
    }

    public static function createNode_OTV($leftText, $operator_tx, $rightVarname, $var_map)
    {
        $left_obj = new \simplerulesengine\TNText($leftText);
        $right_obj = new \simplerulesengine\TNVariable($rightVarname,$var_map);
        return TestUtility::createNode_ONN($left_obj,$operator_tx,$right_obj);
    }

    public static function createNode_ONN($leftNode, $operator_tx, $rightNode)
    {
        $root_node = new \simplerulesengine\TNOBoolean($leftNode,$operator_tx,$rightNode);
        return $root_node;
    }
}
