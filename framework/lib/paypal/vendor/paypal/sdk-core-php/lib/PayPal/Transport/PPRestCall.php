<?php
namespace PayPal\Transport;
use PayPal\Core\PPLoggingManager;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPHttpConnection;
class PPRestCall {

	
	/**
	 * 
	 * @var PPLoggingManager logger interface
	 */
	private $logger;
	
	private $apiContext;

	public function __construct($apiContext) {
		$this->apiContext = $apiContext;
		$this->logger = new PPLoggingManager(__CLASS__, $apiContext->getConfig());
	}

	/**
	 * @param array $handlers array of handlers
	 * @param string $path   Resource path relative to base service endpoint
	 * @param string $method HTTP method - one of GET, POST, PUT, DELETE, PATCH etc
	 * @param string $data   Request payload
	 * @param array $headers HTTP headers
	 */
	public function execute($handlers, $path, $method, $data='', $headers=array()) {

		$config = $this->apiContext->getConfig();		
		$httpConfig = new PPHttpConfig(null, $method);
		$httpConfig->setHeaders($headers + 
			array(
				'Content-Type' => 'application/json'
			)	
		);
		
		foreach($handlers as $handler) {
			if (!is_object($handler)) {
				$shandler = "\\".$handler;
				$handler = new $shandler($this->apiContext);
			}
			$handler->handle($httpConfig, $data, array('path' => $path, 'apiContext' => $this->apiContext));
		}
		$connection = new PPHttpConnection($httpConfig, $config);
		$response = $connection->execute($data);
		$this->logger->fine($response);
		
		return $response;
	}
	
}
