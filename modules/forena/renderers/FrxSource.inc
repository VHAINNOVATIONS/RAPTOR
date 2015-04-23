<?php
/**
 * @file FrxSource
 * Displays source of Frx Report rather than rendering
 * Look at the FrxRender class to see a full list of
 * properties that can be used here.
 */
class FrxSource extends FrxRenderer {
  public function render() {
    $node = $this->reportDocNode;
    $html = $node->asXML();
    $html = str_replace(' frx:renderer="FrxSource"', '', $html);
    $html = str_replace('<html>', '<html xmlns:frx="urn:FrxReports">', $html);
    $output = "<pre>\n" . htmlspecialchars($html) . "\n</pre>";
    return $output;
  }
}