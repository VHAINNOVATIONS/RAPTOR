<?php
/**
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants for RAPTOR phase 2
 * Open Source VA Innovation Project 2011-2015
 * VA Innovator: Dr. Jonathan Medverd
 * SAN Implementation: Andrew Casertano, Frank Font, et al
 * Contacts: acasertano@sanbusinessconsultants.com, ffont@sanbusinessconsultants.com
 * ------------------------------------------------------------------------------------
 *  
 * Functions that are only loaded when working with protocol
 * 
 */

//Protocol lib management
function raptor_glue_viewscannedprotocol_form() 
{
    return drupal_get_form("raptor_glue_viewscannedprotocol_form_builder");
}
function raptor_glue_mng_protocols_form() 
{
    return drupal_get_form("raptor_glue_mng_protocols_form_builder");
}
function raptor_glue_viewprotocollib_form() 
{
    return drupal_get_form("raptor_glue_viewprotocollib_form_builder");
}
function raptor_glue_addprotocollib_form() 
{
    return drupal_get_form("raptor_glue_addprotocollib_form_builder");
}
function raptor_glue_editprotocollib_form() 
{
    return drupal_get_form("raptor_glue_editprotocollib_form_builder");
}
function raptor_glue_deleteprotocollib_form() 
{
    return drupal_get_form("raptor_glue_deleteprotocollib_form_builder");
}

function raptor_glue_addprotocollib_form_builder($form, &$form_state)
{
    module_load_include('php', 'raptor_glue', 'form/AddProtocolLibPage');
    $oPI = new \raptor\AddProtocolLibPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_addprotocollib_form_builder_customvalidate($form, &$form_state) 
{
    module_load_include('php', 'raptor_glue', 'form/AddProtocolLibPage');
    $myvalues = $form_state['values'];
    $oPI = new \raptor\AddProtocolLibPage();
    return $oPI->looksValidFormState($form, $form_state);    //['values']);
}
function raptor_glue_addprotocollib_form_builder_submit($form, &$form_state) 
{
    module_load_include('php', 'raptor_glue', 'form/AddProtocolLibPage');
    $myvalues = $form_state['values'];
    $oPI = new \raptor\AddProtocolLibPage();
    $isokay = $oPI->updateDatabase($form, $myvalues);
    if($isokay)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}

function raptor_glue_getprotocollibtab_form() 
{
    return drupal_get_form("raptor_glue_getprotocollibtab_form_builder");
}

function raptor_glue_getprotocollibtab_form_builder($form, &$form_state)
{
    module_load_include('php', 'raptor_glue', 'form/GetProtocolLibTab');
    $oPI = new \raptor\GetProtocolLibTab();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_protocolinfo_form_inputarea() 
{
    return drupal_get_form("raptor_glue_protocolinfo_form_builder");
}

function raptor_glue_protocolinfo_form_builder($form, &$form_state)
{
    module_load_include('php', 'raptor_datalayer', 'core/Context');
    $oContext = \raptor\Context::getInstance();
    if(isset($_REQUEST['drupalmsg']))
    {
        //There was there a request to generate a drupal message in the UI?
        $msg=urldecode($_GET['drupalmsg']);
        drupal_set_message($msg);
    }
    if(isset($_REQUEST['pbatch']))
    {
        //drupal_set_message('PBATCH detected!!!>>>' . $_REQUEST['pbatch']);
        $rawrtid=null;
        $pbatch=$_REQUEST['pbatch'];
        if($pbatch !== 'CONTINUE')
        {
            $aPBatch=explode(',', urldecode($pbatch));
            $oContext->setPersonalBatchStack($aPBatch);
        } else {
            //drupal_set_message('PBATCH CONTINUE detected and selected!!!');
            $oContext->clearSelectedTrackingID();  //Blank out current selection so gets next from the stack.
        }
    } else if(isset($_REQUEST['rawrtid'])) {
        $rawrtid=$_REQUEST['rawrtid'];
        $pbatch=NULL;
        if($rawrtid === null || substr($rawrtid,0,1) !== '[' || substr($rawrtid,-1) !== ']')
        {
            throw new \Exception("The rawrtid parameter MUST start with [ and end with ] bracket!");
        }
        $clean_rawrtid = substr($rawrtid, 1, -1);
        $oContext->setSelectedTrackingID($clean_rawrtid);
    }
    if(!$oContext->hasSelectedTrackingID())
    {
        //Clear all current ticket locks now!
        module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
        $oTT = new \raptor\TicketTrackingData();
        $nUID = $oContext->getUID();
        $oTT->deleteAllUserTicketLocks($nUID);  //Clear all other ticket locks for this user now.
        //Go back to the worklist now!
        drupal_goto('worklist',array('redirectreason'=>'nomorertids'));
    }
    $disabled = FALSE;
    if(isset($_REQUEST['mode'])) 
    {
        $showmode=  strtoupper($_REQUEST['mode']);
        if($showmode == 'VIEW')
        {
            $disabled = TRUE;
        }
    }
        
    $getform = TRUE;
    if($getform)
    {
        $pbmsg = $oContext->getPersonalBatchStackMessage();
        if($pbmsg !== null)
        {
            drupal_set_message('<div class="personalbatchmsg">'.$pbmsg.'</div>');
        }
        $tid = $oContext->getSelectedTrackingID();
        module_load_include('php', 'raptor_glue', 'form/ProtocolInfoPage');
        $oPI = new \raptor\ProtocolInfoPage($tid,$disabled);
        $form = array();
        if(!isset($form_state['values']))
        {
            $myvalues_override = $oPI->getFieldValues();
        } else {
            $myvalues_override = NULL;
        }
        $buildResult = $oPI->getForm($form, $form_state, $disabled, $myvalues_override);
    } else {
        $buildResult = array();
    }
    return $buildResult;
}


/**
 * Called by #after_build
 */
function raptor_glue_protcolinfo_after_build($form, $form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    //Make sure the safety checklist comment boxes show if there is text.
    foreach($myvalues as $key=>$value)
    {
        if($key === 'questions')
        {
            if(is_array($value['thisuser']))
            {
                $aAnswers = $value['thisuser'];
                foreach($aAnswers as $sShortname=>$aQuestionBlock)
                {
                    $response = $aQuestionBlock['response'];
                    $showOnValues = $aQuestionBlock['showcommentonvalues'];
                    $commentDrupalName = 'comment';
                    $commentHtmlTagName = 'questions[thisuser]['.$sShortname.']['.$commentDrupalName.']'; //Because #tree structure!
                    //$javascript = 'alert("now set comment for name='.$commentHtmlTagName.'");';
                    $javascript = 'manageChecklistQuestionCommentByName("'.$response.'","'.$showOnValues.'","'.$commentHtmlTagName.'");';
                    drupal_add_js('jQuery(document).ready(function () {'.$javascript.'});', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
                }
            }
        }
    }
    return $form;
}


function raptor_glue_protocolinfo_form_builder_validate($form, &$form_state) 
{
    $clickedbutton = '';
    $clickedvalue = '';
    if(isset($form_state['clicked_button']))
    {
        $clickedbutton = $form_state['clicked_button'];
        $clickedvalue = $clickedbutton['#value'];
    }
    if($clickedvalue == '')
    {
        //Some kind of ajax event, get out now with success.
        return TRUE;
    }
    $myvalues = $form_state['values'];
    if(isset($myvalues['collaboration_uid']) && is_numeric($myvalues['collaboration_uid']))
    {
        //Handle it this way because simple javascript submit seems to assume Approve button otherwise.
        $clickedvalue = 'Collaborate';
    }
    module_load_include('php', 'raptor_datalayer', 'core/Context');
    module_load_include('php', 'raptor_glue', 'form/ProtocolInfoPage');
    $oContext = \raptor\Context::getInstance();
    $tid = $oContext->getSelectedTrackingID();
    $oPI = new \raptor\ProtocolInfoPage($tid);
    $oPI->setupFormContext();
    return $oPI->looksValidFormState($form, $form_state);
}

/**
 * This is called by explict Form API value setting
 */ 
function raptor_glue_protocolinfo_form_builder_submit($form, &$form_state) 
{
    $clickedbutton = $form_state['clicked_button'];
    $clickedvalue = $clickedbutton['#value'];
    if($clickedvalue == '')
    {
        //Some kind of ajax event, get out now with success.
        return TRUE;
    }
    $myvalues = $form_state['values'];
    if(isset($myvalues['collaboration_uid']) && is_numeric($myvalues['collaboration_uid']))
    {
        //Handle it this way because simple javascript submit seems to assume Approve button otherwise.
        $clickedvalue = 'Collaborate';
    }
    module_load_include('php', 'raptor_datalayer', 'core/Context');
    module_load_include('php', 'raptor_glue', 'form/ProtocolInfoPage');
    $oContext = \raptor\Context::getInstance();
    $tid = $oContext->getSelectedTrackingID();
    $oPI = new \raptor\ProtocolInfoPage($tid);
    $bOkay = $oPI->updateDatabase($clickedbutton, $form_state['values']);
    if($bOkay)
    {
        if($clickedvalue == 'Approve' 
                || substr($clickedvalue,0,11) == 'Collaborate' 
                || substr($clickedvalue,0,12) == 'Cancel Order' 
                || substr($clickedvalue,0,7) == 'Suspend' 
                || substr($clickedvalue,0,7) == 'Reserve' 
                || substr($clickedvalue,0,7) == 'Request' 
                || substr($clickedvalue,0,4) == 'Exam' 
                || substr($clickedvalue,0,5) == 'Inter'
                || substr($clickedvalue,0,2) == 'QA'
                || substr($clickedvalue,0,3) == 'Ack'
                || substr($clickedvalue,0,6) == 'Commit')
        {
            $form_state['redirect'] = array(
                'protocol',array('query' => array('pbatch'=>'CONTINUE'))
              );  
        } else {
            //Re-display the current protocol the hard way!
            $ready_rawrtid = '['.$tid.']';
            $form_state['redirect'] = array(
                'protocol',
                array(
                  'query' => array('rawrtid'=>$ready_rawrtid),
                ),
              );
        }
    }
}

function raptor_glue_viewscannedprotocol_form_builder($form, &$form_state)
{
    if(!isset($_GET['protocol_shortname']))
    {
        throw new \Exception("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid psn url parameter: that value MUST NOT be numeric!");
    }

    module_load_include('php', 'raptor_glue', 'form/ViewScannedProtocolLibFile');
    
    $showclose = isset($_GET['showclose']);
    $oPI = new \raptor\ViewScannedProtocolLibFile($psn,$showclose);
    $form = array();
    return $oPI->getForm($form, $form_state);
}

function raptor_glue_viewprotocollib_form_builder($form, &$form_state)
{
    if(!isset($_GET['protocol_shortname']))
    {
        throw new \Exception("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }

    module_load_include('php', 'raptor_glue', 'form/ViewProtocolLibPage');
    
    $oPI = new \raptor\ViewProtocolLibPage($psn);
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_deleteprotocollib_form_builder($form, &$form_state)
{
    if(!isset($_GET['protocol_shortname']))
    {
        throw new \Exception("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }

    module_load_include('php', 'raptor_glue', 'form/DeleteProtocolLibPage');
    $oPI = new \raptor\DeleteProtocolLibPage($psn);
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_deleteprotocollib_form_builder_submit($form, &$form_state) 
{
    if(!isset($_GET['protocol_shortname']))
    {
        throw new \Exception("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }
    
    module_load_include('php', 'raptor_glue', 'form/DeleteProtocolLibPage');
    $oPI = new \raptor\DeleteProtocolLibPage($psn);
    $myvalues = $form_state['values'];
    $oPI->updateDatabase($form, $myvalues);
    $form_state['redirect'] = array(
        $oPI->getGobacktoURL()
       ,$oPI->getGobacktoURLParams()
      );
}
function raptor_glue_editprotocollib_form_builder($form, $form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array(); //Blank if ajax.
    if(isset($_GET['protocol_shortname']))
    { 
        $psn = $_GET['protocol_shortname'];
    } else if(isset($myvalues['protocol_shortname'])) {
        $psn = $myvalues['protocol_shortname'];
    } else {
        throw new \Exception("Missing required protocol_shortname url parameter! !!!! See fs=".print_r($form_state['values'],TRUE));
    }
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid protocol_shortname url parameter: that value MUST be text!");
    }

    module_load_include('php', 'raptor_glue', 'form/EditProtocolLibPage');
    $oPI = new \raptor\EditProtocolLibPage($psn);
    $form = array();    //IMPORTANT THAT WE CLEAR IT HERE NOW???
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editprotocollib_form_builder_customvalidate($form, &$form_state) 
{
    module_load_include('php', 'raptor_glue', 'form/EditProtocolLibPage');
    
    $psn = $_GET['protocol_shortname'];
    $oPI = new \raptor\EditProtocolLibPage($psn);
    $myvalues = $form_state['values'];
    return $oPI->looksValidFormState($form, $form_state);    //['values']);
}
function raptor_glue_editprotocollib_form_builder_submit($form, &$form_state) 
{
    if(!isset($_GET['protocol_shortname']))
    {
        throw new \Exception("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        throw new \Exception("Invalid protocol_shortname url parameter: that value MUST be text!");
    }

    module_load_include('php', 'raptor_glue', 'form/EditProtocolLibPage');
    
    $myvalues = $form_state['values'];
    $oPI = new \raptor\EditProtocolLibPage($psn);
    $isokay = $oPI->updateDatabase($form, $myvalues);
    if($isokay)
    {
        //Success!
        $form_state['redirect'] = array(
            $oPI->getGobacktoURL()
           ,$oPI->getGobacktoURLParams()
          );
    }
}

