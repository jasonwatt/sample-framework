<?
class error404 extends controller {
	function __construct(){
		parent::__construct();
	}
	public function GET($url) {
//		header("HTTP/1.0 404 Not Found");
		$this->View->Data = ['error'=>'404', 'URL'=>$url];
		$this->View->View = '404';
		$this->render();
	}
}