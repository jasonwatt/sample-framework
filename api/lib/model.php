<?
/**
 * Class Model
 *   Gets loaded from the controller class
 *   newModel function in the controller class will load the models
 *   All models extend this class.
 */
class Model
{
  public $valid = true;
  public $errors = [];

  public function __construct() {

  }

  /**
   * @param $data
   *  key - function to be run
   *  value - data passed to the key function called
   *
   * @return bool
   * @throws APIException
   */
  public function validate($data) {
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        if (method_exists($this, $k)) { //check if the model has this function
          $ret = $this->$k($v);
        } else if (method_exists('Validate', $k)) { // look in the validate class to see if this is a global function.
          $ret = call_user_func_array(array('Validate', $k), [$v]);
        } else { //if all else fails then throw an error
          throw new APIException('the Method Validate::' . $k . ' dose not exist');
        }
        /**
         * $ret can be a string to explain why the validation failed.
         */
        if ($ret !== true) {
          $this->valid      = false;
          $this->errors[$k] = $ret;
        }
      }

      return $this->valid;
    } else {
      return false;
    }
  }
}