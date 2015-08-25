<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.

 * ------------------------------------------------------------------------------------
 *
 * This is a simple decision support engine module for Drupal.
 */

namespace simplerulesengine;

/**
 * Boolean unary operator class for parser tree
 *
 * @author Frank Font
 */
abstract class TNOUnary extends \simplerulesengine\ATreeNode
{
    protected $m_sFunctionName = NULL;
    protected $m_aParams = NULL;
    
    public function __construct($sFunctionName,$aParams)
    {
        if($sFunctionName == NULL)
        {
            throw new \Exception('Missing function name!');
        } else if(is_numeric($sFunctionName)) {
            throw new \Exception('Cannot use "'.$sFunctionName.'" as a function name!');
        }
        if(!is_array($aParams))
        {
            throw new \Exception('Must provide an array for "'.$sFunctionName.'"!');
        }
        if(count($aParams) == 0)
        {
            throw new \Exception('Must provide an array for "'.$sFunctionName.'" that is not empty!');
        }
        $this->m_sFunctionName = $sFunctionName;
        $this->m_aParams = $aParams;
        $this->m_bBoolean = TRUE;
    }
    
    public function getName()
    {
        return $this->m_sFunctionName;
    }
    
    public function getParameters()
    {
        return $this->m_aParams;
    }
    
    public function __toString()
    {
        try
        {
            $text = $this->m_sFunctionName . '('. implode(',',$this->m_aParams) .')';
        } catch (\Exception $ex) {
            $text = $this->m_sFunctionName . '('. $ex->getMessage() .')';
        }
        return $text;
    }
}
