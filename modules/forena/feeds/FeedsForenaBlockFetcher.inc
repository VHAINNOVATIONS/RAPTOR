<?php
// Data Fetcher
class FeedsForenaBlockFetcherResult extends FeedsFetcherResult {


  // Constructor for fetcher result
  public function __construct($block, $data) {
    parent::__construct('');
    $this->block = $block;
    $this->data = $data;

  }

  /**
   * Overrides parent::getRaw().
   */
  public function getRaw() {
    $xml = forena_xml($this->block, $this->data);
    $raw = '';
    if (is_object($xml) && method_exists($xml, 'asXML')) {

      $raw = $xml->asXML();
    }
    return $this->sanitizeRaw($raw);
  }

}

// Block Fetcher
class FeedsForenaBlockFetcher extends FeedsFetcher {

  // Implement data block fetch.
  public function fetch(FeedsSource $source) {

    $source_config = $source->getConfigFor($this);

    // Just return a Block result if this is a Block.
    $r = Frx::RepoMan();
    $block = $source_config['source'];
    $data = @drupal_parse_info_format($source_config['data']);
    if ($r->loadBlock($block)) {
      return new FeedsForenaBlockFetcherResult($block, $data);
    }

    throw new Exception('Source is not a valid Forena Data Block');

  }

  public function sourceForm($source_config) {
    $form = array();
    $form['source'] = array(
      '#type' => 'textfield',
      '#autocomplete_path' => 'forena/data_block/autocomplete',
      '#title' => 'Data Block',
      '#required' => TRUE,
      '#description' => t('Select an exising block.'),
      '#default_value' => isset($source_config['source']) ? $source_config['source'] : '',
    );

    $form['data'] = array(
      '#type' => 'textarea',
      '#title' => t('Parameters  '),
      '#description' => t('Specify one per line the key value pairs that will be used when fetching the data.   The format
          used is the same as in .info files.'),
      '#default_value' => isset($source_config['data']) ? $source_config['data'] : '',
      '#rows' => 20,
      '#maxlength' => NULL,
      '#required' => FALSE,
    );
    return $form;
  }

  public function sourceFormValidate(&$values) {
    $values['source'] = trim($values['source']);
    $block = @Frx::RepoMan()->loadBlock($values['source']);
    if (!$block) {
      form_set_error('feeds][FeedsForenaBlockFetcher][source', t('The specified block does not exist.'));
    }
  }






}