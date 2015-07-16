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
 * Implements the suspend ticket page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class SuspendTicketPage
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
        
        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();    //$tid);
        $ehrDao = $this->m_oContext->getEhrDao();
        $aOneRow = $ehrDao->getDashboardDetailsMap();
        $nSiteID = $this->m_oContext->getSiteID();
        
        $nIEN = $tid;
        $nUID = $this->m_oContext->getUID();
        
        $myvalues = array();
        $myvalues['tid'] = $tid;
        $myvalues['procName'] = $aOneRow['Procedure'];
        
        
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
        //TODO - special checks here
        return $bGood;
    }
    
    /**
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        //Try to create the record now
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());
        
        if($myvalues['reason'] == 'Other' && (!isset($myvalues['suspend_notes_tx']) || trim($myvalues['suspend_notes_tx']) == ''))
        {
            form_set_error('suspend_notes_tx','Cannot suspend a ticket without an explanation when reason is "Other".');
            return 0;
        }

        //Create the raptor_ticket_suspend_notes record now
        try
        {
            $oInsert = db_insert('raptor_ticket_suspend_notes')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'notes_tx' => 'REASON:' . $myvalues['reason'] . '<br>NOTES:' . $myvalues['suspend_notes_tx'],
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(\Exception $e)
        {
            error_log('Failed to create raptor_ticket_suspend_notes: ' . $e . "\nDetails..." . print_r($oInsert,true));
            form_set_error('suspend_notes_tx','Failed to save notes for this ticket!');
             return 0;
        }

        $sNewWFS = 'IA';
        $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

        //Write success message
        drupal_set_message('Suspended ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        
        
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

        
        //TODO real form fields
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);

        
        $form['data_entry_area1']['toppart']['reason'] = array(
            "#type" => "select",
            "#title" => t("Reason for suspend"),
            "#options" => array(
                'Patient requested' => t('Patient requested'),
                'VA requested' => t('VA requested'),
                'Other' => t('Other'),
            ),
            "#description" => t("Select reason for suspending this ticket."),
            "#required" => TRUE,
            );        
        
        $form['data_entry_area1']['toppart']['suspend_notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Suspension Notes'),
            '#disabled'      => $disabled,
            '#default_value' => '',
        );
        
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );
        $form['data_entry_area1']['action_buttons']['remove'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array('admin-action-button'))
                , '#value' => t('Remove ticket from worklist')
                , '#disabled' => $disabled
        );
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Cancel">');
        
        return $form;
    }
}

