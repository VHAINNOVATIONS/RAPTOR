<?php
/**
 * @file FrxPrincePDF
 * PDF document via Prince XML
 * @author davidmetzler
 *
 */
class FrxPrincePDF extends FrxDocument {
  private $p;

  public function __construct() {
    include_once('sites/all/libraries/prince/prince.php');
    $this->content_type='application/pdf';
    $prince_path = variable_get('forena_pdf_prince_path', '/usr/local/bin/prince');
    if (class_exists('Prince') && forena_library_file('prince')) {
      $this->p = new Prince($prince_path);
    }

  }


  public function render($r, $format, $options = array()) {

    $disable_links = variable_get('forena_pdf_disable_links', TRUE);
    $html = $r->html;
    if ($disable_links) {
      $html = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $html);
    }

    $link_class = $disable_links ? 'prince-disable-links': '';
    $output = '<html><head>';
    $output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
    if (@$options['css'] || isset($r->rpt_xml->head->style)) {
      $output .= '<style type="text/css">';
      $output .= @$options['css'];
      if (isset($r->rpt_xml->head->style) || isset($r->rpt_xml->head->style)) {
        $sheet = (string)$r->rpt_xml->head->style;
        $output .= $sheet;
      }
      $output .= '</style>';
    }

    $output .= '<title>' . $r->title . "</title></head><body class='forena-report $link_class'><h1>" . $r->title . '</h1>' . $html . '</body></html>';

    // Generate the document
    if ($this->p) {
      $p = $this->p;
      foreach (Frx::Skin()->stylesheets as $type => $sheets) {
        foreach ($sheets as $sheet) {
          switch ($type) {
            case 'all':
            case 'print':
            case 'screen':
            case 'pdf':
              $p->addStyleSheet($sheet);
              break;
          }
        }
      }
      $msg = array();
      $pdf_file = tempnam(file_directory_temp(), 'prince_pdf');
      if ($p->convert_string_to_file($output, $pdf_file, $msg)) {
        $output = file_get_contents($pdf_file);
      }
      else {
        drupal_set_message('Could not generate PDF File', 'Error');
        watchdog('pdf error', print_r($msg,1));
        $output = '';
      }
      // We don't care if this fails because it's temproary.
      @unlink($pdf_file);
      return $output;
    }
    else {
      drupal_set_message(t('Prince XML Not Properly Installed'), 'error');
      return ('');
    }
  }

  public function header($r, $print = FALSE) {
    $r->html = '';
    if ($print) {
      header('Content-Type: ' . $this->content_type);
      header('Cache-Control:');
      header('Pragma:');
      header('Cache-Control: must-revalidate');
    }
  }
}
