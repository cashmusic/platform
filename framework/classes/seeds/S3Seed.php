<?php
/**
 * Simple class for interfacing with Donovan Schönknecht's S3 library
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
 * This file is generously sponsored by Miles Fender - http://www.streetlightfarm.com
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
			//require_once(CASH_PLATFORM_ROOT.'/lib/S3.php');
			$s3_key    = $this->settings->getSetting('key');
			$s3_secret = $this->settings->getSetting('secret');
            $s3_account_id = $this->settings->getSetting('account_id');

			if (!$s3_key || !$s3_secret) {
				$connections = CASHSystem::getSystemSettings('system_connections');
				if (isset($connections['com.amazon'])) {
					$s3_key    = $connections['com.amazon']['key'];
					$s3_secret = $connections['com.amazon']['secret'];
                    $s3_account_id = $connections['com.amazon']['account_id'];
				}
			}

			$this->s3 = S3Seed::createS3Client($s3_key, $s3_secret);
			$this->bucket = $this->settings->getSetting('bucket');

			$this->bucket_region = $this->settings->getSetting('bucket_region');

			if (empty($this->bucket_region) && !empty($this->bucket)) {
				$this->bucket_region = S3Seed::getBucketRegion($this->s3, $this->bucket);
			}

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

	/*
	ASSET-SCOPE SEED REQUIRED FUNCTIONS
	(if they aren't relevant simply return true)

	finalizeUpload($filename)
	getExpiryURL($filename)
	getUploadParameters()
	makePublic($filename)
	*/

	public static function getRedirectMarkup($data=false) {
		// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth
		// calls would only happen in the admin. If this changes we can fuck around with it later.
		$return_markup = '<h4>Amazon S3</h4>'
					   . '<p>You\'ll need your S3 key and secret to proceed. For security reasons '
					   . 'we don\'t store your key or secret — you\'re granting permission to our own account to access the '
					   . 'bucket, which you can revoke any time.</p>'
					   . '<form accept-charset="UTF-8" method="post" action="' . $data . '">'
					   . '<label for="key">Key</label><input type="text" name="key" value="" /><br />'
					   . '<label for="secret">Secret</label><input type="text" name="secret" value="" /><br />'
					   . '<input type="hidden" name="bucket" value="cashmusic-' . AdminHelper::getPersistentData('cash_effective_user') . '-' . time() . '" /><br />'
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

			$connection_name = $data['bucket'] . ' (Amazon S3)';
			if (substr($connection_name, 0, 10) == 'cashmusic.') {
				$connection_name = 'Amazon S3 (created ' . date("M j, Y") . ')';
			}
			$result = $new_connection->setSettings(
				$connection_name,
				'com.amazon',
				array(
					'bucket' => $data['bucket']
				)
			);
			if ($result) {
				AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
			} else {
				AdminHelper::formFailure('Error. Something just didn\'t work right.');
			}
		} else {
			//$return_markup = '<h4>Error</h4>'
			//			   . '<p>We couldn\'t connect with your S3 account. Please check the key and secret.</p>';
			AdminHelper::formFailure('We couldn\'t connect your S3 account. Please check the key and secret.');
		}
		return true;
	}

	public static function connectAndAuthorize($key,$secret,$bucket,$email,$auth_type='FULL_CONTROL') {

/*        $shit = new \Aws\Sts\StsClient([
            'version'     => '2011-06-15',
            'region'      => 'us-east-1',
            'credentials' => [
                'key'    => $key,
                'secret' => $secret
            ]
        ]);

        error_log("stsclient: ". print_r(
            $shit->GetCallerIdentity([]), true
        ));*/

		$s3_instance = S3Seed::createS3Client($key, $secret, 'us-east-1');
		$bucket_region = S3Seed::getBucketRegion($s3_instance, $bucket);

		$system_s3_settings = CASHSystem::getSystemSettings();
		$s3_settings = $system_s3_settings['system_connections']['com.amazon'];

		// check if bucket exists
		if ($s3_instance->doesBucketExist($bucket, true, [
			'@region' => $bucket_region
		])) {

			//TODO: when does this happen?
            if (!S3Seed::accountHasACL($s3_instance, $bucket, $s3_settings['account_id'])) {
				S3Seed::putBucketAcl($s3_instance, $bucket, $s3_settings['account_id']);
            }

		} else {

			try {
				$new_bucket = $s3_instance->createBucket([
					'Bucket' => $bucket,
					'GrantFullControl' => 'id='.$s3_settings['account_id']
				]);

                // make sure the ACLs are set correctly
				if (!S3Seed::accountHasACL($s3_instance, $bucket, $s3_settings['account_id'])) {
					return false;
				}

			} catch (Exception $e) {
				return false;
			}

			return $new_bucket;
		}
	}

	public function getBucketName() {
		return $this->bucket;
	}

	// pass-through to S3 class
	public static function getBucketAcl($s3_instance, $bucket) {

		$bucket_acl = $s3_instance->getBucketAcl([
            'Bucket' => $bucket
        ]);

		if ($bucket_acl) {
			// for now let's just send them the first key if it exists
			if (!empty($bucket_acl['Grants'])) {
				return $bucket_acl['Grants'];
			}

			return false;
		}

		return false;
	}

	public static function putBucketAcl($s3_instance, $bucket, $account_id) {

		$bucket_acl = $s3_instance->putBucketAcl([
			'Bucket' => $bucket,
            'GrantFullControl' => 'id='.$account_id

		]);
	}

	public static function accountHasACL($s3_instance, $bucket, $account_id) {
        $acl = S3Seed::getBucketAcl($s3_instance, $bucket);

        foreach($acl as $grants) {
        	if ($grants['Grantee']['ID'] == $account_id) {
        		return true;
			}
		}

		return false;
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
		// TODO:
		// move all options after location to a parameters array, so we can have a unified
		// footprint across seeds.
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

	public function prepareFileMetadata($filename,$content_type,$private=true) {
		if ($private) {
			$s3_acl = S3::ACL_PRIVATE;
		} else {
			$s3_acl = S3::ACL_PUBLIC_READ;
		}
		//$new_filename = strtolower(preg_replace('/[^a-zA-Z 0-9.\-\/]+/','',$filename));
		//error_log('1: '.$filename);
		//error_log('2: '.strtolower(preg_replace('/[^a-zA-Z 0-9.\-\/]+/','',$filename)));
		$new_filename = $filename;
		return $this->s3->copyObject(
			$this->bucket, $filename, $this->bucket,
			$new_filename,
			$s3_acl,
			array(),
			array("Content-Type" => $content_type, "Content-Disposition" => 'attachment')
		);
	}

	public function finalizeUpload($filename) {
		$content_type = CASHSystem::getMimeTypeFor($filename);
		return $this->prepareFileMetadata($filename,$content_type);
	}

	public function makePublic($filename) {
		$content_type = CASHSystem::getMimeTypeFor($filename);
		if ($this->prepareFileMetadata($filename,$content_type,false)) {
			return 'https://s3.amazonaws.com/' . $this->getBucketName() . '/' . $filename;
		} else {
			return false;
		}
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

	public function getUploadParameters($key_preface=false,$success_url=200,$for_flash=false) {
		if (!$key_preface) {
			$key_preface = 'cashmusic-' . $this->connection_id . $this->settings->creation_date . '/' . time() . '/';
		}
		if (substr($key_preface, -1) != '/') {
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
		$return_array = (array) $params;
		$return_array['bucketname'] = $this->bucket;
		$return_array['connection_type'] = $this->settings_type;
		return $return_array;
	}

	public function getPOSTUploadHTML($key_preface='',$success_url=200,$for_flash=false) {
		$params = $this->getUploadParameters($key_preface,$success_url,$for_flash);
		$upload_url = 'https://' . $this->bucket . '.s3.amazonaws.com/';

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

	/**
	 * @param $s3_key
	 * @param $s3_secret
	 */
	public static function createS3Client($s3_key, $s3_secret, $region='us-east-1')
	{
// this is dumb but it's how the new SDK works
		try {
			$s3_client = new \Aws\S3\S3MultiRegionClient([
				'version'     => '2006-03-01',
				'region'      => $region,
				'credentials' => [
					'key'    => $s3_key,
					'secret' => $s3_secret
				]
			]);

		} catch (Exception $e) {
			return false;
		}

		return $s3_client;
	}

	public static function getBucketRegion($s3_client, $bucket_name) {
		try {
			$bucket_location = $s3_client->getBucketLocation([
				'Bucket' => $bucket_name
			]);
		} catch (Exception $e) {
			return false;
		}
		error_log("#######".$bucket_location['LocationConstraint']);
		return $bucket_location['LocationConstraint'];
	}
} // END class
?>
