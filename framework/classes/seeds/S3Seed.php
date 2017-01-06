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
 * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html#_getObject
 *
 **/
class S3Seed extends SeedBase {
	protected $s3,$bucket='',$s3_key,$s3_secret,$s3_account_id;

	public function __construct($user_id,$connection_id) {
		$this->settings_type = 'com.amazon';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->connectDB();
		if ($this->getCASHConnection()) {
			//require_once(CASH_PLATFORM_ROOT.'/lib/S3.php');
			$this->s3_key    = $this->settings->getSetting('key');
			$this->s3_secret = $this->settings->getSetting('secret');
            $this->s3_account_id = $this->settings->getSetting('account_id');

            $this->bucket = $this->settings->getSetting('bucket');
            $this->bucket_region = $this->settings->getSetting('bucket_region');


			if (!$this->s3_key || !$this->s3_secret) {
				$connections = CASHSystem::getSystemSettings('system_connections');
				if (isset($connections['com.amazon'])) {
					$this->s3_key    = $connections['com.amazon']['key'];
					$this->s3_secret = $connections['com.amazon']['secret'];
                    $this->s3_account_id = $connections['com.amazon']['account_id'];
				}
			}

			$this->s3 = S3Seed::createS3Client($this->s3_key, $this->s3_secret);

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


		if ($bucket_region = S3Seed::connectAndAuthorize($data['key'],$data['secret'],$data['bucket'],$s3_default_email)) {
			// we can safely assume (AdminHelper::getPersistentData('cash_effective_user') as the OAuth
			// calls would only happen in the admin. If this changes we can fuck around with it later.
			$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));

			$connection_name = 'Amazon S3 (created ' . date("M j, Y") . ')';

			$result = $new_connection->setSettings(
				$connection_name,
				'com.amazon',
				array(
					'bucket' => $data['bucket'],
                    'bucket_region' => $bucket_region
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

		$s3_instance = S3Seed::createS3Client($key, $secret, 'us-east-1');

		$system_s3_settings = CASHSystem::getSystemSettings();
		$s3_settings = $system_s3_settings['system_connections']['com.amazon'];

		// check if bucket exists
		if ($s3_instance->doesBucketExist($bucket, true, [])) {

            if (!S3Seed::accountHasACL($s3_instance, $bucket, $s3_settings['account_id'])) {
				S3Seed::putBucketAcl($s3_instance, $bucket, $s3_settings['account_id']);
            }

            S3Seed::setBucketCORS($s3_instance, $bucket);

            $bucket_region = S3Seed::getBucketRegion($s3_instance, $bucket);

            return $bucket_region;

		} else {

			try {
				$s3_instance->createBucket([
					'Bucket' => $bucket,
					'GrantFullControl' => 'id='.$s3_settings['account_id'],
					'LocationConstraint' => 'us-east-1'
				]);

                S3Seed::setBucketCORS($s3_instance, $bucket);

                // make sure the ACLs are set correctly
				if (!S3Seed::accountHasACL($s3_instance, $bucket, $s3_settings['account_id'])) {
					return false;
				}

			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}

			return 'us-east-1';
		}
	}

    /**
     * @param $bucket
     * @param $s3_instance
     */
    public static function setBucketCORS($s3_instance, $bucket)
    {
        try {
            $s3_instance->putBucketCors([
                // Bucket is required
                'Bucket' => $bucket,
                // CORSRules is required
                'CORSConfiguration' => ['CORSRules' => [
						[
							'AllowedHeaders' => ['*'],
							'AllowedMethods' => ['PUT', 'POST', 'DELETE', 'GET', 'HEAD'],
							'AllowedOrigins' => ['*'],
                            'MaxAgeSeconds' => 3000
						]

					]
				]
            ]);
		} catch (Exception $e) {
        	error_log($e->getMessage());
        	return false;
		}

		return true;
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

	public function authorizeEmailForBucket($bucket,$address,$auth_type='FULL_CONTROL') {
		$acp = $this->s3->getAccessControlPolicy($bucket);
		$acp['acl'][] = array('email' => $address,'permission'=>$auth_type);
		return $this->s3->setAccessControlPolicy($bucket,'',$acp);
	}

	public function getExpiryURL($path,$timeout=1000,$attachment=true,$private=true,$mime_type=true) {
		// TODO: move all options after location to a parameters array, so we can have a unified footprint across seeds.
		$headers = false;
		if ($attachment || $private) {
			$headers = array(
                'Bucket' => $this->bucket,
                'Key' => $path,
			);

			if ($attachment) {
				$headers['ResponseContentDisposition'] = 'attachment; filename="'.basename($path).'"';
			} else {
                $headers['ResponseContentDisposition'] = 'filename="'.basename($path).'"';
			}

			if ($private) {
				$headers['ResponseCacheControl'] = 'no-cache';
			} else {
				$headers['ResponseExpires'] = 'Expires: Fri, 15 Apr '.date('Y', strtotime("+20 years")).' 20:00:00 GMT';
			}

			if ($mime_type && $mime_type !== true) {
				$headers['ResponseContentType'] = $mime_type;
			} else if ($mime_type === true) {
                $headers['ResponseContentType'] = CASHSystem::getMimeTypeFor($path);
			}
		}

        $command = $this->s3->getCommand('GetObject', $headers);

        return $this->s3->createPresignedUrl($command, '+'.($timeout/60).' minutes');
	}

	public function uploadFile($local_file,$remote_key=false,$private=true,$content_type='application/octet-stream') {

        $filename = strtolower(preg_replace('/[^a-zA-Z 0-9.\-\/]+/','',
            	(($remote_key) ? $remote_key : basename($local_file))
			));

        $headers = [
            'ACL' => (($private) ? "private" : "public"),
            'Body' => file_get_contents($local_file),
            'Bucket' => $this->bucket,
            'ContentDisposition' => 'filename="'.basename($filename).'"',
            'ContentLength' => filesize($local_file),
            'ContentType' => $content_type,
            'Key' => $filename
        ];

        if ($private) {
            $headers['CacheControl'] = 'no-cache';
        } else {
            $headers['Expires'] = 'Expires: Fri, 15 Apr '.date('Y', strtotime("+20 years")).' 20:00:00 GMT';
        }

        return $this->s3->putObject($headers);
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

	public function makePublic($filename) {
        return 'https://s3.amazonaws.com/' . $filename; //$this->bucket . '/' .
	}

	public function deleteFile($remote_key) {
		return $this->s3->deleteObject([
			'Bucket' => $this->bucket,
			'Key'	 => $remote_key
		]);
	}

	public function getFileDetails($remote_key) {
		return $this->s3->getObject([
			'Bucket' => $this->bucket,
			'Key' => $remote_key
		]);
	}

	public function listAllFiles($show_folders=false) {
		$raw_file_list = $this->s3->getIterator('ListObjects', array(
            'Bucket' => $this->bucket
        ));

		if (!$show_folders) {
			$return_array = array();
			foreach ($raw_file_list as $file) {
					$return_array[$file['Key']] = $file['Key'];
			}
		} else {
			$return_array = $raw_file_list;
		}
		return $return_array;
	}

	public function getUploadParameters($acl=false, $key_preface=false,$success_url=200,$for_flash=false) {

        if (!$acl) $acl = "private";

		if (!$key_preface) {
			$key_preface = 'cashmusic-' . $this->connection_id . $this->settings->creation_date . '/' . time() . '/';
		}

		if (substr($key_preface, -1) != '/') {
			$key_preface .= '/';
		}

        $upload = new \EddTurtle\DirectUpload\Signature(
        	$this->s3_key,
			$this->s3_secret,
			$this->bucket,
			$this->bucket_region,
            ['acl'=>$acl]
		);

		return [
			'upload_url' => $upload->getFormUrl(),
            'inputs' => $upload->getFormInputsAsHtml(),
			'bucketname' => $this->bucket,
			'connection_type' => $this->settings_type,
			'key_preface' => $key_preface,
			'acl' => 'private',
			'lifetime' => 1200,
			'max_size' => 5000000000,
			'success' => $success_url,
			's3_key' => $this->s3_key
		];
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

	public function finalizeUpload($arg) {
		return true;
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
		    error_log($e->getMessage());
			return false;
		}

		return $bucket_location['LocationConstraint'];
	}
} // END class
?>
