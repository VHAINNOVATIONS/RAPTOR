<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 * 
 * Updated 20140727
 */

namespace raptor_ci;

/**
 * Variable class for parser tree
 *
 * @author FrankWin7VM
 */
class TNVariable extends \raptor_ci\ATreeNode
{
    
    protected $m_sVariableName = NULL;
    protected $m_aVariableMap = NULL;

    public function __construct($sVariableName, &$aVariableMap)
    {
        if($sVariableName == NULL)
        {
            throw new \Exception('Missing variable name!');
        } else if(is_numeric($sVariableName)) {
            throw new \Exception('Cannot use "'.$sVariableName.'" as a variable name!');
        }
        $this->m_aVariableMap = $aVariableMap;
        $this->m_bVariable = TRUE;
        $this->m_sVariableName = $sVariableName;
    }
    
    public function getName()
    {
        return $this->m_sVariableName;    
    }
    
    public function __toString()
    {
        try
        {
            $text = $this->m_sVariableName . '['. $this->getValue() .']';
        } catch (\Exception $ex) {
            $text = $this->m_sVariableName . '['. $ex->getMessage() .']';
        }
        return $text;
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if(!array_key_exists($this->m_sVariableName, $this->m_aVariableMap))
        {
            throw new \Exception('Did not find a defined value for variable ['.$this->m_sVariableName.'] in context '.print_r($this->m_aVariableMap,TRUE));
        }
        $sValue = $this->m_aVariableMap[$this->m_sVariableName];
        if($bVerboseDiagnostic)
        {
            drupal_set_message('Verbose variable: (' . $this->m_sVariableName . ' has value ' . $sValue . ')' );
        }
        return $sValue;
    }
}
