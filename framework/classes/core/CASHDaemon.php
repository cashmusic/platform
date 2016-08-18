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
	private $history = false;
	private $runtime = 0;

	public function __construct($user_id=false) {
		$this->user_id = $user_id;
		$this->connectDB();
		$this->runtime = time();
		// get stored history
		$history_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'daemon',
				'user_id' => -1
			)
		);
		if ($history_request->response['payload']) {
			$this->history = $history_request->response['payload'];
		} else {
			$this->history = array(
				'total_runs' 		=> 1,
				'last_run' 			=> $this->runtime,
				'last3_runs' 		=> array($this->runtime),
				'last_sceduled'	=> array()
			);
		}
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
		if ($this->history['last_run'] <= time() - 300) {
			$this->clearExpiredSessions();
			$this->clearOldTokens();
			// update history
			$this->history['total_runs'] 		= $this->history['total_runs'] + 1;
			$this->history['last_run'] 		= $this->runtime;
			$this->history['last3_runs'][]	= $this->runtime;
			if (count($this->history['last3_runs']) > 3) {
				$this->history['last3_runs'] = array_slice($this->history['last3_runs'],-3);
			}
			// store settings for next run
			$history_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'setsettings',
					'type' => 'daemon',
					'user_id' => -1,
					'value' => $this->history
				)
			);
		}
	}
} // END class
?>
