<?php
class FrxSection extends  FrxRenderer {

  public $templateName = 'Section';
  public $lastClass = '';

  /**
   * oull configs.
   */
  public function scrapeConfig() {
    $content = array();
    // Determine if we are starting from a dom node
    $sec=0;
    $config['sections'] = array();

    // put the divs in next
    $nodes = $this->reportDocNode->xpath('div');
    if ($nodes) foreach ($nodes as $node) {
      $dom_node = dom_import_simplexml($node);
      $sec++;
      $id = (string) @$node['id'] ? (string)$node['id'] : 'section-' . $sec;
      $config['sections'][$id]  = array(
        'id' => $id,
        'markup' => $node->asXML(),
        'class' => @(string) $node['class'] ? (string)$node['class'] : 'FrxMergeDocument',
        'weight' => $sec,
      );
    }

    return $config;
  }

  /**
   * Generate configuration.
   */
  public function configForm($config) {

    $form['sections'] = array('#theme' => 'forena_element_draggable',   '#draggable_id' => 'FrxContainer-sections');
    if (isset($config['sections'])) foreach ($config['sections'] as $id => $section) {
      $ctl = array();
      $ctl['id'] = array('#type' => 'item', '#markup' => $id, '#title' => 'id');
      $ctl['markup'] = array('#type' => 'value', '#value' => $section['markup']);
      $ctl['class_label'] = array('#type' => 'item', '#markup' => @$section['class'], '#title' => t('Type'));
      $ctl['class'] = array('#type' => 'value', '#value' => @$section['class']);
     // $ctl['display'] = array('#type' => 'item', '#title' => 'html',  '#markup' =>$section['markup']);
      $form['sections'][$id] = $ctl;
    }
    return $form;
  }

  /**
   * Validate the configuration
   */
  public function configValidate(&$config) {
  }

  /**
   * Build the template
   * @param $xml
   * @param $config
   */
  public function generate($xml, &$config) {

    if (!isset($config['sections']) || !count($config['sections'])) {
      $class = @$config['class'];
      $frx = '';
      if (!$class || $class== 'FrxMergeDocument') {
        $frx = isset($config['foreach']) ? ' frx:foreach="' . $config['foreach'] . '"' : ' frx:foreach="*"';
      }
      $id =  isset($config['id']) ? $config['id'] : 'section-1';
      $id .= '-' . strtolower(str_replace('Frx', '', $class));
      $o = "<div id='$id' class='$class'$frx>";
      foreach(array('header', 'content', 'footer') as $content) {
        if(isset($config[$content]['value']) && trim($config[$content]['value'])) {
          $o .= $config[$content]['value'];
        }
      }
      $o .= '</div>';
      $config['sections'][$id] = array(
          'id' => $id,
          'markup' => $o,
          'class' => $class,
          'weight' => 1,
      );
    }

    $config['class'] = get_class($this);
    $div = $this->blockDiv($config);
    $this->removeChildren($div);
    if (isset($config['sections'])) foreach ($config['sections'] as $id => $section){
      $this->addFragment($div, $section['markup']);
    }
  }


}