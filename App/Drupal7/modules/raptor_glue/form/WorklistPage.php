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

require_once 'ASimpleFormPage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class WorklistPage extends \raptor\ASimpleFormPage
{
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
