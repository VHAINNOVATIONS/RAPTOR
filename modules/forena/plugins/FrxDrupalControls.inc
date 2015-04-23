<?php
// $Id$
/**
 * @file
 * Provides data blocks for native drupal connections using the default
 * drupal connections.
 *
 */
class FrxDrupalControls {

  /**
   * Implement custom SQL formatter to make sure that strings are properly escaped.
   * Ideally we'd replace this with something that handles prepared statements, but it
   * wouldn't work for
   *
   * @param unknown_type $value
   * @param unknown_type $key
   * @param unknown_type $data
   */
  public function format($value, $key,  $raw = FALSE) {
    if ($raw) return $value;
    $db = Database::getConnection('default');
    if ($db) {
      if ($value=='')
      $value = 'NULL';
      else {
        if (is_array($value)) {
          if ($value == array()) {
            $value = 'NULL';
          }
          else {
            // Build a array of values string
            $i=0;
            $val = '';
            foreach ($value as $v) {
              $i++;
              if ($i>1) {
                $val .= ',';
              }
              $val .=   $db->quote($v);
            }
            $value = $val;
          }
        }
        else {
          $value = trim($value);
          $value =    $db->quote($value) ;
        }
      }
    }
    return $value;
  }

  /**
   * @section
   * Formatters
   * Below here are formatting types
   */
  public function formats() {
    $formats = array(
                     'drupal_node_content' => 'Content from node id'
                     );
                     return $formats;
  }


  public function drupal_node_content($nid, $format) {
    $nid = @(int)$nid;
    $node = @node_load($nid, NULL, TRUE);
    if (!$node) {
      return '';
    }
    if ((strcasecmp($format, 'teaser') === 0) || (!$format)) {
      $format = 'teaser';
    }
    else {
      $format = 'full';
    }
    //If true, render the teaser
    //else render the body
    $output = theme('node', node_view($node, $format));
    return $output;

  }
}













