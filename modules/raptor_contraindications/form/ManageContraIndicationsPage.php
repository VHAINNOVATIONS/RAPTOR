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
 *  
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
        
        module_load_include('php','raptor_datalayer','core/data_context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->hasPrivilege('ECIR1'))
        {
            throw new \Exception('The user account does not have privileges for this page.');
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled
            , $myvalues, $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['table-container'] = 'table-container';
            $html_classname_overrides['action-buttons'] = 'raptor-action-buttons';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        $form = parent::getForm($form
                , $form_state
                , $disabled
                , $myvalues
                , $html_classname_overrides=NULL);
        
        global $base_url;
        
        $form['data_entry_area1']['action_buttons']['return'] = array(
                '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit" />'); 
        
        return $form;
    }
    
 }
