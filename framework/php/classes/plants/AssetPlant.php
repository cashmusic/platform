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
				'addasset'                => array('addAsset','direct'),
				'claim'                   => array('redirectToAsset',array('get','post','direct')),
				'editasset'               => array('editAsset','direct'),
				'getanalytics'            => array('getAnalytics','direct'),
				'getasset'                => array('getAssetInfo','direct'),
				'getassetsforconnection'  => array('getAssetsForConnection','direct'),
				'getassetsforuser'        => array('getAssetsForUser','direct'),
				'syncconnectionassets'    => array('syncConnectionAssets','direct'),
				'unlock'                  => array('unlockAsset','direct')
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				return $this->response->pushResponse(
					404,$this->request_type,$this->action,
					$this->request,
					'unknown action'
				);
			}
		} else {
			return $this->response->pushResponse(
				400,$this->request_type,$this->action,
				$this->request,
				'no action specified'
			);
		}
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
	
	protected function addAsset($title,$description,$location,$user_id,$connection_id=0,$hash='',$size=0,$public_url='',$type='storage',$tags=false,$metadata=false,$parent_id=0,$public_status=1) {
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
				'public_url' => $public_url=''
				
			)
		);
		if ($result) {
			$this->setAllMetaData('assets',$result,$user_id,$tags,$metadata);
		}
		return $result;
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
				'hash' => $hash
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

	protected function syncConnectionAssets($connection_id) {
		$connection = $this->getConnectionDetails($connection_id);

		switch ($connection['type']) {
			case 'com.amazon':
				$s3 = new S3Seed($connection['user_id'],$connection_id);
				$all_remote_files = $s3->listAllFiles();
				if (!is_array($all_remote_files)) {
					// could not get remote list. boo. abort.
					return false;
				} else {
					$all_local_assets = $this->getAssetsForConnection($connection_id);
				
					$id_lookup = array();
					$compare_local = array();
					$compare_remote = array();
				
					// create reference arrays
					foreach ($all_local_assets as $asset) {
						$id_lookup[$asset['location']] = $asset['id'];
						$compare_local[$asset['location']] = $asset['hash'];
					}
					foreach ($all_remote_files as $file) {
						$compare_remote[$file['name']] = $file['hash'];
					}
				
					//find deltas
					$deltas = array_diff_assoc($compare_remote,$compare_local);
					if (is_array($deltas)) {
						foreach ($deltas as $location => $change) {
							if (array_key_exists($location,$compare_local) && array_key_exists($location,$compare_remote)) {
								// keys in both location - means hash has changed. edit local.
								echo '<br /><br />trying edit<br /><br />';
								$this->editAsset(
									$id_lookup[$location],
									$all_remote_files[$location]['hash'],
									$all_remote_files[$location]['size']
								);
							} else {
								if (array_key_exists($location,$compare_remote)) {
									// remote key only - means new file. add local.
									$this->addAsset(
										$location,
										'',
										$location,
										$connection['user_id'],
										$connection_id,
										$all_remote_files[$location]['hash'],
										$all_remote_files[$location]['size']
									);
								} else {
									// local key only - means file is gone. remove local.
									$this->deleteAsset($id_lookup[$location]);
								}
							}
						}
					}
					return true;
				}
				break;
		}
	}
} // END class 
?>