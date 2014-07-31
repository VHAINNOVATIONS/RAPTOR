<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

require_once('TNVariable.inc');
require_once('TNConstant.inc');

/**
 * Operator class for parser tree
 *
 * @author FrankWin7VM
 */
abstract class TNOperator extends \raptor_ci\ATreeNode
{
    
    protected $m_sOperator = NULL;
    protected $m_oLeftChild = NULL;
    protected $m_oRightChild = NULL;

    public function __construct($oLeft,$sOperator,$oRight)
    {
        //drupal_set_message('OP Look L O R ==<br>(((' . print_r($oLeft,TRUE) . ')))<br>' . $sOperator . '<br>(((' . print_r($oRight,TRUE) . ')))', 'warning');
        
        $this->m_bOperator = TRUE;
        $this->m_sOperator = $sOperator;
        $this->m_oLeftChild = $oLeft;
        $this->m_oRightChild = $oRight;
    }
    
    public function __toString()
    {
        try 
        {
            $left = $this->m_oLeftChild;
            $right = $this->m_oRightChild;
            if($left != NULL)
            {
                return '(' . $left . ' ' . $this->m_sOperator . ' ' . $right . ')' ;
            } else {
                return $this->m_sOperator . '(' . $right . ')';
            }
        } catch (\Exception $ex) {
            drupal_set_message('Cannot print because ' . $ex->getMessage(),'error');
        }
    }
    
    public function getOperator()
    {
        return $this->m_sOperator;
    }
    
    public function getLeftChild()
    {
        return $this->m_oLeftChild;
    }
    
    public function getRightChild()
    {
        return $this->m_oRightChild;
    }
}
