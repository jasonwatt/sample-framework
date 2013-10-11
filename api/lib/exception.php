<?PHP
class ApiException extends Exception
{
}

class MysqlException extends Exception
{
}

class ExceptionHandler
{
  private $rethrow;

  public function __construct() {
    set_exception_handler(array($this, 'handler'));
  }

  public function __destruct() {
    if ($this->rethrow) {
      throw $this->rethrow;
    }
  }

  public function handler($e) {
    if ($e instanceof ApiException) {
      $type    = 'APIException';
      $message = sprintf('"%s" thrown at %s(%d)', $e->getMessage(), $e->getFile(), $e->getLine());
      logError($message, ERROR_LOG);
    } else if ($e instanceof MysqlException) {
      $type    = 'MysqlException';
      $message = sprintf('"%s" thrown at %s(%d)', $e->getMessage(), $e->getFile(), $e->getLine());
      logError($message, DB_LOG);
    } else {
      $type    = 'Exception';
      $message = sprintf('"%s" thrown at %s(%d)', $e->getMessage(), $e->getFile(), $e->getLine());
    }
    echo '<b>' . $type . '</b> ' . $message;
  }
}

new ExceptionHandler;


function logError($error, $file = ERROR_LOG) {
  error_log(date(DATE_RFC822) . " - " . $error . "\r\n", 3, $file);
}