<?php
/**
 * @file forena.datablock.inc
 * Data block building and testing forms.
 */
require_once 'FrxDataSource.inc';
require_once 'forena.data.inc';

/**
 * Create new sql data form hook
 * @param unknown $form
 * @param unknown $form_state
 * @return unknown
 */
function forena_query_create_block_form($form, &$form_state, $provider='') {
  $providers = Frx::RepoMan()->repositories;
  $form['provider'] = array(
    '#required' => TRUE,
    '#title' => t('Data Source'),
    '#type' => 'select',
    '#options' => Frx::RepoMan()->listRepos(),
    '#default_value' => $provider,

  );

  $form['block_name'] = array(
  	'#type' => 'forena_machine_name',
    '#required' => TRUE,
    '#title' => t('Block to create'),
  );

  $form['add'] = array(
  	'#type' => 'submit',
    '#value' => t('Create'),
  );
  return $form;
}

/**
 * Create a new block.
 * @param unknown $form
 * @param unknown $form_state
 */
function forena_query_create_block_form_validate($form, &$form_state) {
  extract ($form_state['values']);
  $pattern = "@^[A-Za-z0-9\/\_]$@";
  if (preg_match($pattern, $block_name)) {
    form_set_error('block_name', t('Invalid characters in block name') );
  }
  else {
    $path = $provider . '/' . $block_name;
    $block = Frx::RepoMan()->loadBlock($path);
    if ($block) form_set_error('block_name', t('Data Block already exists'));
  }
}

/**
 * Submit handler that creates the block
 * @param unknown $form
 * @param unknown $form_state
 */
function forena_query_create_block_form_submit($form, &$form_state) {
  extract($form_state['values']);
  $path = $provider . '/' . $block_name;
  Frx::BlockEditor($path)->update(
  	array('type' => 'sql',
  	      'access' => '',
  	      'file' => '',
  	      'source' => '',
  	)
  );
  $form_state['redirect'] = 'admin/structure/forena/data/' . str_replace('/', '.', $path) . "/edit";
}

/**
 * Basic SQL Editor form hook implementation
 * @param unknown $form
 * @param unknown $form_state
 * @param string $block_name
 * @return multitype:NULL
 */
function forena_query_editor($form, &$form_state, $block_name='') {
  static $first=TRUE;

  if ($first) {
    drupal_add_library('system', 'ui.autocomplete');
    $m_path = drupal_get_path('module', 'forena_query');
    drupal_add_js("$m_path/forena_query.js");
    drupal_add_css("$m_path/forena_query.css");
    @list($repos, $name) = explode('.', $block_name);
    drupal_add_js(array('forenaSQLEditor' => array('repos' => $repos)), 'setting');
    $first = FALSE;
  }
  $o = Frx::BlockEditor($block_name);
  $block = $o->block;
  if (!isset($form_state['storage'])) {
    $form_state['storage']['block_name'] = $block_name;
    $form_state['storage']['parameters'] = array();
    $form_state['storage']['block'] = $block;
  }
  drupal_set_title(str_replace('.', '/', $block_name));
  $block = $form_state['storage']['block'];
  $builder = isset($block['builder']) ? $block['builder'] : 'FrxQueryBuilder';
  $builders = $o->builderList();
  $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
  );

  $form['advanced']['builder'] = array(
    '#type' => 'select',
    '#title' => t('Template'),
    '#options' => $builders,
    '#access' => FALSE,
    '#default_value' => $builder,
  );

  $form['advanced']['access'] = array(
      '#title' => t('Data Security'),
      '#description' => t('Value depends on the security for the data block,
            but for drupal data, this is a right as defined by the module provided.  '),
      '#type' => 'textfield',
      '#access' => user_access('forena data security'),
      '#default_value' => @$block['access'],
  );
  $form['config'] = $o->configForm($builder, $block);
  $form['config']['#tree'] = 'true';
  $form['config']['#prefix'] = "<div id='query-builder-config'>\n";
  $form['config']['#suffix'] = '</div>';


  $form['save'] = array(
      '#type' => 'submit',
      '#value' => 'Save',
      '#validate' => array('forena_query_editor_validate'),
      '#submit' => array('forena_query_editor_update', 'forena_query_save_block')
  );
  $form['cancel'] = array('#type' => 'submit', '#value' => t('Cancel'), '#submit' => array('forena_query_close'), '#limit_validation_errors' => array());
  $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Test Changes'),
      '#submit' => array('forena_query_editor_update', 'forena_query_preview'),
      '#ajax' => array(
  	    'callback' => 'forena_query_preview_ajax',
        'wrapper' => 'forena-query-preview',
      ),

  );


  $tokens = $o->tokens();
  $form['preview'] = array(
  	'#prefix' => '<div id="forena-query-preview">',
    '#suffix' => '</div>',
  );
  if ($tokens) {
    $form['preview']['parameters'] = array(
        '#title' => 'Parameters',
        '#type' => 'fieldset',
        '#tree' => TRUE
    );

    foreach ($tokens as $name) {
      $ctl = array(
          '#type' => 'textfield',
          '#title' => check_plain($name),
      );

      $form['preview']['parameters'][$name] = $ctl;
    }
  }

  $form['preview']['report'] = @$form_state['storage']['preview'];
  return $form;
}

function forena_query_preview_ajax($form) {
  drupal_get_messages('warning');
  return $form['preview'];
}

function forena_query_editor_validate($form, &$form_state) {
   $o = Frx::BlockEditor();
   $o->load($form_state['storage']['block_name']);
   if (user_access('forena data security')) {
     $r = $o->repos();
     $access = $r->access($form_state['values']['access']);
     if (!$access) {
       form_set_error('access', t('You must be granted access to use this right.'));
     }
   }
   if (!Frx::DataFile()->isWritable(Frx::BlockEditor()->block_name . '.sql')) {
     form_set_error('block', t('Insufficient Permission to save file'));
   }
   $config = array_merge($form_state['storage']['block'], $form_state['values']['config']);
   $o->configValidate($form_state['values']['builder'], $config);
   $form_state['storage']['block'] = $config;
}

function forena_query_preview($form, &$form_state) {
  $form_state['rebuild'] = TRUE;

  $parms = $form_state['storage']['parameters'];
  if (isset($form_state['values']['parameters'])) {
    $parms = $form_state['storage']['parameters'] = $form_state['values']['parameters'];
  }
  $preview = forena_data_block_preview($form_state['storage']['block_name'], $parms, TRUE);
  $form_state['storage']['preview'] = $preview['content'];
}

function forena_query_delete_form($form, &$form_state, $block_name) {
  $block_name = str_replace('.', '/', $block_name);
  $o =  Frx::BlockEditor($block_name);
  $block = $o->block;
  $file = @$block['file'];
  if ($o->modified) {
    $o->cancel();
    drupal_not_found($block_name);
    exit;
  }

  $form_state['storage']['block_name'] = $block_name;

  $form['block_title'] = array('#markup' => "<h2>$block_name</h2>");

  $form['file'] = array('#markup' => "<pre>$file</pre>");

  $form['confirm'] = array(
    '#type' => 'checkbox',
    '#title' => t('Are you sure you want to delete this block?'),
    '#required' => TRUE,
  );

  $form['block_name'] = array(
  	'#type' => 'value',
    '#value' => $o->block_name,
  );

  $form['delete'] = array(
  	'#type' => 'submit',
    '#value' => t('Delete'),
    '#submit' => array('forena_query_delete_block', 'forena_query_close'),
  );
  $form['cancel'] = array('#type' => 'submit', '#value' => t('Cancel'), '#submit' => array('forena_query_close'), '#limit_validation_errors' => array());
  return $form;
}


function forena_query_save_block($form, &$form_state) {
  $form_state['rebuild'] = FALSE;
  Frx::BlockEditor()->save();
}

function forena_query_delete_block($form, &$form_state) {
  $block_name = $form_state['values']['block_name'];
  Frx::RepoMan()->deleteBlock($block_name);
  $form_state['redirect'] = 'admin/structure/forena/data';
  drupal_set_message(t('Deleted %s', array('%s' => $block_name)));
}

function forena_query_close($form, &$form_state) {
  Frx::BlockEditor($form_state['storage']['block_name'])->cancel();
  $form_state['rebuild'] = FALSE;
  $form_state['redirect'] = 'admin/structure/forena/data';

}

/**
 * Function to allow all normal button submits to be for the form.
 * @param unknown_type $form
 * @param unknown_type $form_state
 */
function forena_query_editor_update($form, &$form_state) {
  // Instantiate form values
  $form_state['values']['config']['access'] = $form_state['values']['access'];
  $o = Frx::BlockEditor($form_state['storage']['block_name']);
  $config = array_merge($form_state['storage']['block'], $form_state['values']['config']);
  Frx::BlockEditor()->update($config);
  $form_state['rebuild'] = TRUE;
}

/**
 * Standard drupal autocomplete that searches for tables.
 * @param string $repos
 * @param string $str
 */
function forena_query_autocomplete($repos='', $str='') {
  $tables = array();
  if ($repos && $str) {
    $r = Frx::RepoMan()->repository($repos);
    if ($r && method_exists($r, 'searchTables')) {
      $tables = $r->searchTables($str);
      $tables = array_combine($tables,$tables);
    }
  }
  print drupal_json_output($tables);
}

/**
 * Standard drupal autocomplete that searches for tables or column values if a table is specified.
 * @param string $repos
 * @param string $str
 */
function forena_query_autocomplete_simple() {
  $repos = @$_GET['repos'];
  $str = @$_GET['term'];
  $table = @$_GET['table'];
  $alias = @$_GET['alias'];
  $values = array();
  if ($repos) {
    // If we have a table query the columns
    $r = Frx::RepoMan()->repository($repos);
    if ($table && $alias) {
      if ($r && method_exists($r, 'searchTableColumns')) {
        $cols = $r->searchTableColumns($table, $str);
        if ($cols) foreach ($cols as $col) {
          $values[] = $alias . '.' . $col;
        }
      }
    }
    else if ($str) {
      // Otherwise query the table
      if ($r && method_exists($r, 'searchTables')) {
        $values = $r->searchTables($str);
      }
    }
  }

  print drupal_json_output($values);
}




