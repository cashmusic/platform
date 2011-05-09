<?php
require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../core/php/cashmusic.php';

class BasicTests extends PHPUnit_Framework_TestCase {
	public function testCASHInstance() {
		$this->assertInstanceOf('CASHRequest', new CASHRequest);
   }
}
?>