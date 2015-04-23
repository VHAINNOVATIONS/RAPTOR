<?php
/**
 * @file forena.data.inc
 * Common files for displaying and previewing data blocks.
 */
function forena_display_blocks() {

  $header = array(t('Block'), t('Access'), t('Keywords'), t('Actions'));
  Frx::BlockEditor()->revertDBBLocks();
  $blocks = Frx::DataFile()->userBlocks();
  if(module_exists('forena_query') && user_access('build forena sql blocks')) {
    $links[] = array('href' => 'admin/structure/forena/data/add', 'title' => 'Create New SQL Query' );
    $content['add']['#markup'] = theme('links',
        array(
            'links' => $links,
            'attributes' => array('class' => 'action-links'),
        )
    );
  }
  $data=array();

  foreach ($blocks as $block_name => $block) {
    $blck = str_replace('/', '.', $block_name);
    $actions = '';
    if ($block->cache['type'] == 'sql'  && module_exists('forena_query')){
      if ($block->include) {
        $actions = l(t('Override'), 'admin/structure/forena/data/' . $blck . '/edit');
      }
      else {
        $actions = l(t('Edit'), 'admin/structure/forena/data/' . $blck . '/edit');
      }
      $clone = l(t('Clone'), 'admin/structure/forena/data/clone/' . $blck);
      // Determine the nature of the report delete link.
      if ($block->override && !$block->include) {
        $actions .= ' ' .l(t('Revert'), 'admin/structure/forena/data/' . $blck . '/delete', array('query' => array('destination' => 'admin/structure/forena/data')));
      }
      else if (!$block->include) {
        $actions .= ' ' . l(t('Delete'), 'admin/structure/forena/data/' . $blck . '/delete', array('query' => array('destination' => 'admin/structure/forena/data')));
      }
    }
    // Build the table.
    $data[] = array(
        l($block_name, "forena/data/$blck"),
        @$block->cache['access'],
        @$block->cache['keywords'],
        $actions,
    );
  }
  // Theme the table
  $output = "<div id='forena-data-block-list'>\n";
  $output .= theme('forena_data_table', array('header' => $header, 'rows' => $data));
  $output .= "</div>\n";
  $content['data_blocks'] = array('#markup' => $output);
  return $content;
}


function forena_data_block_preview($block_name, $parms = array(), $edit=FALSE) {
  $o = Frx::Editor('__forena_block_preview__');
  $o->frxReport->block_edit_mode = $edit;
  $o->alterParameters($parms);
  $o->setEditorParms($parms);
  $config = array();
  $o->addBlock($block_name, 'FrxTable', $config);
  $o->update();
  $content =   $o->preview($parms);
  $o->cancel();
  return $content;
}

function forena_data_preview() {
  $args = func_get_args();
  $block_name = implode('/', $args);
  $parms = $_GET;
  unset($parms['q']);
  $content['add_report']['form'] = drupal_get_form('forena_data_quick_report_form', $block_name);

  $content['preview'] = forena_data_block_preview($block_name, $parms);
  $content['preview']['#prefix'] = '<div id="forena-block-preview">';
  $content['preview']['#suffix'] = '</div>';
  $m_path = drupal_get_path('module', 'forena');
  $content['#attached'] = array(
    'js' => array(
      "$m_path/forena.admin.js",
      forena_library_file('dataTables'),
    ),
    'css' => array(
      "$m_path/forena.css",

    ),
  );
  return $content;
}

/**
 * Create a report quickly from a data block
 * @param unknown $form
 * @param unknown $form_state
 * @param unknown $block_name
 */
function forena_data_quick_report_form($form, &$form_state, $block_name) {
  require_once 'forena.report.inc';

  // Get the add form.
  $form['add'] = array('#type' => 'fieldset', '#title' => t('Quick Report'), '#collapsible' => TRUE, '#collapsed' => TRUE);
  $form['add'] = array_merge($form['add'], forena_add_report_form($form, $form_state));

  $form['block_name'] = array(
  	'#type' => 'value',
    '#value' => $block_name,
  );

  $form['add']['save']['#submit'][] = 'forena_data_quick_report_form_submit';
  return $form;
}

function forena_data_quick_report_form_submit($form, &$form_state) {
  Frx::BlockEditor($form_state['values']['block_name'])->saveParms();
  $report_name = str_replace('/', '.', $form_state['values']['report_name']);
  $block_name = str_replace('/', '.', $form_state['values']['block_name']);
  $path =  "reports/$report_name/edit/add-data/$block_name";
  $parms = $_GET;
  unset($parms['q']);
  $form_state['redirect'] = array($path, array('query' => $parms));
}