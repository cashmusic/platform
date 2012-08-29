<?php
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if (isset($_POST['docontactedit'])) {
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'editcontact',
			'id' => $_POST['id'],
			'email_address' => $_POST['email_address'],
			'first_name' => $_POST['first_name'],
			'last_name' => $_POST['last_name'],
			'organization' => $_POST['organization'],
			'address_line1' => $_POST['address1'],
			'address_city' => $_POST['address_city'],
			'address_region' => $_POST['address_region'],
			'address_postalcode' => $_POST['address_postalcode'],
			'address_country' => $_POST['address_country'],
			'phone' => $_POST['phone']
		)
	);

	if ($edit_response['payload']) {
		AdminHelper::formSuccess('Success. Edited.');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.');
	}
}

$contact_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getcontact',
		'id' => $request_parameters[0]
	)
);
if ($contact_response['payload']) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$contact_response['payload']);
	$cash_admin->page_data['ui_title'] = 'Contacts: ' . $contact_response['payload']['first_name'] . ' ' . $contact_response['payload']['last_name'];
	$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL($contact_response['payload']['address_country']);
} else {
	$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();
}

$cash_admin->setPageContentTemplate('people_contacts_details');
?>