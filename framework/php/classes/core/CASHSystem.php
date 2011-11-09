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
		
		// set up auto-load
		spl_autoload_register('CASHSystem::autoloadClasses');
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
		$cash_page_request = new CASHRequest();
		
		$cash_body_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getmarkup',
				'id' => $element_id, 
				'status_uid' => $cash_page_request->response['status_uid']
			)
		);
		echo $cash_body_request->response['payload'];
		unset($cash_page_request);
		unset($cash_body_request);
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
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public static function getCurrentIP() {
		$proxy = '';
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
	 */public static function getCurrentURL() {
		if(!defined('STDIN')) { // check for command line
			return 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s') 
					.'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?');
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
		$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
		if ($twitter) {
			$text= preg_replace("/@(\w+)/", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $text);
			$text= preg_replace("/\#(\w+)/", '<a href="http://search.twitter.com/search?q=$1" target="_blank">#$1</a>',$text);
		}
		return $text;
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
	 */public static function getURLContents($data_url) {
		$url_contents = false;
		if (ini_get('allow_url_fopen')) {
			// try with fopen wrappers
			$url_contents = @file_get_contents($data_url);
		} elseif (in_array('curl', get_loaded_extensions())) {
			// fall back to cURL
			// tip of the cap: http://davidwalsh.name/download-urls-content-php-curl
			$ch = curl_init();
			$timeout = 5;
			$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.5; rv:7.0) Gecko/20100101 Firefox/7.0';
			
			curl_setopt($ch,CURLOPT_URL,$data_url);
			
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			$url_contents = $data;
		}
		return $url_contents;
	}
	
	/*
	 * Sends a plain text and HTML email for system things like email verification,
	 * password resets, etc.
	 *
	 * USAGE:
	 * ASHSystem::sendEmail('test email','CASH Music <info@cashmusic.org>','dev@cashmusic.org','message, with link: http://cashmusic.org/','title');
	 *
	 */public static function sendEmail($subject,$fromaddress,$toaddress,$message_text,$message_title) {
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
		$message .= "\n--PHP-alt-$random_hash--\n";
		//send the email
		$mail_sent = @mail($toaddress,$subject,$message,$headers);
		return $mail_sent;
	}
} // END class 
?>