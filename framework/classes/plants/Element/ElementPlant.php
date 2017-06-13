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

namespace CASHMusic\Plants\Element;

use CASHMusic\Core\CASHConnection;
use CASHMusic\Core\CASHDBAL;
use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Entities\Element;
use CASHMusic\Entities\ElementAnalytic;
use CASHMusic\Entities\ElementAnalyticBasic;
use CASHMusic\Entities\People;

class ElementPlant extends PlantBase {
	protected $elements_array=array();
	protected $typenames_array=array();

	public function __construct($request_type,$request) {
		$this->request_type = 'element';
        $this->getRoutingTable();

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
			if (ctype_alnum($file)) {
				$tmpKey = strtolower($file);
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

	/**
	 * Feed in a user id and element type (string) and this function returns either
	 * true, meaning the user has defined all the required bits needed to set up an
	 * element of the type; or an array containing codes for what's missing.
	 *
	 * A boolean return of false means there was an error reading the JSON
	 *
	 * @param {int} $user_id 		 - the user
	 * @param {string} $element_type - element type name
	 * @return true|false|array
	 */ protected function checkUserRequirements($user_id,$element_type) {
		$json_location = CASH_PLATFORM_ROOT.'/elements/' . $element_type . '/app.json';
		$app_json = false;
		if (file_exists($json_location)) {
			$app_json = json_decode(file_get_contents($json_location),true);
		}
		if ($app_json) {
			$failures = array();
			foreach ($app_json['options'] as $section_name => $details) {
				foreach ($details['data'] as $data => $values) {
					if (isset($values['required'])) {
						if ($values['type'] == 'select' && $values['required'] == true) {
							if (is_string($values['values'])) {
								if (substr($values['values'],0,7) == 'connect') {
									$scope = explode('/',$values['values']);
									// get system settings:
									$data_object = new CASHConnection($user_id);
									if (!$data_object->getConnectionsByScope($scope[1])) {
										$failures[] = $values['values'];
									}
								} else {
									$action_name = false;
									switch ($values['values']) {
										case 'assets':
											$plant_name = 'asset';
											$action_name = 'getassetsforuser';
											break;
										case 'people/lists':
											$plant_name = 'people';
											$action_name = 'getlistsforuser';
											break;
										case 'items':
										case 'commerce/items':
											$plant_name = 'commerce';
											$action_name = 'getitemsforuser';
											break;
									}
									if ($action_name) {
										$requirements_request = new CASHRequest(
											array(
												'cash_request_type' => $plant_name,
												'cash_action' => $action_name,
												'user_id' => $user_id,
												'parent_id' => 0
											)
										);

										if (!$requirements_request->response['payload']) {
											$failures[] = $values['values'];
										}
									}
								}
							}
						}
					}
				}
			}
			if (count($failures) == 0) {
				return true;
			} else {
				return $failures;
			}
		} else {
			return false;
		}
	}

	protected function getElement($id,$user_id=false) {

		if ($user_id) {
            $element = Element::findWhere(['id'=>$id, 'user_id'=>$user_id]);
		} else {
			$element = Element::find($id);
		}

		if ($element) {

			if (is_array($element)) $element = $element[0];

			// CONVERT METADATA STORAGE TYPE OPTIONS (longer posts, generally)
			$allmetadata = $this->getAllMetaData("elements", $id);

			// convert this to an array to work with current structure
            $element = $element->toArray();
			if (is_array($allmetadata)) {
				// Loop through $this->options and turn metadata values into real values
				foreach ($element['options'] as $name => $value) {
					// now check all metadata, one at a time
					foreach ($allmetadata as $dataname => $data) {
						// an array means it's a scalar, loop through
						if (is_array($value)) {
							foreach ($value as $count => $item) {
								// first loop just gives us the count/array key
								foreach ($item as $suboptionname => $suboption) {
									// now we loop through every sub-option in the scalar
									if ($dataname == $suboptionname . '-clone-' . $name . '-' . $count) {
										// found a match so overwrite
										$the_element['options'][$name][$count][$suboptionname] = $data;
									}
								}
							}
						} else {
							if ($dataname == $name) {
								$the_element['options'][$name] = $data;
							}
						}
					}
				}
			}

			return $element;
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
				// set up our default styles in the template:
				$styles  = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$styles .= '<link rel="icon" type="image/x-icon" href="'.CASH_ADMIN_URL.'/ui/default/assets/images/favicon.png" />';
				$styles .= '<link href="//fonts.googleapis.com/css?family=Montserrat:400,700|Nunito:300,400,700" rel="stylesheet" type="text/css">';
				$styles .= '<link rel="stylesheet" type="text/css" href="'.CASH_ADMIN_URL.'/assets/css/embed-default.css" />';

				// zero or less means use our standard template, less than zero selects options
				if ($element['template_id'] == '-1') {
					$styles .= '<link rel="stylesheet" type="text/css" href="'.CASH_ADMIN_URL.'/assets/css/embed-light.css" />';
				} else if ($element['template_id'] == '-2') {
					$styles .= '<link rel="stylesheet" type="text/css" href="'.CASH_ADMIN_URL.'/assets/css/embed-dark.css" />';
				}

				$template = str_replace('<head>', "<head>\n".$styles."\n", $template);

				return $template;
			}
		} else {
			return false;
		}
	}

	protected function setElementTemplate($element_id,$template_id,$user_id=false) {

        try {
            if ($user_id) {
                $element = Element::findWhere(['id'=>$element_id, 'user_id'=>$user_id]);
            } else {
                $element = Element::find($element_id);
            }

            $element->template_id = $template_id;
            $element->save();

            return $element->toArray();

		} catch (\Exception $e) {
			if (CASH_DEBUG) {
				CASHSystem::errorLog($e->getMessage());
			}
        	return false;
		}
	}

	protected function getElementsForUser($user_id) {
        try {
            $elements = Element::findWhere(['user_id'=>$user_id]);
        } catch (\Exception $e) {
            if (CASH_DEBUG) {
                CASHSystem::errorLog($e->getMessage());
            }
            return false;
        }

        return $elements;
	}

	protected function getSupportedTypes($force_all=false) {
		$return_array = array_values($this->elements_array);

		$filter_array = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/elements/supported.json'),true);
		if (is_array($filter_array['public']) && !$force_all) {
			$allowed_types = $filter_array['public'];
			if (defined('SHOW_BETA')) {
				if (SHOW_BETA) {
					if (is_array($filter_array['beta']) && !$force_all) {
						$allowed_types = array_merge($filter_array['public'],$filter_array['beta']);
					}
				}
			}
			$return_array = array_intersect($return_array,$allowed_types);
		}
		return $return_array;
	}

	/**
	 * Records the basic access data to the elements analytics table
	 *
	 * @return array|boolean
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

			$result = ElementAnalytic::create([
                'element_id' => $id,
                'access_method' => $access_method,
                'access_location' => $location,
                'access_action' => $access_action,
                'access_data' => json_encode($access_data),
                'access_time' => time(),
                'client_ip' => $ip_and_proxy['ip'],
                'client_proxy' => $ip_and_proxy['proxy'],
                'cash_session_id' => $this->getSessionID()
            ]);
		}
		// basic logging happens for full or basic
		if ($record_type == 'full' || $record_type == 'basic') {
			$result = ElementAnalyticBasic::findWhere(['element_id'=>$id]);

			$short_geo = false;
			if (is_array($access_data)) {
				if (isset($access_data['geo'])) {
					$short_geo = $access_data['geo']['city'] . ', ' . $access_data['geo']['region'] . ' / ' . $access_data['geo']['countrycode'];
				}
			}

			if ($result) {
				$new_total = $result[0]['total'] +1;
				$data      = json_decode($result[0]['data'],true);
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
				if (isset($data['geo'][$short_geo])) {
					$data['geo'][$short_geo] = $data['geo'][$short_geo] + 1;
				} else {
					$data['geo'][$short_geo] = 1;
				}
			} else {
				$new_total = 1;
				$data      = array(
					'locations'  => array(
						$location => 1
					),
					'methods'    => array(
						$access_method => 1
					),
					'geo'        => array(
						$short_geo => 1
					)
				);
				$condition = false;
			}

			$result->total = $new_total;
			$result->data = $data;
			$result->save();

            return $result->toArray();
		}


	}

	/**
	 * Pulls analytics queries in a few different formats
	 *
	 * @return array
	 */
	protected function getAnalytics($analtyics_type,$user_id,$element_id=0) {



		switch (strtolower($analtyics_type)) {
			case 'mostactive':

				$query = CASHDBAL::queryBuilder();

					$query->select(
						["analytics",
							"count(analytics.id) as total",
						    "elements.name as name"]
					)
					->from("CASHMusic:ElementAnalytic", "analytics")
					->join("CASHMusic:Element", "elements", "WITH", 'analytics.element_id = elements.id')
                ->where('elements.user_id = :user_id')
				->andWhere($query->expr()->gte('analytics.access_time', ':two_weeks'))
				->setParameter("user_id", $user_id)
				->setParameter("two_weeks", (time() - 1209600))
					->groupBy("analytics.element_id")
					->orderBy("total", "DESC");

				$query = $query->getQuery();
				$result = $query->getScalarResult();

				return $result;
				break;
			case 'elementbasics':

				$result = ElementAnalyticBasic::findWhere(['element_id' => $element_id]);

				if ($result) {

                    if (is_array($result)) {
                        $result = $result[0]->toArray();
                    } else {
                        $result = $result->toArray();
                    }

                    $data = $result['data'];
                    $data['total'] = $result['total'];
                    return $data;
				} else {
					return false;
				}
				break;
			case 'recentlyadded':

				$result = People::find($user_id)->elements(false, false, "creation_date DESC");

				return $result;
				break;
		}
	}

	protected function getElementMarkup($id,$status_uid,$original_request=false,$original_response=false,$access_method='direct',$location=false,$geo=false,$donottrack=false) {
		$element = $this->getElement($id);
		$element_type = strtolower($element['type']);
		$element_options = $element['options'];

		if ($element_type) {
			$class_name = $this->elements_array[$element_type];
			$for_include = CASH_PLATFORM_ROOT."/elements/$class_name/$class_name.php";

			if (file_exists($for_include)) {
				$element_object_type = "\\CASHMusic\\Elements\\$class_name\\$class_name";

				$element_object = new $element_object_type($id,$element,$status_uid,$original_request,$original_response);
				if ($geo) {
					$access_data = array(
						'geo' => json_decode($geo,true)
					);
				} else {
					$access_data = false;
				}
				if (!$donottrack) {
					$this->recordAnalytics($id,$access_method,'getmarkup',$location,$access_data);
				}
				$markup = $element_object->getMarkup();
				$markup = '<div class="cashmusic element ' . $element_type . ' id-' . $id . '">' . $markup . '</div>';
				return $markup;
			}
		} else {
			return false;
		}
	}

	protected function addElement($name,$type,$options_data,$user_id) {
		// if this worked we can cast to an array
		if($result = Element::create([
            'name' => $name,
            'type' => $type,
            'options' => $options_data,
            'user_id' => $user_id
        ])) {
			CASHSystem::errorLog($result->toArray());
			return $result->id;
		}

		return false;
	}

	protected function editElement($id,$name,$options_data,$user_id=false) {
		$conditions = [
			"id" => $id
		];

		if ($user_id) {
			$conditions['user_id'] = $user_id;
            $element = Element::findWhere($conditions);
		} else {
            $element = Element::find($id);
		}

        $element->name = $name;
        $element->options = $options_data;
        $result = $element->save();

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
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
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

		if (count($campaign['elements'])) {
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
		} else {
			return false;
		}

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

		if (is_array($campaign['elements'])) {
            if(($key = array_search($element_id, $campaign['elements'])) === false) {
                $campaign['elements'][] = $element_id;
            }
		} else {
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
