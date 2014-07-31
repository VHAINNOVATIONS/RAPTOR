<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

require_once(dirname(__FILE__) . '/../FlagExpressionParser.inc');
require_once(dirname(__FILE__) . '/../ITreeNode.inc');
require_once(dirname(__FILE__) . '/../ATreeNode.inc');
require_once(dirname(__FILE__) . '/../TNConstant.inc');
require_once(dirname(__FILE__) . '/../TNOperator.inc');
require_once(dirname(__FILE__) . '/../TNOBoolean.inc');
require_once(dirname(__FILE__) . '/../TNONumeric.inc');
require_once(dirname(__FILE__) . '/../TNVariable.inc');

/**
 * Test the tokenizer
 *
 * @author FrankWin7VM
 */
class TestTokenizer
{
    public static function runAllTests()
    {
        $failedCount = 0;
        $failedCount += TestTokenizer::runTokenizer1Tests();
        $failedCount += TestTokenizer::runTokenizerTextTests();
        return $failedCount;
    }    

    public static function getResultAnalysis($nTest,$nExpectedTokenCount,$aTokens,&$nFailedCount)
    {
        $nActualCount = count($aTokens);
        if($nExpectedTokenCount != $nActualCount)
        {
            $nFailedCount++;
            $sTestDetail = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . $nActualCount . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $sTestDetail = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . $nActualCount . ' contents=' . print_r($aTokens,TRUE) );
        }
        return $sTestDetail;
    }
    
    public static function runTokenizerTextTests()
    {
        $sTestTitle = 'Tokenizer Text';
        $nTest = 0;
        $nFailedCount = 0;
        
        $nTest++;
        $nExpectedTokenCount = 9;
        $sExpression = '("hello" = "hello")';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        $nTest++;
        $nExpectedTokenCount = 9;
        $sExpression = '("hello" == "hello")';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        $nTest++;
        $nExpectedTokenCount = 9;
        $sExpression = '("hello" <> "hello")';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        $nTest++;
        $nExpectedTokenCount = 7;
        $sExpression = '(VAR1 = "hello")';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 7;
        $sExpression = '("hello" = VAR1)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total '.$sTestTitle.' failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    
    
    public static function runTokenizer1Tests()
    {
        $sTestTitle = 'Tokenizer1';
        $nTest = 0;
        $nFailedCount = 0;

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 > 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(10 > 20)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 = 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 <> 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 <= 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 >= 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(VAR1 and VAR2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        $nTest++;
        $nExpectedTokenCount = 9;
        $sExpression = '((VAR1 or VAR2) and VAR3)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(VAR1 or VAR2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        $aTestDetail[] = TestTokenizer::getResultAnalysis($nTest,$nExpectedTokenCount, $aTokens, $nFailedCount);
        
        if($nFailedCount > 0)
        {
            drupal_set_message('Total '.$sTestTitle.' failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }
}
