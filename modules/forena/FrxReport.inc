<?php
// $Id$
/**
 * @file
 * Basic report provider.  Controls the rendering of the report.
 */
define('FRX_NS', 'urn:FrxReports');
require_once('FrxSyntaxEngine.inc');
require_once('renderers/FrxRenderer.inc');

class FrxReport {
  public $block_edit_mode = false;
  public $blocks_loaded;
  public $data;
  public $rpt_xml;
  public $fields;
  public $category;
  public $cache;
  public $descriptor;
  public $form;
  public $access;
  public $parameters;
  public $options;
  public $formats;
  public $title;
  public $frx_title;
  public $body;
  public $html;
  public $parameters_form;
  public $skin;
  private $ids;
  private $data_passed = FALSE;
  public $teng;
  public $compareType; // Sort compare type
  public $sort; // Fields to sort by
  public $parms;
  public $missing_parms = FALSE;
  public $dom;
  public $format;
  public $link_mode = '';
  public $xpathQuery;
  public $frx_attributes = array(); // Saved attributes from prior call.
  public $file; // File handle for the forena code
  public $allowDirectWrite = FALSE; //Determine whether we can output directly.
  public $preview_mode = FALSE;    // This will make the report renderer put in editing controls when TRUE


  public function __construct($xhtml='', $data=array(), $edit = FALSE) {
    $this->access = array();
    $this->parameters = array();
    $this->options = array();
    $this->teng = new FrxSyntaxEngine(FRX_TOKEN_EXP, '{}', $this);
    $this->setParameters($data);

    if ($xhtml) {
      $dom = $this->dom = new DOMDocument('1.0', 'UTF-8');
      // Old assumption is an ojbect is a simplexml one
      if (is_object($xhtml)) $xhtml = $xhtml->asXML();

      // Load document and simplexml representation
      try {
        $success = $dom->loadXML($xhtml);
      }
      catch(Exception $e) {
        return;
      }
      if (!$success) return;

      $this->xpathQuery = new DOMXPath($dom);
      $this->setReport($dom, $this->xpathQuery);
    }


  }

  function __destruct() {
    foreach ($this as $key => $value) {
      unset($this->$key);
    }
  }

  public function setParameters($parms) {
    $this->data = $parms;
    if ($parms) $this->data_passed = TRUE;
  }

  /**
   * Sets the report.
   * @param DOMDocument $dom
   */
  public function setReport(DOMDocument $dom, DOMXPath $xpq, $edit = FALSE) {
    $this->dom = $dom;
    $dom->formatOutput = TRUE;
    $this->xpathQuery = $xpq;
    $rpt_xml = $this->rpt_xml = simplexml_import_dom($this->dom->documentElement);
    $this->missing_parms = FALSE;

    // Load header data
    $this->body = $rpt_xml->body;
    if ($rpt_xml->head) {
      $this->title = (string)$rpt_xml->head->title;
      $nodes = $rpt_xml->xpath('//frx:docgen/frx:doc');
      $this->formats = array();
      if ($nodes) foreach ($nodes as $value) {
        $arr = $value->attributes();
        $this->formats[] = (string)$arr['type'];
      }


      foreach ($rpt_xml->head->children(FRX_NS) as $name => $node) {
        switch ($name) {
        	case 'fields':
        	  $this->fields = $node;
        	  break;
        	case 'category':
        	  $this->category = (string)$node;
        	  break;
        	case 'descriptor':
        	  $this->descriptor = (string)$node;
        	  break;
        	case 'options':
        	  foreach ($node->attributes() as $key => $value) {
        	    $this->options[$key] = (string)$value;
        	  }
        	  break;
        	case 'cache':
        	  foreach ($node->attributes() as $key => $value) {
        	    $this->cache[$key] = (string)$value;
        	  }
        	case 'title':
        	  $this->frx_title = (string)$node;
        	  break;
        	case 'form':
        	  $this->form = (string) $value;
        	  break;
        	case 'parameters':
        	  foreach ($node->children(FRX_NS) as $key => $node) {
        	    $parm = array();
        	    foreach ($node->attributes() as $akey => $attr) {
        	      $parm[$akey] = (string)$attr;
        	    }
        	    $id = $parm['id'];
        	    $val = isset($parm['value']) ? $parm['value'] : '';
        	    $parm['value']= ((string)$node) ? (string)$node : $val;
        	    $this->parameters[$id] = $parm;
        	  }
        	  break;
        	case 'data':
        	  $data = array();
        	  foreach($node->attributes() as $key => $value) {
        	    $data [$key] = (string)$value;
        	  }
            $data['data'] = $node;
        	  $this->data[] = $data;
        	  break;
        	case 'doctypes':
        	  $this->doctypes = $value;
        	  break;
        }
      }
      if ($edit) $this->get_attributes_by_id();
      $this->skin = isset($this->options['skin']) ? $this->options['skin'] : @$this->options['form'];
    }
  }

  /**
   * Get the data block
   * @param $block
   * @return unknown_type
   */
  public function getData($block, $id='', $data_uri='', $raw_mode=FALSE) {
    $data = array();
    if ($data_uri) {
      parse_str($data_uri, $data);
      if (is_array($data)) foreach ($data as $key => $value) {
        $data[$key] = $this->teng->replace($value, TRUE);
      }
    }

    $xml = Frx::BlockEditor($block, $this->block_edit_mode)->data($data, $raw_mode);

    if ($xml) {
      $this->blocks_loaded = TRUE;
    }
    return $xml;
  }

  /**
   * Collapse the parameters if the data is loaded.
   */
  public function collapseParameters() {
    if (is_array($this->parameters_form) && $this->parameters_form) {
      $form = $this->parameters_form;
      if (isset($form['params']) && @$form['params']['#collapsible'])  {
        $this->parameters_form['params']['#collapsed'] = $this->blocks_loaded;
      }
    }
  }

  public function preloadData() {
    $blocks_loaded = $this->blocks_loaded;

    $jsonData = array();
    if ($this->data) foreach ($this->data as $d) {
      if (!empty($d['block'])) {
        $id = @$d['id'];
        $block = $d['block'];
        $parms = @$d['parameters'];
        $raw = !empty($d['json']) || !empty($d['raw_mode']);

        $data = $this->getData($block, $id, $parms, $raw);
        if (@$d['path'] && !$raw && $data) {
          $data = $data->xpath((string)$d['path']);
          if ($data) $data = $data[0];
        }
        Frx::Data()->setContext($id, $data);
        if (!empty($d['json']) && $this->format == 'web') {
          $ret = array();
          if ($data) foreach($data as $row) {
            $ret[] = $row;
          }
          $jsonData[$d['json']] = $ret;
        }
      }
      else {
        $id = @$d['id'];
        if ($id) Frx::Data()->setContext($id, $d['data']);
      }
    }

    if ($jsonData) {
      drupal_add_js(array('forenaData' => $jsonData), 'setting');
    }
    $this->blocks_loaded = $blocks_loaded;
  }

  /**
   * Render the report
   * @return unknown_type
   */
  public function render($format, $render_form=TRUE, $cache_data=array()) {
    if (!$format) $format = 'web';
    // Only push the parameter conte
    Frx::Data()->push($this->parms, 'parm');
    $this->parameters_form = array();

    // Find the Body of the report.
    $this->format = $format;
    $dom = $this->dom;
    // Trap error condition
    if (!$dom) return '';
    $body = $dom->getElementsByTagName('body')->item(0);
    $this->preloadData();
    // Render the rport.
    $c = Frx::Controls('FrxRenderer');
    $c->initReportNode($body, $this);
    if (!$this->missing_parms) $c->renderChildren($body, $this->html);

    //$this->collapseParameters();
    if (!$this->parameters_form) $this->parametersForm(array('collapsible' => TRUE, 'collapsed' => $this->blocks_loaded));

    // Determine the correct filter.
    $filter = variable_get('forena_input_format', 'none');
    $skinfo = Frx::Data()->getContext('skin');
    if (isset($skinfo['input_format'])) $filter = $skinfo['input_format'];
    if (isset($this->options['input_format'])) $filter = $this->options['input_format'];
    if ($filter && $filter != 'none') {
      $this->html = check_markup($this->html, $filter);
    }


    // Default in dynamic title from head.
    if ($this->frx_title) {
      $title = check_plain($this->teng->replace($this->frx_title));
      if ($title) $title = $this->title = $title;
    }

    Frx::Data()->pop();
  }


  /**
   * Convert a relative link to appropriately rendered html
   * return html A properly formatted anchor tag
   */
  public function link($title, $path, $options=array()) {
    // check if we have
    $l = '';
    if (strpos($path, ':')===FALSE) {
      switch ($this->link_mode) {
        case 'remove':
          $l = '';
          break;
        case 'no-link':
        case 'text':
          $valid = drupal_valid_path($path,FALSE);
          $l = $valid ? l($title, $path, $options) : $title;
          break;
        case 'disable':
          $valid = drupal_valid_path($path,FALSE);
          if (!$valid) {
            $options['attributes']['class'][] = 'disabled';
            $l = '<a '. drupal_attributes($options['attributes']) . '>' . check_plain($title) . '</a>';
          }
          else {
            $l = l($title, $path, $options);
          }
          break;
        default:
          $l = l($title, $path, $options);
      }
    }
    else {
      $l = l($title, $path, $options);
    }
    return $l;
  }


  public function getField($id) {
    $field = array_fill_keys(array('default', 'link', 'add-query', 'class', 'rel', 'format', 'format-string', 'target', 'calc', 'context'), '');
    if ($this->fields) {
      $path = 'frx:field[@id="' . $id . '"]';
      $formatters = $this->fields->xpath($path);
      if ($formatters) {
        $formatter = $formatters[0];
        //@TODO: Replace the default extraction with something that will get sub elements of the string
        $field['default'] = (string)$formatter;
        $field['link'] = (string)$formatter['link'];
        $field['add-query'] = (string)$formatter['add-query'];
        $field['class'] = (string)$formatter['class'];
        $field['rel'] = (string)$formatter['rel'];
        $field['format'] = (string) $formatter['format'];
        $field['format-string'] = (string) $formatter['format-string'];
        $field['target'] = (string) $formatter['target'];
        $field['calc'] = (string) $formatter['calc'];
      }
    }

    return $field;
  }

  /*
   * Formatter used by the syntax engine to alter data that gets extracted.
   * This invokes the field translation
   */
  public function format($value, $key, $raw=FALSE) {
    // Determine if there is a field overide entry
    $default='';
    $link ='';
    $format='';
    $format_str = '';
    $target = '';
    $class = '';
    $rel = '';
    $calc= '';
    if ($this->fields) {
      $path = 'frx:field[@id="' . $key . '"]';
      $formatters = $this->fields->xpath($path);
      if ($formatters) foreach ($formatters as $formatter) {
        if ((isset($formatter['block']) &&(string)$formatter['block'] == $this->block) || (!(string)$formatter['block'])) {

          //@TODO: Replace the default extraction with something that will get sub elements of the string
          $default = (string)$formatter;
          $link = (string)$formatter['link'];
          $add_query = (string)$formatter['add-query'];
          $class = (string)$formatter['class'];
          $rel = (string)$formatter['rel'];
          $format = (string) $formatter['format'];
          $calc = (string) $formatter['calc'];
          $context = (string) $formatter['context'];
          $format_str = (string) $formatter['format-string'];
          $target = (string) $formatter['target'];
        }
      }
    }

    // Evaluate any calculations first.
    if ($calc) {
      if ($context) $context .= ".";
      $calc = $this->teng->replace($calc);
      if ($calc) $value = $this->teng->replace('{' . $context . '=' . $calc . '}', TRUE);
    }


    if ($format && !$raw) {
      $value = FrxReportGenerator::$instance->format_data($value, $format, $format_str, $this->teng, $default);
      $value = trim($value);
    }

    if (is_array($value) && !$raw) {
      $value = implode(' ', $value);
    }

    // Default if specified
    if (!$value && $default) {
      $value = $default;
    }


    if ($link && !$raw) {

      $attributes = array();
      $target = $this->teng->replace($target, TRUE);
      // use the target attribute to open links in new tabs or as popups.
      if (@strpos(strtolower($target), 'popup')===0) {
        $options = "status=1";
        $attributes = array('onclick' =>
          'window.open(this.href, \'' . $target . '\', "' . $options . '"); return false;');
      }
      else {
        if ($target) $attributes['target'] = $target;
      }

      // Adding support for ctools modals.
      if ($link && strpos($class, 'ctools-use-modal') !== FALSE && is_callable('ctools_include')) {
        ctools_include('modal');
        ctools_modal_add_js();
      }
      if ($rel) $attributes['rel'] = $this->teng->replace($rel, TRUE);
      if ($class) $attributes['class'] = explode(' ', trim($this->teng->replace($class, TRUE)));
      @list($url, $query) = explode('?', $link);
      $url = $this->teng->replace($url, TRUE);
      @list($query, $queryFrag) = explode('#', $query);
      @list($url, $fragment) = explode('#', $url);
      $fragment = $fragment . $queryFrag;
      $data = array();
      parse_str($query, $data);
      if ($data) foreach ($data as $k => $v) {
        $data[$k] = $this->teng->replace($v, TRUE);
      }
      if ($add_query) {
        $parms = $_GET;
        unset($parms['q']);
        $data = array_merge($parms, $data);
      }
      if (trim($url)) $value = $this->link(
        htmlspecialchars_decode($value),
        $url,
        array('fragment' => $fragment, 'query' => $data, 'attributes' => $attributes, 'absolute' => TRUE)
      );
    }
    if ($this->preview_mode && !$raw) $value .= Frx::Editor()->fieldLink($key, $value);
    return $value;
  }

  /**
   * Delete a node based on id
   * @param unknown_type $id
   * @return unknown_type
   */
  public function deleteNode($id) {
    $path = 'body//*[@id="' . $id . '"]';
    $nodes = $this->rpt_xml->xpath($path);
    if ($nodes) {
      $node = $nodes[0];
      $dom=dom_import_simplexml($node);
      $dom->parentNode->removeChild($dom);
    }
  }

  /**
   * Return the xml data for the report.
   *
   * @return unknown
   */
  public function asXML() {
    $dom =  $this->dom;
    $dom->formatOutput = TRUE;
    return  $this->doc_prefix . $this->dom->saveXML($this->dom->documentElement);
  }
  /**
   * Make sure all xml elements have ids
   */
  public function parse_ids() {
    $i=0;
    if ($this->rpt_xml) {
      $this->rpt_xml->registerXPathNamespace('frx', FRX_NS);
      $frx_attributes = array();
      $frx_nodes = $this->rpt_xml->xpath('body//*[@frx:*]');

      if ($frx_nodes) foreach ($frx_nodes as $node) {
        $attr_nodes = $node->attributes(FRX_NS);
        if ($attr_nodes) {
          // Make sure every element has an id
          $i++;
          $id = 'forena-' . $i;

          if (!isset($node['id'])) {
            $node->addAttribute('id', $id);

          }
          else {
            if (strpos((string)$node['id'], 'forena-')===0) {
              // Reset the id to the numerically generated one
              $node['id'] = $id;
            }
            else {
              // Use the id of the element
              $id = (string)$node['id'];
            }
          }

          // Save away the frx attributes in case we need them later.
          $attr_nodes = $node->attributes(FRX_NS);
          $attrs = array();
          if ($attr_nodes) foreach ($attr_nodes as $key => $value) {
            $attrs[$key] = (string)$value;
          }
          // Save away the attributes
          $frx_attributes[$id] = $attrs;
        }
      }

      $this->frx_attributes = $frx_attributes;
    }
  }

  /**
   * Get the attributes by
   *
   * @return array Attributes
   *
   * This function will return an array for all of the frx attributes defined in the report body
   * These attributes can be saved away and added back in later using.
   */
  public function get_attributes_by_id() {
    $this->parse_ids();
    return $this->frx_attributes;
  }

  /**
   * Save attributes based on id match
   *
   * @param array $attributes
   *
   * The attributes array should be of the form
   * array( element_id => array( key1 => value1, key2 => value2)
   * The function restores the attributes based on the element id.
   */
  public function save_attributes_by_id() {
    $attributes = $this->frx_attributes;
    $rpt_xml = $this->rpt_xml;
    if ($attributes) foreach ($attributes as $id => $att_list) {
      $id_search_path = 'body//*[@id="' . $id . '"]';
      $fnd = $rpt_xml->xpath($id_search_path);
      if ($fnd) {
        $node = $fnd[0];

        // Start attribute replacement
        $frx_attributes = $node->Attributes(FRX_NS);

        foreach ($att_list as $key => $value) {
          if (!$frx_attributes[$key]) {
            $node['frx:' . $key] = $value;
          }
          else {
            unset($frx_attributes[$key]);
            $node['frx:' . $key] = $value;
          }
        }
      }
    }
  }

  /**
   * Set the value of an element within the report
   * @param String $xpath Xpath to element being saved
   * @param string $value Value to be saved.
   * @return unknown_type
   */
  public function set_value($xpath, $value) {
    $xml = $this->rpt_xml;
    $i = strrpos($xpath, '/');
    $path = substr($xpath, 0, $i);
    $key = substr($xpath, $i+1);
    $nodes = $xml->xpath($path);
    if ($nodes) {
      // if the last part of the xpath is a key then assume the key
      if (strpos($key, '@')===0) {
        $key = trim($key, '@');
        if (is_null($value)) {
          unset($nodes[0][$key]);
        }
        else {
          $nodes[0][$key] = $value;
        }
      }
      // We must be refering to the text element of a node.
      else {
        if (is_null($value)) {
          unset($nodes[0]->$key);
        }
        else {
          $nodes[0]->$key = $value;
        }
      }
    }
  }

  /**
   * Default the parameters ba
   * @param $parms Array of parameters.
   * @return boolean indicating whether the required parameters are present.
   */
  public function processParameters($parms=NULL) {
    if ($parms==NULL) {
      $parms = $this->parms;
    }
    else {
      $this->parms = $parms;
    }
    $missing_parms = FALSE;

    foreach ($this->parameters as $key => $parm) {

      if ((@$parms[$key]==='' || @$parms[$key]===array() || @$parms[$key]===NULL) && @$parm['value']) {
        $value = $parm['value'];
        $options = array();
        if (@$parm['options']) {
          parse_str($parm['options'],$options);
        }
        switch ((string)@$parm['type']) {
          case 'date_text':
          case 'date_popup':
          case 'date_select':
             if ($value){
                $date_format = @$options['date_format'] ? $options['date_format'] : 'Y-m-d';
                $datetime = @strtotime($value);
                if ($datetime) {
                  $value = date($date_format, $datetime);
                }

              }
            break;
          default:
            if (strpos($value, '|')!==FALSE) {
              $value = explode('|', $value);
            }
        }
        $parms[$key] = $value;
        $reload_params = TRUE;
      }
      //do not show report if a required parameter does not have a value
      //force the user to input a parameter

      if ((@!$parms[$key]) && (@strcmp($parm['require'], "1") == 0)) {
        $missing_parms = TRUE;
      }
    }
    $this->parms = $parms;
    return $missing_parms;
  }

  public function parametersArray() {
    $parameters = array();
    $head = $this->rpt_xml->head;

    $nodes = $head->xpath('frx:parameters/frx:parm');
    if ($nodes) {
      foreach ($nodes as $node) {
        $parm_def=array();
        $parm_def['default'] = (string)$node;
        foreach ($node->attributes() as $key => $value) {
          $parm_def[$key] = (string)$value;
        }
        $id = @$parm_def['id'];
        $parameters[$id] = $parm_def;
      }
    }
    return $parameters;
  }

  public function parametersForm($variables = array()) {
    $parms = $this->parametersArray();
    $form =  drupal_get_form('forena_parameter_form', $parms, $variables);
    $this->parameters_form = $form;
    return $form;
  }

  public function setSort($sort, $compare_type='') {
    if (!$compare_type) {
      if (defined(SORT_NATURAL)) $compare_type = SORT_NATURAL;
    }
    else {
      if (is_string($compare_type)) {
        if (defined($compare_type)) {
          $compare_type = constant($compare_type);
        }
        else {
          $compare_type = SORT_REGULAR;
        }
      }
    }
    $this->compareType = $compare_type;
    // Assume an array of sort algorithms
    if (is_array($sort)) {
      $this->sortCriteria = $sort;
    }
    else {
      $this->sortCriteria = (array)$sort;
    }
  }


  /**
   * Comparison fucntion for user defined sorts.
   */
  public function compareFunction($a, $b) {
    $c=0;
    foreach ($this->sortCriteria as $sort) {
      //Get a value
      $this->push($a, '_sort');
      $va = $this->teng->replace($sort);
      $this->pop();
      $this->push($b, '_sort');
      $vb = $this->teng->replace($sort);


      switch ($this->compareType) {
      	case SORT_REGULAR:
      	  $c =  $c= $va < $vb ? -1 : ($va == $vb ? 0 : 1);
      	  break;
      	case SORT_NUMERIC:
      	  $va = floatval($va);
      	  $vb = floatval($vb);
      	  $c =  $c= $va < $vb ? -1 : ($va == $vb ? 0 : 1);
      	  break;
      	case SORT_STRING:
      	  $c = strcasecmp($va, $vb);
      	  break;
      	case SORT_NATURAL:
      	  $c = strnatcasecmp($va, $vb);
      	  break;
      	default:
      	  $c =  $c= $va < $vb ? -1 : ($va == $vb ? 0 : 1);
      }
      if ($c!==0) break;
    }
    return $c;
  }

  /**
   * Sort the current data context if it is an array.
   * @param $teng Token replacement engine to use for sort
   * @param unknown $sort
   * @param string $compare_type
   */
  public function sort(&$data, $sort, $compare_type='') {
    $this->setSort($sort, $compare_type);
    if (is_array($data)) {
      uasort($data, array($this, 'compareFunction'));
    }
  }

  /**
   * Iterate the data based on the provided path.
   *
   * @param $path xpath to iterate xml on
   * @param $group grouping value
   * @param $sort Sort criteria
   */
  public function group($data,  $group='', $sums= array()) {
    $rows = array();
    $totals = array();
    if (is_array($group)) $group = implode(' ', $group);
    $group = (string)$group;
    if (is_array($data) || is_object($data)) {
      foreach ($data as $row) {
        Frx::Data()->push($row, '_group');
        $gval = $this->teng->replace($group, TRUE);
        foreach($sums as $sum_col) {
          $sval = $this->teng->replace($sum_col, FALSE);
          $skey = trim($sum_col, '{}');
          $totals[$gval][$skey] = isset($totals[$gval][$skey]) ? $totals[$gval][$skey] + $sval : (float)$sval;
        }
        Frx::Data()->pop();
        $rows[$gval][] = $row;
      }
    }
    foreach($totals as $gval => $col) {
      foreach ($col as $skey => $total) {
        $tkey = $skey . '_total';
        $rows[$gval][0]->$tkey = (string)$total;
      }
    }

    return $rows;
  }

  public function writeBuffer() {
    if ($this->allowDirectWrite) {
      if ($this->file) {
        fwrite($this->file, $this->html);
        $this->html = '';
      }
    }
  }

}


