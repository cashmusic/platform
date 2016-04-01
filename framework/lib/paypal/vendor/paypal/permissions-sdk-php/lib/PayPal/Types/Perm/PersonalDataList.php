<?php 
namespace PayPal\Types\Perm;
use PayPal\Core\PPMessage;  
/**
 * Set of personal data which forms the response of
 * GetPersonalData call. 
 */
class PersonalDataList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Perm\PersonalData	 
	 */ 
	public $personalData;


}
