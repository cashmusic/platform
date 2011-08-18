<?php
/**
 * So dumb. So needed. Echoes a request back to the response for testing.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class EchoPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'echo';
		$this->db_required = false;
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		return $this->pushSuccess($this->request,'no context for a simple echo. no context for a simple echo.');
	}
} // END class 
?>