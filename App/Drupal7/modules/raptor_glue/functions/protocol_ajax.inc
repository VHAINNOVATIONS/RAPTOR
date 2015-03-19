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
 * For optimization reasons, this file is ONLY included by classes that support these ajax operations.
 */ 

/**
 * Important to clear the input values otherwise new default values will not apply.
 */
function raptor_unset_radio_section(&$form_state,$sectionname)
{
    unset($form_state['input'][$sectionname.'_radio_cd']);
    unset($form_state['input'][$sectionname.'_oral_id']);
    unset($form_state['input'][$sectionname.'_iv_id']);
    unset($form_state['input'][$sectionname.'_oral_customtx']);
    unset($form_state['input'][$sectionname.'_iv_customtx']);
}

/**
 * Important to clear the input values otherwise new default values will not apply.
 */
function raptor_unset_checkbox_section(&$form_state,$sectionname)
{
    unset($form_state['input'][$sectionname.'_cd']);
    unset($form_state['input'][$sectionname.'_enteric_id']);
    unset($form_state['input'][$sectionname.'_iv_id']);
    unset($form_state['input'][$sectionname.'_enteric_customtx']);
    unset($form_state['input'][$sectionname.'_iv_customtx']);
}

/**
 * This is for AJAX to populate the default values on the protocol page.
 * @return array of ajax commands for Drupal framework
 */
function raptor_fetch_protocol_defaults($form, &$form_state) 
{
    module_load_include('php', 'raptor_datalayer', 'core/data_protocolsettings');
    
    $myvalues=$form_state['values'];
    $templatevalues = array('NO_TEMPLATE_DECLARED');
    $protocol_shortname = $myvalues['protocol1_nm'];

    $oPS = new \raptor\ProtocolSettings();    //TODO cache it
    $templatevalues = $oPS->getDefaultValuesStructured($protocol_shortname);
    $template_json = json_encode($templatevalues);

    //Now set values from the template for the contraindication engine to analyze.
    $mt = microtime();
    $form_state['storage']['ajax_time'] = $mt;
    if(!isset($form_state['storage']['ajax_count']))
    {
        $form_state['storage']['ajax_count'] = 100;
    } else {
        $form_state['storage']['ajax_count'] = (intval($form_state['storage']['ajax_count']) + 1);
    }
    
    //Hydration
    raptor_unset_radio_section($form_state,'hydration');
    $form_state['ajax_values']['hydration_radio_cd'] = 'none';
    if(isset($templatevalues['hydration']['oral']) && $templatevalues['hydration']['oral'] != -1)
    {
        $form_state['ajax_values']['hydration_oral_customtx'] = $templatevalues['hydration']['oral'];
        $form_state['ajax_values']['hydration_radio_cd'] = 'oral';
    } else {
        $form_state['ajax_values']['hydration_oral_customtx'] = '';
    }
    if(isset($templatevalues['hydration']['iv']) && $templatevalues['hydration']['iv'] != -1)
    {
        $form_state['ajax_values']['hydration_iv_customtx'] = $templatevalues['hydration']['iv'];
        $form_state['ajax_values']['hydration_radio_cd'] = 'iv';
    } else {
        $form_state['ajax_values']['hydration_iv_customtx'] = '';
    }
    $form_state['input']['hydration_radio_cd'] = $form_state['ajax_values']['hydration_radio_cd'];
    
    //Sedation
    raptor_unset_radio_section($form_state,'sedation');
    $form_state['ajax_values']['sedation_radio_cd'] = 'none';
    if(isset($templatevalues['sedation']['oral']) && $templatevalues['sedation']['oral'] != -1)
    {
        $form_state['ajax_values']['sedation_oral_customtx'] = $templatevalues['sedation']['oral'];
        $form_state['ajax_values']['sedation_radio_cd'] = 'oral';
    } else {
        $form_state['ajax_values']['sedation_oral_customtx'] = '';
    }
    if(isset($templatevalues['sedation']['iv']) && $templatevalues['sedation']['iv'] != -1)
    {
        $form_state['ajax_values']['sedation_iv_customtx'] = $templatevalues['sedation']['iv'];
        $form_state['ajax_values']['sedation_radio_cd'] = 'iv';
    } else {
        $form_state['ajax_values']['sedation_iv_customtx'] = '';
    }    
    $form_state['input']['sedation_radio_cd'] = $form_state['ajax_values']['sedation_radio_cd'];
    
    //Contrast
    $contrast_count = 0;
    raptor_unset_checkbox_section($form_state,'contrast');
    if(isset($templatevalues['contrast']['enteric']) && $templatevalues['contrast']['enteric'] != -1)
    {
        $contrast_count++;
        $form_state['ajax_values']['contrast_cd']['enteric'] = 'enteric';
        $form_state['ajax_values']['contrast_enteric_customtx'] = $templatevalues['contrast']['enteric'];
    } else {
        $form_state['ajax_values']['contrast_enteric_customtx'] = '';
    }
    if(isset($templatevalues['contrast']['iv']) && $templatevalues['contrast']['iv'] != -1)
    {
        $contrast_count++;
        $form_state['ajax_values']['contrast_cd']['iv'] = 'iv';
        $form_state['ajax_values']['contrast_iv_customtx'] = $templatevalues['contrast']['iv'];
    } else {
        $form_state['ajax_values']['contrast_iv_customtx'] = '';
    }
    if($contrast_count==0)
    {
        $form_state['ajax_values']['contrast_cd']['none'] = 'none';
    }
    $form_state['input']['contrast_cd'] = $form_state['ajax_values']['contrast_cd'];
    
    //Radioisotope
    $radioisotope_count = 0;
    raptor_unset_checkbox_section($form_state,'radioisotope');
    if(isset($templatevalues['radioisotope']['enteric']) && $templatevalues['radioisotope']['enteric'] != -1)
    {
        $radioisotope_count++;
        $form_state['ajax_values']['radioisotope_cd']['enteric'] = 'enteric';
        $form_state['ajax_values']['radioisotope_enteric_customtx'] = $templatevalues['radioisotope']['enteric'];
    } else {
        $form_state['ajax_values']['radioisotope_enteric_customtx'] = '';
    }
    if(isset($templatevalues['radioisotope']['iv']) && $templatevalues['radioisotope']['iv'] != -1)
    {
        $radioisotope_count++;
        $form_state['ajax_values']['radioisotope_cd']['iv'] = 'iv';
        $form_state['ajax_values']['radioisotope_iv_customtx'] = $templatevalues['radioisotope']['iv'];
    } else {
        $form_state['ajax_values']['radioisotope_iv_customtx'] = '';
    }
    if($radioisotope_count==0)
    {
        $form_state['ajax_values']['radioisotope_cd']['none'] = 'none';
    }
    $form_state['input']['radioisotope_cd'] = $form_state['ajax_values']['radioisotope_cd'];
    
    //Consent
    if(!isset($templatevalues['consentreq']))
    {
        unset($form_state['input']['consentreq_radio_cd']);
    } else {
        $form_state['input']['consentreq_radio_cd'] = $templatevalues['consentreq'];
    }
    
    //Notes
    if(!isset($templatevalues['protocolnotes']['text']))
    {
        unset($form_state['input']['protocolnotes_tx']);
    } else {
        $form_state['input']['protocolnotes_tx'] = $templatevalues['protocolnotes']['text'];
    }
    if(!isset($templatevalues['examnotes']['text']))
    {
        unset($form_state['input']['examnotes_tx']);
    } else {
        $form_state['input']['examnotes_tx'] = $templatevalues['examnotes']['text'];
    }
    
    //Now store in myvalues too.
    $myvalues = $form_state['ajax_values'];
    
    //Important that we include the div otherwise will not work on second Ajax call!
    $hiddenhtml = "\n<div id='protocol-template-data'>"
          . "\n<div id='json-default-values-all-sections' style='visibility:hidden; height:0px;'>$template_json</div>\n"
          . "\n</div>";

    //Build the form now that we have changed key values.
    $form_state['setup_formcontext'] = FALSE;
    $form_state['no_cache'] = TRUE;
    $form_state['rebuild'] = TRUE;
    $newform = drupal_build_form($form['#form_id'], $form_state);
    //$newform = drupal_rebuild_form($form['#form_id'], $form_state);

    $staticwarninghtml  = drupal_render($newform["static_warnings_area"]);
    $area1              = drupal_render($newform['data_entry_area1']);
    $area2              = drupal_render($newform['data_entry_area2']);

    $commands[] = ajax_command_replace('#static-warnings',$staticwarninghtml);
    $commands[] = ajax_command_replace('#input-right-side',$area1);
    $commands[] = ajax_command_replace('#input-bottom-protocol',$area2);
    
    $commands[] = ajax_command_replace('#protocol-template-data',$hiddenhtml);
    //$commands[] = ajax_command_invoke(NULL, 'doDefaultValuesInFromProtocolTemplate');
    //$commands[] = ajax_command_replace('#ci-not-acknowledged',$htmlack);
    
    return array('#type'=>'ajax','#commands'=>$commands);
}


/**
 * This is for AJAX to populate the options on values on the protocol lib page.
 * @return array of ajax commands for Drupal framework
 */
function raptor_fetch_protocollib_options($form, $form_state) 
{
    module_load_include('php', 'raptor_datalayer', 'core/data_protocolsettings');
    
    $myvalues = $form_state['values'];
    $psn = $myvalues['protocol_shortname'];
    $modality_abbr = $myvalues['modality_abbr'];
    $value = raptor\ProtocolLibPageHelper::getAllOptionsOfModalityStructured($modality_abbr);
    $json = json_encode($value);

    //Important that we include the div otherwise will not work on second Ajax call!
    $hiddenhtml = "\n<div id='protocol-lib-options-data'>"
          . "\n<div id='json-option-values-all-sections' style='visibility:hidden; height:0px;'>$json</div>\n"
          . "\n</div>";

    //Now execute all the commands to populate the default values.
    $commands[] = ajax_command_replace('#protocol-lib-options-data',$hiddenhtml);
    //$commands[] = ajax_command_invoke(NULL, 'doOptionValuesForProtocolLibForm');
    //$commands[] = ajax_command_alert('Running this ajax command thing for ['.$modality_abbr.']=' . print_r($json,TRUE));
    return array('#type'=>'ajax','#commands'=>$commands);
}


