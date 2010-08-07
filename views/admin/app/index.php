<?php
class Request {
	// takes a querystring value and turns it into a command + arguments
	private $command;
	private $args = array();  	

	function __construct($query) {
		$this->getArgsFromPost($query);
		$this->getCommandFromArgs();
	}
	
	private function getArgsFromPost($query) {
		$this->args = array_merge($this->args,$query);
	}
	
	private function getCommandFromArgs() {
		if (isset($this->args['cmd'])) {
			$this->command = $this->args['cmd'];
			unset($this->args['cmd']);
		} else {
			$this->command = false;
			$this->args = null;
		}
	}
	
	public function getCommand() {
		// returns string or false
		return $this->command;
	}
	
	public function getArgs() {
		// returns associative array or null
		return $this->args;
	}
}

class RequestProcessor {
	// takes a request and determines the appropriate action, returning
	// the appropriate response or an error
	private $command;
	private $args;
	private $errormsg;
	private $response;	

	function __construct($request) {
		$this->command = $request->getCommand();
		$this->args = $request->getArgs();
		$this->processCommand();
	}
	
	private function processCommand() {
		if (!$this->command) {
			$this->setErrorMessage(20);
		} else {
			switch($this->command) {
				case 'login':
					$this->response = 'User login requested.';
					$validateArguments = $this->checkRequiredArguments(array('email_address','pass'));
					if (!$validateArguments) {
						$this->setErrorMessage(30);
					}
					break;
				default:
					$this->setErrorMessage();
					break;
			}
		}
	}
	
	private function checkRequiredArguments($required) {
		$returnvalue = true;
		if (is_array($required)) {
			foreach ($required as $requiredkey) {
				$keyexists = array_key_exists($requiredkey, $this->args);
				if (!$keyexists) {
					$returnvalue = false;
					break;
				}
			}
		} elseif (is_string($required)) {
			$keyexists = array_key_exists($required, $this->args);
			if (!$keyexists) {
				$returnvalue = false;
			}
		}
		return $returnvalue;
	}
	
	private function setErrorMessage($errorcode,$customMessage) {
		switch($errorcode){
			case 10:
				if (isset($customMessage)) {
					$this->errormsg = $customMessage;
				} else {
					$this->setErrorMessage();
				}
				//break;
			case 20:
				$this->errormsg = 'No command was specified.';
				break;
			case 30:
				$this->errormsg = 'Invalid arguments.';
				break;
			default:
				$this->errormsg = 'An unspecified error has occurred.';
				break;
		}
		$this->response = false;
	}
	
	public function getResponse() {
		// returns string or false
		return $this->response;
	}
	
	public function getError() {
		// returns string
		return $this->errormsg;
	}
}

$request = new Request($_REQUEST);
$response = new RequestProcessor($request);

if ($response->getResponse()) {	
	echo $response->getResponse();
} else {
	echo $response->getError();
}
?>