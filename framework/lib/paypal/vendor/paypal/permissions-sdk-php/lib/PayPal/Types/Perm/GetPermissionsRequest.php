<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * Request to retrieve the approved list of permissions
 * associated with a token. 
 */
class GetPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * The permanent access token to ask about. 
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
