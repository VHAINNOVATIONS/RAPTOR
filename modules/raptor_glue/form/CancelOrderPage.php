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
require_once 'FormHelper.php';

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
            throw new \Exception('Missing selected ticket number!  (If using direct, try overridetid.)');
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
//        $myvalues['notes_tx'] = '';
        $myvalues['esig'] = '';
        $myvalues['providerDUZ'] = '';
        
        
        //$this->m_oContext = \raptor\Context::getInstance();
        //$myvalues['tid'] = $this->m_oContext->getSelectedTrackingID();
        $myvalues['OrderFileIen'] = $aOneRow['OrderFileIen'];
        $myvalues['PatientID'] = $aOneRow['PatientID'];

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
     * Write the values into the database.
     * Return 0 if there was an error, else 1.
     */
    function updateDatabase($form, $myvalues)
    {
        //Try to create the record now
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $orderFileIen = $myvalues['OrderFileIen'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());

        
        $is_okay = TRUE;
        $orderIEN = $nIEN;
        $reasonCode = $myvalues['reason'];
        //$cancelcomment = $myvalues['notes_tx'];
        $cancelesig = $myvalues['esig'];
        $providerDUZ = $myvalues['providerDUZ'];
        
        $canreallycancel = ($cancelesig > '');
        try
        {
            $oContext = \raptor\Context::getInstance();
            $userinfo = $oContext->getUserInfo();
            $mdwsDao = $oContext->getMdwsClient();
            $results = MdwsUtils::cancelRadiologyOrder($mdwsDao, 
                    $myvalues['PatientID'],
                    $orderFileIen,
                    $providerDUZ,
                    'FakeLocation',
                    $reasonCode, 
                    $cancelesig);
            $cancelled_iens = $results['cancelled_iens'];
            $failed_iens = $results['failed_iens'];
        } catch (\Exception $ex) {
            drupal_set_message('Failed cancel order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')','error');
            error_log("Failed to cancel because ".$ex->getMessage()
                    ."\nValue details...".print_r($myvalues,TRUE));
            throw $ex;
        }
        
        /*
        //Create the raptor_ticket_suspend_notes record now
        try
        {
            $oInsert = db_insert('raptor_ticket_suspend_notes')
                    ->fields(array(
                        'siteid' => $nSiteID,
                        'IEN' => $nIEN,
                        'notes_tx' => 'REASON:' . $myvalues['reason'] . '<br>NOTES:' . $myvalues['notes_tx'],
                        'author_uid' => $nUID,
                        'created_dt' => $updated_dt,
                    ))
                    ->execute();
        }
        catch(\Exception $e)
        {
            error_log('Failed to create raptor_ticket_suspend_notes: ' . $e . "\nDetails..." . print_r($oInsert,true));
            form_set_error('notes_tx','Failed to save notes for this ticket!');
             return 0;
        }
        */
        
        $sNewWFS = 'IA';
        $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

        //Write success message
        if($canreallycancel)
        {
            drupal_set_message('Canceled Order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')');
        } else {
            drupal_set_message('Order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .') marked for discontinuation action', 'warn');
        }
        
        return TRUE;
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

        $oWL = new \raptor\WorklistData($this->m_oContext);
        $aOneRow = $oWL->getDashboardMap();    //$tid);
        $sRequestedByName = $aOneRow['RequestedBy'];
        $canOrderBeDCd = $aOneRow['canOrderBeDCd'];
        $orderFileStatus = $aOneRow['orderFileStatus'];

        
        if(!$canOrderBeDCd)
        {
            //This user cannot cancel/replace a ticket thus cannot replace.
            $form['data_entry_area1']['userrejected'] = array('#type' => 'item'
                    , '#markup' => '<h2>This order cannot be discontinued '
                    . 'from RAPTOR because of the current VISTA order status.</h2>',
                );
            $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                    , '#markup' => '<input class="raptor-dialog-cancel" '
                        . 'type="button" value="Exit with No Changes">');
            return $form;
        }
        
        
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $myDuz = $mdwsDao->getDUZ();
        $myIEN = $myvalues['tid'];
        $orderDetails = MdwsUtils::getOrderDetails($mdwsDao, $myIEN);
        $orginalProviderDuz = $orderDetails['orderingPhysicianDuz'];

        //Hidden values
        $form['hiddenthings']['tid'] = array('#type' => 'hidden'
            , '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden'
            , '#value' => $myvalues['procName']);
        $form['hiddenthings']['OrderFileIen'] 
                = array('#type' => 'hidden', '#value' => $myvalues['OrderFileIen']);
        $form['hiddenthings']['PatientID'] = array('#type' => 'hidden'
            , '#value' => $myvalues['PatientID']);
        $form['hiddenthings']['providerDUZ'] = array('#type' => 'hidden'
            , '#value' => $orginalProviderDuz);

        $needsESIG = FALSE;
        if(MdwsUserUtils::isProvider($mdwsDao, $myDuz))
        {
            //He is a provider, can only reallycancel if created the order
            if($myDuz == $orginalProviderDuz)
            {
                $needsESIG = TRUE;
                $form['data_entry_area1']['introblurb'] = array('#type' => 'item'
                        , '#markup' => '<h2>Your account created the '
                    . 'original order and will fully cancel '
                    . 'it by providing the electronic signature.</h2>');
            }
        } else if(MdwsUserUtils::userHasKeyOREMAS($mdwsDao, $myDuz)) {
            //They can cancel with signature on file feature
            $needsESIG = TRUE;
            $form['data_entry_area1']['introblurb'] = array('#type' => 'item'
                    , '#markup' => '<h2>Your account has the priviledge '
                . 'to fully cancel using OREMAS key '
                . 'by providing the electronic signature.</h2>');
        }
        
        if(!$needsESIG)
        {
            //They cannot fully cancel
            $form['data_entry_area1']['introblurb'] = array('#type' => 'item'
                    , '#markup' => '<h2>This order may continue to show up in'
                . ' the worklist until the original order provider'
                . ' signs the discontinuation action.</h2>');
        }
        
        //Provide the normal form.
        $aCancelOptions = MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $form['data_entry_area1']['toppart']['reason'] = array(
            "#type" => "select",
            "#title" => t("Reason for Cancel"),
            "#options" => $aCancelOptions,
            "#description" => t("Select reason for canceling this order."),
            "#required" => TRUE,
            );        

        /*
        $form['data_entry_area1']['toppart']['notes_tx'] = array(
            '#type'          => 'textarea',
            '#title'         => t('Comments'),
            '#disabled'      => $disabled,
            '#default_value' => '',
        );
         */

        if(!$needsESIG)
        {
            $form['hiddenthings']['esig'] = array('#type' => 'hidden'
                , '#value' => '');  //MUST BE EMPTY
        } else {
            $form['data_entry_area1']['toppart']['esig'] = array(
                '#type'          => 'password',
                '#title'         => t('Electronic Signature'),
                '#disabled'      => $disabled,
                '#size' => 55, 
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
                , '#markup' => '<input class="raptor-dialog-cancel" '
                    . 'type="button" value="Exit with No Changes">');
        
        return $form;
    }
}

