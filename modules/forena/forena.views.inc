<?php
/**
 * @file forena.views.inc
 * Views registration
 */
function forena_views_plugins()  {
  $plugins = array();
  $plugins['style'] = array(
    'forena_style' => array(
      'title' => t('Forena Report'),
      'help' => t('Use a forena report to format view results'),
      'handler' => 'ForenaViewsPlugin',
      'theme' => 'forena_view_report',
      'uses options' => TRUE,
      'uses row plugin' => FALSE,
      'uses row class' => FALSE,
      'uses grouping' => FALSE,
      'uses fields' => TRUE,
      'admin' => t('Forena Report'),
      'type' => 'normal',
  ),
  );
  return $plugins;
}