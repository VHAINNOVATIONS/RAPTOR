<?php
/**
 * @file FrxRenderer.inc
 * Base class for Frx custom renderers
 * @author davidmetzler
 *
 */
class FrxRenderer {
  public $teng;           // Token replacement engine.
  public $reportDocDomNode; //A Dom node version of the element.  This is important if you want to walk text nodes.
  public $reportDocNode;  // SimpleXML Report Document Node -- The node of the report we are rendering
  public $frxAttributes;  // Frx Attributes of the node we are rendering.
  public $htmlAttributes;  // Html attributes of the node that we are rendering
  public $dataProvider;   // An FrxData instance that provides the data assiated with the report.
  public $blockName;      // The current data block in context
  public $blockParms;     // The parameters going into the block.
  public $name;
  public $id;
  public $format;
  public $frxReport;  // The report object being used.
  public $columns;
  public $numeric_columns;
  public $xmlns = 'urn:FrxReports';
  public $dom;   // Reports root document node.
  public $xpathQuery;
  public $input_format = 'full_html';
  public $doc_types = array();    // Specify the required document types to use this format.

  public function __construct() {
    $this->input_format = variable_get('forena_input_format', 'none');
  }

  /**
   * This function is called to give the renderer the current conetxt in report rendering.
   * It makes sure the renderer has the current DOM nodes dom documnent, and other attributes.
   * @param DOMElement $domNode
   * @param FrxReport $frxReport
   */
  public function initReportNode(DOMElement $domNode, FrxReport $frxReport) {
    $this->reportDocDomNode = $domNode;
    $this->dom = $domNode->ownerDocument;
    $this->dataProvider = Frx::Data();
    $this->reportDocNode = $node = simplexml_import_dom($domNode);
    $this->teng = $frxReport->teng;
    $this->frxReport = $frxReport;
    $this->format = $this->frxReport->format;
    $skin = Frx::Data()->getContext('skin');
    $this->settings = isset($skin['FrxReport']) ? $skin['FrxReport'] : array();
    $this->htmlAttributes = $node->attributes();
    $this->id = (string)$this->htmlAttributes['id'];
    $this->frxAttributes = $node->attributes(FRX_NS);
    unset($this->xpathQuery);
    $this->xpathQuery = new DOMXPath($this->dom);
  }

  /**
   * A helper function to allow replacement of tokens from inside a renderer wihout
   * needing to understand the object
   * @param unknown $text
   * @param string $raw_mode Field
   * @return Ambigous <unknown_type, text, mixed, string, unknown>
   */
  public function replaceTokens($text, $raw_mode=FALSE) {
    if (is_array($text)) {
      foreach($text as $k => $v) {
        $text[$k] = $this->replaceTokens($v, $raw_mode);
      }
      return $text;
    }
    elseif (is_object($text)) {
      foreach($text as $k => $v) {
        $text->$k = $this->replaceTokens($v, $raw_mode);
      }
      return $text;
    }
    else {
      return $this->teng->replace($text, $raw_mode);
    }
  }

  /**
   * Recursive report renderer
   * Walks the nodes rendering the report.
   */
  public function renderDomNode(DOMNode $dom_node, &$o) {
    // Write out buffer if we've gotten too big
    if ( $this->frxReport->allowDirectWrite && strlen($this->frxReport->html) > 100000) $this->frxReport->writeBuffer();
    $continue = TRUE;
    $is_data_block = FALSE;
    //$o = '';
    $node_type = $dom_node->nodeType;
    $settings = $this->settings;
    $context = Frx::Data()->currentContextArray();
    $last_block = $this->blockName;
    // Shortcut process a text node
    if ($node_type == XML_TEXT_NODE|| $node_type == XML_ENTITY_REF_NODE || $node_type == XML_ENTITY_NODE)
    {
      $text = $dom_node->textContent;
      if (!empty($settings['stripWhiteSpace'])) {
       $o .= trim( $this->teng->replace(htmlspecialchars($text, ENT_NOQUOTES)));
      }
      else {
        $o .= $this->teng->replace(htmlspecialchars($text, ENT_NOQUOTES));
      }
      return;
    }

    //Handle comment nodes
    if ($node_type == XML_COMMENT_NODE) {
      if (!empty($dom_node->length) &&
      !empty($dom_node->data)) {
        $text = $dom_node->data;
        // strip empty comments if configured to
        if (!empty($settings['stripEmptyComments'])) {
          $comment_text = trim($this->teng->replace($text));
          if ($comment_text === '') {
            return '';
          }
        }
        // comment markup is stripped so need to add it back in
        $o .= '<!--' . $this->teng->replace($text) . '-->';
        return;
      } else {
        return;
      }
    }

    // Continue processing non text nodes
    $node = simplexml_import_dom($dom_node);
    // Special catch to make sure we don't process bad nodes
    if (!is_object($node)) {
      return;
    }


    $frx = $node->attributes(FRX_NS);
    $include_root = !isset($frx['skip_root']) || !$frx['skip_root'];
    $elements = $dom_node->childNodes->length;

    // Check for invalid link processing.
    if (@(string)$frx['invalid_link']) {
      $old_link_mode = $this->link_mode;
      $this->frxReport->link_mode = (string)$frx['invalid_link'];
    }

    // Test to see if we have any nodes that contain data url
    $attrs = $node->attributes();
    $id = (string)$attrs['id'];
    $tag = $node->getName();
    $has_children = TRUE;
    // Preprocessing for detecting blank nodes
    if (@$settings['stripEmptyElements']) {
      $has_children = count($node->children()) > 0;
      $has_attributes = FALSE;
      foreach($attrs as $attr) {
        if (trim($this->teng->replace((string)$attr))) {
          $has_attributes = TRUE;
        }
      }
      if (!$has_children && !$has_attributes) {
        $has_text = trim($this->teng->replace((string)$dom_node->textContent)) !== '';
        if (!$has_text) {
          return;
        }
        else {
          $has_children = TRUE;
        }
      }
    }
    else {
      $has_children = count($node->children()) > 0 ||  trim($dom_node->textContent) !== '';
    }

    if ((string)$frx['block']) {
      // Determine the context
      $this->blockName = (string)$frx['block'];
      $this->blockParms = $context;
      if   ($this->frxReport->preview_mode) $o .= Frx::Editor()->blockLinks($this->blockName, $frx, $attrs, $id, $this->blockParms);

      // Now get the block
      $is_data_block = TRUE;
      $xml = $this->frxReport->getData((string)$frx['block'], $id, (string)$frx['parameters']);
      if ($xml) {
        Frx::Data()->push($xml, $id);
      }
      else {
        if ($id) Frx::Data()->setContext($id, $xml);
        return;
      }
    }

    //Implment if then logic
    if ((string)$frx['if']) {
      $cond = (string)$frx['if'];
      if (!$this->teng->test($cond)) return ;
    }

    // Preserve plain attributes
    $attr_text='';
    $tmp_attrs = array();
    if ($attrs) foreach ($attrs as $key => $value) {
      $attr_text .=  ' ' . $key . '="' . (string)$value . '"';
      $tmp_attrs[$key] = (string)$value;
    }

    // Preserve other namespaced attributes
    $ns_attrs = '';
    foreach ($node->getNamespaces() as $ns_key => $ns_uri) {
      if ($ns_key && $ns_key != 'frx') {
        foreach ($node->attributes($ns_uri) as $key => $value) {
          $ns_attrs .= ' ' . $ns_key . ':' . $key . '="' . (string)$value . '"';
        }
      }
    }

    // Determine if we have a custom renderer
    $renderer = (string)$frx['renderer'];
    // if we have a foreach in this node, we need to iterate the children

    if ((string)$frx['foreach'] ) {
      // Get proper XML for current data context.
      $path = $this->teng->replace((string)$frx['foreach'], TRUE);
      if ($path && strpos($path, '.')) {
        @list($context, $path) = explode('.', $path, 2);
        $data = Frx::Data()->getContext($context);
      }
      else {
        $data = Frx::Data()->currentContext();
      }

      if (is_object($data) || $path != '*') {
        if ((method_exists($data, 'xpath') || is_array($data))) {
          if (is_array($data)) $data = FrxData::arrayToXml($data);
          $nodes = $data->xpath($path);
        }
        else {
          $nodes = $data;
        }
      }
      else {
        $nodes = (array)$data;
      }

      // Sort values
      $sort = @(string)$frx['sort'];
      if ($sort) {
        $compare_type = @(string)$frx['compare'];
        $this->frxReport->sort($data, $sort, $compare_type);
      }

      //  values
      $group = @(string)$frx['group'];
      if ($group) {
        $opt = $this->mergedAttributes($node);
        $sums = (array)@$opt['sum'];
        $nodes = $this->frxReport->group($nodes, $group, $sums);
      }

      $i=0;

      //$tmp_attrs = (array)$attrs;
      if ($nodes) foreach ($nodes as $x) {
        if ($group) {
          Frx::Data()->setContext('group', $x[0]);
        }
        Frx::Data()->push($x, $id);
        $i++;
        $odd = $i & 1;
        $row_class = $odd ? 'odd' : 'even';
        $r_attr_text = '';
        if (trim($id)) {
          if (strpos($attrs['id'],'{')!== FALSE) {
            $id_attr = $this->teng->replace($attrs['id']);
          }
          else {
            if (!empty($settings['numericFrxForeachID'])) {
              $id_attr = $i;
            } else {
              $id_attr = $attrs['id'] . '-' . $i;
            }
          }
          $tmp_attrs['id'] =  $id_attr;
        }


        if (@!$settings['noHelperClasses']) {
          $tmp_attrs['class'] = trim($attrs['class'] . ' ' . $row_class);
        }

        foreach ($tmp_attrs as $key => $value) {
          $r_attr_text .=  ' ' . $key . '="' . (string)$value . '"';
        }

        if ($include_root) $o .= $this->teng->replace('<' . $tag . $r_attr_text . $ns_attrs .  '>', TRUE);
        foreach ($dom_node->childNodes as $child) {
          $this->renderDomNode($child, $o);
        }
        if ($i == 1 && strtolower($tag) == 'div' && $this->frxReport->preview_mode) {
          $o .= Frx::Editor()->foreachLinks($this->blockName, $attrs['id']);
        }
        if ($include_root) $o .= '</' . $tag . '>';
        Frx::Data()->pop();
      }
    }
    elseif ($continue) {
      if ($renderer) {
        // Implement custom renderer.
        $co = Frx::Controls($renderer);
        if ($co) {
          $co->initReportNode($dom_node, $this->frxReport);
          if (!empty($settings['stripWhiteSpace'])) {
            $o .= trim($co->render());
          }
          else {
            $o .= $co->render();
          }

        }
      }
      else {
        if ($has_children) {
          if ($include_root) $o .= $this->teng->replace('<' . $tag . $attr_text . $ns_attrs .  '>', TRUE);

          // None found, so render children
          foreach ($dom_node->childNodes as $child) {
            $this->renderDomNode($child, $o);
          }
          if ($include_root) $o .= '</' . $tag . '>';
        }
        else {
          $o .= $this->teng->replace('<' . $tag . $attr_text . $ns_attrs . '/>', TRUE);
        }
      }
    }
    if ($is_data_block && $continue) {
      Frx::Data()->pop();
    }

    // Restore link processing.
    if (@(string)$frx['invalid_link']) {
      $this->frxReport->link_mode = $old_link_mode;
    }
    $this->blockName = $last_block;
    return;
  }

  public function renderChildren(DOMNode $domNode, &$o) {
    foreach ($domNode->childNodes as $node) {
      $this->renderDomNode($node, $o);
    }
  }

  /**
   * Default Render action, which simply does normal forena rendering.
   * You can use renderDomNode at any time to generate the default forena
   * rendering methods.
   * @return Ambigous <string, unknown, unknown_type, text, mixed>
   */
  public function render() {
    $o = '';
    $this->renderDomNode($this->reportDocDomNode, $o);
    return $o;
  }

  /**
   * Helper function for convergint methods to a standard associated array.
   * @param unknown $attributes
   * @param unknown $key
   * @param unknown $value
   */
  public static function addAttributes(&$attributes, $key, $value) {
    $parts = explode('_', $key);
    $suff = '';
    if (count($parts) > 1) {
      $suff=array_pop($parts);
      $part = implode('_', $parts);
    }

    // If we have _0 _1 _2 attributes convert them into arrays.
    if ((int)$suff || $suff === '0') {
      $attributes[$part][] = (string)$value;
    }
    else {
      $attributes[$key] = (string)$value;
    }
  }

  /**
   * Starting at the current report node, this function removes all child nodes.  It aso
   * removes any FRX attributes on the current as well.
   * @return Ambigous <DOMElement, unknown>
   */
  public function resetTemplate() {
     $node = $this->reportDocDomNode;
     $this->removeChildren($node);
     $tag = $node->tagName;
     $new_node = $this->dom->createElement($tag);
     $this->frxAttributes = array();
     $parent = $node->parentNode;
     $parent->replaceChild($new_node, $node);
     $this->reportDocDomNode = $new_node;
     $this->initReportNode($new_node, $this->frxReport);
     return $node;
  }



  /**
   * Set FRX attributes.
   * @param DOMNode $node
   * @param unknown $attributes
   * @param unknown $frxattributes
   */
  public function setAttributes(DOMElement $node, $attributes, $frx_attributes) {
    if ($attributes) foreach ($attributes as $key => $value) {

      $node->setAttribute($key, $value);

    }

    // Iterate the value
    if ($frx_attributes) foreach ($frx_attributes as $key => $value) {

      // If the value is an array create multiple attributes
      // that are of the form key_1, key_2 .... etc.
      if (is_array($value)) {
        $i=0;
        $done=FALSE;
        while(!$done) {
          $v = '';
          if ($value) $v = array_shift($value);
          $i++;
          $k = $key . '_' . trim((string)$i);
          $node->setAttribute($k,$v);
          if (!$v) {
            $done = TRUE;
          }
        }
      }
      // A normal value.
      else {
        if ($value) $node->setAttributeNS($this->xmlns, $key, $value);
      }
    }

  }

  /**
   * Standard php array containing merged attributes
   * Enter description here ...
   */
  public function mergedAttributes($node = NULL) {
    if ($node) {
      $frx_attributes = $node->attributes($this->xmlns);
      $html_attributes = $node->attributes();
    }
    else {
      $frx_attributes = $this->frxAttributes;
      $html_attributes = $this->htmlAttributes;
    }
    $attributes = array();
    if ($frx_attributes) foreach ($frx_attributes as $key => $data) {
      FrxRenderer::addAttributes($attributes, $key, $data);
    }
    if ($html_attributes) foreach ($html_attributes as $key => $data) {
      FrxRenderer::addAttributes($attributes, $key, $data);
    }
    $skin_data = Frx::Data()->getContext('skin');
    $class = get_class($this);

    if (isset($skin_data[$class])) {
      $attributes = array_merge($skin_data[$class], $attributes);
    }
    $classes = class_parents($this);
    array_pop($classes);
    if ($classes) foreach ($classes as $class) {
      if (isset($skin_data[$class])) {
        $attributes = array_merge($attributes, $skin_data[$class]);
      }
    }
    return $attributes;
  }

  /**
   * Gives the token replaced attributes of a node.
   * @return multitype:NULL Ambigous <unknown_type, text, mixed, string, unknown>
   */
  public function replacedAttributes() {
    $attributes = array();
    if (isset($this->frxAttributes)) foreach ($this->frxAttributes as $key => $data) {
      $attributes[$key] =  $this->teng->replace((string)$data, TRUE);
    }
    if (isset($this->htmlAttributes)) foreach ($this->htmlAttributes as $key => $data) {
      $attributes[$key] = $this->teng->replace((string)$data, TRUE);
    }
    return $attributes;
  }

  /**
   * Return the inside xml of the current node
   *
   */
  public function innerXML() {
    $xml = $this->reportDocNode;
    $tag = $xml->getName();
    $text = '';
    if (is_object($xml) && is_object($xml->$tag)) {
      $text = $xml->asXML();
      $text = preg_replace("/<\/?" . $tag . "(.|\s)*?>/", "", $text);
    }
    return $text;
  }

  /**
   * Render a drupal form in a forena template
   * @param $form array
   */
  public function drupalRender($form) {
    $output = drupal_render($form);
    return $output;
  }

  /**
   * Returns the section
   * Enter description here ...
   */
  public function configForm($config) {
    $form_ctl = array();
    $form_ctl['header'] = array(
        '#type' => 'text_format',
        '#title' => t('Header'),
        '#rows' => 5,
        '#format' => $this->input_format,
        '#default_value' => @$config['header']['value'],
       // '#ajax' => $this->configAjax('blur')
    );

    return $form_ctl;
  }

  /**
   * Default configuration validator. Simply validates header and footer attributes.
   * @param unknown $config
   * @return multitype:Ambigous <The, string, A, Optional>
   */
  public function configValidate(&$config) {
    return $this->validateTextFormats($config, array('header', 'footer'));
  }

  /**
   * Helper funciton for validating text_format type controls.
   * @param unknown $config
   * @param unknown $elements
   * @return multitype:Ambigous <The, string, A, Optional>
   */
  public function validateTextFormats(&$config, $elements) {
      $temp_dom = Frx::tempDOM();

     $errors = array();
     foreach ($elements as $element) if (isset($config[$element]['value'])) {
         if ($config[$element]['value']) {
             $body_xml = '<?xml version="1.0" encoding="UTF-8"?>
           <!DOCTYPE root [
           <!ENTITY nbsp "&#160;">
           ]><html xmlns:frx="' . $this->xmlns . '"><body>' . $config[$element]['value'] . '</body></html>';
           @$temp_dom->loadXML($body_xml);
           if (!$temp_dom->documentElement) {
             $errors[$element] = t('Invalid XHTML in %s', array('%s' => $element));
           }
        }
     }
     return $errors;
  }

  /**
   * Default method for extracting configuration information from the template.
   * This just scrapes teh current child html as the template.
   */
  public function scrapeConfig() {
    $content = array();
    $this->extractTemplateHTML($this->reportDocDomNode, $content);
    return $content;
  }

  /**
   * Generate ajax configuration attributes for use in template configurtion forms.
   * @param string $event
   * @return multitype:string
   */
  public function configAjax($event='') {
    $ajax = array(
        'callback' => 'forena_template_callback',
        'wrapper' => 'forena-template-wrapper',
      );
    if ($event) $ajax['event'] = $event;
    return $ajax;
  }

  /**
   * Add a node to the existing dom element with attributes
   * @param $cur_node DOMNode Parent node
   * @param $indent Integer Text indentation.
   * @param $tag String Tag name
   * @param $value String text value of the element
   * @param $attributes array Html attributes to add
   * @param $frx_attributes array FRX attributes.
   * @return void|unknown
   */
  function addNode($cur_node, $indent, $tag='div', $value='', $attributes=array(), $frx_attributes=array()) {
    $dom = $this->dom;
    if (!$cur_node) {
      return;
    }

    if ($indent) {
      $tnode = $dom->createTextNode("\n" . str_repeat(' ', $indent));
      $cur_node->appendChild($tnode);
    }
    $node = $this->dom->createElement($tag, $value);
    $cur_node->appendChild($node);
    $this->setAttributes($node, $attributes, $frx_attributes);
    $cur_node->appendChild($this->dom->createTextNode(""));
    return $node;
  }


  /**
   * Append a textual XHTML fragment to the dom.
   * We do not use the DOMDocumentFragment optioin because they don't properly import namespaces. .
   * @param DOMNode $node
   * @param unknown $xml_string
   * @param string $ctl_name
   */
  function addFragment(DOMNode $node, $xml_string, $ctl_name = 'Header') {
    if (is_array($xml_string) && isset($xml_string['value'])) {
      $xml_string = $xml_string['value'];
    }
    if ($xml_string && !is_array($xml_string)) {
      $temp_dom = Frx::tempDOM();
      $body_xml = '<?xml version="1.0" encoding="UTF-8"?>
         <!DOCTYPE root [
         <!ENTITY nbsp "&#160;">
         ]><html xmlns:frx="' . $this->xmlns . '"><body>' . $xml_string . '</body></html>';
       try {
        $temp_dom->loadXML($body_xml);
      }
      catch (Exception $e) {

        Frx::error('Malformed report body', '<pre>' . $e->getMessage() .
        $e->getTraceAsString() . '</pre>');
      }
      $body = $temp_dom->getElementsByTagName('body')->item(0);
      foreach($body->childNodes as $sub) {
        $new_node = $this->dom->importNode($sub, TRUE);
        $node->appendChild($new_node);
      }
      if ($node->nodeType == XML_ELEMENT_NODE) {
        $xmlnode = simplexml_import_dom($node);
        $frx_nodes = $xmlnode->xpath('//*[@frx:*]');
        if (!$frx_nodes) {
          $this->frxReportsave_attributes_by_id();
        }
      }

    }
  }

  /**
   * Extract a list of columns from the data context.
   * @param $xml SimpleXMLElement The xml data
   * @param string $path
   * @return multitype:|multitype:string mixed
   */
  public function columns($xml, $path='/*/*') {
    //create an array of columns
    if (!is_object($xml)) return array();
    // Use xpath if possible otherwise iterate.
    if (method_exists($xml, 'xpath')) {
      $rows = $xml->xpath($path);
    }
    else {
      $rows = $xml;
    }
    $column_array = array();
    $numeric_columns = array();
    foreach ($rows as $columns) {
      foreach ($columns as $name => $value) {
        $label = str_replace('_', ' ', $name);
        $column_array[$name] = $label;
        if (is_numeric((string)$value)) {
          $numeric_columns[$name] = $label;
        }
        else {
          if (isset($numeric_columns[$name])) unset($numeric_columns[$name]);
        }
      }
      if (is_object($xml) && method_exists($xml, 'attributes')) {
        foreach ($xml->attributes() as $name => $value) {
          $column_array['@' . $name] = '@' . $name;
        }
      }
    }
    $this->columns = $column_array;
    $this->numeric_columns = $numeric_columns;
    return $column_array;
  }

  /**
   * Add a text node to the current dom node.
   * @param unknown $cur_node
   * @param unknown $text
   * @return unknown
   */
  function addText($cur_node, $text) {
    $dom = $this->dom;
    $tnode = $dom->createTextNode($text);
    $cur_node->appendChild($tnode);
    return $tnode;
  }

  /**
   *
   * Extract a configuration var removing it from the array
   * @param string $key attribute key for the data being extracted.
   * @param array $config
   */
  public function extract($key, &$config) {
    $value = '';
    if (isset($config[$key])) {
      $value = $config[$key];
      unset($config[$key]);
    }
    return $value;
  }

  /**
   *
   * Generate generic div tag.
   * @param unknown_type $config
   * @param unknown_type $text
   */
  public function blockDiv(&$config, $text='') {
    $node = $this->reportDocDomNode;
    $heading = $this->extract('heading', $config);
    $descr = $this->extract('description', $config);
    $include = $this->extract('include', $config);
    $block = $this->extract('block', $config);
    $foreach = $this->extract('foreach', $config);
    $id = $this->extract('id', $config);
    if (!$id) {
      $id = $this->idFromBlock($block);
    }
    $class = $this->extract('class', $config);
    if (!$class) $class = get_class($this);
    $frx_attributes = array(
        'block' => $block,
    );
    if ($foreach) $frx_attributes['foreach'] = $foreach;
    $attributes = array(
        'id' => $id,
        'class' => $class,
    );

    $this->setAttributes($node, $attributes, $frx_attributes);
    if ($heading) {
      $this->addNode($node, 4, 'h2', $heading);
    }
    if ($descr) {
      $this->addNode($node, 4, 'p', $descr);
    }
    if ($include) {
      $src = 'reports/' . str_replace('/', '.', $include);
      $this->addNode($node, 4, 'div', NULL, NULL, array('renderer' => get_class($this), 'src' => $src));
    }

    return $node;
  }

  /**
   * Generate the template from the configuration.
   * @param string $data_block
   * @param SimpleXMLElement $xml
   * @param array $config
   */
  public function generate($xml , &$config) {
    if (!@$config['foreach']) $config['foreach']='*';

    $columns = $this->columns($xml);
    $text = '';
    if ($columns) foreach ($columns as $col => $label) {
      $text .= ' {' . $col . '}';
    }
    $this->blockDiv($config, $text);

  }

  /**
   * Simple function to get id from node.
   * @param unknown $block
   * @return mixed
   */
  public function idFromBlock($block) {
    $parts = explode('/', $block);
    $id = str_replace('.', '_', array_pop($parts));
    return $id;
  }

  /**
   * Sets the first child element to a node and returns it.
   * IF the node
   * @param DOMNode $node
   * @param unknown $tag
   * @param unknown $indent
   * @return Ambigous <string, void, DOMElement>
   */
  public function setFirstNode(DOMElement $parent_node,  $indent=0, $tag='div',  $value='', $attributes=array(), $frx_attributes=array()) {
    $dom = $this->dom;
    if (!$parent_node) {
      return;
    }
    $nodes = $parent_node->getElementsByTagName($tag);
    if ($nodes->length) {
      $node = $nodes->item(0);
      $this->setAttributes($node, $attributes, $frx_attributes);
    }
    else {
      $node = $this->addNode($parent_node, $indent, $tag, $value, $attributes, $frx_attributes);
    }
    return $node;
  }

  /**
   * Rmove all the children of a dom node in the current report.
   * @param DOMNode $node
   */
  public function removeChildren(DOMNode $node) {
    while (isset($node->firstChild) && $node->firstChild->nodeType < 9) {
      $this->removeChildren($node->firstChild);
      $node->removeChild($node->firstChild);
    }
  }

  /**
   * Convert XML to key value pairs.
   * This is used in support of graping to get specific key/value pairs in
   * an array format suitable for passing off to php libraries.
   * @param unknown $path
   * @param unknown $data_path
   * @param string $label_path
   * @param string $pairs
   * @return multitype:multitype:number  Ambigous <unknown_type, text, mixed, string, unknown>
   */
  public function xmlToValues($path, $data_path, $label_path='', $pairs = FALSE) {
    $do = Frx::Data();
    $data = $do->currentContext();
    $values = array();
    if (is_object($data)) {
      $nodes = $data->xpath($path);
      if ($nodes) foreach ($nodes as $i => $node) {
        $do->push($node, $this->id);

        $val = $this->teng->replace($data_path, TRUE);
        if ($label_path) {
          $key = strip_tags($this->teng->replace($label_path, FALSE));
        }
        else {
          $key = $i;
        }
        if ($pairs && $label_path) {
          $values[] = array(floatval($key), floatval($val));
        }
        else {
          $values[$key] = $val;
        }


        $do->pop();
      }
    }
    return $values;
  }

  /**
   * Removes all chidren from the dome node expect those with a tagname specified by the
   * the $tags argurment
   * @param $node Parent node to remove from
   * @param $tags array Tags to eingore
   */
  public function removeChildrenExcept(DOMNode $node, $tags = array('table')) {
    foreach ($node->childNodes as $child) {
      if ($child->nodeType != XML_ELEMENT_NODE || array_search($child->tagName, $tags)===FALSE) {
        $this->removeChildren($child);
        $node->removeChild($child);
      }
    }
  }

  /**
   * Get the textual representations of html for the configuration engine.
   */
  public function extractSource(DOMNode $node) {
    $content = '';
    switch ($node->nodeType) {
      	case XML_ELEMENT_NODE:
      	  $content = $this->dom->saveXML($node);
      	  break;
      	case XML_TEXT_NODE:
      	case XML_ENTITY_REF_NODE:
      	case XML_ENTITY_NODE:
      	case XML_ATTRIBUTE_NODE:
      	  $content = $child->textContent;
      	  break;
      	case XML_COMMENT_NODE:
      	  $content = '<!--' . $node->data . '-->';
      	  break;
    }
    return $content;
  }


  /**
   * Get the textual representations of html for the configuration engine.
   */
  public function extractChildSource(DOMNode $node) {
    $content = '';
    foreach ($node->childNodes as $child) {
      switch ($child->nodeType) {
      	case XML_ELEMENT_NODE:
      	  $content .= $this->dom->saveXML($child);
      	  break;
      	case XML_TEXT_NODE:
      	case XML_ENTITY_REF_NODE:
      	case XML_ENTITY_NODE:
      	  $content .= $child->textContent;
      	  break;
      	case XML_COMMENT_NODE:
      	  $content .= '<!--' . $child->data . '-->';
      	  break;
      }
    }
    return $content;
  }

  /**
   * Get the textual representations of html for the configuration engine.
   */
  public function extractTemplateHTML(DOMNode $node, &$content, $tags = array()) {
    $this->frxReport->get_attributes_by_id();
    $cur_section = 'header';
    if (!$content) $content = array('header' => '', 'content' => '', 'footer' => '');
    if (!$tags) $cur_section = 'content';
    foreach ($node->childNodes as $child) {
      switch ($child->nodeType) {
      	case XML_ELEMENT_NODE:
      	  if (array_search($child->tagName, $tags)!==FALSE) {
      	    $cur_section = 'content';
      	  }
      	  elseif ($tags && $cur_section == 'content') {
      	    $cur_section = 'footer';
      	  }
      	  @$content[$cur_section]['value'] .= $this->dom->saveXML($child);
      	  break;
      	case XML_TEXT_NODE:
      	case XML_ENTITY_REF_NODE:
      	case XML_ENTITY_NODE:
      	  @$content[$cur_section]['value'] .= $child->textContent;
      	  break;
      	case XML_COMMENT_NODE:
      	  @$content[$cur_section]['value'] .= '<!--' . $child->data . '-->';
          break;
      }
    }
  }

  /**
   * Extracts the inner html of all nodes that match a particular xpath expression.
   * @param $query string xpath query expression
   * @param DOMNode $context Dom node to use as source
   * @param $concat boolean Set to false to return an array with the source for each element matching the path.
   * @return String XHTML source
   */
  public function extractXPathInnerHTML($query, DOMNode $context, $concat = TRUE) {
    $result = $this->xpathQuery->query($query, $context);
    $length = $result->length;
    $content = array();
    for ($i=0; $i<$length; $i++) {
      $content[] = $this->extractChildSource($result->item($i));
    }
    if ($concat) $content = implode('', $content);
    return $content;
  }

  /**
   * Extracts the inner html of all nodes that match a particular xpath expression.
   * @param $query string xpath query expression
   * @param DOMNode $context Dom node to use as source
   * @param $concat boolean Set to false to return an array with the source for each element matching the path.
   * @return String XHTML source
   */
  public function extractXPath($query, DOMNode $context, $concat = TRUE) {
    $result = $this->xpathQuery->query($query, $context);
    $length = $result->length;
    $content = array();
    for ($i=0; $i<$length; $i++) {
      $content[] = $this->extractSource($result->item($i));
    }
    if ($concat) $content = implode('', $content);
    return $content;
  }

  /**
   * Puts attributes back in array format prior to rendering.
   * @param unknown $attributes
   */
  public function arrayAttributes($attributes) {
    $remove_attrs = array();
    foreach ($attributes as $key => $value) {
      if (is_array($value)) {
        $i=0;
        foreach($value as $idx => $v) {
          $i++;
          $new_key = $key . '_' . trim($i);
          $attributes[$new_key] = (string)$v;
        }
        $remove_attrs [] = $key;
      }
    }

    foreach ($remove_attrs as $key) {
      unset($attributes[$key]);

    }
    return $attributes;
  }

  // Helper sort functoin for sorting config by weight.
  public static function weight_sort_comp($a, $b) {
    if ($a['weight'] == $b['weight']) return 0;
    return $a['weight'] < $b['weight'] ? -1 : 1;
  }

  /**
   * Sort a column list by weight.
   * @param unknown $columns
   */
  public function weight_sort(&$entries) {
    if ($entries) uasort($entries, 'FrxRenderer::weight_sort_comp');
  }
}