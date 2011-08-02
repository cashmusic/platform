<?php
/**
 * A collection of lower level static functions that are useful across classes
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
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public static function getCurrentIP() {
		$proxy = '';
		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
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
} // END class 
?>