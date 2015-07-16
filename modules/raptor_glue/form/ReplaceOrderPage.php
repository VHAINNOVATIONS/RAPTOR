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

//module_load_include('inc', 'raptor_glue', 'functions/replace_order_ajax');
require_once 'FormHelper.php';
require_once 'ProtocolPageUtils.inc';

/**
 * Implementes the replace order page.
 *
 * @author Frank Font of SAN Business Consultants
 */
class ReplaceOrderPage extends \raptor\ASimpleFormPage
{
    private $m_oContext = NULL;
    private $m_oTT = NULL;
    //private $m_oPS = NULL;

    function __construct()
    {
        module_load_include('php', 'raptor_datalayer', 'config/Choices');
        //module_load_include('php', 'raptor_datalayer', 'core/MdwsUtils');
        //module_load_include('php', 'raptor_datalayer', 'core/MdwsUserUtils');
        //module_load_include('php', 'raptor_datalayer', 'core/MdwsNewOrderUtils');
        module_load_include('php', 'raptor_datalayer', 'core/StringUtils');
        module_load_include('php', 'raptor_formulas', 'core/LanguageInference');
        $this->m_oContext = \raptor\Context::getInstance();
        $this->m_oTT = new \raptor\TicketTrackingData();
        //$this->m_oPS = new \raptor\ProtocolSupportingData($this->m_oContext);
    }

    /**
     * Get the values to initiallypopulate the form.
     */
    function getInitialFieldValues()
    {
        $tid = $this->m_oContext->getSelectedTrackingID();
        if($tid == NULL || trim($tid) == '' || trim($tid) == 0)
        {
            throw new \Exception('Missing selected ticket number!  (If using direct, try overridetid.)');
        }
        //$oWL = new \raptor\WorklistData($this->m_oContext);
        //$aOneRow = $oWL->getDashboardMap();
        $mdwsDao = $this->m_oContext->getMdwsClient();
        $aOneRow = $mdwsDao->getDashboardDetailsMap();
        $nUID = $this->m_oContext->getUID();
        $imagetypes = $mdwsDao->getImagingTypesMap($mdwsDao);
        
        $myvalues = array();
        $myvalues['formhost'] = 'fulltab';  //If form is embedded into another form, make this different value
        $myvalues['imagetypes'] = $imagetypes;
        $myvalues['currentstep'] = 1;
        $myvalues['tid'] = $tid;
        $myvalues['uid'] = $nUID;
        $myvalues['procName'] = $aOneRow['Procedure'];
        $myvalues['OriginalRequester'] = $aOneRow['RequestedBy'];
        $myvalues['RequestingLocation'] = $aOneRow['RequestingLocation'];
        $myvalues['canOrderBeDCd'] = $aOneRow['canOrderBeDCd'];
        $myvalues['originalOrderProviderDuz'] = $aOneRow['orderingPhysicianDuz'];
        $myvalues['OrderFileIen'] = $aOneRow['OrderFileIen'];
        $myvalues['PatientID'] = $aOneRow['PatientID'];
        $myvalues['orderitems_options'] = NULL;
        $myvalues['canCreateNewOrder'] = NULL;
        $myvalues['canReallyCancel'] = NULL;
        $myvalues['requestingProviderDuz'] = NULL;  //Who is requesting the new order
        $myvalues['neworderprovider_name'] = NULL;
        $myvalues['newordermodifiers'] = NULL;
        $myvalues['reason'] = '';
        $myvalues['notes_tx'] = '';
        $myvalues['esig'] = '';
        $myvalues['require_esig'] = '';
        $myvalues['cancommitorder'] = NULL; //If 'yes' then user can commit a new order
        $myvalues['cancelreason'] = NULL;
        $myvalues['orderCheckOverrideReason'] = NULL;
        $myvalues['neworderlocation'] = NULL;
        $myvalues['neworderimagetype'] = NULL;
        $myvalues['neworderlocation'] = NULL;
        $myvalues['neworderitem'] = NULL;
        $myvalues['contractSharingIen'] = NULL;
        $myvalues['neworderurgency'] = NULL;
        $myvalues['modecode'] = NULL;
        $myvalues['category'] = NULL;
        $myvalues['submitto'] = NULL;
        $myvalues['isolation'] = 3; //Default to UNKNOWN
        $myvalues['pregnant'] = NULL;
        $myvalues['reasonforstudy'] = NULL;
        $myvalues['clinhist'] = NULL;
        
        $ddtxt = $aOneRow['DesiredDate'];
        $ddtxtparts = explode('@',$ddtxt);
        $ddtime = strtotime($ddtxtparts[0]);
        if($ddtime > time())
        {
            $formatteddd = date('m/d/Y',$ddtime);
        } else {
            $formatteddd = '';  //Already passed!
        }
        
        $myvalues['datedesired_dateonly'] = $formatteddd;   //Format it
        $myvalues['preopdate_dateonly'] = NULL;
        $myvalues['datedesired_timeonly'] = NULL;
        $myvalues['preopdate_timeonly'] = NULL;

        //error_log("Got initial field values for replace order DD=[".$aOneRow['DesiredDate']."]>>>".print_r($myvalues,TRUE));
        
        return $myvalues;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValidFromFormState($form, $form_state)
    {
        $myvalues = $form_state['values'];
        $goodtrack = array();
        $currentstep = $this->getSubmittedStepNumber($form_state);

        if($currentstep == 1)
        {
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'cancelreason', 'Replacement Reason');
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'neworderimagetype', 'Image Type');
            $goodtrack[] = FormHelper::validate_text_field_not_empty($myvalues, 'neworderprovider_name', 'New Order Provider Name');
        } else
        if($currentstep == 2)
        {
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'neworderlocation', 'Location');
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'neworderitem', 'Order Item');
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'neworderurgency', 'Urgency');
            
            $goodtrack[] = FormHelper::validate_text_field_not_empty($myvalues, 'modecode', 'Transport');
            $goodtrack[] = FormHelper::validate_text_field_not_empty($myvalues, 'category', 'Category');

            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'submitto', 'Submit To');
            
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'isolation', 'Isolation');
            $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'pregnant', 'Pregnant');  
            
            $goodtrack[] = FormHelper::validate_text_field_not_empty($myvalues, 'reasonforstudy', 'Reason for Study');
            
            $goodtrack[] = FormHelper::validate_date_field_not_empty($myvalues, 'datedesired_dateonly', 'Desired Date');
            $goodtrack[] = FormHelper::validate_time_field_not_empty($myvalues, 'datedesired_timeonly', 'Desired Time');
            
            //$goodtrack[] = FormHelper::validate_date_field_not_empty($myvalues, 'preopdate_dateonly', 'Preop Date');
            //$goodtrack[] = FormHelper::validate_time_field_not_empty($myvalues, 'preopdate_timeonly', 'Preop Time');
            
            //Make sure we know what kind of modality this order applies to.
            $language_infer = new \raptor_formulas\LanguageInference();
            $imagetype_map = $myvalues['imagetypes'];
            $it_key = $myvalues['neworderimagetype'];
            $imagetype_txt = $imagetype_map[$it_key];
            $modality = $language_infer->inferModalityFromPhrase($imagetype_txt);
            if($modality == NULL)
            {
                $oi_options = $myvalues['orderitems_options'];
                $oi_key = $myvalues['neworderitem'];
                $oi_txt = $oi_options[$oi_key];
                $modality = $language_infer->inferModalityFromPhrase($oi_txt);
            }
            if($modality == NULL)
            {
                //Just highlite the selected order.
                $modalityprefixes = trim($language_infer->getSupportedModalityCodes());
                form_set_error('neworderitem'
                        ,'Cannot determine a RAPTOR supported modality from the order text.'
                        . '  (Prefer text with one of the following prefixes: '.$modalityprefixes.')');
                $goodtrack[] = FALSE;
            }
            
        } else
        if($currentstep == 3)
        {
            $otherlabel = NULL;
            if($myvalues['category'] == 'C')
            {
                $otherlabel = "Contract Source";
            } else
            if($myvalues['category'] == 'S')
            {
                $otherlabel = "Sharing Source";
            } else
            if($myvalues['category'] == 'R')
            {
                $otherlabel = "Research Source";
            }
            if($otherlabel != NULL)
            {
                $goodtrack[] = FormHelper::validate_number_field_not_empty($myvalues, 'contractSharingIen', $otherlabel);
            }
            if($myvalues['require_esig'] == 'yes')
            {
                //They MUST provide an esig!.
                $goodtrack[] = FormHelper::validate_text_field_not_empty($myvalues, 'esig', 'Electronic Signature');
            }
        }

        //Check for trouble
        foreach($goodtrack as $value)
        {
            if($value === FALSE)
            {
                //There was trouble.
                return FALSE;
            }
        }
        
        //There was no trouble, yay!
        return TRUE;
    }
    
    /**
     * Write the values into the database.
     * Throws an exception if it fails.
     */
    function updateDatabaseFromFormState($form, &$form_state)
    {
        $myvalues = $form_state['values'];
        $mdwsDao = $this->m_oContext->getMdwsClient();
        
        $canCreateNewOrder = $myvalues['canCreateNewOrder'];
        $canReallyCancel = $myvalues['canReallyCancel'];
        $canOrderBeDCd = $myvalues['canOrderBeDCd'];
        $myDuz = $mdwsDao->getDUZ();
        //$isPROVIDER = MdwsUserUtils::isProvider($mdwsDao, $myDuz);
        $isPROVIDER = $mdwsDao->isProvider($myDuz);
        //$hasOREMAS = MdwsUserUtils::userHasKeyOREMAS($mdwsDao, $myDuz);
        $hasOREMAS = $mdwsDao->userHasKeyOREMAS($myDuz);
        if($isPROVIDER)
        {
            //Provider overrides OREMAS
            $canUseOREMASFeature = FALSE;
        } else {
            $canUseOREMASFeature = $hasOREMAS;
        }
        
        //Get key information now for replacement logic.
        $userinfo = $this->m_oContext->getUserInfo();
        $nSiteID = $this->m_oContext->getSiteID();
        $nIEN = $myvalues['tid'];
        $orderFileIen = $myvalues['OrderFileIen'];
        $nUID = $this->m_oContext->getUID();
        $sCWFS = $this->m_oTT->getTicketWorkflowState($nSiteID . '-' . $nIEN);
        $updated_dt = date("Y-m-d H:i:s", time());
        
        $reasonCode = $myvalues['cancelreason'];
        $user_esig = $myvalues['esig'];
        
        $orderChecks = $form_state['orderchecks_result'];
        
        $fulloriginalTID = $nSiteID."-".$myvalues['tid'];
        
        $args = array();
        $neworder = array();
        try
        {
            $args['patientId'] = $myvalues['PatientID'];
            $args['locationIEN'] = $myvalues['neworderlocation'];
            $args['imagingTypeId'] = $myvalues['neworderimagetype'];
            $args['orderableItemId'] = $myvalues['neworderitem'];
            $args['urgencyCode'] = $myvalues['neworderurgency'];
            $args['modeCode'] = $myvalues['modecode'];
            $args['classCode'] = $myvalues['category'];
            $args['submitTo'] = $myvalues['submitto'];
            $args['pregnant'] = $myvalues['pregnant'];
            $args['isolation'] = $myvalues['isolation'];
            $args['reasonForStudy'] = $myvalues['reasonforstudy'];
            $args['contractSharingIen'] = $myvalues['contractSharingIen'];
            $args['requestingProviderDuz'] = $myvalues['requestingProviderDuz'];
            $args['orderCheckOverrideReason'] = $myvalues['orderCheckOverrideReason'];
            
            //Add the order history into clinical history
            $or = $myvalues['OriginalRequester'];
            $ol = $myvalues['RequestingLocation'];
            $phrase = "Original order $fulloriginalTID was requested by ".$or." at ".$ol.".";
            $orpos = strpos($myvalues['clinhist'],$phrase);
            if($orpos === FALSE)
            {
                $myvalues['clinhist'] .= "\n".$phrase;
            }
            $clinhistArray = explode("\n",$myvalues['clinhist']);
            $args['clinicalHx'] = $clinhistArray;
            
            $datetimedesired = $myvalues['datedesired_dateonly'].' '.$myvalues['datedesired_timeonly'];
            $args['startDateTime'] = strtotime($datetimedesired);
            $preopdatetime = $myvalues['preopdate_dateonly'].' '.$myvalues['preopdate_timeonly'];
            $args['preOpDateTime'] = strtotime($preopdatetime);
            if(!isset($myvalues['newordermodifiers']))
            {
                $args['modifierIds'] = array();
            } else {
                $mids = array();
                foreach($myvalues['newordermodifiers'] as $k=>$v)
                {
                    if($k==$v)
                    {
                        if($v == 'NONE')
                        {
                            //We just want an empty array then.
                            $mids = array();
                            break;
                        }
                        $mids[] = $k;   //The value is the key
                    }
                }
                $args['modifierIds'] = $mids;
            }
            $args['eSig'] = $myvalues['esig'];
            if($myvalues['cancommitorder'] == 'yes')
            {
                if($canUseOREMASFeature)
                {
                    //The new order will still keep the original provider
                    $args['requestingProviderDuz'] = $myvalues['originalOrderProviderDuz'];
                } else {
                    //We will be the provider on the new order
                    $args['requestingProviderDuz'] = $myDuz;
                }
                //$neworder = MdwsNewOrderUtils::createNewRadiologyOrder($mdwsDao, $orderChecks, $args);
                $neworder = $mdwsDao->createNewRadiologyOrder($orderChecks, $args);
            } else {
                //The other user will need to log into VISTA to sign it
                $args['requestingProviderDuz'] = $myvalues['originalOrderProviderDuz'];
                //$neworder = MdwsNewOrderUtils::createUnsignedRadiologyOrder($mdwsDao, $orderChecks, $args);
                $neworder = $mdwsDao->createUnsignedRadiologyOrder($orderChecks, $args);
            }
            
            $neworder['replaced_tid'] = $myvalues['tid'];
            $form_state['finalstep_result'] = $neworder;

        } catch (\Exception $ex) {
            $msg = 'Failed to create replacement of '.$nIEN
                    .' (orderFileIen='.$orderFileIen.') as user with '
                    . 'DUZ='.$myDuz
                    .' because '.$ex->getMessage();
            drupal_set_message($msg,'error');
            error_log($msg . "\nData Details..."
                    .Context::safeArrayDump($myvalues)
                    ."\nargs="
                    .print_r($args,TRUE)
                    );
            throw $ex;
        }

        //Make sure we really got a new record before we continue.
        if(!isset($neworder['radiologyOrderId']) || empty($neworder['radiologyOrderId']))
        {
            $msg = 'Failed to create replacement of '.$nIEN
                    .' (orderFileIen='.$orderFileIen.') as user with '
                    . 'DUZ='.$myDuz
                    .' because NO radiologyOrderId was returned! '
                    . 'WARNING the new VISTA record MAY have been created BUT the RAPTOR details of the original ticket WILL NOT have been copied into the new ticket!!!';
            drupal_set_message($msg,'error');
            error_log($msg . "\nData Details..."
                    .print_r($myvalues,TRUE)
                    ."\nargs="
                    .print_r($args,TRUE)
                    );
            throw new \Exception($msg);
        }
        
        //Now copy the details to the new order.
        try
        {
            $sourceTID = $nSiteID.'-'.$nIEN;
            $targetTID = $nSiteID.'-'.$neworder['radiologyOrderId'];
            ProtocolPageUtils::copyProtocolDetailsToNewOrder($sourceTID, $targetTID);
        } catch (\Exception $ex) {
            $msg = 'Failed to copy details from '.$nIEN.' (orderFileIen='.$orderFileIen.') '
                    .' on replace because '.$ex->getMessage();
            drupal_set_message($msg,'error');
            error_log($msg . "\nData Details..." 
                    . Context::safeArrayDump($myvalues));
            throw $ex;
        }

        if($canOrderBeDCd)
        {
            //Now cancel the old order.
            try
            {
                if($canReallyCancel)
                {
                    $cancel_esig = $user_esig;
                } else {
                    $cancel_esig = NULL;  //Causes action request but no signature in VISTA
                }
                $cancelLocation = $myvalues['neworderlocation'];
                $cancelDUZ = $myvalues['originalOrderProviderDuz'];    //Always the DUZ from the order being canceled
                $results = $mdwsDao->cancelRadiologyOrder( 
                        $myvalues['PatientID'],
                        $orderFileIen,
                        $cancelDUZ,
                        $cancelLocation,
                        $reasonCode, 
                        $cancel_esig);
                $cancelled_iens = $results['cancelled_iens'];
                $failed_iens = $results['failed_iens'];
            } catch (\Exception $ex) {
                $msg = 'Failed to cancel '.$nIEN.' (orderFileIen='.$orderFileIen.') '
                        .' on replace because '.$ex->getMessage();
                drupal_set_message($msg,'error');
                error_log($msg . "\nData Details..." 
                        . Context::safeArrayDump($myvalues));
                throw $ex;
            }

            //If we made it here, go ahead and mark the order as inactive.
            $sNewWFS = 'IA';
            $this->m_oTT->setTicketWorkflowState($nSiteID . '-' . $nIEN
                    , $nUID, $sNewWFS, $sCWFS, $updated_dt);
        } else {
            //Give some feedback to the user.
            $msg = "RAPTOR created a new order BUT could not cancel existing order ".$nSiteID . '-' . $nIEN . " due to current VistA status of the existing order";
            error_log($msg);
            drupal_set_message($msg, 'warn');
        }

        //Write success message
        if($myvalues['cancommitorder'] == 'yes')
        {
            drupal_set_message('Replaced Order ' . $myvalues['tid'] 
                    . ' (' . $myvalues['procName'] .') with '
                    .$neworder['radiologyOrderId']);
        } else {
            drupal_set_message('Replaced Order ' . $myvalues['tid'] 
                    . ' (' . $myvalues['procName'] .') with UNSIGNED ORDER '
                    .$neworder['radiologyOrderId']);
            drupal_set_message('The new '.$neworder['radiologyOrderId']
                    .' order will not be active until signed by '
                    .$myvalues['requestingProviderDuz']
                    ,'warn');
        }
        return TRUE;
    }
    
    public static function getStepCount()
    {
        return 3;
    }

    /**
     * Return a code as follows...
     * b = navigate back
     * n = navigate to next
     * f = finish processing
     * NULL = no workflow change
     */
    public static function getWorkflowAction($form_state)
    {
        if(isset($form_state['values']))
        {
            $myvalues = $form_state['values'];
        } else {
            $myvalues = array();
        }
        if(isset($myvalues['navigationoverride']) && $myvalues['navigationoverride'] > '')
        {
            //Allow workflow action to be overridden by value in the field
            $clickedvalue = strtolower($myvalues['navigationoverride']);
            $clickednext = strpos($clickedvalue,'next') !== FALSE;
            $clickedback = strpos($clickedvalue,'back') !== FALSE;
            $clickedfinish = strpos($clickedvalue,'finish') !== FALSE;
        } else {
            //Workflow action is interpretted from the button clicked
            if(isset($form_state['clicked_button']))
            {
                $clickedbutton = $form_state['clicked_button'];
                $clickedvalue = strtolower($clickedbutton['#value']);
                $clickednext = strpos($clickedvalue,'next') !== FALSE;
                $clickedback = strpos($clickedvalue,'back') !== FALSE;
                $clickedfinish = strpos($clickedvalue,'finish') !== FALSE;
            } else {
                $clickedbutton = NULL;
                $clickedvalue = NULL;
                $clickednext = FALSE;
                $clickedback = FALSE;
                $clickedfinish = FALSE;
            }
        }
        if($clickedback)
        {
            return 'b';
        } else
        if($clickednext)
        {
            return 'n';
        } else
        if($clickedfinish)
        {
            return 'f';
        }
        return NULL;
    }
    
    /**
     * Just tell us what step was submitted, not what step we are now on.
     */
    public function getSubmittedStepNumber($form_state)
    {
        if(isset($form_state['values'])) 
        {
            $currentstep = $form_state['step'];
        } else {
            $currentstep = 0;
        }    
        return $currentstep;
    }
    
    private function getNonEmptyValueFromArrayElseAlternateArray($a1, $k1, $a2, $k2)
    {
        return empty($a1[$k1]) ? $a2[$k2] : $a1[$k1];
    }
    
    private function getNonEmptyValueFromArrayElseAlternateLiteral($a1, $k1, $literal)
    {
        return empty($a1[$k1]) ? $literal : $a1[$k1];
    }
    
    /**
     * Get the markup of the form
     */
    public function getForm($form, &$form_state, $disabled, $myvalues_override)
    {
        if($myvalues_override != NULL)
        {
            $myvalues = $myvalues_override;
        } else {
            $myvalues = $form_state['values'];
        }
        
        $actioncode = $this->getWorkflowAction($form_state);
        $clickednext = $actioncode == 'n';
        $clickedback = $actioncode == 'b';
        $clickedfinish = $actioncode == 'f';

        $diagnosesteps = 'init';
        $formhost = $myvalues['formhost'];
        if(isset($form_state['values'])) 
        {
            if($clickednext || $clickedfinish)
            {
                $move = 1;
            } else if($clickedback) {
                $move = -1;
            } else {
                $move = 0;
            }
            $submittedstep = $this->getSubmittedStepNumber($form_state);
            if($submittedstep < 1)
            {
                //Odd situation might happen on error condition last submit
                //Try the current step from myvalues.
                $submittedstep = $myvalues['currentstep'];
                error_log("WARNING did not find a 'step' value in form_state!"
                        . "  Defaulting to myvalues currentstep instead (" 
                        . $myvalues['currentstep'] .")" );
            }
            $currentstep = $submittedstep + $move;
            $diagnosesteps = "$currentstep = $submittedstep + $move";
        } else {
            //When no values were already posted then 
            //we can be sure we are step one
            $currentstep = 1;
            $diagnosesteps = "hardcoded at $currentstep";
        }    
        if($currentstep < 1)
        {
            if($formhost == 'fulltab')
            {
                //This is an error
                error_log("ERROR TROUBLE did not find a valid 'step' value!"
                        . "\n...Navigation action=[$actioncode]"
                        . "\n...Form host=[$formhost]"
                        . "\n...Diagnostic tip=[$diagnosesteps]");
                throw new \Exception('Cannot have a step = '.$currentstep);
            } else {
                //Continue with warning assuming this was embedded dialog issue
                error_log("WARNING TROUBLE did not find a valid 'step' value!"
                        . "\n...Navigation action=[$actioncode]"
                        . "\n...Form host=[$formhost]"
                        . "\n...Diagnostic tip=[$diagnosesteps]");
                if($currentstep == 0)
                {
                    $currentstep = $this->getStepCount()-1;   //Back from last
                } else {
                    $currentstep = $this->getStepCount();     //Repeat last step
                }
            }
        } else if($currentstep > $this->getStepCount()){
            //We can be here if the submit on the final step 
            //failed and we are repeating the last step after submit
            $currentstep = $this->getStepCount();   //Repeat last step
        }
        $form_state['step'] = $currentstep;
        
        //Set flags to adjust UI for steps already been completed
        $disabled_step1 = $disabled || ($currentstep > 1);
        $disabled_step2 = $disabled || ($currentstep > 2);

        $mdwsDao = $this->m_oContext->getMdwsClient();
        $myIEN = $myvalues['tid'];
        
        //$oDD = new \raptor\DashboardData($this->m_oContext);
        //$rpd = $oDD->getDashboardDetails();
        $rpd = $mdwsDao->getDashboardDetailsMap();
        $gender = trim($rpd['PatientGender']);
        $age = intval(trim($rpd['PatientAge']));
        $isMale = $gender > '' && strtoupper(substr($gender,0,1)) == 'M';
        if(!$isMale)
        {
            $isFemale = $gender > '' && strtoupper(substr($gender,0,1)) == 'F';
        } else {
            $isFemale = FALSE;
        }
        if($isFemale && $age > 59 && !is_int($myvalues['pregnant']))
        {
            //Assume default not pregnant for anyone too old to bear children.
            $myvalues['pregnant'] = 2;
        }

        //$orderDetails = MdwsUtils::getOrderDetails($mdwsDao, $myIEN);
        $orginalProviderDuz = $myvalues['originalOrderProviderDuz'];
        $canOrderBeDCd = $myvalues['canOrderBeDCd'];
        $imagetypes = $myvalues['imagetypes'];

        $myDuz = $mdwsDao->getDUZ();
        //$isPROVIDER = MdwsUserUtils::isProvider($mdwsDao, $myDuz);
        $isPROVIDER = $mdwsDao->isProvider($myDuz);
        //$hasOREMAS = MdwsUserUtils::userHasKeyOREMAS($mdwsDao, $myDuz);
        $hasOREMAS = $mdwsDao->userHasKeyOREMAS($myDuz);
        if($isPROVIDER)
        {
            //Provider overrides OREMAS
            $canUseOREMASFeature = FALSE;
        } else {
            $canUseOREMASFeature = $hasOREMAS;
        }
        
        if(!$hasOREMAS && !$isPROVIDER)
        {
            //This user cannot cancel/replace a ticket thus cannot replace.
            $form['data_entry_area1']['userrejected'] = array('#type' => 'item'
                    , '#markup' => '<h2>Your account does not have sufficient VISTA'
                . ' privilege to replace CPRS orders</h2>'
                . '</p>Relevant VISTA keys are the following...</p>'
                . '<ul>'
                . '<li>PROVIDER key allows an account to create orders'
                . '<li>OREMAS key allows an account to use Signature on File feature'
                . '</ul>'
                . '</p>Your account has neither of these keys.</p>'
                );
            $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit with No Changes">');
            return $form;
        }
        
        //Set important flags now
        $canCreateNewOrder = TRUE;  //If we are here, then the user can create a new order.
        $needsESIG = TRUE;          //If they are here, we will need a signature
        $canReallyCancel = FALSE;   //Assume they cannot really cancel
        if($isPROVIDER)
        {
            //He is a provider, can only reallycancel if created the order
            if($myDuz == $orginalProviderDuz)
            {
                $canReallyCancel = TRUE;
            }
        } else if($canUseOREMASFeature) {
            //They can cancel with signature on file feature
            $canReallyCancel = TRUE;
        }
        
        //Make sure these values are always preserved
        $form['hiddenthings']['canCreateNewOrder'] = array('#type' => 'hidden', '#value' => $canCreateNewOrder);
        $form['hiddenthings']['canReallyCancel'] = array('#type' => 'hidden', '#value' => $canReallyCancel);
        $form['hiddenthings']['originalOrderProviderDuz'] 
                        = array('#type' => 'hidden'
                            , '#value' => $orginalProviderDuz);
        $form['hiddenthings']['canOrderBeDCd'] = array('#type' => 'hidden', '#value' => $canOrderBeDCd);
        $form['hiddenthings']['imagetypes'] = array('#type' => 'hidden', '#value' => $imagetypes);
        $form['hiddenthings']['uid'] = array('#type' => 'hidden', '#value' => $myvalues['uid']);
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);

        if(!$canOrderBeDCd)
        {
            if($canUseOREMASFeature)
            {
                $introblurb = '<p>This order cannot be discontinued because of current VISTA status but a new '
                        . 'order will be created using the signature on file feature.</p>';
            } else {
                if($canReallyCancel)
                {
                    $introblurb = '<p>This order cannot be discontinued because of current VISTA status but a new '
                        . 'order will be created using <strong>your name</strong> as the provider.</p>';
                } else {
                    $introblurb = '<p>This order cannot be marked for discontinuation because of current VISTA status.'
                            . '  A new order will be created using <strong>your name</strong> as the provider.</p>';
                }
            }
        } else {
            if($canUseOREMASFeature)
            {
                $introblurb = '<p>This order will be canceled and a new '
                        . 'order will be created using the signature on file feature.</p>';
            } else {
                if($canReallyCancel)
                {
                    $introblurb = '<p>This order will be canceled and a new '
                        . 'order will be created using <strong>your name</strong> as the provider.</p>';
                } else {
                    $introblurb = '<p>This order will be marked for discontinuation in VISTA but will not be canceled'
                            . ' until the original order provider signs the discontinuation action.  It may continue to show up'
                            . ' in the RAPTOR worklist until the original order provider signs it.'
                            . '  A new order will be created using <strong>your name</strong> as the provider.</p>';
                }
            }
        }
        
        //Hidden values in the form
        $form['hiddenthings']['navigationoverride'] = array(
            '#type'       => 'hidden',
            '#attributes' =>array('id'=> 'replaceorderstep'),
            '#default_value' => '',
        );
        $form['hiddenthings']['formhost'] = array(
            '#type'       => 'hidden',
            '#attributes' =>array('id'=> 'formhost'),
            '#default_value' => $myvalues['formhost'],
        );
        $form['hiddenthings']['tid'] = array('#type' => 'hidden', '#value' => $myvalues['tid']);
        $form['hiddenthings']['procName'] = array('#type' => 'hidden', '#value' => $myvalues['procName']);
        $form['hiddenthings']['OrderFileIen'] = array('#type' => 'hidden', '#value' => $myvalues['OrderFileIen']);
        $form['hiddenthings']['PatientID'] = array('#type' => 'hidden', '#value' => $myvalues['PatientID']);
        $form['hiddenthings']['currentstep'] = array('#type' => 'hidden', '#value' => $currentstep);
        $form['hiddenthings']['OriginalRequester'] = array('#type' => 'hidden', '#value' => $myvalues['OriginalRequester']);
        $form['hiddenthings']['RequestingLocation'] = array('#type' => 'hidden', '#value' => $myvalues['RequestingLocation']);

        if($currentstep != 1)
        {
            $form['hiddenthings']['cancelreason'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['cancelreason']);
            if($myvalues['neworderimagetype'] != NULL)
            {
                $form['hiddenthings']['neworderimagetype'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['neworderimagetype']);
            }
            if($myvalues['neworderprovider_name'] != NULL)
            {
                $form['hiddenthings']['neworderprovider_name'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['neworderprovider_name']);
            }
        }
        if($currentstep != 2)
        {
            if($myvalues['newordermodifiers'] != NULL)
            {
                $form['hiddenthings']['newordermodifiers'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['newordermodifiers']);
            }
            if($myvalues['neworderlocation'] != NULL)
            {
                $form['hiddenthings']['neworderlocation'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['neworderlocation']);
            }
            if($myvalues['neworderitem'] != NULL)
            {
                $form['hiddenthings']['neworderitem'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['neworderitem']);
            }
            if($myvalues['neworderurgency'] != NULL)
            {
                $form['hiddenthings']['neworderurgency'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['neworderurgency']);
            }
            if($myvalues['modecode'] != NULL)
            {
                $form['hiddenthings']['modecode'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['modecode']);
            }
            if($myvalues['category'] != NULL)
            {
                $form['hiddenthings']['category'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['category']);
            }
            if($myvalues['submitto'] != NULL)
            {
                $form['hiddenthings']['submitto'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['submitto']);
            }
            $form['hiddenthings']['isolation'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['isolation']);
            $form['hiddenthings']['pregnant'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['pregnant']);
            if($myvalues['reasonforstudy'] != NULL)
            {
                $form['hiddenthings']['reasonforstudy'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['reasonforstudy']);
            }
            if($myvalues['clinhist'] != NULL)
            {
                $form['hiddenthings']['clinhist'] 
                        = array('#type' => 'hidden', '#value' => $myvalues['clinhist']);
            }
            $form['hiddenthings']['datedesired_dateonly'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['datedesired_dateonly']);
            $form['hiddenthings']['preopdate_dateonly'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['preopdate_dateonly']);
            $form['hiddenthings']['datedesired_timeonly'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['datedesired_timeonly']);
            $form['hiddenthings']['preopdate_timeonly'] 
                    = array('#type' => 'hidden', '#value' => $myvalues['preopdate_timeonly']);
            if($myvalues['requestingProviderDuz'] != NULL)
            {
                $form['hiddenthings']['requestingProviderDuz'] 
                        = array('#type' => 'hidden'
                            , '#value' => $myvalues['requestingProviderDuz']);
            }
        }
            
        if($canUseOREMASFeature)
        {
            $mytitle = "Step $currentstep of {$this->getStepCount()} (Signature on file)";
        } else {
            $mytitle = "Step $currentstep of {$this->getStepCount()} (No signature on file)";
        }
        $form['data_entry_area1'] = array(
            '#prefix' => "\n<section class='multistep-dataentry'>"
            . "<H2>$mytitle</H2>\n$introblurb\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        //$aCancelOptions = MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $aCancelOptions = $mdwsDao->getRadiologyCancellationReasons();
        
        $form['data_entry_area1']['toppart']['cancelreason'] = array(
            "#type" => "select",
            "#title" => t('Replacement Reason'),
            "#options" => $aCancelOptions,
            "#description" => t("Select reason for canceling the existing order."),
            "#default_value" => $myvalues['cancelreason'],
            "#disabled" => $disabled_step1,
            '#required' => TRUE,
            );        
        
        //$imagetypes = MdwsNewOrderUtils::getImagingTypes($mdwsDao);
        $neworderimagetype = FormHelper::getKeyOfValue($imagetypes, $rpd['ImageType']);
        if($neworderimagetype === FALSE)
        {
            $neworderimagetype = NULL;
        }
        $neworderimagetype = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'neworderimagetype', $neworderimagetype);
        $form['data_entry_area1']['toppart']['neworderimagetype'] = array(
            "#type" => "select",
            "#title" => t('Imaging Type'),
            "#options" => $imagetypes,
            "#description" => t("Select Imaging Type for the new order."),
            "#default_value" => $neworderimagetype,
            "#disabled" => $disabled_step1,
            '#required' => TRUE,
            );

        $neworderprovider_name = $myvalues['OriginalRequester'];
        if(isset($myvalues['neworderprovider_name']))
        {
            $neworderprovider_name = $myvalues['neworderprovider_name'];
        }
        if($isPROVIDER)
        {
            $mytitle = FormHelper::getTitleAsRequiredField('New Order Provider Name');
            $mydesc = t('You will be the provider on the replacement order because your account has the VISTA PROVIDER key');
            $mydisable = $disabled_step1;
            $neworderprovider_name = 'Self'; //A provider can only select themselves as the provider.
            $mydisable = TRUE;
        } else {
            $mytitle = t('New Order Provider Name');
            $mydesc = t('Signature on file for this provider');
            $mydisable = TRUE;
        }
        $form['data_entry_area1']['toppart']['WB2']['neworderprovider_name'] = array(
            '#type'          => 'textfield',
            '#title'         => $mytitle,
            '#size' => 30, 
            '#maxlength' => 30, 
            "#description" => $mydesc,
            "#default_value" => $neworderprovider_name,
            "#disabled" => $mydisable,
        );
        
        if($currentstep > 1)
        {
            //Select the new order requester
            //$neworderproviders = MdwsUserUtils::getProviders($mdwsDao, $neworderprovider_name);
            $neworderproviders = $mdwsDao->getProviders($mdwsDao, $neworderprovider_name);
            $requestingProviderDuz = $this->
                    getNonEmptyValueFromArrayElseAlternateLiteral($myvalues
                            , 'requestingProviderDuz', $neworderproviders);
            if($isPROVIDER)
            {
                //They must be the provider for the new order
                $form['hiddenthings']['requestingProviderDuz'] 
                        = array('#type' => 'hidden'
                            , '#value' => $myDuz);
            } else {
                //They cannot change the provider!
                $form['hiddenthings']['requestingProviderDuz'] 
                        = array('#type' => 'hidden'
                            , '#value' => $orginalProviderDuz);   //$neworderprovider_duz);
            }
            
            //Important NOT to mark fields as #required else BACK will fail!!!
            $imagingTypeId = intval($myvalues['neworderimagetype']);
            //$locations = $this->m_oPS->getAllHospitalLocations($mdwsDao);
            $locations = $this->m_oContext->getMdwsClient()->getAllHospitalLocationsMap();
            $neworderlocation = FormHelper::getKeyOfValue($locations, $rpd['PatientLocation']);
            if($neworderlocation === FALSE)
            {
                $neworderlocation = NULL;
            }
            $neworderlocation = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues
                    , 'neworderlocation', $neworderlocation);
            $form['data_entry_area1']['toppart']['neworderlocation'] = array(
                '#prefix' => '<div id="dropdown-orderlocations-replace">',
                '#suffix' => '</div>',
                '#type' => 'select',
                '#empty_option'=>t('- Select -'),
                '#title' => FormHelper::getTitleAsRequiredField('Ordering Location'),
                '#options' => $locations, 
                '#description' => t('Select location'),
                '#default_value' => $neworderlocation,
                '#disabled' => $disabled_step2,
                );        
            //error_log("LOOK ALL THE LOCATIONS OPTIONS>>>>".print_r($locations,TRUE));                
            //$raw_orderitems = MdwsNewOrderUtils::getOrderableItems($mdwsDao, $imagingTypeId);
            $raw_orderitems = $mdwsDao->getOrderableItems($imagingTypeId);
            $orderitems_options = array();
            foreach($raw_orderitems as $k=>$v)
            {
                $orderitems_options[$k] = $v['name'];
            }
            $neworderitem = FormHelper::getKeyOfValue($orderitems_options, $rpd['Procedure']);
            if($neworderitem === FALSE)
            {
                $neworderitem = NULL;
            }
            $neworderitem = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'neworderitem', $neworderitem);
            $form['data_entry_area1']['toppart']['neworderitem'] = array(
                "#type" => "select",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsRequiredField("Orderable Item"),
                "#options" => $orderitems_options,
                "#description" => t("Select orderable item (imaging procedure)"),
                "#default_value" => $neworderitem,
                "#disabled" => $disabled_step2,
                );  
            //Store the map so we can get it later
            $form['hiddenthings']['orderitems_options'] 
                    = array('#type' => 'hidden', '#value' => $orderitems_options);
            
            $patientId = $myvalues['PatientID'];
            //$raworderoptions = MdwsNewOrderUtils::getRadiologyOrderDialog($mdwsDao, $imagingTypeId, $patientId);
            $raworderoptions = $mdwsDao->getRadiologyOrderDialog($imagingTypeId, $patientId);

            $raw_modifiers = $raworderoptions['modifiers'];
            if(!is_array($raw_modifiers) || count($raw_modifiers) < 1)
            {
                $form['data_entry_area1']['toppart']['newordermodifiers'] = array(
                    "#type" => "checkboxes",
                    "#title" => t("Modifiers"),
                    "#options" => array('NONE'=>'None'),
                    "#default_value" => array('NONE'=>'NONE'),
                    "#disabled" => TRUE,
                    );        
            } else {
                $options_modifiers = array();
                foreach($raw_modifiers as $k=>$v)
                {
                    $options_modifiers[$k] = $v;
                }
                if(isset($myvalues['newordermodifiers']))
                {
                    $defaultmods = $myvalues['newordermodifiers'];
                } else {
                    $defaultmods = array();
                }
                $form['data_entry_area1']['toppart']['newordermodifiers'] = array(
                    "#type" => "checkboxes",
                    "#title" => t("Modifiers"),
                    "#options" => $options_modifiers,
                    "#default_value" => $defaultmods,
                    "#disabled" => $disabled_step2,
                    );        
            }

            $form['data_entry_area1']['toppart']['WB1'] = array(
                '#prefix' => "\n<div id='wb1' style='min-height: 150px;'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            $form['data_entry_area1']['toppart']['WB1']['LB1'] = array(
                '#prefix' => "\n<div id='lb1' style='float:left; width: 250px; padding: 0px 0px 0px 0px'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            $urgencies = $raworderoptions['urgencies'];
            $neworderurgency = FormHelper::getKeyOfValue($urgencies, $rpd['Urgency']);
            if($neworderurgency === FALSE)
            {
                $neworderurgency = NULL;
            }
            $neworderurgency = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'neworderurgency', $neworderurgency);
            $form['data_entry_area1']['toppart']['WB1']['LB1']['neworderurgency'] = array(
                "#type" => "select",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsRequiredField('Urgency'),
                "#options" => $urgencies,
                "#description" => t('Select urgency'),
                "#default_value" => $neworderurgency,
                "#disabled" => $disabled_step2,
                );        
            
            $transports = $raworderoptions['transports'];
            $modecode = FormHelper::getKeyOfValue($transports, $rpd['Transport']);
            if($modecode === FALSE)
            {
                $modecode = NULL;
            }
            $modecode = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'modecode', $modecode);
            $form['data_entry_area1']['toppart']['WB1']['LB1']['modecode'] = array(
                "#type" => "select",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsRequiredField("Transports"),
                "#options" => $transports,
                "#description" => t("Select mode code"),
                "#default_value" => $modecode,
                "#disabled" => $disabled_step2,
                );        

            $form['data_entry_area1']['toppart']['WB1']['RB1'] = array(
                //'#prefix' => "\n<div id='rb1'>\n",
                '#prefix' => "\n<div id='rb1' style='float:left; width: 250px; padding: 0px 0px 0px 0px'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            $categories = $raworderoptions['categories'];
            $category = FormHelper::getKeyOfValue($categories, $rpd['ExamCategory']);
            if($category === FALSE)
            {
                $category = NULL;
            }
            $category = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'category', $category);
            $form['data_entry_area1']['toppart']['WB1']['RB1']['category'] = array(
                "#type" => "select",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsRequiredField("Category"),
                "#options" => $categories,
                "#description" => t('Select class code'),
                "#default_value" => $category,
                "#disabled" => $disabled_step2,
                );        

            $options_submitTo = $raworderoptions['submitTo'];
            $findval_submitto = isset($rpd['LocationTx']) ? $rpd['LocationTx'] : '';
            $submitto = FormHelper::getKeyOfValue($options_submitTo, $findval_submitto);
            if($submitto === FALSE)
            {
                $submitto = NULL;
            }
            $submitto = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'submitto', $submitto);
            $form['data_entry_area1']['toppart']['WB1']['RB1']['submitto'] = array(
                "#type" => "select",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsRequiredField("Submit To"),
                "#options" => $options_submitTo,
                "#description" => t('Select the facility for this order'),
                "#default_value" => $submitto,
                "#disabled" => $disabled_step2,
                );        

            $form['data_entry_area1']['toppart']['WB1']['LB2'] = array(
                '#prefix' => "\n<div id='lb2' style='float:left; width: 250px; padding: 0px 0px 0px 0px'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            $form['data_entry_area1']['toppart']['WB1']['LB2']['isolation'] = array(
                "#type" => "radios",
                "#empty_option"=>t('- Select -'),
                "#title" => FormHelper::getTitleAsUnrequiredField("Isolation"),
                "#options" => array(1=>t('Yes'),2=>t('No'),3=>t('Unknown')),
                "#default_value" => $myvalues['isolation'],
                "#disabled" => $disabled_step2,
                );        
            
            $form['data_entry_area1']['toppart']['WB1']['RB2'] = array(
                '#prefix' => "\n<div id='rb2'>\n",
                '#suffix' => "\n</div>\n",
            );

            if($isMale)
            {
                //Never ask for a male.
                $form['hiddenthings']['pregnant'] = array('#type' => 'hidden', '#value' => '2');
            } else {
                $form['data_entry_area1']['toppart']['WB1']['RB2']['pregnant'] = array(
                    "#type" => "radios",
                    "#title" => FormHelper::getTitleAsRequiredField("Pregnant"),
                    "#options" => array(1=>t('Yes'),2=>t('No'),3=>t('Unknown')),
                    "#default_value" => $myvalues['pregnant'],
                    "#disabled" => $disabled_step2,
                    );        
            }

            $form['data_entry_area1']['toppart']['WB2'] = array(
                '#prefix' => "\n<div id='wb2'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            $reasonforstudy = $this->getNonEmptyValueFromArrayElseAlternateArray($myvalues, 'reasonforstudy', $rpd, 'ReasonForStudy');
            $form['data_entry_area1']['toppart']['WB2']['reasonforstudy'] = array(
                '#type'          => 'textfield',
                '#title'         => FormHelper::getTitleAsRequiredField('Reason for Study'),
                '#size' => 64, 
                '#maxlength' => 64, 
                "#description" => t('Provide short reason for study.  (64 characters maximum)'),
                "#default_value" => $reasonforstudy,
                "#disabled" => $disabled_step2,
            );
            
            $clinhist = $this->getNonEmptyValueFromArrayElseAlternateArray($myvalues, 'clinhist', $rpd, 'ClinicalHistory');
            $form['data_entry_area1']['toppart']['WB2']['clinhist'] = array(
                '#type'          => 'textarea',
                '#title'         => t('Clinical History'),
                "#disabled" => $disabled_step2,
                "#default_value" => $clinhist,
            );
            
            $form['data_entry_area1']['toppart']['LB3'] = array(
                '#prefix' => "\n<div id='lb3' style='float:left; padding: 0px 30px 0px 0px'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            if(isset($myvalues['datedesired_dateonly']))
            {
                $datedesired_dateonly = $myvalues['datedesired_dateonly'];
            } else {
                $datedesired_dateonly = NULL;
            }
            $form['data_entry_area1']['toppart']['LB3']['datedesired_dateonly'] = array(
                '#type' => 'textfield',
                '#attributes' =>array('id'=> 'edit-datedesired-dateonly'
                                     ,'class' => array('ui-datedesired-dateonly-input','datepicker')
                        ),
                '#size' => 10, 
                '#maxlength' => 10, 
                '#title'     => FormHelper::getTitleAsRequiredField('Date Desired'),
                "#disabled" => $disabled_step2,
                "#default_value" => $datedesired_dateonly,
                "#description" => t('When would you like this procedure to occur? (MM/DD/YYYY)'),
            );

            if(isset($myvalues['datedesired_timeonly']))
            {
                $datedesired_timeonly = $myvalues['datedesired_timeonly'];
            } else {
                $datedesired_timeonly = NULL;
            }
            $form['data_entry_area1']['toppart']['LB3']['datedesired_timeonly'] = array(
                '#type' => 'textfield',
                '#attributes' =>array('id'=> 'edit-datedesired-timeonly'
                                     ,'class' => array('ui-timepicker-input','hasTimepicker')
                        ),
                '#size' => 5, 
                '#maxlength' => 5, 
                '#title'     => FormHelper::getTitleAsRequiredField('Desired Exam Time'),
                "#disabled" => $disabled_step2,
                "#default_value" => $datedesired_timeonly,
                "#description" => t('When would you like for this procedure to occur? (HH:MM military time)'),
            );
            
            $form['data_entry_area1']['toppart']['RB3'] = array(
                '#prefix' => "\n<div id='rb3'>\n",
                '#suffix' => "\n</div>\n",
            );
            
            if(isset($myvalues['preopdate_dateonly']))
            {
                $preopdate_dateonly = $myvalues['preopdate_dateonly'];
            } else {
                $preopdate_dateonly = NULL;
            }
            $form['data_entry_area1']['toppart']['RB3']['preopdate_dateonly'] = array(
                '#type'          => 'textfield',
                '#attributes' =>array('id'=> 'edit-preopdate-dateonly'
                                     ,'class' => array('ui-peopdate-dateonly-input','datepicker')
                        ),
                '#size' => 10, 
                '#maxlength' => 10, 
                '#title'         => FormHelper::getTitleAsUnrequiredField('PreOp Scheduled'),
                "#disabled" => $disabled_step2,
                "#default_value" => $preopdate_dateonly,
                "#description" => t('What is the preop date? (MM/DD/YYYY)'),
            );

            if(isset($myvalues['preopdate_timeonly']))
            {
                $preopdate_timeonly = $myvalues['preopdate_timeonly'];
            } else {
                $preopdate_timeonly = NULL;
            }
            $form['data_entry_area1']['toppart']['RB3']['preopdate_timeonly'] = array(
                '#type' => 'textfield',
                '#attributes' =>array('id'=> 'edit-preopdate-timeonly'
                                     ,'class' => array('ui-timepicker-input','hasTimepicker')
                        ),
                '#size' => 5, 
                '#maxlength' => 5, 
                '#title'     => FormHelper::getTitleAsUnrequiredField('Preop Time'),
                "#disabled" => $disabled_step2,
                "#default_value" => $preopdate_timeonly,
                "#description" => t('What is the preop start time? (HH:MM military time)'),
            );

            if($currentstep == $this->getStepCount())
            {

                $otherlabel = NULL;
                $otherOptions = array();
                $otherrequired = FALSE;
                if($myvalues['category'] == 'C')
                {
                    $otherlabel = "Contract Source";
                    $otherOptions = $raworderoptions['contractOptions'];
                } else
                if($myvalues['category'] == 'S')
                {
                    $otherlabel = "Sharing Source";
                    $otherOptions = $raworderoptions['sharingOptions'];
                } else
                if($myvalues['category'] == 'R')
                {
                    $otherlabel = "Research Source";
                    $otherOptions = $raworderoptions['researchOptions'];
                }
                if($otherlabel == NULL)
                {
                    $form['hiddenthings']['contractSharingIen'] = array('#type' => 'hidden', '#value' => '');
                } else {
                    $contractSharingIen = $this->getNonEmptyValueFromArrayElseAlternateLiteral($myvalues, 'contractSharingIen', $contractSharingIen);
                    $form['data_entry_area1']['toppart']['contractSharingIen'] = array(
                        "#type" => "select",
                        "#empty_option"=>t('- Select -'),
                        '#title'   => FormHelper::getTitleAsRequiredField($otherlabel),
                        "#options" => $otherOptions,
                        "#description" => t('Select a '.$otherlabel.' option'),
                        "#default_value" => $category,
                        //"#disabled" => $disabled_step3,
                        );        
                }
                
                //Final step
                $args = array();
                $args['patientId'] = $myvalues['PatientID'];
                
                $date = new \DateTime();
                $timestamp = $date->getTimestamp(); 
                
                $startdatetime = strtotime($myvalues['datedesired_dateonly'] . ' ' . $myvalues['datedesired_timeonly']);
                $args['startDateTime'] = $startdatetime;   //$timestamp;// //Ymd.Hi TODO $myvalues['datedesired'];
                
                $args['locationIEN'] = $myvalues['neworderlocation'];
                $args['orderableItemId'] = $myvalues['neworderitem'];
                //$rawchecks = MdwsNewOrderUtils::getRadiologyOrderChecks($mdwsDao, $args);
                $rawchecks = $mdwsDao->getRadiologyOrderChecks($args);
                $form_state['orderchecks_result'] = $rawchecks;
                
                //Format the order check results for UI
                $checkresultitems = array();
                $orderChecksNeedOverride=FALSE;
                foreach($rawchecks as $key=>$value)
                {
                    if($value['level'] == 1)
                    {
                        $levelword = 'HIGH: ';
                    } else if($value['level'] == 1) {
                        $levelword = 'MODERATE: ';
                    } else {
                        $levelword = '';
                    }
                    if($value['needsOverride'])
                    {
                        $checkresultitems[$key] = $levelword.$value['name'] . ' <strong>(Requires Override)</strong>';
                        $orderChecksNeedOverride=TRUE;
                    } else {
                        $checkresultitems[$key] = $levelword.$value['name'];
                    }
                }
                $checkresulthtml = implode('<li>',$checkresultitems);
                $form['data_entry_area1']['toppart']['confirmationinfo'] = array(
                    '#prefix' => "\n<section class='generic-warning-area'>\n"
                    . '<p><ol>'
                    . $checkresulthtml
                    . '</ol></p>',
                    '#suffix' => "\n</section>\n",
                    '#disabled' => $disabled,
                );
                if($orderChecksNeedOverride)
                {
                     $form['data_entry_area1']['toppart']['orderCheckOverrideReason'] = array(
                        '#type'          => 'textfield',
                        '#title'         => FormHelper::getTitleAsRequiredField('Order Checks Override Reason'),
                        '#size' => 64, 
                        '#maxlength' => 80, 
                        "#description" => t('Provide short reason for order checks override.  (80 characters maximum)'),
                        "#default_value" => $orderCheckOverrideReason,
                    );
                } else {
                    $form['hiddenthings']['orderCheckOverrideReason'] = array('#type' => 'hidden'
                        , '#value' => '');  //MUST BE EMPTY
                }
                
                if(!$needsESIG)
                {
                    $form['hiddenthings']['esig'] = array('#type' => 'hidden'
                        , '#value' => '');  //MUST BE EMPTY
                    $form['hiddenthings']['require_esig'] = array('#type' => 'hidden'
                        , '#value' => 'no');
                } else {
                    $form['hiddenthings']['require_esig'] = array('#type' => 'hidden'
                        , '#value' => 'yes');
                    //This is the person currently logged in.
                    $form['data_entry_area1']['toppart']['esig'] = array(
                        '#type'          => 'password',
                        '#title'         => FormHelper::getTitleAsRequiredField('Electronic Signature'),
                        '#disabled'      => $disabled,
                        '#size' => 55, 
                        '#maxlength' => 128, 
                        '#default_value' => '',
                    );
                }
                
                if(!$isPROVIDER)
                {
                    //Signature on file workflow
                    $form['hiddenthings']['cancommitorder'] = array('#type' => 'hidden', '#value' => 'yes');
                } else {
                    //Could be anyone on the order
                    if($mdwsDao->getDUZ() == $myvalues['requestingProviderDuz'])
                    {
                        $form['hiddenthings']['cancommitorder'] = array('#type' => 'hidden', '#value' => 'yes');
                    } else {
                        $form['hiddenthings']['cancommitorder'] = array('#type' => 'hidden', '#value' => 'no');
                        //This person cannot commit this order.
                        $form['data_entry_area1']['toppart']['cannotcommitorderinfo'] 
                                = array('#type' => 'item'
                                , '#markup' 
                                    => '<h2><strong>IMPORTANT:</strong> You MUST contact '
                                    .$myvalues['neworderprovider_name']
                                    . " to electronically sign the new order before you can continue"
                                    . " with the RAPTOR workflow.</h2>");
                    }
                }
            }
        }
       
        $form['data_entry_area1']['action_buttons'] = array(
            '#prefix' => "\n<section class='raptor-action-buttons'>\n",
            '#suffix' => "\n</section>\n",
            '#disabled' => $disabled,
        );

        if($currentstep > 1)
        {
            $form['data_entry_area1']['action_buttons']['back'] 
                    = array('#type' => 'submit'
                    ,'#id' => 'replace-order-submit-back-button' //alex edits
                    , '#attributes' => array('class' => array('admin-action-button')
                        ,'title'=>'Go back to the previous step')
                    , '#value' => t('<< Back')
                    //, '#limit_validation_errors' => array()
                    , '#disabled' => $disabled
            );
        }
        
        if($currentstep == $this->getStepCount())
        {
            $finishnotallowed = FALSE;
            $form['data_entry_area1']['action_buttons']['next'] = array('#type' => 'submit'
                    ,'#id' => 'replace-order-submit-next-button' 
                    , '#attributes' => array('class' => array('admin-action-button')
                        , 'title'=>'Commit all the values to VistA')
                    , '#value' => t('Finish >>')
                    , '#disabled' => $disabled || $finishnotallowed,
            );
        } else {
            $form['data_entry_area1']['action_buttons']['next'] = array('#type' => 'submit'
                    ,'#id' => 'replace-order-submit-next-button' //alex edits
                    , '#attributes' => array('class' => array('admin-action-button')
                        , 'title'=>'Save values so far and go to the next step')
                    , '#value' => t('Next >>')
                    , '#disabled' => $disabled
            );
        }
        
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" '
                . ' type="button" value="Exit with No Changes"'
                . ' title="Quit the Replace Order action without saving any changes" >');
 
        
        return $form;
    }
}

