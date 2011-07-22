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
	protected $lockCodeCharacters = array(
		// hard-coded to avoid 0/o, l/1 type confusions on download cards
		'num_chars' => array('2','3','4','6','7','8','9'),
		'txt_chars' => array('a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z'),
		'all_chars' => array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z')
	);
	
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
	 * Adds an unlock state to Seed session persistent store
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