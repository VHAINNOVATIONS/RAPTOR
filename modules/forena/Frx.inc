<?php
/**
 * @file Frx.incL
 * General Forena Reporting Class
 */
define('FRX_TOKEN_EXP', '/\{[^\n^\r^}]+}/');
define('FRX_SQL_REWRITE_EXP', '/\[[^\n^\r^\]]+\]/');
define('FRX_SQL_TOKEN', '/(?<!(:|[a-zA-Z]|[0-9]|[_\.])):[a-zA-Z]([a-zA-Z]|[0-9]|[_\.])+/');


class Frx {

  /**
   * Helper method to return current url parameters.
   * @return unknown
   */
  public static function parms() {
    $parms = $_GET;
    unset($parms['q']);
    return $parms;
  }

  /**
   * Temporary dom object used for for fragment importing and manipulation.
   * We use a singleton here to reduce the memory footprint.
   * @return DOMDocument
   */
  public static function tempDOM() {
    static $o = '';
    if (!$o) {
      $o= new DOMDocument('1.0', 'UTF-8');
      @$o->load('<?xml version="1.0" encoding="UTF-8"?>
         <!DOCTYPE root [
         <!ENTITY nbsp "&#160;">
         ]>');
    }
    return $o;
  }

  /**
   * Skin Factory
   * @return FrxSkin
   */
  public static function Skin() {
    static $o = '';
    if (!$o) {
      require_once 'FrxSkin.inc';
      $o = new FrxSkin();
    }
    return $o;
  }

  /**
   * Data Factory
   */
  public static function Data() {
    static $o = '';
    if (!$o) {
      require_once 'FrxData.inc';
      $o = new FrxData();
    }
    return $o;
  }

  /**
   * File singleton factory
   * @return FrxReportFile
   */
  public static function File() {
    static $o = '';
    if (!$o) {
      require_once 'FrxReportFile.inc';
      $o = new FrxReportFile();
    }
    return $o;
  }

  /**
   * File singleton factory
   * @return FrxDataFile
   */
  public static function DataFile() {
    static $o = '';
    if (!$o) {
      require_once 'FrxDataFile.inc';
      $o = new FrxDataFile();
    }
    return $o;
  }

  /**
   * File singleton factory
   * @return FrxMenu
   */
  public static function Menu() {
    static $o = '';
    if (!$o) {
      require_once 'FrxMenu.inc';
      $o = new FrxMenu();
    }
    return $o;
  }

  /**
   *
   * @param  $config array of field configuration;
   * @return FrxFields
   */
  public static function Fields($config = array()) {
    require_once('FrxFields.inc');
    $o = new FrxFields($config);
    return $o;
  }
  /**
   *
   * Enter description here ...
   * @param unknown_type $type
   * @return FrxDocument
   */
  public static function Document($type='web') {

    static $doc_types = '';
    static $objects = '';
    require_once 'FrxDocument.inc';
    // Invoke doc_type hook to see which document types are there.
    if (!$doc_types) $doc_types = module_invoke_all('forena_document_types');

    if (!$type) $type = 'web';
    if (isset($doc_types[$type]) && class_exists(@$doc_types[$type]['class'])) {
      if (!@$doc_types[$type]['object']) {
        $o = new $doc_types[$type]['class'];
        $o->format = $type;
        $doc_types[$type]['object'] = $o;
      }
      else {
        $o = $doc_types[$type]['object'];
      }
      return $o;
    }
    else {
      drupal_set_message(t('Unsupported document type: &s'), array('%s' => $type), 'error');
    }

  }

  /**
   * Returns list of document types supported.
   * @param unknown_type $all
   */
  public static function documentTypes($all = FALSE) {
    require_once 'FrxDocument.inc';
    static $supported_doctypes = '';
    if (!$supported_doctypes || $all) {
      $doc_types = module_invoke_all('forena_document_types');
      if (!$all) unset($doc_types['web']);
      $supported_doctypes = array();
      foreach ($doc_types as $type => $doc) {
        $supported_doctypes[$type] = $type;
      }
    }
    return $supported_doctypes;
  }

  /**
   * Retreive the classes from other modules.
   * @param string $class
   * @return Ambigous <string, unknown>
   */
  public static function Controls($class='') {
    static $instances = '';
    if (!$instances) {
      foreach (module_list() as $module) {
        $function = $module . '_forena_controls';
        if (function_exists($function)) {
          $returned_controls = $function();
          if ($returned_controls) foreach ((array)$returned_controls as $c) {
            $c['module'] = $module;
            $c['file'] = drupal_get_path('module' , $c['module']) . '/' . trim($c['file'], '/');
            if ($c['file']) {
              include_once($c['file'] );
              if (class_exists($c['class'])) {
                $instances[$c['class']] =  new $c['class'];
              }
            }
          }
        }
      }
    }
    // Return class if we asked for one.
    if ($class) {
      return @$instances[$class];
    }
    return $instances;
  }


  /**
   * Returns an object of the template class
   * that has a method named templates.
   * @return FrxRenderer
   */
  public static function Template($class) {
    return Frx::Controls($class);
  }

  /**
   * Forena Repository manager
   * Class factory
   * @return FrxRepoMan
   */
  public static function RepoMan() {
    static $o = '';
    if (!$o) {
      require_once 'FrxDataSource.inc';
      require_once 'FrxRepoMan.inc';
      $o = new FrxRepoMan();
    }
    return $o;
  }

  /**
   * General wrapper procedure for reporting erros
   *
   * @param string $short_message Message that will be displayed to the users
   * @param string $log Message that will be recorded in the logs.
   */
  public static function error($short_message='', $log='') {
    if ($short_message) {
      drupal_set_message(check_markup($short_message), 'error', FALSE);
    }
    if ($log) {
      watchdog('forena', $log, NULL, WATCHDOG_ERROR);
    }
  }

  /**
   * Debug handler
   * Enter description here ...
   * @param unknown_type $short_message
   * @param unknown_type $log
   */
  public static function debug($short_message='', $log='') {
    if ($log) {
      watchdog('forena debug', $log, NULL);
    }
    if ($short_message) {
      drupal_set_message(check_markup($short_message));
    }
  }

  /**
   * Factory for token replacement syntax engine object
   * @param $regexp string regular expression for finding tokens
   * @param $trim string to trim off the end of tokens.
   * @return FrxSyntaxEngine
   */
  public static function SyntaxEngine($regexp, $trim) {
    require_once 'FrxSyntaxEngine.inc';
    return new FrxSyntaxEngine($regexp, $trim);
  }

  /**
   * Facotry for SQL Query builder object
   * @param $new Boolean value that forces a new object
   * @return FrxSQLQueryBuilder
   */
  public static function SQLQueryBuilder($new = FALSE) {
    static $o = '';
    require_once 'FrxSQLQueryBuilder.inc';
    if (!$o || $new) {
      $o = new FrxSQLQueryBuilder();
    }
    return $o;
  }

  /**
   * Factory method to return instance object
   * @param unknown_type $parent
   * @param unknown_type $key
   * @return object
   */
  public static function PluginInstance($parent, $key) {
    static $objects='';
    $o = NULL;
    if (!$objects) {
      $objects = array();
    }
    $plugins = Frx::plugins($parent);
    if (isset($plugins[$key])) {
      $class = @$plugins[$key]['class'];
      if (!isset($objects[$class])) {
        if (class_exists($class)) $objects[$class] = new $class();
      }
      $o = $objects[$class];
    }

    return $o;
  }

  /**
   * Factory object to get the context based on a name.  IF the context doesn't
   * exist we assume the default data context (FrxData).
   * @param $context string
   * @return FrxContext
   */
  public static function Context($context) {
    $o = NULL;

    // Instantiate the class if it exists
    if ($context) {
      $o = Frx::PluginInstance('FrxContext', $context);
    }

    // Otherwise instantiate the data class.
    if (!$o) {
      $o = Frx::Data();
    }

    return $o;
  }

  /**
   * Loads all of the include files that
   */
  public static function plugins( $parent = '') {
    static $plugins = '';

    if (!$plugins) {
      $plugins = array();
      foreach (module_list() as $module) {
        $function = $module . '_forena_plugins';
        if (function_exists($function)) {
          $returned_plugins = $function();
          if ($returned_plugins) foreach ((array)$returned_plugins as $key=>$p) {
            $p['module'] = $module;
            if (@$p['path']) {
              $p['file'] = rtrim($p['path'],'/') . '/' . $p['file'];
            }
            else {
              $p['file'] = drupal_get_path('module' , $p['module']) . '/' . $p['file'];
            }
            if (is_int($key)) {
              $plugins[]=$p;
            }
            else {
              $plugins[$key] = $p;
            }
          }
        }
      }
      foreach ($plugins as $p) {
        if ($p['file'] ) {
          include_once( trim($p['file'], '/'));
        }
      }
    }

    // Return the plugins if a parent was requested.
    $ret_plugins = array();
    if ($parent) {
      foreach($plugins as $key => $p) {
        if (@$p[parent] == $parent) {
          $ret_plugins[$key] = $p;
        }
      }
    }
    return $ret_plugins;
  }


  /**
   *
   * @param  $report_name
   * @return FrxEditor <string, FrxEditor>
   */
  static public function Editor($report_name = '', $edit=TRUE) {
    static $o='';
    require_once('FrxEditor.inc');
    if (!$o) {
      $o = new FrxEditor($report_name, $edit);
    }
    else {
      if ($report_name) $o->load($report_name, $edit) ;
    }
    return $o;
  }

  /**
   *
   * @param $block_name
   * @return FrxBlockEditor
   */
  static public function BlockEditor($block_name = '', $edit=TRUE) {
    static $o='';
    require_once('FrxBlockEditor.inc');
    if (!$o) {
      $o = new FrxBlockEditor($block_name, $edit);
    }
    else {
      if ($block_name) $o->load($block_name, $edit);
    }
    return $o;
  }

}
