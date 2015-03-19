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

function raptor_glue_choosevisit_form() 
{
    return drupal_get_form("raptor_glue_choosevisit_form_builder");
}

function raptor_glue_requestcollaborate_form() 
{
    return drupal_get_form("raptor_glue_requestcollaborate_form_builder");
}

function raptor_glue_suspendticket_form() 
{
    return drupal_get_form("raptor_glue_suspendticket_form_builder");
}

function raptor_glue_cancelorder_form() 
{
    return drupal_get_form("raptor_glue_cancelorder_form_builder");
}

function raptor_glue_addradiationdosehxentry_form() 
{
    return drupal_get_form("raptor_glue_addradiationdosehxentry_form_builder");
}

function raptor_glue_editradiationdosehxentry_form() 
{
    return drupal_get_form("raptor_glue_editradiationdosehxentry_form_builder");
}

function raptor_glue_mng_lists_form() 
{
    return drupal_get_form("raptor_glue_mng_lists_form_builder");
}
function raptor_glue_editsedationoptions_form() 
{
    return drupal_get_form("raptor_glue_editsedationoptions_form_builder");
}
function raptor_glue_edithydrationoptions_form() 
{
    return drupal_get_form("raptor_glue_edithydrationoptions_form_builder");
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
    return drupal_get_form("raptor_glue_editexamroomoptions_form_builder");
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
    $form = array();
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

function raptor_glue_cancelorder_form_builder($form, &$form_state)
{
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
    $bOkay = $oPI->updateDatabase($form, $form_state['values']);
    if($bOkay)
    {
        //Success!
        $form_state['redirect'] = array(
            'worklist',
          );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_edithydrationoptions_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editsedationoptions_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editcontrastoptions_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editradioisotopeoptions_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editexamroomoptions_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editatriskmeds_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editatriskallergycontrast_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
}

function raptor_glue_worklist_form() 
{
    return drupal_get_form("raptor_glue_worklist_form_builder");
}

function raptor_glue_worklist_form_builder($form, &$form_state)
{
    //drupal_set_message('<h2>Building WORKLIST page at ' . microtime(TRUE) . '</h2>');

    $oPI = new \raptor\WorklistPage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    $buildresult = $oPI->getForm($form, $form_state, $disabled, $myvalues);
    //drupal_set_message('<h2>Done building WORKLIST page at ' . microtime(TRUE) . '</h2>');
    
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editatriskbloodthinner_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editatriskrarecontrast_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
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
    $rowsupdated = $oPI->updateDatabase($form_state['values']);
}
function raptor_glue_editatriskrareradioisotope_form_builder_submit($form, &$form_state) 
{
    //Success!
    $form_state['redirect'] = array(
        'worklist',
        array(
          'query' => array(
            'dialog' => 'managelists',
          ),
        ),
      );
}
