<?php
/**
 * AssetPlant handles the abstraction for things like grabbing and adding
 * metadata to assets, claiming private downloads, and adding new files to the
 * system. It is settings-aware and works across multiple storage systems.
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
 * This file is generously sponsored by Rob Morrissey (@robmorrissey)
 **/
class AssetPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'asset';
		$this->routing_table = array(
			// alphabetical for ease of reading
			// first value  = target method to call
			// second value = allowed request methods (string or array of strings)
			'addasset'                => array('addAsset','direct'),
			'addlockcode'             => array('addLockCode','direct'),
			'claim'                   => array('redirectToAsset',array('get','post','direct')),
			'deleteasset'             => array('deleteAsset','direct'),
			'editasset'               => array('editAsset','direct'),
			'finalizeupload'          => array('finalizeUpload','direct'),
			'findassets'              => array('findAssets','direct'),
			'getanalytics'            => array('getAnalytics','direct'),
			'getasset'                => array('getAssetInfo','direct'),
			'getassetsforconnection'  => array('getAssetsForConnection','direct'),
			'getassetsforparent'      => array('getAssetsForParent','direct'),
			'getassetsforuser'        => array('getAssetsForUser','direct'),
			'getasseturl'             => array('getFinalAssetLocation','direct'),
			'getfulfillmentassets'    => array('getStoredAssets','direct'),
			'getuploadparameters'     => array('getUploadParameters','direct'),
			'getpublicurl'				  => array('getPublicURL','direct'),
			'makepublic'              => array('makePublic','direct'),
			'redeemcode'              => array('redeemLockCode',array('direct','get','post')),
			'unlock'                  => array('unlockAsset','direct')
		);
		$this->plantPrep($request_type,$request);
	}

	protected function findAssets($query,$user_id,$page=1,$max_returned=10) {
		$limit = (($page - 1) * $max_returned) . ',' . $max_returned;
		$fuzzy_query = '%' . $query . '%';

		$result = $this->db->getData(
			'AssetPlant_findAssets',
			false,
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				),
				"query" => array(
					"condition" => "=",
					"value" => $fuzzy_query
				),
				"exact_query" => array(
					"condition" => "=",
					"value" => $query
				),
				"starts_with_query" => array(
					"condition" => "=",
					"value" => $query.'%'
				)
			),
			$limit
		);
		return $result;
	}

	protected function getStoredAssets($asset_details,$type='fulfillment',$session_id=false) {
		$result = false; // default return
		if (!is_array($asset_details)) {
			// if $asset details isn't an array, assume it's an id
			$asset_details = $this->getAssetInfo($asset_details);
		}

		// test that getInfo returned results
		if ($asset_details) {
			if ($asset_details['type'] == 'file') {
				$result = array($asset_details);
			} elseif ($asset_details['type'] == 'release') {
				if (isset($asset_details['metadata'][$type])) {
					// check isset first, in case the asset is newly set
					if (count($asset_details['metadata'][$type])) {
						$final_assets = array();
						foreach ($asset_details['metadata'][$type] as $fulfillment_id) {
							$fulfillment_resquest = new CASHRequest(
								array(
									'cash_request_type' => 'asset',
									'cash_action' => 'getasset',
									'id' => $fulfillment_id
								)
							);
							if ($fulfillment_resquest->response['payload']) {
								$final_assets[] = $fulfillment_resquest->response['payload'];
							}
						}
						if (count($final_assets)) {
							$result = $final_assets;
						} else {
							$result = false;
						}
					}
				}
			}
		}

		if (is_array($result)) {
			// if we've got a good result, unlock all the assets for download
			// (user is either admin or allowed by element...)
			foreach ($result as $asset) {
				$this->unlockAsset($asset['id'],$session_id);
			}
		}

		return $result;
	}

	protected function getAssetsForConnection($connection_id) {
		$result = $this->db->getData(
			'assets',
			'*',
			array(
				"connection_id" => array(
					"condition" => "=",
					"value" => $connection_id
				)
			),
			false,
			'location'
		);
		return $result;
	}

	protected function getAssetsForUser($user_id,$type=false,$parent_id=false) {
		$options_array = array(
			"user_id" => array(
				"condition" => "=",
				"value" => $user_id
			)
		);
		if ($type !== false) {
			$options_array["type"] = array(
				"condition" => "=",
				"value" => $type
			);
		}
		if ($parent_id !== false) {
			$options_array["parent_id"] = array(
				"condition" => "=",
				"value" => $parent_id
			);
		}

		$result = $this->db->getData(
			'assets',
			'*',
			$options_array
		);
		if ($result) {
			if (is_array($result)) {
				foreach ($result as &$asset) {
					$asset['tags'] = $this->getAllMetaData('assets',$asset['id'],'tag');
					$asset['metadata'] = json_decode($asset['metadata'],true);
				}
			}
		}

		return $result;
	}

	/**
	 * Gets all details for a specific asset id (or array of ids) — pass in a single
	 * id and get the asset details associative array, pass in an array of asset ids
	 * and get an array of asset detail arrays.
	 *
	 * @return void
	 */protected function getAssetInfo($id,$user_id=false) {
		// first set conditions based on single id or array
		if (!is_array($id)) {
			// straightforward...i mean seriously
			$conditions = array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			);
		} else {
			// implode array and use IN operator
			$conditions = array(
				"id" => array(
					"condition" => "IN",
					"value" => $id
				)
			);
		}
		if ($user_id) {
			$conditions['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$result = $this->db->getData(
			'assets',
			'*',
			$conditions
		);
		if ($result) {
			foreach ($result as &$asset_info) {
				$asset_info['tags'] = $this->getAllMetaData('assets',$id,'tag');
				$asset_info['metadata'] = json_decode($asset_info['metadata'],true);
				if (!is_array($asset_info['metadata'])) {
					// force metadata output to associative array
					$output_array = array();
					if ($asset_info['metadata'] !== false) {
						// there was a non-array stored as priamry metadata value.
						// push it to 'content' key and return associative array
						$output_array['content'] = $asset_info['metadata'];
					}
					$asset_info['metadata'] = $output_array;
				}
			}
			if (!is_array($id)) {
				return $result[0];
			} else {
				return $result;
			}
		} else {
			return false;
		}
	}

	protected function getAssetsForParent($parent_id) {
		$result = $this->db->getData(
			'assets',
			'*',
			array(
				"parent_id" => array(
					"condition" => "=",
					"value" => $parent_id
				)
			)
		);
		return $result;
	}

	protected function addAsset($title,$description,$user_id,$location='',$connection_id=0,$hash='',$size=0,$public_url='',$type='file',$tags=false,$metadata=false,$parent_id=0,$public_status=0) {
		$result = $this->db->setData(
			'assets',
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'user_id' => $user_id,
				'connection_id' => $connection_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status,
				'hash' => $hash,
				'size' => $size,
				'type' => $type,
				'public_url' => $public_url='',
				'metadata' => json_encode($metadata)
			)
		);
		if ($result) {
			$this->setAllMetaData('assets',$result,$user_id,$tags,false);
		}
		return $result;
	}

	protected function deleteAsset($id,$user_id=false) {
		$asset_details = $this->getAssetInfo($id);
		if ($asset_details) {
			$user_id_match = true;
			if ($user_id) {
				if ($asset_details['user_id'] != $user_id) {
					$user_id_match = false;
				}
			}
			if ($user_id_match) {
				if ($asset_details['parent_id']) {
					$parent_details = $this->getAssetInfo($asset_details['parent_id']);
					if ($parent_details['type'] == 'release') {
						if (isset($parent_details['metadata']['cover'])) {
							if ($parent_details['metadata']['cover'] == $id) {
								$parent_details['metadata']['cover'] = '';
							}
						}
						if (isset($parent_details['metadata']['fulfillment'])) {
							foreach ($parent_details['metadata']['fulfillment'] as $key => $value) {
								if ($value == $id) {
									unset($parent_details['metadata']['fulfillment'][$key]);
								}
							}
						}
						if (isset($parent_details['metadata']['private'])) {
							foreach ($parent_details['metadata']['private'] as $key => $value) {
								if ($value == $id) {
									unset($parent_details['metadata']['private'][$key]);
								}
							}
						}
						$this->editAsset(
							$asset_details['parent_id'],
							false,false,false,false,false,false,false,false,false,false,false,false,
							$parent_details['metadata']
						);
					}
				}
				$result = $this->db->deleteData(
					'assets',
					array(
						'id' => array(
							'condition' => '=',
							'value' => $id
						)
					)
				);
				if ($result) {
					$this->removeAllMetaData('assets',$id);
				}
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	protected function editAsset($id,$hash=false,$size=false,$location=false,$title=false,$description=false,$public_url=false,$connection_id=false,$type=false,$parent_id=false,$public_status=false,$user_id=false,$tags=false,$metadata=false) {
		$final_edits = array_filter(
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'public_url' => $public_url,
				'connection_id' => $connection_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status,
				'type' => $type,
				'size' => $size,
				'hash' => $hash,
				'metadata' => json_encode($metadata)
			),
			'CASHSystem::notExplicitFalse'
		);
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
			'assets',
			$final_edits,
			$condition
		);
		if ($result && $tags && $user_id) {
			$this->setAllMetaData('assets',$id,$user_id,$tags,false,true);
		}
		return $result;
	}

	/**
	 * Returns true if asset is public, false otherwise
	 *
	 * @return boolean
	 */protected function getPublicStatus($id) {
		$result = $this->db->getData(
			'assets',
			'public_url', // originally we did this with public_status but that's become unnecessary
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			),
			1
		);
		if ($result[0]['public_url']) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds an unlock state to platform session persistent store
	 *
	 * @return boolean
	 */protected function unlockAsset($id,$session_id=false) {
	 	CASHSystem::startSession($session_id);
		$current_unlocked_assets = $this->sessionGet('unlocked_assets');
		if (!is_array($current_unlocked_assets)) {
			$current_unlocked_assets = array();
		}
		$assets_to_unlock = array($id);
		$asset = $this->getAssetInfo($id);

		foreach ($assets_to_unlock as $asset_id) {
			$current_unlocked_assets[""."$asset_id"] = true;
		}
		$this->sessionSet('unlocked_assets',$current_unlocked_assets);

		if (count($current_unlocked_assets)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if an assetIsUnlocked, false if not
	 *
	 * @return boolean
	 */protected function getUnlockedStatus($id,$session_id=false) {
		CASHSystem::startSession($session_id);
		if ($this->getPublicStatus($id)) {
			return true;
		}
		$current_unlocked_assets = $this->sessionGet('unlocked_assets');

		if (is_array($current_unlocked_assets)) {
			if (array_key_exists(""."$id",$current_unlocked_assets)) {
				if ($current_unlocked_assets[""."$id"] === true) {
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Records the basic access data to the assets analytics table
	 *
	 * @return boolean
	 */protected function recordAnalytics($id,$element_id=0) {
		// check settings first as they're already loaded in the environment
		$record_type = CASHSystem::getSystemSettings('analytics');
		if ($record_type == 'off') {
			return true;
		}

		// only count one asset per session
		$recorded_assets = $this->sessionGet('recorded_assets');
		if (is_array($recorded_assets)) {
			if (in_array($id, $recorded_assets)) {
				// already recorded for this session. just return true.
				return true;
			} else {
				// didn't find a record of this asset. record it and move forward
				$recorded_assets[] = $id;
				$this->sessionSet('recorded_assets',$recorded_assets);
			}
		} else {
			$this->sessionSet('recorded_assets',array($id));
		}

		// first the big record if needed
		if ($record_type == 'full' || !$record_type) {
			$ip_and_proxy = CASHSystem::getRemoteIP();
			$result = $this->db->setData(
				'assets_analytics',
				array(
					'asset_id' => $id,
					'element_id' => $element_id,
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
				"asset_id" => array(
					"condition" => "=",
					"value" => $id
				)
			);
			$current_result = $this->db->getData(
				'assets_analytics_basic',
				'*',
				$condition
			);
			if (is_array($current_result)) {
				$new_total = $current_result[0]['total'] +1;
			} else {
				$new_total = 1;
				$condition = false;
			}
			$result = $this->db->setData(
				'assets_analytics_basic',
				array(
					'asset_id' => $id,
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
	 */protected function getAnalytics($analtyics_type,$user_id) {
		switch (strtolower($analtyics_type)) {
			case 'mostaccessed':
				$result = $this->db->getData(
					'AssetPlant_getAnalytics_mostaccessed',
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
					'assets',
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

	protected function getFinalAssetLocation($connection_id,$user_id,$asset_location,$params=false) {
		$connection = $this->getConnectionDetails($connection_id);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);
		if (is_array($connection_type)) {
			$seed_type = $connection_type['seed'];
			$seed = new $seed_type($user_id,$connection_id);
			return $seed->getExpiryURL($asset_location);
		} else {
			if ($asset_location) {
				return $asset_location;
			} else {
				return false;
			}
		}
	}

	protected function getPublicURL($id,$user_id=false) {
		$asset = $this->getAssetInfo($id);
		if ($user_id) {
			if ($asset['user_id'] != $user_id) {
				return false;
			}
		}
		if ($asset['public_status']) {
			return $asset['location'];
		} else {
			return $this->makePublic($id);
		}
	}

	/**
	 * Reads asset details and redirects to the file directly. The success
	 * Response is set here rather than in processRequest(), allowing it to
	 * exist in the session
	 *
	 * @param {integer} $id - the asset you are trying to retrieve
	 * @return string
	 */protected function redirectToAsset($id,$element_id=0,$session_id=false) {
		//if ($this->getUnlockedStatus($id,$session_id)) {
			$asset = $this->getAssetInfo($id);
			$final_asset_location = $this->getFinalAssetLocation(
				$asset['connection_id'],
				$asset['user_id'],
				$asset['location']
			);
			if ($final_asset_location !== false) {
				$this->pushSuccess(array('asset' => $id),'redirect executed successfully');
				$this->recordAnalytics($id,$element_id);
				CASHSystem::redirectToUrl($final_asset_location);
				die();
			} else {
				return $this->response->pushResponse(
					500,$this->request_type,$this->action,
					$this->request,
					'unknown asset type, please as an admin to check the asset type'
				);
			}
			/*
		} else {
			// fail back to the default embed with an error string
			CASHSystem::redirectToUrl(CASH_PUBLIC_URL . '/request/embed/' . $element_id . '?redirecterror=1&session_id=' . $session_id);
			die();
		}
		*/
	}

	protected function getUploadParameters($connection_id,$user_id) {
		$connection = $this->getConnectionDetails($connection_id);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);
		if (is_array($connection_type)) {
			$seed_type = $connection_type['seed'];
			$seed = new $seed_type($user_id,$connection_id);
			return $seed->getUploadParameters();
		} else {
			return false;
		}
	}

	protected function finalizeUpload($connection_id,$filename) {
		$connection = $this->getConnectionDetails($connection_id);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);
		if (is_array($connection_type)) {
			$seed_type = $connection_type['seed'];
			$seed = new $seed_type($connection['user_id'],$connection_id);
			return $seed->finalizeUpload($filename);
		} else {
			return false;
		}
	}

	protected function makePublic($id,$user_id=false,$commit=false) {
		$asset = $this->getAssetInfo($id);
		if ($user_id) {
			if ($asset['user_id'] != $user_id) {
				return false;
			}
		}
		$connection = $this->getConnectionDetails($asset['connection_id']);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);
		if (is_array($connection_type)) {
			$seed_type = $connection_type['seed'];
			$seed = new $seed_type($asset['user_id'],$asset['connection_id']);
			$public_location = $seed->makePublic($asset['location']);
			if ($commit) {
				$this->editAsset(
					$id,
					false,false,
					$public_location,
					false,false,false,
					0,
					false,false,
					1
				);
			}
			return $public_location;
		} else {
			return false;
		}
	}

	/**
	 * Wrapper for system lock code call
	 *
	 * @param {integer} $element_id - the element for which you're adding the lock code
	 * @return string|false
	 */protected function addLockCode($asset_id){
		$asset_info = $this->getAssetInfo($asset_id);
		if ($asset_info) {
			$user_id = $asset_info['user_id'];
			$add_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'addlockcode',
					'scope_table_alias' => 'assets',
					'scope_table_id' => $asset_id,
					'user_id' => $user_id
				)
			);
			return $add_request->response['payload'];
		}
		return false;
	}

	/**
	 * Wrapper for system lock code call
	 *
	 * @param {string} $code - the code
	 * @return bool
	 */protected function redeemLockCode($code,$user_id=false,$element_id=false) {
			if (!$user_id && $element_id) {
				$element_request = new CASHRequest(
					array(
						'cash_request_type' => 'element',
						'cash_action' => 'getelement',
						'id' => $element_id
					)
				);
				if ($element_request->response['payload']) {
					$user_id = $element_request->response['payload']['user_id'];
				}
			}
			if ($user_id) {
				$redeem_request = new CASHRequest(
					array(
						'cash_request_type' => 'system',
						'cash_action' => 'redeemlockcode',
						'code' => $code,
						'scope_table_alias' => 'assets',
						'user_id' => $user_id
					)
				);
				return $redeem_request->response['payload'];
			} else {
				return false;
			}
	}

} // END class
?>
