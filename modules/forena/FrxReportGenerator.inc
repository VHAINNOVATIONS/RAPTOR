<?php
// $Id$
/**
 * @file
 * Common functions used throughout the project but loaded in this
 * file to keep the module file lean.
 */
// Include Report renderer.
require_once('FrxReport.inc');
require_once('FrxDataSource.inc');

class FrxReportGenerator {

  public $app;
  private $repositories = array();
  static $instance='';
  public $title;
  public $content;

  /**
   * Factory
   *
   * @return FrxReportGenerator
   *
   **/
  static public function instance() {
    global $_forena_application_class;
    if (!self::$instance) {
      if (!$_forena_application_class) {
        $forena_application_class = 'FrxDrupalApplication';
      }

      self::$instance = new FrxReportGenerator($_forena_application_class);
    }
    return self::$instance;
  }


  public function __construct($application_class) {

    $app = $this->app = new $application_class();
  }

  /**
   * Clear session data because we want to reload.
   */
  public function clear_session() {
    if (isset($_SESSION['forena_access'])) unset($_SESSION['forena_access']);
  }




  /**
   * Load the formatters for all initialized repositories.
   *
   */
  function get_formatter($fkey) {
    // Get all repositories
    $repos = Frx::RepoMan()->repositories;
    $formats = array();
    foreach ($repos as $k => $r) {
      $provider = isset($r['data']) ? $r['data'] : NULL;
      if ($provider && method_exists($provider, 'formats'))  {
        $f = $provider->formats();
        if (isset($f[$fkey]) && method_exists($provider, $fkey)) {
          // We found an object with the advertised method return it
          return $provider;
        }
      }
    }
    //Did not find the formater in the data provider
    //Look to see if it's in a control class
    $controls = Frx::Controls();
    foreach ($controls as $k => $r) {
      $provider = $r;
      if ($provider && method_exists($provider, 'formats'))  {
        $f = $provider->formats();
        if (isset($f[$fkey]) && method_exists($provider, $fkey)) {
          // We found an object with the advertised method return it
          return $provider;
        }
      }
    }
    return $formats;
  }



  /**
   * Accepts the name of a file
   *
   * @return FrxReport
   *
   */
  function get_report($report_name, $data=array()) {
    $r=NULL;
    $this->alter_parameters($report_name, $data);
    if ($report_name) {
      $r_text = $this->app->load_report($report_name);
      if ($r_text) {
        $r = new FrxReport($r_text, $data);
      }
    }
    return $r;
  }

  function alter_parameters($report_name, &$data) {
    if (method_exists($this->app, 'alter_parameters')) {
      $this->app->alter_parameters($report_name, $data);
    }

  }


  /**
   * Enter description here...
   *
   * @param simplexml $xml
   * @param string $tag
   * @return string
   */
  function inner_xml($xml, $tag) {
    if (is_object($xml) && is_object($xml->$tag)) {
      $xml_data = $xml->$tag->asXML();
      $xml_data = preg_replace("/<\/?" . $tag . "(.|\s)*?>/", "", $xml_data);
    };
    return $xml_data;
  }

  /**
   * Accepts the name of the html tag, and the string the tag is in.
   *
   * Returns the string within the html tag name
   *
   */
  function get_html($tag, $r_text) {
    $open = strpos($r_text, $tag);
    $close = strpos($r_text, '>', $open);
    $next = strpos($r_text, '<', $close + 1);
    $str = substr($r_text, $close + 1, $next - ($close + 1));

    return $str;
  }

  function format_data($value, $format, $format_str, $teng='') {
    $fo = $this->get_formatter($format);
    if ($fo) {
      $ret = $fo->$format($value, $format_str, $teng);
    }
    else {
      $ret = $value;
    }
    return $ret;
  }



  /**
   * Returns an object of the template class
   * that has a method named templates.
   *
   * If it fails it returns a 0;
   */
  function get_templates($fkey) {
    $templates = $this->supported_templates();
    if (class_exists($fkey)) {
      return new $fkey();
    }
  }

  /**
   *
   * @return returns an array of supported templates
   *
   */
  function supported_templates() {


    return $templates;
  }

  function supported_formats() {
    $controls = Frx::Controls();
    $supported_formats = array();
    $f = array();
    foreach ($controls as $k => $r) {
      $provider = $r;
      if ($provider && method_exists($provider, 'formats'))  {
        $f = $provider->formats();
        $supported_formats = array_merge($supported_formats, $f);
      }
    }
    return $supported_formats;
  }


  /**
   * Load and render a report based on a drupal path.
   * In this function the arglist is used to get the full path to the report.
   *
   * @return unknown
   */
  function report($name_in, $parms = array(), $print=TRUE) {
    global $user;
    $output = '';
    $desc = Frx::Menu()->parseURL($name_in);
    $name = $desc['name'];
    $format = isset($desc['format']) ? $desc['format'] : '';
    $filename = $desc['filename'];
    // Determine the data to get.
    if (!$parms) {
      $parms = array_merge($_GET, $_POST);
      unset($parms['q']);
    }
    else $parms = (array)$parms;


    if ($name) {
      $r = @$this->get_report($name, $parms);
      if (!$r || !$r->rpt_xml) {
        $this->app->error('Could not load report. Check for invalid XML in ' . $name);
        return '';
      }

      // When doing outputs lets check to make sure
      // we have access to the reports.   If not throw an error.
      if ($print) {
        $cache = forena_load_cache($r->rpt_xml);
        $access = forena_check_all_access($cache['access']);
        if (!$access) {
          drupal_access_denied();
          exit;
        }
      }

      //check for default parameters
      $r->processParameters();
      Frx::Skin()->load($r->skin);
      Frx::Skin()->loadSkinFiles($name);
      $cached_data = FALSE;
      $cache = array();

      // Check for cache data
      if ($r->cache) {
        $cid = 'forena:report:' . $name . ':' . drupal_http_build_query($parms);
        if (@$r->cache['per_user'] && $user) $cid .= ':' . $user->uid;
        if (@$r->cache['per_doctype']) @$cid .= ':' . $format;
        $cache = cache_get($cid, 'cache');
        if (!$cache || (isset($r->cache['duration']) && $cache->expire < time())) {
          $r->render($format, $print);
          $time = null;
          if (isset($r->cache['duration'])) {
            try {
              $time = @new DateTime($r->cache['duration']);
            }
            catch (Exception $e) {
              drupal_set_message('Invalid Cache Duration', 'error', TRUE);
            }
            if ($time) $time = $time->getTimeStamp();
          }
          if (!$time) {
            $time = CACHE_PERMANENT;
          }
          cache_set($cid, $r->cache, 'cache', $time);
        }
        $r->render($format, $print, $cache->data);
      }
      else {
        $r->render($format, $print);
      }


      $o =  Frx::Document($format);
      if ($o) {
        $output = $o->render($r, $format,  array());

        if ($print) {
          $printed = $o->output($output);
        }
        else {
          $printed = FALSE;
        }
        if (!$printed) {
          return  $output;
        }
      }
    }
  }

  public function debug($short_message='', $log='') {
    $this->app->debug($short_message, $log);
  }

  public function error($short_message='', $log='') {
    $this->app->error($short_message, $log);
  }

  public function configuration($name) {
    return $this->app->configuration($name);
  }

  public function report_path() {
    return $this->app->configuration('report_repos');
  }

  public function url($path, $options = array()) {
    return $this->app->url($path, $options);
  }

}