<?php
/**
 * Facebook library wrapper and public feed fetcher
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
 * This file is generously sponsored by echo
 *
 **/
class FacebookSeed extends SeedBase {
	protected $twitter;

	public function __construct($user_id=false,$connection_id=false) {
		$this->settings_type = 'com.facebook';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->primeCache();
		if ($user_id && $connection_id) {
			$this->connectDB();
			if ($this->getCASHConnection()) {
				// fire up an instance of the lib
			} else {
				// error out — potentially to special error message page.
			}
		}
	}

	public function getUserOrPage($id) {
		$endoint_url = 'https://graph.facebook.com/' . $id;
		$user_data = json_decode(CASHSystem::getURLContents($endoint_url),true);
		return $user_data;
	}
	
} // END class 
?>