<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Numeric operator class for parser tree
 *
 * @author FrankWin7VM
 */
class TNONumeric extends \raptor_ci\TNOperator
{
    public function __construct($oLeft,$sOperator,$oRight)
    {
        parent::__construct($oLeft,$sOperator,$oRight);
        $this->m_bNumeric = TRUE;
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        //Evaluate an expression.
        $left = $this->getLeftChild()->getValue();
        $right = $this->getRightChild()->getValue();
        if($this->m_sOperator == '*')
        {
            $bResult = ($left * $right);
        } else if($this->m_sOperator == '/') {
            $bResult = ($left / $right);
        } else if($this->m_sOperator == '+') {
            $bResult = ($left + $right);
        } else if($this->m_sOperator == '-') {
            $bResult = ($left - $right);
        } else {
            throw new \Exception('Cannot process evaluation for numeric operator [' . $this->m_sOperator . '] on values [' . $left . '] and [' . $right . ']');
        }
        if($bVerboseDiagnostic)
        {
            drupal_set_message('Verbose numeric result: ' . $left . ' ' . $this->m_sOperator . ' ' . $right);
        }
        return $bResult;
    }
}
