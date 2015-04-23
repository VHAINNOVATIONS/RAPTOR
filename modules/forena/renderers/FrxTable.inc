<?php
/**
 * @file FrxTable
 * Template that lays out a report in a particular table format.
 */
class FrxTable extends FrxRenderer {
  public $templateName = 'Simple Table';

  /**
   * Extract table configuration from the HTML
   * @see FrxRenderer::scrapeConfig()
   */
  public function scrapeConfig() {
    $config=array();
    $caption = $this->extractXPath('*//caption', $this->reportDocDomNode);
    $this->extractTemplateHTML($this->reportDocDomNode, $config, array('table'));
    $tds = $this->extractXPathInnerHTML('*//td', $this->reportDocDomNode, FALSE);
    $ths = $this->extractXPathInnerHTML('*//th', $this->reportDocDomNode, FALSE);
    $columns = array_combine($ths, $tds);
    $weight = 0;
    foreach($columns as $label=>$token) {
      $key = trim($token, '{}');
      $weight++;
      $config['columns'][$key] = array(
      	'contents' => $token,
        'label' => $label,
        'include' => 1,
        'weight' => $weight,
      );
    }
    return $config;
  }

  public function generate($xml, &$config) {
    $block = @$config['block'];
    $id = @$config['id'];
    if ($block) {
      $id = $this->idFromBlock($block);
      $config['id'] = $id . '_block';
    }
    $config['class'] = @$config['class'] ? $config['class'] . ' FrxTable' : 'FrxTable';
    $div = $this->blockDiv($config);

    // PUt on the header
    $this->removeChildren($div);
    if (isset($config['header']['value'])) $this->addFragment($div, $config['header']['value']);

    // Decide to inlcude columns
    $found_columns = $this->columns($xml);
    if (!$found_columns) {
      $found_columns = $this->columns($xml, '/*');
      $attrs = array();
    }
    $include_column = 0;
    $weight = 0;
    if (!@$config['columns']) {
      $include_column = 1;
    }
    else {
      $weight = count($config['columns']);
    }

    foreach ($found_columns as $column => $label) {
      $token = '{' . $column . '}';
      if (!isset($config['columns'][$column])) {
        $weight++;
        $config['columns'][$column] = array(
          'contents' => $token,
        	'include' => $include_column,
          'label' => $label,
          'weight' => $weight,
        );
      }
    }

    $attrs = array('foreach' => '*');
    $table = $this->setFirstNode($div, 4, 'table');
    if (@$config['caption']) {
      $this->setFirstNode($table, 6, 'caption', $config['caption']);
    }

    $thead = $this->setFirstNode($table, 6, 'thead');
    $throw = $this->setFirstNode($thead, 8, 'tr');
    $tbody = $this->setFirstNode($table, 6, 'tbody');
    $tdrow = $this->setFirstNode($tbody, 8, 'tr', NULL, array('id' => $id),$attrs);
    if (isset($config['columns'])) foreach ($config['columns'] as $key => $col) if ($col['include']) {
      $this->addNode($throw, 10, 'th', $col['label']);
      $this->addNode($tdrow, 10, 'td', $col['contents']);
    }

    if (isset($config['footer']['value'])) $this->addFragment($div, $config['footer']['value']);
  }

  public function configForm($config) {
    // Load header informationi from parent config.
    $form = parent::configForm($config);
    $this->weight_sort($config['columns']);
    $form['columns'] = array('#theme' => 'forena_element_draggable',   '#draggable_id' => 'FrxTable-columns');
    $form['caption'] = array('#type' => 'textfield', '#title' => t('Caption'), '#default_value' => @$config['caption']);
    foreach ($config['columns'] as $key => $col) {
      $ctl = array();
      $ctl['label'] = array(
      	'#type' => 'textfield',
        '#size' => 30,
        '#title' => t('Label'),
        '#default_value' => $col['label'],
      );

      $ctl['contents'] = array(
      	'#type' => 'textfield',
        '#size' => '30',
        '#title' => t('Data'),
        '#default_value' => $col['contents'],
      );

      $ctl['include'] = array(
      	'#type' => 'checkbox',
        '#title' => t('Include'),
        '#default_value' => $col['include'],
  //      '#ajax' => $this->configAjax()
      );

      $ctl['weight'] = array(
         "#type" => 'weight',
          '#title' => t('Weight'),
          "#delta" => 50,
          '#default_value' => $col['weight'],
      );

      $form['columns'][$key] = $ctl;
    }
    return $form;
  }

  public function configValidate(&$config) {
    parent::configValidate($config);
    $this->weight_sort($config['columns']);
  }


}