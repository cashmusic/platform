<?php
class MC_OAuth2Client extends OAuth2Client {

	public $access_token = null;
	public $refresh_token = null;
	public $instance_url = null;

	public function __construct($options=array()) {
		$redirect_uri = $options['redirect_uri']; //basically, your redirect_url
		$client_id = $options['client_id'];   //client_id from creating app in MC (see README)
		$client_secret = $options['client_secret']; //client_secret from creating app in MC  (see README)
		$code = false; 
		if (isset($options['code'])) {
			$code = $options['code'];
		}

		$config = array(
			'client_id'=>$client_id, 
			'client_secret'=>$client_secret,
			'authorize_uri'=>'https://login.mailchimp.com/oauth2/authorize',
			'access_token_uri'=>'https://login.mailchimp.com/oauth2/token',
			'redirect_uri'=>$redirect_uri,
			'cookie_support'=>false, 'file_upload'=>false,
			'token_as_header'=>true,
			'base_uri'=>'https://login.mailchimp.com/oauth2/',
			'code' => $code
		);
   
		parent::__construct($config);
	}
	
	/**
	* Get a Login URL for use with redirects. A full page redirect is currently
	* required.
	*
	* @param $params
	*   Provide custom parameters.
	*
	* @return
	*   The URL for the login flow.
	*/
	public function getLoginUri($params = array()) {
		$def_params = array(
			'response_type' => 'code',
			'client_id' => $this->getVariable('client_id'),
			'redirect_uri' => $this->getVariable('redirect_uri'),
		);
		$params = array_merge($params, $def_params);
		return $this->getUri( $this->getVariable('authorize_uri'), $params);
	}

	public function api($path, $method = 'GET', $params = array()) {
		try {
			return parent::api($path, $method, $params);
		} catch (OAuth2Exception $e){
			//once and only once, try to get use the refresh token to get a fresh token
			if ($e->getMessage()=='INVALID_SESSION_ID'){
				$this->refreshToken();
				return parent::api($path, $method, $params);
			} else {
				throw $e;
			}
		}

	}
}
