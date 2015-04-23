<?php
/**
 * @file FrxQueryBuilder.inc
 * Base query builder class used for creating data blocks.
 * Classes that extend this one can be used to create data blocks
 * @author metzlerd
 *
 */
class FrxQueryBuilder {
  // The supported plugins for this type
  public $supportedPlugins = array(
  	'FrxDrupal',
    'FrxOracle',
    'FrxMSSQL',
    'FrxPDO',
    'FrxPostgres',
  );

  public $name = 'SQL Statement';

  public $type = 'sql';

  public function initBlock($block) {

  }

  public function scrapeConfig() {
    return $config();
  }

  public function configForm($config) {

    $form['file'] = array(
        '#type' => 'textarea',
        '#title' => t('Source'),
        '#default_value' => @$config['file'],
    );
    return $form;
  }

  /**
   * Returns a set of errors keyed by the form elements that
   * Can also be used to alter block configuration.
   * @param $config
   * @param $form_state
   */
  public function configValidate(&$config) {
  }

  public function generate($config) {
  }
}