<?php
require_once(dirname(__FILE__) . '/base.php');
require_once('framework/php/classes/plants/PeoplePlant.php');

class PeoplePlantTests extends UnitTestCase {	
	var $testing_list, $testing_mailing;
	
	function testPeoplePlant(){
		echo "Testing PeoplePlant\n";
		
		$p = new PeoplePlant('People', array());
		$this->assertIsa($p, 'PeoplePlant');
	}
	
	function testAddList() {
		$list_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'name' => 'Test List',
				'description' => 'Test Description',
				'user_id' => 1,
			)
		);
		// should work fine with no description or connection_id
		$this->assertTrue($list_add_request->response['payload']);
		$this->testing_list = $list_add_request->response['payload'];
		
		$list_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'connection_id' => 0,
				'user_id' => 1,
			)
		);
		// should fail with no name
		$this->assertFalse($list_add_request->response['payload']);
	}

	function testGetList() {
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertEqual($list_request->response['payload']['name'],'Test List');
	}

	function testEditList() {
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editlist',
				'list_id' => $this->testing_list,
				'name' => 'New List Name',
				'description' => 'New List Description',
				'connection_id' => '322'
			)
		);
		$this->assertTrue($list_request->response['payload']);
		
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		if ($list_request->response['payload']) {
			$this->assertEqual($list_request->response['payload']['name'],'New List Name');
			$this->assertEqual($list_request->response['payload']['description'],'New List Description');
			$this->assertEqual($list_request->response['payload']['connection_id'],'322');
		}
	}

	function testDeleteList() {
		// delete it
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'deletelist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertTrue($list_request->response['payload']);
		
		// test that it's really gone
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertFalse($list_request->response['payload']);
	}

	function testSignupRemove() {
		$test_address = 'whatever@cashmusic.org';
		$add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addaddresstolist',
				'address' => $test_address,
				'list_id' => $this->testing_list,
				'do_not_verify' => true,
			)
		);
		$this->assertTrue($add_request->response['payload']);

		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getaddresslistinfo',
				'address' => $test_address,
				'list_id' => $this->testing_list,
			)
		);
		// make sure that the address has been added to the local list
		$this->assertTrue($list_request->response['payload']);

		$remove_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'removeaddress',
				'address' => $test_address,
				'list_id' => $this->testing_list
			)
		);
		$this->assertTrue($remove_request->response['payload']);
	}

	function testAddMailing() {
		// first try adding with just required values. test 
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addmailing',
				'user_id' => 1,
				'list_id' => $this->testing_list,
				'connection_id' => 42,
				'subject' => 'Test subject',
			)
		);
		$this->assertTrue($mailing_request->response['payload']);
		$this->testing_mailing = $mailing_request->response['payload'];

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailing',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['subject'],'Test subject');
		$this->assertEqual($mailing_request->response['payload']['user_id'],1);
		$this->assertEqual($mailing_request->response['payload']['list_id'],$this->testing_list);
		$this->assertEqual($mailing_request->response['payload']['connection_id'],42);
		$this->assertEqual($mailing_request->response['payload']['template_id'],0);
		$this->assertEqual($mailing_request->response['payload']['html_content'],'');
		$this->assertEqual($mailing_request->response['payload']['text_content'],'');

		// now test with non-defaults
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addmailing',
				'user_id' => 2,
				'list_id' => $this->testing_list,
				'connection_id' => 43,
				'subject' => 'Test subject 2',
				'template_id' => 13,
				'html_content' => '<p>hello!</p>',
				'text_content' => 'hello!'
			)
		);
		$this->assertTrue($mailing_request->response['payload']);
		$nondefault_mailing_id = $mailing_request->response['payload'];

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailing',
				'mailing_id' => $nondefault_mailing_id
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['subject'],'Test subject 2');
		$this->assertEqual($mailing_request->response['payload']['user_id'],2);
		$this->assertEqual($mailing_request->response['payload']['list_id'],$this->testing_list);
		$this->assertEqual($mailing_request->response['payload']['connection_id'],43);
		$this->assertEqual($mailing_request->response['payload']['template_id'],13);
		$this->assertEqual($mailing_request->response['payload']['html_content'],'<p>hello!</p>');
		$this->assertEqual($mailing_request->response['payload']['text_content'],'hello!');

		//test user security
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailing',
				'mailing_id' => $this->testing_mailing,
				'user_id' => 23
			)
		);
		$this->assertFalse($mailing_request->response['payload']);
	}

	function testInitialAnalytics() {
		// we're testing that the initial analytics state was set correctly
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['sends'],0);
		$this->assertEqual($mailing_request->response['payload']['opens_total'],0);
		$this->assertEqual($mailing_request->response['payload']['opens_unique'],0);
		$this->assertEqual($mailing_request->response['payload']['opens_mobile'],0);
		$this->assertEqual($mailing_request->response['payload']['opens_country'],'{}');
		$this->assertEqual($mailing_request->response['payload']['opens_ids'],'[]');
		$this->assertEqual($mailing_request->response['payload']['clicks'],0);
		$this->assertEqual($mailing_request->response['payload']['clicks_urls'],'{}');
		$this->assertEqual($mailing_request->response['payload']['failures'],0);

		//test user security
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing,
				'user_id' => 23
			)
		);
		$this->assertFalse($mailing_request->response['payload']);
	}

	function testEditMailing() {
		$send_date = time();
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editmailing',
				'mailing_id' => $this->testing_mailing,
				'subject' => 'Final subject',
				'html_content' => '<p>success!</p>',
				'text_content' => 'success!',
				'send_date' => $send_date
			)
		);
		$this->assertTrue($mailing_request->response['payload']);

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailing',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and new ones were set correctly
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['subject'],'Final subject');
		$this->assertEqual($mailing_request->response['payload']['user_id'],1);
		$this->assertEqual($mailing_request->response['payload']['list_id'],$this->testing_list);
		$this->assertEqual($mailing_request->response['payload']['connection_id'],42);
		$this->assertEqual($mailing_request->response['payload']['template_id'],0);
		$this->assertEqual($mailing_request->response['payload']['html_content'],'<p>success!</p>');
		$this->assertEqual($mailing_request->response['payload']['text_content'],'success!');
		$this->assertEqual($mailing_request->response['payload']['send_date'],$send_date);

		// test user id security
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editmailing',
				'mailing_id' => $this->testing_mailing,
				'subject' => 'Should not work',
				'user_id' => 23
			)
		);
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailing',
				'mailing_id' => $this->testing_mailing
			)
		);

		// don't test a false. test that the edit didn't happen and that the final subject is unchanged
		$this->assertEqual($mailing_request->response['payload']['subject'],'Final subject');
	}

	function testMailingAnalytics() {
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'recordmailinganalytics',
				'mailing_id' => $this->testing_mailing,
				'sends' => 12,
				'opens_total' => 1,
				'opens_mobile' => 1,
				'opens_country' => array(
					'US' => array(
						'regions' => array(
							'massachusetts' => 1
						),
						'cities' => array(
							'wrentham' => 1
						),
						'postal' => array(
							'02093' => 1
						)
					)
				),
				'opens_id' => '42',
				'click_url' => 'http://test.com/',
				'failures' => 1,
			)
		);

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['sends'],12);
		$this->assertEqual($mailing_request->response['payload']['opens_total'],1);
		$this->assertEqual($mailing_request->response['payload']['opens_unique'],1);
		$this->assertEqual($mailing_request->response['payload']['opens_mobile'],1);
		$this->assertEqual($mailing_request->response['payload']['clicks'],1);
		$this->assertEqual($mailing_request->response['payload']['failures'],1);

		$country_data = json_decode($mailing_request->response['payload']['opens_country'],true);
		$this->assertTrue(is_array($country_data));
		$this->assertEqual($country_data['US']['total'],1);
		$this->assertEqual($country_data['US']['regions']['massachusetts'],1);
		$this->assertEqual($country_data['US']['cities']['wrentham'],1);
		$this->assertEqual($country_data['US']['postal']['02093'],1);

		$opens_ids = json_decode($mailing_request->response['payload']['opens_ids'],true);
		$this->assertTrue(in_array('42',$opens_ids));

		$clicks_urls = json_decode($mailing_request->response['payload']['clicks_urls'],true);
		$this->assertTrue(is_array($clicks_urls));
		$this->assertEqual($clicks_urls['http://test.com/'],1);

		// test click/city incrementing in a more realistic scenario
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'recordmailinganalytics',
				'mailing_id' => $this->testing_mailing,
				'opens_total' => 1,
				'opens_country' => array(
					'US' => array(
						'regions' => array(
							'massachusetts' => 1
						),
						'cities' => array(
							'wrentham' => 1
						),
						'postal' => array(
							'02093' => 1
						)
					)
				),
				'opens_id' => '4',
				'click_url' => 'http://test.com/'
			)
		);

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['sends'],12); // no change
		$this->assertEqual($mailing_request->response['payload']['opens_total'],2);
		$this->assertEqual($mailing_request->response['payload']['opens_unique'],2);
		$this->assertEqual($mailing_request->response['payload']['opens_mobile'],1); // no change
		$this->assertEqual($mailing_request->response['payload']['clicks'],2);
		$this->assertEqual($mailing_request->response['payload']['failures'],1); // no change

		$country_data = json_decode($mailing_request->response['payload']['opens_country'],true);
		$this->assertTrue(is_array($country_data));
		$this->assertEqual($country_data['US']['total'],2);
		$this->assertEqual($country_data['US']['regions']['massachusetts'],2);
		$this->assertEqual($country_data['US']['cities']['wrentham'],2);
		$this->assertEqual($country_data['US']['postal']['02093'],2);

		$clicks_urls = json_decode($mailing_request->response['payload']['clicks_urls'],true);
		$this->assertTrue(is_array($clicks_urls));
		$this->assertEqual($clicks_urls['http://test.com/'],2);

		// test click/city incrementing in a more realistic scenario
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'recordmailinganalytics',
				'mailing_id' => $this->testing_mailing,
				'opens_total' => 1,
				'opens_country' => array(
					'US' => array(
						'regions' => array(
							'massachusetts' => 1
						),
						'cities' => array(
							'boston' => 1
						),
						'postal' => array(
							'02111' => 1
						)
					)
				),
				'opens_id' => '4',
				'click_url' => 'http://test.com/anotherlink'
			)
		);

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['opens_total'],3);
		$this->assertEqual($mailing_request->response['payload']['opens_unique'],2); // no change
		$this->assertEqual($mailing_request->response['payload']['clicks'],3);

		$country_data = json_decode($mailing_request->response['payload']['opens_country'],true);
		$this->assertTrue(is_array($country_data));
		$this->assertEqual($country_data['US']['total'],3);
		$this->assertEqual($country_data['US']['regions']['massachusetts'],3); // no change
		$this->assertEqual($country_data['US']['cities']['wrentham'],2); // no change
		$this->assertEqual($country_data['US']['postal']['02093'],2); // no change
		$this->assertEqual($country_data['US']['cities']['boston'],1);
		$this->assertEqual($country_data['US']['postal']['02111'],1);

		$clicks_urls = json_decode($mailing_request->response['payload']['clicks_urls'],true);
		$this->assertTrue(is_array($clicks_urls));
		$this->assertEqual($clicks_urls['http://test.com/'],2);
		$this->assertEqual($clicks_urls['http://test.com/anotherlink'],1); // no change

		// finally check that a new region is added correctly, preserving data from before
		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'recordmailinganalytics',
				'mailing_id' => $this->testing_mailing,
				'opens_total' => 1,
				'opens_country' => array(
					'US' => array(
						'regions' => array(
							'new york' => 1
						),
						'cities' => array(
							'new york' => 1
						),
						'postal' => array(
							'10027' => 1
						)
					)
				),
				'opens_id' => '4',
			)
		);

		$mailing_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getmailinganalytics',
				'mailing_id' => $this->testing_mailing
			)
		);
		// test that values held and defaults are correct
		$this->assertTrue($mailing_request->response['payload']);
		$this->assertEqual($mailing_request->response['payload']['opens_total'],4);
		$this->assertEqual($mailing_request->response['payload']['clicks'],3); // no change

		$country_data = json_decode($mailing_request->response['payload']['opens_country'],true);
		$this->assertTrue(is_array($country_data));
		$this->assertEqual($country_data['US']['total'],4);
		$this->assertEqual($country_data['US']['regions']['massachusetts'],3); // no change
		$this->assertEqual($country_data['US']['cities']['wrentham'],2); // no change
		$this->assertEqual($country_data['US']['postal']['02093'],2); // no change
		$this->assertEqual($country_data['US']['cities']['boston'],1); // no change
		$this->assertEqual($country_data['US']['postal']['02111'],1); // no change
		$this->assertEqual($country_data['US']['regions']['new york'],1); 
		$this->assertEqual($country_data['US']['cities']['new york'],1);
		$this->assertEqual($country_data['US']['postal']['10027'],1);
	}
}

?>
