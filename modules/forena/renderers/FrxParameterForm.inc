<?php
/**
 * @file
 * paramter form
 * Use to customize report parameters form.
 * @author metzlerd
 *
 */
class FrxParameterForm extends FrxRenderer {
  public function render() {
    $output = '';
    $variables = $this->replacedAttributes();
    $variables['template']  = $this->innerXML();
    $form = $this->frxReport->parametersForm($variables);
    $this->frxReport->parameters_form = array('#markup' => drupal_render($form));
    return $output;
  }
}