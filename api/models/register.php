<?
class Register extends Model
{
  public $password = 'password';
  public $email = 'email';

  public function username($username) {
    $userData = DAO::checkUsername($username);
    if (!empty($userData)) {

      //if exists, fail
      return true;
    }

    return false;
  }
}