<?PHP
/**
 * Class SignedAPI
 * This class is to check for an verify signed API requests
 */
class SignedAPI {
  /**
   * Is this an API or Portal request
   * @var bool
   */
  public $isAPI = false;

  /**
   * is this request authentic
   * @var bool
   */
  public $authentic = false;

  /**
   * This is set to the http header signature
   * @var String
   */
  private $sig;

  /**
   * This is the post or get request data based off of the request method.
   * @var Array
   */
  private $request;
	
	public function __construct(){
		if(isset($_SERVER[HTTP_HEADER_SIGNATURE])&&(isset($_GET['userid'])||isset($_POST['userid']))){
			$this->isAPI=true;
			$this->sig = $_SERVER[HTTP_HEADER_SIGNATURE];
			
			$rm = strtoupper($_SERVER['REQUEST_METHOD']);
			switch($rm){
				case 'POST': $this->request = $_POST;
				case 'GET': $this->request = $_GET;
			}
		}
	}

  /**
   * Static function to be used through out the app to check for authenticity.
   * @return bool
   */
  public static function isAuthentic(){
		return $this->authentic;
	}
	
	public function checkData(){
		//Future DAO
    //$this->secret = SQL::getSecretKey($this->request['userid']);
		if(empty($this->secret)){ return NULL; }

		$key = $this->generateSignature($this->request,$secret);
		if($key==$this->sig){
			$this->authentic = true;
			//return SQL::getAccountLogin($this->request['userid']);
		}
	}

  /**
   * Generate the check signature hash
   * @param $data
   *
   * @return string
   */
  private function generateSignature($data){
		//sort data array alphabetically by key
    ksort($data);
    //combine keys and values into one long string
    $dataString = '';
    foreach($data as $key => $value) {
        $dataString .= $key.$value;
    }
    //lowercase everything
    $dataString = strtolower($dataString);
    //generate signature using the SHA256 hashing algorithm
    return hash_hmac("sha1",$dataString,$this->secret);
	}
}

/**
 * For reference
 *
* @param array $data Array of key/value pairs of data
* @param string $secretKey
* @return string A generated signature for the $data based on $secretKey

$USER_ID = "1234";
$SECRET_KEY = "bobs-super-secret-key";

function generateSignature($data,$secretKey)
{
		//sort data array alphabetically by key
    ksort($data);
    //combine keys and values into one long string
    $dataString = '';
    foreach($data as $key => $value) {
        $dataString .= $key.$value;
    }
    //lowercase everything
    $dataString = strtolower($dataString);
    //generate signature using the SHA256 hashing algorithm
    return hash_hmac("sha1",$dataString,$this->secret);
}
 
$bobsData = array(
    "userid" => $USER_ID,
    "email" => "newbob@example.com"
);
 
$sig = generateSignature($bobsData,$SECRET_KEY);
//add signature to the outgoing data
$bobsData['sig'] = $sig;
//generate HTTP query string
$queryString = http_build_query($bobsData);

header("coaster-junction-sig: ".$sig);

echo $queryString;

*/