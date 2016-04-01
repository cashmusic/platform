<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * The list of permissions associated with the token. 
 */
class GetPermissionsResponse  
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
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
