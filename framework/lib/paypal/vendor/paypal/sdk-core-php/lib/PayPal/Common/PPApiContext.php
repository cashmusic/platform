<?php
namespace PayPal\Common;
use PayPal\Core\PPConfigManager;
/**
 * 
 * Container for Call level parameters such as
 * SDK configuration 
 */
class PPApiContext {
	
	/**
	 * 
	 * @var array Dynamic SDK configuration
	 */
	protected $config;
	
	/**
	 * @var custom SOAPHeader 
	 */
	private $SOAPHeader;
	
	private $httpHeaders;
	
	/*
	 *
	 * @param array associative array of HTTP headers to attach to request
	 */
	public function setHttpHeaders(array $httpHeaders) {
		$this->httpHeaders = $httpHeaders;
		return $this;
	}
	
	/*
	 *
	 * @return array
	 */
	public function getHttpHeaders() {
		return $this->httpHeaders;
	}
	
	/*
	 *
	 * @param string $name header name
	 * @param string $value header value
	 * @param boolean $force if true (default), existing value is overwritten
	 */
	public function addHttpHeader($name, $value, $force=true) {
		if(!$force && array_key_exists($name, $this->httpHeaders)) {
			return;
		}
		$this->httpHeaders[$name] = $value;
		return $this;
	}
	
	/*
	 *
	 * @param PPXmlMessage object to attach to SOAP header
	 */
	public function setSOAPHeader($SOAPHeader) {
		$this->SOAPHeader = $SOAPHeader;
		return $this;
	}
	
	/*
	 *
	 * @return PPXmlMessage
	 */
	public function getSOAPHeader() {
		return $this->SOAPHeader;
	}
	
	/*
	 *
	 * @param array SDK configuration parameters
	 */
	public function setConfig(array $config) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
		return $this;
	}
	
	/*
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}
	
    public function get($searchKey)
    {
        if(!isset($this->config)) {
            return PPConfigManager::getInstance()->get($searchKey);
        }
        else
        {
            if (array_key_exists($searchKey, $this->getConfig()))
                return $this->config[$searchKey];
        }
        return false;
    }
	
    /*
	 *
	 * @param array SDK configuration parameters
	 */
	public function __construct($config=null) {
		$this->config = PPConfigManager::getConfigWithDefaults($config);
	}
}
