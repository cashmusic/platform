<?php
/**
 * Base for all Seed classes
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
abstract class SeedBase extends SeedData {
	protected $settings,$settings_type=false;

	protected function getSeedSettings() {
		if ($this->settings_type) {
			require_once(SEED_ROOT.'/classes/core/SeedSettings.php');
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