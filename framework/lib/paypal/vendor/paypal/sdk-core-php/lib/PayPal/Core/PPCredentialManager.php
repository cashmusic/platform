<?php
namespace PayPal\Core;
use PayPal\Core\PPCredentialManager;
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPCertificateCredential;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Exception\PPInvalidCredentialException;
use PayPal\Exception\PPMissingCredentialException;

class PPCredentialManager
{
	private static $instance;
	//hashmap to contain credentials for accounts.
	private $credentialHashmap = array();
	/**
	 * Contains the API username of the default account to use
	 * when authenticating API calls.
	 * @var string
	 */
	private $defaultAccountName;
	
	/*
	 * Constructor initialize credential for multiple accounts specified in property file.
	 */
	private function __construct($config){
		try {
			$this->initCredential($config);
		} catch (Exception $e) {
			$this->credentialHashmap = array();
			throw $e;
		}		
	}
	
	/*
	 * Create singleton instance for this class.
	 */
	public static function getInstance($config)
	{
		
			return self::$instance = new PPCredentialManager($config);
		
	}
	
	/*
	 * Load credentials for multiple accounts, with priority given to Signature credential. 
	 */
	private function initCredential($config) {
		$suffix = 1;
		$prefix = "acct";
		if(array_key_exists($prefix, $config))
		{
			$credArr =  $this->config[$searchKey];
		}
		else {
			$arr = array();
			foreach ($config as $k => $v){
				if(strstr($k, $prefix)){
					$arr[$k] = $v;
				}
			}
				
			$credArr =  $arr;
		}
		
		$arr = array();
		foreach ($config as $key => $value) {
			$pos = strpos($key, '.');
			if(strstr($key, "acct")){
				$arr[] = substr($key, 0, $pos);
			}
		}
		$arrayPartKeys =  array_unique($arr);
		
		if(count($arrayPartKeys) == 0)
			throw new PPMissingCredentialException("No valid API accounts have been configured");

		$key = $prefix.$suffix;
		while (in_array($key, $arrayPartKeys)){
							
			if(isset($credArr[$key.".Signature"]) 
					&& $credArr[$key.".Signature"] != null && $credArr[$key.".Signature"] != ""){
					
				$userName = isset($credArr[$key.'.UserName']) ? $credArr[$key.'.UserName'] : "";
				$password = isset($credArr[$key.'.Password']) ? $credArr[$key.'.Password'] : "";
				$signature = isset($credArr[$key.'.Signature']) ? $credArr[$key.'.Signature'] : "";
				
				$this->credentialHashmap[$userName] = new PPSignatureCredential($userName, $password, $signature);
				if (isset($credArr[$key.'.AppId'])) {				
					$this->credentialHashmap[$userName]->setApplicationId($credArr[$key.'.AppId']);
				}
				
			} elseif (isset($credArr[$key.".CertPath"]) 
					&& $credArr[$key.".CertPath"] != null && $credArr[$key.".CertPath"] != ""){
						
				$userName = isset($credArr[$key.'.UserName']) ? $credArr[$key.'.UserName'] : "";
				$password = isset($credArr[$key.'.Password']) ? $credArr[$key.'.Password'] : "";
				$certPassPhrase = isset($credArr[$key.'.CertKey']) ? $credArr[$key.'.CertKey'] : "";	
				$certPath = isset($credArr[$key.'.CertPath']) ? $credArr[$key.'.CertPath'] : "";				
				
				$this->credentialHashmap[$userName] = new PPCertificateCredential($userName, $password, $certPath, $certPassPhrase);
				if (isset($credArr[$key.'.AppId'])) {
					$this->credentialHashmap[$userName]->setApplicationId($credArr[$key.'.AppId']);
				}				
			} elseif (isset($credArr[$key.".ClientId"]) && isset($credArr[$key.".ClientId"]) ){
				$userName = $key;
				$this->credentialHashmap[$userName] = array('clientId' => $credArr[$key.".ClientId"], 
						'clientSecret' => $credArr[$key.".ClientSecret"]);
			}

			if($userName && isset($credArr[$key . ".Subject"]) && trim($credArr[$key . ".Subject"]) != "" ) {
				$this->credentialHashmap[$userName]->setThirdPartyAuthorization(
						new PPSubjectAuthorization($credArr[$key . ".Subject"]));
			}
			else if($userName && (isset($credArr[$key . 'accessToken']) && isset($credArr[$key . 'tokenSecret']))) {
				$this->credentialHashmap[$userName]->setThirdPartyAuthorization(
						new PPTokenAuthorization($credArr[$key.'accessToken'], $credArr[$key.'tokenSecret']));
			}
			
			if ($userName && $this->defaultAccountName == null) {
				if(array_key_exists($key. '.UserName', $credArr)) {
					$this->defaultAccountName = $credArr[$key . '.UserName'];
				} else {
					$this->defaultAccountName = $key;
				}
			}
			$suffix++;
			$key = $prefix.$suffix;
		}

	}

	/*
	 * Obtain Credential Object based on UserId provided.
	 */
	public function getCredentialObject($userId = null){
		
		if($userId == null) {			
			$credObj = $this->credentialHashmap[$this->defaultAccountName];
		} else if (array_key_exists($userId, $this->credentialHashmap)) {
			$credObj = $this->credentialHashmap[$userId];
		}
			
		if (empty($credObj)) {
			throw new PPInvalidCredentialException("Invalid userId $userId");
		}
		return $credObj;
	}

	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

}
