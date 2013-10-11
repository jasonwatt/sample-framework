<?
class permDenied extends controller {
	public function GET($get=NULL) {
		header("HTTP/1.0 403 Forbidden");
		header( 'Location: http://'.BASE_URL.'/'.$get ) ;
		$this->View->Data = ['error'=>'403', 'URL'=>$url,'message'=>'You must login to view this page'];
		$this->View->View = '403';
		$this->render();
	}
}