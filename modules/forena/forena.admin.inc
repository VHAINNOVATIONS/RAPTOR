<?php
// $Id$
/**
 * @file
 * Report administration forms and functions.
 */
require_once('forena.common.inc');

/**
 * Display reports to edit for admins in the structure menu
 * Enter description here ...
 */
function forena_admin_reports() {
  GLOBAL $language;
  $data = array();
  $output = '';
  $content = drupal_get_form('forena_sync_form');
  $sync_form = drupal_render($content);
  $links[] = array('href' => 'reports/add', 'title' => 'Create New Report' );
  $output .= theme('links',
    array(
      'links' => $links,
      'attributes' => array('class' => 'action-links'),
    )
  );
  // Add Data tables if it exists.
  $headers = array(t('category'), t('title'), t('name'), t('operation'));
  $result = Frx::File()->allReports();
  foreach ($result as $report => $row) {
    $rpt = str_replace('/', '.', $report);
    if ($row->include) {
      $edit = l(t('Override'), 'reports/' . $rpt . '/edit');
    }
    else {
      $edit = l(t('Edit'), 'reports/' . $rpt . '/edit');
    }
    $clone = l(t('Clone'), 'reports/add/' . $rpt);
    // Determine the nature of the report delete link.
    if ($row->override) {
      $delete = l(t('Revert'), 'reports/' . $rpt . '/delete', array('query' => array('destination' => 'admin/structure/forena')));
    }
    else if (!$row->include) {
      $delete = l(t('Delete'), 'reports/' . $rpt . '/delete', array('query' => array('destination' => 'admin/structure/forena')));
    }
    else {
      $delete = '';
    }

    $title = l(t($row->cache['title']), 'reports/' . $rpt);
    $data[] = array($row->cache['category'], $title, $report, $edit . ' ' . $clone . ' ' . $delete);
  }
  $output .= '<div id="forena-reports-list">';
  $output .= theme('forena_data_table', array('header' => $headers, 'rows' => $data));
  $output .= '</div>';
  $output .= $sync_form;
  return $output;
}






function forena_filter_element($fmt, $name) {
  global $user;
  $element['format'] =  array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => $fmt!='',
    '#title' => t('Input formats'),
  );
  if (!$fmt) $fmt='full_html';
  // Get a list of formats that the current user has access to.
  $formats = filter_formats($user);
  foreach ($formats as $format) {
    $options[$format->format] = $format->name;
    $element['format']['guidelines'][$format->format] = array(
      '#theme' => 'filter_guidelines',
      '#required' => TRUE,
      '#format' => $format,
    );
  }
  $element['format']['guidelines']['#weight']=12;

  $element['format'][$name] = array(
    '#type' => 'select',
    '#title' => t('Text format'),
    '#options' => $options,
    '#default_value' => $fmt,
    '#access' => count($formats) > 1,
    '#weight' => 10,
    '#attributes' => array('class' => array('filter-list')),
  );

  $element['format']['help'] = array(
    '#type' => 'container',
    '#theme' => 'filter_tips_more_info',
    '#attributes' => array('class' => array('filter-help')),
    '#weight' => 11,
  );
  return $element['format'];
}



/**
 * Report syncronization form
 * @param $formid
 * @param $form_state
 */
function forena_sync_form($formid, &$form_state) {

  $form['sync_overwrite'] = array(
    '#type' => 'checkbox',
    '#title' => t('Revert all delivered reports to original'),
    '#required' => TRUE,
    '#description' => t('All customizations to module delivered reports will be lost.')
  );

  $form['sync'] = array(
    '#type' => 'submit',
    '#value' => t('Revert'),
    '#submit' => array('forena_settings_sync_submit'),
  );

  return $form;

}

/**
 * Forena admin settings form
 *
 */
function forena_settings($form, &$form_state) {
  $skins = Frx::File()->skins();

  $report_path = forena_report_path();


  $form['forena_report_repos'] = array(
    '#type' => 'textfield',
    '#title' => t('Report Repository'),
    '#description' => t('Indicate the directory that you want to use for storing your report sources (frx files). In order for you ' .
                        'to be able to save report sources, this directory should be writable by the web user. Relative ' .
                        'paths should be entered relative to the base path of your drupal installation.'),
    '#default_value' => $report_path,
  );

  $form['forena_last_report_path'] = array(
    '#type' => 'value',
    '#value' => variable_get('forena_report_repos', '')
  );

  if (module_exists('forena_query')) {
      $form['forena_query_data_path'] = array(
        '#type' => 'textfield',
        '#title' => t('Custom Data Block Repository'),
        '#description' => t('Indicate the directory that you want to use to save custom created data blocks (SQLs or
            XMLs) using the forena query builder tool. These will override any module delivered data blocks. This
            needs to be a directory that is writable by the web user but should not be browsable by the web.'),
        '#default_value' => Frx::DataFile()->dir,
      );

      $form['forena_last_query_data_path'] = array(
          '#type' => 'value',
          '#value' =>  variable_get('forena_query_data_path', '')
      );

  }

  $input_format = variable_get('forena_input_format', 'none');
  $input_format_enabled = isset($form_state['values']['forena_input_format_enabled']) ? $form_state['values']['forena_input_format_enabled'] : $input_format != 'none';

  $form['format'] = array('#prefix' => '<div id="forena-format">', '#suffix' => '</div>');
  $form['format']['forena_input_format_enabled'] = array(
  	'#type' => 'checkbox',
    '#title' => t('Use Text Format filtering system'),
    '#description' => t('Checking this allows use of Drupal\' custom markup and automatic URL replacement in reports with a slight performance penalty.'),
    '#default_value' => $input_format_enabled,
    '#ajax' => array(
  	  'callback' => 'forena_settings_format_callback',
      'wrapper' => 'forena-format',
    ),
  );

  $form['format']['forena_input_format'] = forena_filter_element(variable_get('forena_input_format', filter_default_format()), 'forena_input_format');

  $form['format']['forena_input_format']['#access'] = $input_format_enabled;
  $form['format']['forena_input_format']['#collapsible'] = FALSE;
  $form['forena_default_form'] = array(
    '#type' => 'select',
    '#title' => t('Default report skin'),
    '#options' => $skins,
    '#description' => t('Specify the default skin to be used.   New skins can be created by creating .skinfo files in your reports directory.'
    . ' Skins are basically css and javascript libraries added to your report.'),
    '#default_value' => variable_get('forena_default_form', ''),
    );

  $form =  system_settings_form($form);
  return $form;

}

function forena_settings_format_callback($form, &$form_state) {
  return $form['format'];
}

function forena_settings_validate($form, &$form_state) {

  $values = $form_state['values'];
  if (!$values['forena_input_format_enabled']) $form_state['values']['forena_input_format'] = 'none';
  $path = $values['forena_report_repos'];
  if ($path != $values['forena_last_report_path']) {
    if (!file_exists($path)) {
      try {
        mkdir($path);
        if (file_exists($path)) {
          drupal_set_message(t('Created Report Directory %s', array('%s' => $path))) ;
        }
      } catch (Exception $e) {
        Frx::error(t('Unable to create Report Directory'), $e->getMessage());
      }
    }
  }
  if (!file_exists($path)) {
    form_set_error('forena_report_repos', t('Report Directory (') . $path . t(') does not exist'));
  }
  else {
    if (!is_writable($path)) {
      form_set_error('forena_report_repos', t('Report Directory (') . $path . t(') must be writable by the web user'));
    }
  }
  if (isset($values['forena_query_data_path'])) {
    $path = $values['forena_query_data_path'];
    if ($path != $values['forena_last_query_data_path']) {
      if (!file_exists($path)) {
        try {
          mkdir($path);
          if (file_exists($path)) {
            drupal_set_message(t('Created Custom Data Block Directory %s', array('%s' => $path))) ;
          }
        } catch (Exception $e) {
          Frx::error(t('Unable to create Custom Data Block Directory'), $e->getMessage());
        }
      }
    }
    if (!file_exists($path)) {
      form_set_error('forena_query_data_path', t('Custom Data Block Directory (') . $path . t(') does not exist'));
    }
    else {
      if (!is_writable($path)) {
	form_set_error('forena_query_data_path', t('Custom Data Block Directory (') . $path . t(') must be writable by the web user'));
      }
    }
  }
}


/**
 * Added submit handler to create directories and clear menu cache
 *
 * @param unknown_type $form
 * @param unknown_type $form_state
 */
function forena_settings_submit($form, &$form_state) {
}

function forena_settings_sync_submit($form, &$form_state) {
  forena_sync_reports($form_state['values']['sync_overwrite']);
}

/**
 * Form function for the edit report form
 * @param $form_state
 * @return the form
 */


/**
 * Handle delete buttons from edit forms.
 * @return unknown_type
 */
function forena_edit_delete_submit($form, &$form_state) {
  $link = 'reports/' . $form_state['values']['name_in'] ;
  $destination = '';
  if (isset($_REQUEST['destination'])) {
    $destination = drupal_get_destination();
    unset($_REQUEST['destination']);
  }
  $form_state['redirect'] = array('path' => $link . '/delete', 'query' => array('destination' => $destination));
}


function forena_create_trans_form($formid, $form_state, $report_name) {
  $name = '';
  $filename = '';
  $format = '';
  $desc = Frx::Menu()->parseURL($report_name);


  $form = array();
  global $language;
  $languages = language_list();
  //determine if this is an add new report request

  $r = Frx::Editor($report_name, FALSE);
  $base_name = $r->desc['base_name'];
  $title = (string)$r->title;
  $lang = @$_GET['target_language'];
  if ($lang) {
    $language = $languages[$lang];
  }
  $form['base_name'] = array(
    '#type' => 'value',
    '#value' => $base_name
  );
  $form['report_name'] = array(
    '#type' => 'value',
    '#value' => $name,
  );

  foreach ($languages as $key => $obj) {
    $languages[$key] = $obj->native;
  }
  $form['report_lang'] = array(
    '#type' => 'value',
    '#value' => $lang,
  );
  $def_lang = $lang ? $lang : 'en';
  $form['save_report_language'] = array(
    '#type' => 'select',
    '#title' => t('Language'),
    '#options' => $languages,
    '#default_value' => $def_lang,
  );


  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => $title,
    '#required' => TRUE,
  );


  $form['save'] = array(
    '#type' => 'submit',
    '#value' => 'Create',
  );
  return $form;

}

function forena_create_trans_form_submit($form, &$form_state) {
  global $language;
  $language = language_default();
  $values = $form_state['values'];
  $base_name = $values['base_name'];
  $new_name = $values['save_report_language'] . '/' . $base_name;
  $r = Frx::Editor($base_name, TRUE);
  $link = $r->report_link;

  // Determine new fielname
  $desc = Frx::Menu()->parseURL($new_name);
  $filename = $desc['filename'];
  $report_name = $desc['name'];

  //determine redirection.

  if (Frx::File()->exists($filename, FALSE)) {
    drupal_set_message(t('Report %s already exists', array('%s' => $new_name)), 'error');
    return;
  }
  else {
    // Title and category
    $r->setTitle($values['title']);
    $r->update();
    $r->rename($new_name);
    drupal_set_message(t('Translation,  %s has been created. Switch languages to translate.', array('%s' => $values['title'])));
    //$r->cancel();
    $r->save();
    $form_state['redirect']= $link . '/edit';
  }

  //if this is a new report then redirect to data blocks
}



/*
 * administer the settings for document format options
 */
function forena_doc_formats_settings() {
  // Invoke doc_type hook to see which document types are there.
  $supported_doctypes = Frx::documentTypes();

  $form['forena_doc_formats'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Allowed Document Formats'),
    '#default_value' => variable_get('forena_doc_formats', $supported_doctypes),
    '#description' => t('check your desired document format'),
    '#options' => $supported_doctypes,
  );

  $form['forena_doc_defaults'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Default Document Formats'),
    '#default_value' => variable_get('forena_doc_defaults', array()),
    '#description' => t('check your desired document format'),
    '#options' => $supported_doctypes,
  );

  $form['forena_email_override'] = array(
    '#type' => 'checkbox',
    '#title' => 'Run email merges in test mode' ,
    '#default_value' => variable_get('forena_email_override', FALSE),
    '#description' => t('When this box is checked emails are sent to the currently logged in user.  Useful for testing environments.'),
  );
  $form['forena_email_input_format'] = forena_filter_element(variable_get('forena_email_input_format', 'full_text'), 'forena_email_input_format');
  $form['forena_email_input_format']['#title'] = t('Email Input Format');

  return system_settings_form($form);
}

/*
 * administer the settings for document format options
 */
function forena_data_settings() {
  $repos = Frx::RepoMan()->repositories;
  $r_list = array();
  $headers = array(t('Name'), t('Description'), t('Path'), t('Operation'));
  foreach ($repos as $name => $r) {
    $r_list[] = array(
    $name,
    $r['title'],
    $r['path'],
    l(t('configure'), 'admin/config/content/forena/data/configure/' . $name)
    );

  }

  $output = '<ul class="action-links"><li>' . l(t('Add data source'), 'admin/config/content/forena/data/add') . '</li></ul>';
  $output .= theme_table(array('header' => $headers, 'rows' => $r_list, 'attributes' => array(), 'caption' => '', 'sticky' => TRUE, 'colgroups' => array(), 'empty' => ''));
  return $output;
}

function forena_data_settings_edit($form, &$form_state, $source=-1) {
  global $databases;
  $adding = ($source === -1);
  if (!@$form_state['storage']) {
    if ($adding) {
      $form_state['storage'] = array(
        'name' => '',
        'title' => '',
        'path' => '',
        'config' => array(
          'source' => 'user',
          'data provider' => 'FrxDrupal',
          'database' => 'default',
          'access callback' => 'user_access',
          'user callback' => 'forena_current_user_id'
          ),
          );
    }
    else {
      Frx::RepoMan()->repository($source);
      $repos = Frx::RepoMan()->repositories;
      $r = $repos[$source];
      // Remove teh object from the data.
      unset($r['data']);
      $form_state['storage'] = array(
        'name' => $source,
        'title' => $r['title'],
        'path' => @$r['path'],
        'config' => $r,
      );

    }
  }
  $data = $form_state['storage'];
  $config = $data['config'];

  $locked = !($adding || (@$config['source'] == 'user'));
  $values = @$form_state['values'];

  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#description' => t('Machine readable name.  Used in referencing all data used by this source. must should not contain any special characters or spaces.'),
    '#disabled' => !$adding,
    '#default_value' => $data['name'],
    '#required' => TRUE,
  );

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#required' => TRUE,
    '#description' => t('Human readable name that describes the data source.  This primarily occurs in error messages where the data source cannot be accessed.'),
    '#default_value' => $data['title'],
  );

  $form['enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enabled'),
    '#description' => t('Disabling will cause all queries to return no data.'),
    '#default_value' => @$data['enabled']!==0,
  );

  $form['debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug'),
    '#description' => t('Write information to the screen and logs for each query executed.'),
    '#default_value' => @$config['debug'],
  );

  $form['path'] = array(
    '#type' => 'textfield',
    '#title' => t('Path'),
    '#required' => TRUE,
    '#disabled' => $locked,
    '#description' => t('Directory containing data block files.'),
    '#default_value' => @$data['path'],
  );

  $user_options = array(
    '' => 'None',
    'forena_current_user_id' => 'UID',
    'forena_current_user_name' => 'User name',
  );

  $form['user_callback'] = array(
    '#type' => 'select',
    '#title' =>  'Current user',
    '#disabled' => $locked,
    '#description' => t('Can be refererenced as :current_user in each data block.'),
    '#options' => $user_options,
    '#default_value' => @$config['user callback'],
    '#disabled' => $locked,
  );


  // Access method list
  $access = array(
    'callback' => t('Use drupal permissions'),
    'block' => t('Match values provided by a data block.'),
  );

  $form['access_method'] = array(
    '#type' => 'select',
    '#options' => $access,
    '#disabled' => $locked,
    '#title' => t('Data security method'),
    '#default_value' => empty($config['access block']) ? 'callback' : 'block',
    '#description' => t('Specify how the ACCESS defined for a data block is to be interpreted.'),
    '#ajax' => array(
      'callback' => 'forena_access_info_callback',
      'wrapper' => 'access-details',
  ),
  );

  $form['access_details'] = array(
    '#type' => 'fieldset',
    '#prefix' => '<div id="access-details">',
    '#suffix' => '</div>',
    '#title' => t('Details'),
  ) ;

  switch (!empty($values['access_method']) ? $values['access_method'] : $form['access_method']['#default_value']) {
    case 'block':
      $form['access_details']['access_block'] = array(
        '#type' => 'textfield',
        '#title' => 'Data block providing permissions list',
        '#disabled' => $locked,
        '#autocomplete_path' => 'forena/data_block/autocomplete',
        '#description' => t('The datablock to be used to interpret permissions.  This should return a single column of permissions based on the current user.   May be provided by another repository.'),
        '#default_value' => @$config['access block'],
      );
      break;
    default:
      $form['access_details']['access_callback'] = array(
        '#type' => 'item',
        '#title' => 'Access callback',
        '#disabled' => $locked,
        '#markup' => @$config['access callback'],
      );
  }



  // Driver list
  $drivers = array(
    'FrxDrupal' => t('Drupal'),
    'FrxOracle' => t('Oracle Database'),
    'FrxPDO' => t('PDO other than Drupal'),
    'FrxPostgres' => t('Postgres Database'),
    'FrxMSSQL' => t('MSSQL Database'),
    'FrxFiles' => t('XML Files'),
  );

  $form['data_provider'] = array(
    '#type' => 'select',
    '#title' => t('Driver'),
    '#description' => t('Forena data connection type'),
    '#options' => $drivers,
    '#disabled' => $locked,
    '#default_value' => $config['data provider'],
    '#ajax' => array(
      'callback' => 'forena_connection_info_callback',
      'wrapper' => 'conn-div',
  ),
  );

  $form['connection'] = array(
    '#type' => 'fieldset',
    '#tree' => TRUE,
    '#title' => 'Connection info',
   '#prefix' =>  '<div id="conn-div">',
   '#suffix' => '</div>',
  );
  $data_provider = (!empty($form_state['values']['data_provider']) ? $form_state['values']['data_provider'] : $config['data provider']);

  // Common controls used in mulitple providers.
  $uri = array(
    '#type' => 'textfield',
    '#title' => t('uri'),
    '#descripton' => t('Connection string: see appropriate php documentation for more details.'),
    '#default_value' => @$config['uri'],
    '#required' => TRUE,
  );

  $user = array(
    '#type' => 'textfield',
    '#title' => t('User'),
    '#default_value' => @$config['user'],
  );
  $password= array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#default_value' => @$config['password'],
  );

  switch ($data_provider) {
    case 'FrxDrupal':
      $db_list = array_combine(array_keys($databases), array_keys($databases));
      $form['connection']['database'] = array(
        '#type' => 'select',
        '#title' => t('Database'),
        '#disabled' => $locked,
        '#default_value' => @$config['database'],
        '#options' => $db_list,
        '#markup' => 'Determined by Drupal settings.php file',
      );
      break;
    case 'FrxMSSQL':
      $form['connection']['uri'] = $uri;
      $form['connection']['user'] = $user;
      $form['connection']['new_password'] = $password;
      $form['connection']['database'] = array(
        '#type' => 'textfield',
        '#disabled' => $locked,
        '#title' => t('Database'),
        '#default_value' => $config['database'],
        '#required' => TRUE,
      );
      $form['connection']['mssql_xml'] = array(
        '#type' => 'checkbox',
        '#disabled' => $locked,
        '#title' => t('Microsoft SQL native XML'),
        '#description' => t('Use for XML auto queries to generate XML.'),
        '#default_value' => $config['mssql_xml'],
      );
      break;
    case 'FrxOracle':
      $form['connection']['uri'] = $uri;
      $form['connection']['user'] = $user;
      $form['connection']['new_password'] = $password;
      $form['connection']['character_set'] = array(
        '#type' => 'textfield',
        '#title' => t('Character Set'),
        '#disabled' => $locked,
        '#description' => t('Leave blank for default character set'),
        '#default_value' => @$config['character_set'],

      );
      $form['connection']['oracle_xml'] = array(
        '#type' => 'checkbox',
        '#title' => t('Oracle native XML'),
        '#disabled' => $locked,
        '#description' => t('Use the function provided with Forena to generate XML.  Requires installing a function into the database'),
        '#default_value' => @$config['oracle_xml'],
      );
      break;
    case 'FrxPDO':
      $form['connection']['uri'] = $uri;
      $form['connection']['user'] = $user;
      $form['connection']['new_password'] = $password;
      break;
    case 'FrxPostgres':
      $form['connection']['uri'] = $uri;
      $form['connection']['new_password'] = $password;
      $form['connection']['postgres_xml'] = array(
        '#type' => 'checkbox',
        '#title' => t('Postgres native XML'),
        '#disabled' => $locked,
        '#default_value' => @$config['postgres_xml'],
        '#description' => t('Use Postgres native XML support.  Requires Posgres 8.3 or better'),
      );

    default:
      $form['connection']['uri'] = $uri;
  }

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#submit' => array('forena_data_settings_save'),
  );


  $form['delete'] = array(
    "#type" => 'submit',
    '#value' => t('Delete'),
    '#submit' => array('forena_data_settings_delete'),
  );

  return $form;
}



/**
 * Submit handler to cause save events to happen.
 * Enter description here ...
 */
function forena_data_settings_save($form, &$form_state) {
  $values = $form_state['values'];
  $name = $values['name'];
  $config = $form_state['storage']['config'];
  $config['path'] = @$values['path'];
  $config['data provider'] = $values['data_provider'];
  $config['user callback'] = $values['user_callback'];
  $config['debug'] = $values['debug'];
  if (@$values['connection']['new_password']) {
    $values['connection']['password'] = $values['connection']['new_password'];
  }
  if (isset($values['connection']['new_password'])) unset($values['connection']['new_password']);
  if (is_array(@$values['connection'])) $config = array_merge($config, @$values['connection']);
  if ($values['access_method']=='callback') {
    $config['access callback'] = empty($values['access_callback']) ? 'user_access' : $values['access_callback'];
    if (isset($config['access block'])) unset($config['access block']);
  }
  else {
    $config['access block'] = $values['access_block'];
  }
  $config_str = serialize($config);
  $result = db_query('SELECT * FROM {forena_repositories} WHERE repository = :name', array(':name' => $name));

  if ($result) drupal_set_message(t('Data connection settings saved'));
  if ($repos = $result->fetchObject()) {
    db_update('forena_repositories')
    ->fields(array(
        'title' => $values['title'],
        'enabled' => $values['enabled'],
        'config' => $config_str,
    ))
    ->condition('repository', $name, '=')
    ->execute();
  }
  else {
    $form_state['#redirect'] = 'admin/forena/content/data';
    db_insert('forena_repositories')
    ->fields(array(
        'repository' => $name,
        'title' => $values['title'],
        'enabled' => $values['enabled'],
        'config' => $config_str,
    ))
    ->execute();
  }
  $form_state['redirect'] = 'admin/config/content/forena/data';
}

/**
 * Delete the function
 * Enter description here ...
 * @param unknown_type $form
 * @param unknown_type $form_state
 */
function forena_data_settings_delete($form, &$form_state) {
  if (!@$form_state['storage]']['locked']) {
    $form_state['redirect'] = 'admin/config/content/forena/data';
    db_delete('forena_repositories')
    ->condition('repository', $form_state['values']['name'], '=')
    ->execute();
  }

}

/**
 * Generates the controls for external databases.
 * Enter description here ...
 */
function forena_connection_info_callback($form, $form_state) {
  return $form['connection'];

}

function forena_access_info_callback($form, $form_state) {
  return $form['access_details'];
}



/**
 * Clean xhtml
 *
 * @param unknown_type $xhtml
 * @return unknown
 */
function forena_clean_xhtml($xhtml) {
  $ret = $xhtml;
  // If tidy is installed lets clean the html using that.
  if (is_callable('tidy_repair_string')) {
    $config = array(
      'doctype'            => 'omit',
      'indent-spaces'             => 2,
      'indent' => 'auto',
      'input-xml'          => TRUE,
      'output-xml'         => TRUE,
      'indent-attributes'  => FALSE,
      'indent-spaces'      => 2,
      'add-xml-space'      => TRUE,
      'wrap'               => 135 );
    $ret = tidy_repair_string($xhtml, $config, 'utf8');
  }
  else {
    $ret = str_replace('&nbsp;', '&#160;', $ret);
  }
  return $ret;
}

/**
 *  Prettifies an XML string into a human-readable and indented work of art
 *  @param string $xml The XML as a string
 *  @param boolean $html_output True if the output should be escaped (for use in HTML)
 */
function forena_xmlpp($xml, $html_output=FALSE) {
  $xml_obj = new SimpleXMLElement($xml);
  $level = 4;
  $indent = 0; // current indentation level
  $pretty = array();

  // get an array containing each XML element
  $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

  // shift off opening XML tag if present
  if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
    $pretty[] = array_shift($xml);
  }

  foreach ($xml as $el) {
    if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
      // opening tag, increase indent
      $pretty[] = str_repeat(' ', $indent) . $el;
      $indent += $level;
    }
    else {
      if (preg_match('/^<\/.+>$/', $el)) {
        $indent -= $level;  // closing tag, decrease indent
      }
      if ($indent < 0) {
        $indent += $level;
      }
      $pretty[] = str_repeat(' ', $indent) . $el;
    }
  }
  $xml = implode("\n", $pretty);
  return ($html_output) ? htmlentities($xml) : $xml;
}

/**
 * Auto complete function for categories
 * Checks access for security as well.
 *
 * @param $string = string to be matched against categories
 * @return An array containing all matching categories
 */
function forena_get_categories($string='') {
  $data = Frx::File()->reportsByCategory();
  if ($string) {
    $categories = array();
    foreach (array_keys($data) as $cat) {
      if (strpos(strtolower($cat), strtolower(trim($string))) === 0) {
        $categories[] = $cat;
      }
    }

  }
  else {
    $categories = array_keys($data);
  }
  $categories = array_combine($categories, $categories);
  return $categories;
}

function forena_template_ajax(&$form_element) {
  $form_element['#ajax'] = array(
        'callback' => 'forena_template_info_callback',
        'wrapper' => 'template-wrapper',
  );
}

function forena_query_string($var) {
  return drupal_http_build_query($var);
}


