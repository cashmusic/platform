<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * Permanent access token and token secret that can be used to
 * make requests for protected resources owned by another
 * account. 
 */
class GetAccessTokenResponse  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ResponseEnvelope	 
	 */ 
	public $responseEnvelope;

	/**
	 * Identifier for the permissions approved for this
	 * relationship. 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $scope;

	/**
	 * Permanent access token that identifies the relationship that
	 * the user authorized. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $token;

	/**
	 * The token secret/password that will need to be used when
	 * generating the signature. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $tokenSecret;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
