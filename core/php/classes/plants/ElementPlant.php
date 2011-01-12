<?php
/**
 * Takes an element ID finds it's settings, returns either raw data or markup
 * to be used in the requesting app
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class ElementPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'event';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			switch ($this->action) {
				case 'addelement':
					// REQUIRE DIRECT REQUEST!
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('name')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('type')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('options_data')) { return $this->sessionGetLastResponse(); }
					$result = $this->addElement($this->request['name'],$this->request['type'],$this->request['options_data']);
					if ($result) {
						return $this->response->pushResponse(
							200,$this->request_type,$this->action,
							array('element_id' => $result),
							'success. element id included in payload'
						);
					} else {
						return $this->response->pushResponse(
							500,$this->request_type,$this->action,
							$this->request,
							'there was an error adding the element'
						);
					}
					break;
				case 'getelement':
					// REQUIRE DIRECT REQUEST!
					if (!$this->checkRequestMethodFor('direct')) { return $this->sessionGetLastResponse(); }
					if (!$this->requireParameters('element_id')) { return $this->sessionGetLastResponse(); }
						$result = $this->getElement($this->request['element_id']);
						if ($result) {
							return $this->response->pushResponse(
								200,$this->request_type,$this->action,
								$result,
								'success. element included in payload'
							);
						} else {
							return $this->response->pushResponse(
								500,$this->request_type,$this->action,
								$this->request,
								'there was an error retrieving the element'
							);
						}
					break;
				default:
					return $this->response->pushResponse(
						400,$this->request_type,$this->action,
						$this->request,
						'unknown action'
					);
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				$this->request,
				'no action specified'
			);
		}
	}
	
	public function getElement($element_id) {
		$query = "SELECT name,type,options FROM seed_elements WHERE id = $element_id";
		$result = $this->db->doQueryForAssoc($query);
		if ($result) {
			$the_element = array($result['name'],$result['type'],json_decode($result['options']);
			return $the_element;
		} else {
			return false;
		}
	}

	public function addElement($name,$type,$options_data,$user_id=0) {
		$options_data = json_encode($options_data);
		$options_data = "'" . mysql_real_escape_string($options_data) . "'";
		$current_date = time();
		$query = "INSERT INTO seed_elements (name,type,data,user_id,creation_date) VALUES ($name,$type,$options_data,$user_id,$current_date)";
		if ($this->db->doQuery($query)) { 
			return mysql_insert_id();
		} else {
			return false;
		}
	}

} // END class 
?>