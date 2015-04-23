<?php
/**
 * Implementation of a drupal node load function
 * @author baileys
 */
class FrxDrupalNode {
  public $access = 'access content';
  public $tokens = array('nid', 'vid');

  public function data($parms = array()) {
    $nid = isset($parms['nid']) ? $parms['nid'] : 1;
    $vid = isset($parms['vid']) ? $parms['vid'] : NULL;

    // No node ID means don't try and laod a node.
    if (!$nid && !$vid) return '';
    $node = node_load($nid, $vid);
    $return = new SimpleXMLElement('<node/>');
    $lang = isset($node->language) ? $node->language : 'und';
    $display = isset($parms['display']) ? $parms['display'] : 'default';
    if ($node) foreach ($node as $key => $val) if ($val) {
      if (strpos($key, 'field_') === 0) {
        //$fields = field_get_items('node', $node, $key);
        $field = field_view_field('node', $node, $key, $display);
        $field['#theme'] = array('forena_inline_field');
        $value  = drupal_render($field);
        $f = $return->addChild($key, $value);
        if (isset($field['#field_type'])) $f['type'] = $field['#field_type'];
        if (isset($field['#field_name'])) $f['name'] = $field['#field_name'];

      } else if (is_array($val) && isset($val[$lang])) {
        $tmp = $val[$lang][0];
        if (isset($tmp['safe_value'])) {
          $return->addChild($key, $tmp['safe_value']);
        } else if (isset($tmp['value'])) {
          $return->addChild($key, $tmp['value']);
        }
      } else if (is_scalar($val)) {
        $return->addChild($key, $val);
      }
    }
    return $return;
  }
}