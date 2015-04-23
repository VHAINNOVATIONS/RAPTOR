<?php
/**
 * @file FrxWebDoc.inc
 * Standard web document manager
 * @author metzlerd
 *
 */
class FrxWebDoc extends FrxDocument {
  private $title;
  public function render($r, $format, $content=array()) {
    // Set title based on report.
    if ($r->title) {
      $this->title = $r->title;
      //drupal_set_title(filter_xss($r->title));
    }
    // Add Style attribute from header

    // Add css files
    foreach (Frx::Skin()->stylesheets as $type => $sheets) {
      foreach ($sheets as $sheet) {
        switch ($type) {
          case 'all':
          case 'print':
          case 'screen':
            drupal_add_css($sheet, array('media' => $type));
            break;
        }
      }
    }

    // Add inline styles
    if (isset($r->rpt_xml->head->style)) {
      $sheet = (string)$r->rpt_xml->head->style;
      drupal_add_css($sheet, array('type' => 'inline'));
    }

    // Add javascript files
    foreach (Frx::Skin()->scripts as $script) {
      if (strpos($script, 'http:') === 0 || strpos($script, 'https:') === 0) {
        drupal_add_js($script, 'external');
      }
      else {
        drupal_add_js($script);

      }
    }

    //$output = theme('forena_web_report', $variables);
    return $content;
  }

  public function output(&$output) {
    if ($this->title) {
      drupal_set_title(filter_xss($this->title), PASS_THROUGH);
    }
    return FALSE;
  }


}