<?php

namespace CASHMusic\Core;

use CASHMusic\Core\API\RoutingMiddleware;
use CASHMusic\Core\CASHData as CASHData;
use CASHMusic\Core\CASHResponse as CASHResponse;
use ReflectionMethod;
use Exception;

/**
 * Abstract base for all Plant classes
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by fluorine
 * fluorine was here: http://polvo.ca/fluorine/ 
 *
 */abstract class PlantBase extends CASHData {
	protected $request_method,$request_type,$action=false,$request,$response,$db_required=true,$routing_table,$repository,$api,$contextual_message, $error_code;

	/**
	 * Called by CASHRequest to begin action and return an instance of CASHResponse 
	 *
	 */public function processRequest($api=false,$http_method=false) {

		if ($this->action) {

		    if (!$api) {
                return $this->routeBasicRequest();
            } else {
		        return $this->routeAPIRequest($http_method);
            }

		} else {
		    if (!isset($this->contextual_message)) $this->contextual_message = false;
            if (!isset($this->error_code)) $this->error_code = 400;
			return $this->response->pushResponse(
                $this->error_code,$this->request_type,$this->action,
				$this->request,
				$this->contextual_message,
				'no action specified'
			);
		}
	}
	
	/**
	 * Sets object parameters and makes database connections if needed
	 *
	 * @param {string} $request_method - 'get'/'post'/'direct'/'commandline'
	 * @param {array} $request - an associative array containing all request parameters
     * @param $api boolean
	 * @return void
	 */protected function plantPrep($request_method,$request) {
		$this->request_method = $request_method;
		$this->request = $request;

		if (isset($this->request['cash_action'])) {
			$this->action = strtolower($this->request['cash_action']);
		}

		$this->response = new CASHResponse();
		if ($this->db_required) {
            $attempts = 0;
            do {
                try
                {
                    $this->connectDB();
                } catch (Exception $e) {
                    $attempts++;
                    continue;
                }

                break;
            } while($attempts < 3);
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
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Returns an array of all allowed requests for the child class
	 *
	 * @return array
	 */public function profileRequests() {
		if (empty($routing_table)) {
			// set a bogus action, fire procesRequest to set routing table
			$this->action = 'profileallowedrequests';
			$this->processRequest();
		}
		// this will hold all found requests
		$requests = array();
		// cycle through the entire routing table
		if (is_array($this->routing_table)) {
			foreach ($this->routing_table as $route => $details) {
				$target_method = $details[0];
				$allowed_methods = $details[1];

				// reflect the target method for each route, returning an array of params
				$method = new ReflectionMethod(get_class($this), $target_method);
				$params = $method->getParameters();
				$final_parameters = array();
				foreach ($params as $param) {
					// $param is an instance of ReflectionParameter
					$param_name = $param->getName();
					$param_optional = false;
					$param_default = null;
					if ($param->isOptional()) {
						$param_optional = true;
						$param_default = $param->getDefaultValue();
					}
					$final_parameters[$param_name] = array(
						'optional' => $param_optional,
						'default' => $param_default
					);
				}
				// add to the final array of acceptable requests
				$requests[$route] = array(
					'allowed_methods' => $allowed_methods,
					'parameters' => $final_parameters
				);
			}
			return $requests;
		} else {
			return false;
		}
	}

	public function getRoutingTable() {
	     try {
             $routing_table = CASHSystem::getFileContents(
                 CASH_PLATFORM_ROOT."/classes/plants/".ucfirst($this->request_type)."/"."routing.json", true
             );

             $routing_to_array = json_decode($routing_table, true);
             $this->routing_table = $routing_to_array['requestactions'];

         } catch (Exception $e) {
	         // couldn't find routing.json in plant folder
             $this->routing_table = false;
         }

		return $this->routing_table;
	}

	public function routeAPIRequest($http_method) {

        $class = new \ReflectionClass($this);

        list($restful_routes, $soap_routes) = RoutingMiddleware::getRoutingTables($class->getShortName());

		if (isset($restful_routes[$this->action]['verbs'][$http_method])) {
			try {

				$target_method = $restful_routes[$this->action]['verbs'][$http_method]['plantfunction'];
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
							if ($param_name == 'full_cash_request') {
								// this is a special case. it allows us to add a required
								// parameter called 'full_cash_request' to any method and
								// get all of the values passed in as an array — useful
								// for parsing variable data POSTed to a request
								$final_parameters[$param_name] = $this->request;
							} else {
								return $this->pushFailure('missing required parameter: ' . $param_name);
							}
						}
					}
				}

				// call the method using call_user_func_array — slower than ReflectionMethod::invokeArgs
				// but allows us to stay in $this context, calling protected methods the proper way
				$result = call_user_func_array(array($this, $target_method), $final_parameters);
				unset($method);
				if ($result !== false) {
					return $this->pushSuccess($result,'success.');
				} else {
					return $this->pushFailure('there was an error');
				}
			} catch (Exception $e) {
				return $this->pushFailure('corresponding class method not found, exception: ' . $e);
			}
		} else {
			// not found in standard routing table
			return $this->response->pushResponse(
				404,$this->request_type,$this->action,
				$this->request,
				'unknown action'
			);
		}
	}

    public function routeBasicRequest() {
        if (isset($this->routing_table[$this->action])) {
            if (!$this->checkRequestMethodFor($this->routing_table[$this->action]['security'])) {
                return $this->response->pushResponse(
                    403, $this->request_type, $this->action,
                    false,
                    "please try another request method, '{$this->request_method}' is not allowed"
                );
            }
            try {
                $target_method = $this->routing_table[$this->action]['plantfunction'];

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
                            if ($param_name == 'full_cash_request') {
                                // this is a special case. it allows us to add a required
                                // parameter called 'full_cash_request' to any method and
                                // get all of the values passed in as an array — useful
                                // for parsing variable data POSTed to a request
                                $final_parameters[$param_name] = $this->request;
                            } else {
                                return $this->pushFailure('missing required parameter: ' . $param_name);
                            }
                        }
                    }
                }

                // call the method using call_user_func_array — slower than ReflectionMethod::invokeArgs
                // but allows us to stay in $this context, calling protected methods the proper way
                $result = call_user_func_array(array($this, $target_method), $final_parameters);
                unset($method);
                if ($result !== false) {
                    return $this->pushSuccess($result,'success.');
                } else {

                    $message = 'there was an error';

                    if (isset($this->contextual_message)) {
                        $message = $this->contextual_message;
                    }
                    return $this->pushFailure($message);
                }
            } catch (Exception $e) {
                return $this->pushFailure('corresponding class method not found, exception: ' . $e);
            }
        } else {
            // not found in standard routing table
            return $this->response->pushResponse(
                404,$this->request_type,$this->action,
                $this->request,
                'unknown action'
            );
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

	/*
	 * CASHRequest response building
	 * Errors: return $this->error('404')->message("No settings of type `$type` found for this user.");
	 * Otherwise we need to work in responses so they return substantive data
	 */

	//TODO: need to put together a proper positive response chain, as well
    public function response($code) {
        $this->code = $code;

        return $this;
    }

    public function error($code) {
        $this->error_code = $code;

        return $this;
    }

    public function message($message) {
        $this->contextual_message = $message;
        CASHSystem::errorLog([$this->error_code => $message]); // only logs if in DEBUG mode

        return false;
    }
} // END class 
?>