<?php
/**
 * @file
 * Provides data blocks for native drupal connections using the default
 * drupal connections.
 *
 */
class FrxDrupal extends FrxDataSource {
  /**
   * Implements hooks into the drupal applications
   */

  private $database = 'default';


  /**
   * Object constructor
   *
   * @param unknown_type $uri Database connection string.
   * @param string $repos_path Path to location of data block definitions
   */
  public function __construct($conf, $repos_path, $name) {
    parent::__construct($conf, $repos_path, $name);
    if (@$conf['database'] && @$conf['database'] != 'default')  {
      $this->database = $conf['database'];
    }
    $target = $this->database ? $this->database : 'default';
    $this->db_type = Database::getConnection($target)->databaseType();
    // Set up the stuff required to translate.
    $this->te = new FrxSyntaxEngine(FRX_SQL_TOKEN, ':', $this);
  }

  public function searchTables($str) {
    $str .= '%';
    $rs = db_query($this->searchTablesSQL(), array(':str' => $str));
    if ($rs) return $rs->fetchCol();
  }

  /**
   * Generate a list of columns for a table.
   * @param unknown $table
   * @param unknown $str
   * @return An
   */
  public function searchTableColumns($table, $str) {
    $str .= '%';
    $table = trim($table, '{} ');
    $key = $this->database ? $this->database : 'default';
    $info = Database::getConnectionInfo($key);
    $database = $info[$key]['database'];
    $rs = db_query($this->searchTableColumnsSQL(), array(':str' => $str, ':database' => $database, ':table' => $table));
    if ($rs) return $rs->fetchCol();
  }

  public function query($sql, $options = array()) {
    if ($this->database != 'default') {
      db_set_active($this->database);
    }
    // Load the types array based on data
    $this->types = isset($options['type']) ? $options['type'] : array();

    // Load the block from the file
    $xml ='';
    $sql = $this->te->replace($sql);
    try {
      if (@(int)$options['limit']) {
        $rs = db_query_range($sql, 0, (int)$options['limit']);
      }
      else {
        $rs = db_query($sql);
      }
    }
    catch (PDOException $e) {
      watchdog_exception('error',$e);
      $line = $e->getLine();
      $text = $e->getMessage();
      //if (user_access('build forena sql blocks')) {
      if (!$this->block_name) {
        $short = t('%e', array('%e' => $text, '%l' => $line));
      } else {
        $short = t('SQL Error in %b.sql', array('%b' => $this->block_name));
      }
      drupal_set_message($short, 'error', FALSE);
      if ($this->database != 'default') {
        db_set_active();
      }
      return;

    }


    if ($this->database != 'default') {
      db_set_active();
    }
    return $rs;
  }

  /**
   * Get data based on file data block in the repository.
   *
   * @param String $block_name
   * @param Array $parm_data
   * @param Query $subQuery
   */
  public function sqlData($sql, $options = array()) {
    $rs = $this->query($sql, $options);
    $entity_map = array();
    $select_fields = array();
    $rownum = 0;
    if (!$rs) return;
    if (@$options['return_type'] == 'raw') return $rs;
    $xml = new SimpleXMLElement('<table/>');
    if ($rs && $rs->columnCount()) foreach ($rs as $data) {
      $rownum++;
      $row_node = $xml->addChild('row');
        $row_node['num'] = $rownum;
        foreach ($data as $key => $value) {
          $row_node->addChild($key, htmlspecialchars($value));
        }
      /* If we are querying entities, we will store away IDs
       * for later querying and XML processing in the
       * loadEntitys method
       */
      if (@$options['entity_type']  && @$options['entity_id'] ) {
        $id_key = $options['entity_id'];
        $type = $options['entity_type'];
        $id = $data->$id_key;
        if ($id) {
          $entity_map[$id][] = $row_node;
          $select_fields[$id] = $data;
        }
      }
    }

    if ($entity_map) {
      $this->loadEntities($type, $entity_map, $select_fields);
    }
    if ($this->debug) {
      $d = ($xml) ? htmlspecialchars($xml->asXML()) : '';
      $this->debug('SQL: ' . $sql, '<pre> SQL:' . $sql . "\n XML: " . $d . "\n</pre>");
    }
    return $xml;
  }

  /**
   * Perform a bulk load of entities 100 at a time and then join data back onto the simplexml already created.
   * @param $type Type of entity being loaded.
   * @param $entity_map Map of entity ids to nodes.
   */
  private function loadEntities($type, $entity_map, $select_fields) {
    // Do these 100 at a time for performance reasons
    $chunks = array_chunk($entity_map, 100, TRUE);
    foreach ($chunks as $chunk) {
      $ids = array_keys($chunk);

      $data = entity_load($type, $ids);

      if ($data) foreach ($data as $id => $e) {
        // Get node permissions
        $access = ($type=='node') ? node_access('view', $e) : TRUE;
        if (isset($entity_map[$id])) {
          foreach($entity_map[$id] as $entry) {
            $row_node = $entry;
            $lang = isset($e->language) ? $e->language : 'und';

            // remove elements we don't have access to
            if (!$access) {
              unset($row_node[0]);
            }
            else {

              foreach ($e as $key => $val) {
                if ($val && !isset($row_node->$key)) {
                  if (strpos($key, 'field_') === 0) {
                    //$fields = field_get_items('node', $node, $key);
                    $field = field_view_field($type, $e, $key);
                    $field['#theme'] = array('forena_inline_field');
                    $value = drupal_render($field);
                    $f = $row_node->addChild($key, $value);
                    if (isset($field['#field_type'])) {
                      $f['type'] = $field['#field_type'];
                    }
                    if (isset($field['#field_name'])) {
                      $f['name'] = $field['#field_name'];
                    }
                  }
                  else {
                    if (is_array($val) && isset($val[$lang])) {
                      $tmp = $val[$lang][0];
                      if (isset($tmp['safe_value'])) {
                        $row_node->addChild($key, $tmp['safe_value']);
                      }
                      else {
                        if (isset($tmp['value'])) {
                          $row_node->addChild($key, $tmp['value']);
                        }
                      }
                    }
                    else {
                      if (is_array($val) && isset($val['und'])) {
                        $tmp = $val['und'][0];
                        if (isset($tmp['safe_value'])) {
                          $row_node->addChild($key, $tmp['safe_value']);
                        }
                        else {
                          if (isset($tmp['value'])) {
                            $row_node->addChild($key, $tmp['value']);
                          }
                        }
                      }
                      else {
                        if (is_scalar($val)) {
                          $row_node->addChild($key, $val);
                        }
                      }
                    }
                  }
                }
              }
              // Now add on select fields
              if ($select_fields[$id]) {
                foreach ($select_fields[$id] as $key => $val) {
                  if (!isset($row_node->$key)) {
                    $row_node->addChild($key, $val);
                  }
                }
              }

            }
          }
        }
      }
    }

  }

  /**
   * Implement custom SQL formatter to make sure that strings are properly escaped.
   * Ideally we'd replace this with something that handles prepared statements, but it
   * wouldn't work for
   *
   * @param unknown_type $value
   * @param unknown_type $key
   * @param unknown_type $data
   */
  public function format($value, $key, $raw = FALSE) {
    if ($raw) return $value;
    $db = Database::getConnection('default');
    $value = $this->parmConvert($key, $value);
    if ($db) {
      if ($value==='' || $value===NULL)
      $value = 'NULL';
      else {
        if (is_array($value)) {
          if ($value == array()) {
            $value = 'NULL';
          }
          else {
            // Build a array of values string
            $i=0;
            $val = '';
            foreach ($value as $v) {
              $i++;
              if ($i>1) {
                $val .= ',';
              }
              $val .=   $db->quote($v);
            }
            $value = $val;
          }
        }
        elseif (is_int($value)) {
          $value = (int)$value;
          $value = (string)$value;
        }
        elseif (is_float($value)) {
          $value = (float)$value;
          $value = (string)$value;
        }
        else {
          $value = trim($value);
          $value =    $db->quote($value) ;
        }
      }
    }
    return $value;
  }

}













