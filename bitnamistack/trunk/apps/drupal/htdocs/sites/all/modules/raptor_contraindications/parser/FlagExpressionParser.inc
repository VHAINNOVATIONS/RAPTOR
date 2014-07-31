<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140729
 */

namespace raptor_ci;

require_once('ITreeNode.inc');
require_once('ATreeNode.inc');
require_once('TNOperator.inc');
require_once('TNOBoolean.inc');
require_once('TNONumeric.inc');
require_once('TNVariable.inc');
require_once('TNConstant.inc');
require_once('TNText.inc');
require_once('AExpressionParser.inc');


/**
 * Parser cor contraindication flag expression
 *
 * @author FrankWin7VM
 */
class FlagExpressionParser extends \raptor_ci\AExpressionParser
{
    public function __construct($aVariableMap=NULL)
    {
        if($aVariableMap == NULL)
        {
            $this->m_aVariableMap = array();
        } else {
            $this->m_aVariableMap = $aVariableMap;
        }
    }
    
    /**
     * Parses the string into a tree of nodes
     * @param type $sExpression
     * @return tree of nodes
     */
    public function parse($sExpression)
    {
        $this->m_sExpression = '('.$sExpression.')';    //IMPORTANT THAT WE ALWAYS WRAP IN OUTER PARENS SO TERMS ARE NOT LOST!
        if($sExpression !== NULL)
        {
            $this->m_aTokens = FlagExpressionParser::getTokens($this->m_sExpression);
            $this->m_oTree = FlagExpressionParser::getTree($this->m_aTokens);
        }
        return $this->m_oTree;
    }
    
    /**
     * Compile the expression to produce metadata
     * keys: dependencies, errors
     * @param type $sExpression
     * @return array of compilation details
     */
    public function compile($sExpression)
    {
        $oRoot=NULL;
        $aDependencies = array();   //Names of dependencies
        $aErrors = array();         //Text of errors
        try
        {
            $oRoot = $this->parse($sExpression);
            $aDependencies = $this->getAllVariableNames($oRoot);
        } catch (\Exception $ex) {
            $aErrors[] = "Trouble parsing because " . $ex->getMessage();
        }
        return array('haserrors'=>(count($aErrors) > 0),'dependencies'=>$aDependencies, 'errors'=>$aErrors, 'treeroot'=>$oRoot);
    }
    
    /**
     * Return the next null and the next offset or NULL if done.
     * @param array $aTokens
     * @param int $nNewOffset
     * @param int $nLevel nesting level
     * @return root node or NULL
     */
    private function getNextNode($aTokens,&$nNewOffset,$nLevel)
    {
        $nLevel++;
        $bNegate = FALSE;
        $i = $nNewOffset;
        $oLeftTerm=NULL;
        $sOperator=NULL;
        $oRightTerm=NULL;
        $nTokens = count($aTokens);
        $nOriginalOffset = $nNewOffset;
        while($i < $nTokens)
        {
            $sToken = $aTokens[$i];
            //drupal_set_message("LOOK level $nLevel INITIAL TOKEN=[$sToken]");
            if($sToken == '(')
            {
                //Inside of a parenthetical
                $i++;
                $oLeftTerm = $this->getNextNode($aTokens, $i, $nLevel);
                if($i < $nTokens)
                {
                    $sToken = $aTokens[$i];
                    if($sToken == ')')
                    {
                        //Done, unwind.
                        //drupal_set_message("LOOK level $nLevel only ONE TERM: 1[$oLeftTerm]@$i " . ' of ' . print_r($aTokens,TRUE));
                    } else {
                        //This term is part of a tuple.
                        $sOperator = $aTokens[$i];
                        $i++;
                        $oRightTerm = $this->getNextNode($aTokens, $i, $nLevel);
                        if($oRightTerm == NULL)
                        {
                            throw new \Exception("Ran out of text in nesting level $nLevel but a term " .print_r($aTokens,TRUE));
                        }
                        //drupal_set_message("LOOK level $nLevel ALL THREE TERMS: 1[$oLeftTerm] 2[$sOperator] 3[$oRightTerm] @$i" . ' of ' . print_r($aTokens,TRUE));
                        if($i < 0)
                        {
                            throw new \Exception("Ran out of text in nesting level $nLevel but expected closing parenthesize in " .print_r($aTokens,TRUE));
                        }
                        $sToken = $aTokens[$i];
                        if($sToken !== ')')
                        {
                            throw new \Exception("In nesting level $nLevel expected closing parenthesize at offset $i of " .print_r($aTokens,TRUE));
                        }
                    }
                    $i++;
                }
                break;  //Unwind!
                //drupal_set_message('A2 token in paren [' . $oLeftTerm . '] next Offset is ' . $i . ' :Expecting op at next pos and then after that more ' . print_r($aTokens,TRUE),'warning');
            } else if($sToken == 'not') {
                //This contains a negation
                $bNegate = TRUE;
                $i++;
                $oLeftTerm = $this->getNextNode($aTokens, $i, $nLevel);
                break; 
            } else {
                //drupal_set_message("!!!LOOK level $nLevel ELSE GOT [".$sToken ."] at $i ".' of token array ' . print_r($aTokens,TRUE) . '!');
                if($sToken != ')')
                {
                    //We expect this to be a single term.
                    if($sToken == '"')
                    {
                        //This contains a text literal term
                        $i++;
                        $oLeftTerm = new \raptor_ci\TNText($aTokens[$i]);
                        $i++;
                        if($aTokens[$i] != '"')
                        {
                            throw new \Exception('Expected a closing quote at position '.$i.' of token array ' . print_r($aTokens,TRUE) . '!');
                        }
                    } else {
                        if(is_numeric($sToken))
                        {
                            $oLeftTerm = new \raptor_ci\TNConstant($sToken);
                        } else {
                            $oLeftTerm = new \raptor_ci\TNVariable($sToken, $this->m_aVariableMap);
                        }
                    }
                }
                $i++;
                break;
            }
        }
        $nNewOffset = $i;
        if($oLeftTerm == NULL)
        {
            //Nothing.
            $oNode = NULL;
            $nNewOffset = -1;
        } else
        if($sOperator == NULL)
        {
            //One simple node.
            $oNode = $oLeftTerm;
        } else {
            //A node with two children.
            if(get_class($oLeftTerm) == 'TNText')
            {
                //This can only be a boolean operation.
                $oNode = new \raptor_ci\TNOBoolean($oLeftTerm,$sOperator,$oRightTerm);
            } else {
                //Might be math operation.
                if(FlagExpressionParser::isNumericOperator($sOperator))
                {
                    $oNode = new \raptor_ci\TNONumeric($oLeftTerm,$sOperator,$oRightTerm);
                } else {
                    $oNode = new \raptor_ci\TNOBoolean($oLeftTerm,$sOperator,$oRightTerm);
                }
            }
        }
        if($bNegate)
        {
            //Negate the result of the node.
            $oNode = new \raptor_ci\TNONegate($oNode);
        }
        return $oNode;
    }
    
    private function getTree($aTokens)
    {
        $nNewPosition = 0;
        $oRoot = $this->getNextNode($aTokens,$nNewPosition,0);
        return $oRoot;
    }
}
