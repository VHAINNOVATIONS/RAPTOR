<?php
require_once 'FrxReport.inc';
/**
 * @file FrxEditor.inc
 * Wrapper XML class for working with DOM object.
 * It provides helper
 * Enter description here ...
 * @author metzlerd
 *
 */
class FrxEditor {
  public $edit = TRUE;   // Indicates we are running in edit mode.
  public $dom;
  public $document_root;
  public $simplexml;
  public $title;
  public $report_name;
  public $report_link;
  public $frx_attributes;
  public $cache;
  public $frxReport;
  public $desc;
  public $parms = array(); // Current editor parameters.
  public $access = TRUE; // The user has access to the report.
  public $doc_prefix = '<?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE root [
    <!ENTITY nbsp "&#160;">
    ]>';
  public $xmlns = 'urn:FrxReports';
  private $field_ids;
  public $xpq;



  /**
   * Construct the editor
   * Enter description here ...
   * @param unknown_type $report_name name of report to load
   */
  public function __construct($report_name, $edit = TRUE) {
    $this->dom = new DOMDocument('1.0', 'UTF-8');
    $this->edit = $edit;
    $dom = $this->dom;
    $dom->formatOutput = TRUE;
    $dom->preserveWhiteSpace = TRUE;
    $this->frxReport = new FrxReport();
    $this->load($report_name, $edit);
  }

  /**
   * Report the root element
   * Enter description here ...
   */
  public function asXML() {
    $this->dom->formatOutput = TRUE;
    return  $this->doc_prefix . $this->dom->saveXML($this->dom->documentElement);
  }

  // Renames session storage.
  public function rename($name) {
    $old_name = $this->report_name;
    $this->report_name = $name;
    $this->report_link = 'reports/' . str_replace('/', '.', $name);
    unset($_SESSION['forena_report_editor'][$old_name]);
    $this->update();
  }

  /**
   * Save data away in the session state.
   */
  public function update() {
    $_SESSION['forena_report_editor'][$this->report_name] =
      $this->doc_prefix . $this->dom->saveXML($this->dom->documentElement);
  }

  public function cancel() {
    unset($_SESSION['forena_report_editor'][$this->report_name]);
    drupal_get_messages('warning');
  }

  /**
   * Load report from file system
   * @param unknown_type $report_name
   * @return string
   */
  public function load($report_name, $edit=TRUE) {
    $this->desc = Frx::Menu()->parseURL($report_name);
    $r_text='';
    $dom = $this->dom;
    $this->report_name = $report_name = $this->desc['name'];
    $this->report_link = 'reports/' . str_replace('/', '.', $this->desc['base_name']);
    // Load the latest copy of the report editor
    if ($report_name) {
      if (isset($_SESSION['forena_report_editor'][$report_name]) && $edit) {
        $r_text = $_SESSION['forena_report_editor'][$report_name];
        drupal_set_message(t('All changes are stored temporarily.  Click Save to make your changes permanent.  Click Cancel to discard your changes.'), 'warning', FALSE);
      }
      else {
        $filename = $report_name . '.frx';
        $r_text = Frx::File()->contents($filename);
      }
    }
    if (!$r_text) {
      $m_path = drupal_get_path('module', 'forena');
      $r_text = file_get_contents($m_path . '/default.frx');
    }

    libxml_use_internal_errors();
    try {
      @$dom->loadXML($r_text);
      $this->xpq = new DOMXPath($dom);
    }
    catch (Exception $e) {
      Frx::error('Invalid or malformed report document', '<pre>' .
        $e->getMessage() . $e->getTraceAsString() . '</pre>');
    }
    if (!$this->dom->documentElement) {
      Frx::error(t('Invalid or malformed report document'));
      return;
    }

    $this->verifyHeaderElements();
    $tnodes = $dom->getElementsByTagName('title');
    if ($tnodes->length) $this->title = $tnodes->item(0)->textContent;
    $this->document_root = $dom->documentElement;
    $this->simplexml = simplexml_import_dom($dom);
    $dom->formatOutput = TRUE;
    // Try to make sure garbage collection happens.
    unset($this->xpq);
    $this->xpq = new DOMXPath($dom);
    $this->xpq->registerNamespace('frx', $this->xmlns);
    // Make sure document header is reparsed.
    $this->frxReport->setReport($this->dom, $this->xpq, $this->edit);
    $cache = forena_load_cache($this->frxReport->rpt_xml);
    if (isset($cache['access'])) $this->access = forena_check_all_access($cache['access']);
    if (!$edit) $this->cache = Frx::File()->getCacheEntry($report_name . '.frx');
    return $r_text;
  }

  /**
   * Save report
   */
  public function save() {
    $this->cleanup_ids();
    unset($_SESSION['forena_report_editor'][$this->report_name]);
    forena_save_report($this->report_name, $this->asXML(), TRUE);
    drupal_set_message(t('Your report, %s has been saved.', array('%s' => $this->report_name)));
    drupal_get_messages('warning');
    $cid = 'forena:report:' . $this->report_name . '%';
    // Remove cache entries
    db_delete('cache')
      ->condition('cid', $cid, 'LIKE')
      ->execute();
    menu_rebuild();
  }

  public function delete() {
    $filepath = $this->report_name . '.frx';
    $do = Frx::File()->delete($filepath);
  }

  /**
   * Set the value of an element within the report
   * @param String $xpath Xpath to element being saved
   * @param string $value Value to be saved.
   * @return unknown_type
   */
  public function setValue($xpath, $value) {
    $xml = $this->simplexml;
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
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Set the value of the body of the report
   * Will parse and set the value of the body of the report
   * using XML
   * @param html $body
   */
  public function setBody($body) {
    $dom = $this->dom;
    $nodes = $dom->getElementsByTagName('body');
    $cur_body = $nodes->item(0);
    // Make sure that we have a body tag.
    if (strpos($body, '<body')===FALSE) {
      $body = '<body>' . $body . '</body>';
    }
    // Attempt to parse the xml
    $body_doc = new DOMDocument('1.0', 'UTF-8');
    $body_xml = $this->doc_prefix . '<html xmlns:frx="' . $this->xmlns . '">' . $body . '</html>';
    try {
      $body_doc->loadXML($body_xml);
      $new_body = $dom->importNode($body_doc->getElementsByTagName('body')->item(0), TRUE);
      $parent = $cur_body->parentNode;
      $parent->replaceChild($new_body, $cur_body);
    }
    catch (Exception $e) {

      Frx::error('Malformed report body', '<pre>' . $e->getMessage() .
      $e->getTraceAsString() . '</pre>');
    }
    // If there are no frx attributes in the body then replace them with the old values.
    $frx_nodes = $this->frxReport->rpt_xml->xpath('body//*[@frx:*]');
    if (!$frx_nodes) {
      $this->frxReport->save_attributes_by_id();
    }
  }
  /**
   * Makes sure that the normal header elements for a report are there.
   * Enter description here ...
   */
  public function verifyHeaderElements($required_elements = array()) {
    if (!$required_elements) $required_elements = array(
      'category',
      'options',
      'fields',
      'parameters',
      'docgen',
    );
    $dom = $this->dom;
    if (!$this->dom->documentElement) {
      drupal_set_message('error', 'error'); return;
    }

    $head = $dom->getElementsByTagName('head')->item(0);
    if (!$head)  {
      $head = $dom->createElement('head');
       $dom->documentElement->appendChild($head);
    }
    // Make sure the report title exists.
    if ($dom->getElementsByTagName('title')->length==0) {
      $n = $dom->createElement('title');
      $head->appendChild($n);
    }
    // Make sure each of these exists in the header
    foreach ($required_elements as $tag) {
      if ($dom->getElementsByTagNameNS($this->xmlns, $tag)->length == 0 ) {
        $n = $dom->createElementNS($this->xmlns, $tag);
        $head->appendChild($n);
      }
    }
  }

  /**
   * Genreal utility for setting data in the header of a reprot
   *
   * @param string $parent Name of parent element
   * @param string $element Name of child element
   * @param array $element_array Data containing the elements
   * @param array $attributes array of attribute names to set
   * @param string $element_field name of field containing node data
   * @param unknown_type $id_field name of field containint node id
   */
  public function setFrxHeader($parent, $element, $element_array, $attributes, $element_field='', $id_field = 'id') {
    $dom = $this->dom;
    $xpq = $this->xpq;
    $this->verifyHeaderElements(array($parent));
    $pnode = $dom->getElementsByTagNameNS($this->xmlns, $parent)->item(0);

    // Iterate through all child arrays in the header
    $tnode = $dom->createTextNode("\n");
    $pnode->appendChild($tnode);
    foreach ($element_array as $element_data) {
      $id = @$element_data[$id_field];
      $path = '//frx:' . $parent . '/frx:' . $element . '[@' . $id_field . '="' . $id . '"]';
      $nodes = $xpq->query($path);
      $value = NULL;
      if ($element && isset($element_data[$element_field])) {
        $value = $element_data[$element_field];
      }

      $node = $dom->createElementNS($this->xmlns, $element, trim($value, "|"));
      if ($nodes->length == 0) {
        $tnode = $dom->createTextNode("  ");
        $pnode->appendChild($tnode);        $pnode->appendChild($node);
        $tnode = $dom->createTextNode("\n");
        $pnode->appendChild($tnode);
      }
      else {
        $src_node = $nodes->item(0);
        $pnode->replaceChild($node, $src_node);
      }

      foreach ($attributes as $attribute) {
        if (!empty($element_data[$attribute])) {
          $node->setAttribute($attribute, $element_data[$attribute]);
        }
        else {
          if ($node->hasAttribute( $attribute)) {
            $node->removeAttribute($attribute);
          }
        }
      }
    }



  }

  /**
   * Builds the fields from an array of elements.
   * Enter description here ...
   * @param $fieldElements
   */
  public function setFields($fieldElements) {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('fields'));
    $this->setFrxHeader('fields', 'field',
    $fieldElements,
    array('id', 'link', 'format', 'format-string', 'target', 'rel', 'class', 'add-query', 'calc', 'context'),
      'default');

  }

  /**
   * Makes sure specific document types are asserted.
   */
  public function ensureDocGen($types) {
    $types = array_combine($types, $types);
    $doctypes = $this->getDocgen();
    $doctypes = array_merge($types, $doctypes);
    $this->setDocgen($doctypes);
  }


  /**
   * Gets the array of selected document types or default if they are present.
   * @return array
   */
  public function getDocgen() {

    //build the options and default list
    $nodes = $this->simplexml->head->xpath('//frx:doc');
    if ($nodes)  {
      $doctypes = array();
      foreach($nodes as $doc) {
        $doctypes[] = (string) $doc['type'];
      }
    }
    else {
        $doctypes = variable_get('forena_doc_formats', array());
    }

    // Verify that they are not disabled
    $supported_types = array_keys(Frx::documentTypes());
    $doctypes = array_intersect($doctypes, $supported_types);
    $doctypes = array_combine($doctypes, $doctypes);
    return $doctypes;
  }

  /**
   * Set document generation types that apply to this report.
   * Enter description here ...
   * @param unknown_type $docgenElements
   */
  public function setDocgen($doctypes) {
    $docgenElements = array();
    if ($selected = array_filter($doctypes)) {
      if ($selected) foreach ($selected as $key => $value) {
        if ($value) $docgenElements[] = array('type' => $key);
      }
    }

    $dom = $this->dom;
    $newDocs = $dom->createElementNS($this->xmlns, 'docgen');
    $this->verifyHeaderElements(array('docgen'));
    $dnode = $dom->getElementsByTagNameNS($this->xmlns, 'docgen')->item(0);
    $p = $dnode->parentNode;
    $p->replaceChild($newDocs, $dnode);
    $this->setFrxHeader('docgen', 'doc',
      $docgenElements,
      array('type'),
      NULL,
      'type'
      );
  }

  /**
   * Set report parameters
   * Enter description here ...
   * @param array $parmElements array
   */
  public function setParameters($parmElements) {
    $dom = $this->dom;
    $newParms = $dom->createElementNS($this->xmlns, 'parameters');
    $this->verifyHeaderElements(array('parameters'));
    $fnode = $dom->getElementsByTagNameNS($this->xmlns, 'parameters')->item(0);
    $p = $fnode->parentNode;
    $p->replaceChild($newParms, $fnode);

    $this->setFrxHeader('parameters', 'parm',
    $parmElements,
    array('id', 'label', 'require', 'desc', 'data_source', 'data_field', 'label_field', 'type', 'class', 'options'),
      'default');
  }


  public function addParameters($parms_to_add) {
    $parms_to_add = (array)$parms_to_add;
    foreach ($parms_to_add as $parm) {
      $parms= array();
      $parms[$parm] = array('id' => $parm );
      $this->setFrxHeader('parameters', 'parm',
      $parms,
      array('id', 'label', 'require', 'desc', 'data_source', 'data_field', 'type'),
        'default');
    }
  }

  /**
   * Set the report title
   * @param String $title
   */
  public function setTitle($title) {
    $dom = $this->dom;
    $head = $dom->getElementsByTagName('head')->item(0);
    $tnode = $dom->getElementsByTagName( 'title')->item(0);
    $node = $dom->createElement( 'title', $title);
    $head->replaceChild($node, $tnode);
  }

  /**
   * Set the report category
   * Enter description here ...
   * @param unknown_type $cateogry
   */
  public function setCategory($category) {
    $ret = array();
    $dom = $this->dom;
    $this->verifyHeaderElements(array('category'));
    $head = $dom->getElementsByTagName('head')->item(0);
    $cnode = $dom->getElementsByTagNameNS($this->xmlns, 'category')->item(0);
    $node = $dom->createElementNS($this->xmlns, 'category', $category);
    $head->replaceChild($node, $cnode);
  }

  public function getCategory() {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('category'));
    $cnode = $dom->getElementsByTagNameNS($this->xmlns, 'category')->item(0);
    return $cnode->textContent;
  }

  /**
   * Retrieve options element in array form
   */
  public function getOptions() {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('options'));
    $opts = $dom->getElementsByTagNameNS($this->xmlns, 'options')->item(0);
    $ret = array();
    // Simplexml is easier to work with
    $options = simplexml_import_dom($opts);
    foreach ($options->attributes() as $key => $value) {
      $ret[(string)$key] = (string)$value;
    }
    return $ret;
  }

  /**
   * Set the options list for the report
   * Enter description here ...
   * @param unknown_type $option_data
   */
  public function setOptions($option_data) {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('options'));
    $options = $dom->getElementsByTagNameNS($this->xmlns, 'options')->item(0);
    foreach ($option_data as $key => $value) {
      if ($value) {
        $options->setAttribute($key, $value);
      }
      else {
        if ($options->hasAttribute($key)) {
          $options->removeAttribute($key);
        }
      }
    }
  }

  /*
   * Retrieve menu data for the report
   */
  public function getMenu() {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('menu'));
    $opts = $dom->getElementsByTagNameNS($this->xmlns, 'menu')->item(0);
    $ret = array();
    // Simplexml is easier to work with
    $options = simplexml_import_dom($opts);
    foreach ($options->attributes() as $key => $value) {
      $ret[(string)$key] = (string)$value;
    }
    return $ret;

  }

  /*
   * Retrieve menu data for the report
  */
  public function getCache() {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('cache'));
    $opts = $dom->getElementsByTagNameNS($this->xmlns, 'cache')->item(0);
    $ret = array();
    // Simplexml is easier to work with
    $options = simplexml_import_dom($opts);
    foreach ($options->attributes() as $key => $value) {
      $ret[(string)$key] = (string)$value;
    }
    return $ret;

  }

  /*
   * Set menu data for the report
   * @param $menu_data array of key values for menu options.
   */
  public function setMenu($menu_data) {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('menu'));
    $options = $dom->getElementsByTagNameNS($this->xmlns, 'menu')->item(0);
    foreach ($menu_data as $key => $value) {
      if ($value) {
        $options->setAttribute($key, $value);
      }
      else {
        if ($options->hasAttribute($key)) {
          $options->removeAttribute($key);
        }
      }
    }
  }

  /*
   * Set Cache data for the report
  * @param $cache_data array of key values for menu options.
  */
  public function setCache($data) {
    $dom = $this->dom;
    $this->verifyHeaderElements(array('cache'));
    $options = $dom->getElementsByTagNameNS($this->xmlns, 'cache')->item(0);
    foreach ($data as $key => $value) {
      if ($value) {
        $options->setAttribute($key, $value);
      }
      else {
        if ($options->hasAttribute($key)) {
          $options->removeAttribute($key);
        }
      }
    }
  }


  /*
   * Set CSS Style Data
   * @param $menu_data array of key values for menu options.
   */
  public function setStyle($css) {
    $dom = $this->dom;
    //$this->verifyHeaderElements(array('menu'));
    $head = $dom->getElementsByTagName('head')->item(0);
    $nodes = $dom->getElementsByTagName('style');
    $style = $dom->createElement('style');
    $style->appendChild(new DOMText($css));
    if ($nodes->length==0) {
      $head->appendChild($style);
    }
    else {
      $head->replaceChild($style, $nodes->item(0));
    }

  }

  public function removeParm($id) {
    $dom = $this->dom;
    $xpq = $this->xpq;

    $pnode = $dom->getElementsByTagNameNS($this->xmlns, 'parameters')->item(0);
    $path = '//frx:parameters/frx:parm[@id="' . $id . '"]';

    $nodes = $xpq->query($path);
    if ($nodes->length) {
      foreach ($nodes as $node) $pnode->removeChild($node);
    }
  }

  /**
   * Make sure all xml elements have ids
   */
  private function parse_ids() {
    $i=0;
    if ($this->simplexml) {
      $this->simplexml->registerXPathNamespace('frx', FRX_NS);
      $frx_attributes = array();
      $frx_nodes = $this->simplexml->xpath('body//*[@frx:*]');

      if ($frx_nodes) foreach ($frx_nodes as $node) {
        $attr_nodes = $node->attributes(FRX_NS);
        if ($attr_nodes) {
          // Make sure every element has an id
          $i++;
          $id = 'forena-' . $i;

          if (!(string)$node['id']) {
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
   * Removes the attributes associated with forena-# that are added by forena.
   * There is no real reason to persist them as they can be added on later and they
   * are only created for wysiwyg compatibility.
   */
  private function cleanup_ids() {
    if ($this->simplexml) {
      $this->simplexml->registerXPathNamespace('frx', FRX_NS);
      $frx_attributes = array();
      $frx_nodes = $this->simplexml->xpath('body//*[@frx:*]');

      if ($frx_nodes) foreach ($frx_nodes as $node) {
        $attr_nodes = $node->attributes(FRX_NS);
        if ($attr_nodes) {
          if ((string)$node['id'] && strpos($node['id'], 'forena-')===0) {
            unset($node['id']);

          }
        }
      }
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
  public function save_attributes_by_id($attributes) {
    $rpt_xml = $this->simplexml;
    if ($attributes) foreach ($attributes as $id => $att_list) {
      $id_search_path = 'body//*[@id="' . $id . '"]';
      $fnd = $rpt_xml->xpath($id_search_path);
      if ($fnd) {
        $node = $fnd[0];

        // Start attribute replacement
        $frx_attributes = $node->Attributes(FRX_NS);

        foreach ($att_list as $key => $value) {
          if (!$frx_attributes[$key]) {
            if ($value) $node['frx:' . $key] = $value;
          }
          else {
            unset($frx_attributes[$key]);
            if ($value) $node['frx:' . $key] = $value;
          }
        }
      }
    }
  }

  /**
   * Delete a node based on id
   * @param unknown_type $id
   * @return unknown_type
   */
  public function deleteNode($id) {
    $path = 'body//*[@id="' . $id . '"]';
    $dom = $this->dom;
    $nodes = $this->simplexml->xpath($path);
    if ($nodes) {
      $node = $nodes[0];
      $dom=dom_import_simplexml($node);
      $dom->parentNode->removeChild($dom);
    }
    return $this;
  }

  /**
   * Scrape Data block configuration
   * This tries to introspect the frx:block configuration based
   * on the child nodes in the report by calling the
   * getConfig method on the block.
   */
  public function scrapeBlockConfig($id, &$config) {
    $template_class = "FrxMergeDocument";
    $path = "body//*[@id='$id']";
    $nodes = $this->simplexml->xpath($path);
    if ($nodes)  {
      $node = dom_import_simplexml($nodes[0]);
    }
    else {
      drupal_set_message(t('Could not find %s in report', array('%s' => $id)), 'error');
      return '';
    }

    $block_name = $node->getAttributeNS($this->xmlns, 'block');
    $class = $node->getAttribute("class");
    $templates = $this->templateOptions();
    $config['id'] = $id;
    foreach ($templates as $tclass => $desc) {
      if (strpos($class, $tclass)!==FALSE) {
        $template_class = $tclass;
        break;
      }
    }
    if ($template_class) {
      $c = Frx::Template($template_class);
      $config['class'] = $template_class;
      if ($c && method_exists($c, 'scrapeConfig')) {
        $c->initReportNode($node, $this->frxReport);
        $config = array_merge($config,$c->scrapeConfig());
      }
    }
    return $template_class;
  }



  /**
   * Apply a template based on the block id.
   * @param unknown $id
   * @param unknown $class
   * @param unknown $config
   */
  public function applyTemplate($id, $template_class, $config=array()) {
    $path = "body//*[@id='$id']";
    $nodes = $this->simplexml->xpath($path);
    if ($nodes) {
       $node = dom_import_simplexml($nodes[0]);
    }
    else {
      drupal_set_message(t('Could not find %s in report', array('%s' => $id)), 'error');
      return;
    }

    $block_name = $node->getAttributeNS($this->xmlns, 'block');
    $class = $node->getAttribute("class");

    $config['block'] = $block_name;
    $data= Frx::BlockEditor($block_name)->data($this->parms);
    $c = Frx::Template($template_class);
    if ($c) {
      $c->initReportNode($node, $this->frxReport);
      if (strpos($class, $template_class)===FALSE) {
        $c->resetTemplate();
      }
      $c->generate($data, $config);
    }
    else {
      drupal_set_message(t('Could not find template %s', array('%s' => $templateClass)), 'error');
    }
  }

  public function setEditorParms($parms) {
    $this->parms = $parms;
  }

  /**
   * Add a data blcok
   * @param unknown $block
   * @param unknown $class
   * @param unknown $id
   * @return FrxEditor
   */
  public function addBlock($block_name, $template_class, &$config,  $id='') {
    if (!$template_class) $template_class = 'FrxTable';
    $block_name = str_replace('.', '/', $block_name);
    if ($id) {
      $path = "body//*[@id='$id']";
      $nodes = $this->simplexml->xpath($path);
      if ($nodes) {
        $pnode = dom_import_simplexml($nodes[0]);
        $node = $this->dom->createElement('div');
        $pnode->appendChild($node);
      }
      else {
        drupal_set_message(t('Could not find %s in report', array('%s' => $id)), 'error');
        return;
      }
    }
    else {
      $nodes = $this->dom->getElementsByTagName('body');
      $pnode = $nodes->item(0);
      $node = $this->dom->createElement('div');
      $pnode->appendChild($node);
    }
    $this->frxReport->setReport($this->dom, $this->xpq, $this->edit);
    $config['block'] = $block_name;
    $b = Frx::BlockEditor($block_name, $this->frxReport->block_edit_mode);
    $data= $b->data($this->parms);
    $this->addParameters($b->tokens());
    $c = Frx::Template($template_class);
    if ($c) {
      $c->initReportNode($node, $this->frxReport);
      $c->generate($data, $config);
    }
    else {
      drupal_set_message(t('Could not find template %s', array('%s' => $template_class)), 'error');
    }
    return $this;
  }


  /**
   * Insert a data block before a node.
   * @param unknown $block
   * @param unknown $class
   * @param unknown $id
   * @return FrxEditor
   */
  public function prependBlock($block_name, $template_class='FrxTable', $config=array(),  $id) {
    $block_name = str_replace('.', '/', $block_name);
    $path = "body//*[@id='$id']";
    $nodes = $this->simplexml->xpath($path);
    if ($nodes) {
       $target = dom_import_simplexml($nodes[0]);
    }
    else {
      drupal_set_message(t('Could not find %s in report', array('%s' => $id)), 'error');
      return;
    }

    $node = $this->dom->createElement('div');
    $pnode = $target->parentNode;
    $pnode->insertBefore($node, $target);
    $config['block'] = $block_name;
    $b = Frx::BlockEditor($block_name, $this->frxReport->block_edit_mode);
    $data= $b->data($this->parms);
    $this->addParameters($b->tokens());
    $this->frxReport->setReport($this->dom, $this->xpq, $this->edit);
    $c = Frx::Template($template_class);
    if ($c) {
      $c->initReportNode($node, $this->frxReport);
      $c->generate($data, $config);
    }
    else {
      drupal_set_message(t('Could not find template %s', array('%s' => $template_class)), 'error');
    }
    return $this;
  }


  public function preview($parms = array()) {
    $r = $this->frxReport;
    if(strpos($this->report_name, '__') !== 0) $r->preview_mode = TRUE;
    return $this->report($parms, TRUE, TRUE);
  }

  /**
   * Load and render a report based on a drupal path.
   * In this function the arglist is used to get the full path to the report.
   * Pass parameters or NULL to use get /post parameters.
   *
   * @return unknown
   */
  function report($parms = array(), $print = FALSE, $no_cache=FALSE, $filename='') {
    global $user;
    global $language;
    $this->field_ids = array();
    $format = $this->desc['format'];
    if ($format == 'web' && $print) {
      drupal_add_css(drupal_get_path('module', 'forena') . '/forena.css');
    }
    $r = $this->frxReport;
    // Determine the data to get.
    if (!$parms) {
      $parms = array_merge($_GET, $_POST);
      unset($parms['q']);
    }
    // Removed this becuase we don't want it to nuke xml.
    //else $parms = (array)$parms;
    // Allow other modules to alter paramters.
    $this->alterParameters($parms);

    //check for default parameters
    $missing_parms = $r->processParameters($parms);
    Frx::Skin()->load($r->skin);
    Frx::Skin()->loadSkinFiles($this->report_name);
    $cached_data = FALSE;
    $cache = array();


    $o =  Frx::Document($format);
    $o->header($this->frxReport, $print);
    $this->frxReport->allowDirectWrite = $o->allowDirectOutput;
    if ($this->frxReport->allowDirectWrite && $filename) {
      $this->frxReport->file = fopen($filename, 'w');
    }

    Frx::Data()->push($parms, 'parm');


    if (!$missing_parms) {
      // Check for cache data
      if ($r->cache && !$r->preview_mode) {
        // Get file modification time for determining if cache needs to be rebuilt.
        $entry = @Frx::File()->getCacheEntry($this->desc['filename']);
        $cid = 'forena:report:' . $this->report_name . ':' . drupal_http_build_query($parms);
        if (@$r->cache['per_user'] && $user) $cid .= ':' . $user->uid;
        if (@$r->cache['per_doctype']) @$cid .= ':' . $format;
        $cache = cache_get($cid, 'cache');
        if (!$cache || (isset($r->cache['duration']) && $cache->expire < time()) || $cache->created < $entry->mtime || $no_cache) {
          $this->field_ids = array();
          $r->render($format);
          $time = null;
          if (isset($r->cache['duration'])) {
            try {
              $time = @new DateTime($r->cache['duration']);
            }
            catch (Exception $e) {
              drupal_set_message('Invalid Cache Duration', 'error', TRUE);
            }
            if ($time) $time = $time->getTimeStamp();
          }
          if (!$time) {
            $time = CACHE_PERMANENT;
          }
          $r->cache['content'] = $r->html;
          $r->cache['title'] = $r->title;
          cache_set($cid, $r->cache, 'cache', $time);
        }
        else {
          $r->html = $cache->data['content'];
          $r->title = $cache->data['title'];
        }
      }
      else {
        $r->render($format);
      }
    }
    else {
      $r->parametersForm(array('collapsible' => FALSE, 'collapsed' => FALSE));
    }

    $links = '';
    if ($r->preview_mode) {
      $r_link = str_replace('/', '.', $this->report_name);
      $parms = $_GET;
      unset($parms['q']);
      $links = '<div class="forena-edit-links">';
      $links .= $this->l_icon("reports/$r_link/edit/select-data/add-data", 'plus.svg', 'Add Data', $parms, t('Data'));
      $links .= '</div>';
    }

    $content = array(
      '#has_data' => $r->blocks_loaded,
      'parameter_form' => $r->parameters_form,
      'editorLinks' => array('#markup' => $this->editorLinks()),
      'links' => $this->documentLinks(),
      'content' => array('#markup' => $r->html . $links),
    );

    if ($this->frxReport->file) {
      $this->frxReport->writeBuffer();
      fclose($this->frxReport->file);
      return '';
    }

    if ($o) {
      $output = $o->render($r, $format,  $content);
      if ($filename) {
        file_put_contents($filename, $output);
        return '';
      }
      if ($print) {
        $printed = $o->output($output);
      }
      else {
        $printed = FALSE;
      }
      if (!$printed) {
        return  $output;
      }
    }
    else {
      return $content;
    }

    Frx::Data()->pop();
  }

  public function fieldLink($id, $value) {
    $o = '';
    if (!isset($this->field_ids[$id])) {
      $m_path = drupal_get_path('module', 'forena');
      $report_link = $this->report_link;
      $image = array(
          'path' => url("$m_path/icons/cog.svg"),
          'alt' => t('Configure'),
          'title' => t('Configure'),
          'class' => 'forena-field-config'
      );
      $image = theme('image', $image);
      $id = urlencode($id);
      $o= l($image, "$report_link/edit/edit-field/$id", array('html' => TRUE, 'attributes' => array( 'class' => 'forena-field-config')));
      $this->field_ids[$id] = 1;
    }
    return $o;

  }

  /**
   * Add foreach section links to blocks.
   * @param unknown $block_name
   * @param string $id
   * @param string $context
   * @return string
   */
  public function foreachLinks($block_name, $id='', $context='') {
    $o = '';
    $report_name = $this->report_name;
    // Add the block or ID link
    $o .= '<div class="forena-edit-links">'
        . $this->l_icon("reports/$report_name/edit/select-data/add-data/$id", 'plus.svg', 'Add Detail', null, t("Data"))
        . "</div>";
    return $o;
  }


  public function l_icon($link, $name, $alt, $context=array(), $label="") {
    $path = $name=='configure.png' ? 'misc' : drupal_get_path('module', 'forena') . '/icons';

    $image = array(
      'path' => file_create_url("$path/$name"),
    	'alt' => t($alt),
      'title' => t($alt),
    );
    $image = theme('image', $image);
    $options = array('query' => $context, 'html' => TRUE);
    return l($image . $label, $link, $options);
  }


  public function blockLinks($block_name, $frx, $attrs, $id='', $context = array()) {
    $o = '';
    if (!$context) $context = array();
    $parms = Frx::Data()->getContext('parm');
    if ($parms && is_array($context)) $context = array_merge($parms, $context);
    $class = @(string)$attrs['class'];
    $frx_class = strpos($class, 'Frx') !== FALSE;
    if ($frx_class) {
      $frx_class = FALSE;
      foreach ($this->templateOptions() as $key => $label) {
        if(strpos($class, $key) !==FALSE) $frx_class = TRUE;
      }
    }


    $block_tag = (string)$frx['block'];

    if ($frx_class || $block_tag ) {

      $block_label = (string)$frx['block'] ? $block_name : '#' . $id;
      $block_link = str_replace('/', '.', $block_name);
      $report_name = str_replace('/', '.' , $this->desc['base_name']);
      $b = Frx::RepoMan()->loadBlock($block_name);
      $options = array();
      if ($context) $options['query'] = $context;

      // Add the prepend link.
      if ($block_tag) {
        // If we have a block tag we're going to prepend another data block?
        $o .= '<div class="forena-edit-links">' . $this->l_icon("reports/$report_name/edit/select-data/prepend-data/$id", 'plus.svg', 'Insert Data', null, t("Data")).  "</div>";
      }
      else {
        //$o .= '<div class="forena-edit-links">' . $this->l_icon("reports/$report_name/edit/prepend-section/$block_link/$id", 'plus.svg', 'Add Data').  "</div>";
      }
      // Add the block or ID link
      $o .= '<div class="forena-edit-links">'
         . l($block_label, "reports/$report_name/edit/edit-data/$block_link/$id", $options )
         . ' ' . $this->l_icon("reports/$report_name/edit/delete-data/$id", 'minus.svg', 'Remove Data',  $context)
         . "</div>";
    }
    return $o;

  }

  /**
   * Generate the configuration form for the template for a class.
   * @param unknown $class
   * @param unknown $config
   */
  public function templateConfigForm($class, $config) {
    $form  = array();
    $c = Frx::Template($class);
    if ($c && method_exists($c, 'configForm')) {
      $form = $c->configForm($config);
    }
    return $form;
  }

  public function templateConfigFormValidate($class, &$config) {
    $c = Frx::Template($class);
    $errors = array();
    if ($c && method_exists($c, 'configValidate')) {
      $errors = $c->configValidate($config);
    }
    return $errors;
  }

  /**
   * Generate the list of possible templates.
   */
  public function templateOptions() {
     $controls = Frx::Controls();
     $templates = array();
     foreach ($controls as $control) {
       if (method_exists($control, 'generate') && isset($control->templateName)) {
         $templates[get_class($control)] = $control->templateName;
       }
     }
     asort($templates);
     return $templates;
  }

  public function editorLinks() {
    $o = '';
    $report_link = $this->report_link;
    if (!$this->edit && user_access('design any report')) {
    // Add the block or ID link
      $o .= '<div class="forena-editor-links">'
          . $this->l_icon("$report_link/edit", 'pencil.svg', 'Edit', (array)Frx::Data()->getContext('parm'));
      if (module_exists('locale')) $o .= $this->l_icon("$report_link/translations", 'file.svg', 'Translations');
      if (!$this->cache->include) $o .= $this->l_icon("$report_link/delete", 'minus.svg', 'Delete');
      $o .= "</div>";
    }
    return $o;
  }

  public function documentLinks() {
    $doctypes = array_keys(Frx::documentTypes());
    $links = array();
    $r = $this->frxReport;

    $formats = $r->formats ? $r->formats : array_filter(variable_get('forena_doc_defaults', array()));
    $parms = Frx::Data()->getContext('parm');
    foreach ($doctypes as $ext) {
      if (array_search($ext, $formats) !== FALSE) {
        $links[] = array('title' => strtoupper($ext), 'href' => $this->report_link . ".$ext", 'query' => $parms);
      }
    }
    if ($links) return array(
        '#theme' => 'links',
        '#links' => $links,
        '#attributes' => array('class' => array('forena-doclinks')));
    return '';
  }

  /**
   * Allow modules to alter the parameters of a report.
   * @param unknown_type $report_name
   * @param unknown_type $parms
   */
  function alterParameters(&$parms) {
    drupal_alter('forena_parameters', $this->report_name, $parms );
  }
}