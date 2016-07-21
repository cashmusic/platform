<?php
/**
 * GC and background tasks
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Leigh Marble
 * Leigh Marble, independent musician, Portland, OR -- www.leighmarble.com --
 *
 */class CASHDaemon extends CASHData {
	private $user_id = false;

	public function __construct($user_id=false) {
		$this->user_id = $user_id;
		$this->connectDB();
	}

	private function cleanTempData($table,$conditional_column,$timestamp) {
		$this->db->deleteData(
			$table,
			array(
				$conditional_column => array(
					'condition' => '<',
					'value' => $timestamp
				)
			)
		);
	}

	private function clearExpiredSessions() {
		$this->cleanTempData('sessions','expiration_date',time());
	}

	private function clearOldTokens() {
		$this->cleanTempData('people_resetpassword','creation_date',time() - 86400);
	}

	public function __destruct() {
		$this->clearExpiredSessions();
		$this->clearOldTokens();
	}
} // END class
?>
