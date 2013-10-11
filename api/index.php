<?PHP
//Always show API responses regardless of headers sent.
define('API', true);
/**
 * Required config.php - configuration with constants
 * Required loader.php - loads all required files
 */
require('config.php');
require('loader.php');

$checkSigned = new SignedAPI();
if ($checkSigned->isAPI == true) {
  $session = $checkSigned->checkData();
} else {
  $session = new Session();
}

// Run the router
$router->execute();