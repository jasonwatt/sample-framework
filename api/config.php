<?
//Config.php
if(!defined('ROOT')){
	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', dirname(__FILE__));
}
define('BASE_URL_PATH', 'api');
define('BASE_URL', $_SERVER['SERVER_NAME']);

//Lib and file locations
define('LIB', ROOT.DS.'lib'.DS);
define('CONTROLLER', ROOT.DS.'controllers'.DS);
define('MODEL', ROOT.DS.'models'.DS);
define('VIEW', ROOT.DS.'views'.DS);
define('VENDOR', ROOT.DS.'vendor'.DS);
define('TEMPLATE', VIEW.DS.'templates'.DS);
define('ELEMENT', VIEW.DS.'elements'.DS);

define('DATABASE', LIB.'database'.DS);

define('HTTP_HEADER_SIGNATURE', 'HTTP-SIG');

//Debug level
define('DEBUG', 3); //3: debug 2:info 1: errors 0: production

//Set log locations
define('LOGS', ROOT.DS.'logs'.DS);
define('ERROR_LOG', LOGS.'error.log');
define('DEBUG_LOG', LOGS.'debug.log');
define('DB_LOG', LOGS.'database.log');

//appended to the end of the views title
define('TITLEEND','Coaster Junction');
//the default view title
define('TITLEDEFAULT','Coaster Junction | Your theme park, for the world to discover');