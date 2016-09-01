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
	private $user_id 	= false;
	private $history 	= false;
	private $runtime 	= 0;
	// define the schedule — it's a fuzzy schedule based on traffic
	// the jobs will run ~5min early/late, depending. keep that in mind.

	// not necessary for these to be in the same timezone as the server
	// but it's probably smart — otherwise the beginning/end of a day
	// could cause some issues with the schedule runner
	private $schedule	= array(
		"soundscan-digital" => array(
			"type" => "friday", // lowercase day
			"time" => "3:00 AM America/Los_Angeles" // time with timezone
		),
		"soundscan-physical" => array(
			"type" => "tuesday",
			"time" => "3:00 AM America/New_York"
		)
	);

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
				'last_run' 			=> 0,
				'last3_runs' 		=> array($this->runtime),
				'last_scheduled'	=> array()
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

	private function runSchedule() {
		$total_runs = count($this->history['last3_runs']);
		// create an array of the gaps between tun times
		$spans = array($this->runtime - $this->history['last3_runs'][$total_runs - 1]);
		$i = $total_runs;
		while ($i > 1) {
			$spans[] = $this->history['last3_runs'][$i - 1] - $this->history['last3_runs'][$i - 2];
			$i--;
		}
		// assuming we have 3 last runs, we now have 3 spans. let's add a minimum span:
		$spans[] = 300;
		// now let's get a max, plus, you know...a little extra
		$max_span = floor(max($spans) * 1.15);

		// last thing we need to know is what day it is (lol)
		$today = strtolower(date('l'));

		foreach ($this->schedule as $key => $details) {

			// the day wrapping sucks. sorry but it's true. we need some special checks
			// just to even see if there's a chance  we missed a latenight task and
			// wrapped into the next day
			$overdue = false;
			if (isset($this->history['last_scheduled'][$key])) {
				if ($details['type'] == strtolower(date('l',$this->runtime - 86400)) &&
					date('d',$this->history['last_scheduled'][$key]) !== date('d',$this->runtime - 86400)) {
					// this if statment is ugly AF. in a nutshell:
					// if the type is, say, 'tuesday' and $this->runtime-24 hours is ALSO 'tuesday' then
					// check that the last known runtime day was the same day as $this->runtime
					// if the days are not the same then it means the job never ran on the day it was
					// supposed to (probably near midnight) so now it's overdue
					$overdue = true;
				}
			}

			if ($details['type'] == 'daily' || $today == $details['type'] || $overdue) {
				$target = strtotime($details['time']);
				// in case of first run
				$already_run = false;
				if (isset($this->history['last_scheduled'][$key]) && !$overdue) {
					// if we ran the job this same day call it already run
					if (date('d',$this->history['last_scheduled'][$key]) == date('d',$this->runtime)) {
						$already_run = true;
					}
				}
				// if it hasn't already been run AND we're within the max span (+15%) of
				// the scheduled run time then we go. (the max span stuff is an attempt
				if ((!$already_run && ($this->runtime + $max_span) > $target) || $overdue) {
					$this->runScheduledJob($key);
				}
			}
		}
	}

	private function runScheduledJob($type) {
		if (!$type) {
			return false;
		}
		switch ($type) {
			case 'soundscan-digital':
				$this->doSoundScanReport('digital');
				break;
			case 'soundscan-physical':
				$this->doSoundScanReport('physical');
				break;
		}
		$this->history['last_scheduled'][$type] = time();
	}

	private function doSoundScanReport($type) {
		if ($type == 'physical') {

		}
		if ($type == 'digital') {

			// translates to the previous thursday
			$report_end = strtotime("Yesterday 8:59PM America/Los_Angeles");
			$report_start = ($report_end-604800);

			$external_fulfillment = new ExternalFulfillmentSeed(false);
			$orders = $external_fulfillment->getOrders($report_start, $report_end, false);

			$soundscan = new SoundScanSeed(
				$orders, // upc, zip
				date("ymd", $report_end),    // 12345
				"digital"
			);

			$soundscan->createReport()
				->sendReport();
		}
	}


	/****************************************************************************
	 *
	 * The destructor function is where all the magic actually happens
	 *
	 * 1. clean up old sessions and tokens
	 * 2. check/run scheduled jobs
	 * 3. update all the runtime stats/data for the daemon
	 *
	 ***************************************************************************/
	public function __destruct() {
		if ($this->history['last_run'] <= time() - 300) {
			$this->clearExpiredSessions();
			$this->clearOldTokens();
			$this->runSchedule();
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
