<?php
/**
 * An abstract collection of lower level static functions that are useful
 * across other classes.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 */abstract class CASHSystem  {

	/**
	 * Handle annoying environment issues like magic quotes, constants and
	 * auto-loaders before firing up the CASH platform and whatnot
	 *
	 */public static function startUp($return_request=false) {
	 	// set errors and logging
	 	ini_set('error_reporting',E_ALL);
		ini_set('log_errors',TRUE);
		ini_set('display_errors',FALSE);

		// only want to do this once, so we check for 'initial_page_request_time'
		if (!isset($GLOBALS['cashmusic_script_store']['initial_page_request_time'])) {
			// remove magic quotes, never call them "magic" in front of your friends
			if (get_magic_quotes_gpc()) {
				function stripslashes_from_gpc(&$value) {$value = stripslashes($value);}
				$gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
				array_walk_recursive($gpc, 'stripslashes_from_gpc');
				unset($gpc);
			}

			// define constants (use sparingly!)
			$root = realpath(dirname(__FILE__) . '/../..');
			define('CASH_PLATFORM_ROOT', $root);
			$cash_settings = CASHSystem::getSystemSettings();
			define('CASH_API_URL', trim($cash_settings['apilocation'],'/'));
			define('CASH_ADMIN_URL', str_replace('/api','/admin',CASH_API_URL));
			define('CASH_PUBLIC_URL',str_replace('/api','/public',CASH_API_URL));

			// venues api or bust
			if (!isset($cash_settings['venues_api'])) {
				define('CASH_VENUES_API', "https://venues.cashmusic.org/");
			} else {
				define('CASH_VENUES_API', trim($cash_settings['venues_api'],'/'));
			}

			define('CASH_DEBUG',(bool)$cash_settings['debug']);
			// set up auto-load
			spl_autoload_register('CASHSystem::autoloadClasses');
			// composer autoloader, for seeds initially
			require_once(CASH_PLATFORM_ROOT . '/library/autoload.php');

			// set timezone
			date_default_timezone_set($cash_settings['timezone']);

			// fire off new CASHRequest to cover any immediate-need things like GET
			// asset requests, etc...
			$cash_page_request = new CASHRequest();
			if (!empty($cash_page_request->response)) {
				$cash_page_request->sessionSet(
					'initial_page_request',
					array(
						'request' => $cash_page_request->request,
						'response' => $cash_page_request->response,
						'status_uid' => $cash_page_request->response['status_uid']
					),
					'script'
				);
			}
			$cash_page_request->sessionSet('initial_page_request_time',time(),'script');
			if ($return_request) {
				return $cash_page_request;
			} else {
				unset($cash_page_request);
			}
		}
	}

	/**
	 * Starts a persistent CASH session in the database, with corresponding cookie
	 *
	 * @return none
	 */public static function startSession($force_session_id=false,$sandbox=false) {
		$cash_page_request = new CASHRequest(null);
		$session = $cash_page_request->startSession($force_session_id,$sandbox);
		unset($cash_page_request);
		return($session);
	}

	/**
	 * The main public method to embed elements. Notice that it echoes rather
	 * than returns, because it's meant to be used simply by calling and spitting
	 * out the needed code...
	 *
	 * @return none
	 */public static function embedElement($element_id,$access_method='direct',$location=false,$geo=false,$donottrack=false) {
		// fire up the platform sans-direct-request to catch any GET/POST info sent
		// in to the page
		CASHSystem::startSession();
		$cash_page_request = new CASHRequest(null);
		$initial_page_request = $cash_page_request->sessionGet('initial_page_request','script');
		if ($initial_page_request && isset($initial_page_request['request']['element_id'])) {
			// now test that the initial POST/GET was targeted for this element:
			if ($initial_page_request['request']['element_id'] == $element_id) {
				$status_uid = $initial_page_request['response']['status_uid'];
				$original_request = $initial_page_request['request'];
				$original_response = $initial_page_request['response'];
			} else {
				$status_uid = false;
				$original_request = false;
				$original_response = false;
			}
		} else {
			$status_uid = false;
			$original_request = false;
			$original_response = false;
		}
		$cash_body_request = new CASHRequest(
			array(
				'cash_request_type' => 'element',
				'cash_action' => 'getmarkup',
				'id' => $element_id,
				'status_uid' => $status_uid,
				'original_request' => $original_request,
				'original_response' => $original_response,
				'access_method' => $access_method,
				'location' => $location,
				'geo' => $geo,
				'donottrack' => $donottrack
			)
		);
		if ($cash_body_request->response['status_uid'] == 'element_getmarkup_400') {
			// there was no element found. so you know...punt
			echo '<div class="cashmusic error">Element #' . $element_id . ' could not be found.</div>';
		}
		if (is_string($cash_body_request->response['payload'])) {
			// element found and happy. spit it out.
			echo $cash_body_request->response['payload'];
		}
		if ($cash_body_request->sessionGet('initialized_element_' . $element_id,'script')) {
			// second half of a wrapper element — fringe case
			if (ob_get_level()) {
				ob_flush();
			}
		}
		unset($cash_page_request);
		unset($cash_body_request);
	}

	/**
	 * Gets the contents from a URL. First tries file_get_contents then cURL.
	 * If neither of those work, then the server asks a friend to print out the
	 * page at the URL and mail it to the data center. Since this takes a couple
	 * days we return false, but that's taking nothing away from the Postal
	 * service. They've got a hard job, so say thank you next time you get some
	 * mail from the postman.
	 *
	 * @return string
	 */public static function getURLContents($data_url,$post_data=false,$ignore_errors=false,$headers=false) {
		$url_contents = false;
		$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.5; rv:7.0) Gecko/20100101 Firefox/7.0';
		$do_post = is_array($post_data);
		if ($do_post) {
			$post_query = http_build_query($post_data);
			$post_length = count($post_data);
		}
		if (ini_get('allow_url_fopen')) {
			// try with fopen wrappers
			$options = array(
				'http' => array(
					'protocol_version'=>'1.1',
					'header'=>array(
						'Connection: close'
					),
					'user_agent' => $user_agent
				));
			if ($do_post) {
				$options['http']['method'] = 'POST';
				$options['http']['content'] = $post_query;
			}
			if ($ignore_errors) {
				$options['http']['ignore_errors'] = true;
			}
			$context = stream_context_create($options);
			$url_contents = @file_get_contents($data_url,false,$context);
		} elseif (in_array('curl', get_loaded_extensions())) {
			// fall back to cURL
			$ch = curl_init();
			$timeout = 20;

			@curl_setopt($ch,CURLOPT_URL,$data_url);

			if ($headers) {
				@curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			}

			if ($do_post) {
				curl_setopt($ch,CURLOPT_POST,$post_length);
				curl_setopt($ch,CURLOPT_POSTFIELDS,$post_query);
			}
			@curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			@curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			@curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			if ($ignore_errors) {
				@curl_setopt($ch, CURLOPT_FAILONERROR, false);
			} else {
				@curl_setopt($ch, CURLOPT_FAILONERROR, true);
			}
			$data = curl_exec($ch);
			curl_close($ch);
			$url_contents = $data;
		}
		return $url_contents;
	}

	/**
	 * If the function name doesn't describe what this one does well enough then
	 * seriously: you need to stop reading the comments and not worry about it
	 *
	 */public static function autoloadClasses($classname) {
		foreach (array('/classes/core/','/classes/seeds/') as $location) {
			$file = CASH_PLATFORM_ROOT.$location.$classname.'.php';
			if (file_exists($file)) {
				// using 'include' instead of 'require_once' because of efficiency
				include($file);
			}
		}
	}

	/**
	 * Gets API credentials for the effective or actual user
	 *
	 * @param {string} effective || actual
	 * @return array
	 */public static function getAPICredentials($user_type='effective') {
		$data_request = new CASHRequest(null);
		$user_id = $data_request->sessionGet('cash_' . $user_type . '_user');
		if ($user_id) {
			$data_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'getapicredentials',
					'user_id' => $user_id
				)
			);
			return $data_request->response['payload'];
		}
		return false;

	}

	/**
	 * Very basic. Takes a URL and checks if headers have been sent. If not we
	 * do a proper location header redirect. If headers have been sent it
	 * returns a line of JS to initiate a client-side redirect.
	 *
	 * @return none
	 */public static function redirectToUrl($url) {
		if (!headers_sent()) {
			header("Location: $url");
			exit;
		} else {
			$output_script = '<script type="text/javascript">window.location = "' . $url . '";</script>';
			echo $output_script;
			return $output_script;
		}
	}

	public static function findReplaceInFile($filename,$find,$replace) {
		if (is_file($filename)) {
			$file = file_get_contents($filename);
			$file = str_replace($find, $replace, $file);
			if (file_put_contents($filename, $file)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function getSystemSettings($setting_name='all') {
		$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
		if (!$cash_settings) {
			$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
			// check for system connections in environment and on file
			$system_connections = json_decode(getenv('cashmusic_system_connections'),true);
			if (!$system_connections && file_exists(CASH_PLATFORM_ROOT.'/settings/connections.json')) {
				$system_connections = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/connections.json'),true);
			}
			if ($system_connections) {
				$cash_settings['system_connections'] = $system_connections;
			}
			// put all the settings into the current environment
			$json_settings = json_encode($cash_settings);
			putenv("cashmusic_platform_settings=$json_settings");
		} else {
			// so we found the environment variable — if we're on file-based settings the 'system_connections'
			// would be set. if we're purely environment variables they wouldn't be present, so we add them
			if (!isset($cash_settings['system_connections'])) {
				if (file_exists(CASH_PLATFORM_ROOT.'/settings/connections.json')) {
					$cash_settings['system_connections'] = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/connections.json'),true);
				}
				$system_connections = json_decode(getenv('cashmusic_connection_settings'),true);
				if ($system_connections) {
					$cash_settings['system_connections'] = $system_connections;
				}
			}
		}
		if ($setting_name == 'all') {
			return $cash_settings;
		} else {
			if (array_key_exists($setting_name, $cash_settings)) {
				return $cash_settings[$setting_name];
			} else {
				return false;
			}
		}
	}

	public static function setSystemSetting($setting_name=false,$value='') {
		if ($setting_name) {
			$cash_settings = CASHSystem::getSystemSettings();
			// if ($cash_settings['instancetype'] != 'multi') { --- DISABLED SINGLE-USER ONLY BECAUSE WHY?
				if (array_key_exists($setting_name, $cash_settings)) {
					$success = CASHSystem::findReplaceInFile(
						CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php',
						$setting_name . ' = "' . $cash_settings[$setting_name],
						$setting_name . ' = "' . $value
					);
					if ($success) {
						$cash_settings[$setting_name] = $value;
						$json_settings = json_encode($cash_settings);
						putenv("cashmusic_platform_settings=$json_settings");
						return true;
					}
				}
			//}
			return false;
		} else {
			return false;
		}
	}

	/**
	 * Super basic XOR encoding — used for encoding connection data
	 *
	 */public static function simpleXOR($input, $key = false) {
		if (!$key) {
			$key = CASHSystem::getSystemSettings('salt');
		}
		// append key on itself until it is longer than the input
		while (strlen($key) < strlen($input)) { $key .= $key; }

		// trim key to the length of the input
		$key = substr($key, 0, strlen($input));

		// Simple XOR'ing, each input byte with each key byte.
		$result = '';
		for ($i = 0; $i < strlen($input); $i++) {
			$result .= $input{$i} ^ $key{$i};
		}
		return $result;
	}

	/**
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public static function getRemoteIP() {
		$proxy = '';
		if(!defined('STDIN')) {
			if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
					$proxy = $_SERVER["HTTP_CLIENT_IP"];
				} else {
					$proxy = $_SERVER["REMOTE_ADDR"];
				}
				$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else {
				if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
					$ip = $_SERVER["HTTP_CLIENT_IP"];
				} else {
					$ip = $_SERVER["REMOTE_ADDR"];
				}
			}
		} else {
			$ip = 'local';
		}
		$ip_and_proxy = array(
			'ip' => $ip,
			'proxy' => $proxy
		);
		return $ip_and_proxy;
	}

	/**
	 * Returns the (best guess at) current URL or false for CLI access
	 *
	 * @return array
	 */public static function getCurrentURL($domain_only=false) {
		if(!defined('STDIN')) { // check for command line
			if ($domain_only) {
				return strtok($_SERVER['HTTP_HOST'],':');
			} else {
				$protocol = 'http';
				if (isset($_SERVER['HTTPS'])) {
					if ($_SERVER['HTTPS']!=="off" || !$_SERVER['HTTPS']) {
						$protocol = 'https';
					}
				}
				if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
					if ($_SERVER['HTTP_X_FORWARDED_PROTO']=="https") {
						$protocol = 'https';
					}
				}
				$root = $protocol.'://'.$_SERVER['HTTP_HOST'];
				$page = strtok($_SERVER['REQUEST_URI'],'?');
				return $root.$page;
			}
		} else {
			return false;
		}
	}

	/**
	 * Takes a datestamp or a string capable of being converted to a datestamp and
	 * returns a "23 minutes ago" type string for it. Now you can be cute like
	 * Twitter.
	 *
	 * @return string
	 */public static function formatTimeAgo($time,$long=false) {
		if (is_string($time)) {
			if (is_numeric($time)) {
				$datestamp = (int) $time;
			} else {
				$datestamp = strtotime($time);
			}
		} else {
			$datestamp = $time;
		}
		$seconds = floor((time() - $datestamp));
		if ($seconds < 60) {
			$ago_str = $seconds . ' seconds ago';
		} else if ($seconds >= 60 && $seconds < 120) {
			$ago_str = '1 minute ago';
		} else if ($seconds >= 120 && $seconds < 3600) {
			$ago_str = floor($seconds / 60) .' minutes ago';
		} else if ($seconds >= 3600 && $seconds < 7200) {
			$ago_str = '1 hour ago';
		} else if ($seconds >= 7200 && $seconds < 86400) {
			$ago_str = floor($seconds / 3600) .' hours ago';
		} else if ($seconds >= 86400 && $seconds < 31536000) {
			if ($long) {
				$ago_str = date('l, F d', $datestamp);
			} else {
				$ago_str = date('d M', $datestamp);
			}
		} else {
			if ($long) {
				$ago_str = date('l, F d, Y', $datestamp);
			} else {
				$ago_str = date('d M, y', $datestamp);
			}
		}
		return $ago_str;
	}

	public static function formatCount($val) {
		if ($val > 999 && $val <= 99999) {
			$result  = round((float)$val / 1000, 1) . 'K';
		} elseif ($val > 99999 && $val <= 999999) {
			$result  = floor($val / 1000) . 'K';
		} elseif ($val > 999999) {
			$result  = round((float)$val / 1000000, 1) . 'M';
		} else {
			$result  = $val;
		}
		return $result;
	}

	/**
	 * Turns plain text links into HYPERlinks. Welcome to the future, chump.
	 *
	 * Stole all the regex from:
	 * http://buildinternet.com/2010/05/how-to-automatically-linkify-text-with-php-regular-expressions/
	 * (Because I stink at regex.)
	 *
	 * @return string
	 */public static function linkifyText($text,$twitter=false) {
		if ($twitter) {
			$text = preg_replace("/@(\w+)/", '<a href="https://twitter.com/$1" target="_blank">@$1</a>', $text);
			$text = preg_replace("/\#(\w+)/", '<a href="https://search.twitter.com/search?q=$1" target="_blank">#$1</a>',$text);
			if (is_array($twitter)) {
				foreach ($twitter as $url_details) {
					$text = str_replace($url_details->url,'<a href="' . $url_details->expanded_url . '" target="_blank">' . $url_details->display_url . '</a>',$text);
				}
				return $text;
			}
		}
		$text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\">$3</a>", $text);
		$text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\">$3</a>", $text);
		$text = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
		return $text;
	}

	/*
	 * Returns the system default email address from the settings ini file
	 *
	 * USAGE:
	 * ASHSystem::getDefaultEmail();
	 *
	 */public static function getDefaultEmail($all_settings=false) {
		$cash_settings = CASHSystem::getSystemSettings();
		if (!$all_settings) {
			return $cash_settings['systememail'];
		} else {
			$return_array = array(
				'systememail' => $cash_settings['systememail'],
				'smtp' => $cash_settings['smtp'],
				'smtpserver' => $cash_settings['smtpserver'],
				'smtpport' => $cash_settings['smtpport'],
				'smtpusername' => $cash_settings['smtpusername'],
				'smtppassword' => $cash_settings['smtppassword'],
			);
			return $return_array;
		}

	}

	public static function notExplicitFalse($test) {
		if ($test === false) {
			return false;
		} else {
			return true;
		}
	}

	public static function parseEmailAddress($address) {
		if (strpos($address, '>')) {
			preg_match('/([^<]+)\s<(.*)>/', $address, $matches);
			if (count($matches)) {
				$finaladdress = array($matches[2] => $matches[1]);
			} else {
				$finaladdress = $address;
			}
		} else {
			$finaladdress = $address;
		}
		return $finaladdress;
	}

	/**
	 * Sends a plain text and HTML email for system things like email verification,
	 * password resets, etc.
	 *
	 * USAGE:
	 * CASHSystem::sendEmail('test email','CASH Music <info@cashmusic.org>','dev@cashmusic.org','message, with link: http://cashmusic.org/','title');
	 *
	 */
	public static function sendEmail($subject,$user_id,$toaddress,$message_text,$message_title,$encoded_html=false) {
		// pulling out just the TO email from a 'Address Name <address@name.com>' style address:
		if (strpos($toaddress, '>')) {
			preg_match('/([^<]+)\s<(.*)>/', $toaddress, $matches);
			if (count($matches)) {
				$toaddress = $matches[2];
			}
		}
		// if the email is bullshit don't try to send to it:
		if (!filter_var($toaddress, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		// TODO: look up user settings for email if user_id is set — allow for multiple SMTP settings
		// on a per-user basis in the multi-user system
		$email_settings = CASHSystem::getDefaultEmail(true);
		if ($email_settings['smtp'] && $email_settings['smtpserver']) {
			if (CASHSystem::getSystemSettings('instancetype') == 'multi' && $user_id) {
				$user_request = new CASHRequest(
					array(
						'cash_request_type' => 'people',
						'cash_action' => 'getuser',
						'user_id' => $user_id
					)
				);
				$user_details = $user_request->response['payload'];
				$setname = false;
				if (trim($user_details['display_name'] . '') !== '' && $user_details['display_name'] !== 'Anonymous') {
					$setname = $user_details['display_name'];
				}
				if (!$setname && $user_details['username']) {
					$setname = $user_details['username'];
				}
				if ($setname) {
					$fromaddress = '"' . str_replace('"','\"',$setname) . '" <' . $user_details['email_address'] . '>';
				} else {
					$fromaddress = $user_details['email_address'];
				}
			} else {
				$fromaddress = $email_settings['systememail'];
			}

			// let's deal with complex versus simple email addresses. if we find '>' present we try
			// parsing for name + address from a 'Address Name <address@name.com>' style email:
			$from = CASHSystem::parseEmailAddress($fromaddress);
			$sender = CASHSystem::parseEmailAddress($email_settings['systememail']);

			if (is_array($from) && is_array($sender)) {
				// sets the display name as the username NOT the system name
				$from_keys = array_keys($from);
				$sender_keys = array_keys($sender);
				$sender[$sender_keys[0]] = $from[$from_keys[0]];
			}

			// handle encoding of HTML if specific HTML isn't passed in:
			if (!$encoded_html) {

				$message_text = CASHSystem::parseMarkdown($message_text);

				if ($template = CASHSystem::setMustacheTemplate("system_email")) {

					// render the mustache template and return
					$encoded_html = CASHSystem::renderMustache(
						$template, array(
							// array of values to be passed to the mustache template
							'encoded_html' => $message_text,
							'message_title' => $message_title,
							'cdn_url' => (defined('CDN_URL')) ? CDN_URL : CASH_ADMIN_URL
						)
					);

				} else {

					//TODO: can we move this to
					$encoded_html .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>' . $message_title . '</title></head><body>'
						. "<h1>$message_title</h1>\n" . "<p>" . $encoded_html . "</p>"
						. "</body></html>";
				}
			}

			// deal with SMTP settings later:
			$smtp = $email_settings['smtp'];

			// include swift mailer
			include_once CASH_PLATFORM_ROOT . '/lib/swift/swift_required.php';

			if ($smtp) {
				// use SMTP settings for goodtimes robust happy mailing
				$transport = Swift_SmtpTransport::newInstance($email_settings['smtpserver'], $email_settings['smtpport']);
				if ($email_settings['smtpusername']) {
					$transport->setUsername($email_settings['smtpusername']);
					$transport->setPassword($email_settings['smtppassword']);
				}
			} else {

				//TODO: sendmail is gonna mess shit up

				// aww shit. use mail() and hope it gets there
				//$transport = Swift_MailTransport::newInstance();
			}

			$swift = Swift_Mailer::newInstance($transport);

			$message = new Swift_Message($subject);
			$message->setFrom($sender);
			$message->setReplyTo($from);
			//	$message->setSender($sender);
			$message->setBody($encoded_html, 'text/html');
			$message->setTo($toaddress);
			$message->addPart($message_text, 'text/plain');
			$headers = $message->getHeaders();
			$headers->addTextHeader('X-MC-Track', 'opens'); // Mandrill-specific tracking...leave in by defauly, no harm if not Mandrill

			if ($recipients = $swift->send($message, $failures)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Send email via Mandrill or SMTP if Mandrill isn't available
	 *
	 * @param $user_id
	 * @param $subject
	 * @param $recipients
	 * @param $message_text
	 * @param $message_title
	 * @param bool $global_merge_vars
	 * @param bool $merge_vars
	 * @param bool $encoded_html
	 * @param bool $message_text_html
	 * @return bool
	 */

	public static function sendMassEmail($user_id, $subject, $recipients, $message_text, $message_title, $global_merge_vars=false, $merge_vars=false, $encoded_html=false, $message_text_html=true) {

		if (CASH_DEBUG) {
			error_log("CASHSystem:sendMassEmail message:\n$message_text");
		}
		// get current user details for email
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people',
				'cash_action' => 'getuser',
				'user_id' => $user_id
			)
		);

		$user_details = $user_request->response['payload'];

		if ($user_details['display_name'] == 'Anonymous' || !$user_details['display_name']) {
			$user_details['display_name'] = $user_details['email_address'];
		}

		// handle encoding of HTML if specific HTML isn't passed in:
		if (!$encoded_html) {

			// need to make sure this isn't already parsed
			if (!$message_text_html) {
				$message_body_parsed = CASHSystem::parseMarkdown($message_text);
			} else {
				$message_body_parsed = $message_text;
			}

			if ($template = CASHSystem::setMustacheTemplate("system_email")) {

				// render the mustache template and return
				$encoded_html = CASHSystem::renderMustache(
					$template, array(
						// array of values to be passed to the mustache template
						'encoded_html' => $message_body_parsed,
						'message_title' => $message_title,
						'cdn_url' => (defined('CDN_URL')) ? CDN_URL : CASH_ADMIN_URL
					)
				);
			} else {

				//TODO: can we move this to
				$encoded_html .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>' . $message_title . '</title></head><body>'
					. "<h1>$message_title</h1>\n" . "<p>" . $encoded_html . "</p>"
					. "</body></html>";
			}
		}

		$message_html = $encoded_html;

		// get mandrill connection info
		$cash_request = new CASHConnection($user_id);
		$mandrill = $cash_request->getConnectionsByType('com.mandrillapp');

		// check viability of using mandrill
		//TODO: this might be a good place to have a firewall to stop abuse
		$connection_id = false;

		// if a viable connection, set connection id with user connection
		if (is_array($mandrill) && !empty($mandrill[0]['id'])) {
			$connection_id = $mandrill[0]['id'];
		} else {
			// else tell MandrillSeed to use system settings in connections.json
			$connection_id = "system";

			$connections = CASHSystem::getSystemSettings('system_connections');

			if (!isset($connections['com.mandrillapp']['api_key'])) {
				$connection_id = false;
			}
		}

		// either we've got a valid connection ID, or a fallback api_key
		if ($connection_id) {

			$mandrill = new MandrillSeed($user_id, $connection_id);

			if ($result = $mandrill->send(
				$subject,
				$message_text,
				$message_html,
				$user_details['email_address'],
				$user_details['display_name'],
				$recipients,
				null,
				$global_merge_vars,
				$merge_vars
			)) {
				return true;
			}
		}

		// if all else fails, cry
		return false;

	}
	/**
	 * Parsing markdown, returning an HTML string. Automatically converts URLs to links, using the Parsedown library.
	 *
	 * https://github.com/erusev/parsedown/wiki/Tutorial:-Get-Started
	 * @param $markdown
	 * @return string
	 */
	public static function parseMarkdown($markdown) {
		return Parsedown::instance()
			//->setUrlsLinked(false)
			->text($markdown);
	}

	/**
	 * Grab a specified mustache template
	 *
	 * @param $template
	 * @param bool $directory_override
	 * @return bool|string
	 */
	public static function setMustacheTemplate($template, $directory_override=false) {

		// defaults to default template directory unless we're told otherwise
		$directory = "/settings/defaults";

		if ($directory_override) $directory = $directory_override;

		// catch an exception or file not found and walk away if shit hits the fan
		try {
			$template = @file_get_contents(CASH_PLATFORM_ROOT . $directory . '/' . $template . '.mustache');
		} catch(Exception $e) {
			return false;
		}

		return $template;
	}

	/**
	 * Render mustache based on loaded template file and set vars.
	 *
	 * @param $template
	 * @param $title
	 * @param $body
	 * @return bool|string
	 */
	public static function renderMustache($template, $vars_array) {

		if (CASH_DEBUG) {
			error_log(
				"CASHSystem::renderMustacheTemplate\n".
				'$vars_array: '.print_r($vars_array, true)
			);
		}

		// try to render the template with the provided vars, or die
		$mustache_engine = new Mustache_Engine;
		try {
			$rendered_template = $mustache_engine->render($template, $vars_array);
		} catch (Exception $e) {
			return false;
		}

		return $rendered_template;
	}

	public static function getMimeTypeFor($path) {
		$types = array(
			'json'   => 'application/json',
			'pdf'    => 'application/pdf',
			'woff'   => 'application/font-woff',
			'zip'    => 'application/zip',
			'gzip'   => 'application/x-gzip',
			'mp4'    => 'audio/mp4',
			'mp3'    => 'audio/mpeg',
			'ogg'    => 'audio/ogg',
			'flac'   => 'audio/ogg',
			'vorbis' => 'audio/vorbis',
			'wav'    => 'audio/vnd.wave',
			'webm'   => 'audio/webm',
			'gif'    => 'image/gif',
			'jpg'    => 'image/jpeg',
			'jpeg'   => 'image/jpeg',
			'png'    => 'image/png',
			'svg'    => 'image/svg+xml',
			'tiff'   => 'image/tiff',
			'css'    => 'text/css',
			'csv'    => 'text/csv',
			'htm'    => 'text/html',
			'html'   => 'text/html',
			'js'     => 'text/javascript',
			'txt'    => 'text/plain',
			'mpeg'   => 'video/mpeg',
			'mpg'    => 'video/mpeg',
			'mp4'    => 'video/mp4',
			'mov'    => 'video/quicktime',
			'wmv'    => 'video/x-ms-wmv',
			'flv'    => 'video/x-flv'
		);
		$extension = pathinfo($path,PATHINFO_EXTENSION);
		if ($extension) {
			if (isset($types[$extension])) {
				return $types[$extension];
			}
		} else {
			return 'application/octet-stream';
		}
	}

	public static function getConnectionTypeSettings($type_string) {
		$definition_location = CASH_PLATFORM_ROOT . '/settings/connections/' . $type_string . '.json';
		if (file_exists($definition_location)) {
			$settings_array = json_decode(file_get_contents($definition_location),true);
			return $settings_array;
		} else {
			return false;
		}
	}

	public static function getCurrencySymbol($iso_string) {
		$iso_string = strtoupper($iso_string);
		$currencies = array(
			'USD' => '$',
			'EUR' => '€',
			'JPY' => '¥',
			'GBP' => '£',
			'AUD' => '$',
			'CHF' => '(Fr) ',
			'CAD' => '$',
			'HKD' => '$',
			'SEK' => '(kr) ',
			'NZD' => '$',
			'SGD' => '$',
			'NOK' => '(kr) ',
			'MXN' => '$'
		);
		if ($iso_string == 'ALL') {
			return $currencies;
		} else {
			if (isset($currencies[$iso_string])) {
				return $currencies[$iso_string];
			} else {
				return false;
			}
		}
	}

	public static function errorLog($data) {
		switch(gettype($data)) {
			case "string":
			case "boolean":
			case "integer":
			case "double":
				error_log("### errorLog -> " . $data);
				return true;
			break;

			case "NULL":
			case "unknown type":
				error_log("### errorLog -> NULL");
				return true;
			break;

			case "array":
			case "object":
			case "resource":
				error_log("### errorLog -> ". print_r($data, true));
				return true;
			break;

			default:
				error_log("### errorLog -> no data");
				return true;

		}
	}
} // END class
?>
