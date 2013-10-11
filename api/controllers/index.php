<?
class index extends controller
{
  public function GET_AJAX() {
    $this->View->Data = ['message' => 'Welcome to Coaster Junction'];
    $this->render();
  }

  public function GET() {
    $this->newModel('Register');
    if ($this->Model->Register->Validate(['username' => 'zanson1', 'password' => '123134'])) {
      dbug($this->Model->Register->errors);
      $this->View->Data = ['message' => 'There was a problem'];
    } else {
      $this->View->Data = ['message' => 'Hi there.'];
    }
    $this->View->View     = 'index';
    $this->View->Template = 'homepage';
    $this->render();
  }
}