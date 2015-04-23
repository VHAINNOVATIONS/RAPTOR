<?php
require_once 'FrxFile.inc';
require_once 'forena.common.inc';
class FrxDataFile extends FrxFile {

  private $report_cache = array();
  /**
   * Constructor
   *   Sets the initial reort directory
   */
  public function __construct() {
    // Load default directory from configuration.
    $report_path = variable_get('forena_query_data_path', '');
    if (!$report_path) {
      $report_path = variable_get('file_private_path' . '/data', conf_path() . '/data');
    }

    // Add directories for data  repos.
    $default_directory = rtrim($report_path, '/');
    $repositories = Frx::RepoMan()->repositories;
    $directories = array();
    foreach ($repositories as $k => $repos) {
      $directories[$k] = $repos['path'];
    }
    foreach ($directories as $dir) {
      $this->includes[$k] = rtrim($dir, '/');
    }

    // Parent constructor.
    parent::__construct($default_directory, $directories, array('sql', 'xml', 'inc'));
  }

  /**
   * List all the reports for a language.
   * @return unknown
   */
  public function userBlocks($search = '*') {
    $blocks = array();
    $this->validateAllCache();
    $sql = $this->getCache('sql');
    $inc = $this->getCache('inc');
    $xml = $this->getCache('xml');
    $data = array_merge($xml, $sql, $inc);
    if ($data) foreach ($data as $base_name => $obj) {

  	  if ($search == '*' || drupal_match_path($base_name, $search)) {
        if ($obj->cache) {
    	    $r = Frx::RepoMan()->repository($obj->cache['provider']);
    	    if ($r && $r->access($obj->cache['access'])) {
            $blocks[$base_name] = $obj;
    	    }
        }
  	  }
    }
    uksort($blocks, 'FrxDataFile::blockCompare');
    return $blocks;
  }

  /**
   * Return the full path to the filename
   *   @param $filename
   */
  public function path($filename, $use_include = TRUE) {
    $path = $this->dir . '/' . $filename;
    if ($use_include && !file_exists($path)) {
      foreach ($this->includes as $prefix=>$dir) {
        if (strpos($filename, $prefix)===0) {
          $inc_file = substr($filename, strlen($prefix) +1);
          if (file_exists($dir . '/' . $inc_file)) {
            $path = $dir . '/' . $inc_file;
          }
        }
      }
    }
    return $path;
  }


  public function scan($prefix = '') {
    if ($this->needScan) {
      // Add the base report files.
      $this->scanInclude($this->dir, '');

      // Now add the module provided ones.
      if ($this->includes) foreach ($this->includes as $repos => $directory) {
        $this->scanInclude($directory, $repos . '/');
      }
      $this->needScan = FALSE;
    }
  }
  /**
   * Sort compare function for sorting data by category then title.
   * @param unknown $a
   * @param unknown $b
   * @return number
   */
  static public function blockCompare($a, $b) {
    $c = strnatcasecmp($a, $b);
    return $c;
  }

  /**
   * Should load cache data based on that.
   * @see FrxFile::buildCache()
   */
  public function buildCache($ext, $base_name, &$object) {
    if ($base_name) {
      $block_name = $base_name;
      $parts = explode('/', $base_name);
      $provider = array_shift($parts);
      $block = Frx::RepoMan()->loadBlock($block_name);
      $object->cache = array(
      	'provider' => $provider,
        'name' => $block_name,
        'access' => @$block['access'],
        'type' => @$block['type'],
        'options' => @$block['options'],
      );
    }
  }


}
