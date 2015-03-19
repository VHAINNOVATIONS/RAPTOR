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

/**
 * Test the meassure expression processing
 *
 * @author Frank Font
 */
class TestMeasureExpression
{
    private $m_expression = NULL;
    private $m_root_node = NULL;

    public static function runAllTests()
    {
        $failedCount = 0;
        $failedCount += TestMeasureExpression::runTreeNumericBooleanEvalTests();
        $failedCount += TestMeasureExpression::runTreeMathEvalTests();
        $failedCount += TestMeasureExpression::runTreeTextLiteralsEvalTests();
        $failedCount += TestMeasureExpression::runTreeTextVariablesEvalTests();
        $failedCount += TestMeasureExpression::runTreeTextVarAndLiteralsEvalTests();
        $failedCount += TestMeasureExpression::runParseAndEvalTextBoolTests();
        $failedCount += TestMeasureExpression::runParseAndEvalBoolTests();
        $failedCount += TestMeasureExpression::runParseAndEvalArrayBoolTests();
        $failedCount += TestMeasureExpression::runCompilerTests();
        if($failedCount == 0)
        {
            drupal_set_message(t('Found ZERO errors in Measure tests!'));
        }
        return $failedCount;
    }
    
    public static function runTreeTextLiteralsEvalTests()
    {
        $sTestTitle = 'Tree Text Literals Eval';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYTEXTVAR1'] = 'Hello1';
        $var_map['MYTEXTVAR2'] = 'Hello2';
        $var_map['MYTEXTVAR2B'] = 'hello2';
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('Hello', '=', 'Hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('Hello', '==', 'Hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('HELLO', '=', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('hello', '=', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('hello', '!==', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('HELLO', '!==', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTT('hello', '<>', 'goodbye');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('Hello', '=', 'Goodbye');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('HELLO', '==', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('hello', '==', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('hello', '!==', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('hello', '<>', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('HELLO', '<>', 'hello');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OTT('hello', '<>', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed '.$sTestTitle.' tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTreeTextVariablesEvalTests()
    {
        $sTestTitle = 'Tree Text Variables Eval';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYTEXTVAR1'] = 'HELLO';
        $var_map['MYTEXTVAR1B'] = 'hello';
        $var_map['MYTEXTVAR2'] = 'GOODBYE';
        $var_map['MYTEXTVAR2B'] = 'goodbye';
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR1', '=', 'MYTEXTVAR1',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR2', '==', 'MYTEXTVAR2',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR1', '=', 'MYTEXTVAR2',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR2', '=', 'MYTEXTVAR2B',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR2', '!==', 'MYTEXTVAR2B',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR2', '<>', 'MYTEXTVAR2B',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OVV('MYTEXTVAR2', '==', 'MYTEXTVAR2B',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed '.$sTestTitle.' tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTreeTextVarAndLiteralsEvalTests()
    {
        $sTestTitle = 'Tree Text Variables and Literals Eval';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYTEXTVAR1'] = 'HELLO';
        $var_map['MYTEXTVAR1B'] = 'hello';
        $var_map['MYTEXTVAR2'] = 'GOODBYE';
        $var_map['MYTEXTVAR2B'] = 'goodbye';
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR1', '=', 'HELLO',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OTV('HELLO', '=', 'MYTEXTVAR1',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR2', '==', 'GOODBYE',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR1', '=', 'elephant',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR2', '=', 'goodBye',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR2', '!==', 'airplane',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OVT('MYTEXTVAR2', '<>', 'GOODBYE',$var_map);
        TestUtility::runOneTreeTextEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed '.$sTestTitle.' tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    

    public static function runTreeMathEvalTests()
    {
        $sTestTitle = 'Tree Math Eval';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $nExpectedResult = 200;
        $root_node = TestUtility::createNode_OCC(100, '+', 100);
        TestUtility::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $nExpectedResult = 45;
        $root_node = TestUtility::createNode_OCC(200, '-', 155);
        TestUtility::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $nExpectedResult = 20;
        $root_node = TestUtility::createNode_OCC(4, '*', 5);
        TestUtility::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $nExpectedResult = 40;
        $root_node = TestUtility::createNode_OCC(200, '/', 5);
        TestUtility::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed '.$sTestTitle.' tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTreeNumericBooleanEvalTests()
    {
        $sTestTitle = 'Tree Numeric Boolean Eval';

        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OCC(100, '>', 100);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OCC(1, '>', 2);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OCC(2, '>', 1);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestUtility::createNode_OCC(2, '<', 1);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OCC(1, '<', 2);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OCC(1, '=', 1);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OCC(1, '>=', 1);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestUtility::createNode_OCC(115, '<=', 115);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(111, '<', 211);
        $right_node_obj = TestUtility::createNode_OCC(10, '<', 20);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestUtility::createNode_OCC(111, '>', 222);
        $right_node_obj = TestUtility::createNode_OCC(1111, '>', 22222);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestUtility::createNode_OCC(1, '<', 2);
        $right_node_obj = TestUtility::createNode_OCC(1, '>', 2);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(0, '<', 2);
        $right_node_obj = TestUtility::createNode_OCC(2, '>', 0);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(0, '<', 2);
        $right_node_obj = TestUtility::createNode_OCC(2, '>', 0);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(2, '<', 1);
        $right_node_obj = TestUtility::createNode_OCC(2, '>', 0);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(2,'>',1);
        $right_node_obj = TestUtility::createNode_OCC(2,'<',1);
        $root_node = TestUtility::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestUtility::createNode_OCC(2,'<',1);
        $right_node_obj = TestUtility::createNode_OCC(2,'<',1);
        $root_node = TestUtility::createNode_ONN($left_node_obj,'or',$right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestUtility::createNode_OCC(200,'<',100);
        $right_node_obj = TestUtility::createNode_OCC(200,'>',100);
        $root_node = TestUtility::createNode_ONN($left_node_obj,'or',$right_node_obj);
        TestUtility::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $failed_count, $nodes_array);
        
        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed '.$sTestTitle.' tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }
    
    public static function runParseAndEvalBoolTests()
    {
        $sTestTitle = 'Expression Parse and Eval Bool';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(78 > 56)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $expression = '((158 > 269) and (21 > 31))';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '((358 < 469) and (821 < 831))';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(((458) < (569)) and ((921) < (931)))';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(((4458 < 4569) and (4921 < 4931)) or (1 > 0))';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '((5458 < 5569) and (1 > 5931)) or (1 > 0)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(MYVAR1 < 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = '(MYVAR1 > 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR1 > 100) and (MYVAR99 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR99 > 100) and (MYVAR1 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = 'not(MYVAR99 > 100) and (MYVAR1 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = 'MYVAR99 > 100';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'not(MYVAR1 > 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'not(MYVAR1 > 100) and not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = '(MYVAR1 > 100) and not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(MYVAR1 > 100) or (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'not(MYVAR1 > 100) or (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'not(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = '(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR1 > 1) and (MYVAR99 > 1)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(MYVAR1 > 1) or (MYVAR99 > 1)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR1 > 9999) or (MYVAR99 > 1)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR99 > 1) or (MYVAR1 > 9999)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }
    
    public static function runParseAndEvalTextBoolTests()
    {
        $sTestTitle = 'Expression Parse and Eval Text Bool';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYTXT1'] = 'A';
        $var_map['MYTXT2'] = 'XYZ';
        $var_map['MYTXT3'] = 'Hello';
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'MYTXT1 = MYTXT1';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'MYTXT1 = "A"';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'not(MYTXT1 = "A")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'MYTXT1 = "B"';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'not(MYTXT1 = "B")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '"A" = MYTXT1';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'not("A" = MYTXT1)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = '"B" = MYTXT1';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'not("B" = MYTXT1)';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }
    
    public static function runParseAndEvalArrayBoolTests()
    {
        $sTestTitle = 'Expression Parse and Eval Array Bool';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYTXT1'] = 'A';
        $var_map['MYTXT2'] = 'XYZ';
        $var_map['MYTXT3'] = 'Hello';
        $var_map['MYVAR99'] = NULL;
        $var_map['MYAR1'] = array('ant','bat','cat');
        $var_map['MYMEDS1'] = array('ASPIRIN TAB,EC');
        $var_map['MYMEDS2'] = array('TYLENOL','acetaminophen');
        
        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'items("a","b") somematch items("x","y","z")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("a","z","b") somematch items("x","y","z")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'items("aa","bb") somematch items("xa","ya","za")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'items("aa","aza","ba") somematch items("xaa","yaa","zaaa")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("aa","za","ba") somematch items("xaa","yaa","zaaa")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("aa","za","ba") somematch items("xa","aaya","za")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'items("a","z","b") someexactmatch items("xa","aaya","za")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("aa","za","ba") someexactmatch items("xa","aaya","za")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("aa","aaya","ba") someexactmatch items("xa","aaya","za")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("a","Z","b") somematch items("x","y","z")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'items("a","Z","b") somefullmatch items("x","y","z")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'items("a","Z","b") someexactmatch items("x","y","z")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'MYAR1 somematch items("dog","cat","horse")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'MYAR1 somematch items("dog","snake","horse")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'MYAR1 somematch MYAR1';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $is_expected_result = TRUE;
        $expression = 'MYMEDS1 somematch items("Aldesleukin" , "Aspirin" , "Avandamet " , "Clopidogrel" , "Coumadin" , "Dalteparin" , "Enoxaparin" , "Fragmin" , "Glucophage" , "Glucovance" , "Heparin" , "Lovenox" , "Metaglys" , "Metformin" , "Plavix" , "Proleukin" , "Warfarin")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
        
        $test_num++;
        $is_expected_result = FALSE;
        $expression = 'MYMEDS2 somematch items("Aldesleukin" , "Aspirin" , "Avandamet " , "Clopidogrel" , "Coumadin" , "Dalteparin" , "Enoxaparin" , "Fragmin" , "Glucophage" , "Glucovance" , "Heparin" , "Lovenox" , "Metaglys" , "Metformin" , "Plavix" , "Proleukin" , "Warfarin")';
        TestUtility::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    

    public static function runCompilerTests()
    {
        $sTestTitle = 'Expression Compiler';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1','MYVAR2');
        $expression = '(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = "this is not a real formula";
        TestUtility::runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = '(1 + 2';
        TestUtility::runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = '1 + 2)';
        TestUtility::runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1');
        $expression = 'MYVAR1';
        TestUtility::runOneMeasureCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
        
    }    
}
