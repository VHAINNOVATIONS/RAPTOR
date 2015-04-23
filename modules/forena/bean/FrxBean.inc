<?php
/**
 * @file
 * bean plugin for with a forena report
 */

class FrxBean extends BeanPlugin {
  public function values() {
    $values = array(
      'settings' => array(
        'selected_report' => FALSE,
    ),
    );
    return array_merge(parent::values(), $values);
  }

  public function form($bean, $form, &$form_state) {
    $form = array();
    $form['settings'] = array(
      '#type' => 'fieldset',
      '#tree' => 1,
      '#title' => t('Settings'),
    );
    // ask politely which node to show
    $form['settings']['selected_report'] = array(
      '#type' => 'textfield',
      '#title' => t('Select a report'),
      '#autocomplete_path' => 'forena/reports/autocomplete',
      '#default_value' => isset($bean->settings['selected_report']) ? $bean->settings['selected_report'] : '' ,
      '#required' => TRUE,
      '#multiple' => FALSE,
    );

    $form['settings']['include_url_parameters'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use menu parameters from url'),
      '#description' => t('If checked, parameters from url or in menu path will be used when rendering this report.'),
      '#default_value' => @$bean->settings['include_url_parameters'],
      '#multiple' => FALSE,
    );

    $form['settings']['parms'] = array(
      '#type' => 'textfield',
      '#title' => 'URL Parameters',
      '#description' => 'Specify parameters as they would appear on the url (e.g. state=wa&county=foo) ',
      '#default_value' => @$bean->settings['parms'],
      '#multiple' => FALSE,
    );

    return $form;
  }


  public function view($bean, $content, $view_mode = 'default', $langcode = NULL) {
    $parms = array();
    parse_str($bean->settings['parms'], $parms);

    if (!$parms) $parms = array();
    $report = $bean->settings['selected_report'];
    if ($bean->settings['include_url_parameters']) {
      $query = $_GET;
      unset($query['q']);
      $parms = array_merge($parms, $query);
      if (Frx::Data()->contextExists('parm')) $parms = array_merge($parms, Frx::Data()->getContext('parm'));
    }

    $output = forena_report($report, $parms, FALSE);
    return array('content' => array('#markup' => $output));
  }
}

