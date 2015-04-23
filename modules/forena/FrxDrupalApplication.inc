<?php
global $_forena_application_class;
$_forena_application_class = 'FrxDrupalApplication';
class FrxDrupalApplication {
  /**
   * @file HostApp.inc
   * Defines all the interface points between the host application and Forena.
   * Each of these methods must be specified in order for Forena to function properly.
   * The base class here is drupal, so those
   */

  private $default_config = array();
  public function __construct() {
    $this->default_config['report_repos'] = drupal_get_path('forena', 'module') . '/repos/reports';
    $this->default_config['default_form'] = 'letter';
    $this->default_config['doc_defaults'] = array();
    $this->default_config['library_path'] = 'sites/all/libraries';
    $this->default_config['doc_formats'] = array();
  }

  /**
   * Configuration retrieval method
   * Returns configuration varialbes used by forena.
   * @param unknown_type $var_name
   */
  public function configuration($var_name) {
    $v = variable_get('forena_' . $var_name, @$this->default_config[$var_name]);
    return $v;
  }


  public function url($path, $options = array()) {
    return url($path, $options);
  }

  /**
   * Theme the output of a css
   * Enter description here ...
   * @param unknown_type $output
   * @param unknown_type $doc_type
   */
  public function theme($r, $title, $doc_type) {
    $output = $r->html;
    $input_format = variable_get('forena_input_format', 'none');
    if ($input_format != 'none') $output = check_markup($output, $input_format);

    if (!$doc_type || $doc_type='embed') {
      // Set the title and return the output
      $f = drupal_get_form('forena_parameter_form', $r->rpt_xml->asXML(), $r->blocks_loaded);
      if ($f) $output = drupal_render($f) . $output;
      drupal_set_title(filter_xss($title));
      $output = '<div class="forena-report">' . $output . '</div>';
    }
    else {
      //Build the doucment and theme the output
      print($output);
    }
    return $output;
  }

  /**
   * Add a css file for theming.
   * Enter description here ...
   * @param unknown_type $css_file
   */
  public function add_css($css_file) {
    drupal_add_css($css_file, 'module');
  }

  /**
   * Add a javascript file for theming css
   * Enter description here ...
   * @param unknown_type $js_file
   */
  public function add_js($js_file) {
    drupal_add_js($js_file);
  }

  /**
   * Convert the report name into a link to the report
   * Enter description here ...
   * @param unknown_type $report_name
   */
  public function report_link($report_name, $title) {

  }

  /**
   * What to do if we don't find a report
   * Enter description here ...
   */
  public function not_found($name) {
    require_once('forena.admin.inc');
    forena_delete_report($name);
    return 'Report Not Found';
  }

  public function forena_path() {
    return drupal_get_path('module', 'forena');
  }


  /**
   * Accepts the name of a file
   *
   * Returns a xml object of the file.
   *
   */
  function load_report($report_name) {
    $r=NULL;
    global $language;
    $r_text = '';
    if ($report_name) {
      $i_report_name = $report_name;
      $int_filename = $language->language . '/' . $report_name . '.frx';
      $filename = $report_name . '.frx';
      if (Frx::File()->exists($int_filename) && @$_GET['language']!= 'en') {
        $i_report_name = $language->language . '/' . $report_name;
        $r_text = Frx::File()->contents($int_filename);
        $full_path = Frx::File()->path($int_filename);
        $modified = filemtime($full_path);
        $filename = $int_filename;
      }
      elseif (Frx::File()->exists($filename)) {
        $r_text = Frx::File()->contents($filename);
        $modified = filemtime(Frx::File()->path($filename));
      }
      return $r_text;
    }
  }





  /**
   * Builds a global array of available controls
   * and returns the array.
   */
  function controls() {
    static $controls = '';
    if (!$controls) {
      $controls = array();
      foreach (module_list() as $module) {
        $function = $module . '_forena_controls';
        if (function_exists($function)) {
          $returned_controls = $function();
          if ($returned_controls) foreach ((array)$returned_controls as $c) {
            $c['module'] = $module;
            $c['file'] = drupal_get_path('module' , $c['module']) . '/' . trim($c['file'], '/');
            $controls[]=$c;
          }
        }
      }
    }
    return $controls;
  }

  /**
   * Return the repositories configured for this applicaiton.
   */
  public function repositories() {
    global $_forena_repositories;

    $repos = array();
    // Load the repository list from the global settings.php file.
    if ($_forena_repositories) {
      $repos = $_forena_repositories;
    }
    $path = $this->forena_path();
    // Overide difinitions of the sample and drupal repositories.
    $repos['forena_help'] = array(
    'path' => $path . '/repos/forena_help',
    'title' => 'Forena Help Reports',
    );
    $repos['drupal'] = array(
    'path' => $path . '/repos/drupal',
    'title' => 'Drupal Reports',
    );
    $repos['sampledb'] = array(
    'path' => $path . '/repos/sample',
    'title' => 'Sample DB Repository'
    );
    // Retrieve the repositories defined in the database;
    $results = db_query('SELECT * FROM {forena_repositories}');
    foreach ($results as $r) {
      if ($r->config) {
        $new_r = unserialize($r->config);
      }
      else {
        $new_r = array();
      }
      $r_name = $r->repository;
      if (is_array(@$repos[$r_name])) {
        $new_r = array_merge($new_r, $repos[$r_name]);
      }
      else {
        $new_r['source'] = 'user';
      }
      $new_r ['title'] = $r->title;
      $new_r ['enabled'] = $r->enabled;

      $repos[$r_name] = $new_r;
    }


    drupal_alter('forena_repos',  $repos);
    return $repos;
  }

  /**
   * Allow modules to alter the parameters of a report.
   * @param unknown_type $report_name
   * @param unknown_type $parms
   */
  function alter_parameters($report_name, &$parms) {
    drupal_alter('forena_parameters', $report_name, $parms );
  }

}
