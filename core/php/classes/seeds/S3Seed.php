<?php
/**
 * Simple class for interfacing with Donovan Schönknecht's S3 library
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
class S3Seed extends SeedBase {
	protected $s3,$bucket='';

	public function __construct($user_id,$use_specific_settings=false) {
		$this->settings_type = 'com.amazon.aws';
		$this->user_id = $user_id;
		$this->use_specific_settings = $use_specific_settings;
		$this->connectDB();
		if ($this->getCASHSettings()) {
			require_once(CASH_PLATFORM_ROOT.'/lib/S3.php');
			$this->s3 = new S3($this->settings->getSetting('key'), $this->settings->getSetting('secret'));
			$this->bucket = $this->settings->getSetting('bucket');
		} else {
			/* 
			 * error: could not get S3 settings
			 * The likely problem here is that somehow an invalid setting was requested,
			 * like a deleted setting without cascade or some other kind of invalid 
			 * or unknown setting. We should consider redirecting to a special-case
			 * error message page so it doesn't just break like a big failure.
			*/
		}
	}
	
	public function getExpiryURL($path,$timeout=1000) {
		return $this->s3->getAuthenticatedURL($this->bucket, $path, $timeout);
		/*
		 * In case of error we should be redirecting to a special-case error message page
		 * as mentioned above. 
		*/
	}
} // END class 
?>