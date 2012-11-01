<?php
/**
 * GC and background tasks
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class CASHDaemon extends CASHData {
	public $lottery_val,
		   $user_id = false,
		   $go = false;

	public function __construct($user_id=false,$chance=3) {
		$this->lottery_val = rand(1,100);
		$this->user_id = $user_id;
		if ($this->lottery_val <= $chance) {
			$this->go = true;
		}
	}

	private function clearOldSessions() {
		
	}

	private function clearOldTokens() {
		
	}

	private function pollUserAccounts() {
		
	}

	private function setAnalytics() {
		
	}

	public function getAnalytics() {
		$return_array = array('last_run' => rand(1,110283348));
		return $return_array;
	}

	public function __destruct() {
		if ($this->go) {
			$this->clearOldSessions();
			$this->clearOldTokens();
			if ($this->user_id) {
				$this->pollUserAccounts();
			}
			$this->setAnalytics();
		}
	}
} // END class 
?>