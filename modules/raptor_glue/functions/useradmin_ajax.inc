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
 * For optimization reasons, this file is ONLY included by classes that support these ajax operations.
 */ 


/**
 * This is for AJAX to populate the default values on the user profile page.
 * @return array of ajax commands for Drupal framework
 * @deprecated because of Drupal checkbox initialization bug
 */
function raptor_fetch_userprofile_role_defaults($form, &$form_state) 
{
    module_load_include('php', 'raptor_datalayer', 'core/UserInfo');
    
    $myvalues=$form_state['values'];
    $templatevalues = array('NO_TEMPLATE_DECLARED');

    $role_nm = $myvalues['role_nm'];
    if(trim($role_nm) == '' || $role_nm[0] == '-')
    {
        //Just blank it out.
        $templatevalues = array();
    } else {
        //Get the template values for this selection.
        $templatevalues = \raptor\UserInfo::getRoleDefaults($role_nm);
    }

    //Now set values from the template for the contraindication engine to analyze.
    $mt = microtime();
    $form_state['storage']['ajax_time'] = $mt;
    if(!isset($form_state['storage']['ajax_count']))
    {
        $form_state['storage']['ajax_count'] = 100;
    } else {
        $form_state['storage']['ajax_count'] = (intval($form_state['storage']['ajax_count']) + 1);
    }

    //NOW SET THE VALUES ON THE FORM
    //TODO
    unset($form_state['input']['prefemail']);
    $form_state['input']['prefemail'] = $role_nm.':aaa['.$form_state['input']['VREP1'].']['.$templatevalues['lockCEUA1'].']'.$mt;

    //die('LOOK STATE>>>'.print_r($form_state['input'],TRUE));
    
    foreach($templatevalues as $k=>$v)
    {
        if($k !== 'roleid' && $k !== 'enabled_yn' && $k !== 'name')
        {
            unset($form_state['input'][$k]);    //Important to clear the control here!
            unset($form_state['ajax_values'][$k]);
            //Only assign a value IF it is NOT zero!
            if($v != 0)
            {
                $form_state['input'][$k] = $v;
                $form_state['ajax_values'][$k] = $v;    //This value gets merged later.
            }
        }
    }

    //die('LOOK STATE>>>'.print_r($form_state['input'],TRUE));
    
    
    /*

    unset($form_state['input']['VREP1']);
    unset($form_state['input']['VREP2']);
    $form_state['input']['VREP1'] = 0;
    $form_state['input']['VREP2'] = 1;
    
    
    unset($form_state['input']['CEUA1']);
    $form_state['input']['CEUA1'] = $templatevalues['CEUA1'];
    unset($form_state['input']['lockCEUA1']);
    $form_state['input']['lockCEUA1'] = $templatevalues['lockCEUA1'];
    $form_state['input']['VREP1'] = $templatevalues['VREP1'];
    $form_state['input']['lockVREP1'] = $templatevalues['lockVREP1'];
    $form_state['input']['VREP2'] = $templatevalues['VREP1'];
    $form_state['input']['lockVREP2'] = $templatevalues['lockVREP1'];
    */
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    
    //Now store in myvalues too.
    $myvalues = $form_state['ajax_values'];
    
    //Build the form now that we have changed key values.
    $form_state['setup_formcontext'] = FALSE;
    $form_state['no_cache'] = TRUE;
    $form_state['rebuild'] = TRUE;
    $newform = drupal_build_form($form['#form_id'], $form_state);

    $area1      = drupal_render($newform['data_entry_area1']);
    //$area1 = $form['data_entry_area1'];
    $commands[] = ajax_command_replace('#input-main-values',$area1);
    
    return array('#type'=>'ajax','#commands'=>$commands);
}


