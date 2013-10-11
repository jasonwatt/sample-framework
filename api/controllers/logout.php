<?
class logout extends controller {
	public function GET($ref=NULL) {
		global $session;
		$session->destroy();
		header( 'Location: http://'.BASE_URL.'/' ) ;
	}
	public function POST() {
		echo $_POST;
	}
}