<?
class login extends controller {
	public function GET($ref=NULL) {
		echo $ref;
	}
	public function POST() {
		dbug($session['login_redirect']);
		echo $_POST;
	}
}