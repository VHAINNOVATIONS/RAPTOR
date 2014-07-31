<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

require_once(dirname(__FILE__) . '/../config/choices.php');
require_once('FormHelper.php');
module_load_include('inc', 'raptor_datalayer', 'core/data_user.php');

/**
 * Helper for report pages.
 *
 * @author FrankWin7VM
 */
class ReportPageHelper
{
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues, $aHelpText)
    {

        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='user-profile-dataentry'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        
        //TODO

        $form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Refresh the Report')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="/drupal/worklist?dialog=viewreports">');
        
        return $form;
    }
}
