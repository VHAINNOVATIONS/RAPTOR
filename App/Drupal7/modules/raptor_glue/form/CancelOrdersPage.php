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
 * DEPRECATED!!!!!!!!!!!!!!!!!!!!
 */ 

namespace raptor;

module_load_include('php', 'raptor_datalayer', 'config/Choices');

require_once ('FormHelper.php');
require_once ('ProtocolInfoUtility.php');

/**
 * This class returns the Admin Information input content
 *
 * @author Frank Font of SAN Business Consultants
 * @deprecated since version number
 */
class CancelOrdersPage extends \raptor\ASimpleFormPage
{

    /**
     * @return a myvalues array
     */
    function getFieldValues()
    {
       return array(
           'cancelreason'=>'',
           'cancelcomment'=>'',
       );
    }    
    
    /**
     * @return array of all the selected IENs
     */
    public function getSelectedOrderIENs($myvalues)
    {
        $orders = $myvalues['table_container']['orders'];
        if(!is_array($orders))
        {
            throw new \Exception('Did not get an array of order IENs in myvalues array!');
        }
        $selections = array();
        foreach($orders as $key=>$value)
        {
            if($key==$value)
            {
                $selections[$key] = $key;
            }
        }
        return $selections;
    }
    
    /**
     * Some checks to validate the data before we try to save it.
     */
    function looksValid($form, $myvalues)
    {
        $is_good = TRUE;
        
        //Array ( [table_container] => Array ( [orders] => Array ( [449] => 449 [601] => 601
        $orders = $myvalues['table_container']['orders'];
        if(!is_array($orders))
        {
            form_set_error('table_container','Did not find any orders!');
            $is_good = FALSE;
        } else {
            $foundone = FALSE;
            foreach($orders as $key=>$value)
            {
                if($key==$value)
                {
                    $foundone = TRUE;
                    break;
                }
            }
            if(!$foundone)
            {
                form_set_error('table_container','Did not find any selected orders');
                $is_good = FALSE;
            }
        }
    
        if(!isset($myvalues['cancelreason']) || !is_numeric(($myvalues['cancelreason'])))
        {
            form_set_error('cancelreason','Did not find any a valid cancel reason');
            $is_good = FALSE;
        }
        
        return $is_good;
    }    
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $is_okay = TRUE;
            $orderIENs = $this->getSelectedOrderIENs($myvalues);
            $reasonCode = $myvalues['cancelreason'];
            $cancelcomment = $myvalues['cancelcomment'];

            die('TODO CANCEL THESE IENS >>>>'.print_r($orderIENs,TRUE) 
                    . '<br>For reason="'.$reasonCode.'"'
                    . '<br>comment="'.$cancelcomment.'"'
                    . '<br>Hit the back button to test again');

            $oContext = \raptor\Context::getInstance();
            $userinfo = $oContext->getUserInfo();
            $mdwsDao = $oContext->getMdwsClient();
            $results = MdwsUtils::cancelRadiologyOrder($mdwsDao, $reasonCode, $orderIENs);
            $cancelled_iens = $results['cancelled_iens'];
            $failed_iens = $results['failed_iens'];
            
            if(count($cancelled_iens) > 0)
            {
                //Record the fact that this user canceled some orders
                error_log('The user '.print_r($userinfo,TRUE).' cancelled the following orders: '.print_r($cancelled_iens,TRUE));
            }            
            if(count($failed_iens) > 0)
            {
                //We keep going but show them what failed.
                $is_okay = FALSE;
                $errmsg = 'Failed to cancel the following orders: '.implode(', ',$failed_iens);
                drupal_set_message($errmsg);
                error_log('The user '.print_r($userinfo,TRUE).' '.$errmsg);
            }
            return $is_okay;
        } catch (\Exception $ex) {
            drupal_set_message('Failed to commit cancel selections because '.$ex->getMessage());
            return FALSE;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='raptor-dialog'>\n",
            '#suffix' => "\n</section>\n",
        );

        $oContext = \raptor\Context::getInstance();
        $oWL = new \raptor\WorklistData($oContext);
        $raptor_worklist_rows = $oWL->getWorklistRows();    //$oContext);
        $data_rows = $raptor_worklist_rows["DataRows"];
        $mdwsDao = $oContext->getMdwsClient();
        
        $aCancelOptions = MdwsUtils::getRadiologyCancellationReasons($mdwsDao);
        $form["data_entry_area1"]['metadata']['cancelreason'] = array(
            '#type' => 'select',
            '#required' => TRUE,
            '#empty_value' => '',
            '#title' => t('Reason for Canceling Selected Orders'),
            '#options' => $aCancelOptions,
            '#default_value' => $myvalues['cancelreason'],
            '#description' => t('Select the reason for canceling the selected orders.'),
         );
        $form["data_entry_area1"]['metadata']['cancelcomment'] = array(
            '#type' => 'textarea',
            '#required' => FALSE,
            '#title' => t('Additional Cancellation Reason Comment'),
            '#default_value' => $myvalues['cancelcomment'],
            '#description' => t('Additional reason for the cancellation.'),
         );
        
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="raptor-dialog-table-container"><p>Each of the rows with a checkmark in the table below is an order that is selected for cancellation.  Only place a checkmark on the orders you intend to cancel.</p>',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        
        $rows = "\n";
        $rowcount=0;
        foreach($data_rows as $data_row)
        {
            $rowcount++;
            if($rowcount > 100)
            {
                break;
            }
            if(!is_array($data_row) || count($data_row) == 0)  
            {
                continue;
            } 
            $aRankScoreDetails = $data_row[18];
            $score = $aRankScoreDetails[0];
            $aRSComment = $aRankScoreDetails[1];
            $rscomment = '';
            foreach($aRSComment as $key => $value)
            {
                $rscomment .= "<br>$key=$value";
            }

            //$rsurl = getRankScoreIcon($score);
            // Change row background color if it is assigned to the current user
            $extra_row_class = is_array($data_row[12]) && $data_row[12]['uid'] == $m_oContext->getUID() ? "special-row" : "";
            
            $assignmentinfo =  is_array($data_row[12]) ? '<span title="'.$data_row[12]['requester_notes_tx'].'">'.$data_row[12]['fullname'].'</span>' : $data_row[12];

            $ticketid = $data_row[0];
            
            $options[$data_row[0]] = array(
                'ticketid'=>$ticketid,
                'patientname'=>$data_row[WorklistData::WLIDX_PATIENTNAME],
                'targetdate'=>$data_row[3],
                'ordereddate'=>$data_row[4],
                'modality'=>$data_row[WorklistData::WLIDX_MODALITY],
                'imagetype'=>$data_row[17],
                'study'=>$data_row[WorklistData::WLIDX_STUDY],
                'urgency'=>$data_row[7],
                'transport'=>$data_row[WorklistData::WLIDX_TRANSPORT],
                'patientlocation'=>$data_row[WorklistData::WLIDX_PATIENTCATEGORYLOCATION],
                'workflowstatus'=>$data_row[WorklistData::WLIDX_WORKFLOWSTATUS],
                'assignment'=>$assignmentinfo,
                'numpending'=>$data_row[WorklistData::WLIDX_PENDINGORDERSSAMEPATIENT],
                'scheduled'=>$data_row[WorklistData::WLIDX_SCHEDINFO]['ShowTx'],
                'seemore'=>'<a href="#" title="click to see more info" onclick="alert(\'todo show data for '.$ticketid.'\');return false;">see more</a>',
            );
        }        
        
        $header = array(
          'ticketid' => t('Ticket'),
          'patientname' => t('Patient'),
          'targetdate' => t('Date Desired'),
          'ordereddate' => t('Date Order'),
          'modality' => t('Modality'),
          'imagetype' => t('Image Type'),
          'study' => t('Study'),
          'urgency' => t('Urgency'),
          'transport' => t('Transport'),
          'patientlocation' => t('Patient Category / Location'),
          'workflowstatus' => t('Workflow Status'),
          'assignment' => t('Assignment'),
          'numpending' => t('#P'),
          'scheduled' => t('Scheduled'),
          'seemore' => t('More Info'),
        );

        $form["data_entry_area1"]['table_container']['orders'] = array(
            '#title' => 'Select Orders to Cancel',
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $options,
            '#empty' => t('No content available.'),
          );
        $form["data_entry_area1"]['table_container']['orders']['#attributes']['class']['dataTable'] = 'dataTable';
        

        $form['data_entry_area1']['action_buttons'] = array(
             '#type' => 'item', 
             '#prefix' => '<div class="raptor-action-buttons">',
             '#suffix' => '</div>', 
             '#tree' => TRUE,
         );

        $form['data_entry_area1']['action_buttons']['cancelorder'] = array('#type' => 'submit', '#value' => t('Cancel the Selected Orders'));
       
        $form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => '<input class="raptor-dialog-cancel" type="button" value="Exit without Canceling any Orders" />');        
        
        return $form;
    }
}
