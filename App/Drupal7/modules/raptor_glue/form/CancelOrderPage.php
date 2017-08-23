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

module_load_include('php', 'raptor_datalayer', 'config/Choices');
module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
require_once ('FormHelper.php');

/**
 * Implementes the cancel order page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class CancelOrderPage extends \raptor\ASimpleFormPage
{
    private $m_oContext = null;
    private $m_oTT = null;

    function __construct()
    {
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $tid = $this->m_oContext->getSelectedTrackingID();
        if($tid == NULL || trim($tid) == '' || trim($tid) == 0)
        {
            throw new \Exception('Missing selected ticket number!');
        }
        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getDashboardMap();    //$tid);
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        $myvalues['reason'] = '';
        $myvalues['notes_tx'] = '';
        //$myvalues['esig'] = '';
        
        
        $this->m_oContext = \raptor\Context::getInstance();
        $myvalues['tid'] = $this->m_oContext->getSelectedTrackingID();

        //TODO: Pre-populate values for display
        
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     * @param type $form
     * @param type $myvalues
     * @return TRUE or FALSE
     */
    function looksValid($form, $myvalues)
    {
        $bGood = TRUE;
        if(!isset($myvalues['reason']) || !is_numeric(($myvalues['reason'])))
        {
            form_set_error('reason','Did not find any a valid cancel reason');
            $is_good = FALSE;
        }
        return $bGood;
    }
    
    /**
     * Perform the change
     */
    function updateDatabase($form, $myvalues)
    {
        //Try to cancel the record now
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());

        $is_okay = TRUE;
        $orderIEN = $nIEN;
        $reasonCode = $myvalues['reason'];
        $cancelcomment = $myvalues['notes_tx'];

        try
        {
            $oContext = \raptor\Context::getInstance();
            $userinfo = $oContext->getUserInfo();
            
            //Write a suspend record locally
            //TODO
                    
            //Now cancel the record in VISTA
            $mdwsDao = $oContext->getMdwsClient();
            MdwsUtils::cancelRadiologyOrder($mdwsDao, $reasonCode, $orderIEN);
        } catch (\Exception $ex) {
            $msg = 'Failed to cancel Radiology Order because '.$ex->getMessage();
            drupal_set_message($msg,'error');
            error_log($msg.'\n\tParams were reasoncode=['.$reasonCode.'] and IEN=['.$orderIEN.']');
            throw $ex;
        }
        
        //Write success message
        drupal_set_message('Cancelled Order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');

        return $is_okay;
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

        
        //TODO real form fields
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);

        $mdwsDao = $this->m_oContext->getMdwsClient();
        $aCancelOptions = MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $form['data_entry_area1']['toppart']['reason'] = array(
            "#type" => "select",
            "#title" => t("Reason"),
            "#options" => $aCancelOptions,
            "#description" => t("Select reason for cancelling this order."),
            "#required" => TRUE,
            );        
        
        $showcustomcomment = FALSE;
        if(!$showcustomcomment)
        {
            $form['hiddenthings']['notes_tx'] = '';
            $form['hiddenthings']['esig'] = '';
        } else {
            $form['data_entry_area1']['toppart']['notes_tx'] = array(
                '#type'          => 'textarea',
                '#title'         => t('Comments'),
                '#disabled'      => $disabled,
                '#default_value' => '',
            );

            $form['data_entry_area1']['toppart']['esig'] = array(
                '#type'          => 'textfield',
                '#title'         => t('Electronic Signature'),
                '#disabled'      => $disabled,
                '#size' => 60, 
                '#maxlength' => 128, 
                '#default_value' => '',
                '#required' => TRUE,
            );
        }
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['action_buttons']['remove'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Cancel this Imaging Order')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit with No Changes">');
        
        return $form;
    }
}

