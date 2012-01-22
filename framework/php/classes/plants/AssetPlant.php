<?php
/**
 * AssetPlant handles the abstraction for things like grabbing and adding 
 * metadata to assets, claiming private downloads, and adding new files to the 
 * system. It is settings-aware and works across multiple storage systems.
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
class AssetPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'asset';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addasset'          => array('addAsset','direct'),
				'claim'             => array('redirectToAsset',array('get','post','direct')),
				'editasset'         => array('editAsset','direct'),
				'getanalytics'      => array('getAnalytics','direct'),
				'getasset'          => array('getAssetInfo','direct'),
				'getassetsforuser'  => array('getAssetsForUser','direct'),
				'unlock'            => array('unlockAsset','direct')
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				// switch statement for cases that require more thinking than a straight pass-throgh
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

	protected function getAssetsForUser($user_id) {
		$result = $this->db->getData(
			'assets',
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

	protected function getAssetInfo($id) {
		$result = $this->db->getData(
			'assets',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			)
		);
		if ($result) {
			$asset_info = $result[0];
			$asset_info['tags'] = $this->getAllMetaData('assets',$id,'tag');
			$asset_info['metadata'] = $this->getAllMetaData('assets',$id);
			return $asset_info;
		} else {
			return false;
		}
	}
	
	protected function addAsset($title,$description,$location,$user_id,$connection_id=0,$tags=false,$metadata=false,$parent_id=0,$public_status=1) {
		$result = $this->db->setData(
			'assets',
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'user_id' => $user_id,
				'connection_id' => $connection_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status
			)
		);
		if ($result) {
			$this->setAllMetaData('assets',$result,$user_id,$tags,$metadata);
		}
		return $result;
	}
	
	protected function editAsset($id,$title=false,$description=false,$location=false,$connection_id=false,$parent_id=false,$public_status=false,$user_id=false,$tags=false,$metadata=false) {
		$final_edits = array_filter(
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'connection_id' => $connection_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'assets',
			$final_edits,
			array(
				'id' => array(
					'condition' => '=',
					'value' => $id
				)
			)
		);
		if ($result && $tags && $metadata && $user_id) {
			$this->setAllMetaData('assets',$id,$user_id,$tags,$metadata,true);
		}
		return $result;
	}

	protected function deleteAsset($id) {
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
	}

	/**
	 * Returns true if asset is public, false otherwise
	 *
	 * @return boolean
	 */protected function getPublicStatus($id) {
		$result = $this->db->getData(
			'assets',
			'public_status',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $id
				)
			),
			1
		);
		if ($result[0]['public_status']) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Adds an unlock state to platform session persistent store
	 *
	 * @return boolean
	 */protected function unlockAsset($id) {
		$current_unlocked_assets = $this->sessionGet('unlocked_assets');
		if (is_array($current_unlocked_assets)) {
			$current_unlocked_assets[""."$id"]=true;
			$this->sessionSet('unlocked_assets',$current_unlocked_assets);
			return true;
		} else {
			$this->sessionSet('unlocked_assets',array(""."$id" => true));
			return true;
		}
		return false;
	}

	/**
	 * Returns true if an assetIsUnlocked, false if not
	 *
	 * @return boolean
	 */protected function getUnlockedStatus($id) {
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
		$ip_and_proxy = CASHSystem::getRemoteIP();
		$result = $this->db->setData(
			'assets_analytics',
			array(
				'asset_id' => $id,
				'element_id' => $element_id,
				'access_time' => time(),
				'client_ip' => $ip_and_proxy['ip'],
				'client_proxy' => $ip_and_proxy['proxy'],
				'cash_session_id' => $this->getCASHSessionID()
			)
		);
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

	protected function getFinalAssetLocation($connection_id,$user_id,$asset_location) {
		$connection_type = $this->getConnectionType($connection_id);
		$final_asset_location = false;
		switch ($connection_type) {
			case 'com.amazon':
				$s3 = new S3Seed($user_id,$connection_id);
				$final_asset_location = $s3->getExpiryURL($asset_location);
				break;
		    default:
				if (parse_url($asset_location) || strpos($asset_location, '/') !== false) {
					$final_asset_location = $asset_location;
					break;
				}
		}
		return $final_asset_location;
	}

	/**
	 * Reads asset details and redirects to the file directly. The success 
	 * Response is set here rather than in processRequest(), allowing it to 
	 * exist in the session 
	 *
	 * @param {integer} $id - the asset you are trying to retrieve
	 * @return string
	 */protected function redirectToAsset($id,$element_id=0) {
		if ($this->getUnlockedStatus($id)) {
			$asset = $this->getAssetInfo($id);
			$final_asset_location = $this->getFinalAssetLocation(
				$asset['connection_id'],
				$asset['user_id'],
				$asset['location']
			);
			if ($final_asset_location !== false) {
				$this->pushSuccess(array('asset' => $id),'redirect executed successfully');
				$this->recordAnalytics($id,$element_id);
				header("Location: " . $final_asset_location);
				die();
			} else {
				return $this->response->pushResponse(
					500,$this->request_type,$this->action,
					$this->request,
					'unknown asset type, please as an admin to check the asset type'
				);
			}
		}
	}
} // END class 
?>