<?php
/**
 * Base for all Seed classes
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
	protected $settings,$settings_type=false,$user_id=null;

	/**
	 * Retrieves any needed settings (API keys, passwords, etc) based on the
	 * type of Seed — returns false if no settings are found
	 *
	 * @return array|false
	 */protected function getCASHSettings() {
		if ($this->settings_type) {
			require_once(CASH_PLATFORM_ROOT.'/classes/core/CASHSettings.php');
			if ($this->use_specific_settings) {
				if ($this->settings = new CASHSettings($this->settings_type,$this->user_id,$this->use_specific_settings)) {
					return $this->settings->getSettings();
				} else {
					return false;
				}
			} else {
				if ($this->settings = new CASHSettings($this->settings_type,$this->user_id)) {
					return $this->settings->getSettings();
				} else {
					return false;
				}
			}
		}
	}
} // END class 
?>