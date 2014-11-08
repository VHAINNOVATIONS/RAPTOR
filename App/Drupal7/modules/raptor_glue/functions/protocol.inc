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
 * Functions that are only loaded when working with protocol
 * 
 */


//Protocol lib management
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
    $oPI = new \raptor\AddProtocolLibPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_addprotocollib_form_builder_customvalidate($form, &$form_state) 
{
    $oPI = new \raptor\AddProtocolLibPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_addprotocollib_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\AddProtocolLibPage();
    $rowsupdated = $oPI->updateDatabase($form, $form_state['values']);
    if($rowsupdated == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
            array(
              'query' => array(
                'dialog' => 'manageProtocolLib',
              ),
            ),
          );
    }
}

function raptor_glue_getprotocollibtab_form() 
{
    return drupal_get_form("raptor_glue_getprotocollibtab_form_builder");
}

function raptor_glue_getprotocollibtab_form_builder($form, &$form_state)
{
    $oPI = new \raptor\GetProtocolLibTab();
    $form = array();
    $disabled = false;
    $myvalues = array();    //No values for this one.
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_protocolinfo_form_stub() 
{
    module_load_include('php', 'raptor_datalayer', 'core/data_context');
    
    //drupal_set_message('About to check for selected TID');
    $oContext = \raptor\Context::getInstance();
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
        $pbatch=null;
        if($rawrtid === null || substr($rawrtid,0,1) !== '[' || substr($rawrtid,-1) !== ']')
        {
            die("The rawrtid parameter MUST start with [ and end with ] bracket!");
        }
        $clean_rawrtid = substr($rawrtid, 1, -1);
        $oContext->setSelectedTrackingID($clean_rawrtid);
    }
    if(!$oContext->hasSelectedTrackingID())
    {
        //drupal_set_message('<h1>Did NOT find a selected TID</h1>');
        drupal_goto('worklist',array('redirectreason'=>'nomorertids'));
    }
    //drupal_set_message('Found a selected TID');
    return array(); //The theme builds it.
}

function raptor_glue_protocolinfo_form_inputarea() 
{
    return drupal_get_form("raptor_glue_protocolinfo_form_builder");
}

function raptor_glue_protocolinfo_form_builder($form, &$form_state)
{
    //drupal_set_message('About to check for selected TID');
    $oContext = \raptor\Context::getInstance();
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
        $pbatch=null;
        if($rawrtid === null || substr($rawrtid,0,1) !== '[' || substr($rawrtid,-1) !== ']')
        {
            die("The rawrtid parameter MUST start with [ and end with ] bracket!");
        }
        $clean_rawrtid = substr($rawrtid, 1, -1);
        $oContext->setSelectedTrackingID($clean_rawrtid);
    }
    if(!$oContext->hasSelectedTrackingID())
    {
        //drupal_set_message('<h1>Did NOT find a selected TID</h1>');
        drupal_goto('worklist',array('redirectreason'=>'nomorertids'));
    }
    
    
    
    //die('LOOK builder has fs=['.$form_state['fjf_testing'].']');
    //die('LOOK PATH='.drupal_get_path()."\n".'AT MYARG>>>' . print_r($myarg,TRUE));
    //drupal_set_message('<h2>Building PROTOCOLINFO page at ' . microtime(TRUE) . '</h2>');
    //$oContext = \raptor\Context::getInstance();
    $getform = TRUE;
    //die('LOOK NOW We are here with $rawrtid = '.$rawrtid);
    if($getform)
    {
        $pbmsg = $oContext->getPersonalBatchStackMessage();
        if($pbmsg !== null)
        {
            drupal_set_message('<div class="personalbatchmsg">'.$pbmsg.'</div>');
        }
        $tid = $oContext->getSelectedTrackingID();
        $oPI = new \raptor\ProtocolInfoPage($tid);
        $form = array();
        $disabled = FALSE;
        if(!isset($form_state['values']))
        {
            $myvalues_override = $oPI->getFieldValues();
        } else {
            $myvalues_override = NULL;
        }
        $buildResult = $oPI->getForm($form, $form_state, $disabled, $myvalues_override);
        //drupal_set_message('Built values for ' . $tid . ' see...' . print_r($myvalues,TRUE));
    } else {
        //die('should run commands now!!!!!!!!');
        $buildResult = array();
        //$commands[] = ajax_command_alert('Another ajax command thing.');
        //return array('#type'=>'ajax','#commands'=>$commands);
    }
    //drupal_set_message('<h2>Done building PROTOCOLINFO page at ' . microtime(TRUE) . '</h2>');
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
                    $javascript = 'manageChecklistQuestionComment("'.$response.'","'.$showOnValues.'","'.$commentHtmlTagName.'");';
                    drupal_add_js('jQuery(document).ready(function () {'.$javascript.'});', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
                }
            }
        }
    }
    return $form;
}


function raptor_glue_protocolinfo_form_builder_validate($form, &$form_state) 
{
    //die('Custom validate for protocol info>>>'.print_r($form_state,TRUE));
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
    $oContext = \raptor\Context::getInstance();
    $tid = $oContext->getSelectedTrackingID();
    $oPI = new \raptor\ProtocolInfoPage($tid);
    $oPI->setupFormContext();
    $bOkay = $oPI->looksValidFormState($form, $form_state);
//die('LOOK in validate >>>>result=['.$bOkay.'] clicked=['.$clickedvalue.']<br>'.print_r($myvalues,TRUE));

    return 0; //$bOkay;
}

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
    //drupal_set_message('Submit clicked button = [' . $clickedvalue . ']');
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
                || substr($clickedvalue,0,6) == 'Commit')
        {
            $form_state['redirect'] = array(
                'protocol',array('query' => array('pbatch'=>'CONTINUE'))
              );  
        } else {
            //Staying on current form so rebuild it.
            
            //drupal_set_message('Clicked button=['.$clickedvalue.']');
            $form_state['rebuild'] = TRUE;
            $form_state['flag'] = 1;
            //return drupal_rebuild_form($form, $form_state);
        }
    }
}

function raptor_glue_viewprotocollib_form_builder($form, &$form_state)
{
    if(!isset($_GET['protocol_shortname']))
    {
        die("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        die("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }

    $oPI = new \raptor\ViewProtocolLibPage($psn);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_deleteprotocollib_form_builder($form, &$form_state)
{
    if(!isset($_GET['protocol_shortname']))
    {
        die("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        die("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }

    $oPI = new \raptor\DeleteProtocolLibPage($psn);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_deleteprotocollib_form_builder_submit($form, &$form_state) 
{
    if(!isset($_GET['protocol_shortname']))
    {
        die("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        die("Invalid protocol_shortname url parameter: that value MUST NOT be numeric!");
    }
    $oPI = new \raptor\DeleteProtocolLibPage($psn);
    $rowsupdated = $oPI->updateDatabase($form, $form_state['values']);
    if($rowsupdated == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
            array(
              'query' => array(
                'dialog' => 'manageProtocolLib',
              ),
            ),
          );
    }
}
function raptor_glue_editprotocollib_form_builder($form, $form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array(); //Blank if not ajax.
    if(isset($_GET['protocol_shortname']))
    { 
        $psn = $_GET['protocol_shortname'];
    } else if(isset($myvalues['protocol_shortname'])) {
        $psn = $myvalues['protocol_shortname'];
    } else {
        die("Missing required protocol_shortname url parameter! !!!! See fs=".print_r($form_state['values'],TRUE));
    }
    if(is_numeric($psn))
    {
        die("Invalid protocol_shortname url parameter: that value MUST be text!");
    }

    $oPI = new \raptor\EditProtocolLibPage($psn);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($psn);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editprotocollib_form_builder_customvalidate($form, &$form_state) 
{
    $psn = $_GET['protocol_shortname'];
    $oPI = new \raptor\EditProtocolLibPage($psn);
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_editprotocollib_form_builder_submit($form, &$form_state) 
{
    if(!isset($_GET['protocol_shortname']))
    {
        die("Missing required protocol_shortname url parameter!");
    }
    $psn = $_GET['protocol_shortname'];
    if(is_numeric($psn))
    {
        die("Invalid protocol_shortname url parameter: that value MUST be text!");
    }

    $oPI = new \raptor\EditProtocolLibPage($psn);
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
    if($bOkay)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
            array(
              'query' => array(
                'dialog' => 'manageProtocolLib',
              ),
            ),
          );
    }
}

