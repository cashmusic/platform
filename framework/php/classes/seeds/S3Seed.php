<?php
/**
 * Simple class for interfacing with Donovan Schönknecht's S3 library
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
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

	public static function getRedirectMarkup($data=false) {
		return '<b>popup stuff</b>';
	}

	public static function handleRedirectReturn($data=false) {

	}

	public function getBucketName() {
		return $this->bucket;
	}
	
	public function getExpiryURL($path,$timeout=1000,$attachment=true,$private=true,$mime_type=true) {
		$headers = false;
		if ($attachment || $private) {
			$headers = array();
			if ($attachment) {
				$headers['response-content-disposition'] = 'attachment';
			}
			if ($private) {
				$headers['response-cache-control'] = 'no-cache';
			}
			if ($mime_type && $mime_type !== true) {
				$headers['response-content-type'] = $mime_type;	
			} else if ($mime_type === true) {
				CASHSystem::getMimeTypeFor($path);
			}
		}
		return $this->s3->getAuthenticatedURL($this->bucket, $path, $timeout, false, false, $headers);
		/*
		 * In case of error we should be redirecting to a special-case error message page
		 * as mentioned above. 
		*/
	}
	
	public function uploadFile($local_file,$remote_key=false,$private=true,$content_type='application/octet-stream') {
		if ($private) {
			$s3_acl = S3::ACL_PRIVATE;
		} else {
			$s3_acl = S3::ACL_PUBLIC_READ;
		}
		if (!$remote_key) {
			$remote_key = baseName($local_file);
		}
		return $this->s3->putObjectFile($local_file, $this->bucket, $remote_key, $s3_acl, array(), $content_type);
	}

	public function createFileFromString($contents,$remote_key,$private=true,$content_type='text/plain') {
		if ($private) {
			$s3_acl = S3::ACL_PRIVATE;
		} else {
			$s3_acl = S3::ACL_PUBLIC_READ;
		}
		if (!$remote_key) {
			return false;
		} else {
			return $this->s3->putObjectString($contents, $this->bucket, $remote_key, $s3_acl, array(), $content_type);
		}
	}

	public function changeFileMIME($filename,$content_type,$private=true) {
		if ($private) {
			$s3_acl = S3::ACL_PRIVATE;
		} else {
			$s3_acl = S3::ACL_PUBLIC_READ;
		}
		return $this->s3->copyObject(
			$this->bucket, $filename, $this->bucket, $filename, $s3_acl,
			array(), 
			array("Content-Type" => $content_type)
		);
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

	public function getPOSTUploadParams($key_preface='',$success_url=200,$for_flash=false) {
		if (substr($key_preface, -1 && $key_preface) != '/') {
			$key_preface .= '/';
		}
		$upload_url = 'https://' . $this->bucket . '.s3.amazonaws.com/';
		$params = false;
		if (!$for_flash) {
			$params = $this->s3->getHttpUploadPostParams(
				$this->bucket,
				$key_preface,
				S3::ACL_PRIVATE,
				1200,
				1610612736,
				$success_url
			);
		} else {
			$params = $this->s3->getHttpUploadPostParams(
				$this->bucket,
				$key_preface,
				S3::ACL_PRIVATE,
				1200,
				2147483648,
				201,
				array(),
				array(),
				true
			);
		}
		return $params;
	}

	public function getPOSTUploadHTML($key_preface='',$success_url=200,$for_flash=false) {
		$params = $this->getPOSTUploadParams($key_preface,$success_url,$for_flash);
		
		if (!$params) { 
			return false;
		} else {
			$output_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
						 . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>'
						 . '<title>CASH Music upload</title>'
						 . '<style type="text/css">'
						 . 'html, body {margin:0;padding:0;}'
						 . 'input,textarea,select {font:italic 14px/1.25em georgia, times, serif !important;border:1px solid #dddddf;width:auto;padding:8px;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;}'
						 . 'input:active, input:focus, textarea:focus {outline:0;border:1px solid #888;}'
						 . 'input.button {background-color:#df0854;padding:8px 18px 8px 18px !important;font:bold 14px/1.25em helvetica,"segoe ui","segoe wp",arial,sans-serif !important;cursor:pointer;width:auto !important;border:1px solid transparent;color:#fff;-webkit-transition: all 0.25s ease-in-out;-moz-transition: all 0.25s ease-in-out;-o-transition: all 0.25s ease-in-out;transition: all 0.25s ease-in-out;}'
						 . 'input.button:hover {background-color:#000 !important;color:#fff;}'
						 . '</style>'
						 . '</head><body>'
						 . '<form method="post" action="' . $upload_url . '" enctype="multipart/form-data">';
			foreach ($params as $p => $v) {
				$output_html .= "<input type=\"hidden\" name=\"{$p}\" value=\"{$v}\" />\n";
			}
			$output_html .= '<input type="file" name="file" /><br /><input type="submit" class="button" value="Upload" /></form></body></html>';
			return $output_html;
		}
	}

	public function getAWSSystemTime() {
		return $this->s3->getAWSSystemTime();
	}
} // END class 
?>