<?php
/**
 * @file FrxInclude
 * Include a reference to another report as an asset.
 * @author davidmetzler
 *
 */
class FrxInclude extends FrxRenderer {
  public function render() {
    // Get data from source
    $attributes = $this->mergedAttributes();
    $output = '';

    // Determine data type
    $include = @$attributes['src'];

    // Reformat URL
    @list($url, $query_str)=@explode('?', $include);
    $url = $this->teng->replace($url, TRUE);
    $report_url = $url;
    $parts = @explode('/', $url);
    $file = @$parts[count($parts) - 1];
    $parts = explode('.', $file);
    // Determine file extention
    $ext = count($parts) > 1 ? $parts[count($parts) - 1] : '';
    $query = array();
    if ($query_str) {
      parse_str($query_str, $query );
      foreach ($query as $key=>$value) {
        $query[$key] = $this->teng->replace($value, TRUE);
      }
    }

    // Build URL
    $options = array('query' => $query);
    $url = url($url, $options);
    if ($this->format=='web') {
      $output = $this->render_reference($url, $ext, $attributes);
    }
    else {
      $output = forena_report_include(str_replace('reports/', '', $report_url), $query);
    }
    return $output;
  }

  function render_reference($url, $ext, $attributes) {
    $ext = strtolower($ext);
    $attributes = $this->teng->replace($attributes);
    switch ($ext) {
      case 'png':
      case 'gif':
      case 'jpg':
      case 'jpeg':
        $x = new SimpleXMLElement('<img/>');
        $x['src'] = $url;
        if (isset($attributes['height'])) $x['height'] = $attributes['height'];
        if (isset($attributes['width'])) $x['width'] = $attributes['width'];
        break;
      case 'svg':
        $x = new SimpleXMLElement('<embed/>');
        $x['src'] = $url;
        $x['type'] = 'image/svg+xml';
        $x['pluginspage'] = "http://www.adobe.com/svg/viewer/install/";
        if (isset($attributes['height'])) $x['height'] = $attributes['height'];
        if (isset($attributes['width'])) $x['width'] = $attributes['width'];
        break;
      default:
        $x = new SimpleXMLElement('<a>' . $ext . ' document</a>' );
        $x['href'] = $url;
    }

    if (isset($attributes['id'])) $x['id'] = $attributes['id'];
    return $x->asXML();
  }

  /**
   * Implement template generator.
   * @see FrxRenderer::generate()
   */
  public function generate($xml, &$config) {
    $src = $this->extract('src', $key);
    $div = $this->blockDiv($config);
    $frx = array('src' => $src, 'renderer' => 'FrxInclude');
    $this->addNode($div, 4, 'div', NULL, $config, $frx);
  }

}