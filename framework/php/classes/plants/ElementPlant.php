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
	protected $lockCodeCharacters = array(
		// hard-coded to avoid 0/o, l/1 type confusions on download cards
		'num_chars' => array('2','3','4','6','7','8','9'),
		'txt_chars' => array('a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z'),
		'all_chars' => array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z')
	);
	
	public function __construct($request_type,$request) {
		$this->request_type = 'element';
		$this->plantPrep($request_type,$request);
		$this->buildElementsArray();
	}
	
	public function processRequest() {
		if ($this->action) {
			switch ($this->action) {
				case 'addelement':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('name','type','options_data','user_id')) return $this->sessionGetLastResponse();
					$result = $this->addElement($this->request['name'],$this->request['type'],$this->request['options_data'],$this->request['user_id']);
					if ($result) {
						return $this->pushSuccess(array('id' => $result),'success. element id included in payload');
					} else {
						return $this->pushFailure('there was an error adding the element');
					}
					break;
				case 'editelement':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('id','name','options_data')) return $this->sessionGetLastResponse();
					$result = $this->editElement($this->request['id'],$this->request['name'],$this->request['options_data']);
					if ($result) {
						return $this->pushSuccess($this->getElement($result),'success. element included in payload');
					} else {
						return $this->pushFailure('there was an error editing the element');
					}
					break;
				case 'getelement':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('id')) return $this->sessionGetLastResponse();
						$result = $this->getElement($this->request['id']);
						if ($result) {
							return $this->pushSuccess($result,'success. element included in payload');
						} else {
							return $this->pushFailure('there was an error retrieving the element');
						}
					break;
				case 'deleteelement':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('id')) return $this->sessionGetLastResponse();
						$result = $this->deleteElement($this->request['id']);
						if ($result) {
							return $this->pushSuccess($result,'success. deleted');
						} else {
							return $this->pushFailure('there was an error deleting the element');
						}
					break;
				case 'getelementsforuser':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('user_id')) return $this->sessionGetLastResponse();
						$result = $this->getElementsForUser($this->request['user_id']);
						if ($result) {
							return $this->pushSuccess($result,'success. element(s) array included in payload');
						} else {
							return $this->pushFailure('no elements were found or there was an error retrieving the elements');
						}
					break;
				case 'getanalytics':
					if (!$this->requireParameters('analtyics_type','user_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getAnalytics($this->request['analtyics_type'],$this->request['user_id']);
					if ($result) {
						return $this->pushSuccess($result,'asset list in payload');
					} else {
						return $this->pushFailure('there was an error getting asset details');
					}
					break;
				case 'getmarkup':
					if (!$this->checkRequestMethodFor('direct','api_key','api_public')) return $this->sessionGetLastResponse();
					if (!$this->requireParameters('id')) return $this->sessionGetLastResponse();
					$result = $this->getElementMarkup($this->request['id'],$this->request['status_uid']);
					if ($result) {
						return $this->pushSuccess($result,'success. markup in the payload');
					} else {
						return $this->pushFailure('markup not found');
					}
					break;
				case 'getsupportedtypes':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$result = $this->getSupportedTypes();
					if ($result) {
						return $this->pushSuccess($result,'success. types array in the payload');
					} else {
						return $this->pushFailure('there was a problem getting the array');
					}
					break;
				case 'addlockcode':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('asset_id')) { return $this->sessionGetLastResponse(); }
					$new_code = $this->addLockCode($this->request['asset_id']);
					if ($new_code) {
						return $this->pushSuccess(array('code' => $new_code),'code added successfully');
					} else {
						return $this->pushFailure('there was an error adding the code');
					}
					break;
				case 'unlock':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('asset_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->unlockAsset($this->request['asset_id']);
					if ($result) {
						return $this->pushSuccess(true,'asset unlocked successfully');
					} else {
						return $this->pushFailure('there was an error unlocking the asset');
					}
					break;
				default:
					return $this->response->pushResponse(
						400,$this->request_type,$this->action,
						$this->request,
						'unknown action'
					);
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
	 * Builds an associative array of all Element class files in /classes/elements/
	 * stored as $this->elements_array and used to include proper markup in getElementMarkup()
	 *
	 * @return void
	 */protected function buildElementsArray() {
		if ($elements_dir = opendir(CASH_PLATFORM_ROOT.'/classes/elements/')) {
			while (false !== ($file = readdir($elements_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmpKey = strtolower(substr_replace($file, '', -4));
					$this->elements_array["$tmpKey"] = $file;
				}
			}
			closedir($elements_dir);
		}
	}

	public function buildTypeNamesArray() {
		if ($elements_dir = opendir(CASH_PLATFORM_ROOT.'/classes/elements/')) {
			while (false !== ($file = readdir($elements_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$element_object_type = substr_replace($file, '', -4);
					$tmpKey = strtolower($element_object_type);
					include(CASH_PLATFORM_ROOT.'/classes/elements/'.$file);
					
					// Would rather do this with $element_object_type::type but that requires 5.3.0+
					// Any ideas?
					$this->typenames_array["$tmpKey"] = constant($element_object_type . '::name');
				}
			}
			closedir($elements_dir);
		}
	}

	public function getElement($element_id) {
		$result = $this->db->getData(
			'elements',
			'id,name,type,user_id,options',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $element_id
				)
			)
		);
		if ($result) {
			$the_element = array(
				'id' => $result[0]['id'],
				'name' => $result[0]['name'],
				'type' => $result[0]['type'],
				'user_id' => $result[0]['user_id'],
				'options' => json_decode($result[0]['options'])
			);
			return $the_element;
		} else {
			return false;
		}
	}
	
	public function getElementsForUser($user_id) {
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

	public function getSupportedTypes() {
		return array_keys($this->elements_array);
	}

	/**
	 * Records the basic access data to the elements analytics table
	 *
	 * @return boolean
	 */protected function recordAnalytics($element_id,$access_method,$access_action='getmarkup',$access_data='') {
		$ip_and_proxy = CASHSystem::getCurrentIP();
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
						"value" => $element_id
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
					'element_id' => $element_id,
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
	 */protected function getAnalytics($analtyics_type,$user_id) {
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

	public function getElementMarkup($element_id,$status_uid,$access_method='direct') {
		$element = $this->getElement($element_id);
		$element_type = $element['type'];
		$element_options = $element['options'];
		if ($element_type) {
			$for_include = CASH_PLATFORM_ROOT.'/classes/elements/'.$this->elements_array[$element_type];
			if (file_exists($for_include)) {
				include($for_include);
				$element_object_type = substr_replace($this->elements_array[$element_type], '', -4);
				$element_object = new $element_object_type($element_id,$element,$status_uid);
				$this->recordAnalytics($element_id,$access_method);
				return $element_object->getMarkup();
			}
		} else {
			return false;
		}
	}

	public function addElement($name,$type,$options_data,$user_id) {
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
	
	public function editElement($element_id,$name,$options_data) {
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
					'value' => $element_id
				)
			)
		);
		return $result;
	}

	public function deleteElement($element_id) {
		$result = $this->db->deleteData(
			'elements',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $element_id
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
	 */protected function getLastLockCodeUID() {
		$result = $this->db->getData(
			'lock_codes',
			'uid',
			array(
				"list_id" => array(
					"condition" => "=",
					"value" => $list_id
				)
			),
			1,
			'creation_date DESC'
		);
		if ($result) {
			return $result[0]['uid'];
		} else {
			$num_chars = $this->lockCodeCharacters['num_chars'];
			$txt_chars = $this->lockCodeCharacters['txt_chars'];
			$all_chars = $this->lockCodeCharacters['all_chars'];
			$char_count_num = count($num_chars)-1;
			$char_count_txt = count($txt_chars)-1;
			$char_count_all = count($all_chars)-1;

			$firstUID = $all_chars[rand(0,$char_count_all)];
			$firstUID .= $all_chars[rand(0,$char_count_all)];
			$firstUID .= $num_chars[rand(0,$char_count_num)];
			$firstUID .= $all_chars[rand(0,$char_count_all)];
			$firstUID .= $txt_chars[rand(0,$char_count_txt)];
			$firstUID .= $all_chars[rand(0,$char_count_all)];
			$firstUID .= $all_chars[rand(0,$char_count_all)];
			$firstUID .= $num_chars[rand(0,$char_count_num)];
			$firstUID .= $txt_chars[rand(0,$char_count_txt)];
			$firstUID .= $all_chars[rand(0,$char_count_all)];

			return $firstUID;
		}
	}

	/**
	 * Increments through an array based on $inc_by, wrapping at the end
	 *
	 * @param {integer} $current -  the current position in the array
	 * @param {integer} $inc_by - the increment amount	
	 * @param {integer} $total - the total number of members in the array
	 * @return string|false
	 */protected function lockCodeUIDWrapInc($current,$inc_by,$total) {
		if (($current+$inc_by) < ($total)) {
			$final_value = $current+$inc_by;
		} else {
			$final_value = ($current-$total)+$inc_by;
		}
		return $final_value;
	}

	/**
	 * Decrements through an array based on $dec_by, wrapping at the end
	 *
	 * @param {integer} $current -  the current position in the array
	 * @param {integer} $dec_by - the decrement amount	
	 * @param {integer} $total - the total number of members in the array
	 * @return string|false
	 */protected function lockCodeUIDWrapDec($current,$dec_by,$total) {
		if (($current-$dec_by) > -1) {
			$final_value = $current-$dec_by;
		} else {
			$final_value = ($total+$current) - $dec_by;
		}
		return $final_value;
	}

	public function verifyUniqueLockCodeUID($lookup_uid) {
		$result = $this->db->getData(
			'lock_codes',
			'uid',
			array(
				"uid" => array(
					"condition" => "=",
					"value" => $lookup_uid
				)
			),
			1
		);
		// backwards return. if we find results, return false for not unique.
		// if there are none return true. broke. yer. mindz.
		if ($result) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Creates a new lock/unlock code for and asset
	 *
	 * @param {integer} $asset_id - the asset for which you're adding the lock code
	 * @return string|false
	 */protected function addLockCode($asset_id){
		$uid = $this->getNextLockCodeUID();
		if ($uid) {
			$result = $this->db->setData(
				'lock_codes',
				array(
					'uid' => $uid,
					'asset_id' => $asset_id
				)
			);
			if ($result) { 
				return $uid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets the last UID and computes the next in sequence
	 *
	 * @return string
	 */protected function getNextComputedLockCodeUID() {
		// no 1,l,0,or o to avoid confusion...
		$num_chars = $this->lockCodeCharacters['num_chars'];
		$txt_chars = $this->lockCodeCharacters['txt_chars'];
		$all_chars = $this->lockCodeCharacters['all_chars'];
		$last_uid = $this->getLastLockCodeUID();
		$exploded_last_uid = str_split($last_uid);
		$char_count_num = count($num_chars)-1;
		$char_count_txt = count($txt_chars)-1;
		$char_count_all = count($all_chars)-1;
		
		$next_uid = $all_chars[rand(0,$char_count_all)];
		if ($exploded_last_uid[1] == $num_chars[3]) {
			$next_uid .= $all_chars[$this->lockCodeUIDWrapInc(array_search($exploded_last_uid[1],$all_chars),1,$char_count_all)];
		} else {
			$next_uid .= $exploded_last_uid[1];
		}
		$next_uid .= $num_chars[$this->lockCodeUIDWrapDec(array_search($exploded_last_uid[2],$num_chars),rand(1,3),$char_count_num)];
		$next_uid .= $all_chars[$this->lockCodeUIDWrapInc(array_search($exploded_last_uid[3],$all_chars),5,$char_count_all)];
		$next_uid .= $txt_chars[$this->lockCodeUIDWrapDec(array_search($exploded_last_uid[4],$txt_chars),rand(1,3),$char_count_txt)];
		$next_uid .= $all_chars[$this->lockCodeUIDWrapInc(array_search($exploded_last_uid[5],$all_chars),11,$char_count_all)];
		if ($exploded_last_uid[0] == $all_chars[0]) {
			$next_uid .=  $all_chars[$this->lockCodeUIDWrapDec(array_search($exploded_last_uid[6],$all_chars),1,$char_count_all)];
		} else {
			$next_uid .= $exploded_last_uid[6];
		}
		$next_uid .= $num_chars[$this->lockCodeUIDWrapDec(array_search($exploded_last_uid[7],$num_chars),3,$char_count_num)];
		$next_uid .= $txt_chars[$this->lockCodeUIDWrapInc(array_search($exploded_last_uid[8],$all_chars),1,$char_count_txt)];
		$next_uid .= $all_chars[rand(0,$char_count_all)];
		
		return $next_uid;
	}
	
	/**
	 * Calls getNextComputedLockCodeUID and ensures the result is unique
	 *
	 * @return string
	 */protected function getNextLockCodeUID() {
		$next_uid = $this->getNextComputedLockCodeUID();
		$this->verifyUniqueLockCodeUID($next_uid);
		while (!$this->verifyUniqueLockCodeUID($next_uid)) {
			$next_uid = $this->getNextComputedLockCodeUID();
		}
		return $next_uid;
	}

	public function getLockCodeDetails($uid,$asset_id) {
		return $this->db->doQueryForAssoc($query);
		$result = $this->db->getData(
			'lock_codes',
			'*',
			array(
				"uid" => array(
					"condition" => "=",
					"value" => $lookup_uid
				),
				"asset_id" => array(
					"condition" => "=",
					"value" => $asset_id
				)
			),
			1
		);
		return $result[0];
	}

	public function parseLockCode($code) {
		return array(
			'id' => substr($code,0,(strlen($code)-10)),
			'uid' => substr($code,-10)
		);
	}

	public function verifyLockCode($code,$email=false) {
		$identifier = $this->parseLockCode($code);
		$result = $this->getLockCodeDetails($identifier['uid'],$identifier['id']);
		if ($result !== false) {
			if (!$email) {
				if ($result['expired'] == 1) {
					return false;
				} else {
					return true;
				}
			} else {
				// email is required, yo
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Adds an unlock state to cash session persistent store
	 *
	 * @return boolean
	 */protected function unlockAsset($asset_id) {
		$current_unlocked_assets = $this->sessionGetPersistent('unlocked_assets');
		if (is_array($current_unlocked_assets)) {
			$current_unlocked_assets[""."$asset_id"]=true;
			$this->sessionSetPersistent('unlocked_assets',$current_unlocked_assets);
			return true;
		} else {
			$this->sessionSetPersistent('unlocked_assets',array(""."$asset_id" => true));
			return true;
		}
		return false;
	}

	/**
	 * Returns true if an assetIsUnlocked, false if not
	 *
	 * @return boolean
	 */protected function getUnlockedStatus($asset_id) {
		if ($this->getPublicStatus($asset_id)) {
			return true;
		}
		$current_unlocked_assets = $this->sessionGetPersistent('unlocked_assets');
		if (is_array($current_unlocked_assets)) {
			if (array_key_exists(""."$asset_id",$current_unlocked_assets)) {
				if ($current_unlocked_assets[""."$asset_id"] === true) {
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

} // END class 
?>