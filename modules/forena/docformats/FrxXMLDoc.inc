<?php
/**
 * @file FrxHtmlDoc
 * Straight XML document with no wrapping theme.
 * @author davidmetzler
 *
 */
class FrxXMLDoc extends FrxDocument {
  public $root_tag = 'div';
  public $root_attributes = array();

  public function __construct() {
    $this->allowDirectOutput = TRUE;
    $this->content_type = 'application/xml';
    $skin = Frx::Data()->getContext('skin');
    if (isset($skin['FrxXMLDoc']['rootElementName'])) {
      $this->root_tag = $skin['FrxXMLDoc']['rootElementName'];
      if ($this->root_tag == 'none') $this->root_tag = '';
    }
    if (isset($skin['FrxXMLDoc']['rootElementAttributes'])) {
      if (is_array($skin['FrxXMLDoc']['rootElementAttributes'])) {
        $this->root_attributes = $skin['FrxXMLDoc']['rootElementAttributes'];
      }
    }
  }

  public function header($r, $print = TRUE) {
    if ($print) {
      header('Content-Type: ' . $this->content_type);
      header('Cache-Control:');
      header('Pragma:');
      header('Cache-Control: must-revalidate');
    }
    $r->html =  '<?xml version="1.0"?>' . "\n";
    $opening_tag = $this->_build_opening_root_tag_contents();
    if ($this->root_tag) {
      $r->html .= "<$opening_tag>";
    }

  }

  public function render($r, $format, $options = array()) {
    $ending_tag = $this->root_tag;
    if ($ending_tag) {
      $r->html .= "</$ending_tag>";
    }
    return $r->html;
  }

  private function _build_opening_root_tag_contents() {
    $tag_contents = $this->root_tag;
    if (isset($this->root_attributes) && is_array($this->root_attributes)) {
      foreach ($this->root_attributes as $attr_key => $attr_val) {
        if (!empty($attr_key) && (string)$attr_val !=='') {
          $attr_val = addslashes($attr_val);
          $tag_contents .= " " . $attr_key . "=" . "'$attr_val'";
        }
      }
    }
    return $tag_contents;
  }
}
