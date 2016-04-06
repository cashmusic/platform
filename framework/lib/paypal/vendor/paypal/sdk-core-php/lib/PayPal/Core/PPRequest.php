<?php
namespace PayPal\Core;
/**
 * Encapsulates API request information
 *
 */
class PPRequest {
	
	/**
	 * Request Object
	 *
	 * @var object
	 */
	private $requestObject;
	
	/**
	 * Optional credentials associated with
	 * the request
	 * @var ICredential
	 */
	private $credential;
		
	/**
	 * Transport binding for this request.
	 * Can be NVP, SOAP etc
	 * @var string
	 */
	private $bindingType;

	/**
	 * 
	 * Holder for any binding specific info
	 * @var array
	 */
	private $bindingInfo = array();
	
	public function __construct($requestObject, $bindingType) {
		$this->requestObject = $requestObject;
		$this->bindingType = $bindingType;
	}

	public function getRequestObject() {
		return $this->requestObject;
	}
	
	public function getBindingType() {
		return $this->bindingType;
	}
	
	public function getBindingInfo($name=NULL) {
		if(isset($name)) {
			return array_key_exists($name, $this->bindingInfo) ? $this->bindingInfo[$name] : null;
		}
		return $this->bindingInfo;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function addBindingInfo($name, $value) {
		$this->bindingInfo[$name] = $value;
	}
	
	public function setCredential($credential) {
		$this->credential = $credential;
	}
	
	public function getCredential() {
		return $this->credential;
	}
}