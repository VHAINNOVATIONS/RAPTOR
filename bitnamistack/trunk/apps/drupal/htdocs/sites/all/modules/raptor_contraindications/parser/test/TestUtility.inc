<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140730
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
 * Utilities for the parser tests
 *
 * @author FrankWin7VM
 */
class TestUtility
{
    public static function runOneTreeCompleteTest($nTest,$bExpectedResult, $aVarMap, $sExpression,&$aTestDetail,&$nFailedCount,&$aNodes)
    {
        $bFailed=FALSE;
        try
        {
            $oParser = new \raptor_ci\FlagExpressionParser($aVarMap);
            $oRoot = $oParser->parse($sExpression);
        } catch (\Exception $ex) {
            //Cannot assume that root is okay.
            $aTestDetail[] = ('Failed Test' . $nTest . ': "' . $sExpression . '" = Caught error ' . $ex->getMessage() );
            $bFailed=TRUE;
            //die('LOOK NOW' . print_r($aTestDetail,TRUE));
        }
        if(!$bFailed)
        {
            $bFailed = TestUtility::runOneTreeEvalTest($nTest, $bExpectedResult, $oRoot, $aTestDetail, $nFailedCount, $aNodes);
            if($bFailed)
            {
                //Add more debug info.
                $aTestDetail[count($aTestDetail)-1] .= '<br>NOTE Test'.$nTest . ' was for expression = "' . $sExpression . '"';
            }
        }
        return $bFailed;
    }
    
    
              //TestUtility::runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression, $aTestDetail, $nFailedCount, $aNodes);
    public static function runOneCompilerTest($nTest, $bExpectSytaxError, $aExpectedVars, $aVarMap, $sExpression,&$aTestDetail,&$nFailedCount,&$aNodes)
    {
        $bFailed=FALSE;
        try
        {
            $oParser = new \raptor_ci\FlagExpressionParser($aVarMap);
            $aResults = $oParser->compile($sExpression);
        } catch (\Exception $ex) {
            if($bExpectSytaxError)
            {
                $aTestDetail[] = ('Okay Test' . $nTest . ':[' . $sExpression . '] has expected parser error; details->' . $ex->getMessage());
            } else {
                $aTestDetail[] = ('Failed Compiler Test' . $nTest . ': "' . $sExpression . '" = Caught error ' . $ex->getMessage() );
                $bFailed=TRUE;
            }
        }
        if(!$bExpectSytaxError && !$bFailed)
        {
            $aDepVars = $aResults['dependencies'];
            $aMissing = array();
            foreach($aDepVars as $sVarName)
            {
                if(!in_array($sVarName, $aExpectedVars))
                {
                    $aMissing[$sVarName] = 'Invalid ' . $sVarName;
                }
            }
            foreach($aExpectedVars as $sVarName)
            {
                if(!in_array($sVarName, $aDepVars))
                {
                    $aMissing[$sVarName] = 'Expected ' . $sVarName;
                }
            }
            if(count($aMissing) > 0)
            {
                $aTestDetail[] = ('Failed Test' . $nTest . ':[' . $sExpression . '] has parsing issue for these '.count($aMissing).' variable names ' . print_r($aMissing,TRUE));
            } else {
                $aTestDetail[] = ('Okay Test' . $nTest . ':[' . $sExpression . '] has ' . count($aDepVars) . ' vars expected ' . count($aExpectedVars) . ' vars');
            }
        }
        if($bFailed)
        {
            $nFailedCount++;
        }
        return $bFailed;
    }

    public static function runOneTreeEvalTest($nTest,$bExpectedResult,$oRoot,&$aTestDetail,&$nFailedCount,&$aNodes)
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
            $aTestDetail[] = ('Failed Test' . $nTest . ':' . $oRoot . '=' . 'Caught error ' . $ex->getMessage() );
            $bFailed=TRUE;
        }
        if($bFailed)
        {
            $nFailedCount++;
        }
        return $bFailed;
    }

    public static function runOneTreeNumericEvalTest($nTest,$nExpectedResult,$oRoot,&$aTestDetail,&$nFailedCount,&$aNodes)
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
            $aTestDetail[] = ('Failed Test' . $nTest . ':' . $oRoot . '=' . 'Caught error ' . $ex->getMessage() );
            $bFailed=TRUE;
        }
        if($bFailed)
        {
            $nFailedCount++;
        }
        return $bFailed;
    }
    
    public static function runOneTreeTextEvalTest($nTest,$bExpectedResult,$oRoot,&$aTestDetail,&$nFailedCount,&$aNodes)
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
            if($bEvalResult != NULL)
            {
                $aNodes[$bEvalResult][] = $oRoot;
            }
        } catch (\Exception $ex) {
            $aTestDetail[] = ('Failed Test' . $nTest . ':' . $oRoot . '=' . 'Caught error ' . $ex->getMessage() );
            $bFailed=TRUE;
        }
        if($bFailed)
        {
            $nFailedCount++;
        }
        return $bFailed;
    }

    public static function createNode_OCC($leftConstant, $sOperator, $rightConstant)
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
    
    public static function createNode_OTT($leftText, $sOperator, $rightText)
    {
        $oLeft = new \raptor_ci\TNText($leftText);
        $oRight = new \raptor_ci\TNText($rightText);
        $oRoot = new \raptor_ci\TNOBoolean($oLeft,$sOperator,$oRight);
        return $oRoot;
    }

    public static function createNode_OVV($leftVarname, $sOperator, $rightVarname, $aVarMap)
    {
        $oLeft = new \raptor_ci\TNVariable($leftVarname,$aVarMap);
        $oRight = new \raptor_ci\TNVariable($rightVarname,$aVarMap);
        return TestUtility::createNode_ONN($oLeft,$sOperator,$oRight);
    }

    public static function createNode_OVT($leftVarname, $sOperator, $rightText, $aVarMap)
    {
        $oLeft = new \raptor_ci\TNVariable($leftVarname,$aVarMap);
        $oRight = new \raptor_ci\TNText($rightText);
        return TestUtility::createNode_ONN($oLeft,$sOperator,$oRight);
    }

    public static function createNode_OTV($leftText, $sOperator, $rightVarname, $aVarMap)
    {
        $oLeft = new \raptor_ci\TNText($leftText);
        $oRight = new \raptor_ci\TNVariable($rightVarname,$aVarMap);
        return TestUtility::createNode_ONN($oLeft,$sOperator,$oRight);
    }

    public static function createNode_ONN($leftNode, $sOperator, $rightNode)
    {
        $oRoot = new \raptor_ci\TNOBoolean($leftNode,$sOperator,$rightNode);
        return $oRoot;
    }
}
