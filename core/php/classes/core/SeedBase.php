<?php
/**
 * Base for all Seed classes
 *
 * @package seed.org.cashmusic
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
abstract class SeedBase extends SeedData {
	protected $settings,$settings_type=false;

	/**
	 * Retrieves any needed settings (API keys, passwords, etc) based on the
	 * type of Seed — returns false if no settings are found
	 *
	 * @return array|false
	 */protected function getSeedSettings() {
		if ($this->settings_type) {
			require_once(CASH_PLATFORM_ROOT.'/classes/core/SeedSettings.php');
			if ($this->use_specific_settings) {
				if ($this->settings = new SeedSettings($this->settings_type,$this->use_specific_settings)) {
					return $this->settings->getSettings();
				} else {
					return false;
				}
			} else {
				if ($this->settings = new SeedSettings($this->settings_type)) {
					return $this->settings->getSettings();
				} else {
					return false;
				}
			}
		}
	}
} // END class 
?>