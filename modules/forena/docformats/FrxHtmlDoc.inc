<?php
/**
 * @file FrxHtmlDoc
 * Straight html document with no wrapping theme.
 * @author davidmetzler
 *
 */
class FrxHtmlDoc extends FrxDocument {
  public function render($r, $format, $options = array()) {
    $css = $this->loadCSSFiles($format);
    $output = '<html><head>';
    $output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
    $title = $r->title;
      // Add inline styles
    if ($css || isset($r->rpt_xml->head->style)) {
      $output .= '<style type="text/css">';
      $output .= $css;
      if (isset($r->rpt_xml->head->style)) {
        $sheet = (string)$r->rpt_xml->head->style;
        $output .= $sheet;
      }
      $output .= '</style>';
    }
    $output .= '<title>' . $r->title . '</title></head><body class="forena-report"><h1>' . $r->title . '</h1>' . $r->html . '</body></html>';
    return $output;
  }
}