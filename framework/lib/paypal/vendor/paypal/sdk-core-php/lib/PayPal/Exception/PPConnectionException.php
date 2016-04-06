<?php
namespace PayPal\Exception;
class PPConnectionException extends \Exception
{
	/**
	 * The url that was being connected to when the exception occured
	 * @var string
	 */
	private $url;
	
	/**
	 * Any response data that was returned by the server
	 * @var string
	 */
	private $data;

	public function __construct($url, $message, $code = 0) {		
		parent::__construct($message, $code);
		$this->url = $url;		
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function getUrl() {
		return $this->url;
	}
}