<?php
/**
 * @file FrxFile.inc
 * File toolbox for manipulating files
 * contained tn the report directory.
 */
class FrxFile {
  public $dir; // Path to Default directory
  public $writable;
  public $includes = array(); //Other places to look for a directory.
  public $use_includes;
  public $cached_extensions;
  protected $validated = FALSE;
  protected $cache;
  protected $needSave = TRUE;
  protected $needScan = TRUE;
  protected $cacheKey = NULL;

  public function __construct($default_directory, $include_directories, $extentions = array(), $use_includes=TRUE) {
    // Check to see if directory is writable
    $this->dir = $default_directory;
    $this->includes = $include_directories;
    $this->use_includes = $use_includes;
    $this->writable = is_writable($this->dir);
    $this->cached_extensions = $extentions;
    $this->cacheKey = 'forena:' . get_class($this);
  }


  private function scanDirectory($directory, &$files, $recursive=TRUE) {
    // Loop through the directories, ignoring hidden files.
    $path = $directory;
    // Scan the directory for files.
    $d = @dir($path);
    if ($d) while (false !== ($rpt_file = $d->read())) {
      $src_file = rtrim($d->path, '/') . '/' . trim($rpt_file, '/');
      if (is_file($src_file)) {
        @list($base_file, $ext) = explode('.', $rpt_file, 2);
        if (array_search($ext, $this->cached_extensions) !== FALSE) {
          $files[$ext][$src_file] = filemtime($src_file);
        }
      }
      elseif (is_dir($src_file)) {
        if (strpos($rpt_file, '.')!==0 && $recursive) {
          $this->scanDirectory($src_file, $files, $recursive);
        }
      }
    }
    if ($d) $d->close();

  }

  /**
   * Parse a drectory
   * @param unknown $directory
   * @param string $prefix a prefix to use in the base name
   */
  protected function scanInclude($directory, $prefix='') {
    $default = strpos($directory, $this->dir) === 0;
    $files = array();
    $this->scanDirectory($directory, $files);
    foreach($files as $ext => $files_of_type ) {
      foreach($files_of_type as $file=>$mtime) {
        $base_name = $prefix . substr($file, strlen($directory) + 1, -1 * (strlen($ext) + 1));
        if (!isset($this->cache[$ext][$base_name])) {
          $obj = new stdClass();
          $obj->file = $file;
          $obj->mtime = $mtime;
          $obj->cache = NULL;
          $obj->include = !$default;
          $obj->override = FALSE;
          $this->cache[$ext][$base_name] = $obj;
        }
        else {
          // If its our first pas on this replace the entry
          $entry = $this->cache[$ext][$base_name];
          if (isset($this->filesToDelete[$ext][$base_name])) {
            if ($entry->file != $file) {
              $entry->file = $file;
              $entry->cache = NULL;
              $entry->mtime = $mtime;
              $entry->include = !$default;
              $entry->override = FALSE;
            }
          }
          else {
            if (!$entry->override) {
              if ($entry->file != $file && strpos($entry->file, $this->dir) ===0) $entry->override = TRUE;
            }
          }
          unset($this->filesToDelete[$ext][$base_name]);
        }
      }
    }
  }

  private function setFilesToDelete() {
    $this->filesToDelete = array();
    //Quickly make a list of files in the cache right now.
    if ($this->cache) foreach ($this->cache as $ext => $files_of_type) {
      $this->filesToDelete[$ext] = array_fill_keys(array_keys($files_of_type), 1);
    }
  }

  private function deleteMissingEntries() {
    foreach ($this->filesToDelete as $ext => $files_of_type ) {
      foreach ($files_of_type as $base_name => $val) {
        unset($this->cache[$ext][$base_name]);
        $this->needSave = TRUE;
      }
    }
  }

  public function scan($prefix = '') {
    // Add the base report files.
    if ($this->needScan) {
      $this->scanInclude($this->dir, $prefix);

      // Now add the module provided ones.
      if ($this->includes) foreach ($this->includes as $directory) {
        $this->scanInclude($directory, $prefix);
      }
    }
  }

  /**
   * This is a wrapper function used to build the cache.  It should
   * be overrident by
   * @param $ext extension of the cache name
   * @param $base_name of the object.
   * @param $object Object
   */
  protected function buildCache($ext, $base_name, &$object) {
    $object->cache = array();
  }

  private function _validateAllCache($prefix) {
    foreach($this->cached_extensions as $ext) {
      if (isset($this->cache[$ext]) && is_array($this->cache[$ext])) {
        $names = array_keys($this->cache[$ext]);
        foreach ($names as $base_name) {
          $this->validateCache($ext, $base_name);
        }
      }
    }
  }

  /**
   * Called anytime we want to
   * make sure the include cache is good and complete. Any file modifications
   * will cause cache to be rebuild to be rebuilt.
   * @param unknown $ext
   * @param string $prefix
   */
  public function validateAllCache($prefix='') {
    // Make sure once per session.
    if (!$this->cache) {
      $cache = cache_get($this->cacheKey, 'cache');
      if ($cache) {
        $this->cache = $cache->data;
      }
      $this->needSave = FALSE;
    }
    // Skip extra stuff after we've validated once.
    if ($this->validated) return;
    // Load data form the cache
    // Save current paths away.
    $this->setFilesToDelete();
    $this->scan($prefix);
    $this->_validateAllCache($prefix);
    //Rescan in case we found deleted files.
    if ($this->needScan) {
      $this->scan($prefix);
      $this->_validateAllCache($prefix);
    }

    //Remove any reports that have dissapeared.
    $this->deleteMissingEntries();

    //Resave the cache if had to be altered.
    if ($this->needSave) {
      cache_set($this->cacheKey, $this->cache, 'cache', CACHE_PERMANENT);
    }
    //$this->needScan = FALSE;
    $this->validated = TRUE;
  }

  public function getCache($ext) {
    if (isset($this->cache[$ext])) {
      return $this->cache[$ext];
    }
    else {
      return array();
    }
  }

  /**
   * Validate a single cache entry
   * @param unknown $ext
   * @param unknown $base_name
   */
  public function validateCache($ext, $base_name) {
    if (isset($this->cache[$ext])) {
      if (isset($this->cache[$ext][$base_name])) {
        $obj = $this->cache[$ext][$base_name];
        if (file_exists($obj->file)) {
          $mtime = filemtime($obj->file);
          if ($obj->cache === NULL || $mtime != $obj->mtime) {
            //Expensive cache building process.
            $this->buildCache($ext, $base_name, $obj);
            $this->needSave = TRUE;
          }
          $this->cache[$ext][$base_name] = $obj;
        }
        else {
          // Remove the file from the cache.
          unset($this->cache[$ext][$base_name]);
          $this->needScan = TRUE;
          $this->needSave=TRUE;
        }
      }
    }
  }

  /**
   * Revert an individual report
   * @param $file
   */
  public function revert($file) {
    $i = 0;
    if ($this->includeExists($file)) {
      $file_to_delete = $this->dir . '/' . $file;
      if (file_exists($file_to_delete)) {
        if (is_writeable(dirname($file_to_delete))) {
          drupal_set_message(t('Removing customization %s', array('%s' => $file_to_delete)));
          unlink($file_to_delete);
          $i++;
        }
        else {
          drupal_set_message(t('Unable to revert %s', array('%s' => $file_to_delete)), 'error');
        }
      }
    }
    return $i;
  }


  /**
   * Determine if the file exists in the include path.
   * @param $file
   */
  public function includeExists($file) {
    $found = false;
    $i = 0;
    while(isset($this->includes[$i]) && !$found ) {
      $filename = $this->includes[$i] . '/' . $file;
      if (file_exists($this->includes[$i] . '/' . $file)) {
        $found = TRUE;
      }
      $i++;
    }
    return $found;
  }

  /**
   * Return the full path to the filename
   *   @param $filename
   */
  public function path($filename, $use_include = TRUE) {
    $path = $this->dir . '/' . $filename;
    if ($use_include && !file_exists($path)) {
      foreach ($this->includes as $dir) {
        if (file_exists($dir . '/' . $filename)) {
          $path = $dir . '/' . $filename;
        }
      }
    }
    return $path;
  }

  /**
   * Return the directory portioin of a report filename.
   * @param unknown_type $filename
   */
  public function directory($filename) {
    @list ($dir, $name_part) = explode('/', $filename, -1);
    return $this->dir . '/' . $dir;
  }

  /**
   * Return whether the file exists.
   * @param unknown_type $filename
   */
  public function exists($filename, $use_include = TRUE) {
    return file_exists($this->path($filename, $use_include));
  }

  /**
   * Return the contents of a file located in the report directory
   * @param $filename filename and extension for report file.
   */
  public function contents($filename) {
    $path = $this->path($filename);
    if (file_exists($path)) {
      return file_get_contents($path);
    }
    else {
      return '';
    }
  }

  function verifyDirectory($fullpath, $recursive=FALSE) {
    static $path='';
    $success = TRUE;
    if (!$recursive) {
      $path = $this->dir;
      if (!is_writable($path)) {
        drupal_set_message(t('Directory %s is not modifiable', array('%s' => $path)), 'error');
        return FALSE;
      }
    }
    @list($dir, $file) = explode('/', $fullpath, 2);
    $path .= '/' . $dir;


    // Path
    if (!file_exists($path) && $file) {
      @mkdir($path);
      if (!is_writable($path)) {
        drupal_set_message(t('Error creating directory %path', array('%path' => $path)), 'error');
        return FALSE;
      }

    }
    // Recurse to next file.
    if ($file && strpos($file, '/')) {
      $this->verifyDirectory($file, TRUE);
    }
    return TRUE;
  }

  /**
   * Save a file into the report directory.
   * @param unknown_type $filename
   * @param unknown_type $data
   */
  public function save($filename, $data) {
    $path = $this->dir . '/' . $filename;
    $this->verifyDirectory($filename);

    if (is_writable($path) || (!file_exists($path) && is_writable(dirname($path)))) {
      file_put_contents($path, $data);
    }
    else {
      Frx::error(t('Insufficient privileges to write file.'));
    }
  }

  /**
   * Delete a file from the directory.
   * @param unknown_type $filename
   * @return boolean
   */
  public function delete($filename) {
    $path = $this->dir . '/' . $filename;
    $dir = getcwd();
    $do = TRUE;
    if (file_exists($path) && is_writeable($path) && is_writable(dirname($path))) {
      $info = pathinfo($path);
      chdir(dirname($path));
      $do = unlink($info['basename']);
      chdir($dir); ;
    }
    return $do;
  }

  /**
   * Retrieve path info
   * @param $filename filename used for data
   * @param $use_include boolean value determining whether to search include path.
   * @return mixed
   */
  public function pathinfo($filename, $use_include = TRUE) {
    return pathinfo($this->path($filename, $use_include));
  }
  /**
   * Return an indicator as to whether the file is savable.
   * New files can be saved if the directory is writabel.
   * @param unknown $filename
   * @return boolean
   */
  public function isWritable($filename) {
    return is_writeable($this->dir . "/$filename") || (!file_exists($this->dir . "/$filename"));
  }

  /**
   * Returns the cache entry based on a filename.
   * @param unknown $filename
   * @return unknown
   */
  public function getCacheEntry($filename) {
    if (!$this->cache) $this->validateAllCache();
    list($base_name, $ext) = explode('.', $filename , 2);
    $cache = $this->cache[$ext][$base_name];
    return $cache;
  }

  public function isOverriden($filename) {
    $cache = $this->getCacheEntry($filename);
    return $cache->override;
  }

  public function isCustom($filename) {
    $cache = $this->getCacheEntry($filename);
    return !$cache->include;
  }


}