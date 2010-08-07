<?php
class userRequest {
	/*
		 
	*/
	private $page;
	private $subs;  	

	function __construct($path) {
		$this->setCommandAndArgsFromPath($path);
	}
	
	private function setCommandAndArgsFromPath($path) {
		if ( $path != '' ) {
			$ret = true;
			
			$pathArray = explode('/',$path);
			$this->page = array_shift($pathArray);
			
			$this->subs = $pathArray;
		} else {
			$this->page = 'default';
		}
	}

	
	public function getPage() {
		return $this->page;
	}
	
	public function getSubs() {
		return $this->subs;
	}
}
?>