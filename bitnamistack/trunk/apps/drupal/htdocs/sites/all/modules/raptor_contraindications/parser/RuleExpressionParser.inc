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
require_once('FlagExpressionParser.inc');


/**
 * Parser cor contraindication rule expression
 *
 * @author FrankWin7VM
 */
class RuleExpressionParser extends \raptor_ci\FlagExpressionParser
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
}
