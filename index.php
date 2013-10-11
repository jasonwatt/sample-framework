<?PHP
define('API', false);

require('config.php');
require(ROOT.DS.'loader.php');

$session = new Session();

// Run the router
$router->execute();