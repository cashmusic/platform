<?php
require_once(dirname(__FILE__) . '/base.php');
require_once('framework/php/classes/plants/ElementPlant.php');

class ElementPlantTests extends UnitTestCase {	
	var $testingElement;
	var $testingElement2;
	var $testingCampaign;

	function testElementPlant(){
		echo "Testing ElementPlant\n";
		$e = new ElementPlant('element', array());
		$this->assertIsa($e, 'ElementPlant');
	}

	function testAddElement() {
		// first test requirements
		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'type' => 'stupidfakeelement'
			)
		);
		// should have failed with just a type:
		$this->assertFalse($element_request->response['payload']);

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => 'Test name',
				'type' => 'stupidfakeelement',
				'options_data' => array(
					'testoption1' => 'here is a thing to test',
					'testoption2' => 47
				),
				'user_id' => 1
			)
		);
		// should have failed with just a title:
		$this->assertTrue($element_request->response['payload']);
		$this->testingElement = $element_request->response['payload'];

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelement',
				'id' => $this->testingElement['id']
			)
		);
		$this->assertTrue($element_request->response['payload']);
		$this->assertEqual($element_request->response['payload']['name'],'Test name');
		$this->assertEqual($element_request->response['payload']['type'],'stupidfakeelement');
		$this->assertEqual($element_request->response['payload']['user_id'],1);
		$this->assertTrue(is_array($element_request->response['payload']['options']));
		$this->assertEqual($element_request->response['payload']['options']['testoption1'],'here is a thing to test');
		$this->assertEqual($element_request->response['payload']['options']['testoption2'],47);
	}

	function testEditElement() {
		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'editelement',
				'name' => 'New name',
				'id' => $this->testingElement['id']
			)
		);
		// should fail because we're requiring options_data to be included
		$this->assertFalse($element_request->response['payload']);

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'editelement',
				'name' => 'New name',
				'options_data' => array(
					'testoption1' => 'here is another thing to test',
					'testoption2' => 47
				),
				'id' => $this->testingElement['id']
			)
		);
		$this->assertTrue($element_request->response['payload']);

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelement',
				'id' => $this->testingElement['id']
			)
		);
		$this->assertTrue($element_request->response['payload']);
		$this->assertEqual($element_request->response['payload']['name'],'New name');
		$this->assertTrue(is_array($element_request->response['payload']['options']));
		$this->assertEqual($element_request->response['payload']['options']['testoption1'],'here is another thing to test');
	}

	function testDeleteElement() {
		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'deleteelement',
				'id' => $this->testingElement['id']
			)
		);
		$this->assertTrue($element_request->response['payload']);

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelement',
				'id' => $this->testingElement['id']
			)
		);
		// should fail because the element no longer exists
		$this->assertFalse($element_request->response['payload']);
	}

	function testAddCampaign() {
		// first set up some testing elements
		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => 'Test name',
				'type' => 'stupidfakeelement',
				'options_data' => array(
					'testoption1' => 'here is a thing to test',
					'testoption2' => 47
				),
				'user_id' => 1
			)
		);
		$this->testingElement = $element_request->response['payload'];
		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => 'Whatever',
				'type' => 'anotherfakeelement',
				'options_data' => array(
					'testoption1' => 42
				),
				'user_id' => 1
			)
		);
		$this->testingElement2 = $element_request->response['payload'];


		// test requirements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addcampaign',
				'title' => 'Test Title'
			)
		);
		// should have failed without description and user_id:
		$this->assertFalse($campaign_request->response['payload']);

		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addcampaign',
				'title' => 'Test Title',
				'description' => 'Here is a test description',
				'user_id' => 1
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->testingCampaign = $campaign_request->response['payload'];

		// get it and check defaults
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->assertEqual($campaign_request->response['payload']['title'],'Test Title');
		$this->assertEqual($campaign_request->response['payload']['description'],'Here is a test description');
		$this->assertEqual($campaign_request->response['payload']['user_id'],1);
		$this->assertEqual($campaign_request->response['payload']['metadata'],array());
		$this->assertEqual($campaign_request->response['payload']['elements'],array());

		// i know we should test delete in a different function but you know...living on the edge.
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'deletecampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);

		// now test adding a campaign with pre-defined elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addcampaign',
				'title' => 'Test campaign',
				'description' => 'A test description',
				'elements' => array(
					$this->testingElement,
					$this->testingElement2	
				),
				'user_id' => 1
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->testingCampaign = $campaign_request->response['payload'];

		// get it and check elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->assertTrue(is_array($campaign_request->response['payload']['elements']));
		$this->assertEqual($campaign_request->response['payload']['elements'],array(
			$this->testingElement,
			$this->testingElement2	
		));

		// now remove one of those elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'removeelementfromcampaign',
				'campaign_id' => $this->testingCampaign,
				'element_id' => $this->testingElement2
			)
		);
		$this->assertTrue($campaign_request->response['payload']);

		// make sure it was removed correctly
		// get it and check elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->assertEqual($campaign_request->response['payload']['elements'],array(
			$this->testingElement
		));

		// add that shit back
		// now remove one of those elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelementtocampaign',
				'campaign_id' => $this->testingCampaign,
				'element_id' => $this->testingElement2
			)
		);
		$this->assertTrue($campaign_request->response['payload']);

		// make sure it was removed correctly
		// get it and check elements
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->assertEqual($campaign_request->response['payload']['elements'],array(
			$this->testingElement,
			$this->testingElement2
		));
	}

	function testCampaignElements() {
		$campaign_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelementsforcampaign',
				'id' => $this->testingCampaign
			)
		);
		$this->assertTrue($campaign_request->response['payload']);
		$this->assertTrue(is_array($campaign_request->response['payload']));
		$this->assertEqual($campaign_request->response['payload'][0]['id'],$this->testingElement);
		$this->assertEqual($campaign_request->response['payload'][1]['id'],$this->testingElement2);
		// check data integrity
		$this->assertEqual($campaign_request->response['payload'][0]['name'],'Test name');
		$this->assertEqual($campaign_request->response['payload'][0]['type'],'stupidfakeelement');
		$this->assertEqual($campaign_request->response['payload'][0]['options'],array(
			'testoption1' => 'here is a thing to test',
			'testoption2' => 47
		));
	}

}

?>
