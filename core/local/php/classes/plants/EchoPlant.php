<?php
/**
 * So dumb. So needed.
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
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
		$result = $this->response->pushResponse(200,$this->request_type,$this->action,$this->request,'no context for a simple echo');
		return $result;
	}
} // END class 
?>