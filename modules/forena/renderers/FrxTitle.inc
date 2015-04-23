<?php
/**
 * @file
 * Implements a title renderer
 * @author metzlerd
 *
 */
class FrxTitle extends FrxRenderer {
  public function render() {
    $html = $this->innerXML();
    $html = $this->teng->replace($html);
    $this->frxReport->title = $html;
    return '';

  }
}
