<?php
/**
 * Abstract base for all Seed classes
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by Hypebot and Music Think Tank
 * Read Hypebot.com and MusicThinkTank.com
 *
 **/
abstract class SeedBase extends CASHData {
	protected $settings,$settings_id=false,$user_id=null;

	/**
	 * Retrieves any needed settings (API keys, passwords, etc) based on the
	 * type of Seed — returns false if no settings are found
	 *
	 * @return array|false
	 */protected function getCASHSettings() {
		if ($this->settings_id) {
			if ($this->settings = new CASHSettings($this->user_id,$this->settings_id)) {
				return $this->settings->getSettings();
			} else {
				return false;
			}
		}
	}
} // END class 
?>