<?php
namespace PayPal\Auth;
use PayPal\Auth\IPPThirdPartyAuthorization;
/**
 *
 * Represents token based third party authorization
 * Token based authorization credentials are obtained using
 * the Permissions API
 */
class PPTokenAuthorization implements IPPThirdPartyAuthorization {
	
	/**
	 * Permanent access token that identifies the relationship 
	 * between the authorizing user and the API caller.
	 * @var string
	 */
	private $accessToken;
	
	/**
	 * The token secret/password that will need to be used when 
	 * generating the signature.
	 * @var string
	 */
	private $tokenSecret;
	
	public function __construct($accessToken, $tokenSecret) {
		$this->accessToken = $accessToken;
		$this->tokenSecret = $tokenSecret;
	}
	
	public function getAccessToken() {
		return $this->accessToken;
	}
	
	public function getTokenSecret() {
		return $this->tokenSecret;
	}
}