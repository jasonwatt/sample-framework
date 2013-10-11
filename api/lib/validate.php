<?
/**
 * Class Validate
 *  Set global model validation function here
 */


class Validate
{
  public static function password($password) {
    if (empty($password)) {
      return 'Not a valid Password';
    }

    return true;
  }

  //TODO: Add email validations
}