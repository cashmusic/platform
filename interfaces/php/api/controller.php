<?php
$api_root = dirname(__FILE__);
define('CASH_PLATFORM_ROOT', $api_root.'/../../../framework/php');

// set up autoload for core classes
function cash_autoloadCore($classname) {
	$file = CASH_PLATFORM_ROOT.'/classes/core/'.$classname.'.php';
	if (file_exists($file)) {
		require_once($file);
	}
}
spl_autoload_register('cash_autoloadCore');

$http_codes = array(
	100 => 'Continue',
	101 => 'Switching Protocols',
	200 => 'OK',
	201 => 'Created',
	202 => 'Accepted',
	203 => 'Non-Authoritative Information',
	204 => 'No Content',
	205 => 'Reset Content',
	206 => 'Partial Content',
	300 => 'Multiple Choices',
	301 => 'Moved Permanently',
	302 => 'Found',
	303 => 'See Other',
	304 => 'Not Modified',
	305 => 'Use Proxy',
	307 => 'Temporary Redirect',
	400 => 'Bad Request',
	401 => 'Unauthorized',
	402 => 'Payment Required',
	403 => 'Forbidden',
	404 => 'Not Found',
	405 => 'Method Not Allowed',
	406 => 'Not Acceptable',
	407 => 'Proxy Authentication Required',
	408 => 'Request Time-out',
	409 => 'Conflict',
	410 => 'Gone',
	411 => 'Length Required',
	412 => 'Precondition Failed',
	413 => 'Request Entity Too Large',
	414 => 'Request-URI Too Large',
	415 => 'Unsupported Media Type',
	416 => 'Requested Range Not Satisfiable',
	417 => 'Expectation Failed',
	500 => 'Internal Server Error',
	501 => 'Not Implemented',
	502 => 'Bad Gateway',
	503 => 'Service Unavailable',
	504 => 'Gateway Time-out',
	505 => 'HTTP Version Not Supported'
);

header("Cache-Control: no-store, no-cache, must-revalidate, private");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header($http_codes[403], true, 403);
	exit;
}

if ($_REQUEST['p']) {
	$exploded_request = explode('/',trim($_REQUEST['p'],'/'));
	$request_parameters = array(
		'plant' => false,
		'action' => false,
		'id' => false
	);
	$request_parameters['plant'] = array_shift($exploded_request);
	$request_parameters['action'] = array_shift($exploded_request);
	if (is_numeric($exploded_request[0])) {
		$request_parameters['id'] = array_shift($exploded_request);
	}
	$request_array = array(
		'cash_request_type' => $request_parameters['plant'], 
		'cash_action' => $request_parameters['action']
	);
	if (count($exploded_request)) {
		$is_parameter = true;
		foreach ($exploded_request as $position => $parameter) {
			if ($is_parameter) {
				if (isset($exploded_request[$position + 1])) {
					$request_array[$parameter] = $exploded_request[$position + 1];
				} else {
					$request_array[$parameter] = false;
				}
				$is_parameter = false;
			} else {
				$is_parameter = true;
			}
		}
	}
	$api_request = new CASHRequest(
		$request_array,
		'api_public'
	);
	if ($api_request->response) {
		header($http_codes[$api_request->response['status_code']], true, $api_request->response['status_code']);
		echo json_encode($api_request->response);
		exit;
	} else {
		header(400, true, 400);
		echo json_encode(array('status_code'=>400,'status_message'=>$http_codes[400],'contextual_message'=>'You did that wrong.'));
		exit;
	}
}

header($http_codes[302], true, 302);
exit;
?>