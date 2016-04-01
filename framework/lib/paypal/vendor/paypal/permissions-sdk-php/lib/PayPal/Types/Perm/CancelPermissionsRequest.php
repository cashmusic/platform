<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * Request to invalidate an access token and revoke the
 * permissions associated with it. 
 */
class CancelPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $token;

	/**
	 * Constructor with arguments
	 */
	public function __construct($token = NULL) {
		$this->token = $token;
	}


}
