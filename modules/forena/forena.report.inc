<?php
/*
 * @file forena.report.inc
 * Include files containing form function for editinig reports
 */
function forena_add_report_form($formid, $form_state, $report_name='') {
  $name = '';
  $filename = '';
  $format = '';
  if ($report_name) {
    $desc = Frx::Menu()->parseURL($report_name);
    $name = $desc['name'];
    $filename = $desc['filename'];
    $format = @$desc['format'];
  }
  $form = array();
  global $language;
  //determine if this is an add new report request

  $r = Frx::Editor($name);
  $title = (string)$r->title;
  if (module_exists('locale')) {
    @list($tlang,  $tsave_name) = explode('/', $name, 2);
    // FInd out if the starting name of the report is an installed language.
    if (array_key_exists($tlang, language_list() )) {
      $lang = $tlang;
      $save_name = $tsave_name;
    }
    else {
      $lang = 'en';
    }
  }
  // Need to get all option attributes
  $frx_options = $r->getOptions();
  $hidden = @$frx_options['hidden']=='1' ? 1 : 0;
  $report_form = @$frx_options['form'];
  $attributes = $r->get_attributes_by_id();
  $category = $r->getCategory();
  $body = $r->simplexml->body->asXML();


  //array of xml attributes that are required to have a value
  $required = array('id' => TRUE, 'label' => TRUE);
  //list of supported document formats
  $doclist = variable_get('forena_doc_formats', array());


  $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => $title,
      '#required' => TRUE,
  );
  $form['report_name'] = array(
      '#type' => 'forena_machine_name',
      '#title' => t('Report Name'),
      '#description' => t('Enter only letters, numbers, and special characters:  - _ /
                         <br/>White space is not permitted.
                         Create a directory using the format: (directory name) / (report name). Save multiple reports to the same directory
                         by referencing the same name.'),
      '#required' => TRUE,
      '#machine_name' => array('source' => array('report_name'), 'label' => 'Report_name'),
  );


  $form['category'] = array(
      '#type' => 'textfield',
      '#title' => t('Category'),
      '#default_value' => $category,
      '#autocomplete_path' => 'forena/categories/autocomplete',
      '#description' => t('The heading your report will be grouped under on the report list.'),
  );

  $form['hidden'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hidden'),
      '#default_value' => $hidden,
      '#description' => t('Hide your report from showing up on the report list.'),
  );

  $form['clone_report_name'] = array(
      '#title' => t('Create from report'),
      '#type' => 'textfield',
      '#autocomplete_path' => 'forena/reports/autocomplete',
      '#default_value' => $name,
  );

  $form['save'] = array(
      '#type' => 'submit',
      '#validate' => array('forena_validate_report_name'),
      '#submit' => array('forena_create_report_submit'),
      '#value' => 'Create',
  );
  return $form;

}

function forena_validate_report_name($form, &$form_state) {
  $values = $form_state['values'];
  $pattern = "@^[A-Za-z0-9\/\_]$@";
  $save_report_name = $values['report_name'];
  if (preg_match($pattern, $save_report_name)) {
    form_set_error('report_name', t('Invalid characters in report name') );
  }
  //comparing the report names to see if they have changed.
  //If they have, making sure the new name does not already exist.
  $filename = $save_report_name . '.frx';
  if (Frx::File()->exists($filename, FALSE)) {
    form_set_error('reprot_name', t('The report ' . $save_report_name . ' already exists. Please enter another name.'));
  }
}

function forena_create_report_submit($form, &$form_state) {
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  $desc = Frx::Menu()->parseURL($report_name);
  $report_name = $desc['name'];

  if ($values['report_name']) {
    $r = Frx::Editor($values['clone_report_name']);
    $r->report_name = $values['report_name'];
  }
  else {
    $r = Frx::Editor($values['report_name']);
  }
  // Title and category
  $r->setTitle($values['title']);
  $r->setCategory($values['category']);
  // Form options
  $options = array(
      'hidden' => $values['hidden'],
  );
  $r->setOptions($options);

  //determine redirection.
  $filename = $desc['filename'];

  $r->update();
  //if this is a new report then redirect to data blocks
  if ($values['report_name']) {
    $edit = '/edit';
  }
  else {
    $edit = '/edit/select-data/add-data';
  }
  $form_state['redirect']= $desc['i_link'] . $edit;

}

/**
 * Creates a simple save button to be used with other forms
 * @return multitype:string multitype:string  NULL
 */
function forena_report_save_form($form, &$form_state, $report_name) {
  $form['report_name'] = array(
  	'#type' => 'value',
    '#value' => $report_name,
  );

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#submit' => array('forena_report_save_submit'),
  );

  $form['cancel'] = array(
  	'#type' => 'submit',
    '#value' => t('Cancel'),
    '#submit' => array('forena_report_save_cancel'),
  );
  $parms = $_GET;
  unset($parms['q']);
  $form_state['redirect'] = array("reports/$report_name", array('query' => $parms));
  return $form;
}

/**
 * Form to confirm the delete of a form.
 * @param $form_state
 * @return unknown_type
 */
function forena_report_delete_form($form_state, $formid, $report_name) {
  // Parse names from url
  $name_in = $report_name;
  $desc = Frx::Menu()->parseURL($report_name);
  $report_name = $desc['name'];
  global $language;

  $link = $desc['link'];
  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name
  );
  if (module_exists('locale')) {

    $options = array('all' => t('All Languages'),
        'single' => t('Only %lang', array('%lang' => $language->native)),
    );
    $form['delete_all_languages'] = array(
        '#type' => 'radios',
        '#title' => t('Remove custom reports in all languages?'),
        '#options' => $options,
        '#default_value' => 'single',
    );
    $form['base_name'] = array(
        '#type' => 'value',
        '#value' => $desc['base_name'],
    );
  }
  return confirm_form($form,
      t('Are you sure you want to remove %title?', array('%title' => $report_name)),
      isset($_GET['destination']) ? $_GET['destination'] : $link,
      t('This action cannot be undone.'),
      t('Remove'),
      t('Cancel')
  );
  return $form;
}


function forena_report_delete_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  if (isset($values['delete_all_languages']) && $values['delete_all_languages']=='all') {
    foreach (language_list() as $language) {
      $report_name = $language->language . '/' . $values['base_name'];
      $desc = Frx::Menu()->parseURL($report_name);
      Frx::Editor($report_name)->delete();
      forena_delete_report($desc['name']);
    }
    drupal_set_message(t('Deleted %s', array('%s' => $values['base_name'])));
  }
  else {
    drupal_set_message(t('Deleted %s', array('%s' => $report_name)));
    Frx::Editor($report_name)->delete();
    }
  menu_rebuild();
  $form_state['redirect'] = 'forena';
}




function forena_report_save_submit($form, &$form_state) {
  $report_name = $form_state['values']['report_name'];
  $r = Frx::Editor($report_name);
  $r->save();
  $r_link = $r->report_link;
  $form_state['redirect'] = array("$r_link", array('query' => Frx::parms()));
}

function forena_report_preview($report_name) {
  $r = Frx::Editor($report_name, TRUE);
  drupal_set_title($r->title);
  $content['save'] = drupal_get_form('forena_report_save_form', $report_name);
  $content['preview'] = $r->preview();
  return $content;
}

function forena_report_general_form($form, &$form_state, $report_name) {
  if (!$report_name) {
    $report_name = $form_state['storage']['report_name'];
  }
  else {
    $form_state['storage']['report_name'] = $report_name;
  }

  //set the name to empty string for new reports
  $r = Frx::Editor($report_name);
  $title = (string)$r->title;
  drupal_set_title(filter_xss($r->title));
  $frx_options = $r->getOptions();
  $hidden = @$frx_options['hidden']=='1' ? 1 : 0;
  $report_form = @$frx_options['form'];
  $category = $r->getCategory();
  if (isset($form_state['values']['menu'])) {
    $menu = $form_state['values']['menu'];
  }
  else {
    $menu = $r->getMenu();
    $menu['enabled'] = @$menu['path'] ? 1 : 0;
    if (!isset($menu['type'])) $menu['type'] = 'callback';
  }
  $cache = $r->getCache();
  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name,
  );

  $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => $title,
  );

  $form['visibility'] = array(
      '#type' => 'fieldset',
      '#title' => t('Visibility'),
  );

  $form['visibility']['category'] = array(
      '#type' => 'textfield',
      '#title' => t('Category'),
      '#default_value' => $category,
      '#autocomplete_path' => 'forena/categories/autocomplete',
      '#description' => t('The heading your report will be grouped under on the report list.'),
  );

  $form['visibility']['hidden'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hidden'),
      '#default_value' => $hidden,
      '#description' => t('Hide your report from showing up on the report list.'),
  );

  $form['menu'] = array(
      '#type' => 'fieldset',
      '#title' => t('Menu'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => empty($menu),
      '#prefix' => '<div id="forena-menu-wrapper">',
      '#suffix' => '</div>',
  );

  $form['menu']['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Provide a menu link'),
      '#default_value' => @$menu['enabled'],
      '#ajax' => array(
         'callback' => 'forena_report_general_menu_ajax',
         'wrapper' => 'forena-menu-wrapper',
      ),
  );

  $form['menu']['path'] = array(
      '#type' => 'textfield',
      '#title' => t('Menu Path'),
      '#access' => $menu['enabled'],
      '#description' => t('Indicate site relative path to menu.  Parameters may be embedded in the url using a :parm syntax (e.g. states/:state)'),
      '#default_value' => @$menu['path'],
  );

  $form['menu']['args'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional Arguments'),
      '#description' => t('Indicate additonal parameters that should be extracted after the menu path using a :parm syntax (e.g. :parma/:parmb)'),
      '#access' => $menu['enabled'],
      '#default_value' => @$menu['args'],
  );

  $menu_options = array(
      'normal-item' => t('Normal'),
      'local-task' => t('Tab'),
      'default-local-task' => t('Default Tab'),
      'callback' => t('Callback'),
  );

  $form['menu']['type'] = array(
      '#type' => 'select',
      '#title' => 'Type of menu to create',
      '#options' => $menu_options,
      '#access' => $menu['enabled'],
      '#default_value' => @$menu['type'],
      '#ajax' => array(
        'callback' => 'forena_report_general_menu_ajax',
  	    'wrapper' => 'forena-menu-wrapper'
      ),
  );

  $form['menu']['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Menu Title'),
      '#description' => t('Title of menu item.  Leave blank to use the report title as the menu title.'),
      '#access' => $menu['enabled'] && $menu['type']!='callback',
      '#default_value' => @$menu['title'],
  );
  $options = menu_parent_options(menu_get_menus(), 0);
  $default = @$menu['menu_name'];
  if ($default && @$menu['plid']) {
    $default .= ':' . $menu['plid'];
  }

  $form['menu']['parent'] = array(
      '#type' => 'select',
      '#title' => t('Parent item'),
      '#default_value' => $default,
      '#options' => $options,
      '#empty_option' => t('Not Specified'),
      '#empty_value' => '',
      '#access' => $menu['enabled'] && $menu['type'] == 'normal-item',
      '#attributes' => array('class' => array('menu-parent-select')),
  );
  $form['menu']['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#delta' => 50,
      '#access' => $menu['enabled']  && $menu['type']!= 'callback',
      '#default_value' => @$menu['weight'],
      '#description' => t('Menu links with smaller weights are displayed before links with larger weights.'),
  );

  $form['cache'] = array(
      '#type' => 'fieldset',
      '#title' => t('Cache'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
  );

  $form['cache']['duration'] = array(
      '#type' => 'textfield',
      '#title' => t('Duration'),
      '#description' => t('Specify a php strtotime relative date duration, (e.g. +1 hour, +2 days)'),
      '#default_value' => @$cache['duration'],
  );

  $form['cache']['per_user'] = array(
      '#type' => 'checkbox',
      '#title' => t('Per User'),
      '#default_value' => @$cache['per_user'],
  );

  $form['cache']['per_doctype'] = array(
      '#type' => 'checkbox',
      '#title' => t('Per Document Type'),
      '#default_value' => @$cache['per_doctype'],
  );

  $form['update'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
      '#submit' => array('forena_report_general_form_submit'),
  );

  $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => 'Cancel',
      '#submit' => array('forena_update_cancel'),
  );

  return $form;
}

function forena_report_general_menu_ajax($form) {
  drupal_get_messages('warning');
  return $form['menu'];
}


function forena_report_general_form_validate($form, &$form_state) {
  $values = $form_state['values'];
  if ($values['menu']['path']) {
    if (!valid_url(str_replace(':', '', $values['menu']['path']), FALSE)) {
      form_set_error('menu][path', t('Invalid Path'));
    }
  }
  if ($values['cache']['duration']) {
    try {
      $time = @new DateTime($values['cache']['duration']);
    }
    catch (Exception $e) {
    }
    if (!$time) form_set_error('cache][duration' , t('Invalid Cache Duration'));
  }
}

function forena_report_general_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  $r = Frx::Editor($report_name);
  $r_link = $r->report_link;

  // Title and category
  $r->setTitle($values['title']);
  $r->setCategory($values['category']);
  // Form options
  $options = array(
      'hidden' => $values['hidden'],
  );
  $r->setOptions($options);

  $menu = $r->getMenu();
  $menu['enabled'] = (int)(bool) @$menu['path'];
  $new_menu = $form_state['values']['menu'];
  $new_menu= $values['menu'];
  // Preprocess the menu
  if (!$values['menu']['enabled']) {
    $new_menu = array_fill_keys(array_keys($new_menu), '');
  }
  elseif ($new_menu['type']!=='normal-item') {
    $new_menu['plid'] = '';
    $new_menu['menu_name'] = '';
  }
  else {
    @list($new_menu['menu_name'], $new_menu['plid']) = explode(':', $new_menu['parent'], 2);
    unset($new_menu['parent']);
  }
  unset($menu['enabled']);

  // If the menu is different, save it.
  if ($values['menu']!= $menu) {
    $r->setMenu($new_menu);
  }

  $cache = $r->cache;
  if ($values['cache']!=$cache) {
    $r->setCache($values['cache']);
  }

  $r->update();
  $form_state['redirect'] = array("$r_link/edit", array('query' => Frx::parms()));
}

function forena_report_format_form($formid, $form_state, $report_name) {
  $r = Frx::Editor($report_name);

  drupal_set_title($r->title);
  $form = array();

  $doclist = Frx::Menu()->doc_formats;

  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name,
  );

  //begin checking doc generation options
  if ($r) $nodes = $r->simplexml->head->xpath('frx:docgen/frx:doc');
  if ($doclist) {
    $form['docgen'] = array(
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => t('Document Options'),
        '#description' => t('These are document transformation options. Options selected will display as links in your report view.')
    );

    //build the options and default list
    $options = array();
    $default = array();
    foreach ($doclist as $value) {
      $options[$value] = strtoupper($value);
      $doc =  isset($r) ? $r->simplexml->head->xpath('frx:docgen/frx:doc[@type="' . $value . '"]') : '';
      if ($doc && array_search($value, $doclist)!==FALSE) {
        $default[$value] = $value;
      }
    }

    //display checkboxes
    $form['docgen']['docs'] = array(
        '#type' => 'checkboxes',
        '#description' => t('If no options are selected, the system will display all of the above as available for this report.'),
        '#options' => $options,
        '#default_value' => $default
    );

    $form['update'] = array(
        '#type' => 'submit',
        '#value' => 'Update',

    );

    $form['cancel'] = array(
        '#type' => 'submit',
        '#value' => 'Cancel',
        '#submit' => array('forena_update_cancel'),
    );
  }
  return $form;
}

function forena_report_format_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  $r = Frx::Editor($report_name);
  $r_link = $r->report_link;



  // Doc gen settings.
  if (isset($form['docgen'])) {
    $r->setDocgen($values['docgen']['docs']);
  }

  $r->update();
  $form_state['redirect'] = array("$r_link/edit", array('query' => Frx::parms()));

}


/**
 * A form to preview and add data blocks to an existing report
 * @param $formid String representation fo form
 * @param $form_state Array of block type
 * @return unknown_type
 */
function forena_report_data_block_form($formid, &$form_state, $report_name, $action, $block_name, $id='') {
  $config = array();
  $r = Frx::Editor($report_name);
  // List of templates
  $templates = $r->templateOptions();
  if (!isset($form_state['storage'])) {
    $form_state['storage']['id'] = $id;
    $parms = $_GET;
    unset($parms['q']);
    $form_state['storage']['parms'] = $parms;
  }
  $id = isset($form_state['storage']['id']) ? $form_state['storage']['id'] : $id;
  $template_class = isset($form_state['values']['template_class']) ? $form_state['values']['template_class'] : 'FrxTable';
  if (isset($form_state['values']['config'])) {
    $config = array_merge($form_state['storage']['config'], $form_state['values']['config']);
  }
  elseif ($id && $action != 'add-data') {
    $template_class = $r->scrapeBlockConfig($id, $config);
  }
  $form_state['storage']['config'] = $config;
  //The default submit handler
  //If someone presses enter, this handler will execute
  $form['action'] = array(
    '#type' => 'value',
    '#value' => $action,
  );

  $form['add'] = array(
    '#type' => 'submit',
    '#value' => 'Add',
    '#submit' => array('forena_report_add_template_submit'),
    '#access' => $action=='add-data',
  );

  $form['update'] = array(
    '#type' => 'submit',
    '#value' => 'Update',
    '#submit' => array('forena_report_edit_template_submit'),
    '#access' => $action=='edit-data',
  );

  $form['prepend'] = array(
    '#type' => 'submit',
    '#value' => 'Insert',
    '#submit' => array('forena_report_prepend_template_submit'),
    '#access' => $action=='prepend-data',
  );

  $form['cancel'] = array(
    '#type' => 'submit',
    '#value' => 'Cancel',
    '#submit' => array('forena_update_cancel'),
  );

  $form['report_name'] = array(
  	'#type' => 'value',
    '#value' => $report_name,
  );

  $form['block_name'] = array(
  	'#type' => 'value',
    '#value' => $block_name,
  );

  $form['id'] = array(
  	'#type' => 'value',
    '#value' => $id,
  );

  $form['template_class'] = array(
    '#type' => 'select',
    '#title' => t('Template'),
    '#options' => $templates,
    '#default_value' => $template_class,
    '#ajax' => array(
  	   'callback' => 'forena_template_callback',
        'wrapper' => 'forena-template-wrapper'
     ),
  );

  $form['template']['#prefix'] = '<div id="forena-template-wrapper">';
  $form['template']['#suffix'] = '</div>';
  $form['template']['config'] = array(
      '#type' => 'fieldset',
      '#title' => 'Settings',
      '#collapsible'=>TRUE,
      "#tree" => TRUE,
  );

  // Generate Data block with preview
  $parms = $form_state['storage']['parms'];
  $r = Frx::Editor('__report_block_preview__');
  $r->setEditorParms($parms);
  $r->addBlock($block_name, $template_class, $config);
  $form['template']['config'] = array_merge($form['template']['config'], $r->templateConfigForm($template_class, $config));
  $r->update();
  $preview = $r->preview($parms);

  $form['template']['preview_button'] = array('#type' => 'button', '#value' => t('Preview'));



  if (!$preview['#has_data']) {
    $path = "reports/$report_name/edit/preview-data/$action/$block_name";
    if ($id) $path .= "/$id";
    $options = array();
    if ($parms) $options['query'] = Frx::parms();
    drupal_goto("$path", $options);
  }
  $form['template']['preview']['content'] = $preview['content'];
  $r->cancel();

  return $form;
}

function forena_report_data_block_form_validate($form, &$form_state) {
  $r = Frx::Editor($form_state['values']['report_name']);
  $template_class = $form_state['values']['template_class'];
  $config = array_merge($form_state['storage']['config'], $form_state['values']['config']);
  $errors = $r->templateConfigFormValidate($template_class, $config, 'template][config][');
  $form_state['storage']['config'] = $config;
  if ($errors) foreach ($errors as $key => $message) {
    form_set_error($key, $message);
  }
}

/**
 * Ajax callback to refresh the template data
 * @param unknown $form
 * @param unknown $form_state
 * @return unknown
 */
function forena_template_callback($form, &$form_state) {
  return $form['template'];
}

/**
 * Edit an existing id based on the template.
 * @param unknown $form
 * @param unknown $form_state
 */
function forena_report_edit_template_submit($form, &$form_state){
  extract($form_state['values']);
  $r = Frx::Editor($report_name);
  $parms = $form_state['storage']['parms'];
  $config = array_merge($form_state['storage']['config'], $config);
  $r = Frx::Editor($report_name);
  $r->setEditorParms($parms);
  $r->applyTemplate($id, $template_class, $config);
  $r->update();
  $form_state['redirect'] = array("reports/$report_name/edit", array('query' => Frx::parms()));
}

function forena_report_add_template_submit($form, &$form_state) {
  extract($form_state['values']);
  $parms = $form_state['storage']['parms'];
  $config = array_merge($form_state['storage']['config'], $config);
  $r = Frx::Editor($report_name);
  $r->setEditorParms($parms);
  Frx::Editor($report_name)->addBlock($block_name, $template_class, $config, $id);
  Frx::Editor() ->update();
  $form_state['redirect'] = array("reports/$report_name/edit", array('query' => Frx::parms()));
  }

function forena_report_prepend_template_submit($form, &$form_state) {
  extract($form_state['values']);
  $parms = $form_state['storage']['parms'];
  $r = Frx::Editor($report_name);
  $r->setEditorParms($parms);
  Frx::Editor($report_name)->prependBlock($block_name, $template_class, (array)$config, $id);
  Frx::Editor() ->update();
  $parms = $_GET;
  unset($parms['q']);
  $form_state['redirect'] = array("reports/$report_name/edit", array('query' => $parms));
}


function forena_report_translations($name_in='') {
  $desc = Frx::Menu()->parseURL($name_in);
  global $language;
  $cur_lang = $language->language=='en' ? '' : $language->language;
  $name = $desc['name'];
  $filename = $desc['filename'];
  $languages = language_list();
  @list($lang, $base_name) = explode('/', $name, 2);
  if (array_key_exists($lang, $languages)) {
    $name = $base_name;
  }
  elseif ($cur_lang &&  Frx::File()->exists( $cur_lang . '/' . str_replace('.', '/', $name_in) . '.frx')) {
    $name_in = $cur_lang . '.' . $name_in;
  }
  $data = array();
  $headers = array(t('Language'), t('Title'), t('Last Modified'), t('Operation'));
  // Generate list of all existing tranlsations and their status.
  foreach ($languages as $key => $language) {
    $i_name = ($key=='en') ? $name : $key . '/' . $name;
    $i_link = $key . '.' . $name;
    if (Frx::File()->exists($i_name . '.frx')) {
      $modified = format_date(filemtime(Frx::File()->path($i_name . '.frx')));
      $r = Frx::Editor($i_name);
      $title = $r->title;
      $edit = '';// l(t('Edit'), 'reports/' . str_replace('/', '.', $i_link) . '/edit', array('query' => array('target_language' => $key)));
    }
    else {
      $modified = '';
      $title ='';
      $edit = l(t('Add'), 'reports/' . $name_in . '/translate', array('query' => array('target_language' => $key)));
    }
    $data[] = array($language->native, $title, $modified, $edit);

  }

  return theme_table(array('header' => $headers, 'rows' => $data, 'attributes' => array(), 'caption' => '', 'sticky' => TRUE, 'colgroups' => array(), 'empty' => ''));
}



function forena_update_cancel($form, &$form_state) {
  extract($form_state['values']);
  $form_state['redirect'] = array("reports/$report_name/edit", array('query' => Frx::parms()));
  }

function forena_report_save_cancel($form, &$form_state) {
  extract($form_state['values']);
  Frx::Editor($report_name)->cancel();
  $form_state['redirect'] = array("reports/$report_name", array('query' => Frx::parms()));
}

function forena_select_block_list($report_name, $action='add-data', $id='') {
  $header = array(t('Block'), t('Access'), t('Keywords'));
  $blocks = Frx::DataFile()->userBlocks();
  $data=array();

  foreach ($blocks as $block_name => $block) {
    $blck = str_replace('/', '.', $block_name);
    $path = "reports/$report_name/edit/preview-data/$action/$blck";
    // Build the table.
    if ($id) $path .= "/$id";
    $data[] = array(
      l($block_name, $path),
      @$block->cache['access'],
      @$block->cache['keywords']
    );
  }

  // Theme the table
  $content['data_blocks'] = array('#markup' => theme('forena_data_table', array('header' => $header, 'rows' => $data)));
  return $content;
}

/**
 * Preview the block
 * @param unknown $report_name
 * @param unknown $block_name
 * @param string $id
 * @return multitype:string unknown multitype:string unknown  multitype:string  multitype:string Ambigous <The, string, A, Optional>
 */
function forena_report_block_select_form($form, &$form_state, $report_name, $action, $block_name, $id='') {
  $form['action'] = array(
      '#type' => 'value',
      '#value' => $action,
  );

  $form['report_name'] = array(
  	'#type' => 'value',
    '#value' => $report_name,
  );

  $form['block_name'] = array(
  	'#type' => 'value',
    '#value' => $block_name,
  );

  $form['id'] = array(
  	'#type' => 'value',
    '#value' => $id,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Select'),
  );
  return $form;
}

function forena_report_block_select_form_submit($form, &$form_state) {
  //Instantiate form variables
  extract($form_state['values']);
  $parms = $_GET;
  unset($parms['q']);
  $path = "reports/$report_name/edit/$action/$block_name";
  if ($id) $path = "$path/$id";
  $form_state['redirect'] = array($path, array('query' => $parms));
}
/**
 * Page to allow selection of a data block with parameters.
 * @param unknown $report_name
 * @param unknown $block_name
 * @param string $id
 * @return Ambigous <unknown, multitype:multitype:NULL  Ambigous <string, multitype:multitype:multitype:string   multitype:multitype:string unknown   > Ambigous <The, boolean, string> >
 */
function forena_report_block_preview($report_name, $action, $block_name, $id='') {
  $parms = $_GET;
  unset($parms['q']);
  $preview = forena_data_block_preview($block_name, $parms);
  if (@trim($preview['content']['#markup'])) {
    $content['select'] = drupal_get_form('forena_report_block_select_form', $report_name, $action, $block_name, $id);
  }
  else {
    drupal_set_message(t('Sample data is required in order to correctly generate a template.  Try changing parameters'), 'warning');
  }
  $content['preview'] = $preview;
  return $content;
}


function forena_report_delete_confirm($form, $form_state, $report_name, $id) {
  $parms = $_GET;
  unset($parms['q']);

  $form['report_name'] = array(
    '#type' => 'value',
    '#value' => $report_name,
  );

  $form['id'] = array(
    '#type' => 'value',
    '#value' => $id,
  );
  $path = array('path' => "reports/$report_name/edit", 'options' => array('query' => $parms));
  $form = confirm_form(
    $form,
    t('Are you sure you would like to remove the %s content', array('%s' => $id)),
    $path,
    'Detail blocks contain inside this block will also be deleted.',
    'Yes', 'No'
  );
  return $form;
}

function forena_report_delete_confirm_submit($form, &$form_state) {
  extract($form_state['values']);

  // Delete the block
  $r = Frx::Editor($report_name);
  $r->deleteNode($id);
  $r->update();


  $parms = $_GET;
  unset($parms['q']);
  $form_state['redirect'] = array("reports/$report_name/edit", array('query' => $parms));
}

/**
 * Form field translation editor.
 * @param unknown $form
 * @param unknown $form_state
 * @param unknown $report_name
 * @param unknown $id
 * @return multitype:string multitype:string  Ambigous <The, string, A, Optional>
 */
function forena_report_field_form($form, &$form_state, $report_name, $id) {
  $r = Frx::Editor($report_name);
  $id = urldecode($id);
  $form['report_name'] = array(
  	'#type' => 'value',
    '#value' => $report_name,
  );
  $field = $r->frxReport->getField($id);
  $form['field'] = array('#tree' => TRUE);

  $form['field']['id'] = array(
      '#type' => 'value',
      '#value' => $id,
  );

  $form['field']['id_label'] = array(
  	'#type' => 'item',
    '#markup' => $id,
    '#title' => t('token'),
  );

  $form['field']['format'] = array(
      '#type' => 'textfield',
      '#title' => t('format'),
      '#default_value' => $field['format'],
      '#size' => 30,
      '#autocomplete_path' => 'forena/fields/format/autocomplete',
      '#description' => t('Format a date and time field by entering the name of a supported format function. Enter a "*" to see all available formats.')
  );

  $form['field']['format-string'] = array(
      '#type' => 'textfield',
      '#title' => t('format-string'),
      '#default_value' => $field['format-string'],
      '#size' => 30,
      '#description' => t('The display type of your format.')
  );

  $form['field']['link'] = array(
      '#type' => 'textfield',
      '#title' => t('link'),
      '#default_value' => $field['link'],
      '#size' => 100,
      '#maxlength' => 256,
      '#description' => t('Create a link that incorporates this field, e.g "profile/{field_name}" will create a link to this field_name\'s profile. *Note the field must be wrapped in {}.')
  );

  $form['field']['add-query'] = array(
      '#type' => 'checkbox',
      '#title' => t('Pass through url parameters'),
      '#default_value' => $field['add-query'],
      '#description' => t('Automatically pass through url parameters on this link'),
  );

  $form['field']['rel'] = array(
      '#type' => 'textfield',
      '#title' => t('rel') ,
      '#description' => t('Relationship attribute to apply to the link.'),
      '#size' => 100,
      '#maxlength' => 256,
      '#default_value' => $field['rel'],
  );

  $form['field']['class'] = array(
      '#type' => 'textfield',
      '#title' => t('class') ,
      '#description' => t('Class applied to be applied to the link.'),
      '#size' => 100,
      '#maxlength' => 256,
      '#default_value' => $field['class'],
  );

  $form['field']['target'] = array(
      '#type' => 'textfield',
      '#title' => t('target'),
      '#default_value' => $field['target'],
      '#size' => 30,
      '#description' => t('Link target eg. _BLANK,  Targets that begin with "popup" will be opened in a new window using javascript.')
  );

  $form['field']['default'] = array(
      '#type' => 'textfield',
      '#title' => t('default value'),
      '#default_value' => $field['default'],
      '#size' => 30,
      '#description' => t('The value to be displayed in the report when no value exists.')
  );

  $form['field']['calc'] = array(
    '#type' => 'textfield',
    '#title' => t('calulation'),
    '#default_value' => $field['calc'],
    '#size' => 100,
    '#description' => t('Enter a valid XPath expression used to calculate this field. Tokens (e.g. {title} may be used in the calculation.'),
  );

  $form['field']['context'] = array(
    '#type' => 'textfield',
    '#title' => t('context'),
    '#default_value' => $field['context'],
    '#size' => 100,
    '#description' => t('Enter the data context used in the calculation. ')
  );

  $form['update'] = array(
    '#type' => 'submit',
    '#value' => t('Update'),
  );

  $form['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#submit' => array('forena_update_cancel'),
  );

  return $form;
}

function forena_report_field_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $name = $values['report_name'];

  $r = Frx::Editor($name);
  /*now build the fields*/
  $fields = $values['field'];

  $r->setFields(array($fields));
  $r->update();
  $parms = Frx::parms();
  $form_state['redirect'] = array("reports/$name/edit", array('query' => $parms));

}


/**
 * Implementation of hook_form
 * Edits a single parameter config
 * @param unknown $formid
 * @param unknown $form_state
 * @param $report_name string name of report to edit
 * @param $id string name of parameter to edit
 * @return multitype:multitype:boolean string  multitype:string  multitype:string multitype:string   multitype:string Ambigous <An, string>  multitype:string unknown
 */
function forena_report_parameter_config_form($formid, $form_state, $report_name, $id) {

  $r = Frx::Editor($report_name);


  // Set up basic control types.
  $control_types= array('textfield' => 'Text',
      'hidden' => 'Hidden',
      'radios' => 'Radios',
      'checkbox' => 'Checkbox',
      'checkboxes' => 'Checkboxes',
      'select' => 'Select',
      'selectajax' => 'Select with Refresh',
      'multiselect' => 'Multi-Select',
      'multiselectajax' => 'Multi-select With Refresh',
  );

  // Supported module specific control types.
  if (module_exists('date_api')) {
    $control_types['date_select'] =  'Date Select';
  }
  if (module_exists('date_popup')) {
    $control_types['date_popup'] = 'Date Popup';
  }

  drupal_set_title($r->title);
  $form = array();

  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name,
  );

  $parameters = $r->frxReport->parametersArray();

  if (!isset($parameters[$id])) {
    drupal_not_found();
    drupal_exit();
  }
  $parm = $parameters[$id];

  //make a subtree for each param
  $form['parm'] = array('#tree' => TRUE);

  $form['parm']['id'] = array(
      '#type' => 'value',
      '#value' => $id,
  );

  $form['parm']['id_label'] = array(
  	'#type' => 'item',
    '#title' => t('Name'),
    '#markup' => $id,
    '#description' => t('The machine friendly name of the parameter as used in the query or report.  ')
  );

  $form['parm']['label'] = array(
    '#type' => 'textfield',
    '#title' => t('label'),
    '#default_value' => @$parm['label'],
    '#required' => FALSE,
    '#description' => t('A descriptive name of the parameter to be displayed on a form.')
  );

  $form['parm']['require'] = array(
      '#type' => 'radios',
      '#title' => t('require'),
      '#default_value' => @$parm['require'] ? $parm['require'] : "0",
      '#options' => array("1" => t('Yes'), "0" => t('No')),
      '#description' => t('Requires a value for this parameter to display the report. If there is not a default value, the user will be prompted to enter a value.')
  );

  $form['parm']['default'] = array(
    '#type' => 'textfield',
    '#title' => 'default value',
    '#default_value' => @$parm['default'],
    '#description' => t('The value of the parameter for the initial view of the report.')
  );

  $form['parm']['desc'] = array(
      '#type' => 'textfield',
      '#title' => t('description'),
      '#default_value' => @$parm['desc'],
      '#description' => t('Enter a helpful description about this parameter. This will display on the form when the user is prompted to enter a parameter.'),
  );

  $form['parm']['type'] = array(
      '#type' => 'select',
      '#title' => t('Input Control Type'),
      '#default_value' => @$parm['type'],
      '#options' => $control_types,
      '#description' => t('Enter a helpful description about this parameter. This will display on the form when the user is prompted to enter a parameter.'),
  );

  $form['parm']['data_source'] = array(
      '#type' => 'textfield',
      '#title' => t('Source Data'),
      '#default_value' => @$parm['data_source'],
      '#autocomplete_path' => 'forena/data_block/autocomplete',
      '#description' => t('Data block to be used for select, multiselect, and checkboxes types'),
  );

  $form['parm']['data_field'] = array(
      '#type' => 'textfield',
      '#title' => t('Data Field'),
      '#default_value' => @$parm['data_field'],
      '#description' => t('Column in data block to be used data of select boxes and radio buttons. '),
  );

  $form['parm']['label_field'] = array(
      '#type' => 'textfield',
      '#title' => t('Label Field'),
      '#default_value' => @$parm['label_field'],
      '#description' => t('Column in data block to be used for the label for selet boxes and radio buttons.')
  );

  $form['parm']['options'] = array(
      '#type' => 'textfield',
      '#title' => t('Options'),
      '#default_value' => @$parm['options'],
      '#description' => t('Used to configure the control type above options depend on the control type above.')
  );

  $form['parm']['class'] = array(
      '#type' => 'textfield',
      '#title' => t('Class'),
      '#description' => t('Specify the html class you wish to apply to this parameter'),
      '#default_value' => @$parm['class'],
  );

  $form['update'] = array(
      '#type' => 'submit',
      '#value' => 'Update',
  );

  $form['cancel'] = array(
        '#type' => 'submit',
        '#value' => 'Cancel',
        '#submit' => array('forena_update_cancel'),
    );
  return $form;

}

function forena_report_parameter_config_form_submit($formid, &$form_state) {
  $r = Frx::Editor($form_state['values']['report_name']);
  $parameters = $r->frxReport->parametersArray();
  $id = $form_state['values']['parm']['id'];
  $parameters[$id] = array_merge($parameters[$id], $form_state['values']['parm']);
  $r->setParameters($parameters);
  $r->update();
  $r_link = $r->report_link;
  $form_state['redirect'] = array("$r_link/edit/parameters", array('query' => Frx::parms()));
}


function forena_report_parameters_form($form, &$form_state, $report_name) {
  $r = Frx::Editor($report_name);
  $r_link = $r->report_link;
  if (!isset($form_state['storage']['parameters']))  {
    $form_state['storage']['parameters'] = $r->frxReport->parametersArray();

  }
  $parameters = $form_state['storage']['parameters'];
  $links = array();
  $links[] = array('href' => $r->report_link . '/edit/add-parameter', 'title' => 'Add Parameter' );
  $form['add_link'] = array(
      '#type' => 'markup',
      '#markup' => theme('links',
          array(
              'links' => $links,
              'attributes' => array('class' => 'action-links'),
          )
      )
  );
  $i=0;
  $form['parameters'] = array('#tree' => TRUE);
  foreach ($parameters as $parameter) {
    $id = urlencode($parameter['id']);
    $ctl = array();
    $ctl['id_label'] = array(
        '#type' => 'item',
        '#title' => 'id',
        '#title_display' => 'invisible',
        '#markup' => check_plain($parameter['id']) . $r->l_icon("$r_link/edit/edit-parameter/$id", 'configure.png', 'Configure Parameter'),
        );

    $ctl['label'] = array(
    	'#type' => 'textfield',
      '#size' => 30,
      '#default_value' => @$parameter['label'],
      '#title' => t('Label or Prompt'),
      '#title_display' => 'invisible',
    );

    $ctl['default'] = array(
    	'#type' => 'textfield',
      '#size' => 30,
      '#title' => t('Default Value'),
      '#default_value' => @$parameter['default'],
      '#title_display' => 'invisible',
    );

    $ctl['actions'] = array(
    	'#type' => 'actions',
      '#title' => t('Actions'),
      '#title_display' => 'invisible',
    );

    $ctl['actions']['remove'] = array(
    	'#type' => 'submit',
      '#value' => t('Remove'),
      '#submit' => array('forena_report_parameters_remove_submit'),
      '#name' => 'remove-' . $parameter['id'],
    );

    $ctl['weight'] = array(
    	'#type' => 'weight',
      '#title' => 'Weight',
      '#default_value' => $i,
      '#title_display' => 'invisible',
    );
    $i++;
    $form['parameters'][$parameter['id']] = $ctl;
  }

  $form['report_name'] = array(
  	'#type' => 'value',
    '#value' => $report_name,
  );

  $form['update'] = array(
  	'#type' => 'submit',
    '#value' => t('Update'),
  );

  $form['cancel'] = array(
  	'#type' => 'submit',
    '#value' => t('Cancel'),
    '#submit' => array('forena_update_cancel'),
  );

  return $form;

}

function forena_report_parameters_form_submit($form, &$form_state) {
  $parms = array();
  $new_parameters = $form_state['values']['parameters'];
  $parm_weights = array();
  foreach ($new_parameters as $id => $parm) {
    $parm_weights[$id] = $parm['weight'];
  }
  asort($parm_weights);
  // Reorder by weight;

  foreach ($parm_weights as $id => $weight) {
    $parms[$id] = array_merge($form_state['storage']['parameters'][$id], $new_parameters[$id]);
  }

  // Now replace parameters
  $r = Frx::Editor($form_state['values']['report_name']);
  $r_link = $r->report_link;
  $r->setParameters($parms);
  $r->update();

  $form_state['redirect'] = array("$r_link/edit", array('query' => Frx::parms()));
}

/**
 * Removes a parameter from the form until update is performed.
 * @param unknown $form
 * @param unknown $form_state
 */
function forena_report_parameters_remove_submit($form, &$form_state) {
  $new_parameters = $form_state['values']['parameters'];
  $form_state['rebuild'] = TRUE;
  $key = $form_state['triggering_element']['#parents'][1];
  unset($form_state['storage']['parameters'][$key]);
  unset($form_state['input']['parameters'][$key]);
}


function forena_report_add_parameter_form($form, &$form_state, $report_name) {

  $r = Frx::Editor($report_name);
  //drupal_set_title($r->title);
  $form = array();

  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name,
  );

  $form['parameters'] = array('#tree' => TRUE);
  $form['parameters']['label'] = array(
  	'#type' => 'textfield',
    '#title' => t("Label"),
    '#description' => t('The Label to display to the user. ')
  );

  $form['parameters']['id'] = array(
      '#type' => 'forena_machine_name',
      '#title' => t('id'),
      '#required' => TRUE,
      '#description' => t('The machine friendly name of the as used in queries or reports.')
  );


  $form['add'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
  );

  $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => array('forena_update_cancel'),
  );

  return $form;
}

function forena_report_add_parameter_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  $param = $values['parameters'];
  $r = Frx::Editor($report_name);
  $r_link = $r->report_link;
  //add the new parms
  $r->addParameters($param['id']);
  $r->update();
  $form_state['redirect'] = array("$r_link/edit/parameters", array('query' => Frx::parms()));
}

/**
 * Theme function for the draggable parameters form.
 * @param unknown $variables
 * @return Ambigous <string, A, boolean>
 */
function theme_forena_report_parameters_form($variables) {
  $form = &$variables['form'];
  $headers = array();
  $rows = array();
  // Iterate the controls of the table giving some labels.
  foreach (element_children($form['parameters']) as $e_key) {
    $form['parameters'][$e_key]['weight']['#attributes']['class'] = array('parameters-weight');
    $row = array();
    foreach (element_children($form['parameters'][$e_key]) as $ctl) {
      if (isset($form['parameters'][$e_key][$ctl]['#title'])) {
        $headers[$ctl] = $form['parameters'][$e_key][$ctl]['#title'];
      }
      $row[] = drupal_render($form['parameters'][$e_key][$ctl]);
    }
    $rows[] = array('data' => $row, 'class' => array('draggable'));
  }
  $headers = array_values($headers);
  $output = theme('table', array('header' => $headers, 'rows' => $rows, 'attributes' => array('id' => 'forena-parameters')));
  // Render the rest of the children.
  $output .= drupal_render_children($form);
  drupal_add_tabledrag('forena-parameters', 'order', 'group', 'parameters-weight');
  return $output;
}

function __forena_element_theme(&$element) {

}

function theme_forena_element_draggable($variables) {
  $output = '';
  $element = $variables['element'];
  $id = $element['#draggable_id'];
  $headers = array();
  $rows = array();
  foreach (element_children($element) as $e_key) {
    $element[$e_key]['weight']['#attributes']['class'] = array($id . '-weight');
    $row = array();
    foreach (element_children($element[$e_key]) as $ctl) {
      if (isset($element[$e_key][$ctl]['#title'])) {
        $headers[$ctl] = $element[$e_key][$ctl]['#title'];
        $element[$e_key][$ctl]['#title_display'] = 'invisible';
        $e = drupal_render($element[$e_key][$ctl]);
        $row[] = $e;
      }



    }
    $rows[] = array('data' => $row, 'class' => array('draggable'));
  }
  $output = theme('table', array('header' => $headers, 'rows' => $rows, 'attributes' => array('id' => $id)));
  drupal_add_tabledrag($id, 'order', 'group', $id . '-weight');
  //$output .= drupal_render_children($element);
  return $output;
}


function forena_report_layout_form($form, &$form_state, $report_name) {
  $desc = Frx::Menu()->parseURL($report_name);
  $name = $desc['name'];
  $r = Frx::Editor($name);
  drupal_set_title(filter_xss($r->title));
  // Need to get all option attributes
  $frx_options = $r->getOptions();
  $report_form = @$frx_options['form'];
  $filter = variable_get('forena_input_format', 'none');
  if (isset($frx_options['input_format'])) $filter = $frx_options['input_format'];

  $body = $r->simplexml->body->asXML();
  $css = @(string)$r->simplexml->head->style;

  //array of xml attributes that are required to have a value
  $required = array('id' => TRUE, 'label' => TRUE);

  $form['report_name'] = array(
      '#type' => 'value',
      '#value' => $report_name,
  );


  // Skin control
  $frx_options = $r->getOptions();
  $skin = @$frx_options['skin'];
  $skins = Frx::File()->skins();
  $form['skin'] = array(
      '#type' => 'select',
      '#title' => t('Skin'),
      '#options' => $skins,
      '#empty_option' => t('--use default--'),
      '#default_value' => $skin,
      '#description' => t('The page style of your report.  The {skin}.skinfo file specifies css and js file in your report.')
  );


  $form['body'] = array(
      '#type' => 'text_format',
      '#title' => t('Body'),
      '#default_value' => $body,
      '#rows' => 25,
      '#format' => $filter,
  );

  $form['style'] = array(
      '#type' => 'fieldset',
      '#title' => t('CSS Styles'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
  );

  $form['style']['css'] = array(
      '#type' => 'textarea',
      '#default_value' => $css,
      '#description' => t('Specify small css snipets that can be used in the reports.'),
      '#rows' => 10,
      '#cols' => 80,
  );

  $form['buttons']['update'] = array(
      '#type' => 'submit',
      '#value' => 'Update',
      '#submit' => array('forena_report_layout_form_submit'),
  );

  $form['buttons']['cancel'] = array(
      '#type' => 'submit',
      '#value' => 'Cancel',
      '#submit' => array('forena_update_cancel'),
  );  return $form;

  return $form;
}

function forena_report_layout_form_validate($form, &$form_state) {
  $values = $form_state['values'];
  $body = $values['body']['value'];

  $doc_prefix = '<?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE root [
    <!ENTITY nbsp "&#160;">
    ]>';
  if ($body) {
    $body_doc = new DOMDocument('1.0', 'UTF-8');
    $body_xml =  $doc_prefix . '<html xmlns:frx="urn:FrxReports">' . $body . '</html>';
    if (@$body_doc->loadXML($body_xml) === FALSE) {
      form_set_error('body', t('Invalid XHTML Document. Check for unclosed tags or stray &'));
    }
  }

}

/**
 * builds a string of the xml document,
 * submits it to forena_save_report.
 */
function forena_report_layout_form_submit($form, &$form_state) {
  $nodes = array();
  $rebuild_menu = FALSE;
  $values = $form_state['values'];
  $report_name = $values['report_name'];
  $r = Frx::Editor();
  $r_link = $r->report_link;

  $options = array();
  $filter = variable_get('forena_input_format', 'none');
  if (isset($values['body']['format']) && $values['body']['format'] != $filter) {
    $options['input_format'] = $values['body']['format'];
  }
  $options['skin'] = $values['skin'];
  $r->setOptions($options);

  // Body
  $r->setBody($values['body']['value']);
  // CSS
  $r->setStyle($values['css']);
  $r->update();
  // If we changed the menu we need to rebuild it.
  $form_state['redirect'] = array("$r_link/edit", array('query' => Frx::parms()));
}


/**
 * Remove the report from the database and file system.
 * @param string $report_name
 */
function forena_delete_report($report_name) {

  $filepath = $report_name . '.frx';
  $do = Frx::File()->delete($filepath);
}
