<?php
/**
 * Simple class for interfacing with Donovan Schönknecht's S3 library
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class S3Seed extends SeedBase {
	protected $s3,$bucket='crap';

	public function __construct($use_specific_settings=false) {
		$this->settings_type = 'com.amazon.aws';
		$this->use_specific_settings = $use_specific_settings;
		$this->connectDB();
		if ($this->getSeedSettings()) {
			require_once(SEED_ROOT.'/lib/S3.php');
			$this->s3 = new S3($this->settings->getSetting('key'), $this->settings->getSetting('secret'));
			$this->bucket = $this->settings->getSetting('bucket');
		} else {
			// error: could not get S3 settings
		}
	}
	
	public function getExpiryURL($path,$timeout=1000) {
		return $this->s3->getAuthenticatedURL($this->bucket, $path, $timeout);
	}
} // END class 
?>