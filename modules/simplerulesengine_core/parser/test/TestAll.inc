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

require_once 'TestUtility.inc';
require_once 'TestTokenizer.inc';
require_once 'TestMeasureExpression.inc';
require_once 'TestRuleExpression.inc';
require_once dirname(__FILE__) . '/../MeasureExpressionParser.inc';

/**
 * Tests for the simplerulesengine parser
 *
 * @author Frank Font
 */
class TestAll
{
    public static function runAllTests()
    {
        $nFailed = 0;
        $nFailed += TestTokenizer::runAllTests();
        $nFailed += TestMeasureExpression::runAllTests();
        $nFailed += TestRuleExpression::runAllTests();
        return $nFailed;
    }    
    
    public static function runTokenizerTests()
    {
        $test_num = 0;
        $failed_count = 0;

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 > 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(10 > 20)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array) || $tokens_array[1] != 10 || $tokens_array[2] != '>' || $tokens_array[3] != 20)
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 = 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 <> 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 <= 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 >= 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }

        $test_num++;
        $expected_token_count = 5;
        $expression = '(VAR1 and VAR2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }
        
        $test_num++;
        $expected_token_count = 9;
        $expression = '((VAR1 or VAR2) and VAR3)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }
        
        $test_num++;
        $expected_token_count = 5;
        $expression = '(VAR1 or VAR2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        if($expected_token_count != count($tokens_array))
        {
            $failed_count++;
            $test_detail_array[] = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $test_detail_array[] = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . count($tokens_array) . ' contents=' . print_r($tokens_array,TRUE) );
        }
        
        if($failed_count > 0)
        {
            drupal_set_message(t('Total token failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' token tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }

    public static function runTreeTests()
    {
        TestAll::runTreeEvalTests();
        TestAll::runTreeNumericEvalTests();
        TestAll::runTreeCompleteTests();
    }

    public static function runTreeNumericEvalTests()
    {
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
        $root_node = TestAll::createNode_OCC(100, '+', 100);
        $is_failed = TestAll::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $nExpectedResult = 45;
        $root_node = TestAll::createNode_OCC(200, '-', 155);
        $is_failed = TestAll::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $nExpectedResult = 20;
        $root_node = TestAll::createNode_OCC(4, '*', 5);
        $is_failed = TestAll::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $nExpectedResult = 40;
        $root_node = TestAll::createNode_OCC(200, '/', 5);
        $is_failed = TestAll::runOneTreeNumericEvalTest($test_num, $nExpectedResult, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed tree numeric eval tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' tree numeric eval tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTreeEvalTests()
    {
        $test_detail_array = array();
        $test_num = 0;
        $failed_count = 0;
        $nodes_array = array();  //Collect nodes as we build them for aggregate tests.
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestAll::createNode_OCC(100, '>', 100);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestAll::createNode_OCC(1, '>', 2);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestAll::createNode_OCC(2, '>', 1);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = FALSE;
        $root_node = TestAll::createNode_OCC(2, '<', 1);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestAll::createNode_OCC(1, '<', 2);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestAll::createNode_OCC(1, '=', 1);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestAll::createNode_OCC(1, '>=', 1);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $root_node = TestAll::createNode_OCC(115, '<=', 115);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(111, '<', 211);
        $right_node_obj = TestAll::createNode_OCC(10, '<', 20);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestAll::createNode_OCC(111, '>', 222);
        $right_node_obj = TestAll::createNode_OCC(1111, '>', 22222);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestAll::createNode_OCC(1, '<', 2);
        $right_node_obj = TestAll::createNode_OCC(1, '>', 2);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(0, '<', 2);
        $right_node_obj = TestAll::createNode_OCC(2, '>', 0);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'and', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(0, '<', 2);
        $right_node_obj = TestAll::createNode_OCC(2, '>', 0);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(2, '<', 1);
        $right_node_obj = TestAll::createNode_OCC(2, '>', 0);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(2,'>',1);
        $right_node_obj = TestAll::createNode_OCC(2,'<',1);
        $root_node = TestAll::createNode_ONN($left_node_obj, 'or', $right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = FALSE;
        $left_node_obj = TestAll::createNode_OCC(2,'<',1);
        $right_node_obj = TestAll::createNode_OCC(2,'<',1);
        $root_node = TestAll::createNode_ONN($left_node_obj,'or',$right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = TRUE;
        $left_node_obj = TestAll::createNode_OCC(200,'<',100);
        $right_node_obj = TestAll::createNode_OCC(200,'>',100);
        $root_node = TestAll::createNode_ONN($left_node_obj,'or',$right_node_obj);
        $is_failed = TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        if($failed_count > 0)
        {
            drupal_set_message(t('Total failed tree eval tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' tree eval tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }
    
    public static function runTreeCompleteTests()
    {
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
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }
        
        $test_num++;
        $is_expected_result = FALSE;
        $expression = '((158 > 269) and (21 > 31))';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '((358 < 469) and (821 < 831))';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(((458) < (569)) and ((921) < (931)))';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(((458 < 569) and (921 < 931)) or (1 > 0))';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '((458 < 569) and (1 > 931)) or (1 > 0)';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = TRUE;
        $expression = '(MYVAR1 < 100) and (MYVAR2 > 100)';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = FALSE;
        $expression = '(MYVAR1 > 100) and (MYVAR2 > 100)';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        $test_num++;
        $is_expected_result = NULL;
        $expression = '(MYVAR99 > 100) and (MYVAR1 > 100)';
        $is_failed = TestAll::runOneTreeCompleteTest($test_num, $is_expected_result, $var_map, $expression, $test_detail_array, $nodes_array);
        if($is_failed)
        {
            $failed_count++;
        }

        if($failed_count > 0)
        {
            drupal_set_message(t('Total tree complete failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' tree complete tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }

    private static function runOneTreeCompleteTest($test_num,$is_expected_result, $var_map, $expression,&$test_detail_array,&$nodes_array)
    {
        $parserengine = new \simplerulesengine\MeasureExpressionParser($var_map);
        $root_node = $parserengine->parse($expression);
        return TestAll::runOneTreeEvalTest($test_num, $is_expected_result, $root_node, $test_detail_array, $nodes_array);
    }
    
    private static function runOneTreeEvalTest($test_num,$is_expected_result,$root_node,&$test_detail_array,&$nodes_array)
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
            drupal_set_message(t('Caught error on Test' . $test_num . ' is ' . $ex->getMessage()));
            $is_failed=TRUE;
        }
        return $is_failed;
    }

    private static function runOneTreeNumericEvalTest($test_num,$nExpectedResult,$root_node,&$test_detail_array,&$nodes_array)
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
            drupal_set_message(t('Caught error on Test' . $test_num . ' is ' . $ex->getMessage()));
            $is_failed=TRUE;
        }
        return $is_failed;
    }
    
    private static function createNode_OCC($leftConstant, $operator_tx, $rightConstant)
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
    
    private static function createNode_ONN($leftNode, $operator_tx, $rightNode)
    {
        $root_node = new \simplerulesengine\TNOBoolean($leftNode,$operator_tx,$rightNode);
        return $root_node;
    }
}
