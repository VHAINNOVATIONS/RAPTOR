<?php
/**
 * @file FrxXML
 * Just render the XML source data.
 * Look at the FrxRender class to see a full list of
 * properties that can be used here.
 */
class FrxXML extends FrxRenderer {
  public function render() {
    $output = '';
    $node = $this->reportDocNode;
    $options = $this->mergedAttributes();
    if (isset($options['context'])) {
      $xml = Frx::Data()->getContext($options['context']);
    }
    else {
      $xml = FrxData::instance()->currentContext();
    }
    if (is_array($xml)) $xml = FrxData::arrayToXml($xml);
    if ($xml && is_callable(array($xml, 'asXML')))  {
        $dom = dom_import_simplexml($xml);
        $dom->ownerDocument->formatOutput = TRUE;
        $dom->ownerDocument->preserveWhiteSpace = TRUE;
        $output = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
    }
    if ($this->format != 'xml') $output = '<pre>' . htmlspecialchars($output) . '</pre>';
    return $output;
  }
}