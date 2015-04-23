<?php
/**
 * @file contains various methods for extending report
 * formating, layout, transformation and design.
 *
 */

class FrxControls {
  /**
   * @section
   * Below here are advertising methods
   */

  //date formats
  public function formats() {
    $formats = array('drupal_date_format' => 'Drupal Date',
                     'xhtml' => 'XHTML Decode of data',
                     'drupal_text_format' => 'Drupal Text Format',
                     'iso_date' => 'ISO Date',
                     'sprintf' => 'Sprintf() function',
                     'number' => 'Number',
                     'drupal_translation' => 'Translation Text (Drupal)',
                     'template' => 'Template (Field is a forena)',
                     'indentation' => 'Indentation',
                     'expression' => 'Expression (xpath)'
                     );
                     return $formats;
  }


  private function is_number($value) {
    $non_numeric_chars = trim($value, ' +-.,0123456789');
    // Determine if it contains +- in the interior
    // Zero is ok here bu
    $inner_symbols = FALSE;
    if (strpos($value, '+') || strpos($value, '-') || strpos($value, ' ')) $inner_symbols =  TRUE;
    return (empty($non_numeric_chars) && trim($value)!=='' && !$inner_symbols) ? TRUE : FALSE;
  }


  // Custom formats based on translation string
  public function drupal_translation($value, $format_str, $teng) {
    $field = '';
    if ($format_str) $field = '{' . $format_str . '}';
    $field = $teng->replace($field, NULL, TRUE);
    $vars = array();
    if ($field) {
      $vars = @unserialize($field);
      if (!$vars) $vars = array();
    }
    return t($value, $vars);
  }

  // Custom formats based on translation string
  public function drupal_text_format($value, $format_str, $teng) {
    $field = '';
    if ($format_str) $field = $format_str;
    $field = $teng->replace($field, NULL, TRUE);
    if ($field) {
      $format = $field;
    }
    else {
      $format = filter_default_format();
    }
    return check_markup(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), $format);
  }


  //Date format methods
  public function drupal_date_format($value, $format_str, $teng) {
    if (!$format_str) $format_str = 'small';
    switch ($format_str) {
      case 'medium':
        $type = $format_str;
        $format='';
        break;
      case 'small':
        $type = $format_str;
        $format='';
        break;
      case 'large':
        $type = $format_str;
        $format='';
        break;
      default:
        $type = 'custom';
        $format = $format_str;
    }
    if ($value) {
      $value = function_exists('format_date') ? format_date($value, $type, $format) : date($format, $value);
    }
    return $value;
  }

  public function iso_date($value, $format_str, $teng) {
    $date = ($value) ? strtotime($value) : '' ;
    return $this->drupal_date_format($date, $format_str, $teng);
  }

  public function xhtml($value, $format_str, $teng) {
    if ($value) {
      $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

      if ($format_str && filter_format_exists($format_str)) {
        $value = check_markup($value, $format_str);
      }
    }
    return $value;
  }

  public function sprintf($value, $format_str, $teng) {
    if ($value) {
      $value = sprintf($format_str, $value);
    }
    return $value;
  }

  public function number($value, $format_str, $teng) {
    // Determine number nubmer formatter from the string.
    $chars = str_replace(array('9', '0', '$'), '', $format_str);
    if (strlen($chars) > 1) {
      $th_sep = substr($chars, 0, 1);
      $dec_sep = substr($chars, 1, 1);
      $dec_pos = strrpos($format_str, $dec_sep);
      if ($dec_pos) {
        $dec = strlen($format_str) - $dec_pos -1;
      }
      else {
        $dec = 0;
        $dec_sep = '';
      }
    }
    elseif (strlen($chars) == 1) {
      $th_sep = substr($chars, 0, 1);
      $dec_sep = '';
      $dec = 0;
    }
    else {
      $dec_sep='';
      $th_sep = '';
      $dec = 0;
    }


    if ($value && $this->is_number($value))  $value = number_format($value, $dec, $dec_sep, $th_sep);
    return $value;
  }

  /**
   * Indicates the data in the field is a template that can be used by forena to format.
   * @param unknown $value
   * @param unknown $format_str
   * @param string $teng
   * @return Ambigous <string, The, mixed>
   */
  public function template($value, $format_str, $teng='', $default='') {
    if (!$value) {
      $value = $default;
    }
    if ($value) {
      $value = $teng->replace($value);
      $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
      if ($format_str && filter_format_exists($format_str)) {
        $value = check_markup($value, $format_str);
      }
    }

    return $value;
  }

  public function indentation($value, $format_str, $teng='') {
    if ($value) {
      $vars = array('size' => $value);
      $value = theme_indentation($vars);
    }
    else {
      $value = '';
    }
    return $value;
  }

  /**
   * @param $value
   * @param $format_str
   * @param string $teng
   * @return mixed
   * Return an xapth expression based on data in the
   */
  public function expression($value, $format_str, $teng='', $default='') {
    if ($value) {
      $value = $teng->replace($format_str);
      if ($value) $value = $teng->replace('{' . $value . '}');
    }
    return $value;
  }


}
