<?php
/**
 * @file
 * Postgres specific driver that takes advantage of native XML support
 *
 * In order to take advantage of XML support the following XML
 *
 */
class FrxPostgres extends FrxDataSource {


  private $db;
  private $use_postgres_xml=FALSE;

  /**
   * Object constructor
   *
   * @param unknown_type $uri Database connection string.
   * @param string $repos_path Path to location of data block definitions
   */
  public function __construct($conf, $repos_path, $name) {
    parent::__construct($conf, $repos_path, $name);
    $this->use_postgres_xml = FALSE;
    $uri = $conf['uri'];
    $this->db_type = 'postgres';
    if (!empty($conf['password'])) $uri = trim($uri) . ' password=' . $conf['password'];
    $this->debug = @$conf['debug'];
    if (isset($conf['postgres_xml'])) $this->use_postgres_xml = $conf['postgres_xml'];
    if ($uri) {
      // Test for postgres suport
      if (!is_callable('pg_connect')) {
        $this->error('PHP Postgres support not installed.', 'PHP Postgres support not installed.');
        return;
      }
      try {
        $db = pg_connect($uri);
        if (isset($conf['search path'])) @pg_query($db, "SET search_path=" . $conf['search path']);
        $this->db = $db;
      } catch (Exception $e) {
        $this->error('Unable to connect to database ' . $conf['title'], $e->getMessage());
      }

    }
    else {
      $this->error('No database connection string specified', 'No database connection: ' . print_r($conf, 1));
    }

    // Set up the stuff required to translate.
    $this->te = new FrxSyntaxEngine(FRX_SQL_TOKEN, ':', $this);
  }
  /**
   * Get data based on file data block in the repository.
   *
   * @param String $block_name
   * @param Array $parm_data
   * @param Query $subQuery
   */
  public function sqlData($sql, $options = array()) {
    // Load the block from the file
    $db = $this->db;
    $xml ='';
    // Load the types array based on data
    $this->types = isset($options['type']) ? $options['type'] : array();

    if ($sql && $db) {
      $sql = $this->te->replace($sql);

      if ($this->use_postgres_xml) {
        $xml = $this->postgres_xml($sql, 'table');
      }
      else {
        $xml = $this->php_xml($sql);
      }
      if ($this->debug) {
        $d = '';
        if ($xml) $d = htmlspecialchars($xml->asXML());
        $this->debug('SQL: ' . $sql, '<pre> SQL:' . $sql . "\n XML: " . $d . "\n</pre>");
      }
      return $xml;
    }

  }

  /**
   * Generate xml from sql using the provided f_forena
   *
   * @param unknown_type $sql
   * @return unknown
   */
  private function postgres_xml($sql, $block) {
    $db = $this->db;
    //$rs->debugDumpParams();
    $fsql = 'select query_to_xml($1,true,false,$2);';
    $rs = @pg_query_params($db, $fsql, array($sql, ''));
    $e = pg_last_error();
    if ($e) {
      $text = $e;
      if (!$this->block_name) {
        $short = t('%e', array('%e' => $text));
      } else {
        $short = t('SQL Error in %b.sql', array('%b' => $this->block_name));
      }
      $this->error($short, $text);
      return;
    }
    $xml_text='';
    if ($rs) {
      $row = pg_fetch_row($rs);
      $xml_text = $row[0];
    }
    $xml = NULL;
    if ($xml_text) {
      $xml = new SimpleXMLElement($xml_text);
      if ($xml->getName() == 'error') {
        $msg = (string)$xml . ' in ' . $block . '.sql. ';
        $this->error($msg . 'See logs for more info', $msg . ' in <pre> ' . $sql . '</pre>');
      }
      if (!$xml->children()) $xml ='';
    }
    if ($rs) pg_free_result($rs);
    return $xml;
  }

  private function php_xml($sql) {
    $db = $this->db;
    $xml = new SimpleXMLElement('<table/>');
    $rs = @pg_query($sql);
    $e = pg_last_error();
    if ($e) {
      $text = $e;
      if (!$this->block_name) {
        $short = t('%e', array('%e' => $text));
      } else {
        $short = t('SQL Error in %b.sql', array('%b' => $this.block_name));
      }
      $this->error($short, $text);
    }
    $rownum = 0;
    if ($rs) while ($row = pg_fetch_assoc($rs)) {
      $rownum++;
      $row_node = $xml->addChild('row');
      $row_node['num'] = $rownum;
      foreach ($row as $key => $value) {
        $row_node->addChild(strtolower($key), htmlspecialchars($value));
      }
    }
    if ($rs) pg_free_result($rs);
    return $xml;
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
    $value = $this->parmConvert($key, $value);
    if ($value===''||$value===NULL)
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
            $val .=  "'" . pg_escape_string($v) . "'";
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
        $value =  "'" . pg_escape_string($value) . "'";
      }
    }
    return $value;

  }

  public function searchTables($str) {
    $str .= '%';
    $db = $this->db;
    $sql = $this->searchTablesSQL();
    $str = pg_escape_string($str);
    $str = "'$str'";
    $sql = str_replace(':str', $str, $sql);

    $rs = @pg_query($sql);
    $rownum = 0;
    $tables = array();
    if ($rs) {
      $tables = pg_fetch_all_columns($rs, 0);
    }
    if ($rs) pg_free_result($rs);
    return $tables;
  }


  public function parseConnectionStr() {
    $uri = @$this->conf['uri'];
    $uri = str_replace(';', ' ', $uri);
    $info = array();
    foreach(explode(' ', $uri) as $pairs) {
      if (strpos($pairs, '=')!==FALSE) {
        list($key, $value) = @explode('=', $pairs, 2);
        $info[trim($key)] = trim($value);
      }
    }
    return $info;
  }


  public function searchTableColumns($table, $str) {
    $str .= '%';
    $db = $this->db;
    $sql = $this->searchTableColumnsSQL();
    $str = pg_escape_string($str);
    $str = "'$str'";
    $sql = str_replace(':str', $str, $sql);
    $table = pg_escape_string($table);
    $table = "'$table'";
    $sql = str_replace(':table', $table, $sql);

    $info = $this->parseConnectionStr();
    $database = isset($info['dbname']) ? $info['dbname'] : @$info['database'];
    $database = pg_escape_string($database);
    $database = "'$database'";
    $sql = str_replace(':database', $database, $sql);

    $rs = @pg_query($sql);
    $rownum = 0;
    $tables = array();
    if ($rs) {
      $tables = pg_fetch_all_columns($rs, 0);
    }
    if ($rs) pg_free_result($rs);
    return $tables;
  }


  /**
   * Destructor - Closes database connections.
   *
   */
  public function __destruct() {
    $db = $this->db;
    if ($db) {
      pg_close($db);
    }
  }


}