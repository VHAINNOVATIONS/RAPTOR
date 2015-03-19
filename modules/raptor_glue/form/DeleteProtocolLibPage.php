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

require_once (RAPTOR_GLUE_MODULE_PATH . '/functions/protocol.inc');

module_load_include('php', 'raptor_datalayer', 'config/Choices');

require_once 'FormHelper.php';
require_once 'ProtocolLibPageHelper.php';
require_once 'ChildEditBasePage.php';

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 */
class DeleteProtocolLibPage extends \raptor\ChildEditBasePage
{
    private $m_oPageHelper = null;
    private $m_protocol_shortname = null;
    
     //Call same function as in EditUserPage here!
    function __construct($protocol_shortname)
    {
        if(!isset($protocol_shortname) || is_numeric($protocol_shortname))
        {
            die("Missing or invalid protocol_shortname value = " . $protocol_shortname);
        }
        $this->m_protocol_shortname = $protocol_shortname;
        $this->m_oPageHelper = new \raptor\ProtocolLibPageHelper();
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
        $myvalues =  $this->m_oPageHelper->getFieldValues($this->m_protocol_shortname);
        return $myvalues;
    }
    
    /**
     * Remove the record IF there are no records referencing this user.
     */
    function updateDatabase($form, $myvalues)
    {

        //Perform some data quality checks now.
        if(!isset($myvalues['protocol_shortname']))
        {
            die("Cannot delete record because missing protocol_shortname in array!\n" . var_dump($myvalues));
        }

        $updated_dt = date("Y-m-d H:i", time());
        

        $protocol_shortname = $myvalues['protocol_shortname'];
        
        //Backup all the existing records.
        $this->m_oPageHelper->copyProtocolLibToReplacedTable($protocol_shortname);
        $this->m_oPageHelper->copyKeywordsToReplacedTable($protocol_shortname);
        $this->m_oPageHelper->copyTemplateValuesToReplacedTable($protocol_shortname);
        
        //Delete all the records.
        $num_deleted = db_delete('raptor_protocol_lib')
          ->condition('protocol_shortname', $protocol_shortname)
          ->execute();            
        $num_deleted = db_delete('raptor_protocol_keywords')
          ->condition('protocol_shortname', $protocol_shortname)
          ->execute();            
        $num_deleted = db_delete('raptor_protocol_template')
          ->condition('protocol_shortname', $protocol_shortname)
          ->execute();            

        //Success?  
        if($num_deleted == 1)
        {
            $feedback = 'The '.$protocol_shortname.' protocol has been succesfully deleted.';
            drupal_set_message($feedback);
            return 1;
        } 

        //We are here because we failed.
        $feedback = 'Trouble deleting '.$protocol_shortname.' protocol!';
        error_log($feedback . ' delete reported ' . $num_deleted);
        drupal_set_message($feedback, 'warning');
        return 0;
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
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, TRUE, $myvalues, 'protocol_container_styles');
        
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
        $form["data_entry_area1"]['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Delete Protocol From System')
                , '#disabled' => $disabled
            );

        global $base_url;
        $goback = $this->getGobacktoFullURL();
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="admin-cancel-button" id="user-cancel"'
                . ' type="button" value="Cancel"'
                . ' data-redirect="'.$goback.'">');
        return $form;
    }
}
