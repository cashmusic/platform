<?php
/**
 * ElementPlant Takes an element ID finds it's settings, returns either raw data 
 * or markup ready to be used in the requesting app.
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
 * This file is generously sponsored by John 'Warthog9' Hawley
 *
 **/
class ElementPlant extends PlantBase {
	protected $elements_array=array();
	protected $typenames_array=array();
	
	public function __construct($request_type,$request) {
		$this->request_type = 'element';
		$this->routing_table = array(
			// alphabetical for ease of reading
			// first value  = target method to call
			// second value = allowed request methods (string or array of strings)
			'addelementtocampaign'      => array('addElementToCampaign','direct'),
			'addcampaign'               => array('addCampaign','direct'),
			'addelement'                => array('addElement','direct'),
			'addlockcode'               => array('addLockCode','direct'),
			'deletecampaign'            => array('deleteCampaign','direct'),
			'deleteelement'             => array('deleteElement','direct'),
			'editelement'               => array('editElement','direct'),
			'editcampaign'              => array('editCampaign','direct'),
			'getanalytics'              => array('getAnalytics','direct'),
			'getanalyticsforcampaign'   => array('getAnalyticsForCampaign','direct'),
			'getcampaign'               => array('getCampaign','direct'),
			'getelement'                => array('getElement','direct'),
			'getcampaignsforuser'       => array('getCampaignsForUser','direct'),
			'getcampaignforelement'     => array('getCampaignForElement','direct'),
			'getelementsforcampaign'    => array('getElementsForCampaign','direct'),
			'getelementsforuser'        => array('getElementsForUser','direct'),
			'getelementtemplate'        => array('getElementTemplate','direct'),
			//'getmarkup'            => array('getElementMarkup',array('direct','get','post','api_public','api_key','api_fullauth')),
			// closing up the above -> security risk allowing people to simply request markup and pass a status UID via 
			// API or GET. we'll need to require signed status codes and reopen...
			'getmarkup'                 => array('getElementMarkup','direct'),
			'getsupportedtypes'         => array('getSupportedTypes','direct'),
			'redeemcode'                => array('redeemLockCode',array('direct','get','post')),
			'removeelementfromcampaign' => array('removeElementFromCampaign','direct'),
			'setelementtemplate'        => array('setElementTemplate','direct')
		);
		$this->buildElementsArray();
		$this->plantPrep($request_type,$request);
	}
	
	/**
	 * Builds an associative array of all Element class files in /elements/
	 * stored as $this->elements_array and used to include proper markup in getElementMarkup()
	 *
	 * @return void
	 */protected function buildElementsArray() {
		$all_element_files = scandir(CASH_PLATFORM_ROOT.'/elements/',0);
		foreach ($all_element_files as $file) {
			if (substr($file,0,1) != "." && substr($file,-4) == '.php') {
				$tmpKey = strtolower(substr_replace($file, '', -4));
				$this->elements_array["$tmpKey"] = $file;
			}
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

	protected function getElement($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->getData(
			'elements',
			'id,name,type,user_id,template_id,options',
			$condition
		);
		if ($result) {
			$the_element = array(
				'id' => $result[0]['id'],
				'name' => $result[0]['name'],
				'type' => $result[0]['type'],
				'user_id' => $result[0]['user_id'],
				'template_id' => $result[0]['template_id'],
				'options' => json_decode($result[0]['options'],true)
			);
			return $the_element;
		} else {
			return false;
		}
	}

	protected function getElementTemplate($element_id,$return_template=false) {
		$element = $this->getElement($element_id);
		if ($element) {
			if (!$return_template) {
				return $element['template_id'];
			} else {
				if ($element['template_id']) {
					$template_request = new CASHRequest(
						array(
							'cash_request_type' => 'system', 
							'cash_action' => 'gettemplate',
							'template_id' => $element['template_id'],
							'all_details' => 1
						)
					);
					if ($template_request->response['payload']) {
						$template = $template_request->response['payload']['template'];
					} else {
						$template = @file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/embed.mustache');	
					}
				} else {
					$template = @file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/embed.mustache');
				}
				// zero or less means use our standard template, less than zero selects options
				if ($element['template_id'] == '-1') {
					$template = str_replace(array('!--light','light--'), '', $template);
					$template = str_replace('<body', '<body class="light"', $template);
				} else if ($element['template_id'] == '-2') {
					$template = str_replace(array('!--dark','dark--'), '', $template);
					$template = str_replace('<body', '<body class="dark"', $template);
				}
				return $template;
			}
		} else {
			return false;
		}
	}

	protected function setElementTemplate($element_id,$template_id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $element_id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->setData(
			'elements',
			array(
				'template_id' => $template_id
			),
			$condition
		);
		return $result;
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
		$return_array = array_keys($this->elements_array);
		$filter_array = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/elements/'.'unsupported.json'));
		if (is_array($filter_array)) {
			$return_array = array_diff($return_array,$filter_array);
		}
		return $return_array;
	}

	/**
	 * Records the basic access data to the elements analytics table
	 *
	 * @return boolean
	 */protected function recordAnalytics($id,$access_method,$access_action='getmarkup',$location=false,$access_data='') {
		// check settings first as they're already loaded in the environment
		$record_type = CASHSystem::getSystemSettings('analytics');
		if ($record_type == 'off') {
			return true;
		}

		if (!$location) {
			$location = CASHSystem::getCurrentURL();
		}

		// only count one asset + situation per session
		$recorded_elements = $this->sessionGet('recorded_elements');
		if (is_array($recorded_elements)) {
			if (in_array($id . $access_method . $location, $recorded_elements)) {
				// already recorded for this session. just return true.
				return true;
			} else {
				// didn't find a record of this asset. record it and move forward
				$recorded_elements[] = $id . $access_method . $location;
				$this->sessionSet('recorded_elements',$recorded_elements);	
			}
		} else {
			$this->sessionSet('recorded_elements',array($id . $access_method . $location));
		}

		// first the big record if needed
		if ($record_type == 'full' || !$record_type) {
			$ip_and_proxy = CASHSystem::getRemoteIP();
			$result = $this->db->setData(
				'elements_analytics',
				array(
					'element_id' => $id,
					'access_method' => $access_method,
					'access_location' => $location,
					'access_action' => $access_action,
					'access_data' => $access_data,
					'access_time' => time(),
					'client_ip' => $ip_and_proxy['ip'],
					'client_proxy' => $ip_and_proxy['proxy'],
					'cash_session_id' => $this->getSessionID()
				)
			);
		}
		// basic logging happens for full or basic
		if ($record_type == 'full' || $record_type == 'basic') {
			$condition = array(
				"element_id" => array(
					"condition" => "=",
					"value" => $id
				)
			);
			$current_result = $this->db->getData(
				'elements_analytics_basic',
				'*',
				$condition
			);
			if (is_array($current_result)) {
				$new_total = $current_result[0]['total'] +1;
				$data      = json_decode($current_result[0]['data'],true);
				if (isset($data['locations'][$location])) {
					$data['locations'][$location] = $data['locations'][$location] + 1;
				} else {
					$data['locations'][$location] = 1;
				}
				if (isset($data['methods'][$access_method])) {
					$data['methods'][$access_method] = $data['methods'][$access_method] + 1;
				} else {
					$data['methods'][$access_method] = 1;
				}
			} else {
				$new_total = 1;
				$data      = array(
					'locations' => array(
						$location => 1
					),
					'methods'   => array(
						$access_method => 1
					)
				);
				$condition = false;
			}
			$result = $this->db->setData(
				'elements_analytics_basic',
				array(
					'element_id' => $id,
					'data' => json_encode($data),
					'total' => $new_total
				),
				$condition
			);
		}
		
		return $result;
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
			case 'elementbasics':
				$result = $this->db->getData(
					'elements_analytics_basic',
					'*',
					array(
						"element_id" => array(
							"condition" => "=",
							"value" => $element_id
						)
					)
				);
				if ($result) {
					$data = json_decode($result[0]['data'],true);
					$data['total'] = $result[0]['total'];
					return $data;
				} else {
					return false;
				}
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

	protected function getElementMarkup($id,$status_uid,$original_request=false,$original_response=false,$access_method='direct',$location=false) {
		$element = $this->getElement($id);
		$element_type = $element['type'];
		$element_options = $element['options'];
		if ($element_type) {
			$for_include = CASH_PLATFORM_ROOT.'/elements/'.$this->elements_array[$element_type];
			if (file_exists($for_include)) {
				include_once($for_include);
				$element_object_type = substr_replace($this->elements_array[$element_type], '', -4);
				$element_object = new $element_object_type($id,$element,$status_uid,$original_request,$original_response);
				$this->recordAnalytics($id,$access_method,'getmarkup',$location);
				$markup = $element_object->getMarkup();
				$markup = '<div class="cashmusic element ' . $element_type . ' id-' . $id . '">' . $markup . '</div>';
				return $markup;
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
	
	protected function editElement($id,$name,$options_data,$user_id=false) {
		$options_data = json_encode($options_data);
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->setData(
			'elements',
			array(
				'name' => $name,
				'options' => $options_data,
			),
			$condition
		);
		return $result;
	}

	protected function deleteElement($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->deleteData(
			'elements',
			$condition
		);
		return $result;
	}

	/**
	 * Wrapper for system lock code call
	 *
	 * @param {integer} $element_id - the element for which you're adding the lock code
	 * @return string|false
	 */protected function addLockCode($element_id){
		$add_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlockcode',
				'scope_table_alias' => 'elements', 
				'scope_table_id' => $element_id
			)
		);
		return $add_request->response['payload'];
	}

	/**
	 * Wrapper for system lock code call
	 *
	 * @param {string} $code - the code
	 * @param {integer} $element_id - the element for which you're adding the lock code
	 * @return bool
	 */protected function redeemLockCode($code,$element_id) {
		$redeem_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'redeemlockcode',
				'code' => $code,
				'scope_table_alias' => 'elements', 
				'scope_table_id' => $element_id
			)
		);
		return $redeem_request->response['payload'];
	}

	/*
	 *
	 *
	 * CAMPAIGNS
	 * 
	 *
	 */

	protected function addCampaign($title,$description,$user_id,$elements='[]',$metadata='{}') {
		$final_options = array(
			'title' => $title,
			'description' => $description,
			'elements' => $elements,
			'metadata' => $metadata,
			'user_id' => $user_id
		);
		if (is_array($metadata)) {
			$final_options['metadata'] = json_encode($metadata);
		}
		if (is_array($elements)) {
			// array_values ensures a non-associative array. which we want.
			$final_options['elements'] = json_encode(array_values($elements));
		}
		$result = $this->db->setData(
			'elements_campaigns',
			$final_options
		);
		return $result;
	}
	
	protected function editCampaign($id,$user_id=false,$title=false,$description=false,$elements=false,$metadata=false,$template_id=false) {
		$final_edits = array_filter(
			array(
				'title' => $title,
				'description' => $description,
				'template_id' => $template_id
			),
			'CASHSystem::notExplicitFalse'
		);
		if (is_array($metadata)) {
			$final_edits['metadata'] = json_encode($metadata);
		}
		if (is_array($elements)) {
			// array_values ensures a non-associative array. which we want.
			$final_edits['elements'] = json_encode(array_values($elements));
		}
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->setData(
			'elements_campaigns',
			$final_edits,
			$condition
		);
		return $result;
	}

	protected function deleteCampaign($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->deleteData(
			'elements_campaigns',
			$condition
		);
		return $result;
	}

	protected function getCampaign($id,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->getData(
			'elements_campaigns',
			'*',
			$condition
		);
		if ($result) {
			$result[0]['metadata'] = json_decode($result[0]['metadata'],true);
			$result[0]['elements'] = json_decode($result[0]['elements'],true);
			return $result[0];
		} else {
			return false;
		}
	}

	protected function getCampaignsForUser($user_id) {
		$result = $this->db->getData(
			'elements_campaigns',
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

	protected function getElementsForCampaign($id) {
		$campaign = $this->getCampaign($id);
		$result = $this->db->getData(
			'elements',
			'*',
			array(
				"id" => array(
					"condition" => "IN",
					"value" => $campaign['elements']
				)
			)
		);
		foreach ($result as $key => &$val) {
			$val['options'] = json_decode($val['options'],true);
		}
		return $result;
	}

	protected function getAnalyticsForCampaign($id) {
		$campaign = $this->getCampaign($id);
		$result = $this->db->getData(
			'elements_analytics_basic',
			'MAX(total)',
			array(
				"element_id" => array(
					"condition" => "IN",
					"value" => $campaign['elements']
				)
			)
		);
		$returnarray = array(
			'total_views' => 0
		);
		if ($result) {
			$returnarray['total_views'] = $result[0]['MAX(total)'];
			return $returnarray;
		} else {
			return false;
		}
		return $result;
	}

	protected function getCampaignForElement($id) {
		$result = $this->db->getData(
			'ElementPlant_getCampaignForElement',
			false,
			array(
				"elements1" => array(
					"condition" => "LIKE",
					"value" => '["'.$id.'",%'
				),
				"elements2" => array(
					"condition" => "LIKE",
					"value" => '%,"'.$id.'",%'
				),
				"elements3" => array(
					"condition" => "LIKE",
					"value" => '%,"'.$id.'"]'
				),
				"elements4" => array(
					"condition" => "LIKE",
					"value" => '['.$id.',%'
				),
				"elements5" => array(
					"condition" => "LIKE",
					"value" => '%,'.$id.',%'
				),
				"elements6" => array(
					"condition" => "LIKE",
					"value" => '%,'.$id.']'
				)
			)
		);
		// 6 conditions is overkill, but wanted to make sure this would work if PHP treats the 
		// json_encode variables as strings OR ints (have only seen string handling)
		// 
		// i swear i'll never take regex for granted
		// PS: pattern matching across sqlite and mysql is hard. like stupid hard. 
		// like no thank you. No REGEXP, no GLOB, and CONCAT versus || issues.
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	protected function addElementToCampaign($element_id,$campaign_id) {
		$campaign = $this->getCampaign($campaign_id);
		if(($key = array_search($element_id, $campaign['elements'])) === false) {
			$campaign['elements'][] = $element_id;
		}
		return $this->editCampaign($campaign_id,false,false,false,$campaign['elements']);
	}

	protected function removeElementFromCampaign($element_id,$campaign_id) {
		$campaign = $this->getCampaign($campaign_id);
		if(($key = array_search($element_id, $campaign['elements'])) !== false) {
			unset($campaign['elements'][$key]);
		}
		return $this->editCampaign($campaign_id,false,false,false,$campaign['elements']);
	}

} // END class 
?>