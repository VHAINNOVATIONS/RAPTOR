<?php
/**
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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
require_once ('FormHelper.php');
//module_load_include('php', 'raptor_datalayer', 'core/data_worklist');

/**
 * Implementes the radation dose edit page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class EditRadiationDoseHxEntryPage
{
    private $m_oContext = null;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        //TODO
        $myvalues = array();
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        //TODO - special checks here
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        //TODO
        return 1;
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues)
    {

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        
        //TODO
        
        $form['data_entry_area1']['toppart']['placeholder'] = array(
            '#markup'         => '<p>'.t('PLACEHOLDER FOR THE EDIT FORM').'</p>',
        );
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['action_buttons']['remove'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Save This Radiation Dose Entry')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

