<?php
/**
 * @file FrxData.inc
 * The FrxData class holds all of the data contexts during the report rendering process.
 * The general idea is that during the report render, data objects are pushed on the stack with the id's of the block or foreach objects that invoke them.
 * Rendering controls may then get current data contexts, and push data onto the stack or pop data onto the stack. They may also use this method to get the current data
 * context from the stack.
 *
 * The static instance function of this object acts as a factory method that allows other code in forena to always be able to operate on the current context of the class.
 * @var unknown_type
 */
require_once('FrxContext.inc');
class FrxData extends FrxContext {

  public $id;
  private $cur_context;  // The data of the xml;
  private $cur_context_xml; //
  private $data_sources = array();
  private $data_stack = array();
  private $id_stack = array();

  /**
   * Constructor to initialize drupal site specific settings.
   *
   */
  public function __construct() {
    $site = array();
    global $language;
    global $user;
    global $theme_path;
    global $base_root;
    $site['base_path'] = base_path();
    $site['theme_path'] = base_path() . $theme_path;
    $site['theme_dir'] = &$theme_path;
    $site['base_url'] = &$base_root;
    $site['user_name'] = $user->uid ? $user->name : '';
    $site['uid'] = $user ? $user->uid : 0;
    $site['language'] = &$language;
    $site['args'] = arg();
    $site['page'] = base_path() . $_GET['q'];
    $site['destination'] = drupal_get_destination();
    $this->setContext('site', $site);
  }

  // For backward compatibility.
  public static function instance() {
    return Frx::Data();
  }

  /**
   * Return the current data context
   *
   */
  public function currentContext() {
    return $this->cur_context;
  }

  /**
   * Return current context as key value pairs
   * Enter description here
   */
  public function currentContextArray() {
    $data = $this->cur_context;
    if (is_array($data)) {
      return $data;
    }

    $ret = array();
    if (is_object($data)) {
      // Get attributes
      $ret = get_object_vars($data);
      if (method_exists($data, 'attributes')) {
        foreach ($data->attributes() as $key => $value) {
          $ret[$key] = (string)$value;
        }
      }

    }
    else {
      $ret = (array)$data;
    }
    return $ret;

  }

  /**
   * Provides an api to the {=xpath} syntax that can be used
   * to evaluat expressions such as sum and count in a report.  We
   * need to use the DOM object here, because this method isn't exposed
   * with simplexml.
   *
   * @param $xml
   * @param $path
   * @return unknown_type
   */
  protected function simplexml_evaluate($xml, $path) {
    if (!method_exists($xml, 'xpath')) return '';
    $dom_node = dom_import_simplexml($xml);
    $dom_doc = new DOMDocument('');
    $dom_node = $dom_doc->importNode($dom_node, TRUE);
    $dom_doc->appendChild($dom_node);
    // Do we also need to call AppendChild?
    $xpath = new DOMXpath($dom_doc);
    $ret = $xpath->evaluate($path, $dom_node);
    return $ret;
  }


  static public function arrayToXml($a, &$xml=NULL) {
    if (!$xml) $xml = new SimpleXMLElement('<root/>');
    $tag = '';
    foreach($a as $k => $v) {
      if (preg_match('/^[0-9\-\.]/', $k)) {
        if (!$tag) $tag = "element";
      }
      else {
        $tag = $k;
      }
      if (is_array($v) || is_object($v)) {
        $node = $xml->addChild($tag, '');
        if ($tag != $k) {
          $node['key'] = $k;
        }
        $node['type'] = is_object($v) ? 'object' : 'array';
        FrxData::arrayToXml($v, $node);
      }
      else {
        $node = $xml->addChild($tag, $v);
        $node['key'] = $k;
      }

      $tag =  preg_replace( '/[^a-zA-Z0-9]/', '_', (string) $k);
    }
    return $xml;
  }

  /**
   * Get the value from the data.
   * This is used by token_replace method to extract the data based on the path provided.
   * @param $data
   * @param $key
   * @return unknown_type
   */
  public function getValue($key, $context = '') {
    $retvar = '';
    // Default to theo current context
    $data = $this->currentContext();

    if ($context && $this->contextExists($context)) {

      $data = $this->getContext($context);

    }
    if (is_array($data) && !preg_match('/[\=\[\/\]\(\)]/', $key)) {

      $retvar = @$data[$key];
    }
    elseif (is_object($data) || is_array($data)) {
      if (is_array($data) || !method_exists($data, 'xpath')) {
        if ( !$this->cur_context_xml) $this->cur_context_xml = FrxData::arrayToXml($data);
        $data = $this->cur_context_xml;
      }
      if (strpos($key, '=')===0) {
        $retvar = $this->simplexml_evaluate($data, ltrim($key, '='));
      }
      else {
        $x ='';
        if (isset($data->$key)) {
          $x = $data->$key;
        }
        elseif (method_exists($data, 'xpath')) {
          $rows = @$data->xpath($key);
          if ($rows === FALSE) {
            drupal_set_message(t('Invalid field: "%s"', array('%s' => $key)), 'error', FALSE);
          }

          if ($rows) $x = $rows[0];
        }

        if ($x && is_object($x) && method_exists($x, 'asXML')) {
          $retvar = $x->asXML();
          // Check to see if there are child nodes
          // If so use asXML otherwise string cast.
          if ($retvar && strpos($retvar, '<')!==FALSE) {
            // Find the end of the first tag.
            $p = strpos($retvar, '>');
            $retvar = substr_replace($retvar, '', 0, $p+1);
            $p = strrpos($retvar, '<', -1);
            $retvar = substr_replace($retvar, '', $p, strlen($retvar) - $p);

          }
          else {
            $retvar = (string)$x;
          }
        }
        else {
          $retvar = &$x;
        }
      }
    }

    if (!is_array($retvar)) {
      if (is_object($retvar) && is_a($retvar, 'DOMNodeList')) {
        $retvar = $retvar->item(0);
        if ($retvar) ($retvar = trim($retvar->textContent));
      }
      else {
        $retvar = trim((string)$retvar);
      }
    }
    return $retvar;
  }

  /**
   * Allows override of a value for the current context.
   * @param String $key
   * @param String $value
   */
  public function setValue($key, $value, $context='') {
    if (is_array($this->cur_context)) {
      $this->cur_context[$key] = $value;
      if ($this->cur_context_xml) $this->cur_context_xml->$key = $value;
    }
    elseif (is_object($this->cur_context)) {
      if (strpos($key, '@')===0) {
        $this->cur_context[trim($key, '@')] = $value;
      }
      else {
        $this->cur_context->$key = $value;
      }
    }
  }

  /**
   * Push a data context onto the data stacks
   * to make sure that we can address these using an
   * appropriate syntax.  I think we don't need data_stack
   * but i'm holding it there in case we develop a "relative" path syntax.
   * @param $data
   * @param $id
   * @return unknown_type
   */
  public function push($data, $id='') {
    $this->data_stack[] = $this->cur_context;
    $this->id_stack[] = $this->id;
    $this->id = $id;
    $this->cur_context = $data;
    $this->cur_context_xml = '';

    if ($id) {
      /*    if (@is_array($this->data_sources[$id]) && is_array($data)) {
       $data = array_merge($this->data_sources[$id], $data);
       }
       */
      $this->data_sources[$id] = $data;
    }
  }

  public function setContext($id, &$data) {
    if (is_object($data)) {
      $this->data_sources[$id] = $data;
    }
    else {
      $this->data_sources[$id] = &$data;
    }
  }

  /**
   * Remove data from the data stack.  This will make data unavaiable
   * when we leave the context of the current nested reports.
   * @param $id
   * @return mixed
   */
  public function pop() {
    $this->id = array_pop($this->id_stack);
    $this->cur_context = array_pop($this->data_stack);
    $this->cur_context_xml = '';
  }

  /**
   * Determines whether an array context exists for the specified id.
   * Returns true if the key exists othewise false
   * @param string $id
   * @return boolean
   */
  public function contextExists($id) {
    if (array_key_exists($id, $this->data_sources)) {
      return TRUE;
    }
    else return FALSE;
  }

  /**
   * Return a data context by id
   * Enter description here ...
   * @param unknown_type $id
   */
  public function getContext($id) {
    return @$this->data_sources[$id];
  }

  public function dumpContext() {
    drupal_set_message(filter_xss('cur_context ' . $this->id . '<br/>Stack<br/><pre>' . print_r($this->cur_context, 1) . '</pre>'));
  }

  /**
   * Iterate the data based on the provided path.
   *
   * @param $path xpath to iterate xml on
   * @param $group grouping value
   * @param $sort Sort criteria
   */
  public function group($data,  $group='') {
    $rows = array();
    if (is_array($group)) $group = implode(' ', $group);
    $group = (string)$group;
    if (is_array($data) || is_object($data)) {
      foreach ($data as $row) {
        Frx::Data()->push($row, '_group');
        $gval = $this->teng->replace($group, TRUE);
        Frx::Data()->pop();
        $rows[$gval][] = $row;
      }
    }
    return $rows;
  }


}