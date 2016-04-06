<?php
namespace PayPal\Auth;
/**
 * Interface that represents API credentials
 */
abstract class IPPCredential
{
	/**
	 * 
	 * @var IPPThirdPartyAuthorization
	 */
	protected $thirdPartyAuthorization;
	
	public function setThirdPartyAuthorization($thirdPartyAuthorization) {
		$this->thirdPartyAuthorization = $thirdPartyAuthorization;
	}
	
	public function getThirdPartyAuthorization() {
		return $this->thirdPartyAuthorization;
	}
	
	public abstract function validate();
}