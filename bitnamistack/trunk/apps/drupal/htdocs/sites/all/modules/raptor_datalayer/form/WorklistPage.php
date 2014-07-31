<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once("FormHelper.php");

/**
 * This class returns the Admin Information input content
 *
 * @author FrankWin7VM
 */
class WorklistPage
{
    /**
     * Get the values to populate the form.
     * @param type $nUID the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        //TODO
    }
    
    /**
     * Write the values into the database.
     * @param type associative array where 'username' MUST be one of the values so we know what record to update.
     */
    function updateDatabase()
    {
        //TODO
        return 1;
    }

    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        //TODO
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='login-dataentry'>\n",
            '#suffix' => "\n</section>\n",
        );

        $form["data_entry_area1"]['stuff'] = array(
          '#type' => 'item', 
          '#markup' => '<h1>My worklist page code</h1>',
        );
        
        $form["data_entry_area1"]['submit'] = array('#type' => 'submit', '#value' => t('Change'));

        return $form;
    }


}
