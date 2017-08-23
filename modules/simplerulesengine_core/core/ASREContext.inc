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
 * Interface for context information that simple rules engine needs at execution time.
 *
 * @author Frank Font
 */
abstract class ASREContext 
{
    /**
     * @return name of rule database table
     */
    abstract public function getRuleTablename();
    
    /**
     * @return name of measure database table
     */
    abstract public function getMeasureTablename();
    
    /**
     * The engine constructor requires an array with these keys.
     * @return array of names
     */
    abstract public function getRequiredBaselineInfoKeys();
    
    /**
     * The rules evaluator requires an array with these keys.
     * @return array of names
     */
    abstract public function getRequiredCandidateDataKeys();
    
    /**
     * Return the dictionary instance that expains all of the coded inputs.
     */
    abstract public function getDictionary();
}
