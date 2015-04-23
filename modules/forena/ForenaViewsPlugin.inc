<?php
/**
 * @file ForenaViewsPlugin.inc
 * Views plugin to use forena report as a views renderer.
 * @author davidmetzler
 *
 */
class ForenaViewsPlugin extends views_plugin_style {

  function option_definition() {
    $options = parent::option_definition();
    $options['report_name'] = array('default' => 'reports/test');
    return $options;
  }

  /**
   * Select forena report to use for renderig
   */
  function options_form(&$form, &$form_state) {
    $form['report_name'] = array(
      '#title' => t('Create from report'),
      '#type' => 'textfield',
      '#autocomplete_path' => 'forena/reports/autocomplete',
      '#default_value' => $this->options['report_name'],
      '#required' => TRUE,
    );

  }

  public function render() {
    // Group the rows according to the grouping instructions, if specified.
    $output = '';
    $sets = $this->render_grouping(
      $this->view->result,
      $this->options['grouping'],
      TRUE
    );
    $filename = $this->options['report_name'] . '.frx';
    if (Frx::File()->exists($filename)) {
      $output = forena_report($this->options['report_name'], $this->rendered_fields, FALSE);
    }
    return $output;
  }



}