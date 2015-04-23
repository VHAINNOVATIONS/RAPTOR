<?php
/**
 * @file
 * General database engine used to do sql queries.
 *
 */

class FrxPDO extends FrxDataSource {


  private $db;
  public $debug;


  /**
   * Object constructor
   *
   * @param unknown_type $uri Database connection string.
   * @param string $repos_path Path to location of data block definitions
   */
  public function __construct($conf, $repos_path, $name) {
    parent::__construct($conf, $repos_path, $name);
    $uri = $conf['uri'];
    $this->debug = @$conf['debug'];
    if ($uri) {
      // Test for PDO suport
      if (!class_exists('PDO')) {
        $this->error('PDO support not installed.', 'PDO support not installed.');
        return;
      }

      $options = array();
      if (@$conf['mysql_charset']) {
        $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $conf['mysql_charset'],
        );
      }

      // Test for driver support
      @list($prot, $c) = explode(':', $uri, 2);
      $drivers = PDO::getAvailableDrivers();
      $this->db_type = $prot;

      if ($drivers && (array_search($prot, $drivers)===FALSE)) {
        $msg = 'PDO driver support for ' . $prot . ' not installed';
        $this->error($msg, $msg);
        return;
      }
      try {
        if (isset($conf['user'])) {
          $db = new PDO($uri, $conf['user'], @$conf['password'], $options);
        }
        else {
          $db = new PDO($uri, NULL, NULL, $options);
        }
        $this->db = $db;
        if (!is_object($db)) {
          $this->error('Unknown error connecting to database ' . $uri);
        }
      } catch (PDOException $e) {
        $this->error('Unable to connect to database', $e->getMessage());
      }
      if($this->db) $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    }
    else {
      $this->error('No database connection string specified');
    }

    // Set up the stuff required to translate.
    $this->te = new FrxSyntaxEngine(FRX_SQL_TOKEN, ':', $this);
  }

  public function parseConnectionStr() {
    $uri = @$this->conf['uri'];
    @list($prot, $conn) = explode(':', $uri, 2);
    $conn = str_replace(';', ' ', $conn);
    $info = array();
    foreach(explode(' ', $conn) as $pairs) {
      if (strpos($pairs, '=')!==FALSE) {
        list($key, $value) = @explode('=', $pairs, 2);
        $info[trim($key)] = trim($value);
      }
    }
    return $info;
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
      try {
        $rs = $db->query($sql);

      }
      catch (PDOException $e) {
        watchdog_exception('error',$e);
        $line = $e->getLine();
        $text = $e->getMessage();
       drupal_set_message($short, 'error', FALSE);
       return;

      }
      if (@$options['return_type'] == 'raw') {
        return $rs;
      }
      $xml = new SimpleXMLElement('<table/>');
      $e = $db->errorCode();

      if ($e != '00000') {
        $i = $db->errorInfo();
        $text =  $i[0] . ':' . $i[2];
        //if (user_access('build forena sql blocks')) {
        if (!$this->block_name) {
          $short = t('%e', array('%e' => $text));
        } else {
          $short = t('SQL Error in %b.sql', array('%b' => $this->block_name));
        }
        $this->error($short, $text);

      }
      else if ($rs && $rs->columnCount())  {
        if (@$options['return_type'] == 'raw') return $rs;
        $rownum = 0;
        foreach ($rs as $data) {
          $rownum++;
          $row_node = $xml->addChild('row');
          $row_node['num'] = $rownum;
          foreach ($data as $key => $value) {
            $row_node->addChild($key, htmlspecialchars($value));
          }
        }
      }

      if ($this->debug) {
        $d = '';
        if ($xml)  {
          $d = htmlspecialchars($xml->asXML());
        }
        $this->debug('SQL: ' . $sql, '<pre> SQL:' . $sql . "\n XML: " . $d . "/n</pre>");
      }
      return $xml;
    }

  }

  /**
   * Wrapper method cause some ODBC providers do not support
   * quoting.   We're going to assume the MSSQL method of quoting.
   * @param $value
   */
  public function quote($value) {
    $new_value =  $this->db->quote($value);
    if (($value!=='' || $value!==NULL) && !$new_value) {
      $value = "'" . str_replace("'", "''", $value) . "'";
    }
    else {
      $value = $new_value;
    }
    return $value;
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
    $db = $this->db;
    $value = $this->parmConvert($key, $value);
    if ($db) {
      if ($value==='' || $value ===NULL || $value === array()) {
        $value = 'NULL';
      }
      elseif (is_int($value)) {
        $value = (int)$value;
        $value = (string)$value;
      }
      elseif (is_float($value)) {
        $value = (float)$value;
        $value = (string)$value;
      }
      elseif (is_array($value)) {
        if ($value == array()) {
          $value = 'NULL';
        }
        else {
          // Build a array of values string
          $i=0;
          $val ='';
          foreach ($value as $v) {
            $i++;
            if ($i!=1) {
              $val .= ',';
            }
            $val .= $this->quote($v);
          }
          $value = $val;
        }
      }
      else  $value =  $this->quote($value);
    }
    return (string)$value;
  }

  public function searchTables($str) {
    $str .= '%';
    $sql = $this->searchTablesSQL();
    if ($sql) {
      $st = $this->db->prepare($sql);
      if ($st) $st->execute(array(':str' => $str));
      if ($st) return $st->fetchAll(PDO::FETCH_COLUMN, 0);
    }
  }

  public function searchTableColumns($table, $str) {
    $str .= '%';
    $sql = $this->searchTableColumnsSQL();
    $info = $this->parseConnectionStr();
    $database = isset($info['dbname']) ? $info['dbname'] : @$info['database'];
    if ($sql) {
      $st = $this->db->prepare($sql);
      if ($st) $st->execute(array(':table' => $table, ':database' => $database, ':str' => $str));
      if ($st) return $st->fetchAll(PDO::FETCH_COLUMN, 0);
    }
  }
}