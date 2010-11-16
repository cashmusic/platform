<?php
/**
 * Plant handling assets: query information, handle download codes/passwords, etc
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 *
 * Copyright (c) 2010, CASH Music
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
				case 'redirect':
					$this->response->pushResponse(
						200,
						$this->request_type,
						$this->action,
						$this->request,
						'redirect executed successfully'
					);
					$this->redirectToAsset($this->request['asset_id']);
					die();
				default:
					return $this->response->pushResponse(
						400,
						$this->request_type,
						$this->action,
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
		$query = "SELECT a.user_id,a.parent_id,a.location,a.title,a.description,a.comment,a.seed_settings_id,";
		$query .= "s.name,s.type ";
		$query .= "FROM asst_assets a LEFT OUTER JOIN seed_settings s ON a.seed_settings_id = s.id ";
		$query .= "WHERE a.id = $asset_id";
		$result = $this->db->doQueryForAssoc($query);
		return $result;
	}
	
	public function redirectToAsset($asset_id) {
		if ($this->restrictExecutionTo('direct')) {
			if ($asset_id) {
				$asset = $this->getAssetInfo($asset_id);
				switch ($asset['type']) {
					case 'com.amazon.aws':
						include(SEED_ROOT.'/classes/seeds/S3Seed.php');
						$s3 = new S3Seed();
						header("Location: " . $s3->getExpiryURL($asset['location']));
						break;
				    default:
				        // error: type not known
				}
			} else {
				// error: no asset specified
			}
		} else {
			// error: you need to call this action a from a different type
			//        of request. try a direct call.
		}
	}
} // END class 
?>