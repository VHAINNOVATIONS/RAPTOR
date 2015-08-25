<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
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
 */ 

namespace raptor;

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
abstract class ASimpleFormPage
{
    /**
     * @return a myvalues array
     */
    function getFieldValues()
    {
       return array();
    }
    
    /**
     * Use form state to validate the form.
     */
    function looksValidFormState($form, &$form_state)
    {
        $myvalues = $form_state['values'];
        return $this->looksValid($form, $myvalues);
    }    
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValid($form, $myvalues)
    {
        return TRUE;
    }    
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        return TRUE;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
       return array();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract function getForm($form, &$form_state, $disabled, $myvalues_override);

}
