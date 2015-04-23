<?php
/**
 * @file FrxDocument.inc
 * Given a report, render the appropriate output given the document format.
 * @author davidmetzler
 *
 */
class FrxDocument {
  public $format;
  public $content_type='';
  public $charset = 'UTF-8';
  public $allowDirectOutput = FALSE;

  public function header($r, $print = TRUE) {
    $r->html = '';
    if ($this->content_type) {
      header('Content-Type: ' . $this->content_type . ' ;charset=' . $this->charset);
      header('Cache-Control:');
      header('Pragma:');
      $file_name = basename($_GET['q']);
      header('Content-Disposition: attachment; filename="' . $file_name . '"');
      header('Cache-Control: must-revalidate');
    }
  }

  public function loadCSSFiles($format) {
    // Add css files
    $css = '';
    foreach (Frx::Skin()->stylesheets as $type => $sheets) {
      foreach ($sheets as $sheet) {
        switch ($type) {
          case 'all':
          case $format:
            $css .= file_get_contents($sheet);
            break;
        }
      }
    }
    return $css;
  }

  // All document objects must implement this method.
  public function render($r, $format, $options = array()) {
    $header = '<h1>' . $r->title . '</h1>';
    $output = $header . $output;
    $css_text = '';
    $r_text = '';

    $stylesheets = Frx::Skin()->stylesheets;
    foreach ($stylesheets as $type => $sheets) {
      foreach ($sheets as $sheet) {
        switch ($type) {
          case 'all':
          case $this->format:
            $css_file[] = $sheet;
            break;
        }
      }
    }

    if ($css_files) foreach ($css_files as $css_file) {
      $css_text .= file_get_contents($css_file);
    }
    $options = array('css' => $css_text,
                     'docname' => str_replace(' ', '_', $title),
                     'xml' => $r_text,
                     'title' => $title,
    );

    $output = $this->generate_doc($format, $output, $options, $print);
    if ($format!='email') {
      print $output;
    }

  }

  public function output(&$output) {
      print  $output;
      return TRUE;
  }

  /**
   * Wrapper function for check output to default the right type.
   * @param $output
   */
  public function check_markup($output) {
    return check_markup($output, variable_get('forena_input_format', 'none'));
  }

  public function convertCharset($data, $default_encoding='UTF-8') {

    if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
      $parts = @explode(';', $_SERVER['HTTP_ACCEPT_CHARSET']);
      $parts = @explode(',', $parts[0]);
      $to_encoding=@$parts[0];
    }
    else {
      $to_encoding = $default_encoding;
    }
    if ($to_encoding!='UTF-8')  {
      $this->charset = $to_encoding;
      $data = mb_convert_encoding($data, $to_encoding, 'UTF-8');
    }
    return $data;
  }
  }
