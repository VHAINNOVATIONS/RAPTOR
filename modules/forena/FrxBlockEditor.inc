<?php
class FrxBlockEditor {
  public $block = array();
  public $block_name;
  public $modified;
  public $provider;
  public $cache = FALSE;    // Turn on caching of parameter to facilitate block caching.
  private $teng;
  public $edit;
  private $builders;
  private $new_block = array(
    'type' => 'sql',
    'file' => '',
    'access' => '',
    'source' => '',
    'info' => array(),
  );


  public function __construct($block_name = '', $edit = TRUE) {
    $this->edit = $edit;
    if ($block_name) {
      $this->load($block_name, $edit);
    }
    $this->teng = Frx::SyntaxEngine(FRX_SQL_TOKEN, ':');
  }


  /**
   * Block loader method.
   * @param  $block_name
   */
  public function load($block_name, $edit=TRUE) {
    $block_name = str_replace('.', '/', $block_name);
    @list($provider, $path) = explode('/', $block_name, 2);
    $this->provider = $provider;
    $this->block_name = $block_name;
    if (isset($_SESSION['forena_query_editor'][$block_name]) && $edit) {
      $block = $_SESSION['forena_query_editor'][$block_name];
      drupal_set_message(t('All changes are stored temporarily.  Click Save to make your changes permanent.  Click Cancel to discard your changes.'), 'warning', FALSE);
      $this->modified = TRUE;
    }
    else {
      $block = Frx::RepoMan()->loadBlock($block_name);
      $this->modified = FALSE;
      if (!$block) {
        $block = $this->block = $this->new_block;
        $this->update($this->block);
      }
    }
    $this->block = $block;
    return $block;
  }


  /**
   * Update the block data.
   * @param unknown $block
   * @return FrxBlockEditor
   */
  public function update($block = array()) {
    if ($block) {
      // Make sure we don't put any invalid values
      $block = array_intersect_key($block, $this->block);
      $block = array_merge($this->block, $block);
      $same = $block['file']== $this->block['file'] && @$block['access'] == @$this->block['access'];
      // UPdate the block;
      if (!$same) {
        $this->block = $block;
        $_SESSION['forena_query_editor'][$this->block_name] = $this->block;
        $this->modified = TRUE;
      }
    }
    return $this;
  }

  /**
   * Saves the data block
   */
  public function save() {
    Frx::RepoMan()->saveBlock($this->block_name, $this->block);
    unset($_SESSION['forena_query_editor'][$this->block_name]);
    drupal_get_messages('warning');
    drupal_set_message(t('Saved Changes'));
    return $this;
  }


  /**
   * Cancel the editing event.
   */
  public function cancel() {
    unset($_SESSION['forena_query_editor'][$this->block_name]);
    drupal_get_messages('warning');
    $this->modified = FALSE;
  }

  /**
   * Return repository.
   * @return FrxDataSource
   */
  public function repos() {
    return Frx::RepoMan()->repository($this->provider);
  }

  /**
   * Rename the exisiting block.
   * @param unknown $name
   */
  public function rename($name) {
    $old_name = $this->$block_name;
    unset($_SESSION['forea_query_editor'][$old_name]);
    $this->$block_name = $this->provider . '/' . $name;
    $this->update($this->block);
  }

  /**
   * Get data and working cache.
   * @param unknown $parms
   * @return Ambigous <unknown, string, NULL>
   */
  public function data($parms = array(), $raw_mode = FALSE) {
    // Merge in current_context
    $parms = array_merge(Frx::Data()->currentContextArray(), $parms);
    $report_parms = Frx::Data()->getContext('parm');
    if ($report_parms) $parms = array_merge($report_parms, $parms);
    $id = str_replace('/', '-', $this->block_name) . '-parm';
    Frx::Data()->push($parms, $id);
    if ($this->edit && $this->provider) {
      $xml = Frx::RepoMan()->sqlData($this->provider,  $this->block['file'], $parms);
    }
    else {
      $xml = Frx::RepoMan()->data($this->block_name, $raw_mode);
    }

    $driver = get_class(Frx::RepoMan()->repository($this->provider));

    // Allow modules to alter data returned.
    $context = array(
      'block' => $this->block_name,
      'definition' => $this->block,
      'provider' => $this->provider,
      'parameters' => $parms,
      'raw_mode' => $raw_mode,
      'driver' => $driver,
    );
    drupal_alter('forena_data', $xml, $context);

    if ($parms) Frx::Data()->pop();


    return $xml;
  }

  /**
   * Writes blocks from the old db structure to the new one.
   */
  public function revertDBBLocks() {
    $block = array();
    $fo = Frx::DataFile();

    if (db_table_exists('forena_data_blocks') && is_writable($fo->dir)) {
      // Get all blocks from the db.
      $sql = 'SELECT * FROM {forena_data_blocks} order by repository, block_name';
      $rs = db_query(
        $sql
      );
      $empty_table = TRUE;
      foreach ($rs as $b) {
        $block = array(
          'repository' => $b->repository,
          'block_name' => $b->block_name,
          'type' => $b->block_type,
          'file' => $b->src,
          'builder' => $b->builder,
          'access' => $b->access,
          'title' => $b->title,
          'locked' => $b->locked,
          'modified' => $b->modified,
        );
        $d = Frx::RepoMan()->repository($b->repository);
        $block = array_merge($block, $d->parseSQLFile($block['file']));
        if ($block['builder']) {
          $block['builder'] = unserialize($block['builder']);
        }
        $path = $b->repository . '/' . $b->block_name;
        Frx::RepoMan()->saveBlock($path, $block);
        if (Frx::DataFile()->isCustom($path . '.sql')) {
          db_delete('forena_data_blocks')
            ->condition('repository', $b->repository)
            ->condition('block_name' , $b->block_name)
            ->execute();
        }
        $empty_table = FALSE;

      }
      if ($empty_table) {
        db_drop_table('forena_data_blocks');
      }
      $fo->validateAllCache();
    }

  }

  /**
   * Return the tokens for the block.
   */
  public function tokens()  {
    // skip non-sql blocks
    if (!isset($this->block['file'])) return array();
    $block = Frx::RepoMan()->sqlBlock($this->provider, $this->block['file']);
    $tokens = @$block['tokens'];
    $c_idx = array_search('current_user', $tokens);
    if ($c_idx !==FALSE) unset($tokens[$c_idx]);
    return $tokens;
  }

  /**
   * Get persisted parateters from a block from a session.
   */
  public function getParms() {
    return $_SESSION['forena_data_block_parms'][$this->block_name];
  }

  /**
   * Persist paramaters for a block into a session
   */
  public function saveParms() {
    $parms = Frx::Data()->getContext('parm');
    $_SESSION['forena_data_block_parms'][$this->block_name] = $parms;
  }

  /**
   * Instantiate the builders if necessary
   */
  public function getBuilders() {
    if (!$this->builders) {
      $this->builders = array();
      $builders = module_invoke_all('forena_query_builders');
      foreach ($builders as $builder) {
        if (isset($builder['file'])) @include_once $builder['file'];
        if (class_exists($builder['class'])) {
          $b = new $builder['class'];
          $this->builders[$builder['class']] = $b;
        }
      }
    }
  }

  public function builderList() {
    $this->getBuilders();
    $r = Frx::RepoMan()->repository($this->provider);
    $plugin = get_class($r);
    $list = array();
    foreach ($this->builders as $class => $b) {
      if (array_search($plugin, $b->supportedPlugins)!==FALSE && $b->name && $b->type == $this->block['type']) {
        $list[$class] = $b->name;
      }
    }
    asort($list);
    return $list;
  }

  /**
   * Use the classes configForm method to build the form.
   * @param $builder
   * @param $config
   * @return multitype:
   */
  public function configForm($builder, &$config) {
    $this->getBuilders();
    $form = array();
    if (isset($this->builders[$builder])) {
      $b = $this->builders[$builder];
      if (method_exists($b, 'configForm')) {
        $form = $b->configForm($config);
      }
    }
    return $form;
  }

  /**
   * Use the classes generate method to generate the block.
   * @param $builder
   * @param $config
   * @return multitype:
   */
  public function generate($builder, &$config) {
    $this->getBuilders();
    $form = array();
    if (isset($this->builders[$builder])) {
      $b = $this->builders[$builder];
      if (method_exists($b, 'generate')) {
        $form = $b->generate($config);
      }
    }
    return $form;
  }

  /**
   * Use the classes validate method to validate the block.
   * @param $builder
   * @param $config
   * @return multitype:
   */
  public function configValidate($builder, &$config) {
    $this->getBuilders();
    $errors = array();
    if (isset($this->builders[$builder])) {
      $b = $this->builders[$builder];
      if (method_exists($b, 'configValidate')) {
        $errors = $b->configValidate($config);
      }
      if ($errors) foreach ($errors as $name => $error) {
        form_set_error($name, $error);
      }
    }
    return $errors;
  }

}
