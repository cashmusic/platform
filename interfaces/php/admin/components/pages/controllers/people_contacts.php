<?php
$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();

if (isset($_POST['docontactadd'])) {
	/*
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addcontact',
			'address' => 'jesse@cashmusic.org'
		)
	);
	*/
}

$cash_admin->setPageContentTemplate('people_contacts');
?>