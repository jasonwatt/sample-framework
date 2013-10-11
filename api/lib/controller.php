<?PHP
/**
 * Class controller
 *
 */
class controller {
	public $Session;
	private $AJAX=false;
	public $View;
	public $Model;
	
	public function __construct(){
		global $session;
		require(LIB.'view.php');
		require(LIB.'model.php');
		require(LIB.'validate.php');
		
		$this->Session = $session;
		$this->View = new View();
	}

  /**
   * Loads model into object
   * @param $modelName
   *
   * @throws APIException
   * @throws Exception
   */
  public function newModel($modelName){
		if(empty($modelName)){
			throw new Exception('Model Name is Empty');
		}
		if(empty($this->Model)){
			$this->Model = new stdClass();
		}
		if(!@include(MODEL.strtolower($modelName).'.php')) throw new APIException('Failed to include '.MODEL.strtolower($modelName).'.php'.'');
		$this->Model->{$modelName} = new $modelName;
	}

  /**
   * Renders a view or JSON if ajax request.
   */
  public function render(){
		if(AJAX){
			header('Content-Type: application/json');
			echo json_encode($this->View->Data);
			return;
		}
		$this->View->Render();
	}
}