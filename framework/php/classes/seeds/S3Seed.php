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
			$s3_key = $this->settings->getSetting('key');
			$s3_secret = $this->settings->getSetting('secret');
			if (!$s3_key || !$s3_secret) {
				$connections = CASHSystem::getSystemSettings('system_connections');
				if (isset($connections['com.amazon'])) {
					$s3_key = $connections['com.amazon']['key'];
					$s3_secret = $connections['com.amazon']['secret'];
				}
			}
			$this->s3 = new S3($s3_key,$s3_secret);
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
		$return_markup = '<h3>Connect to Amazon S3</h3>'
					   . '<p>You\'ll need your S3 key, secret, and a bucket name to proceed. For security reasons '
					   . 'we don\'t store your key and secret — you\'re granting permission to our own account to access the '
					   . 'bucket, which you can revoke any time.</p>'
					   . '<form accept-charset="UTF-8" method="post" action="' . $data . '">'
					   . '<label for="key">Key</label><br /><input type="text" name="key" value="" /><br />'
					   . '<label for="secret">Secret</label><br /><input type="text" name="secret" value="" />'
					   . '<div class="row_seperator">.</div><br />'
					   . '<label for="bucket">Bucket name</label><br /><input type="text" name="bucket" value="" />'
					   . '<div class="row_seperator">.</div><br />'
					   . '<div><input class="button" type="submit" value="Add The Connection" /></div>'
					   . '</form>';
		return $return_markup;
	}

	public static function handleRedirectReturn($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		if (isset($connections['com.amazon'])) {
			$s3_default_email = $connections['com.amazon']['email'];
		} else {
			$s3_default_email = false;
		}
		$success = S3Seed::connectAndAuthorize($data['key'],$data['secret'],$data['bucket'],$s3_default_email);
		if ($success) {
			// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth 
			// calls would only happen in the admin. If this changes we can fuck around with it later.
			$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
			$result = $new_connection->setSettings(
				$data['bucket'] . ' (Amazon S3)',
				'com.amazon',
				array(
					'bucket' => $data['bucket']
				)
			);
			if ($result) {
				AdminHelper::formSuccess('Success. Connection added. You\'ll see it below.','/settings/connections/');
			} else {
				AdminHelper::formFailure('Error. Something just didn\'t work right.','/settings/connections/');
			}
		} else {
			$return_markup = '<h3>Error</h3>'
						   . '<p>We couldn\'t connect with your S3 account. Please check the key, secret, and bucket and try again.</p>';
		}
		return $return_markup;
	}

	public static function connectAndAuthorize($key,$secret,$bucket,$email,$auth_type='FULL_CONTROL') {
		require_once(CASH_PLATFORM_ROOT.'/lib/S3.php');
		$s3_instance = new S3($key,$secret);
		$acp = $s3_instance->getAccessControlPolicy($bucket);
		if (is_array($acp)) {
			$acp['acl'][] = array('email' => $email,'permission'=>$auth_type);
			return $s3_instance->setAccessControlPolicy($bucket,'',$acp);
		} else {
			return false;
		}
	}

	public function getBucketName() {
		return $this->bucket;
	}

	// pass-through to S3 class
	public function getAccessControlPolicy($bucket,$uri='') {
		return $this->s3->getAccessControlPolicy($bucket,$uri);
	}

	// pass-through to S3 class
	public function setAccessControlPolicy($bucket,$uri='',$acp=array()) {
		return $this->s3->setAccessControlPolicy($bucket,$uri,$acp);
	}

	public function authorizeEmailForBucket($bucket,$address,$auth_type='FULL_CONTROL') {
		$acp = $this->s3->getAccessControlPolicy($bucket);
		$acp['acl'][] = array('email' => $address,'permission'=>$auth_type);
		return $this->s3->setAccessControlPolicy($bucket,'',$acp);
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