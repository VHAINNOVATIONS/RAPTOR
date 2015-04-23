<?php
class FrxCrosstab extends FrxRenderer {
  public $templateName = 'Crosstab';
  private $headers = array();
  private $dim_columns = array();
  private $group_columns = array();
  private $dim_headers = array();
  private $group_headers = array();
  private $weight;

  /**
   * Render the crosstab
   */
  public function render() {


    $variables = $this->mergedAttributes();
    if (!empty($variables['hidden']) && $this->format != 'csv' && $this->format != 'xls') {
      return '';
    }
    $path = isset($variables['path']) ? $variables['path'] : '*';
    if (!$path) $path = "*";
    $group = $variables['group'];
    $dim = $variables['dim'];
    $sum = (array)$variables['sum'];
    // Get the current context
    $data = Frx::Data()->currentContext();

    // Generate the data nodes.
    if (is_object($data)) {
      if (method_exists($data, 'xpath')) {
        $nodes = $data->xpath($path);
      }
      else {
        $nodes = $data;
      }
    }
    else {
      $nodes = (array)$data;
    }

    // Group the data.
    $data = $this->frxReport->group($nodes, $group, $sum);
    $this->dim_headers = array();
    $this->dim_rows = array();
    $this->dim_columns = array();
    $this->group_columns = array();
    $this->group_headers = array();
    $dim_values = array();
    $rows = array();
    foreach ($data as $gk => $group_rows) {
      $row_copy = array_values($group_rows);
      $dims = $this->frxReport->group($group_rows, $dim);
      $rows[$gk] = $group_rows[0];
      foreach($dims as $dk=>$r) {
        $dims = array_values($r);
        $dim_values[$dk] = $dk;
        $dim_rows[$gk][$dk] = $r[0];
      }
    }

    // Default controling attributes
    $this->defaultHeaders($dim_values);
    $hrow = array();
    foreach ($this->group_headers as $col) {
      $cell = $col;
      if (count($this->dim_columns) > 1) $cell['rowspan'] = 2;
      $hrow[] = $cell;
    }

    // Add the dimension headers.
    foreach ($dim_values as $dk) {
      foreach ($this->dim_headers as $i => $col) {
        $cell = $col;
        $cell['data'] = $dk;
        if (count($this->dim_columns) > 1) {
          $cell['data'] = $i ? $col['data'] : $dk . ' ' . $col['data'];
        }
        $hrow []  = $cell;
      }
    }

    $trows = array();
    foreach ($rows as $k=>$row) {
      Frx::Data()->push($row, '_group');
      $trow = array();
      // Base group
      foreach($this->group_columns as $col) {
        $cell = $col;
        foreach ($col as $key => $v) {
          $cell[$key] = $this->teng->replace($v);
        }
        $trow[] = $cell;
      }
      Frx::Data()->pop();

      // Dimensions
      $dim_data = $dim_rows[$k];
      foreach($dim_values as $dk) {
        $dim_row = isset($dim_data[$dk]) ? $dim_data[$dk] : array();
        frx::Data()->push($dim_row, '_dim');
        foreach($this->dim_columns as $col) {
          $cell = $col;
          foreach ($col as $k => $v) {
            $cell[$k] = $this->teng->replace($v);
          }
          $trow[] = $cell;
        }
        frx::Data()->pop();
      }
      $trows[] = $trow;

    }
    $vars = array(
    	'header' => $hrow,
      'rows' => $trows,
      'attributes' => array('class' => array('crosstab-table' )),
    );

    $output = theme('table', $vars);
    return $output;
  }

  /**
   * Generate default headers from Embedded xml.
   */
  private function defaultHeaders() {
    $node = $this->reportDocNode;
    if ($node->thead && $node->thead->tr) {
    		foreach ($node->thead->tr->children() as $name => $cell) {
    		  $hcol = array();
    		  $hcol['data'] = forena_inner_xml($cell, $name);
    		  $hcol['depth'] = 1;
    		  foreach ($cell->attributes() as $k => $v) {
    		    $hcol[$k] = (string)$v;
    		  }
    		  if ($name == 'th') {
    		    $this->group_headers[] = $hcol;
    		  }
    		  else {
    		    $this->dim_headers[] = $hcol;
    		  }
    		}
    }
    if ($node->tbody && $node->tbody->tr) {
    		foreach ($node->tbody->tr->children() as $name => $cell) {
    		  $col = array();
    		  $col['data'] = forena_inner_xml($cell, $name);
    		  foreach ($cell->attributes() as $k => $v) {
    		    $col[$k] = (string)$v;
    		  }
    		  if ($name == 'th') {
    		    $this->group_columns[] = $col;
    		  }
    		  else {
    		    $this->dim_columns[] = $col;
    		  }
    		}
    }
  }

  /**
   * Crosstab configuration form.
   */
  public function configForm($config) {
    // Load header informationi from parent config.

    $form = parent::configForm($config);
    $this->weight_sort($config['crosstab_columns']);
    $types = array('heading' => t('Heading'), 'crosstab' => t('Crosstab'), 'value' => 'Value', 'ignore' => t('Ignore'));
    $form['crosstab_columns'] = array('#theme' => 'forena_element_draggable',   '#draggable_id' => 'FrxCrosstab-columns');
    foreach ($config['crosstab_columns'] as $key => $col) {
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

      $ctl['type'] = array(
          '#type' => 'radios',
          '#title' => t('Type'),
          '#default_value' => $col['type'],
          '#options' => $types,
          '#ajax' => $this->configAjax()
      );

      $ctl['weight'] = array(
          "#type" => 'weight',
          '#title' => t('Weight'),
          '#delta' => 50,
          '#default_value' => $col['weight'],
      );

      $form['crosstab_columns'][$key] = $ctl;
    }
    return $form;
  }

  public function generate($xml, &$config) {
    $config['class'] = get_class($this);
    $block = @$config['block'];
    $id = @$config['id'];
    if ($block) {
      $id = $this->idFromBlock($block);
      $config['id'] = $id . '_block';
    }
    $config['class'] = @$config['class'] ? $config['class'] . ' FrxCrosstab' : 'FrxCrosstab';
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

    $numeric_columns = $this->numeric_columns;
    $new_columns = @$config['crosstab_columns'] ? FALSE : TRUE;

    foreach ($found_columns as $column => $label) {
      $token = '{' . $column . '}';
      if ($new_columns) {
        $type = isset($numeric_columns[$column]) ? 'value' : 'heading';
      } else {
        $type = 'ignore';
      }
      if (!isset($config['crosstab_columns'][$column])) {
        $this->addColumn($type, '{' . $column . '}', $column, $config);
      }
    }

    // Generate the grouping row
    $group = '';
    $dim = array();
    foreach($config['crosstab_columns'] as $col) {
      if ($col['type'] == 'heading') $group .= $col['contents'];
      if ($col['type'] == 'crosstab') $dim = $col['contents'];
    }
    $r_id = $id . '-renderer';
    $table_frx['renderer'] = 'FrxCrosstab';
    $table_frx['group'] = $group;
    $table_frx['dim'] = $dim;
    $attrs[$id] = $r_id;
    //$attrs = array('foreach' => '*');
    $table = $this->setFirstNode($div, 4, 'table', NULL, $attrs, $table_frx);
    $thead = $this->setFirstNode($table, 6, 'thead');
    $throw = $this->setFirstNode($thead, 8, 'tr');
    $tbody = $this->setFirstNode($table, 6, 'tbody');
    $tdrow = $this->setFirstNode($tbody, 8, 'tr', NULL, array('id' => $id),$attrs);
    if ($config['crosstab_columns']) foreach ($config['crosstab_columns'] as $key => $col) if ($col['type']!=='ignore') {
      if ($col['type']=='heading') {
        $tag = 'th';
      }
      else {
        $tag = 'td';
      }
      if ($col['type'] != 'crosstab') {
        $this->addNode($throw, 10, $tag, $col['label']);
        $this->addNode($tdrow, 10, $tag, $col['contents']);
      }
    }
    if (isset($config['footer']['value'])) $this->addFragment($div, $config['footer']['value']);
  }

  /**
   * Default configuration validator. Simply validates header and footer attributes.
   * @param unknown $config
   * @return multitype:Ambigous <The, string, A, Optional>
   */
  public function configValidate(&$config) {
    $errors = $this->validateTextFormats($config, array('header', 'footer'));
    $dims = 0;
    if (@$config['crosstab_columns']) foreach ($config['crosstab_columns'] as $col) {
      if (@$col['type']=='value') {
        $dims++;
      }
    }
    if ($dims > 1)  $errors[] = t('Too many value columns.  Please select only one');

    return $errors;
  }

  private function addColumn($type, $token, $label,  &$config) {
    $key = trim($token, '{}');
    $this->weight++;
    $config['crosstab_columns'][$key] = array(
        'contents' => $token,
        'label' => $label,
        'type' => $type,
        'weight' => $this->weight,
    );
  }

  /**
   * Extract table configuration from the HTML
   * @see FrxRenderer::scrapeConfig()
   */
  public function scrapeConfig() {
    $this->weight = 0;
    $config=array();
    $nodes =  $this->reportDocNode->xpath('//table');
    if ($nodes) {
      $table = $nodes[0];
      $attrs = $this->mergedAttributes($table);
    }
    $config['group'] = $group = $attrs['group'];
    $config['dim']  = $dim = $attrs['dim'];
    $this->extractTemplateHTML($this->reportDocDomNode, $config, array('table'));
    $head_ths = $this->extractXPathInnerHTML('*//thead/tr/th', $this->reportDocDomNode, FALSE);
    $head_tds = $this->extractXPathInnerHTML('*//thead/tr/td', $this->reportDocDomNode, FALSE);
    $body_ths = $this->extractXPathInnerHTML('*//tbody/tr/th', $this->reportDocDomNode, FALSE);
    $body_tds = $this->extractXPathInnerHTML('*//tbody/tr/td', $this->reportDocDomNode, FALSE);
    $heading_cols = array_combine($head_ths, $body_ths);
    $data_cols = array_combine($head_tds, $body_tds);
    // Get the named headers
    foreach($heading_cols as $label=>$token) {
      $this->addColumn('heading', $token, $label, $config);
    }
    // Get the data cells
    if ($dim) {
      $dims = (array)$dim;
      foreach($dims as $dim) {
        $this->addColumn('crosstab', $dim, trim($dim, '{}'), $config);
      }
    }
    foreach($data_cols as $label=>$token) {
      $this->addColumn('value', $token, $label, $config);
    }
    return $config;
  }
}