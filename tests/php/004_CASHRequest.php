<?php

require_once('tests/php/base.php');

class CASHRequestTests extends UnitTestCase {

    function testCASHRequest(){
		$cr = new CASHRequest(array(
			'cash_request_type' => 'asset',
			'cash_action'       => 'unlock',
			'asset_id'          => 42,
		));
        $this->assertIsa($cr, 'CASHRequest');
    }

}
?>
