<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

require_once('TestUtility.inc');
require_once('TestTokenizer.inc');
require_once('TestFlagExpression.inc');
require_once(dirname(__FILE__) . '/../FlagExpressionParser.inc');
require_once(dirname(__FILE__) . '/../ITreeNode.inc');
require_once(dirname(__FILE__) . '/../ATreeNode.inc');
require_once(dirname(__FILE__) . '/../TNConstant.inc');
require_once(dirname(__FILE__) . '/../TNOperator.inc');
require_once(dirname(__FILE__) . '/../TNOBoolean.inc');
require_once(dirname(__FILE__) . '/../TNONegate.inc');
require_once(dirname(__FILE__) . '/../TNONumeric.inc');
require_once(dirname(__FILE__) . '/../TNVariable.inc');

/**
 * Tests for the contraindication parser
 *
 * @author FrankWin7VM
 */
class TestAll
{
    private $m_sExpression = NULL;
    private $m_oTree = NULL;

    public static function runAllTests()
    {
        TestTokenizer::runAllTests();
        TestFlagExpression::runAllTests();
    }    
    
    public static function runTokenizerTests()
    {
        $nTest = 0;
        $nFailedCount = 0;

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 > 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(10 > 20)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens) || $aTokens[1] != 10 || $aTokens[2] != '>' || $aTokens[3] != 20)
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 = 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 <> 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 <= 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(1 >= 2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }

        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(VAR1 and VAR2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }
        
        $nTest++;
        $nExpectedTokenCount = 9;
        $sExpression = '((VAR1 or VAR2) and VAR3)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }
        
        $nTest++;
        $nExpectedTokenCount = 5;
        $sExpression = '(VAR1 or VAR2)';
        $aTokens = FlagExpressionParser::getTokens($sExpression);
        if($nExpectedTokenCount != count($aTokens))
        {
            $nFailedCount++;
            $aTestDetail[] = ('Failed Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        } else {
            $aTestDetail[] = ('Okay Test' . $nTest . ': expected ' . $nExpectedTokenCount . ' got ' . count($aTokens) . ' contents=' . print_r($aTokens,TRUE) );
        }
        
        if($nFailedCount > 0)
        {
            drupal_set_message('Total token failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' token tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }

    public static function runTreeTests()
    {
        TestAll::runTreeEvalTests();
        TestAll::runTreeNumericEvalTests();
        TestAll::runTreeCompleteTests();
    }

    public static function runTreeNumericEvalTests()
    {
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
        $oRoot = TestAll::createNode_OCC(100, '+', 100);
        $bFailed = TestAll::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $nExpectedResult = 45;
        $oRoot = TestAll::createNode_OCC(200, '-', 155);
        $bFailed = TestAll::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $nExpectedResult = 20;
        $oRoot = TestAll::createNode_OCC(4, '*', 5);
        $bFailed = TestAll::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $nExpectedResult = 40;
        $oRoot = TestAll::createNode_OCC(200, '/', 5);
        $bFailed = TestAll::runOneTreeNumericEvalTest($nTest, $nExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed tree numeric eval tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' tree numeric eval tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }    
    
    public static function runTreeEvalTests()
    {
        $aTestDetail = array();
        $nTest = 0;
        $nFailedCount = 0;
        $aNodes = array();  //Collect nodes as we build them for aggregate tests.
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestAll::createNode_OCC(100, '>', 100);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestAll::createNode_OCC(1, '>', 2);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestAll::createNode_OCC(2, '>', 1);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oRoot = TestAll::createNode_OCC(2, '<', 1);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestAll::createNode_OCC(1, '<', 2);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestAll::createNode_OCC(1, '=', 1);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestAll::createNode_OCC(1, '>=', 1);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oRoot = TestAll::createNode_OCC(115, '<=', 115);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(111, '<', 211);
        $oRightNode = TestAll::createNode_OCC(10, '<', 20);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'and', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestAll::createNode_OCC(111, '>', 222);
        $oRightNode = TestAll::createNode_OCC(1111, '>', 22222);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'and', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestAll::createNode_OCC(1, '<', 2);
        $oRightNode = TestAll::createNode_OCC(1, '>', 2);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'and', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(0, '<', 2);
        $oRightNode = TestAll::createNode_OCC(2, '>', 0);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'and', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(0, '<', 2);
        $oRightNode = TestAll::createNode_OCC(2, '>', 0);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'or', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(2, '<', 1);
        $oRightNode = TestAll::createNode_OCC(2, '>', 0);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'or', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(2,'>',1);
        $oRightNode = TestAll::createNode_OCC(2,'<',1);
        $oRoot = TestAll::createNode_ONN($oLeftNode, 'or', $oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = FALSE;
        $oLeftNode = TestAll::createNode_OCC(2,'<',1);
        $oRightNode = TestAll::createNode_OCC(2,'<',1);
        $oRoot = TestAll::createNode_ONN($oLeftNode,'or',$oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = TRUE;
        $oLeftNode = TestAll::createNode_OCC(200,'<',100);
        $oRightNode = TestAll::createNode_OCC(200,'>',100);
        $oRoot = TestAll::createNode_ONN($oLeftNode,'or',$oRightNode);
        $bFailed = TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        if($nFailedCount > 0)
        {
            drupal_set_message('Total failed tree eval tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' tree eval tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }
    
    public static function runTreeCompleteTests()
    {
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
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }
        
        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '((158 > 269) and (21 > 31))';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '((358 < 469) and (821 < 831))';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(((458) < (569)) and ((921) < (931)))';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(((458 < 569) and (921 < 931)) or (1 > 0))';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '((458 < 569) and (1 > 931)) or (1 > 0)';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = TRUE;
        $sExpression = '(MYVAR1 < 100) and (MYVAR2 > 100)';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = FALSE;
        $sExpression = '(MYVAR1 > 100) and (MYVAR2 > 100)';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        $nTest++;
        $bExpectedResult = NULL;
        $sExpression = '(MYVAR1 > 100) and (MYVAR99 > 100)';
        $bFailed = TestAll::runOneTreeCompleteTest($nTest, $bExpectedResult, $aVarMap, $sExpression, $aTestDetail, $aNodes);
        if($bFailed)
        {
            $nFailedCount++;
        }

        if($nFailedCount > 0)
        {
            drupal_set_message('Total tree complete failed tests = ' . $nFailedCount . '<ul>' . implode('<li>', $aTestDetail) . '</ul>','error');
        } else {
            drupal_set_message('All ' . $nTest . ' tree complete tests succeeded!' . '<ul>' . implode('<li>', $aTestDetail) . '</ul>');
        }
        return $nFailedCount;
    }

    private static function runOneTreeCompleteTest($nTest,$bExpectedResult, $aVarMap, $sExpression,&$aTestDetail,&$aNodes)
    {
        $oParser = new \raptor_ci\FlagExpressionParser($aVarMap);
        $oRoot = $oParser->parse($sExpression);
        return TestAll::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $aNodes);
    }
    
    private static function runOneTreeEvalTest($nTest,$bExpectedResult,$oRoot,&$aTestDetail,&$aNodes)
    {
        $bFailed=FALSE;
        try
        {
            $bEvalResult = $oRoot->getValue();
            if($bEvalResult === NULL)
            {
                $sEvalResultText = 'NULL';
            } else {
                $sEvalResultText = ($bEvalResult ? 'TRUE' : 'FALSE');
            }
            if($bExpectedResult === NULL)
            {
                $sExpectedResultTxt = 'NULL';
            } else {
                $sExpectedResultTxt = ($bExpectedResult ? 'TRUE' : 'FALSE');
            }
            if($bExpectedResult !== $bEvalResult && !($bExpectedResult === NULL && $bEvalResult === NULL))
            {
                $aTestDetail[] = ('Failed Test' . $nTest . ':' . $oRoot . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
                $bFailed=TRUE;
            } else {
                $aTestDetail[] = ('Okay Test' . $nTest . ':' . $oRoot . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
            }
            if($bEvalResult !== NULL)
            {
                $aNodes[$bEvalResult][] = $oRoot;
            }
        } catch (\Exception $ex) {
            drupal_set_message('Caught error on Test' . $nTest . ' is ' . $ex->getMessage());
            $bFailed=TRUE;
        }
        return $bFailed;
    }

    private static function runOneTreeNumericEvalTest($nTest,$nExpectedResult,$oRoot,&$aTestDetail,&$aNodes)
    {
        $bFailed=FALSE;
        try
        {
            $nEvalResult = $oRoot->getValue();
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
                $aTestDetail[] = ('Failed Test' . $nTest . ':' . $oRoot . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
                $bFailed=TRUE;
            } else {
                $aTestDetail[] = ('Okay Test' . $nTest . ':' . $oRoot . '=' . $sEvalResultText . ' expected ' . $sExpectedResultTxt );
            }
            if($nEvalResult != NULL)
            {
                $aNodes[$nEvalResult][] = $oRoot;
            }
        } catch (\Exception $ex) {
            drupal_set_message('Caught error on Test' . $nTest . ' is ' . $ex->getMessage());
            $bFailed=TRUE;
        }
        return $bFailed;
    }
    
    private static function createNode_OCC($leftConstant, $sOperator, $rightConstant)
    {
        $oLeft = new \raptor_ci\TNConstant($leftConstant);
        $oRight = new \raptor_ci\TNConstant($rightConstant);
        if(strpos('*/+-', $sOperator) === FALSE)
        {
            $oRoot = new \raptor_ci\TNOBoolean($oLeft,$sOperator,$oRight);
        } else {
            $oRoot = new \raptor_ci\TNONumeric($oLeft,$sOperator,$oRight);
        }
        return $oRoot;
    }
    
    private static function createNode_ONN($leftNode, $sOperator, $rightNode)
    {
        $oRoot = new \raptor_ci\TNOBoolean($leftNode,$sOperator,$rightNode);
        return $oRoot;
    }
}
