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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
require_once 'FormHelper.php';
require_once 'ProtocolLibPageHelper.php';
require_once 'ChildEditBasePage.php';


/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class ViewProtocolLibPage extends \raptor\ChildEditBasePage
{
    private $m_oPageHelper = null;
    private $m_protocol_shortname = null;
    
     //Call same function as in EditUserPage here!
    function __construct($protocol_shortname)
    {
        if(!isset($protocol_shortname) || is_numeric($protocol_shortname))
        {
            die('Missing or invalid protocol_shortname value = ' . $protocol_shortname);
        }
        $this->m_protocol_shortname = $protocol_shortname;
        $this->m_oPageHelper = new \raptor\ProtocolLibPageHelper();
        
        //Set the default gobackto url now
        global $base_url;
        $this->setGobacktoURL($base_url.'/raptor/manageprotocollib');
    }

    /**
     * Get the values to populate the form.
     * @param type $sProtocolName the user id
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_protocol_shortname);
        return $myvalues;
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
        $form = $this->m_oPageHelper->getForm('V',$form, $form_state, TRUE, $myvalues, 'protocol_container_styles');
        
        $form['data_entry_area1']['toppart']['protocol_shortname'] = array(
          '#type' => 'textfield', 
          '#title' => t('Short Name'), 
          '#value' => $this->m_protocol_shortname, 
          '#size' => 20, 
          '#maxlength' => 20, 
          '#required' => TRUE,
          '#description' => t('The unique short name for this protocol'),
          '#disabled' => TRUE,
        );        
      
        
        //Replace the buttons
        $goback = $this->getGobacktoFullURL();
        /*
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Exit"'
                . ' data-redirect="'.$goback.'">');
        
         */
        $form['data_entry_area1']['action_buttons']['cancel'] = $this->getExitButtonMarkup($goback);
        return $form;
    }
}
