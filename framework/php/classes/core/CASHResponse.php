<?php
/**
 * The CASHRequest / CASHResponse relationship is the core of the CASH framework. 
 * CASHResponse takes output from all Plants and gives consistent output including 
 * REST-style status codes, contextual messages, and a data payload.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */class CASHResponse extends CASHData  {
	protected $response;
	
	protected $status_codes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version Not Supported'
	);
	
	/**
	 * Formats a proper response, stores it in the session, and returns it
	 *
	 * @return array
	 */public function pushResponse($status_code,$request_type,$action,$response_details,$contextual_message,$reset_session_id=false) {
		$this->response = array(
			'status_code' => $status_code,
			'status_uid' => $request_type . '_' . $action . '_' . $status_code,
			'status_message' => $this->status_codes[(int) $status_code],
			'contextual_message' => $contextual_message,
			'request_type' => $request_type,
			'action' => $action,
			'payload' => $response_details
		);
		$this->sessionSetLastResponse($this->response,$reset_session_id);
		return $this->response;
	}
} // END class 
?>