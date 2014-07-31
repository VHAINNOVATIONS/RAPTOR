<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Boolean operator class for parser tree
 *
 * @author FrankWin7VM
 */
class TNOBoolean extends \raptor_ci\TNOperator
{
    public function __construct($oLeft,$sOperator,$oRight)
    {
        //drupal_set_message('TNOB Look L O R ==<br>(((' . print_r($oLeft,TRUE) . ')))<br>' . $sOperator . '<br>(((' . print_r($oRight,TRUE) . ')))', 'warning');
        parent::__construct($oLeft,$sOperator,$oRight);
        $this->m_bBoolean = TRUE;
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        //Evaluate an expression.
        if($this->getLeftChild() !== NULL)
        {
            $left = $this->getLeftChild()->getValue($bVerboseDiagnostic);
        } else {
            $left = NULL;
        }
        if($this->getRightChild() !== NULL)
        {
            $right = $this->getRightChild()->getValue($bVerboseDiagnostic);
        } else {
            $right = NULL;
        }
        if(($right === NULL) 
            || ($this->m_sOperator !== 'not' && $left === NULL))
        {
            //Evaluate as NULL with no further processing.
            $bResult = NULL;    
        } else {
            if($this->m_sOperator != 'and' && $this->m_sOperator != 'or' 
                    && !is_numeric($left) && !is_numeric($right) 
                    && $right !== FALSE && $right !== TRUE)
            {
                //This is a text comparison
                if($this->m_sOperator == '=')
                {
                    //Case insensitive equality check
                    $bResult = (strtoupper($left) == strtoupper($right));
                } else if($this->m_sOperator == '<>') {
                    //Case insensative inequality check
                    $bResult = (strtoupper($left) !== strtoupper($right));
                } else if($this->m_sOperator == '!==') {
                    //Case sensative inequality check
                    $bResult = ($left !== $right);
                } else if($this->m_sOperator == '==') {
                    //Case sensitive equality check
                    $bResult = ($left == $right);
                } else {
                    throw new \Exception('Cannot process evaluation for text operator [' . $this->m_sOperator . '] on values [' . $left . '] and [' . $right . ']');
                }
            } else {
                //This is a non-text comparison
                if($this->m_sOperator == 'and')
                {
                    $bResult = ($left && $right);
                } else if($this->m_sOperator == 'or') {
                    $bResult = ($left || $right);
                } else if($this->m_sOperator == '>') {
                    $bResult = ($left > $right);
                } else if($this->m_sOperator == '<') {
                    $bResult = ($left < $right);
                } else if($this->m_sOperator == '<=') {
                    $bResult = ($left <= $right);
                } else if($this->m_sOperator == '>=') {
                    $bResult = ($left >= $right);
                } else if($this->m_sOperator == '<>') {
                    $bResult = ($left != $right);
                } else if($this->m_sOperator == '!==') {
                    //Same as simple '<>'
                    $bResult = ($left != $right);
                } else if($this->m_sOperator == '=') {
                    $bResult = ($left == $right);
                } else if($this->m_sOperator == '==') {
                    //Same as simple '='
                    $bResult = ($left == $right);
                } else if($this->m_sOperator == 'not') {
                    $bResult = (!$right);
                } else {
                    throw new \Exception('Cannot process evaluation for operator boolean [' . $this->m_sOperator . '] on values [' . $left . '] and [' . $right . ']');
                }
            }
        }
        if($bVerboseDiagnostic)
        {
            drupal_set_message('Verbose boolean result: (' . $left . ' ' . $this->m_sOperator . ' ' . $right . ') is ' . ($bResult ? 'TRUE' : 'FALSE') );
        }
        return $bResult;
    }
}
