<?php
$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();

/*
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system', 
		'cash_action' => 'setresetflag',
		'address' => 'jesse@cashmusic.org'
	)
);

var_dump(
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'validateresetflag',
			'address' => 'jesse@cashmusic.org',
			'key' => '68198cc3c44dc9fa1f5e40e473a2b4c1'
		)
	)
);
*/

$cash_admin->setPageContentTemplate('people_contacts');
?>