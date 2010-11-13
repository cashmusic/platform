<?php
/**
 * Base for all Plant classes
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
abstract class PlantBase extends SeedData {
	protected $request_type,$request;

	abstract public function processRequest();

	protected function plantPrep($request_type,$request) {
		$this->request_type = $request_type;
		$this->request = $request;
		$this->connectDB();
	}

	protected function restrictExecutionTo() {
		$args_count = func_num_args();
		if ($args_count > 0) {
			$args = func_get_args();
			foreach ($args as $arg) {
			    if ($arg == $this->request_type) {
					return true;
				}
			}
			return false;
		} else {
			// error: at least one argument must be given
		}
	}
} // END class 
?>