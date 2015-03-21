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
 * These are order specifcic global functions.
 */

function raptor_glue_cancelorders_form() 
{
    module_load_include('php', 'raptor_glue', 'form/CancelOrdersPage');
    return drupal_get_form("raptor_glue_cancelorders_form_builder");
}

function raptor_glue_cancelorders_form_builder($form, &$form_state)
{
    $oPI = new \raptor\CancelOrdersPage();
    $form = array();
    $disabled = FALSE;
    if(isset($form_state['values']))
    {
        $myvalues = $form_state['values'];    
    } else {
        $myvalues = $oPI->getInitialFieldValues();    
    }
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_cancelorders_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrdersPage();
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_cancelorders_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrdersPage();
    $myvalues = $form_state['values'];
    $is_okay = $oPI->updateDatabase($form, $myvalues);
    if($is_okay)
    {
        
        //Success!
        $form_state['redirect'] = array(
            'worklist',
             array(),
          );
    }
}
