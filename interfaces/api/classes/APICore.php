<?php
/**
 * The main API Class used to translate incoming requests to
 *
 * @package api.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class APICore  {
	public $version;
	private $cash_framework_core;
	private $passed_url;

	public function __construct($incoming_url) {
		// future: deal with headers/methods before url stuff
		// present: url stuff
		if (!class_exists('CASHRequest')) {
			header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found",true);
			exit('{"api_error":"API could not connect to the core framework. (class CASHRequest not defined.)"}');
		}
		$this->version = floatval('1.' . CASHRequest::$version);
		$this->respond($this->parseURL($incoming_url));
	}

	/**
	 * Parse the URL and return a response
	 *
	 * @return array
	 */public function parseURL($url) {
		if ($url) {
			$exploded_request = explode('/',trim($url,'/'));
			$request_array = false;
			$request_parameters = array(
				'plant' => false,
				'action' => false,
				'id' => false,
				'request' => false,
				'verbose' => false,
				'parsed' => $exploded_request
			);
			if ($exploded_request[0] == 'verbose') {
				$request_parameters['verbose'] = true;
				array_shift($exploded_request);
			}
			if($request_parameters['verbose']) {
				$request_parameters['plant'] = array_shift($exploded_request);
				$request_parameters['action'] = array_shift($exploded_request);
				if (is_numeric($exploded_request[0])) {
					$request_parameters['id'] = array_shift($exploded_request);
				}
				$request_array = array(
					'cash_request_type' => $request_parameters['plant'],
					'cash_action' => $request_parameters['action']
				);
				if ($request_parameters['id']) {
					$request_array['id'] = $request_parameters['id'];
				}
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
				$request_parameters['request'] = $request_array;
			}
			return $request_parameters;
		} else {
			return false;
		}
	}

	/**
	 * Parse the URL and return a response
	 *
	 * @return array
	 */public function respond($parsed_url) {
		// pass basic no-cache / CORS headers
		header('P3P: CP="ALL CUR OUR"'); // P3P privacy policy fix
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
      header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');

		// check the parsed_url to see if things are good
		if (count($parsed_url['parsed'])) {
			if ($parsed_url['verbose']) {
				// make a request based on the parsed url data
				$request_method = 'api_public';
				// fold in any POST/GET
				if(!empty($_POST)) {
					$parsed_url['request'] = array_merge($_POST,$parsed_url['request']);
				}
				if(!empty($_GET)) {
					$parsed_url['request'] = array_merge($_GET,$parsed_url['request']);
				}
				if (isset($parsed_url['request']['api_key'])) {
					$api_key = $parsed_url['request']['api_key'];
					unset($parsed_url['request']['api_key']);
					$auth_request = new CASHRequest(
						array(
							'cash_request_type' => 'system',
							'cash_action' => 'validateapicredentials',
							'api_key' => $api_key
						)
					);
					if ($auth_request->response['status_code'] == '200') {
						$request_method = $auth_request->response['payload']['auth_type'];
						$parsed_url['request']['user_id'] = $auth_request->response['payload']['user_id'];
					}
				}
				$api_request = new CASHRequest(
					$parsed_url['request'],
					$request_method
				);
				if ($api_request->response) {
					// echo the response from
					if ($api_request->response['status_code'] == 400 && $api_request->response['action'] == 'processwebhook') {
						// some webhooks check for 200 on the base URL. we need to return 200 on processwebhook bad requests. dumb.
						header("{$_SERVER['SERVER_PROTOCOL']} 200 OK",true);
					} else {
						header("{$_SERVER['SERVER_PROTOCOL']} {$api_request->response['status_code']} {$api_request->response['status_message']}",true);
					}
					$api_request->response['api_version'] = $this->version;
					$api_request->response['timestamp'] = time();
					echo json_encode($api_request->response);
					exit;
				} else {
					header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request",true);
					echo json_encode(array('status_code'=>404,'status_message'=>'Not Found','contextual_message'=>'You did that wrong.','api_version'=>$this->version,'timestamp'=>time()));
					exit;
				}
			} else {
				if (count($parsed_url['parsed']) > 1 && $parsed_url['parsed'][0] == 'asset') {
					$valid_request = false;
					$asset_identifiers = explode('.', $parsed_url['parsed'][1]);
					$asset_id = $asset_identifiers[0];
					$asset_format = $asset_identifiers[1];
					$asset_request = new CASHRequest(
						array(
							'cash_request_type' => 'asset',
							'cash_action' => 'getasset',
							'id' => $asset_id
						)
					);
					if ($asset_request->response['payload']['connection_id'] == 0 && $asset_request->response['payload']['location']) {
						$asset_stored_format = explode('.', $asset_request->response['payload']['location']);
						$asset_stored_format = $asset_stored_format[count($asset_stored_format) - 1];
						if (strtolower($asset_format) == 'json') {
							$user_request = new CASHRequest(
								array(
									'cash_request_type' => 'people',
									'cash_action' => 'getuser',
									'user_id' => $asset_request->response['payload']['user_id']
								)
							);
							$api_response = array(
								'asset' => array(
									'id' => $asset_request->response['payload']['id'],
									'location' => $asset_request->response['payload']['location'],
									'title' => $asset_request->response['payload']['title'],
									'description' => $asset_request->response['payload']['description'],
									'metadata' => json_decode($asset_request->response['payload']['metadata'],true),
								),
								'user' => array(
									'id' => $asset_request->response['payload']['user_id'],
									'username' => $user_request->response['payload']['username'],
									'display_name' => $user_request->response['payload']['display_name'],
									'url' => $user_request->response['payload']['url']
								)
							);
						} else if (strtolower($asset_format) == strtolower($asset_stored_format)) {
							header('Location: ' . $asset_request->response['payload']['location']);
							exit;
						} else {
							$api_response = array('status_code'=>415,'status_message'=>'Unsupported Media Type','contextual_message'=>'You requested a format that is not compatible with this asset.');
						}
					} else {
						$api_response = array('status_code'=>403,'status_message'=>'Forbidden','contextual_message'=>'The asset you requested is either private or does not exist.');
					}

					$api_response['api_version'] = $this->version;
					$api_response['timestamp'] = time();
					echo json_encode($api_response);
				} else {
					header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request",true);
					echo json_encode(array('status_code'=>400,'status_message'=>'Bad Request','contextual_message'=>'You did that wrong.','api_version'=>$this->version,'timestamp'=>time()));
					exit;
				}
			}
		} else {
			header("{$_SERVER['SERVER_PROTOCOL']} 302 Found",true);
			echo json_encode(array('greeting'=>'hi.','api_version'=>$this->version,'timestamp'=>time()));
			exit;
		}
	}

} // END class
?>
