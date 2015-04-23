<?php
/**
 * @file FrxWordDoc.inc
 * Word document exporter.
 * @author davidmetzler
 *
 */
class FrxWordDoc extends FrxDocument {
  public function __construct() {
    $this->content_type = 'application/msword';
  }

  public function render($r, $format, $options = array()) {
    $css = $this->loadCSSFiles($format);
    $output = '<html><head>';
    $output .= '<meta http-equiv="Content-Type" content="text/html"/>';
    if (isset($r->rpt_xml->head->style)) {
      $output .= '<style type="text/css">';
      $output .= $css;
      if (isset($r->rpt_xml->head->style)) {
        $sheet = (string)$r->rpt_xml->head->style;
        $output .= $sheet;
      }
      $output .= '</style>';
    }
    $output .= '<title>' . $r->title . '</title></head><body class="forena-report">' . $r->html . '</body></html>';
    return $output;
  }

  public function output(&$output) {
    $output = $this->convertCharset($output, 'windows-1252');
    parent::output($output);
    return TRUE;
  }
}
