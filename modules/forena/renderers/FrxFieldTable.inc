<?php
/**
 * @file FrxFieldTable
 * Template that displays a table of column/value vertically.
 */
class FrxFieldTable extends FrxRenderer {
  public $templateName = 'Table of Fields';


  /**
   * Extract table configuration from the HTML
   * @see FrxRenderer::scrapeConfig()
   */
  public function scrapeConfig() {
    $config=array();
    $this->extractTemplateHTML($this->reportDocDomNode, $config, array('table'));
    $tds = $this->extractXPathInnerHTML('*//td', $this->reportDocDomNode, FALSE);
    $ths = $this->extractXPathInnerHTML('*//th', $this->reportDocDomNode, FALSE);
    $columns = array_combine($ths, $tds);
    $i=0;
    foreach($columns as $label=>$token) {
      $i++;
      $key = trim($token, '{}');
      $config['columns'][$key] = array(
          'contents' => $token,
          'label' => $label,
          'include' => 1,
          'weight' => $i,
      );
    }
    return $config;
  }


  public function generate($xml, &$config) {
    $config['foreach'] = '*';
    $config['class'] = 'FrxFieldTable';
    $div = $this->blockDiv($config);
    $columns = $this->columns($xml);
    // PUt on the header
    $this->removeChildren($div);
    $this->addFragment($div, $config['header']);
    $table = $this->setFirstNode($div, 4, 'table');

    $r=0;
    if ($config['columns']) foreach ($config['columns'] as $key => $col) if ($col['include']) {
      $r++;
      $tbody = $this->addNode($table, 6, 'tbody');
      $tr = $this->addNode($tbody, 8, 'tr');
      $th_attrs = array();
      if ($r==1) {
        $th_attrs = array('style' => 'width: 25em;');
      }

      $th = $this->addNode($tr, 10, 'th', $col['label']);
      $td = $this->addNode($tr, 10, 'td', $col['contents']);
    }

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
  }

  /**
   * Template configuration form
   * @parmater $configuration form
   */
  public function configForm($config) {
    // Load header informationi from parent config.
    $form = parent::configForm($config);
    $this->weight_sort($config['columns']);
    $form['columns'] = array('#theme' => 'forena_element_draggable',   '#draggable_id' => 'FrxTable-columns');
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
          '#ajax' => $this->configAjax()
      );

      $ctl['weight'] = array(
          "#type" => 'weight',
          '#title' => t('Weight'),
          '#delta' => 50,
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