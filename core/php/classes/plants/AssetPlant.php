<?php
/**
 * Plant handling assets: query information, handle download codes/passwords, etc
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
			switch ($this->action) {
				case 'claim':
					if (!$this->requireParameters('asset_id')) { return $this->sessionGetLastResponse(); }
					$this->redirectToAsset($this->request['asset_id']);
					break;
				case 'getasset':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('asset_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getAssetInfo($this->request['asset_id']);
					if ($result) {
						return $this->pushSuccess($result,'asset details in payload');
					} else {
						return $this->pushFailure('there was an error getting asset details');
					}
					break;
				case 'addasset':
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('title','description','location','user_id')) { return $this->sessionGetLastResponse(); }
					// defaults:
					$addasset_settings_id = 0;
					$addasset_tags = false;
					$addasset_metadata = false;
					$addasset_parent_id = 0;
					$addasset_public_status = 1;
					if (isset($this->request['settings_id'])) { $addasset_settings_id = $this->request['settings_id']; }
					if (isset($this->request['tags'])) { $addasset_tags = $this->request['tags']; }
					if (isset($this->request['metadata'])) { $addasset_metadata = $this->request['metadata']; }
					if (isset($this->request['parent_id'])) { $addasset_parent_id = $this->request['parent_id']; }
					if (isset($this->request['public_status'])) { $addasset_public_status = $this->request['public_status']; }

					$result = $this->addAsset(
						$this->request['title'],
						$this->request['description'],
						$this->request['location'],
						$this->request['user_id'],
						$addasset_settings_id,
						$addasset_tags,
						$addasset_metadata,
						$addasset_parent_id,
						$addasset_public_status
					);
					if ($result) {
						return $this->pushSuccess($result,'asset id payload');
					} else {
						return $this->pushFailure('there was an error adding the asset');
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
	
	public function getAssetInfo($asset_id) {
		$result = $this->db->getData(
			'AssetPlant_getAssetInfo',
			false,
			array(
				"asset_id" => array(
					"condition" => "=",
					"value" => $asset_id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	public function addAsset($title,$description,$location,$user_id,$settings_id=0,$tags=false,$metadata=false,$parent_id=0,$public_status=1) {
		$result = $this->db->setData(
			'assets',
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'user_id' => $user_id,
				'settings_id' => $settings_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status
			)
		);
		if ($result && $tags) {
			$this->batchSetTags('assets',$result,$user_id,$tags);
		}
		if ($result && $metadata) {
			$this->batchSetMetaData('assets',$result,$user_id,$metadata);
		}
		return $result;
	}
	
	public function editAsset($asset_id,$title,$description,$location,$user_id,$settings_id,$tags,$metadata,$parent_id,$public_status) {
		$result = $this->db->setData(
			'assets',
			array(
				'title' => $title,
				'description' => $description,
				'location' => $location,
				'user_id' => $user_id,
				'settings_id' => $settings_id,
				'parent_id' => $parent_id,
				'public_status' => $public_status
			),
			array(
				'id' => array(
					'condition' => '=',
					'value' => $asset_id
				)
			)
		);
		$this->removeAllMetaDataForItem('assets',$asset_id,$user_id);
		if ($result && $tags) {
			$this->batchSetTags('assets',$result,$user_id,$tags);
		}
		if ($result && $metadata) {
			$this->batchSetMetaData('assets',$result,$user_id,$metadata);
		}
		return $result;
	}

	public function deleteAsset($asset_id) {
		$result = $this->db->deleteData(
			'assets',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $asset_id
				)
			)
		);
		if ($result) {
			$this->removeAllMetaDataForItem('assets',$asset_id);
		}
		return $result;
	}

	/**
	 * Returns true if asset is public, false otherwise
	 *
	 * @return boolean
	 */protected function getPublicStatus($asset_id) {
		$result = $this->db->getData(
			'assets',
			'public_status',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $asset_id
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
	 * Reads asset details and redirects to the file directly. The success 
	 * Response is set here rather than in processRequest(), allowing it to 
	 * exist in the session 
	 *
	 * @param {integer} $asset_id - the asset you are trying to retrieve
	 * @return string
	 */public function redirectToAsset($asset_id) {
		if ($this->getUnlockedStatus($asset_id)) {
			$asset = $this->getAssetInfo($asset_id);
			switch ($asset['type']) {
				case 'com.amazon':
					include(CASH_PLATFORM_ROOT.'/classes/seeds/S3Seed.php');
					$s3 = new S3Seed($asset['user_id'],$asset['settings_id']);
					$this->pushSuccess(array('asset' => $asset_id),'redirect executed successfully');
					header("Location: " . $s3->getExpiryURL($asset['location']));
					die();
					break;
			    default:
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