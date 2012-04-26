<?php
/**
 * An abstract collection of lower level static functions that are useful 
 * across other classes.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */abstract class CASHSystem  {

	/**
	 * Handle annoying environment issues like magic quotes, constants and 
	 * auto-loaders before firing up the CASH platform and whatnot
	 *
	 * @return array
	 */public static function startUp() {
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
		define('CASH_API_URL', $cash_settings['apilocation']);
		define('CASH_PUBLIC_URL',str_replace('api/','public/',$cash_settings['apilocation']));
		// set up auto-load
		spl_autoload_register('CASHSystem::autoloadClasses');
		
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
		unset($cash_page_request);
	}

	/**
	 * The main public method to embed elements. Notice that it echoes rather
	 * than returns, because it's meant to be used simply by calling and spitting
	 * out the needed code...
	 *
	 * @return none
	 */public static function embedElement($element_id) {
		// fire up the platform sans-direct-request to catch any GET/POST info sent
		// in to the page
		$cash_page_request = new CASHRequest(null);
		$initial_page_request = $cash_page_request->sessionGet('initial_page_request','script');
		if ($initial_page_request && isset($initial_page_request['request']['element_id'])) {
			// now test that the initial POST/GET was targeted for this element:
			if ($initial_page_request['request']['element_id'] == $element_id) {
				$status_uid = $initial_page_request['status_uid'];
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
				'original_response' => $original_response
			)
		);
		if ($cash_body_request->response['status_uid'] == 'element_getmarkup_400') {
			echo '<div class="cash_system_error">Element #' . $element_id . ' could not be found.</div>';
		}
		if (is_string($cash_body_request->response['payload'])) {
			echo '<div class="cash_element cash_element_' . $element_id . '">' . $cash_body_request->response['payload'] . '</div>';
		}
		if ($cash_body_request->sessionGet('initialized_element_' . $element_id,'script')) {
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
	 */public static function getURLContents($data_url,$post_data=false,$ignore_errors=false) {
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
			$timeout = 5;
			
			@curl_setopt($ch,CURLOPT_URL,$data_url);
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
			return '<script type="text/javascript">window.location = "' . $url . '";</script>';
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
		$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
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
			$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
			if (array_key_exists($setting_name, $cash_settings)) {
				$success = CASHSystem::findReplaceInFile(
					CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php',
					$setting_name . ' = "' . $cash_settings[$setting_name],
					$setting_name . ' = "' . $value
				);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function getSystemSalt() {
		$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
		return $cash_settings['salt'];
	}

	/**
	 * Super basic XOR encoding — used for encoding connection data 
	 *
	 */public static function simpleXOR($input, $key = false) {
		if (!$key) {
			$key = CASHSystem::getSystemSalt();
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
				$root = 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s') 
						.'://'.$_SERVER['HTTP_HOST'];
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
	 */public static function formatTimeAgo($time) {
		if (is_string($time)) {
			$datestamp = strtotime($time);
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
		} else {
			$ago_str = date('d M', $datestamp);
		}
		return $ago_str;
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
		$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\">$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\">$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
		if ($twitter) {
			$text= preg_replace("/@(\w+)/", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $text);
			$text= preg_replace("/\#(\w+)/", '<a href="http://search.twitter.com/search?q=$1" target="_blank">#$1</a>',$text);
		}
		return $text;
	}

	/*
	 * Returns the system default email address from the settings ini file
	 *
	 * USAGE:
	 * ASHSystem::getDefaultEmail();
	 *
	 */public static function getDefaultEmail() {
		$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');
		return $cash_settings['systememail'];
	}

	public static function notExplicitFalse($test) {
		if ($test === false) {
			return false;
		} else {
			return true;
		}
	}

	public static function getBrowserIdStatus($assertion,$return_details=false) {
		if (!$assertion) {
			return false;
		} else {
			$post_data = array(
				'assertion' => $assertion,
				'audience' => CASHSystem::getCurrentURL(true)
			);
			$status = json_decode(CASHSystem::getURLContents('https://browserid.org/verify',$post_data,true),true);
			if ($return_details || !$status) {
				return $status;
			} else {
				if ($status['status'] == 'okay') {
					return $status['email'];
				} else {
					return false;
				}
			}
		}
	}

	public static function getBrowserIdJS($element_id=false) {
		$js_string = '<script src="https://browserid.org/include.js" type="text/javascript">'
				   . '</script><script type="text/javascript">'
				   . "(function(){function ha() {navigator.id.get(function(a){if(a){var i = document.getElementById('browseridassertion');if(i){i.value = a;var f=document.getElementById('cash_signin_form');if(f){f.submit();}}}});}var el=document.getElementById('browserid_login_link');if(el.attachEvent){el.attachEvent('onclick',ha);}else{el.addEventListener('click',ha,false);}}());"
				   . '</script>';
		if ($element_id) {
			 $js_string = str_replace(
				array('browseridassertion','browserid_login_link','cash_signin_form'),
				array('browseridassertion_'.$element_id,'browserid_login_link_'.$element_id,'cash_signin_form_'.$element_id),
				$js_string
			);
		}
		return $js_string;
		/*
		ORIGINAL un-minified JavaScript:
		
		<script src="https://browserid.org/include.js" type="text/javascript"></script>
		<script type="text/javascript">
		(function() {
			// deal with the return from browserid.org
			function handleAssertion() {
				navigator.id.get(function(assertion) {
					if (assertion) {
						var assertioninput = document.getElementById('browseridassertion_106');
						if (assertioninput) {
							assertioninput.value = assertion;
							var loginform = document.getElementById('cash_signin_form_106');
							if (loginform) {
								loginform.submit();
							}
						}
					}
				});
			}

			// attach elements
			var el = document.getElementById('browserid_login_link');
			if (el.attachEvent) { // handle IE freakshowfirst — fucking seriously? it's 2012 dudes, get with it
				el.attachEvent('onclick',handleAssertion);
			} else {
				el.addEventListener('click',handleAssertion,false);
			}
		}());
		</script>
		*/
	}

	/*
	 * Sends a plain text and HTML email for system things like email verification,
	 * password resets, etc.
	 *
	 * USAGE:
	 * CASHSystem::sendEmail('test email','CASH Music <info@cashmusic.org>','dev@cashmusic.org','message, with link: http://cashmusic.org/','title');
	 *
	 */public static function sendEmail($subject,$fromaddress,$toaddress,$message_text,$message_title,$encoded_html=false) {
		//create a boundary string. It must be unique 
		//so we use the MD5 algorithm to generate a random hash
		$random_hash = md5(date('r', time())); 
		//define the headers we want passed. Note that they are separated with \r\n
		$headers = "From: $fromaddress\r\nReply-To: $fromaddress";
		//add boundary string and mime type specification
		$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\""; 
		//define the body of the message.
		$message = "--PHP-alt-$random_hash\n";
		$message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
		$message .= "Content-Transfer-Encoding: 7bit\n\n";
		$message .= "$message_title\n\n";
		$message .= $message_text;
		$message .= "\n--PHP-alt-$random_hash\n";
		$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
		$message .= "Content-Transfer-Encoding: 7bit\n\n";
		if ($encoded_html) {
			$message .= $encoded_html;
		} else {
			$message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>' . $message_title . '</title></head><body>';
			$message .= "<style type=\"text/css\">\n";
			if (file_exists(CASH_PLATFORM_ROOT.'/settings/defaults/system_email_styles.css')) {
				$message .= file_get_contents(CASH_PLATFORM_ROOT.'/settings/defaults/system_email_styles.css');
			}
			$message .= "</style>\n";
			$message .= "<h1>$message_title</h1>\n";
			$message .= "<p>";
			$message .= str_replace("\n","<br />\n",preg_replace('/(http:\/\/(\S*))/', '<a href="\1">\1</a>', $message_text));
			$message .= "</p></body></html>";
		}
		$message .= "\n--PHP-alt-$random_hash--\n";
		//send the email
		$mail_sent = @mail($toaddress,$subject,$message,$headers);
		return $mail_sent;
	}
} // END class 
?>
