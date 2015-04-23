<?php
// $Id$
/**
 * @file
 * Oracle specific driver that takes advantage of oracles native XML support
 *
 * In order to take advantage of XML support the following XML
 *
 */
class FrxMSSQL extends FrxDataSource {


  private $db;
  private $use_mssql_xml;

  /**
   * Object constructor
   *
   * @param unknown_type $uri Database connection string.
   * @param string $repos_path Path to location of data block definitions
   */
  public function __construct($conf, $repos_path, $name) {

    parent::__construct($conf, $repos_path, $name);
    $this->db_type = 'mssql';
    $this->use_mssql_xml = FALSE;
    $uri = $conf['uri'];
    $this->debug = $conf['debug'];
    if ($conf['mssql_xml']) $this->use_mssql_xml = TRUE;
    if ($uri) {
      // Test for mssql suport
      if (!is_callable('mssql_connect')) {
        Frx::error('MSSQL support not installed.', 'MSSQL mssql support not installed.');
        return;
      }
      try {
        ini_set('mssql.textlimit', 2147483647);
        ini_set('mssql.textsize', 2147483647);
        $db = mssql_connect($uri, $conf['user'], $conf['password']);
        $this->db = $db;

        if ($db) {
          mssql_select_db($conf['database'], $db);
          mssql_query("SET QUOTED_IDENTIFIER ON");
        }
      } catch (Exception $e) {
        Frx::error('Unable to connect to database ' . $conf['title'], $e->getMessage());
      }

    }
    else {
      Frx::error('No database connection string specified', 'No database connection: ' . print_r($conf, 1));
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

    // Load the types array based on data
    $this->types = isset($options['type']) ? $options['type'] : array();

    $xml ='';
    // Load the types array based on data
    $this->types = isset($options['type']) ? $options['type'] : array();
    if ($sql && $db) {
      $sql = $this->te->replace($sql);

      if ($this->use_mssql_xml) {
        $xml = $this->mssql_xml($sql, 'table');
      }
      else {
        $xml = $this->php_xml($sql);
      }
      if ($this->debug) {
        if ($xml) $d = htmlspecialchars($xml->asXML());
        FRx::debug('SQL: ' . $sql, '<pre> SQL:' . $sql . "\n XML: " . $d . "\n</pre>");
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
  private function mssql_xml($sql, $block) {
    $db = $this->db;

    //$rs->debugDumpParams();
    $fsql = $sql . ' FOR XML AUTO';
    $rs = mssql_query($db, $fsql, array($sql, ''));
    if ($rs) {
      $row = mssql_fetch_row($rs);
      $xml_text = $row[0];
    }
    if ($xml_text) {
      $xml = new SimpleXMLElement($xml_text);
      if ($xml->getName() == 'error') {
        $msg = (string)$xml . ' in ' . $block . '.sql. ';
        Frx::error($msg . 'See logs for more info', $msg . ' in <pre> ' . $sql . '</pre>');
      }
    }
    if ($rs) mssql_free_result($rs);
    return $xml;
  }

  private function php_xml($sql) {
    $db = $this->db;
    $xml = new SimpleXMLElement('<table/>');

    $rs = mssql_query($sql, $db);
    $rownum = 0;
    while ($row = mssql_fetch_assoc($rs)) {
      $rownum++;
      $row_node = $xml->addChild('row');
      $row_node['num'] = $rownum;
      foreach ($row as $key => $value) {
        $row_node->addChild(strtolower($key), htmlspecialchars($value));
      }
    }
    if ($rs) mssql_free_result($rs);
    return $xml;

  }

  /**
   * Perform search of tables.
   * @see FrxDataSource::searchTables()
   */
  public function searchTables($str) {
    $str .= '%';
    $db = $this->db;
    $sql = $this->searchTablesSQL();
    $str = "'" . str_replace("'", "''", $str) . "'";
    $sql = str_replace(':str', $str, $sql);
    $rownum = 0;
    $rs = mssql_query($sql, $db);
    $tables = array();
    while ($row = mssql_fetch_assoc($rs)) {
      $tables[] = $row['table_name'];
    }
    if ($rs) mssql_free_result($rs);
    return $tables;

  }

  /**
   * Perform search of tables.
   * @see FrxDataSource::searchTables()
   */
  public function searchTableColumns($table, $str) {
    $str .= '%';
    $db = $this->db;
    $sql = $this->searchTableColumnsSQL();
    $str = "'" . str_replace("'", "''", $str) . "'";
    $sql = str_replace(':str', $str, $sql);

    $table = "'" . str_replace("'", "''", $table) . "'";
    $sql = str_replace(':table', $table, $sql);
    $rownum = 0;

    $rs = mssql_query($sql, $db);
    $columns = array();
    if ($rs) while ($row = mssql_fetch_assoc($rs)) {
      $columns[] = $row['COLUMN_NAME'];
    }
    if ($rs) mssql_free_result($rs);
    return $columns;

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
    if ($value===''||$value===NULL) {
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
    else $value = "'" . str_replace("'", "''", $value) . "'";
    return $value;
  }

  /**
   * Destructor - Closes database connections.
   *
   */
  public function __destruct() {
    $db = $this->db;
    if ($db) {
      mssql_close($db);
    }
  }
}