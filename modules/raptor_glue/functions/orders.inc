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
 * Copyright 2015 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 * These are order specifcic global functions.
 */

function raptor_replace_order_orderlocations($form, $form_state) {
  return "<h1>DRUPAL AJAX STUFF HAPPENED</h1>\n".$form['neworderlocation'];
}

function raptor_glue_cancelorders_form() 
{
    module_load_include('php', 'raptor_glue', 'form/CancelOrdersPage');
    return drupal_get_form("raptor_glue_cancelorders_form_builder");
}

function raptor_glue_cancelorders_form_builder($form, &$form_state)
{
    $oPI = new \raptor\CancelOrdersPage();
    $form = array();
    $disabled = FALSE;
    if(isset($form_state['values']))
    {
        $myvalues = $form_state['values'];    
    } else {
        $myvalues = $oPI->getInitialFieldValues();    
    }
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function raptor_glue_cancelorders_form_builder_validate($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrdersPage();
    $myvalues = $form_state['values'];
    return $oPI->looksValid($form, $myvalues);
}

function raptor_glue_cancelorders_form_builder_submit($form, &$form_state) 
{
    $oPI = new \raptor\CancelOrdersPage();
    $myvalues = $form_state['values'];
    $is_okay = $oPI->updateDatabase($form, $myvalues);
    if($is_okay)
    {
        
        //Success!
        $form_state['redirect'] = array(
            'worklist',
             array(),
          );
    }
}
