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

require_once 'ContraindicationPageHelper.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewContraindicationPage
{
    private $m_oContext = null;
    private $m_oPageHelper = null;
    private $m_rule_nm = null;
    
    //Call same function as in EditUserPage here!
    function __construct($rule_nm)
    {
        if (!isset($rule_nm) || is_numeric($rule_nm)) {
            die("Missing or invalid rule_nm value = " . $rule_nm);
        }
        $this->m_oContext    = \raptor\Context::getInstance();
        $this->m_rule_nm     = $rule_nm;
        $this->m_oPageHelper = new \raptor\ContraIndicationPageHelper();
        
        
        module_load_include('php','raptor_datalayer','core/data_context');
        $oContext = \raptor\Context::getInstance();
        $oUserInfo = $oContext->getUserInfo();
        if(!$oUserInfo->hasPrivilege('ECIR1'))
        {
            throw new \Exception('The user account does not privileges for this page.');
        }
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_rule_nm);
    }
    
    /**
     * @return array of all option values for the form
     */
    function getAllOptions()
    {
        return $this->m_oPageHelper->getAllOptions();
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('V',$form, $form_state, $disabled, $myvalues);
        
        //These buttons should not be in the helper..... TODO move them to the pages
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => '<div class="raptor-action-buttons">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        global $base_url;
        /*
        $worklist_url = $base_url . '/worklist';
        $form['data_entry_area1']['action_buttons']['cancel'] = array(
            '#type' => 'item',
            '#markup' => '<input class="admin-cancel-button" type="button" value="Cancel" data-redirect="'.$worklist_url.'?dialog=manageContraindications">'
        );
         */
        $goback = $base_url.'/raptor/managecontraindications';
        $form['data_entry_area1']['action_buttons']['cancel'] = ContraindicationPageHelper::getExitButtonMarkup($goback);
        return $form;
    }
}
