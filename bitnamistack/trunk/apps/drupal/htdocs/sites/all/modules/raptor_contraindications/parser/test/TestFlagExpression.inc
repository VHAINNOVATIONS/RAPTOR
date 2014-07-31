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
 * Test the flag expression processing
 *
 * @author FrankWin7VM
 */
class TestFlagExpression
{
    private $m_sExpression = NULL;
    private $m_oTree = NULL;

    public static function runAllTests()
    {
        $failedCount = 0;
        $failedCount += TestFlagExpression::runTreeNumericBooleanEvalTests();
        $failedCount += TestFlagExpression::runTreeMathEvalTests();
        $failedCount += TestFlagExpression::runTreeTextLiteralsEvalTests();
        $failedCount += TestFlagExpression::runTreeTextVariablesEvalTests();
        $failedCount += TestFlagExpression::runTreeTextVarAndLiteralsEvalTests();
        $failedCount += TestFlagExpression::runParseAndEvalBoolTests();
        $failedCount += TestFlagExpression::runCompilerTests();
        return $failedCount;
    }
    
    public static function runTreeTextLiteralsEvalTests()
    {
        $sTestTitle = 'Tree Text Literals Eval';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYTEXTVAR1'] = 'Hello1';
        $aVarMap['MYTEXTVAR2'] = 'Hello2';
        $aVarMap['MYTEXTVAR2B'] = 'hello2';
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('Hello', '=', 'Hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('Hello', '==', 'Hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('HELLO', '=', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('hello', '=', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('hello', '!==', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('HELLO', '!==', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTT('hello', '<>', 'goodbye');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('Hello', '=', 'Goodbye');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('HELLO', '==', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('hello', '==', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('hello', '!==', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('hello', '<>', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('HELLO', '<>', 'hello');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OTT('hello', '<>', 'HELLO');
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed '.$sTestTitle.' tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    
    
    public static function runTreeTextVariablesEvalTests()
    {
        $sTestTitle = 'Tree Text Variables Eval';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYTEXTVAR1'] = 'HELLO';
        $aVarMap['MYTEXTVAR1B'] = 'hello';
        $aVarMap['MYTEXTVAR2'] = 'GOODBYE';
        $aVarMap['MYTEXTVAR2B'] = 'goodbye';
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR1', '=', 'MYTEXTVAR1',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR2', '==', 'MYTEXTVAR2',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR1', '=', 'MYTEXTVAR2',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR2', '=', 'MYTEXTVAR2B',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR2', '!==', 'MYTEXTVAR2B',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR2', '<>', 'MYTEXTVAR2B',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OVV('MYTEXTVAR2', '==', 'MYTEXTVAR2B',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed '.$sTestTitle.' tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    
    
    public static function runTreeTextVarAndLiteralsEvalTests()
    {
        $sTestTitle = 'Tree Text Variables and Literals Eval';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYTEXTVAR1'] = 'HELLO';
        $aVarMap['MYTEXTVAR1B'] = 'hello';
        $aVarMap['MYTEXTVAR2'] = 'GOODBYE';
        $aVarMap['MYTEXTVAR2B'] = 'goodbye';
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR1', '=', 'HELLO',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OTV('HELLO', '=', 'MYTEXTVAR1',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR2', '==', 'GOODBYE',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR1', '=', 'elephant',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR2', '=', 'goodBye',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR2', '!==', 'airplane',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OVT('MYTEXTVAR2', '<>', 'GOODBYE',$aVarMap);
        TestUtility::runOneTreeTextEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed '.$sTestTitle.' tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    

    public static function runTreeMathEvalTests()
    {
        $sTestTitle = 'Tree Math Eval';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $nExpectedResult = 200;
        $oRoot = TestUtility::createNode_OCC(100, '+', 100);
        TestUtility::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $nExpectedResult = 45;
        $oRoot = TestUtility::createNode_OCC(200, '-', 155);
        TestUtility::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $nExpectedResult = 20;
        $oRoot = TestUtility::createNode_OCC(4, '*', 5);
        TestUtility::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $nExpectedResult = 40;
        $oRoot = TestUtility::createNode_OCC(200, '/', 5);
        TestUtility::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed '.$sTestTitle.' tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    
    
    public static function runTreeNumericBooleanEvalTests()
    {
        $sTestTitle = 'Tree Numeric Boolean Eval';

        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OCC(100, '>', 100);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OCC(1, '>', 2);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OCC(2, '>', 1);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestUtility::createNode_OCC(2, '<', 1);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OCC(1, '<', 2);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OCC(1, '=', 1);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OCC(1, '>=', 1);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestUtility::createNode_OCC(115, '<=', 115);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(111, '<', 211);
        $oRightNode = TestUtility::createNode_OCC(10, '<', 20);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'and', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestUtility::createNode_OCC(111, '>', 222);
        $oRightNode = TestUtility::createNode_OCC(1111, '>', 22222);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'and', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestUtility::createNode_OCC(1, '<', 2);
        $oRightNode = TestUtility::createNode_OCC(1, '>', 2);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'and', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(0, '<', 2);
        $oRightNode = TestUtility::createNode_OCC(2, '>', 0);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'and', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(0, '<', 2);
        $oRightNode = TestUtility::createNode_OCC(2, '>', 0);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'or', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(2, '<', 1);
        $oRightNode = TestUtility::createNode_OCC(2, '>', 0);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'or', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(2,'>',1);
        $oRightNode = TestUtility::createNode_OCC(2,'<',1);
        $oRoot = TestUtility::createNode_ONN($oLeftNode, 'or', $oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestUtility::createNode_OCC(2,'<',1);
        $oRightNode = TestUtility::createNode_OCC(2,'<',1);
        $oRoot = TestUtility::createNode_ONN($oLeftNode,'or',$oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestUtility::createNode_OCC(200,'<',100);
        $oRightNode = TestUtility::createNode_OCC(200,'>',100);
        $oRoot = TestUtility::createNode_ONN($oLeftNode,'or',$oRightNode);
        TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
        
        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed '.$sTestTitle.' tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }
    
    public static function runParseAndEvalBoolTests()
    {
        $sTestTitle = 'Expression Parse and Eval Bool';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(78 > 56)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);
        
        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '((158 > 269) and (21 > 31))';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '((358 < 469) and (821 < 831))';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(((458) < (569)) and ((921) < (931)))';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(((4458 < 4569) and (4921 < 4931)) or (1 > 0))';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '((5458 < 5569) and (1 > 5931)) or (1 > 0)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(MYVAR1 < 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '(MYVAR1 > 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = NULL;
        $sExpression = '(MYVAR1 > 100) and (MYVAR99 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = NULL;
        $sExpression = 'not(MYVAR1 > 100) and (MYVAR99 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = 'not(MYVAR1 > 100) and (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = 'not(MYVAR1 > 100) and not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '(MYVAR1 > 100) and not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(MYVAR1 > 100) or (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = 'not(MYVAR1 > 100) or (MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = 'not(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total '.$sTestTitle.' failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }
    
    public static function runCompilerTests()
    {
        $sTestTitle = 'Expression Compiler';
        
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $aVarMap = array();
        $aVarMap['MYVAR1'] = 55;
        $aVarMap['MYVAR2'] = 111;
        $aVarMap['MYVAR99'] = NULL;
        
        $nTest++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1','MYVAR2');
        $sExpression = '(MYVAR1 > 100) or not(MYVAR2 > 100)';
        TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $sExpression = 'this isnt a real formula';
        TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $sExpression = '(1 + 2';
        TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectSytaxError=TRUE;
        $aExpectedVars = array();
        $sExpression = '1 + 2)';
        TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        $nTest++;
        $bExpectSytaxError=FALSE;
        $aExpectedVars = array('MYVAR1');
        $sExpression = 'MYVAR1';
        TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);

        if($nFailedCount > 0)
        {
            drupal_set_message('Total '.$sTestTitle.' failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' '.$sTestTitle.' tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
        
    }    
}
