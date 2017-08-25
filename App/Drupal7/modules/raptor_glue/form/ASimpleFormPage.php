<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2014
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 * 
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
