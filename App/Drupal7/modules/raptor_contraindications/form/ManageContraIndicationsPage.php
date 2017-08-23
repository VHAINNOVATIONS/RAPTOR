<?php

/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

namespace raptor;

module_load_include('php','simplerulesengine_ui','form/ManageRulesPage');
module_load_include('inc','raptor_contraindications','core/ContraIndEngine');

/**
 * This class returns the list of available Rules
 *
 * @author Frank Font of SAN Business Consultants
 */
class ManageContraIndicationsPage extends \simplerulesengine\ManageRulesPage
{

    public function __construct()
    {
        parent::__construct(new \raptor\ContraIndEngine(NULL)
                ,
                    array('add'=>'raptor/addcontraindication'
                        , 'edit'=>'raptor/editcontraindication'
                        , 'delete'=>'raptor/deletecontraindication'
                        , 'view'=>'raptor/viewcontraindication'
                        , 'return'=>NULL)
                );
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $aHtmlClassnameOverrides=NULL)
    {
        if($aHtmlClassnameOverrides == NULL)
        {
            //Set the default values.
            $aHtmlClassnameOverrides = array();
            $aHtmlClassnameOverrides['data-entry-area1'] = 'data-entry-area1';
            $aHtmlClassnameOverrides['table-container'] = 'table-container';
            $aHtmlClassnameOverrides['action-buttons'] = 'raptor-action-buttons';
            $aHtmlClassnameOverrides['action-button'] = 'action-button';
        }
        $form = parent::getForm($form, $form_state, $disabled, $myvalues, $aHtmlClassnameOverrides=NULL);
        
        global $base_url;
        
        /*
        $form['data_entry_area1']['action_buttons']['create'] = array(
                '#markup' => '<input class="raptor-dialog-submit" type="button" value="Add Rule" ' 
                .'data-redirect="'.$base_url.'/raptor/addcontraindication" />');
        */
        
        $form['data_entry_area1']['action_buttons']['return'] = array(
                '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel" />'); 
        
        return $form;
    }
    
 }
