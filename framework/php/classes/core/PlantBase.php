<?php
/**
 * Abstract base for all Plant classes
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 * This file is generously sponsored by fluorine
 * fluorine was here: http://polvo.ca/fluorine/ 
 *
 */abstract class PlantBase extends CASHData {
	protected $request_method,$request_type,$action=false,$request,$response,$db_required=true,$routing_table;

	/**
	 * Called by CASHRequest to begin action and return an instance of CASHResponse 
	 *
	 */abstract public function processRequest();
	
	/**
	 * Sets object parameters and makes database connections if needed
	 *
	 * @param {string} $request_method - 'get'/'post'/'direct'/'commandline'
	 * @param {array} $request - an associative array containing all request parameters
	 * @return void
	 */protected function plantPrep($request_method,$request) {
		$this->request_method = $request_method;
		$this->request = $request;
		if (isset($this->request['cash_action'])) {
			$this->action = strtolower($this->request['cash_action']);
		}
		$this->response = new CASHResponse();
		if ($this->db_required) {
			$this->connectDB();
		}
	}

	/**
	 * Checks the current request method ($this->request_method) against one
	 * or more strings representing allowed methods: 'get','post','direct', or
	 * 'commandline'
	 *
	 * @param {string} one or more strings specifying allowed request methods
	 * @return boolean
	 */protected function checkRequestMethodFor() {
		$args_count = func_num_args();
		if ($args_count > 0) {
			$args = func_get_args();
			if (is_array($args[0])) {
				$test_args = $args[0];
			} else {
				$test_args = $args;
			}
			foreach ($test_args as $arg) {
			    if ($arg == $this->request_method) {
					return true;
				}
			}
			$this->response->pushResponse(
				403, $this->request_type, $this->action,
				false,
				"please try another request method, '{$this->request_method}' is not allowed"
			);
			return false;
		} else {
			// error: at least one argument must be given
			return false;
		}
	}
	
	/**
	 * Checks the request for certain required parameters, quits and returns
	 * an error response if not foun
	 *
	 * @param {string} one or more strings specifying allowed request methods
	 * @return boolean
	 */protected function requireParameters() {
		$args_count = func_num_args();
		if ($args_count > 0) {
			$args = func_get_args();
			$invalid_arg = false;
			foreach ($args as $arg) {
			    if (!isset($this->request["$arg"])) {
					$invalid_arg = true;
				} else {
					if ($this->request["$arg"] === '') {
						$invalid_arg = true;
					}
				}
				if ($invalid_arg) {
					$this->response->pushResponse(
						400, $this->request_type, $this->action,
						false,
						"required parameter missing: '$arg'"
					);
					return false;
				}
			}
		}
		return true;
	}

	public function routeBasicRequest() {
		if (isset($this->routing_table[$this->action])) {
			if (!$this->checkRequestMethodFor($this->routing_table[$this->action]['request_methods'])) { 
				return $this->pushFailure('request method not allowed'); 
			}
			try {
				$target_method = $this->routing_table[$this->action]['target_method'];
				$method = new ReflectionMethod(get_class($this), $target_method);
				$params = $method->getParameters();
				$final_parameters = array();
				foreach ($params as $param) {
					// $param is an instance of ReflectionParameter
					$param_name = $param->getName();
					if ($param->isOptional()) {
						if (isset($this->request[$param_name])) {
							$final_parameters[$param_name] = $this->request[$param_name];
						} else {
							$final_parameters[$param_name] = $param->getDefaultValue();
						}
					} else {
						// required, return failure if missing
						if (isset($this->request[$param_name])) {
							$final_parameters[$param_name] = $this->request[$param_name];
						} else {
							return $this->pushFailure('missing required parameter: ' . $param_name);
						}
					}
				}
				$result = call_user_func_array(array($this, $target_method), $final_parameters);
				if ($result) {
					return $this->pushSuccess($result,'success.');
				} else {
					return $this->pushFailure('there was an error');
				}
			} catch (Exception $e) {
				return $this->pushFailure('corresponding class method not found');
			}
		} else {
			// not found in standard routing table
			return false;
		}
	}

	protected function pushSuccess($payload,$message) {
		return $this->response->pushResponse(
			200,
			$this->request_type,
			$this->action,
			$payload,
			$message
		);
	}
	
	protected function pushFailure($message) {
		return $this->response->pushResponse(
			400,
			$this->request_type,
			$this->action,
			false,
			$message
		);
	}
} // END class 
?>