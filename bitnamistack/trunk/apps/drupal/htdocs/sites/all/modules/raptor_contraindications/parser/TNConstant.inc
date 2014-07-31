<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Constant class for parser tree
 *
 * @author FrankWin7VM
 */
class TNConstant extends \raptor_ci\ATreeNode
{
    
    protected $m_nConstant = NULL;

    public function __construct($nConstant)
    {
        if($nConstant !== NULL && !is_numeric($nConstant))
        {
            throw new \Exception('The value "'.$nConstant.'" is not a valid numeric constant!');
        }
        $this->m_bConstant = TRUE;
        $this->m_nConstant = $nConstant;
    }
    
    public function __toString()
    {
        return (string) $this->m_nConstant; //MUST cast this into a string!
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if($bVerboseDiagnostic)
        {
            drupal_set_message('Verbose constant: (' . $this->m_nConstant . ')' );
        }
        return $this->m_nConstant;
    }
}
