<?php
/**
 * @file FrxCSVDoc.inc
 * CSV Document export.
 * @author davidmetzler
 *
 */
class FrxCSVDoc extends FrxDocument {

  public function __construct() {
    $this->content_type = 'application/csv';
  }

  /**
   * Fill columns with rowspan tags.
   * @param unknown_type $colspan
   */
  private function fillColumns($colspan) {
    $f = '';
    for ($i=2; $i<=$colspan; $i++) {
      $f .= ',';
    }
    return $f;
  }

  public function render($r, $format, $options = array()) {
    $doc = $r->html;
    $dom = new DOMDocument();
    $dom->strictErrorChecking = FALSE;
    $xmlBody = '<?xml version="1.0" encoding="UTF-8"?>' . $doc;
    libxml_use_internal_errors(TRUE);
    @$dom->loadHTML($xmlBody);
    libxml_clear_errors();
    $xml = simplexml_import_dom($dom);

    $output = '';
    $rows = array();
    if (!empty($xml)) {
      $rows = $xml->xpath('//tr');
    }
    $r->html = '';
    $rowspans = array();
    if ($rows) foreach ($rows as $row) {
      $c = 0;
      $line = '';
      foreach ($row as $column) {
        $c++;
        if (@$rowspans[$c]) {
          $cont = TRUE;
          while ($rowspans[$c] && $cont) {
            $rowspans[$c]--;
            $r->html .= ',';
            $c++;
          }
        }
        $value = $column->asXML();
        $value = strip_tags($value);
        $value = decode_entities($value);
        $value = str_replace('"', '""', $value);
        $value = str_replace(array("\n"), '', $value);
        $value =  strpos($value, ',')!==FALSE || strpos($value, '"') !==FALSE ?  '"' . $value . '",' : "$value,";
        $line .= $value;
        // Add Column span elements
        if ((int)$column['colspan'] > 1) {
          for ($i=2; $i<=(int)$column['colspan']; $i++) {
            $c++;
            $line .= ',';
          }
        }
        // Check to see if we have some rowspans that we need to save
        if ((int)$column['rowspan'] > 1) {
          $rowspans[$c] = (int)$column['rowspan'] - 1;
        }
      }
      // Trim off the last comma so we don't put in an extra null column
      $line = substr($line,0,-1);
      $r->html .= "$line\n";
    }
    return $r->html;
  }


  public function output(&$output) {
    $output = $this->convertCharset($output);
    parent::output($output);
    return TRUE;
  }
}

