<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140730
 */

namespace raptor_ci;

/**
 * Text node for parser tree
 *
 * @author FrankWin7VM
 */
class TNText extends \raptor_ci\ATreeNode
{
    
    protected $m_sLiteral = NULL;

    public function __construct($sLiteral)
    {
        $this->m_bConstant = TRUE;
        $this->m_sLiteral = (string) $sLiteral;
    }
    
    public function __toString()
    {
        return '"'.$this->m_sLiteral.'"';
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if($bVerboseDiagnostic)
        {
            drupal_set_message('Verbose text literal: ("' . $this->m_sLiteral . '")' );
        }
        return $this->m_sLiteral;
    }
}
