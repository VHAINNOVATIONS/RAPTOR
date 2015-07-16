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
        module_load_include('php', 'raptor_datalayer', 'config/Choices');
        //module_load_include('php', 'raptor_datalayer', 'core/data_worklist');
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
        $mdwsDao = $this->m_oContext->getVistaDao();
        $aOneRow = $mdwsDao->getDashboardDetailsMap();
        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();    //$tid);
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
     * Cancel the tickets.
     * Returns a success message string.
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
            $mdwsDao = $oContext->getVistaDao();
            $results = $mdwsDao->cancelRadiologyOrder( 
                    $myvalues['PatientID'],
                    $orderFileIen,
                    $providerDUZ,
                    'FakeLocation',
                    $reasonCode, 
                    $cancelesig);
            $cancelled_iens = $results['cancelled_iens'];
            $failed_iens = $results['failed_iens'];
            if(is_array($failed_iens) && count($failed_iens) > 0)
            {
                error_log("WARNING in ".VISTA_SITE." because have failed to cancel these IENs: " 
                        . print_r($failed_iens,TRUE)
                        . "\n\tCanceled these: " .print_r($cancelled_iens,TRUE) );
            }
        } catch (\Exception $ex) {
            drupal_set_message('Failed cancel order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')','error');
            error_log("Failed to cancel because ".$ex->getMessage()
                    ."\nValue details..." 
                    . Context::safeArrayDump($myvalues));
            throw $ex;
        }
        
        $sNewWFS = 'IA';
        $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN, $nUID, $sNewWFS, $sCWFS, $updated_dt);

        //Write success message
        if($canreallycancel)
        {
            $cancelMsg = 'Canceled Order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .')';
            drupal_set_message($cancelMsg);
        } else {
            $cancelMsg = 'Order ' . $myvalues['tid'] . ' (' . $myvalues['procName'] .') marked for discontinuation action';
            drupal_set_message($cancelMsg, 'warn');
        }
        error_log($cancelMsg);
        return $cancelMsg;
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

        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();    //$tid);
        $mdwsDao = $this->m_oContext->getVistaDao();
        $aOneRow = $mdwsDao->getDashboardDetailsMap();        
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
        
        
        $mdwsDao = $this->m_oContext->getVistaDao();
        $myDuz = $mdwsDao->getEHRUserID();
        $myIEN = $myvalues['tid'];
        //$orderDetails = MdwsUtils::getOrderDetails($mdwsDao, $myIEN);
        $orderDetails = $mdwsDao->getOrderDetails($myIEN);
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
        //if(MdwsUserUtils::isProvider($mdwsDao, $myDuz))
        if($mdwsDao->isProvider($myDuz))
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
        //} else if(MdwsUserUtils::userHasKeyOREMAS($mdwsDao, $myDuz)) {
        } else if($mdwsDao->userHasKeyOREMAS($myDuz)) {
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
        //$aCancelOptions = MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $aCancelOptions = $mdwsDao->getRadiologyCancellationReasons();
        $form['data_entry_area1']['toppart']['reason'] = array(
            "#type" => "select",
            "#title" => t("Reason for Cancel"),
            "#options" => $aCancelOptions,
            "#description" => t("Select reason for canceling this order."),
            "#required" => TRUE,
            );        

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

