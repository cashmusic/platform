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

	public function __construct($user_id,$connection_id) {
		$this->settings_type = 'com.amazon';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->connectDB();
		if ($this->getCASHConnection()) {
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
	
	public function getExpiryURL($path,$timeout=1000,$attachment=true,$private=true) {
		$headers = false;
		if ($attachment || $private) {
			$headers = array();
			if ($attachment) {
				$headers['response-content-disposition'] = 'attachment';
			}
			if ($private) {
				$headers['response-cache-control'] = 'no-cache';
			}
		}
		return $this->s3->getAuthenticatedURL($this->bucket, $path, $timeout, false, false, $headers);
		/*
		 * In case of error we should be redirecting to a special-case error message page
		 * as mentioned above. 
		*/
	}
	
	public function uploadFile($local_file,$remote_key=false,$private=true) {
		if ($private) {
			$s3_acl = S3::ACL_PRIVATE;
		} else {
			$s3_acl = S3::ACL_PUBLIC_READ;
		}
		if (!$remote_key) {
			$remote_key = baseName($local_file);
		}
		return $this->s3->putObjectFile($local_file, $this->bucket, $remote_key,$s3_acl);
	}

	public function deleteFile($remote_key) {
		return $this->s3->deleteObject($this->bucket, $remote_key);
	}

	public function getFileDetails($remote_key) {
		return $this->s3->getObjectInfo($this->bucket, $remote_key);
	}

	public function listAllFiles($show_folders=false) {
		$raw_file_list = $this->s3->getBucket($this->bucket);
		if (!$show_folders) {
			$return_array = array();
			foreach ($raw_file_list as $uri => $details) {
				if (substr($uri,-1) !== '/') {
					$return_array[$uri] = $details;
				}
			}
		} else {
			$return_array = $raw_file_list;
		}
		return $return_array;
	}

	public function getAWSSystemTime() {
		return $this->s3->getAWSSystemTime();
	}
} // END class 
?>