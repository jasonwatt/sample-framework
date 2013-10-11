<?php
/**
 * Class MySQL
 * This is a Wrapper for PDO to easily process MYSQL queries
 */
class MySQL
{
  public $sqlList;
  public $selected;
  private $dbConfig;
  private $db;

  /**
   * @param string $dbName
   *   The default connection to use in the config array.
   */
  public function __construct($dbName = 'default') {
    include('dbconfig.php');
    if (isset($this->dbConfig[$dbName]) && is_array($this->dbConfig[$dbName])) {
      $this->selected = $dbName;
      $this->connect();
    } else {
      debug('error initilizing SQL');
    }
  }

  /**
   * @return Array - The config array
   */
  public function getDatabase() {
    return $this->dbConfig[$this->selected]['database'];
  }

  /**
   * Setup and run the connection to MySQL
   * if connection fails, the program will die
   */
  private function connect() {
    $host     = $this->dbConfig[$this->selected]['host'];
    $login    = $this->dbConfig[$this->selected]['login'];
    $password = $this->dbConfig[$this->selected]['password'];
    $database = $this->dbConfig[$this->selected]['database'];
    try {
      $this->db = new PDO('mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8', $login, $password);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//			$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
      die('Could not connect to DB:' . $e);
    }
  }

  /**
   * Allows to run a straight MYSQL query, not recommended but allowed fir advanced queries.
   * @param       $sql - the MySQL Statement
   * @param array $params - pass through params for PDO
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  public function query($sql, $params = []) {
    $r = $this->run_query($sql, $params);

    return $r->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * @param $array
   * $array['table'] = table name
   * $array['fields'] = array('value')
   * $array['where'] = array('key'=>'value')
   * $array['limit'] = number
   * $array['order'] = array('key'=>'value')
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  public function select($array) {
    $fields = $limit = $where = $order = '';
    $params = [];
    if (empty($array['fields'])) {
      $fields = '*';
    } else {
      foreach ($array['fields'] as $k => $v) {
        $fields .= '`' . $v . '`, ';
      }
      $fields = substr($fields, 0, -2);
    }
    if (!empty($array['order'])) {
      $order = 'ORDER BY ';
      foreach ($array['order'] as $k => $v) {
        $order .= $k . ' ' . v . ', ';
      }
      $order = substr($order, 0, -2);
    }
    if (!empty($array['limit'])) {
      $limit = ' LIMIT ' . $array['limit'];
    }
    if (!empty($array['where'])) {
      $where = $this->where($array['where'], $params);
    }

    return $this->run_query('SELECT ' . $fields . ' FROM ' . mysql_real_escape_string($array['table']) . ' ' . $where . ' ' . $order . $limit, $params);
  }

  /**
   * @param $array
   * $array['table'] = table name
   * $array['values'] = array('key'=>'value')
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  public function insert($array) {
    $keys   = $vals = '';
    $params = [];
    foreach ($array['values'] as $k => $v) {
      $va = ':' . str_replace(' ', '_', $k);
      $keys .= '`' . $k . '`, ';
      $vals .= $va . ', ';
      $params[$va] = $v;
    }
    $this->run_query('INSERT INTO ' . mysql_real_escape_string($array['table']) . ' (' . substr($keys, 0, -2) . ') VALUES (' . substr($vals, 0, -2) . ')', $params);

    return $this->db->lastInsertId();
  }

  /**
   * @param $array
   * $array['table'] = table name
   * $array['values'] = array('key'=>'value')
   * $array['where'] = array('key'=>'value')
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  public function update($array) {
    $keys   = $vals = $where = '';
    $params = [];
    foreach ($array['values'] as $k => $v) {
      $va          = ':v_' . $k;
      $params[$va] = $v;
      $keys .= "`" . $k . "`=" . $va . ", ";
    }
    $where = $this->where($array['where'], $params);
    if ($where == false) {
      return false;
    }

    return $this->run_query("UPDATE " . mysql_real_escape_string($array['table']) . " SET " . substr($keys, 0, -2) . " WHERE " . $where . "");
  }

  /**
   * @param $array
   *  $array['table'] = table name
   *  $array['where'] = array('key'=>'value')
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  public function delete($array) {
    $params = [];
    $where  = $this->where($array['where'], $params);
    if ($where == false) {
      return false;
    }

    return $this->run_query("DELETE FROM `" . mysql_real_escape_string($array['table']) . "` WHERE " . $where . " LIMIT 1");
  }

  /**
   * This helps construct the where clause
   * @param $where - Where clause as string or array
   * @param $params - Referenced Params to be updated
   *
   * @return bool|string
   */
  private function where($where, &$params) {
    if (!isset($where)) {
      return false;
    }
    $rwhere = '';
    if (is_array($where)) {
      $rwhere = 'WHERE ';
      foreach ($where as $k => $v) {
        $v = trim($v);
        if (substr($v, 0, 1) == '"' || substr($v, 0, 1) == "'" || substr($v, 0, 2) == 'fn') {
          $s = $v;
        } else {
          $va          = ':w_' . $k;
          $params[$va] = empty($v) ? null : $v;
          $s           = $va;
        }
        $rwhere .= " `" . $k . "` = " . $s . " AND";
      }
      $rwhere = substr($rwhere, 0, -4);
    } else {
      $rwhere = $where;
    }

    return $rwhere;
  }

  /**
   * Runs the query using PDO execute and fetch
   * @param $sql - the SQL statement
   * @param $params - Passed in params
   *
   * @return mixed
   *    Bool - false if failed
   *    Array - Query Results
   */
  private function run_query($sql, $params) {
    try {
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);
      $this->sqlList[] = $stmt->queryString;

      return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      dbug($e->errorInfo);
      $this->dbug("MYSQL ERROR - Query: " . $stmt->queryString . ' | SQL:' . $sql . ' | ' . $e->errorInfo, $params);
      $this->sqlList[] = 'ERROR: ' . $stmt->queryString . ' | ' . $e->errorInfo;

      return false;
    }
  }

  /**
   * This is a debug helper function for stack tracing.
   */
  private function dbug() {
    if (!isset($doc_root)) {
      $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    }
    $back = debug_backtrace();
    // you may want not to htmlspecialchars here
    $line = htmlspecialchars($back[0]['line']);
    $file = htmlspecialchars(str_replace(array('\\', $doc_root), array('/', ''), $back[0]['file']));

    $k = ($back[1]['class'] == 'SQL') ? 3 : 1;

    $class    = !empty($back[$k]['class']) ? htmlspecialchars($back[$k]['class']) . '::' : '';
    $function = !empty($back[$k]['function']) ? htmlspecialchars($back[$k]['function']) . '() ' : '';
    $args     = (count($back[0]['args'] > 0)) ? $back[0]['args'] : $back[0]['object'];
    $args     = (count($args) == 1 && $args[0] != '') ? $args[0] : $args;

    print '<div style="background-color:#eee; width:100%; font-size:11px; font-family: Courier, monospace;" class="myDebug"><div style=" font-size:12px; padding:3px; background-color:#ccc">';
    print "<b>$class$function =&gt;$file #$line</b></div>";
    print '<div style="padding:5px;">';
    if (is_array($args) || is_object($args)) {
      print '<pre>';
      print_r($args);
      print '</pre></div>';
    } else {
      print $args . '</div>';
    }
    print '</div>';
  }
}