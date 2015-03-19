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
 * Test the tokenizer
 *
 * @author Frank Font
 */
class TestTokenizer
{
    public static function runAllTests()
    {
        $failedCount = 0;
        $failedCount += TestTokenizer::runTokenizer1Tests();
        $failedCount += TestTokenizer::runTokenizerTextTests();
        $failedCount += TestTokenizer::runTokenizerArrayTests();
        $failedCount += TestTokenizer::runTokenizerParameterTests();
        return $failedCount;
    }    

    public static function getResultAnalysis($test_num,$expected_token_count,$tokens_array,&$failed_count)
    {
        $nActualCount = count($tokens_array);
        if($expected_token_count != $nActualCount)
        {
            $failed_count++;
            $sTestDetail = ('Failed Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . $nActualCount . ' contents=' . print_r($tokens_array,TRUE) );
        } else {
            $sTestDetail = ('Okay Test' . $test_num . ': expected ' . $expected_token_count . ' got ' . $nActualCount . ' contents=' . print_r($tokens_array,TRUE) );
        }
        return $sTestDetail;
    }
    
    public static function runTokenizerTextTests()
    {
        $sTestTitle = 'Tokenizer Text';
        $test_num = 0;
        $failed_count = 0;
        
        $test_num++;
        $expected_token_count = 9;
        $expression = '("hello" = "hello")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 9;
        $expression = '("hello" == "hello")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 9;
        $expression = '("hello" <> "hello")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 7;
        $expression = '(VAR1 = "hello")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 7;
        $expression = '("hello" = VAR1)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTokenizer1Tests()
    {
        $sTestTitle = 'Tokenizer1';
        $test_num = 0;
        $failed_count = 0;

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 > 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(10 > 20)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 = 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 <> 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 <= 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(1 >= 2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 5;
        $expression = '(VAR1 and VAR2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 9;
        $expression = '((VAR1 or VAR2) and VAR3)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 5;
        $expression = '(VAR1 or VAR2)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }
    
    public static function runTokenizerArrayTests()
    {
        $sTestTitle = 'Tokenizer Array';
        $test_num = 0;
        $failed_count = 0;
        
        $test_num++;
        $expected_token_count = 18;
        $expression = 'items("a","b","c","e")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 18;
        $expression = 'items("abc","bad","cats","eals")';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
    public static function runTokenizerParameterTests()
    {
        $sTestTitle = 'Tokenizer Array';
        $test_num = 0;
        $failed_count = 0;
        
        $test_num++;
        $expected_token_count = 4;
        $expression = 'MyTestFunctionA(MyVar1)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 6;
        $expression = 'MyTestFunctionB(MyVar,MyVar21)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 6;
        $expression = 'MyTestFunctionC(MyVar , MyVar21)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        $test_num++;
        $expected_token_count = 8;
        $expression = 'MyTestFunctionD(MyVar1,MyVar2,MyVar3)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);
        
        $test_num++;
        $expected_token_count = 8;
        $expression = 'MyTestFunctionE(MyVar1 , MyVar2 , MyVar3)';
        $tokens_array = MeasureExpressionParser::getTokens($expression);
        $test_detail_array[] = TestTokenizer::getResultAnalysis($test_num,$expected_token_count, $tokens_array, $failed_count);

        if($failed_count > 0)
        {
            drupal_set_message(t('Total '.$sTestTitle.' failed tests = ' . $failed_count . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'),'error');
        } else {
            drupal_set_message(t('All ' . $test_num . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $test_detail_array) . '</ul>'));
        }
        return $failed_count;
    }    
    
}
