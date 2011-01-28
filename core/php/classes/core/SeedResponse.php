<?php
/**
 * Output a consistent response to every request
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class SeedResponse extends SeedData  {
	protected $response;
	
	protected $status_codes = array(
		'200' => 'OK: Success!',
		'304' => 'Not Modified: There was no new data to return.',
		'400' => 'Bad Request: The request was invalid.',
		'401' => 'Unauthorized: Authentication credentials were missing or incorrect.',
		'403' => 'Forbidden: The request is understood, but it has been refused.',
		'404' => 'Not Found: The requested resource does not exists.',
		'500' => 'Internal Server Error: Something is broken.',
		'502' => 'Bad Gateway: Seed has not been properly installed and/or configured.',
		'503' => 'Service Unavailable: Third party settings are incorrect or unknown.'
	);
	
	/**
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public function pushResponse($status_code,$request_type,$action,$response_details,$contextual_message) {
		$this->response = array(
			'status_code' => $status_code,
			'status_uid' => $request_type . '_' . $action . '_' . $status_code,
			'status_message' => $this->status_codes["$status_code"],
			'contextual_message' => $contextual_message,
			'request_type' => $request_type,
			'action' => $action,
			'payload' => $response_details
		);
		$this->sessionSetLastResponse($this->response);
		return $this->response;
	}
} // END class 
?>