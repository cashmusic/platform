<?php
namespace PayPal\Core;
use PayPal\Exception\PPConfigurationException;
class PPHttpConfig {

	/**
	 * Some default options for curl
	 * These are typically overridden by PPConnectionManager
	 */
	public static $DEFAULT_CURL_OPTS = array(
		CURLOPT_SSLVERSION => 6,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_TIMEOUT        => 60,	// maximum number of seconds to allow cURL functions to execute
		CURLOPT_USERAGENT      => 'PayPal-PHP-SDK',
		CURLOPT_HTTPHEADER     => array(),
		CURLOPT_SSL_VERIFYHOST => 2,
		CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
	);	
	
	const HEADER_SEPARATOR = ';';	
	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';

	private $headers = array();

	private $curlOptions;

	private $url;

	private $method;
	/***
	 * Number of times to retry a failed HTTP call
	 */
	private $retryCount;

	/**
	 * 
	 * @param string $url
	 * @param string $method  HTTP method (GET, POST etc) defaults to POST
	 * @param array $configs All Configurations
	 */
	public function __construct($url=null, $method=self::HTTP_POST,$configs = array()) {
		$this->url = $url;
		$this->method = $method;
		$this->curlOptions = $this->getHttpConstantsFromConfigs($configs, 'http.') + self::$DEFAULT_CURL_OPTS;
		 // Update the Cipher List based on OpenSSL or NSS settings
	        $curl = curl_version();
	        $sslVersion = isset($curl['ssl_version']) ? $curl['ssl_version'] : '';
	        if (substr_compare($sslVersion, "NSS/", 0, strlen("NSS/")) === 0) {
	            //Remove the Cipher List for NSS
	            $this->removeCurlOption(CURLOPT_SSL_CIPHER_LIST);
	        }
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function getHeader($name) {
		if(array_key_exists($name, $this->headers)) {
			return $this->headers[$name];
		}
		return NULL;
	}

	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function setHeaders(array $headers) {
		$this->headers = $headers;
	}

	public function addHeader($name, $value, $overWrite=true) {
		if(!array_key_exists($name, $this->headers) || $overWrite) {
			$this->headers[$name] = $value;
		} else {
			$this->headers[$name] = $this->headers[$name] . self::HEADER_SEPARATOR . $value;			
		}
	}
	
	public function removeHeader($name) {
		unset($this->headers[$name]);
	}

	
	
	public function getCurlOptions() {
		return $this->curlOptions;
	}

	public function addCurlOption($name, $value) {
		$this->curlOptions[$name] = $value;
	}

        /**
	   * Removes a curl option from the list
	   *
	   * @param $name
        */
        public function removeCurlOption($name)
        {
          unset($this->curlOptions[$name]);
        }
 

	public function setCurlOptions($options) {
		$this->curlOptions = $options;
	}

	

	/**
	 * Set ssl parameters for certificate based client authentication
	 *
	 * @param string $certPath - path to client certificate file (PEM formatted file)
	 */
	public function setSSLCert($certPath, $passPhrase=NULL) {		
		$this->curlOptions[CURLOPT_SSLCERT] = realpath($certPath);
		if(isset($passPhrase) && trim($passPhrase) != "") {
			$this->curlOptions[CURLOPT_SSLCERTPASSWD] = $passPhrase;
		}
	}

	/**
	 * Set connection timeout in seconds
	 * @param integer $timeout
	 */
	public function setHttpTimeout($timeout) {
		$this->curlOptions[CURLOPT_CONNECTTIMEOUT] = $timeout;
	}

	/**
	 * Set HTTP proxy information
	 * @param string $proxy
	 * @throws PPConfigurationException
	 */
	public function setHttpProxy($proxy) {
		$urlParts = parse_url($proxy);
		if($urlParts == false || !array_key_exists("host", $urlParts))
			throw new PPConfigurationException("Invalid proxy configuration ".$proxy);

		$this->curlOptions[CURLOPT_PROXY] = $urlParts["host"];
		if(isset($urlParts["port"]))
			$this->curlOptions[CURLOPT_PROXY] .=  ":" . $urlParts["port"];
		if(isset($urlParts["user"]))
			$this->curlOptions[CURLOPT_PROXYUSERPWD]	= $urlParts["user"] . ":" . $urlParts["pass"];
	}	
	
	/**
	 * @param integer $retry
	 */
	public function setHttpRetryCount($retryCount) {
		$this->retryCount = $retryCount;
	}	

	public function getHttpRetryCount() {
		return $this->retryCount;
	}
	
	/**
	 * Sets the User-Agent string on the HTTP request
	 * @param string $userAgentString
	 */
	public function setUserAgent($userAgentString) {
		$this->curlOptions[CURLOPT_USERAGENT] = $userAgentString;
	}
	
	
	 /**
     * Retrieves an array of constant key, and value based on Prefix
     *
     * @param array $configs
     * @param       $prefix
     * @return array
     */
    public function getHttpConstantsFromConfigs($configs = array(), $prefix)
    {
        $arr = array();
        if ($prefix != null && is_array($configs)) {
            foreach ($configs as $k => $v) {
                // Check if it startsWith
                if (substr($k, 0, strlen($prefix)) === $prefix) {
                    $newKey = ltrim($k, $prefix);
                    if (defined($newKey)) {
                        $arr[constant($newKey)] = $v;
                    }
                }
            }
        }
        return $arr;
    }
}
