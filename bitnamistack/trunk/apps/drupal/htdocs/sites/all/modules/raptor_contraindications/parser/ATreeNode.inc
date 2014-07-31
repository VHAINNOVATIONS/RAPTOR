<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Abstract class for parser tree
 *
 * @author FrankWin7VM
 */
abstract class ATreeNode implements \raptor_ci\ITreeNode
{
    protected $m_bConstant = FALSE;
    protected $m_bVariable = FALSE;
    protected $m_bOperator = FALSE;
    protected $m_bBoolean = FALSE;
    protected $m_bNumeric = FALSE;

    public function isConstant()
    {
        return $this->m_bConstant;
    }
    
    public function isVariable()
    {
        return $this->m_bVariable;
    }
    
    public function isOperator()
    {
        return $this->m_bOperator;
    }
    
    public function isBoolean()
    {
        return $this->m_bBoolean;
    }

    public function isNumeric()
    {
        return $this->m_bNumeric;
    }

    public function getLeftChild() 
    {
        return NULL;
    }
    
    public function getRightChild() 
    {
        return NULL;
    }
}
