<?php
class FeedsForenaXMLParser extends FeedsParser {

  /**
   * Implements FeedsParser::parse().
   */
  public function parse(FeedsSource $source, FeedsFetcherResult $fetcher_result) {
    $source_config = $source->getConfigFor($this);
    $result = new FeedsParserResult();
    $parser = Frx::Fields($fields);
    // Load and configure parser.
    $xpath = @$this->config['xpath'];
    $options = @$this->config['options'];
    $options = @drupal_parse_info_format($source_config['options']);
    $fields = @$options['fields'];
    if (!$fields) $fields= array();
    $format = @!$options['format'];
    $raw = $fetcher_result->getRaw();
    $mappings = feeds_importer($this->id)->processor->config['mappings'];


    if ($raw) {
      $xml = new SimpleXMLElement($raw);
      // Treat * as normal iterator to improve performance.
      if ($xpath == '*') {
        $nodes = $xml;
      }
      else {
        $nodes = $xml->xpath($xpath);
      }
      foreach($nodes as $node) {
        Frx::Data()->push($node, 'row');
        $item = array();
        foreach($mappings as $field) {
          $text = $field['source'];
          $item[$text] = $parser->render($text, !$format);
        }
        $result->items[] = $item;
        Frx::Data()->pop();
      }
    }
    // Create a result object and return it.
    return $result;
  }


  /**
   * Define default configuration.
   */
  public function configDefaults() {
    return array(
      'xpath' => '*',
      'options' => '',
    );
  }


  /**
   * Build configuration form.
   */
  public function configForm(&$form_state) {
    $form = array();
    $form['xpath'] = array(
      '#type' => 'textfield',
      '#title' => t('Default XPath Expression'),
      '#description' => t('Specify an xpath expression that will be used to break up the data into feed items.  When in doubt use * '),
      '#default_value' => $this->config['xpath'],
    );
    $form['options'] = array(
      '#type' => 'textarea',
      '#title' => t('Default Formatting Option  '),
      '#description' => t('Specify the field configuration options to be used when specifying the data.    The format
          used is the same as in .info files with falues for the FRX files. (e.g. fields[date_taken][format] = iso_date )'),
      '#default_value' => $this->config['options'],
      '#rows' => 20,
      '#maxlength' => NULL,
      '#required' => FALSE,
    );
    return $form;
  }

}
