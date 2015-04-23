<?php
/*
* Implement a field parser that handles token replacements
*/
require_once 'FrxSyntaxEngine.inc';
class FrxFields {
  public function __construct($fields = array()) {
    $this->teng = new FrxSyntaxEngine(FRX_TOKEN_EXP, '{}', $this);
    $this->fields = $fields;
  }

  /**
   * REnder the fields from {} token syntax based on current data;
   * @param  $text The text to be replaced.
   * @param  $raw boolean should field formatting be skipped
   */
  public function render($text, $raw=FALSE) {
    return $this->teng->replace($text, $raw);
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
    $calc = '';
    // Extract the formatter for this field;
    if ($this->fields  && isset($this->fields[$key])) {
      extract($this->fields[$key]);
    }

    // Evaluate any calculations first.
    if ($calc) {
      $calc = $this->teng->replace($calc);
      if ($calc) $value = $this->teng->replace('{=' . $calc . '}');
    }

    if ($format && !$raw) {
      $value = FrxReportGenerator::$instance->format_data($value, $format, $format_str, $this->teng);
    }

    if (is_array($value)) {
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
        $opts = 'status=1';
        $options = "status=1";
        $attributes = array('onclick' =>
          'window.open(this.href, \'' . $target . '\', "' . $options . '"); return false;');
      }
      else {
        if ($target) $attributes['target'] = $target;
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
    return $value;
  }



}