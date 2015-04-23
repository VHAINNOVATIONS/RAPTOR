<?php
class FrxMergeDocument extends FrxRenderer {
  public $templateName = 'Generic Merge Document';

  /**
   * Returns the section
   * Enter description here ...
   */
  public function configForm($config) {
    $form_ctl = array();
    $form_ctl['content'] = array(
        '#type' => 'text_format',
        '#title' => t('Document'),
        '#rows' => 5,
        '#format' => $this->input_format,
        '#default_value' => @$config['content']['value'],
       // '#ajax' => $this->configAjax('blur')
    );

    return $form_ctl;
  }

  public function configValidate(&$config) {
    $this->validateTextFormats($config, array('content'));
  }

  public function scrapeConfig() {
    $config = array();
    $config['class'] = get_class($this);
    $this->extractTemplateHTML($this->reportDocDomNode, $config);
    return $config;
  }

  public function generate($xml, &$config) {
    $config['class'] = get_class($this);
    $config['foreach'] = '*';
    $div = $this->blockDiv($config);
    $this->removeChildren($div);
    $this->addFragment($div, $config['content']['value']);

  }
}