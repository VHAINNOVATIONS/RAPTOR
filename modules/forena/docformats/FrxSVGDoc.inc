<?php
/**
 * @file FrxSVGDoc
 * Embedded SVG Graph as it's own document.
 * @author davidmetzler
 *
 */
class FrxSVGDoc extends FrxDocument {

  public function __construct() {
    $this->content_type = 'image/svg+xml';
  }

  public function header($r, $print = TRUE) {
    $r->html = '';
    if ($print && $this->content_type) {
      header('Content-Type: ' . $this->content_type);
    }
  }

  public function render($r, $format, $options = array()) {
    $output = '';
    $doc = new DomDocument();
    $xml_body = '<html><body>' . $r->html . '</body></html>';
    $doc->formatOutput=FALSE;
    $doc->strictErrorChecking = FALSE;
    libxml_use_internal_errors(true);
    $doc->loadXML($xml_body);
    libxml_clear_errors();

    $xml = simplexml_import_dom($doc);
    $xml->registerXPathNamespace('__empty_ns', 'http://www.w3.org/2000/svg');
    if ($xml) $svg = $xml->xpath('//__empty_ns:svg');
    if ($xml && !$svg) $svg = $xml->xpath('//svg');
    if ($svg) {
      $output = $svg[0]->asXML();
    }
    else {
      $output = '<svg/>';
    }
    // Add in namespaces
    if (!strpos( $output, 'xmlns') ) {
      $output = str_replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"', $output);
    }
    return $output;
  }

  /**
   * Output SVG content type headers.
   */
  public function output(&$output) {
    if ($this->content_type) {
      print  $output;
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}