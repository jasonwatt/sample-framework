<?
class view {
  /**
   * @View string
   *    The Default View, 404 Not found
   * @Template String
   *    The default template to use
   * @Data Array
   *    Data from the model
   * @Session Object
   *    The global session put into the calss
   * @Meta Object
   *    MetaData for the view from the model and controller
   */
  public $View = '404';
	public $Template='default';
	public $Data = [];
	public $Session;
	public $Meta;
	
	public function __construct(){
		global $session;
		
		$this->Meta = new stdClass();
		$this->Session = $session;
		$this->Meta->Title = TITLEDEFAULT;
	}

  /**
   * Checks if the view files exist
   * Sets the file locations
   * if no exceptions then runs the renderTemplate function
   */
  public function render(){
		$this->ViewFile = VIEW.$this->View.'.php';
		$this->TemplateFile = TEMPLATE.$this->Template.'.php';
		
		if(!file_exists($this->ViewFile)){
			throw new APIException('The view '.$this->View.'.php file dose not exist');
		}
		if(!file_exists($this->TemplateFile)){
			throw new APIException('The template '.$this->Template.'.php file dose not exist');
		}
		$this->renderTemplate();
	}

  /**
   * pulls in the template file
   */
  private function renderTemplate(){
		require($this->TemplateFile);
	}

  /**
   * This is run from the template or view files
   * This loads the view into the template
   */
  private function renderView(){
		require($this->ViewFile);
	}

  /**
   * This is run from the template or view files
   * This adds in an smaller elements to a page making it easy to make micro templates.
   */
	private function element($element=''){
		if(!file_exists(ELEMENT.$element.'.php')){
			throw new APIException('The element '.$element.'.php file dose not exist');
		}
		require(ELEMENT.$element.'.php');
	}
}