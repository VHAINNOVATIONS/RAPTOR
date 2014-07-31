<?php

require_once(dirname(__FILE__) . '/../core/data_protocolsettings.php');

/**
 * This is for AJAX to populate the default values on the protocol page.
 * @param type $form
 * @param type $form_state
 * @return array of ajax commands for Drupal framework
 */
function raptor_fetch_protocol_defaults($form, $form_state) 
{
    //return "Fetched data for " . $form['main_fieldset_left']['protocol1_fieldset']['protocol1_nm'];
    $values=$form_state['values'];
    //return 'DEMO TODO Fetch data for [' . $values['protocol1_nm'] . ']';

    $value = array('NO_TEMPLATE_DECLARED');
    $protocolID = $values['protocol1_nm'];

    $oPS = new \raptor\ProtocolSettings();    //TODO cache it
    $value = $oPS->getDefaultValuesStructured($protocolID);
    $template_json = json_encode($value);

    //Important that we include the div otherwise will not work on second Ajax call!
    $html = "\n<div id='protocol-template-data'>"
          . "\n<div id='json-default-values-all-sections' style='visibility:hidden; height:0px;'>$template_json</div>\n"
          . "\n</div>";

    //Now execute all the commands to populate the default values.
    //$commands[] = ajax_command_invoke(NULL, 'Drupal.behaviors.disableProtocolControls');
    $commands[] = ajax_command_replace('#protocol-template-data',$html);
    $commands[] = ajax_command_invoke(NULL, 'doDefaultValuesInFromProtocolTemplate');
    //$commands[] = ajax_command_invoke(NULL, 'Drupal.behaviors.enableProtocolControls');
    //$commands[] = ajax_command_alert('Running this ajax command thing.');
    return array('#type'=>'ajax','#commands'=>$commands);
}


/**
 * This is for AJAX to populate the options on values on the protocol lib page.
 * @param type $form
 * @param type $form_state
 * @return array of ajax commands for Drupal framework
 */
function raptor_fetch_protocollib_options($form, $form_state) 
{
    $myvalues = $form_state['values'];
    $psn = $myvalues['protocol_shortname'];
    $modality_abbr = $myvalues['modality_abbr'];
    $value = raptor\ProtocolLibPageHelper::getAllOptionsOfModalityStructured($modality_abbr);
    $json = json_encode($value);

    //Important that we include the div otherwise will not work on second Ajax call!
    $html = "\n<div id='protocol-lib-options-data'>"
          . "\n<div id='json-option-values-all-sections' style='visibility:hidden; height:0px;'>$json</div>\n"
          . "\n</div>";

    //Now execute all the commands to populate the default values.
    $commands[] = ajax_command_replace('#protocol-lib-options-data',$html);
    $commands[] = ajax_command_invoke(NULL, 'doOptionValuesForProtocolLibForm');
    //$commands[] = ajax_command_alert('Running this ajax command thing for ['.$modality_abbr.']=' . print_r($json,TRUE));
    return array('#type'=>'ajax','#commands'=>$commands);
}


