<?php

namespace CASHMusic\Core;

use CASHMusic\Core\CASHData as CASHData;
use CASHMusic\Core\CASHRequest as CASHRequest;
use CASHMusic\Seeds\ExternalFulfillmentSeed;
use CASHMusic\Seeds\SoundScanSeed;

/**
 * GC 和背景任务
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
 * 该文件由Leigh Marble慷慨赞助
 * Leigh Marble, 独立音乐家, 波特兰, 俄勒冈 -- www.leighmarble.com --
 *
 */
class CASHDaemon extends CASHData {
	private $user_id 	= false;
	private $history 	= false;
	private $runtime 	= 0;
	// 定义安排计划 — 计划时间是大概，主要基于交通情况，
	// 工作可能提前/推迟 ~5 分钟，请记住.

	// 这那些没有和服务器在同一时区的人可能没必要关系，
	// 但最好还是记住 — 否则对于那些遵守进度安排者来说，
	// 每天的开始和结束可能都会导致一些问题发生。
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
		// 在运行次数间创建空隙间隔数组
		$spans = array($this->runtime - $this->history['last3_runs'][$total_runs - 1]);
		$i = $total_runs;
		while ($i > 1) {
			$spans[] = $this->history['last3_runs'][$i - 1] - $this->history['last3_runs'][$i - 2];
			$i--;
		}
		// 假设我们有上3 次运行，现在有3 个跨度。让我们添加一个最小的跨度:
		$spans[] = 300;
		// 现在让我们加个max, 以及，你知道... 再多一点 
		$max_span = floor(max($spans) * 1.15);

		// 最后我们要知道的是今天是哪天（大笑）
		$today = strtolower(date('l'));

		foreach ($this->schedule as $key => $details) {

			// 日总结很讨厌。对不起，但这是真的。我们需要特别核对，
			// 来查看我们是否漏了完成深夜任务，
			// 而要把它加到第二天去。
			$overdue = false;
			if (isset($this->history['last_scheduled'][$key])) {
				if ($details['type'] == strtolower(date('l',$this->runtime - 86400)) &&
					date('d',$this->history['last_scheduled'][$key]) !== date('d',$this->runtime - 86400)) {
					// 概括起来，这个 if 陈述是丑陋的AF.:
					// 如果类型是，比如说，‘周二’，那么$this->运行时间-24 小时也是'周二'，接着
					// 核对上此制度的运行时间日期是和 $this->runtime 一样的。
					// 如果日期不一样，那么就意味着该工单没有在它所指定的日子（可能将近半夜）运行，
					// 因此现在已经过期了。 
					$overdue = true;
				}
			}

			if ($details['type'] == 'daily' || $today == $details['type'] || $overdue) {
				$target = strtotime($details['time']);
				// 在第一次运行时
				$already_run = false;
				if (isset($this->history['last_scheduled'][$key]) && !$overdue) {
					// 如果在同一天运行，则称之为已运行。
					if (date('d',$this->history['last_scheduled'][$key]) == date('d',$this->runtime)) {
						$already_run = true;
					}
				}
				// 如果尚未运行，并且我们在计划的运行时间的最大跨度（+15%）内，则继续
				// (最大跨度是一次尝试
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

			// 转化到前个星期四
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
	 * 自毁函数是所有魔术实际发生的地方 
	 *
	 * 1. 清除旧的会话和令牌 
	 * 2. 核对/运行已计划的工作 
	 * 3. 为后台程序更新所有运行时间的状态/数据
	 *
	 ***************************************************************************/
	public function __destruct() {
		if ($this->history['last_run'] <= time() - 300) {
			$this->clearExpiredSessions();
			$this->clearOldTokens();
			$this->runSchedule();
			// 更新历史记录
			$this->history['total_runs'] 		= $this->history['total_runs'] + 1;
			$this->history['last_run'] 		= $this->runtime;
			$this->history['last3_runs'][]	= $this->runtime;
			if (count($this->history['last3_runs']) > 3) {
				$this->history['last3_runs'] = array_slice($this->history['last3_runs'],-3);
			}
			// 为下次运行储存设置 
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
} // 课程结束
?>
