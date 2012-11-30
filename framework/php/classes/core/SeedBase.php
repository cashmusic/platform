<?php
/**
 * Abstract base for all Seed classes
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by Hypebot and Music Think Tank
 * Read Hypebot.com and MusicThinkTank.com
 *
 **/
abstract class SeedBase extends CASHData {
	protected $settings,$connection_id=false,$user_id=null;

	/**
	 * Retrieves any needed settings (API keys, passwords, etc) based on the
	 * type of Seed — returns false if no settings are found
	 *
	 * @return array|false
	 */protected function getCASHConnection() {
		if ($this->connection_id) {
			if ($this->settings = new CASHConnection($this->user_id,$this->connection_id)) {
				return $this->settings->getConnectionSettings();
			} else {
				return false;
			}
		}
	}

	public static function getRedirectMarkup($data=false) {
		return 'This seed does not have a redirect authorization flow.';
	}

	public static function handleRedirectReturn($data=false) {
		return 'This seed does not have a redirect authorization flow.';
	}
} // END class 
?>