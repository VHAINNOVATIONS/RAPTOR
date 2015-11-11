<?php
/**
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

require_once dirname(__FILE__) . '/../RuleExpressionParser.inc';

/**
 * Test the rule expression processing
 *
 * @author Frank Font
 */
class TestRuleExpression
{
    private $m_expression = NULL;
    private $m_root_node = NULL;

    public static function runAllTests()
    {
        $failedCount = 0;
        $failedCount += TestRuleExpression::runBadSyntaxCompilerTests();
        $failedCount += TestRuleExpression::runGoodSyntaxCompilerTests();
        if($failedCount == 0)
        {
            drupal_set_message(t('Found ZERO errors in Rule tests!'));
        }
        return $failedCount;
    }
    
    public static function runBadSyntaxCompilerTests()
    {
        $sTestTitle = 'Bad Syntax Expression Compiler';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array('MYVAR1','MYVAR2');
        $expression = '(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = 'this is not a real formula';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = '(1 + 2';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $expression = '1 + 2)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array('MYVAR1');
        $expression = 'MYVAR1';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array('MYVAR1');
        $expression = 'AllFlagsFakeFunc(MYVAR1)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
        
    }    
    
    public static function runGoodSyntaxCompilerTests()
    {
        $sTestTitle = 'Good Syntax Expression Compiler';
        
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        TestRuleExpression::runStandardCompilerTestsForOneFunction($test_num,'AnyFlagTrue', $test_detail_array, $failed_count, $nodes_array);
        TestRuleExpression::runStandardCompilerTestsForOneFunction($test_num,'AllFlagsTrue', $test_detail_array, $failed_count, $nodes_array);
        TestRuleExpression::runStandardCompilerTestsForOneFunction($test_num,'AllFlagsFalse', $test_detail_array, $failed_count, $nodes_array);
        TestRuleExpression::runStandardCompilerTestsForOneFunction($test_num,'AllFlagsNull', $test_detail_array, $failed_count, $nodes_array);
        
        $aFunctionNames = array('AllFlagsNull');
        TestRuleExpression::runStandardCompilerTestsForManyFunctions($test_num,$aFunctionNames, $test_detail_array, $failed_count, $nodes_array);

        $aFunctionNames = array('AnyFlagTrue','AllFlagsNull');
        TestRuleExpression::runStandardCompilerTestsForManyFunctions($test_num,$aFunctionNames, $test_detail_array, $failed_count, $nodes_array);

        $aFunctionNames = array('AnyFlagTrue','AllFlagsTrue','AllFlagsFalse');
        TestRuleExpression::runStandardCompilerTestsForManyFunctions($test_num,$aFunctionNames, $test_detail_array, $failed_count, $nodes_array);

        $aFunctionNames = array('AnyFlagTrue','AllFlagsTrue','AllFlagsFalse','AllFlagsNull');
        TestRuleExpression::runStandardCompilerTestsForManyFunctions($test_num,$aFunctionNames, $test_detail_array, $failed_count, $nodes_array);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
        
    }    

    private static function runStandardCompilerTestsForManyFunctions(&$test_num, $aFunctionNames, &$test_detail_array, &$failed_count, &$nodes_array)
    {
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR3'] = 333;
        $var_map['MYVAR99'] = NULL;

        $expression = '';
        foreach($aFunctionNames as $sFunctionName)
        {
            if($expression > '') 
            {
                $expression .= ' and '; 
            }
            $expression .= $sFunctionName . '(MYVAR1,MYVAR2,MYVAR3)';
        }
        
        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1','MYVAR2','MYVAR3');
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
    }
    
    private static function runStandardCompilerTestsForOneFunction(&$test_num, $sFunctionName, &$test_detail_array, &$failed_count, &$nodes_array)
    {
        $var_map = array();
        $var_map['MYVAR1'] = 55;
        $var_map['MYVAR2'] = 111;
        $var_map['MYVAR3'] = 333;
        $var_map['MYVAR99'] = NULL;
        
        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1');
        $expression = $sFunctionName.'(MYVAR1)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1','MYVAR2');
        $expression = $sFunctionName.'(MYVAR1 , MYVAR2)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);

        $test_num++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1','MYVAR2','MYVAR3');
        $expression = $sFunctionName.'(MYVAR1 , MYVAR2 , MYVAR3)';
        TestUtility::runOneRuleCompilerTest($test_num, $bExpectSytaxError, $aExpectedVars, $var_map, $expression, $test_detail_array, $failed_count, $nodes_array);
    }
    
}
