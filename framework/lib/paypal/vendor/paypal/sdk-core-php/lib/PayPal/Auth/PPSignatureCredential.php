<?php
namespace PayPal\Auth;
use PayPal\Exception\PPMissingCredentialException;
/**
 * API signature (3-token) based credentials
 */
class PPSignatureCredential extends IPPCredential {

	/**
	 * API username
	 * @var string
	 */
	protected $userName;

	/**
	 * API password
	 * @var string
	 */
	protected $password;
	/**
	 * API Signature
	 * @var string
	 */
	protected $signature;

	/**
	 * Application Id that uniquely identifies an application that uses the
	 * Platform APIs - Not required for Express Checkout / MassPay / DCC etc
	 * Application Ids are issued by PayPal.
	 * Test application Ids are available for the sandbox environment
	 * @var string
	 */
	protected $applicationId;

	public function __construct($userName, $password, $signature) {
		$this->userName = trim($userName);
		$this->password = trim($password);
		$this->signature = trim($signature);
		$this->validate();
	}

	public function validate() {

		if (empty($this->userName)) {
			throw new PPMissingCredentialException("username cannot be empty");
		}
		if (empty($this->password)) {
			throw new PPMissingCredentialException("password cannot be empty");
		}
		// Signature can be empty if using 3-rd party auth tokens from permissions API
	}

	public function getUserName() {
		return $this->userName;
	}
	public function getPassword() {
		return $this->password;
	}
	public function getSignature() {
		return $this->signature;
	}

	public function setApplicationId($applicationId) {
		$this->applicationId = trim($applicationId);
	}
	public function getApplicationId() {
		return $this->applicationId;
	}
}