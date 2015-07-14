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
 * These are miscellaneous global functions.
 * All the functions in this file are loaded every time.
 */

function raptor_about_form() 
{
    return drupal_get_form('raptor_about_form_builder');
}

function raptor_glue_loadcache_form() 
{
    return drupal_get_form('raptor_glue_loadcache_form_builder');
}

function raptor_glue_choosevisit_form() 
{
    return drupal_get_form('raptor_glue_choosevisit_form_builder');
}

function raptor_glue_requestcollaborate_form() 
{
    return drupal_get_form('raptor_glue_requestcollaborate_form_builder');
}

function raptor_glue_suspendticket_form() 
{
    return drupal_get_form('raptor_glue_suspendticket_form_builder');
}

function raptor_glue_resetworkflow_form() 
{
    return drupal_get_form('raptor_glue_resetworkflow_form_builder');
}

function raptor_glue_cancelorder_form() 
{
    return drupal_get_form('raptor_glue_cancelorder_form_builder');
}

function raptor_glue_completion_form() 
{
    return drupal_get_form('raptor_glue_completion_form_builder');
}

function raptor_glue_replaceorder_form() 
{
    return drupal_get_form('raptor_glue_replaceorder_form_builder');
}

function raptor_glue_addradiationdosehxentry_form() 
{
    return drupal_get_form('raptor_glue_addradiationdosehxentry_form_builder');
}

function raptor_glue_editradiationdosehxentry_form() 
{
    return drupal_get_form('raptor_glue_editradiationdosehxentry_form_builder');
}

function raptor_glue_mng_lists_form() 
{
    return drupal_get_form('raptor_glue_mng_lists_form_builder');
}
function raptor_glue_editsedationoptions_form() 
{
    return drupal_get_form('raptor_glue_editsedationoptions_form_builder');
}
function raptor_glue_edithydrationoptions_form() 
{
    return drupal_get_form('raptor_glue_edithydrationoptions_form_builder');
}
function raptor_glue_editcontrastoptions_form() 
{
    return drupal_get_form("raptor_glue_editcontrastoptions_form_builder");
}
function raptor_glue_editradioisotopeoptions_form() 
{
    return drupal_get_form("raptor_glue_editradioisotopeoptions_form_builder");
}
function raptor_glue_editexamroomoptions_form() 
{
    return drupal_get_form('raptor_glue_editexamroomoptions_form_builder');
}

function raptor_glue_editboilerplateprotocoloptions_form() 
{
    return drupal_get_form('raptor_glue_editboilerplateprotocoloptions_form_builder');
}
function raptor_glue_editboilerplateexamoptions_form() 
{
    return drupal_get_form('raptor_glue_editboilerplateexamoptions_form_builder');
}


function raptor_glue_editatriskmeds_form() 
{
    return drupal_get_form("raptor_glue_editatriskmeds_form_builder");
}

function raptor_glue_editatriskallergycontrast_form() 
{
    return drupal_get_form("raptor_glue_editatriskallergycontrast_form_builder");
}

//Miscellaneous forms
function raptor_glue_editworklistranking_form() 
{
    return drupal_get_form("raptor_glue_editworklistranking_form_builder");
}


function raptor_declarehiddenworklistcols_form() 
{
    return drupal_get_form("raptor_declarehiddenworklistcols_form_builder");
}


function raptor_start_form() 
{
    return drupal_get_form("raptor_start_form_builder");
}

/**
 * Navigate to the most appropriate starting page.
 */
function raptor_start_form_builder($form, &$form_state)
{
    $loggedin = FALSE;
    if(user_is_logged_in())
    {
        $oContext = \raptor\Context::getInstance();
        $userinfo = $oContext->getUserInfo(FALSE);
        $loggedin = ($userinfo->getUserID() > 0);
    }
    //die('LOOK USERINFO>>>'.print_r($userinfo,TRUE));
    if($loggedin)
    {
        drupal_goto('worklist', array('query' => array('status' => 'alreadyauthenticated')));
    } else {
        //Not logged in.
        drupal_goto('user/login', array('query' => array('status' => 'notauthenticated')));
    }
}



function raptor_declarehiddenworklistcols_form_builder($form, &$form_state)
{
    if(isset($_REQUEST['hidden_worklistcols'])) 
    {
        $hidden_worklistcols=$_REQUEST['hidden_worklistcols'];
    } else {
        die('Did NOT find required hidden_worklistcols param!!!!');
    }
    $sDelimitedColNames = urldecode($hidden_worklistcols);
    $aCols = explode(',',$sDelimitedColNames);
    $oContext = \raptor\Context::getInstance();
    $userinfo = $oContext->getUserInfo();
    $userinfo->setPrefWorklistColsHidden($aCols);
    if(count($aCols) > 0)
    {
        drupal_set_message('Hiding '.count($aCols).' worklist columns');
    }
    //drupal_exit();
    return array();
}

function raptor_glue_editworklistranking_form_builder($form, &$form_state)
{
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    $oPI = new \raptor\EditWorklistRankingPage($nUID);
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editworklistranking_form_builder_validate($form, &$form_state) 
{
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    $oPI = new \raptor\EditWorklistRankingPage($nUID);
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}
function raptor_glue_editworklistranking_form_builder_submit($form, &$form_state) 
{
    //$nUID = $form_state['values']['uid'];
    $oContext = \raptor\Context::getInstance();
    $nUID = $oContext->getUID();
    $oPI = new \raptor\EditWorklistRankingPage($nUID);
    $rowsupdated = $oPI->updateDatabase($form, $form_state);
    if($rowsupdated == 1)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
          );
    }
}

function raptor_glue_choosevisit_form_builder($form, &$form_state)
{
    $oPI = new \raptor\ChooseVisitPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_choosevisit_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\ChooseVisitPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_choosevisit_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\ChooseVisitPage();
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
    if($bOkay)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
          );
    }
}


function raptor_glue_requestcollaborate_form_builder($form, &$form_state)
{
    $oPI = new \raptor\RequestCollaboratePage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_suspendticket_form_builder($form, &$form_state)
{
    $oPI = new \raptor\SuspendTicketPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_suspendticket_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\SuspendTicketPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_suspendticket_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\SuspendTicketPage();
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
    if($bOkay)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
          );
    }
}

function raptor_glue_addradiationdosehxentry_form_builder($form, &$form_state)
{
    $oPI = new \raptor\AddRadiationDoseHxEntryPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_addradiationdosehxentry_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\AddRadiationDoseHxEntryPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_addradiationdosehxentry_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\AddRadiationDoseHxEntryPage();
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
}

function raptor_glue_editradiationdosehxentry_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditRadiationDoseHxEntryPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editradiationdosehxentry_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditRadiationDoseHxEntryPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_editradiationdosehxentry_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditRadiationDoseHxEntryPage();
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
}

function raptor_glue_resetworkflow_form_builder($form, &$form_state)
{
    if(isset($_GET['overridetid']))
    {
        $tid = trim($_GET['overridetid']);    
        $msg = ('WARNING: Using the overridetid param to cancel order="'.$tid.'"');
        error_log($msg);
        drupal_set_message($msg,'warning');
        $oContext = \raptor\Context::getInstance(FALSE);
        $oContext->setSelectedTrackingID($tid);
    }
    
    $oPI = new \raptor\ResetWorkflowPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_resetworkflow_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\ResetWorkflowPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_resetworkflow_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\ResetWorkflowPage();
    try
    {
        $myvalues = $form_state['values'];
        $bOkay = $oPI->updateDatabase($form, $myvalues);
        if($bOkay)
        {
            global $base_url;
            $rawtid=$myvalues['tid'];
            $drupalmsg=urlencode('Changed state of '.$rawtid);
            $userpage=urlencode($base_url.'/protocol?rawrtid=['.$rawtid.']'
                    .'&drupalmsg='.$drupalmsg);
            $form_state['redirect'] = array(
                'raptor/completionsuccess',
                array(
                  'query' => array('redirecturl'=>$userpage, 'drupalmsg'=>$drupalmsg),
                ),
              );
        }
    } catch (\Exception $ex) {
        //Redisplay the form but also log the details
        error_log('Failed on submit of Reset Workflow because '
                .$ex->getMessage()."\nError Details...\n".print_r($ex,TRUE));
        //Simply show the raw error message.
        drupal_set_message($ex->getMessage(),'error');
        $form_state['rebuild'] = TRUE;
    }
}


function raptor_glue_cancelorder_form_builder($form, &$form_state)
{
    if(isset($_GET['overridetid']))
    {
        $tid = trim($_GET['overridetid']);    
        $msg = ('WARNING: Using the overridetid param to cancel order="'.$tid.'"');
        error_log($msg);
        drupal_set_message($msg,'warning');
        $oContext = \raptor\Context::getInstance(FALSE);
        $oContext->setSelectedTrackingID($tid);
    }
    
    $oPI = new \raptor\CancelOrderPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_cancelorder_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrderPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function raptor_glue_cancelorder_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrderPage();
    try
    {
        $successMessage = $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        drupal_set_message($successMessage);
        $form_state['redirect'] = array(
            'worklist',
          );
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Call this as a landing page when something is completed successfully.
 */
function raptor_glue_completion_form_builder($form, &$form_state)
{
    if(isset($_GET['redirecturl']))
    {
        //Make content available to a javascript scraper to take over.
        $redirecturl=urldecode($_GET['redirecturl']);
        $form['main']['message'] 
                = array(
                    '#markup' => 
                      'Now navigate to <a id="redirection-target" href="'.$redirecturl.'">'.$redirecturl.'</a>',
                    );
    }
    if(isset($_GET['drupalmsg']))
    {
        $msg=urldecode($_GET['drupalmsg']);
        drupal_set_message($msg);
    }
    return $form;
}

function raptor_glue_replaceorder_form_builder($form, &$form_state)
{
    if(isset($_GET['overridetid']))
    {
        $tid = trim($_GET['overridetid']);    
        $msg = ('WARNING: Using the overridetid param to replace order="'.$tid.'"');
        error_log($msg);
        drupal_set_message($msg,'warning');
        $oContext = \raptor\Context::getInstance(FALSE);
        $oContext->setSelectedTrackingID($tid);
    }
 
    $oPI = new \raptor\ReplaceOrderPage();
    $disabled = FALSE;
    if(isset($form_state['values']))
    {
        $myvalues = $form_state['values'];    
    } else {
        $myvalues = $oPI->getInitialFieldValues();    
    }
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_replaceorder_form_builder_validate($form, &$form_state) 
{
    $actioncode = \raptor\ReplaceOrderPage::getWorkflowAction($form_state);
    if($actioncode == 'b')
    {
        //Nothing to validate.
        return TRUE;
    } else {
        //Validate the page.
        $oPI = new \raptor\ReplaceOrderPage();
        return $oPI->looksValidFromFormState($form, $form_state);
    }
}
function raptor_glue_replaceorder_form_builder_submit($form, &$form_state) 
{
    $maxsteps = \raptor\ReplaceOrderPage::getStepCount();
    $currentstep = $form_state['step'];
    $form_state['storedvalues'][$currentstep] = $form_state['values'];

    $actioncode = \raptor\ReplaceOrderPage::getWorkflowAction($form_state);
    
    if($actioncode == 'b' || $currentstep < $maxsteps)
    {
        $form_state['rebuild'] = TRUE;
    } else {
        //Done, submit everything.
        $myvalues = $form_state['values'];
        foreach($form_state['storedvalues'] as $stepdata)
        {
            foreach($stepdata as $key => $value)
            {
                if(strcmp($key, "submit") != 0)
                {
                    $myvalues[$key] = $value;
                }
            }
        }
        $oPI = new \raptor\ReplaceOrderPage();
        try
        {
            $bOkay = $oPI->updateDatabaseFromFormState($form, $form_state);
            if($bOkay)
            {
                $newIEN=$form_state['finalstep_result']['radiologyOrderId'];
                $oldTID=$form_state['finalstep_result']['replaced_tid'];
                $ready_rawrtid = '['.$newIEN.']';
                global $base_url;
                $drupalmsg=urlencode('Replaced '.$oldTID.' with '.$newIEN);
                $userpage=urlencode($base_url.'/protocol?rawrtid='.$ready_rawrtid
                        .'&drupalmsg='.$drupalmsg);
                $form_state['redirect'] = array(
                    'raptor/completionsuccess',
                    array(
                      'query' => array('redirecturl'=>$userpage, 'drupalmsg'=>$drupalmsg),
                    ),
                  );
            }
        } catch (\Exception $ex) {
            //Redisplay the form but also log the details
            error_log('Failed on submit of Replace Order because '
                    .$ex->getMessage()."\nError Details...\n"
                    .print_r($ex,TRUE)
                    ."\nMy Values Details...\n".print_r($myvalues,TRUE));
            if(strpos($ex->getMessage(),'Invalid electronic signature code') !== FALSE)
            {
                //Make the error message more user friendly
                form_set_error('esig','Make corrections');
            } else {
                //Simply show the raw error message.
                drupal_set_message($ex->getMessage(),'error');
            }
            $form_state['rebuild'] = TRUE;
        }
    }
}

function raptor_glue_mng_protocols_form_builder($form, &$form_state)
{
    $oPI = new \raptor\ManageProtocolLibPage();
    $form = array();
    $disabled = false;
    $myvalues = array();    //No values for this one.
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_mng_lists_form_builder($form, &$form_state)
{
    $oPI = new \raptor\ManageListsPage();
    $form = array();
    $disabled = false;
    $myvalues = array();    //No values for this one.
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_edithydrationoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListHydrationPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_edithydrationoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListHydrationPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_edithydrationoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListHydrationPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_glue_editsedationoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListSedationPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editsedationoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListSedationPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editsedationoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListSedationPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_editcontrastoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListContrastPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editcontrastoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListContrastPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editcontrastoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListContrastPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_glue_editradioisotopeoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListRadioisotopePage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editradioisotopeoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListRadioisotopePage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editradioisotopeoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListRadioisotopePage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_editexamroomoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListExamRoomPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editexamroomoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListExamRoomPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editexamroomoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListExamRoomPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_editboilerplateprotocoloptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListBoilerplateProtocolPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editboilerplateprotocoloptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListBoilerplateProtocolPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editboilerplateprotocoloptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListBoilerplateProtocolPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_glue_editboilerplateexamoptions_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListBoilerplateExamPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editboilerplateexamoptions_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListBoilerplateExamPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editboilerplateexamoptions_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListBoilerplateExamPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_glue_editatriskmeds_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListAtRiskMedsPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editatriskmeds_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskMedsPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editatriskmeds_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskMedsPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_editatriskallergycontrast_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListAtRiskAllergyContrastPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editatriskallergycontrast_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskAllergyContrastPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editatriskallergycontrast_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskAllergyContrastPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_worklist_form() 
{
    return drupal_get_form("raptor_glue_worklist_form_builder");
}

function raptor_glue_worklist_form_builder($form, &$form_state)
{
    if(isset($_GET['successmsg']))
    {
        $msg = $_GET['successmsg'];
        $stripprefix = 'Status message';
        $pos = strpos($msg, $stripprefix);
        if($pos !== FALSE && $pos === 0)
        {
            $msg = substr($msg, strlen($stripprefix));
        }
        drupal_set_message($msg);
    }
    if(isset($_GET['releasealltickets']))
    {
        $releasealltickets = $_GET['releasealltickets'];
        if(strtoupper($releasealltickets) == 'TRUE')
        {
            //Clear all current ticket locks for this user now!
            module_load_include('php', 'raptor_datalayer', 'core/data_ticket_tracking');
            $oContext = \raptor\Context::getInstance();
            $oTT = new \raptor\TicketTrackingData();
            $nUID = $oContext->getUID();
            if($nUID > '')
            {
                //They are logged in, delete tickets.
                $oTT->deleteAllUserTicketLocks($nUID); 
            }
        } else
        if(strtoupper($releasealltickets) == 'FALSE')
        {
            drupal_set_message("No existing ticket locks (if any existed) were cleared");
        } else {
            throw new \Exception("Invalid URL parameter value [$releasealltickets] for [releasealltickets]!");
        }
    }

    $oPI = new \raptor\WorklistPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    $buildresult = $oPI->getForm($form, $form_state, $disabled, $myvalues);
    return $buildresult;
}

function raptor_glue_worklist_form_builder_submit($form, &$form_state) 
{
	$form_state['redirect'] = array(
		'worklist',
		array(),
	  );
}


function raptor_glue_editatriskbloodthinner_form() 
{
    return drupal_get_form("raptor_glue_editatriskbloodthinner_form_builder");
}

function raptor_glue_editatriskbloodthinner_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListAtRiskBloodThinnerPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editatriskbloodthinner_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskBloodThinnerPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editatriskbloodthinner_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskBloodThinnerPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}

function raptor_glue_editatriskrarecontrast_form() 
{
    return drupal_get_form("raptor_glue_editatriskrarecontrast_form_builder");
}

function raptor_glue_editatriskrarecontrast_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListAtRiskRareContrastPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editatriskrarecontrast_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskRareContrastPage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editatriskrarecontrast_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskRareContrastPage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_glue_editatriskrareradioisotope_form() 
{
    return drupal_get_form("raptor_glue_editatriskrareradioisotope_form_builder");
}

function raptor_glue_editatriskrareradioisotope_form_builder($form, &$form_state)
{
    $oPI = new \raptor\EditListAtRiskRareRadioisotopePage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();    
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function raptor_glue_editatriskrareradioisotope_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskRareRadioisotopePage();
    return $oPI->looksValid($form_state['values']);
}
function raptor_glue_editatriskrareradioisotope_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\EditListAtRiskRareRadioisotopePage();
    if($oPI->updateDatabase($form_state['values']))
    {
        //Success!
        $form_state['redirect'] = array(
            'raptor/managelists',
            array(
              'query' => array(),
            ),
          );
    }
}


function raptor_about_form_builder($form, &$form_state)
{
    module_load_include('php', 'raptor_glue', 'form/ViewAbout');
    $oPI = new \raptor\ViewAbout();
    return $oPI->getForm($form, $form_state);
}

function raptor_glue_loadcache_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['tids']))
        {
            module_load_include('php', 'raptor_datalayer', 'core/RuntimeResultFlexCache');
            $tids = trim($_GET['tids']);
            $tids_ar=explode(',',$tids);
            $cachedcount=0;
            foreach($tids_ar as $onetid)
            {
                if($cachedcount >= PRE_CACHE_SIZE)
                {
                    //We have cached enough, time to stop
                    break;
                }
                $oContext = \raptor\Context::getInstance();
                $userid = $oContext->getUserInfo()->getUserID();
                $groupname = 'DashboardData';
                $oRuntimeResultFlexCache = \raptor\RuntimeResultFlexCache::getInstance($groupname);
                $sThisResultName = "LOOK TODO userid=[$userid] tid=[$onetid]";
                $aResult = array("TODO"=>"call getDashboardMap for $onetid");
                $oRuntimeResultFlexCache->addToCache($sThisResultName,$aResult);
                $cachedcount++;
                error_log("LOOK $cachedcount called loadcache($groupname) for $sThisResultName");
            }
            return array('feedback' => 
                array('#type'=>'item'
                    ,'#markup'=>"Cached $cachedcount from tids=$tids")
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}
