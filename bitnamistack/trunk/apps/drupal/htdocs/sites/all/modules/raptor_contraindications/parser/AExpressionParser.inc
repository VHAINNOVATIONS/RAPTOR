<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140730
 */

namespace raptor_ci;

require_once('ITreeNode.inc');
require_once('ATreeNode.inc');
require_once('TNOperator.inc');
require_once('TNOBoolean.inc');
require_once('TNONegate.inc');
require_once('TNONumeric.inc');
require_once('TNVariable.inc');
require_once('TNConstant.inc');
require_once('TNText.inc');
require_once('IExpressionParser.inc');

/**
 * Parser for contraindication flag expressions
 *
 * @author FrankWin7VM
 */
abstract class AExpressionParser implements IExpressionParser
{
    private $m_aVariableMap = NULL;   //Key is the variable name, value is the value of the variable
    private $m_sExpression = NULL;
    private $m_aTokens = NULL;
    private $m_oTree = NULL;
    
    
    /**
     * See if any names are missing from the map.
     * @param type $aRefVarMap
     * @param type $aCheckNames
     */
    public function getMissingKeys($aRefVarMap,$aCheckNames)
    {
        $aMissingKey = array();
        foreach($aCheckNames as $sName)
        {
            if(!array_key_exists($sName, $aRefVarMap))
            {
                $aMissingKey[] = $sName;
            }
        }
        return $aMissingKey;
    }
    
    /**
     * Get an array (key,value) of all the variables in the branch.
     * @param type $oRoot
     */
    public function getAllVariableNames($oRoot)
    {
        if(get_class($oRoot) == 'raptor_ci\TNVariable')
        {
            $sName = $oRoot->getName();
            $aMerged = array($sName => $sName); //Support key and value searches
            //drupal_set_message('aaaaaa LOOK VARIABLE class='.get_class($oRoot) . ' >>> ' . $oRoot);
        } else
        if(is_subclass_of($oRoot, 'raptor_ci\TNOperator'))
        {
            $aMerged = array();
            $a = $this->getAllVariableNames($oRoot->getLeftChild());
            if(is_array($a))
            {
                $aMerged = $a;
            }
            $a = $this->getAllVariableNames($oRoot->getRightChild());
            if(is_array($a))
            {
                $aMerged = array_merge($aMerged,$a);
            }
            //drupal_set_message('aaaaaa LOOK operator class='.get_class($oRoot));
        } else {
            $aMerged = NULL;
        }
        //drupal_set_message('aaaaaa LOOK result for class '.get_class($oRoot) . '=' . print_r($aMerged,TRUE));
        return $aMerged;
    }
    
    /**
     * Get an array (just values) of all the operators in the branch.
     * @param type $oRoot
     */
    public function getAllOperatorTokens($oRoot)
    {
        if(is_subclass_of($oRoot, 'raptor_ci\TNOperator'))
        {
            $aMerged = array($oRoot->getOperator());
            $a = $this->getAllOperatorTokens($oRoot->getLeftChild());
            if(is_array($a))
            {
                $aMerged = $a;
            }
            $a = $this->getAllOperatorTokens($oRoot->getRightChild());
            if(is_array($a))
            {
                $aMerged = array_merge($aMerged,$a);
            }
        } else {
            $aMerged = NULL;
        }
        return $aMerged;
    }

    /**
     * Get an array of all non-blank symbols and terms
     */
    public static function getTokens($sExpression)
    {
        $len = strlen($sExpression);
        $aTokens = array();
        $startpos = 0;
        $havechars = FALSE;
        for($i=0; $i<$len; $i++)
        {
            $char = $sExpression[$i];
            if(strpos(' ()"',$char) !== FALSE)
            {
                if($havechars)
                {
                    //We are at the end of a token
                    $token = trim(substr($sExpression,$startpos,$i-$startpos));
                    $aTokens[] = $token;
                    $havechars = FALSE;
                    $startpos = $i; //Start here
                } else {
                    $startpos = $i; //Keep moving it back
                }
                if(strpos('()',$char) !== FALSE)
                {
                    $aTokens[] = $char;
                    $startpos = $i+1; //Start here
                } else
                if('"'==$char)
                {
                    $aTokens[] = $char;
                    $startpos = $i+1; //Start here
                }
            } else {
                $havechars = TRUE;
            }
        }
        if($havechars)
        {
            $token = trim(substr($sExpression,$startpos,$i-$startpos));
            $aTokens[] = $token;
        }
        return $aTokens;
    }
    
    public static function isNumericOperator($sOperator)
    {
        return (strpos('*/+-',$sOperator) !== FALSE);
    }
}
