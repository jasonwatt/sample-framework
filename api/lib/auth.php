<?PHP

class Auth
{
  function __construct() {
    global $session;
    //setup
    $this->session = & $session;
  }

  /**
   * @param string $callback - The page/controller requested
   * @param string $denied_redirect - if the page is denied, this is where it will go.
   *
   * @return bool
   */

  /**
   * TODO: This still needs a lot of work, Still need to figure out the structure of the app before we can use this.
   * For now just return true.
   */

  function check($callback = '', $denied_redirect = '') {
    $group   = 0;
    $checked = true;
    //Check is session permission have been set
    //pull config for the page that should be loaded.
    //check is user has permisison.
    //DAO::checkLogin('','asdf');
    if (isset($this->session['userid']) && isset($this->session['pagePermission'])) {
      $checked = in_array(parent::callback, $this->session['pagePermission']);
    } else {
      //For later, if permissions are not set, pull default permissions for the group
      //$checked = DAO::getPermission($group,parent::callback);
    }

    //if the requested page is the denied page then let it show the denied page, or we will just infinitely loop
    if (!$checked && $callback != $denied_redirect) {
      return false;
    }

    return true;
  }
}