<?php
/**
 * GC and background tasks
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class CASHDaemon extends CASHData {
	public $lottery_val,
		   $go = false;
	public function __construct($chance=3) {
		$this->lottery_val = rand(1,100);
		if ($this->lottery_val <= $chance) {
			$this->go = true;
		}
	}
} // END class 
?>