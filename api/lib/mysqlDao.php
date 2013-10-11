<?
/**
 * Class DAO
 * This is used to call all MySQL queries for the application.
 * This will be used by the models to pull data from MySQL
 */
class DAO
{
  private static $db;

  public static function __init__() {
    require(DATABASE . 'classSQL.php');
    self::$db = new MySQL();
  }

  public static function checkLogin($username, $password) {
    $a = self::$db->select(['table' => 'users', 'where' => ['username' => $username, 'password' => $password]]);
    dbug($a);
  }

  public static function checkUsername($username) {
    $a = self::$db->select(['table' => 'users', 'where' => ['username' => $username]]);

    return $a;
  }
}

DAO::__init__();