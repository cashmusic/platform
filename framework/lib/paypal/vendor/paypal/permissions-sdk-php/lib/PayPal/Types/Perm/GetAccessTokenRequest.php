<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * The request use to retrieve a permanent access token. The
 * client can either send the token and verifier, or a subject.
 * 
 */
class GetAccessTokenRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * The temporary request token received from the
	 * RequestPermissions call. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $token;

	/**
	 * The verifier code returned to the client after the user
	 * authorization flow completed. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $verifier;

	/**
	 * The subject email address used to represent existing 3rd
	 * Party Permissions relationship. This field can be used in
	 * lieu of the token and verifier. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $subjectAlias;


}
