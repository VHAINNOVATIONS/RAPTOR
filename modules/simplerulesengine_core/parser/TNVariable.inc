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
 * Variable class for parser tree
 *
 * @author Frank Font
 */
class TNVariable extends \simplerulesengine\ATreeNode
{
    
    protected $m_var_name_tx = NULL;
    protected $m_var_map = NULL;

    /**
     * Construct an instance of a variable to evaluate later.
     * @param type $var_name_tx the name of the variable.
     * @param type $var_map important that this is a reference because values will change at runtime!
     * @throws \Exception
     */
    public function __construct($var_name_tx, &$var_map)
    {
        if($var_name_tx == NULL)
        {
            throw new \Exception('Missing variable name!');
        } else if(is_numeric($var_name_tx)) {
            throw new \Exception('Cannot use "'.$var_name_tx.'" as a variable name!');
        }
        $this->m_var_map = &$var_map; //Important we MUST preserve the reference!
        $this->m_is_variable = TRUE;
        $this->m_var_name_tx = $var_name_tx;
    }
    
    public function getName()
    {
        return $this->m_var_name_tx;    
    }
    
    private function transformToVisible($sValue)
    {
        if($sValue === NULL)
        {
            return 'NULL';
        }
        if($sValue === FALSE)
        {
            return 'FALSE';
        }
        if($sValue === TRUE)
        {
            return 'TRUE';
        }
        return $sValue;
    }
    
    public function __toString()
    {
        try
        {
            if(is_array($this->getValue()))
            {
                $text = $this->m_var_name_tx . '['. print_r($this->getValue(),TRUE) .']';
            } else {
                $text = $this->m_var_name_tx . '['. $this->transformToVisible($this->getValue()) .']';
            }
        } catch (\Exception $ex) {
            $text = $this->m_var_name_tx . '['. $ex->getMessage() .']';
        }
        return $text;
    }
    
    public function getValue($bVerboseDiagnostic = FALSE)
    {
        if(!array_key_exists($this->m_var_name_tx, $this->m_var_map))
        {
            throw new \Exception('Did not find a defined value for variable ['.$this->m_var_name_tx.'] in context '.print_r($this->m_var_map,TRUE));
        }
        $sValue = $this->m_var_map[$this->m_var_name_tx];
        if($bVerboseDiagnostic)
        {
            drupal_set_message(t('Verbose variable: (' . $this->m_var_name_tx . ' has value ' . $this->transformToVisible($sValue) . ')'));
        }
        return $sValue;
    }
}
