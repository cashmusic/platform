<?php
class Request {
	/*
	class initialRequest (string $context, string $path, $object $post)
	
	The initialRequest class takes parts of the URL as arguments and translates 
	them into context/command/args structure for the controller to digest. The 
	basic format for URL parsing is:
	
	http://subdomain.domainname.tld/firstpathdir/argname/value/...
	
	(actual parsing handled by .htaccess, though programatic capture would work)
	
	 * The subdomain is treated as the context
	 * The firstpathdir is treated as the command
	 * The remaining pathdir values are treated as key/value pairs and added to
	   an associative array as such
	   
	Exceptions:
	 * A blank subdomain would enact the default context, though the default .htaccess
	   file simply does a redirect for no subdomain and displays a non app-driven
	   page in a special folder 
	 * The subdomain is checked against a list of known contexts. If unknown, it will
	   be assumed to be a username, the context will be set to 'userpage', and the 
	   subdomain will be passed as the value for an argument called 'username'
	 
	*/
	private $context; 
	private $command;
	private $args = array();  	

	function __construct($context,$path,$post) {
		if ($context != null) {
			$this->context = $context;
			if (!$this->contextIsKnown($this->context)) {
				$this->args = array_merge($this->args,array('username' => $this->context));
				$this->context = 'userpage';
			}
		} else {
			$this->context = 'default';
		}
		$this->setCommandAndArgsFromPath($path);
	}
	
	private function setCommandAndArgsFromPath($path) {
		if ( $path != '' ) {
			$ret = true;
			
			$argArray = explode('/',$path);
			$this->command = array_shift($argArray);
			
			$assocArgArray = array();
			$lastValue = -1;
			foreach ($argArray as $key => $value) {
				if ($key > $lastValue) {
					$assocArgArray[$value] = $argArray[$key+1];
					$lastValue = $key+1;
				}
			}
			
			$this->args = array_merge($this->args,$assocArgArray);
		} else {
			$this->command = 'default';
		}
	}
	
	private function getArgsFromPost($post) {
		$ret = false;
		
		// may need to convert from POST object to simple array:
		$this->args = array_merge($this->args,$post);
	}
	
	private function contextIsKnown($context) {
		// should query db/xml for complete list of known and compare
		// uses 'knowncontext' as a test case with a silly if/then below
		if ($context=='knowncontext') {
			return true;
		} else {
			return false;	
		}
	}
	
	public function getContext() {
		return $this->context;
	}
	
	public function getCommand() {
		return $this->command;
	}
	
	public function getArgs() {
		return $this->args;
	}
}
?>