<?php
require(LIB . 'auth.php');
/**
 * Igniter Router
 *
 * This it the Igniter URL Router, the layer of a web application between the
 * URL and the function executed to perform a request. The router determines
 * which function to execute for a given URL.
 *
 * @package    Igniter
 * @subpackage Router
 * @version    2.0.1
 * @author     Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */


/**
 * Igniter Router Class
 *
 * This it the Igniter URL Router, the layer of a web application between the
 * URL and the function executed to perform a request. The router determines
 * which function to execute for a given URL.
 *
 * <code>
 * $router = new \Igniter\Router;
 *
 * // Adding a basic route
 * $router->route( '/login', 'login_function' );
 *
 * // Adding a route with a named alphanumeric capture, using the <:var_name> syntax
 * $router->route( '/user/view/<:username>', 'view_username' );
 *
 * // Adding a route with a named numeric capture, using the <#var_name> syntax
 * $router->route( '/user/view/<#user_id>', array( 'UserClass', 'view_user' ) );
 *
 * // Adding a route with a wildcard capture (Including directory separtors), using
 * // the <*var_name> syntax
 * $router->route( '/browse/<*categories>', 'category_browse' );
 *
 * // Adding a wildcard capture (Excludes directory separators), using the
 * // <!var_name> syntax
 * $router->route( '/browse/<!category>', 'browse_category' );
 *
 * // Adding a custom regex capture using the <:var_name|regex> syntax
 * $router->route( '/lookup/zipcode/<:zipcode|[0-9]{5}>', 'zipcode_func' );
 *
 * // Specifying priorities
 * $router->route( '/users/all', 'view_users', 1 ); // Executes first
 * $router->route( '/users/<:status>', 'view_users_by_status', 100 ); // Executes after
 *
 * // Specifying a default callback function if no other route is matched
 * $router->default_route( 'page_404' );
 *
 * // Run the router
 * $router->execute();
 * </code>
 *
 * @since 2.0.0
 */
class Router
{
  /**
   * Contains the callback function to execute, retrieved during run()
   *
   * @var String|Array The callback function to execute during dispatch()
   * @since  2.0.1
   * @access protected
   */
  protected $callback = null;

  /**
   * Contains the callback function to execute if none of the given routes can
   * be matched to the current URL.
   *
   * @var String|Array The callback function to execute as a fallback option
   * @since  2.0.0
   * @access protected
   */
  protected $default_route = null;


  protected $denied_route = null;
  protected $denied_redirect = null;

  /**
   * Contains the last route executed, used when chaining methods calls in
   * the route() function (Such as for put(), post(), and delete()).
   *
   * @var Pointer A pointer to the last route added
   * @since  2.0.0
   * @access protected
   */
  protected $last_route = null;

  /**
   * An array containing the parameters to pass to the callback function,
   * retrieved during run()
   *
   * @var Array An array containing the list of routing rules
   * @since  2.0.1
   * @access protected
   */
  protected $params = array();

  /**
   * An array containing the list of routing rules and their callback
   * functions, as well as their priority and any additional paramters.
   *
   * @var Array An array containing the list of routing rules
   * @since  2.0.0
   * @access protected
   */
  protected $routes = array();

  /**
   * An array containing the list of routing rules before they are parsed
   * into their regex equivalents, used for debugging and test cases
   *
   * @var Array An array containing the list of unaltered routing rules
   * @since  2.0.1
   * @access protected
   */
  protected $routes_original = array();

  /**
   * Whether or not to display errors for things like malformed routes or
   * conflicting routes.
   *
   * @var Boolean Whether or not to display errors
   * @since  2.0.0
   * @access protected
   */
  protected $show_errors = true;

  /**
   * A sanitized version of the URL, excluding the domain and base component
   *
   * @var String A clean URL
   * @since  2.0.0
   * @access protected
   */
  protected $url_clean = '';

  /**
   * The dirty URL, direct from $_SERVER['REQUEST_URI']
   *
   * @var String The unsanitized URL (Full URL)
   * @since  2.0.0
   * @access protected
   */
  protected $url_dirty = '';

  /**
   * Initializes the router by getting the URL and cleaning it
   *
   * @since  2.0.0
   * @access protected
   */
  public function __construct($url = null) {
    if ($url == null) {

      // Get the current URL, differents depending on platform/server software
      if (isset($_SERVER['REQUEST_URL']) && !empty($_SERVER['REQUEST_URL'])) {
        $url = $_SERVER['REQUEST_URL'];
      } else {
        $url = $_SERVER['REQUEST_URI'];
      }
    }

    $this->auth = new Auth();

    // Store the dirty version of the URL
    $this->url_dirty = $url;

    // Clean the URL, removing the protocol, domain, and base directory if there is one
    $this->url_clean = $this->__get_clean_url($this->url_dirty);
  }

  /**
   * Enables the display of errors such as malformed URL routing rules or
   * conflicting routing rules. Not recommended for production sites.
   *
   * @since  2.0.0
   * @access public
   */
  public function show_errors() {
    $this->show_errors = true;
  }

  /**
   * Disables the display of errors such as malformed URL routing rules or
   * conflicting routing rules. Not recommended for production sites.
   *
   * @since  2.0.0
   * @access public
   */
  public function hide_errors() {
    $this->show_errors = false;
  }

  /**
   * If the router cannot match the current URL to any of the given routes,
   * the function passed to this method will be executed instead. This would
   * be useful for displaying a 404 page for example.
   *
   * @since  2.0.0
   * @access public
   *
   * @param string|array $callback The function or class + function to execute if no other routes are matched
   */
  public function default_route($callback) {
    $this->default_route = $callback;
  }

  public function denied_route($callback) {
    $this->denied_route = $callback;
  }

  public function denied_redirect($callback) {
    $this->denied_redirect = $callback;
  }

  /**
   * Tries to match one of the URL routes to the current URL, otherwise
   * execute the default function and return false.
   *
   * @since  2.0.1
   * @access public
   *
   * @return bool True if a route was matched, false if not
   */
  public function run() {
    // Whether or not we have matched the URL to a route
    $matched_route = false;

    // Sort the array by priority
    ksort($this->routes);

    // Loop through each priority level
    foreach ($this->routes as $priority => $routes) {

      // Loop through each route for this priority level
      foreach ($routes as $route => $callback) {
        // Does the routing rule match the current URL?
        if (preg_match($route, $this->url_clean, $matches)) {

          // A routing rule was matched
          $matched_route = true;

          // Parameters to pass to the callback function
          $params = [];
          // Get any named parameters from the route
          foreach ($matches as $key => $match) {
            if (is_string($key)) {
              $params[] = $match;
            }
          }
          // Store the parameters and callback function to execute later
          $this->params   = $params;
          $this->callback = $callback;

          // Return the callback and params, useful for unit testing
          return array('callback' => $callback, 'params' => $params, 'route' => $route, 'original_route' => $this->routes_original[$priority][$route]);
        }
      }
    }
    // Was a match found or should we execute the default callback?
    if (!$matched_route && $this->default_route !== null) {
      $this->params   = explode('/', $this->url_clean);
      $this->callback = $this->default_route;

      return array('params' => $this->url_clean, 'callback' => $this->default_route, 'route' => false, 'original_route' => false);
    }
  }

  /**
   * Calls the appropriate callback function and passes the given parameters
   * given by Router::run()
   *
   * @since  2.0.1
   * @access public
   *
   * @return boolean False if the callback cannot be executed, true otherwise
   */
  public function dispatch() {
    if ($this->callback == null) {
      throw new Exception('Callback Dosnt exist for this route.');

      return false;
    }

    if (API == true || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
      define('AJAX', true);
    } else {
      define('AJAX', false);
    }

    $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
    if ($this->auth->check($this->callback, $this->denied_redirect)) {
      $this->load();
    } else {
      $this->denied();
    }

    return true;
  }

  /**
   * access was denied, Sets the new callback and send redirect url
   *
   * @access public
   */
  public function denied() {
    global $session;
    if (!empty($session['login_redirect'])) {
      $session['login_redirect'] = ['callback' => $this->callback, 'params' => $this->params];
    }
    $this->callback = $this->denied_route;
    $this->method   = 'GET';
    $this->params   = [$this->denied_redirect];
    $this->load();
  }

  /**
   * Loads the controller
   *
   * @access public
   */
  public function load() {
    if (!@include(CONTROLLER . $this->callback . '.php')) {
      throw new APIException('Failed to include ' . $this->callback . '.php');
    }
    if (class_exists($this->callback)) {
      $this->controller = new $this->callback;
      if (AJAX == true && method_exists($this->controller, $this->method . '_AJAX')) {
        call_user_func_array(array($this->controller, $this->method . '_AJAX'), $this->params);
      } else if (method_exists($this->controller, $this->method)) {
        call_user_func_array(array($this->controller, $this->method), $this->params);
      } else if (method_exists($this->controller, 'ALL')) {
        call_user_func_array(array($this->controller, 'ALL'), $this->params);
      } else {
        throw new APIException('The Method ' . $this->method . ' and ALL do not exist in class ' . $this->controller . '');
      }
    } else {
      throw new APIException('The Class ' . $this->controller . ' dose not exist.');
    }
  }

  /**
   * Runs the router matching engine and then calls the dispatcher
   *
   * @uses   Router::run()
   * @uses   Router::dispatch()
   *
   * @since  2.0.1
   * @access public
   */
  public function execute() {
    $this->run();
    $this->dispatch();
  }

  /**
   * Adds a new URL routing rule to the routing table, after converting any of
   * our special tokens into proper regular expressions.
   *
   * @since  2.0.0
   * @access public
   *
   * @param string       $route    The URL routing rule
   * @param string|array $callback The function or class + function to execute if this route is matched to the current URL
   * @param integer      $priority The priority to match this route. Lower priorities are executed before higher priorities
   *
   * @return boolean True if the route was added, false if it was not (If a conflict occured)
   */
  public function route($route, $callback, $priority = 10) {
    // Keep the original routing rule for debugging/unit tests
    $original_route = $route;

    // Make sure the route ends in a / since all of the URLs will
    $route = rtrim($route, '/') . '/';

    // Custom capture, format: <:var_name|regex>
    $route = preg_replace('/\<\:(.*?)\|(.*?)\>/', '(?P<\1>\2)', $route);

    // Alphanumeric capture (0-9A-Za-z-_), format: <:var_name>
    $route = preg_replace('/\<\:(.*?)\>/', '(?P<\1>[A-Za-z0-9\-\_]+)', $route);

    // Numeric capture (0-9), format: <#var_name>
    $route = preg_replace('/\<\#(.*?)\>/', '(?P<\1>[0-9]+)', $route);

    // Wildcard capture (Anything INCLUDING directory separators), format: <*var_name>
    $route = preg_replace('/\<\*(.*?)\>/', '(?P<\1>.+)', $route);

    // Wildcard capture (Anything EXCLUDING directory separators), format: <!var_name>
    $route = preg_replace('/\<\!(.*?)\>/', '(?P<\1>[^\/]+)', $route);

    // Add the regular expression syntax to make sure we do a full match or no match
    $route = '#^' . $route . '$#';

    // Does this URL routing rule already exist in the routing table?
    if (isset($this->routes[$priority][$route])) {

      // Trigger a new error and exception if errors are on
      if ($this->show_errors) {
        throw new Exception('The URI "' . htmlspecialchars($route) . '" already exists in the router table');
      }

      return false;
    }

    // Add the route to our routing array
    $this->routes[$priority][$route]          = $callback;
    $this->routes_original[$priority][$route] = $original_route;

    return true;
  }

  /**
   * Retrieves the part of the URL after the base (Calculated from the location
   * of the main application file, such as index.php), excluding the query
   * string. Adds a trailing slash.
   *
   * <code>
   * http://localhost/projects/test/users///view/1 would return the following,
   * assuming that /test/ was the base directory
   *
   * /users/view/1/
   * </code>
   *
   * @since  2.0.0
   * @access protected
   *
   * @param string $url The "dirty" url, not including the domain (path only)
   *
   * @return string The cleaned URL
   */
  protected function __get_clean_url($url) {
    // The request url might be /project/index.php, this will remove the /project part
    $url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $url);

    // Remove the query string if there is one
    $query_string = strpos($url, '?');

    if ($query_string !== false) {
      $url = substr($url, 0, $query_string);
    }

    // If the URL looks like http://localhost/index.php/path/to/folder remove /index.php
    if (substr($url, 1, strlen(basename($_SERVER['SCRIPT_NAME']))) == basename($_SERVER['SCRIPT_NAME'])) {
      $url = substr($url, strlen(basename($_SERVER['SCRIPT_NAME'])) + 1);
    }

    // Make sure the URI ends in a /
    $url = rtrim($url, '/') . '/';

    // Replace multiple slashes in a url, such as /my//dir/url
    $url = preg_replace('/\/+/', '/', $url);

    return $url;
  }
}