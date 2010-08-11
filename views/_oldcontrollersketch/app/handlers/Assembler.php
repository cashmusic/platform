<?php

class Assembler {
	/*
	class Assembler ()
	
	The Assembler class is responsible for determining what modules are needed
	by the Request object and, once processed, assembling them into one output 
	stream in the appropriate format and layout template.
	*/
	
	private $reqest;
	private $processor;
	
	function __construct() {
		$this->request = null;
		$this->processor = null;
	}
	
	function assemble() {
	
	}
	
	function request($reqest,$processor) {
		$this->request = $reqest;
		$this->processor = $processor;
	}
	
	function respond() {
	
	}
}

?>