<?php
namespace PayPal\Service;
use PayPal\Core\PPMessage;
use PayPal\Core\PPBaseService;
use PayPal\Core\PPUtils;
use PayPal\Common\PPApiContext;
use PayPal\Handler\PPPlatformServiceHandler;
use PayPal\Types\Perm\RequestPermissionsResponse;
use PayPal\Types\Perm\GetAccessTokenResponse;
use PayPal\Types\Perm\GetPermissionsResponse;
use PayPal\Types\Perm\CancelPermissionsResponse;
use PayPal\Types\Perm\GetBasicPersonalDataResponse;
use PayPal\Types\Perm\GetAdvancedPersonalDataResponse;

/**
 * AUTO GENERATED code for Permissions
 */
class PermissionsService extends PPBaseService {

	// Service Version
	private static $SERVICE_VERSION = "";

	// Service Name
	private static $SERVICE_NAME = "Permissions";

    // SDK Name
	protected static $SDK_NAME = "permissions-php-sdk";

	// SDK Version
	protected static $SDK_VERSION = "3.9.1";

    /**
    * @param $config - Dynamic config map. This takes the higher precedence if config file is also present.
    *
    */
	public function __construct($config = null) {
		parent::__construct(self::$SERVICE_NAME, 'NV', $config);
	}


	/**
	 * Service Call: RequestPermissions
	 * @param RequestPermissionsRequest $requestPermissionsRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\RequestPermissionsResponse
	 * @throws APIException
	 */
	public function RequestPermissions($requestPermissionsRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new RequestPermissionsResponse();
		$resp = $this->call('Permissions', 'RequestPermissions', $requestPermissionsRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}


	/**
	 * Service Call: GetAccessToken
	 * @param GetAccessTokenRequest $getAccessTokenRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\GetAccessTokenResponse
	 * @throws APIException
	 */
	public function GetAccessToken($getAccessTokenRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new GetAccessTokenResponse();
		$resp = $this->call('Permissions', 'GetAccessToken', $getAccessTokenRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}


	/**
	 * Service Call: GetPermissions
	 * @param GetPermissionsRequest $getPermissionsRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\GetPermissionsResponse
	 * @throws APIException
	 */
	public function GetPermissions($getPermissionsRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new GetPermissionsResponse();
		$resp = $this->call('Permissions', 'GetPermissions', $getPermissionsRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}


	/**
	 * Service Call: CancelPermissions
	 * @param CancelPermissionsRequest $cancelPermissionsRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\CancelPermissionsResponse
	 * @throws APIException
	 */
	public function CancelPermissions($cancelPermissionsRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new CancelPermissionsResponse();
		$resp = $this->call('Permissions', 'CancelPermissions', $cancelPermissionsRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}


	/**
	 * Service Call: GetBasicPersonalData
	 * @param GetBasicPersonalDataRequest $getBasicPersonalDataRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\GetBasicPersonalDataResponse
	 * @throws APIException
	 */
	public function GetBasicPersonalData($getBasicPersonalDataRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new GetBasicPersonalDataResponse();
		$resp = $this->call('Permissions', 'GetBasicPersonalData', $getBasicPersonalDataRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}


	/**
	 * Service Call: GetAdvancedPersonalData
	 * @param GetAdvancedPersonalDataRequest $getAdvancedPersonalDataRequest
	 * @param mixed $apiCredential - Optional API credential - can either be
	 * 		a username configured in sdk_config.ini or a ICredential object
	 *      created dynamically
	 * @return Types\Perm\GetAdvancedPersonalDataResponse
	 * @throws APIException
	 */
	public function GetAdvancedPersonalData($getAdvancedPersonalDataRequest, $apiCredential = NULL) {
		$apiContext = new PPApiContext($this->config);
		$handlers = array(
			new PPPlatformServiceHandler($apiCredential, self::$SDK_NAME, self::$SDK_VERSION),
		);
		$ret = new GetAdvancedPersonalDataResponse();
		$resp = $this->call('Permissions', 'GetAdvancedPersonalData', $getAdvancedPersonalDataRequest, $apiContext, $handlers);
		$ret->init(PPUtils::nvpToMap($resp));
		return $ret;
	}

}
