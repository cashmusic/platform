<?php
/**
 * The CASHRequest / CASHResponse relationship is the core of the CASH framework. 
 * CASHResponse takes output from all Plants and gives consistent output including 
 * REST-style status codes, contextual messages, and a data payload.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Peter Knowles
 *
 */class CASHResponse extends CASHData  {
	protected $response;
	public $status_codes, $status_contexts;

	public function __construct($user_id=false,$connection_id=false) {
		$response_messages = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/data/response_messages.json'),true);
		$this->status_codes = $response_messages['codes'];
		$this->status_contexts = $response_messages['contexts'];
	}
	
	/**
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public function pushResponse($status_code,$request_type,$action,$response_details,$contextual_message,$reset_session_id=false) {
		$contextual_name = '';
		$status_uid = $request_type . '_' . $action . '_' . $status_code;
		if (isset($this->status_contexts[$status_uid])) {
			$contextual_name = $this->status_contexts[$status_uid]['name'];
			$contextual_message = $this->status_contexts[$status_uid]['message'];
		}
		$this->response = array(
			'status_code' => $status_code,
			'status_uid' => $status_uid,
			'status_message' => $this->status_codes[(string) $status_code],
			'contextual_name' => $contextual_name,
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