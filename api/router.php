<?php
/**
 * Requires the Code ignitor router
 * Requires the Controller
 */
require(LIB . 'ig_route.php');
require(LIB . 'controller.php');

/**
// Adding a basic route
$router->route( '/login', 'login_function' );

// Adding a route with a named alphanumeric capture, using the  syntax
$router->route( '/user/view/<:username>', 'view_username' );

// Adding a route with a named numeric capture, using the  syntax
$router->route( '/user/view/<#user_id>', array( 'UserClass', 'view_user' ) );

// Adding a route with a wildcard capture (Including directory separtors), using
// the  syntax
$router->route( '/browse/<*categories>', 'category_browse' );

// Adding a wildcard capture (Excludes directory separators), using the
//  syntax
$router->route( '/browse/<!category>', 'browse_category' );

// Adding a custom regex capture using the  syntax
$router->route( '/lookup/zipcode/<:zipcode|[0-9]{5}>', 'zipcode_func' );

// Specifying priorities
$router->route( '/users/all', 'view_users', 1 ); // Executes first
$router->route( '/users/<:status>', 'view_users_by_status', 100 ); // Executes after
 */

$router = new Router;

$router->route('/', 'index');

$router->route('/login', 'login');

$router->route('/logout', 'logout');

// Specifying a default callback function if no other route is matched
$router->default_route('error404');

//if permisison is denied then show the alternate view
$router->denied_route('permDenied');
$router->denied_redirect('login');

