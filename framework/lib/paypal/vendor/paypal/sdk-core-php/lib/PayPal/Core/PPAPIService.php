<?php
namespace PayPal\Core;

use PayPal\Core\PPLoggingManager;
use PayPal\Formatter\FormatterFactory;
use PayPal\Core\PPRequest;
use PayPal\Core\PPHttpConfig;
use PayPal\Handler\PPAuthenticationHandler;
use PayPal\Auth\PPTokenAuthorization;

class PPAPIService {

	public $serviceName;
	public $apiMethod;
	public $apiContext;
	private $logger;
	private $handlers = array();
	private $serviceBinding;
	private $port;

	public function __construct($port, $serviceName, $serviceBinding, $apiContext, $handlers=array()) {
		
		$this->apiContext = $apiContext;		
		$this->serviceName = $serviceName;
		$this->port = $port;

		$this->logger = new PPLoggingManager(__CLASS__, $this->apiContext->getConfig());
		$this->handlers = $handlers;
		$this->serviceBinding = $serviceBinding;
		
	}

	public function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}

	/**
	 * Register additional handlers to run before
	 * executing this call
	 *
	 * @param IPPHandler $handler
	 */
	public function addHandler($handler) {
		$this->handlers[] = $handler;
	}


	/**
	 * Execute an api call
	 *
	 * @param string $apiMethod	Name of the API operation (such as 'Pay')
	 * @param PPRequest $params Request object
	 * @return array containing request and response
	 */
	public function makeRequest($apiMethod, $request) {
		
		$this->apiMethod = $apiMethod;
		
		$httpConfig = new PPHttpConfig(null, PPHttpConfig::HTTP_POST);
		if($this->apiContext->getHttpHeaders() != null) {
			$httpConfig->setHeaders($this->apiContext->getHttpHeaders());
		}
		$this->runHandlers($httpConfig, $request);

		
		// Serialize request object to a string according to the binding configuration
		$formatter = FormatterFactory::factory($this->serviceBinding);
		$payload = $formatter->toString($request);
		
		// Execute HTTP call
		$connection = PPConnectionManager::getInstance()->getConnection($httpConfig, $this->apiContext->getConfig());
		$this->logger->info("Request: $payload");
		$response = $connection->execute($payload);
		$this->logger->info("Response: $response");

		return array('request' => $payload, 'response' => $response);
	}

	private function runHandlers($httpConfig, $request) {
	
		$options = $this->getOptions();
		foreach($this->handlers as $handlerClass) {
			$handlerClass->handle($httpConfig, $request, $options);
		}
	}
	
	private function getOptions()
	{
		return array(
			'port' => $this->port,
			'serviceName' => $this->serviceName,
			'serviceBinding' => $this->serviceBinding,
			'config' => $this->apiContext->getConfig(),
			'apiMethod' => $this->apiMethod,
			'apiContext' => $this->apiContext
		);
	}	
}
