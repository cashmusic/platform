<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * A property of User Identity data , represented as a
 * Name-value pair with Name being the PersonalAttribute
 * requested and value being the data. 
 */
class PersonalData  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $personalDataKey;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $personalDataValue;


}
