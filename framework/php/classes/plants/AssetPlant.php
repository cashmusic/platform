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
		$this->routing_table = array(
			// alphabetical for ease of reading
			// first value  = target method to call
			// second value = allowed request methods (string or array of strings)
			'addasset'                => array('addAsset','direct'),
			'addlockcode'             => array('addLockCode','direct'),
			'addremoteuploadform'     => array('addRemoteUploadForm','direct'),
			'claim'                   => array('redirectToAsset',array('get','post','direct')),
			'deleteasset'             => array('deleteAsset','direct'),
			'editasset'               => array('editAsset','direct'),
			'finalizeupload'          => array('finalizeUpload','direct'),
			'findassets'              => array('findAssets','direct'),
			'findconnectiondeltas'    => array('findConnectionAssetDeltas','direct'),
			'getanalytics'            => array('getAnalytics','direct'),
			'getasset'                => array('getAssetInfo','direct'),
			'getassetsforconnection'  => array('getAssetsForConnection','direct'),
			'getassetsforparent'      => array('getAssetsForParent','direct'),
			'getassetsforuser'        => array('getAssetsForUser','direct'),
			'getasseturl'             => array('getFinalAssetLocation','direct'),
			'getuploadparameters'     => array('getPOSTParameters','direct'),
			'getfulfillmentassets'    => array('getFulfillmentAssets','direct'),
			'makepublic'              => array('makePublic','direct'),
			'redeemcode'              => array('redeemLockCode',array('direct','get','post')),
			'syncconnectionassets'    => array('syncConnectionAssets','direct'),
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

	protected function getFulfillmentAssets($asset_details) {
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
				if (isset($asset_details['metadata']['fulfillment'])) {
					// check isset first, in case the asset is newly set
					if (count($asset_details['metadata']['fulfillment'])) {
						$final_assets = array();
						foreach ($asset_details['metadata']['fulfillment'] as $fulfillment_id) {
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
							return $final_assets;
						} else {
							return false;
						}
					}
				}
			}
		}

		if (is_array($result)) {
			// if we've got a good result, unlock all the assets for download
			// (user is either admin or allowed by element...)
			foreach ($result as $asset) {
				$this->unlockAsset($asset['id']);
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
	 */protected function getAssetInfo($id) {
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

	protected function addAsset($title,$description,$user_id,$location='',$connection_id=0,$hash='',$size=0,$public_url='',$type='file',$tags=false,$metadata=false,$parent_id=0,$public_status=1) {
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
				if ($asset_details['parent_id'] != $user_id) {
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
		$assets_to_unlock = array($id);
		$asset = $this->getAssetInfo($id);
		$error_state = false;
		foreach ($assets_to_unlock as $asset_id) {
			if (is_array($current_unlocked_assets)) {
				$current_unlocked_assets[""."$asset_id"] = true;
				$this->sessionSet('unlocked_assets',$current_unlocked_assets);
			} else {
				$this->sessionSet('unlocked_assets',array(""."$asset_id" => true));
			}
			$current_unlocked_assets = $this->sessionGet('unlocked_assets');
		}
		if (is_array($current_unlocked_assets)) {
			return true;
		} else {
			return false;
		}
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

	protected function getFinalAssetLocation($connection_id,$user_id,$asset_location,$inline=false) {
		$connection_type = $this->getConnectionType($connection_id);
		$final_asset_location = false;
		switch ($connection_type) {
			case 'com.amazon':
				$s3 = new S3Seed($user_id,$connection_id);
				if ($inline) {
					$final_asset_location = $s3->getExpiryURL($asset_location,1000,false,false);
				} else {
					$final_asset_location = $s3->getExpiryURL($asset_location,1000,true,true);
				}
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

	/**
	 * Generates HTML upload form to be put on remote server for same-origin uploads
	 *
	 * @return string (HTML)
	 */protected function addRemoteUploadForm($user_id,$connection_id) {
		$connection = $this->getConnectionDetails($connection_id);
		switch ($connection['type']) {
			case 'com.amazon':
				$path_prefix = 'cashmusic-' . $connection['id'] . $connection['creation_date'] . '/' . time();
				$upload_token = $this->initiate_upload($user_id,$connection_id);
				// webhooks
				$api_credentials = CASHSystem::getAPICredentials();
				$webhook_api_url = CASH_API_URL . 'verbose/assets/processwebhook/origin/com.amazon/upload_token/' . $upload_token . '/api_key/' . $api_credentials['api_key'];

				$s3 = new S3Seed($user_id,$connection_id);
				$html_form = $s3->getPOSTUploadHTML($path_prefix,$webhook_api_url);
				$s3_key = $path_prefix . '/cashmusic-' . $upload_token . '.html';
				$upload_url = 'https://' . $s3->getBucketName() . '.s3.amazonaws.com/' . $s3_key;
				if($s3->createFileFromString($html_form,$s3_key,false,'text/html')) {
					$return_array = array (
						'upload_token' => $upload_token,
						'upload_url' => $upload_url,
						'callback' => $webhook_api_url
					);
					return $return_array;
				} else {
					return false;
				}
				break;
		    default:
				return false;
		}
	}

	protected function getPOSTParameters($connection_id) {
		$connection = $this->getConnectionDetails($connection_id);
		switch ($connection['type']) {
			case 'com.amazon':
				$path_prefix = 'cashmusic-' . $connection['id'] . $connection['creation_date'] . '/' . time();
				$s3 = new S3Seed($connection['user_id'],$connection_id);
				return (array) $s3->getPOSTUploadParams($path_prefix);
				break;
		    default:
				return false;
		}
	}

	protected function finalizeUpload($connection_id,$filename) {
		$connection = $this->getConnectionDetails($connection_id);
		switch ($connection['type']) {
			case 'com.amazon':
				$s3 = new S3Seed($connection['user_id'],$connection_id);
				$content_type = CASHSystem::getMimeTypeFor($filename);
				return $s3->changeFileMIME($filename,$content_type);
				break;
		    default:
				return false;
		}
	}

	protected function makePublic($id) {
		$asset = $this->getAssetInfo($id);
		$connection = $this->getConnectionDetails($asset['connection_id']);
		switch ($connection['type']) {
			case 'com.amazon':
				$s3 = new S3Seed($connection['user_id'],$asset['connection_id']);
				$content_type = CASHSystem::getMimeTypeFor($asset['location']);
				if ($s3->changeFileMIME($asset['location'],$content_type,false)) {
					return 'https://s3.amazonaws.com/' . $s3->getBucketName() . '/' . $asset['location'];
				} else {
					return false;
				}
				break;
		    default:
				return false;
		}
	}

	protected function findConnectionAssetDeltas($connection_id,$connection=false) {
		if (!$connection) {
			$connection = $this->getConnectionDetails($connection_id);
		}
		$all_local_assets = $this->getAssetsForConnection($connection_id);
		if (!$all_local_assets) {
			$all_local_assets = array();
		}
		$all_remote_files = false;

		// create reference arrays
		$id_lookup = array();
		$compare_local = array();
		$compare_remote = array();
		// populate local reference arrays
		foreach ($all_local_assets as $asset) {
			$id_lookup[$asset['location']] = $asset['id'];
			$compare_local[$asset['location']] = $asset['hash'];
		}

		// grab remotes, format $compare_remote[] as:
		// $compare_remote['resource_location'] => file or generated hash
		//
		// IMPORTANT:
		// if $all_remote_files must be keyed by service URI and each entry
		// must contain a value for 'size' and 'hash' -- each service Seed
		// should comply to that formatting but if not, fix it there, not here
		switch ($connection['type']) {
			case 'com.amazon':
				$s3 = new S3Seed($connection['user_id'],$connection_id);
				$all_remote_files = $s3->listAllFiles();
				if (!is_array($all_remote_files)) {
					// could not get remote list. boo. abort.
					return false;
				} else {
					// populate remote reference array
					foreach ($all_remote_files as $file) {
						$compare_remote[$file['name']] = $file['hash'];
					}
				}
		}

		if ($all_remote_files) {
			//find deltas
			$deltas = array_diff_assoc($compare_remote,$compare_local);
			$deltas = array_merge($deltas,array_diff_assoc($compare_local,$compare_remote));

			foreach ($deltas as $location => &$change) {
				if (array_key_exists($location,$compare_local) && array_key_exists($location,$compare_remote)) {
					$change = 'update'; // keys in both location - means hash has changed. edit local.
				} else {
					if (array_key_exists($location,$compare_remote)) {
						$change = 'add'; // remote key only - means new file. add local.
					} else {
						$change = 'delete'; // local key only - means file is gone. remove local.
					}
				}
			}

			$return_array = array(
				'local_id_reference' => $id_lookup,
				'remote_details' => $all_remote_files,
				'deltas' => $deltas
			);
			return $return_array;
		} else {
			return false;
		}
	}

	protected function syncConnectionAssets($connection_id) {
		$connection = $this->getConnectionDetails($connection_id);
		$deltas = $this->findConnectionAssetDeltas($connection_id,$connection);

		if (!$deltas) {
			return false;
		} else {
			if (count($deltas['deltas'])) {
				$all_remote_files = $deltas['remote_details'];
				$id_lookup = $deltas['local_id_reference'];
				foreach ($deltas['deltas'] as $location => $change) {
					if ($change == 'update') {
						$this->editAsset(
							$id_lookup[$location],
							$all_remote_files[$location]['hash'],
							$all_remote_files[$location]['size']
						);
					} elseif ($change == 'add') {
						$this->addAsset(
							$location,
							'',
							$connection['user_id'],
							$location,
							$connection_id,
							$all_remote_files[$location]['hash'],
							$all_remote_files[$location]['size']
						);
					} elseif ($change == 'delete') {
						$this->deleteAsset($id_lookup[$location]);
					}
				}
				return true;
			} else {
				// no changes needed. return true
				return true;
			}
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