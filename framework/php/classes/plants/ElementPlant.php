<?php
/**
 * ElementPlant Takes an element ID finds it's settings, returns either raw data 
 * or markup ready to be used in the requesting app.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class ElementPlant extends PlantBase {
	protected $elements_array=array();
	protected $typenames_array=array();
	// hard-coded to avoid 0/o, l/1 type confusions on download cards
	protected $lock_code_chars = array(
		'all_chars' => array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z'),
		'code_break' => array(2,3,3,4,4,4,5)
	);
	
	public function __construct($request_type,$request) {
		$this->request_type = 'element';
		$this->plantPrep($request_type,$request);
		$this->buildElementsArray();
	}
	
	public function processRequest() {
		if ($this->action) {
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addelement'           => array('addElement','direct'),
				'addlockcode'          => array('addLockCode','direct'),
				'deleteelement'        => array('deleteElement','direct'),
				'editelement'          => array('editElement','direct'),
				'getanalytics'         => array('getAnalytics','direct'),
				'getelement'           => array('getElement','direct'),
				'getelementsforuser'   => array('getElementsForUser','direct'),
				'getmarkup'            => array('getElementMarkup',array('direct','get','post','api_public','api_key','api_fullauth')),
				'getsupportedtypes'    => array('getSupportedTypes','direct'),
				'redeemcode'           => array('redeemLockCode',array('direct','get','post'))
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				switch ($this->action) {
					default:
						return $this->response->pushResponse(
							400,$this->request_type,$this->action,
							$this->request,
							'unknown action'
						);
				}
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				$this->request,
				'no action specified'
			);
		}
	}
	
	/**
	 * Builds an associative array of all Element class files in /elements/
	 * stored as $this->elements_array and used to include proper markup in getElementMarkup()
	 *
	 * @return void
	 */protected function buildElementsArray() {
		if ($elements_dir = opendir(CASH_PLATFORM_ROOT.'/elements/')) {
			while (false !== ($file = readdir($elements_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmpKey = strtolower(substr_replace($file, '', -4));
					$this->elements_array["$tmpKey"] = $file;
				}
			}
			closedir($elements_dir);
		}
	}

	protected function buildTypeNamesArray() {
		if ($elements_dir = opendir(CASH_PLATFORM_ROOT.'/elements/')) {
			while (false !== ($file = readdir($elements_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$element_object_type = substr_replace($file, '', -4);
					$tmpKey = strtolower($element_object_type);
					include(CASH_PLATFORM_ROOT.'/elements/'.$file);
					
					// Would rather do this with $element_object_type::type but that requires 5.3.0+
					// Any ideas?
					$this->typenames_array["$tmpKey"] = constant($element_object_type . '::name');
				}
			}
			closedir($elements_dir);
		}
	}

	protected function getElement($id) {
		$result = $this->db->getData(
			'elements',
			'id,name,type,user_id,options',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			)
		);
		if ($result) {
			$the_element = array(
				'id' => $result[0]['id'],
				'name' => $result[0]['name'],
				'type' => $result[0]['type'],
				'user_id' => $result[0]['user_id'],
				'options' => json_decode($result[0]['options'],true)
			);
			return $the_element;
		} else {
			return false;
		}
	}
	
	protected function getElementsForUser($user_id) {
		$result = $this->db->getData(
			'elements',
			'*',
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				)
			)
		);
		return $result;
	}

	protected function getSupportedTypes() {
		return array_keys($this->elements_array);
	}

	/**
	 * Records the basic access data to the elements analytics table
	 *
	 * @return boolean
	 */protected function recordAnalytics($id,$access_method,$access_action='getmarkup',$access_data='') {
		$ip_and_proxy = CASHSystem::getRemoteIP();
		$already_recorded = false;
		// first check and see if we've recorded this session and circumstance yet
		// only do this for empty lock_method_table queries so we don't repeat
		// unnecessary rows and overwhelm the table
		if ($access_action == 'getmarkup') {
			$already_recorded = $this->db->getData(
				'elements_analytics',
				'id',
				array(
					"element_id" => array(
						"condition" => "=",
						"value" => $id
					),
					"access_method" => array(
						"condition" => "=",
						"value" => $access_method
					),
					"access_location" => array(
						"condition" => "=",
						"value" => CASHSystem::getCurrentURL()
					),
					"cash_session_id" => array(
						"condition" => "=",
						"value" => $this->getCASHSessionID()
					),
					"client_ip" => array(
						"condition" => "=",
						"value" => $ip_and_proxy['ip']
					),
					"client_proxy" => array(
						"condition" => "=",
						"value" => $ip_and_proxy['proxy']
					)
				)
			);
		}
		if (!$already_recorded) {
			$result = $this->db->setData(
				'elements_analytics',
				array(
					'element_id' => $id,
					'access_method' => $access_method,
					'access_location' => CASHSystem::getCurrentURL(),
					'access_action' => $access_action,
					'access_data' => $access_data,
					'access_time' => time(),
					'client_ip' => $ip_and_proxy['ip'],
					'client_proxy' => $ip_and_proxy['proxy'],
					'cash_session_id' => $this->getCASHSessionID()
				)
			);
			return $result;
		} else {
			return true;
		}
	}

	/**
	 * Pulls analytics queries in a few different formats
	 *
	 * @return array
	 */protected function getAnalytics($analtyics_type,$user_id,$element_id=0) {
		switch (strtolower($analtyics_type)) {
			case 'mostactive':
				$result = $this->db->getData(
					'ElementPlant_getAnalytics_mostactive',
					false,
					array(
						"user_id" => array(
							"condition" => "=",
							"value" => $user_id
						)
					)
				);
				return $result;
				break;
			case 'elementbylocation':
				$result = $this->db->getData(
					'ElementPlant_getAnalytics_elementbylocation',
					false,
					array(
						"element_id" => array(
							"condition" => "=",
							"value" => $element_id
						)
					)
				);
				return $result;
				break;
			case 'elementbymethod':
				$result = $this->db->getData(
					'ElementPlant_getAnalytics_elementbymethod',
					false,
					array(
						"element_id" => array(
							"condition" => "=",
							"value" => $element_id
						)
					)
				);
				return $result;
				break;
			case 'recentlyadded':
				$result = $this->db->getData(
					'elements',
					'*',
					array(
						"user_id" => array(
							"condition" => "=",
							"value" => $user_id
						)
					),
					false,
					'creation_date DESC'
				);
				return $result;
				break;
		}
	}

	protected function getElementMarkup($id,$status_uid,$original_request=false,$original_response=false,$access_method='direct') {
		$element = $this->getElement($id);
		$element_type = $element['type'];
		$element_options = $element['options'];
		if ($element_type) {
			$for_include = CASH_PLATFORM_ROOT.'/elements/'.$this->elements_array[$element_type];
			if (file_exists($for_include)) {
				include_once($for_include);
				$element_object_type = substr_replace($this->elements_array[$element_type], '', -4);
				$element_object = new $element_object_type($id,$element,$status_uid,$original_request,$original_response);
				$this->recordAnalytics($id,$access_method);
				return $element_object->getMarkup();
			}
		} else {
			return false;
		}
	}

	protected function addElement($name,$type,$options_data,$user_id) {
		$options_data = json_encode($options_data);
		$result = $this->db->setData(
			'elements',
			array(
				'name' => $name,
				'type' => $type,
				'options' => $options_data,
				'user_id' => $user_id
			)
		);
		return $result;
	}
	
	protected function editElement($id,$name,$options_data) {
		$options_data = json_encode($options_data);
		$result = $this->db->setData(
			'elements',
			array(
				'name' => $name,
				'options' => $options_data,
			),
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}

	protected function deleteElement($id) {
		$result = $this->db->deleteData(
			'elements',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		return $result;
	}
	
	/*
	 *
	 * Here lie a bunch of lock code functions that need to reference elements
	 * instead of assets. duh.
	 *
	 */
	
	/**
	 * Retrieves the last known UID or if none are found creates and returns a 
	 * random UID as a starting point
	 *
	 * @return string
	 */protected function getLastLockCode() {
		$result = $this->db->getData(
			'lock_codes',
			'uid',
			false,
			1,
			'id DESC'
		);
		if ($result) {
			$code = $result[0]['uid'];
		} else {
			$code = false;
		}
		return $code;
	}

	/**
	 * Creates a new lock/unlock code for and asset
	 *
	 * @param {integer} $element_id - the element for which you're adding the lock code
	 * @return string|false
	 */protected function addLockCode($element_id){
		$code = $this->generateCode(
			$this->lock_code_chars['all_chars'],
			$this->lock_code_chars['code_break'],
			$this->getLastLockCode()
		);
		$result = $this->db->setData(
			'lock_codes',
			array(
				'uid' => $code,
				'element_id' => $element_id
			)
		);
		if ($result) { 
			return $code;
		} else {
			return false;
		}
	}

	protected function redeemLockCode($code,$element_id) {
		$code_details = $this->getLockCode($code,$element_id);
		if ($code_details) {
			// details found, means the code+element is correct...mark as claimed
			if (!$code_details['claim_date']) {
				$result = $this->db->setData(
					'lock_codes',
					array(
						'claim_date' => time()
					),
					array(
						"id" => array(
							"condition" => "=",
							"value" => $code_details['id']
						)
					)
				);
				return $result;
			} else {
				// allow retries for four hours after claim
				if (($code_details['claim_date'] + 14400) > time()) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	protected function getLockCode($code,$element_id) {
		$result = $this->db->getData(
			'lock_codes',
			'*',
			array(
				"uid" => array(
					"condition" => "=",
					"value" => $code
				),
				"element_id" => array(
					"condition" => "=",
					"value" => $element_id
				)
			),
			1
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	protected function consistentShuffle(&$items, $seed=false) {
		// original here: http://www.php.net/manual/en/function.shuffle.php#105931
		$original = md5(serialize($items));
		mt_srand(crc32(($seed) ? $seed : $items[0]));
		for ($i = count($items) - 1; $i > 0; $i--){
			$j = @mt_rand(0, $i);
			list($items[$i], $items[$j]) = array($items[$j], $items[$i]);
		}
		if ($original == md5(serialize($items))) {
			list($items[count($items) - 1], $items[0]) = array($items[0], $items[count($items) - 1]);
		}
	}
	
	protected function generateCode($all_chars,$code_break,$last_code=false) {
		$seed = CASHSystem::getSystemSalt();
		$this->consistentShuffle($all_chars,$seed);
		$this->consistentShuffle($code_break,$seed);
		if (!$last_code) {
			$last_code = '';
			for ($i = 1; $i <= 10; $i++) {
				$last_code .= $all_chars[rand(0,count($all_chars) - 1)];
			}
		}
		$sequential = substr($last_code,1,$code_break[0])
					. substr($last_code,0 - (7 - $code_break[0]));
		$sequential = $this->iterateChars($sequential,$all_chars);
		$new_code = $all_chars[rand(0,count($all_chars) - 1)]
		 		  . substr($sequential,0,$code_break[0])
				  . $all_chars[rand(0,count($all_chars) - 1)]
				  . $all_chars[rand(0,count($all_chars) - 1)]
				  . substr($sequential,0 - (7 - $code_break[0]));
		return $new_code;
	}

	protected function iterateChars($chars,$all_chars) {
		$chars = str_split($chars);
		// start with the last character of the $chars string
		$current_char = count($chars) - 1;
		$loop = 1;
		do {
			$loop--;
			$current_key = array_search($chars[$current_char],$all_chars);
			if ($current_key == count($all_chars) - 1) {
				$loop++;
				$chars[$current_char] = $all_chars[0];
				if ($current_char == 0) {
					$current_char = count($chars) - 1;
				} else {
					$current_char--;
				}
			} else {
				$chars[$current_char] = $all_chars[$current_key + 1];
			}
		} while ($loop > 0);
		$chars = implode($chars);
		return $chars;
	}

} // END class 
?>