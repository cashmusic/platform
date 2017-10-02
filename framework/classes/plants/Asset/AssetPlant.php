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

namespace CASHMusic\Plants\Asset;

use CASHMusic\Core\CASHDBAL;
use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Admin\AdminHelper;
use CASHMusic\Entities\Asset;
use CASHMusic\Entities\AssetAnalytic;
use CASHMusic\Entities\AssetAnalyticsBasic;
use CASHMusic\Entities\People;
use CASHMusic\Entities\SystemMetadata;
use CASHMusic\Seeds\S3Seed;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pixie\Exception;

class AssetPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'asset';
        $this->getRoutingTable();

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
		CASHSystem::errorLog("getStoredAssets $asset_details");

		// i have no idea why this is
		if (is_array($asset_details) && count($asset_details) == 1
			&& is_numeric($asset_details[0])) {
			$asset_details = array_pop($asset_details);
		}

		if (!is_array($asset_details)) {
			// if $asset details isn't an array, assume it's an id
			$asset_details = $this->getAssetInfo($asset_details);
		}

		CASHSystem::errorLog($asset_details);

		// test that getInfo returned results
		if ($asset_details !== false) {

			//vestigial mess
			CASHSystem::errorLog(gettype($asset_details));
			if (is_cash_model($asset_details)) {
                $asset_details = $asset_details->toArray();
			}

			CASHSystem::errorLog($asset_details);

			if ($asset_details['type'] == 'file') {
				$result = array($asset_details);
			} elseif ($asset_details['type'] == 'release') {

				if (!empty($asset_details['metadata'][$type])) {
					// check isset first, in case the asset is newly set
					if (count($asset_details['metadata'][$type])) {
						$final_assets = array();

						foreach ($asset_details['metadata'][$type] as $fulfillment_id) {


							if (is_array($fulfillment_id)) $fulfillment_id = array_pop($fulfillment_id);


							if ($fulfillment_asset = $this->getAssetInfo($fulfillment_id)) {
								$final_assets[] = $fulfillment_asset->toArray();
							}
						}

						CASHSystem::errorLog($final_assets);

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

		$assets = $this->orm->findWhere(Asset::class, ['connection_id'=>$connection_id] );

		if ($assets) {
			return $assets->toArray();
		}

        return false;

	}

	protected function getAssetsForUser($user_id,$type=false,$parent_id=false) {
		$options = [
			"user_id" => $user_id
		];

		if ($type !== false) {
			$options["type"] = $type;
		}

		if ($parent_id !== false) {
			$options["parent_id"] = $parent_id;
		}

        try {

			// this should actually be from People but i need to build out conditions for relationships
            $assets = $this->orm->findWhere(Asset::class, $options, true);

		} catch (\Exception $e) {
        	CASHSystem::errorLog($e->getMessage());
		}

		if ($assets) {
            return $assets;
		}

		return false;
	}

	protected function getAssetFromUnlockCode($scope_table_alias, $scope_table_id) {

        $session = CASHSystem::startSession();
		$session_id = $session['id'];

		// this is a hack to get test emails working
		if (!is_numeric($scope_table_id) && strlen($scope_table_id) > 64) {
            $asset_id = substr($scope_table_id, 64);
            $asset_details = $this->getAssetInfo($asset_id);
		} else {
            $asset = $this->getAllMetaData($scope_table_alias,$scope_table_id,'asset_id');
            $asset_id = $asset['asset_id'];
		}

		if (!empty($asset_id)) {
			if ($this->unlockAsset($asset_id,$session_id)) {

                $asset_details = $this->getAssetInfo($asset_id);

				return [
					'uri'=>"/request/?cash_request_type=asset&cash_action=claim&id=".$asset_id."&element_id=&session_id=".$session_id,
					'name'=>$asset_details->title
				];
				//$this->redirectToAsset($asset['asset_id'],0,$session_id, true);
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	/**
	 * Gets all details for a specific asset id (or array of ids) — pass in a single
	 * id and get the asset details associative array, pass in an array of asset ids
	 * and get an array of asset detail arrays.
	 *
	 * @return string
	 */protected function getAssetInfo($id,$user_id=false) {

		// handles array or integer
    	$conditions = ['id'=>$id];

		if ($user_id) {
			$conditions['user_id'] = $user_id;
		}

		if ($asset = $this->orm->findWhere(Asset::class, $conditions)) {
			/*foreach ($result as &$asset_info) {
				$asset_info = $asset_info->toArray();
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
			}*/

            return $asset;
		} else {
			CASHSystem::errorLog($asset);
			return false;
		}
	}

	protected function getAssetsForParent($parent_id) {

	 	$assets = $this->orm->findWhere(Asset::class, ['parent_id'=>$parent_id] );

		return $assets;
	}

	protected function addAsset($title,$description,$user_id,$location='',$connection_id=0,$hash='',$size=0,$public_url='',$type='file',$tags=false,$metadata=false,$parent_id=0,$public_status=0) {

	 	$result = $this->orm->create(Asset::class, [
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
            'metadata' => $metadata
        ]);

		if ($result) {
			$this->setAllMetaData('assets',$result->id,$user_id,$tags,false);
		}
		return $result->id;
	}

	protected function deleteAsset($id,$user_id=false, $connection_id=false) {
		$asset_details = $this->getAssetInfo($id);

		if ($asset_details) {

            $connection = $this->getConnectionDetails($connection_id);
            $connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);

            if (is_array($connection_type)) {
                $seed_type = '\\CASHMusic\\Seeds\\'.$connection_type['seed'];
                $seed = new $seed_type($user_id,$connection_id);
                $seed->deleteFile($asset_details->location);
            }

			$user_id_match = true;
			if ($user_id) {
				if ($asset_details->user_id != $user_id) {
					$user_id_match = false;
				}
			}
			if ($user_id_match) {
				if ($asset_details->parent_id) {
					$parent_details = $this->getAssetInfo($asset_details->parent_id);

					if ($parent_details->type == 'release') {
						if (isset($parent_details->metadata['cover'])) {
							if ($parent_details->metadata['cover'] == $id) {
								$parent_details->metadata['cover'] = '';
							}
						}
						if (isset($parent_details->metadata['fulfillment'])) {
							foreach ($parent_details->metadata['fulfillment'] as $key => $value) {
								if ($value == $id) {
									unset($parent_details->metadata['fulfillment'][$key]);
								}
							}
						}
						if (isset($parent_details->metadata['private'])) {
							foreach ($parent_details->metadata['private'] as $key => $value) {
								if ($value == $id) {
									unset($parent_details->metadata['private'][$key]);
								}
							}
						}

                        $parent_details->save();
					}
				}

				if ($asset_details->delete()) {
					$this->removeAllMetaData('assets',$id);
					return true;
				}

				return false;
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
            function($value) {
                return CASHSystem::notExplicitFalse($value);
            }
		);

        $conditions = ['id'=>$id];


        if ($user_id) {
            $conditions['user_id'] = $user_id;
        }

		$asset = $this->orm->findWhere(Asset::class, $conditions);

        $result = $asset->update($final_edits);

		if ($result && $tags && $user_id) {
			$this->setAllMetaData('assets',$id,$user_id,$tags,false,true);
		}

		return $result;
	}

	/**
	 * Returns true if asset is public, false otherwise
	 *
	 * @return boolean
	 */
	protected function getPublicStatus($id) {

	 	$asset = $this->orm->find(Asset::class, $id );

		if (!empty($asset->public_url)) {
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

			$result = $this->orm->create(AssetAnalytic::class, [
                'asset_id' => $id,
                'element_id' => $element_id,
                'access_time' => time(),
                'client_ip' => $ip_and_proxy['ip'],
                'client_proxy' => $ip_and_proxy['proxy'],
                'cash_session_id' => $this->getSessionID()
            ]);

		}
		// basic logging happens for full or basic
		if ($record_type == 'full' || $record_type == 'basic') {

			$basic_analytics = $this->orm->findWhere(AssetAnalyticsBasic::class, ['asset_id'=>$id] );

			if (!empty($basic_analytics->total)) {
				$new_total = $basic_analytics->total +1;
			} else {
				$new_total = 1;
				$condition = false;
			}

            $basic_analytics->total = $new_total;
			$result = $basic_analytics->save();
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

                $query = $this->db->table('assets_analytics')
					->select("assets_analytics.asset_id as 'id', COUNT(assets_analytics.id) as 'count', assets.title as 'title', assets.description as 'description'")
                    ->join('assets', 'assets.id', '=', 'assets_analytics.asset_id')
					->where('assets.user_id', $user_id)
					->where('assets.parent_id', 0)
					->groupBy('assets_analytics.asset_id')
					->orderBy('count', 'DESC');

				$result = $query->get();

				return $result;
				break;
			case 'recentlyadded':

				$query = $this->db->table('assets')
					->where('user_id', $user_id)
					->orderBy('creation_date', 'DESC');

				$result = $query->get();

				return $result;
				break;
		}
	}

	protected function getFinalAssetLocation($connection_id,$user_id,$asset_location,$params=false) {
		if ($connection = $this->getConnectionDetails($connection_id)) {
            $connection_type = CASHSystem::getConnectionTypeSettings($connection->type);

            if (is_array($connection_type)) {
                $seed_type = '\\CASHMusic\\Seeds\\'.$connection_type['seed'];
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

		return false;
	}

	protected function getPublicURL($id,$user_id=false) {

		$asset = $this->getAssetInfo($id);

		if ($user_id) {
			if ($asset->user_id != $user_id) {
				return false;
			}
		}
		if ($asset->public_status) {
			return $asset->location;
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
	 */
    protected function redirectToAsset($id,$element_id=0,$session_id=false,$return_only=false) {
		if ($this->getUnlockedStatus($id,$session_id)) {
			$asset = $this->getAssetInfo($id);

			$final_asset_location = $this->getFinalAssetLocation(
				$asset->connection_id,
				$asset->user_id,
				$asset->location
			);

			if ($final_asset_location !== false) {
				$this->pushSuccess(array('asset' => $id),'redirect executed successfully');
				$this->recordAnalytics($id,$element_id);
				if (!$return_only) {
                    CASHSystem::redirectToUrl($final_asset_location);
                    die();
				} else {
					return $final_asset_location;
				}

			} else {
				return $this->response->pushResponse(
					500,$this->request_type,$this->action,
					$this->request,
					'unknown asset type, please as an admin to check the asset type'
				);
			}
		} else {
			if (!$return_only) {
                // fail back to the default embed with an error string
                CASHSystem::redirectToUrl(CASH_PUBLIC_URL . '/request/embed/' . $element_id . '?redirecterror=1&session_id=' . $session_id);
                die();
			} else {
				return false;
			}

		}
	}

	protected function getUploadParameters($connection_id,$user_id,$acl=false) {
		if ($connection = $this->getConnectionDetails($connection_id)) {
            $connection_type = CASHSystem::getConnectionTypeSettings($connection->type);

            if (is_array($connection_type)) {
                $seed_type = '\CASHMusic\Seeds\\' . $connection_type['seed'];
                $seed = new $seed_type($user_id, $connection_id);
                return $seed->getUploadParameters($acl);
            }
        }

        throw new \Exception("didn't work");
	}

	protected function finalizeUpload($connection_id,$filename) {
		$connection = $this->getConnectionDetails($connection_id);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection['type']);
		if (is_array($connection_type)) {
			$seed_type = '\CASHMusic\Seeds\\'. $connection_type['seed'];
			$seed = new $seed_type($connection['user_id'],$connection_id);
			return $seed->finalizeUpload($filename);
		} else {
			return false;
		}
	}

	protected function makePublic($id,$user_id=false,$commit=false) {

		$asset = $this->getAssetInfo($id);
		if ($user_id) {
			if ($asset->user_id != $user_id) {
				return false;
			}
		}
		$connection = $this->getConnectionDetails($asset->connection_id);
		$connection_type = CASHSystem::getConnectionTypeSettings($connection->type);
		if (is_array($connection_type)) {

			$seed_type = '\CASHMusic\Seeds\\'.$connection_type['seed'];
			$seed = new $seed_type($asset->user_id,$asset->connection_id);
			$public_location = $seed->makePublic($asset->location);
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
	 */
	protected function addLockCode($asset_id){
		$asset = $this->getAssetInfo($asset_id);
		if ($asset) {
			$user_id = $asset->user_id;

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

    protected function addBulkLockCodes($asset_id, $code_count){
        $asset = $this->getAssetInfo($asset_id);
        if ($asset) {
            $user_id = $asset->user_id;

            $add_request = new CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'addbulklockcodes',
                    'scope_table_alias' => 'assets',
                    'scope_table_id' => $asset_id,
                    'user_id' => $user_id,
					'count' => $code_count
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
