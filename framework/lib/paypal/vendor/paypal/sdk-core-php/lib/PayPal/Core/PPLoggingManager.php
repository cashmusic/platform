<?php
namespace PayPal\Core;

/**
 * Simple Logging Manager.
 * This does an error_log for now
 * Potential frameworks to use are PEAR logger, log4php from Apache
 */
class PPLoggingManager {

	// Default Logging Level
	const DEFAULT_LOGGING_LEVEL = 0;

	// Logger name
	private $loggerName;

	// Log enabled
	private $isLoggingEnabled;

	// Configured logging level
	private $loggingLevel;

	// Configured logging file
	private $loggerFile;

	public function __construct($loggerName, $config = null) {
		$this->loggerName = $loggerName;
		$config = PPConfigManager::getConfigWithDefaults($config);

		$this->isLoggingEnabled = (array_key_exists('log.LogEnabled', $config) && $config['log.LogEnabled'] == '1');		
		 
		if($this->isLoggingEnabled) {
			$this->loggerFile = ($config['log.FileName']) ? $config['log.FileName'] : ini_get('error_log');
			$loggingLevel = strtoupper($config['log.LogLevel']);
			$this->loggingLevel = (isset($loggingLevel) && defined(__NAMESPACE__."\\PPLoggingLevel::$loggingLevel")) ? constant(__NAMESPACE__."\\PPLoggingLevel::$loggingLevel") : PPLoggingManager::DEFAULT_LOGGING_LEVEL;
		}
	}

	private function log($message, $level=PPLoggingLevel::INFO) {
		if($this->isLoggingEnabled && ($level <= $this->loggingLevel)) {
			error_log( $this->loggerName . ": $message\n", 3, $this->loggerFile);
		}
	}

	public function error($message) {
		$this->log($message, PPLoggingLevel::ERROR);
	}

	public function warning($message) {
		$this->log($message, PPLoggingLevel::WARN);
	}

	public function info($message) {
		$this->log($message, PPLoggingLevel::INFO);
	}

	public function fine($message) {
		$this->log($message, PPLoggingLevel::FINE);
	}

}
