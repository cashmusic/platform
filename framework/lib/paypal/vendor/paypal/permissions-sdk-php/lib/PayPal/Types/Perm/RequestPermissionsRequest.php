<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * Describes the request for permissions over an account.
 * Primary element is "scope", which lists the permissions
 * needed. 
 */
class RequestPermissionsRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * URI of the permissions being requested. 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $scope;

	/**
	 * URL on the client side that will be used to communicate
	 * completion of the user flow. The URL can include query
	 * parameters. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $callback;

	/**
	 * Constructor with arguments
	 */
	public function __construct($scope = NULL, $callback = NULL) {
		$this->scope = $scope;
		$this->callback = $callback;
	}


}
