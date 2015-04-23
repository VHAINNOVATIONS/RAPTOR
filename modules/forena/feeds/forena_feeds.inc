<?php
function _forena_feeds_plugins() {

  $path = drupal_get_path('module', 'forena') . '/feeds';

  $info = array();

  $info['FeedsForenaBlockFetcher'] = array(
    'name' => 'Forena Block Fetcher',
    'description' => 'Fetch any forena data as XML.  Use with an XML Feed parser such as the Forena XML Parser',
    'handler' => array(
      'parent' => 'FeedsFetcher',
      'class' => 'FeedsForenaBlockFetcher',
      'file' => 'FeedsForenaBlockFetcher.inc',
      'path' => $path,
    ),
  );

  $info['FeedsForenaXMLParser'] = array(
    'name' => 'Forena XML Parser',
    'description' => 'Parse XML using the fornea report token syntax.',
    'handler' => array(
      'parent' => 'FeedsParser',
      'class' => 'FeedsForenaXMLParser',
      'file' => 'FeedsForenaXMLParser.inc',
      'path' => $path,
    ),
  );

  return $info;
}