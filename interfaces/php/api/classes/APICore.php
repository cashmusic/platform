<?php
/**
 * The main API Class used to translate incoming requests to 
 *
 * @package api.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
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
			exit('{"api_error":"API could not connect to the core framework. (class CASHRequest not defined.)"}');
		}
		$this->version = CASHRequest::$version;
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
				'verbose' => false
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
			} else {
				// proper REST stuff goes here. for now return false
				$request_array = false;
			}
			return $request_array;
		} else {
			return false;
		}
	}

	/**
	 * Parse the URL and return a response
	 *
	 * @return array
	 */public function respond($parsed_url) {
		// pass basic no-cache headers
		header("Cache-Control: no-store, no-cache, must-revalidate, private");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		// check the parsed_url to see if things are good
		if ($parsed_url) {
			// make a request based on the parsed url data
			$request_method = 'api_public';
			// fold in any POST/GET
			if(!empty($_POST)) {
				$parsed_url = array_merge($_POST,$parsed_url);
			}
			if(!empty($_GET)) {
				$parsed_url = array_merge($_GET,$parsed_url);
			}
			if (isset($parsed_url['api_key'])) {
				$api_key = $parsed_url['api_key'];
				unset($parsed_url['api_key']);
				$auth_request = new CASHRequest(
					array(
						'cash_request_type' => 'system', 
						'cash_action' => 'validateapicredentials',
						'api_key' => $api_key
					)
				);
				if ($auth_request->response['status_code'] == '200') {
					$request_method = $auth_request->response['payload']['auth_type'];
					$parsed_url['user_id'] = $auth_request->response['payload']['user_id'];
				}
			}
			$api_request = new CASHRequest(
				$parsed_url,
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
				echo json_encode(array('status_code'=>400,'status_message'=>'Bad Request','contextual_message'=>'You did that wrong.','api_version'=>$this->version,'timestamp'=>time()));
				exit;
			}
		} else {
			header("{$_SERVER['SERVER_PROTOCOL']} 302 Found",true);
			echo json_encode(array('greeting'=>'hi.','api_version'=>$this->version,'timestamp'=>time()));
			exit;
		}
	}

} // END class 
?>