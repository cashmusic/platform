<?php
$cash_admin->page_data['country_codes'] = AdminHelper::drawCountryCodeUL();
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if (isset($_POST['docontactadd'])) {
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addcontact',
			'user_id' => $effective_user,
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

	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Contact added.');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.');
	}
}

$initials_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getcontactinitials',
		'user_id' => $effective_user
	)
);

if ($initials_response['payload']) {
	$alphabet = array(
		'A' => array('letter' => 'A', 'contact' => false),
		'B' => array('letter' => 'B', 'contact' => false),
		'C' => array('letter' => 'C', 'contact' => false),
		'D' => array('letter' => 'D', 'contact' => false),
		'E' => array('letter' => 'E', 'contact' => false),
		'F' => array('letter' => 'F', 'contact' => false),
		'G' => array('letter' => 'G', 'contact' => false),
		'H' => array('letter' => 'H', 'contact' => false),
		'I' => array('letter' => 'I', 'contact' => false),
		'J' => array('letter' => 'J', 'contact' => false),
		'K' => array('letter' => 'K', 'contact' => false),
		'L' => array('letter' => 'L', 'contact' => false),
		'M' => array('letter' => 'M', 'contact' => false),
		'N' => array('letter' => 'N', 'contact' => false),
		'O' => array('letter' => 'O', 'contact' => false),
		'P' => array('letter' => 'P', 'contact' => false),
		'Q' => array('letter' => 'Q', 'contact' => false),
		'R' => array('letter' => 'R', 'contact' => false),
		'S' => array('letter' => 'S', 'contact' => false),
		'T' => array('letter' => 'T', 'contact' => false),
		'U' => array('letter' => 'U', 'contact' => false),
		'V' => array('letter' => 'V', 'contact' => false),
		'W' => array('letter' => 'W', 'contact' => false),
		'X' => array('letter' => 'X', 'contact' => false),
		'Y' => array('letter' => 'Y', 'contact' => false),
		'Z' => array('letter' => 'Z', 'contact' => false)
	);
	if (is_array($initials_response['payload'])) {
		foreach ($initials_response['payload'] as $value) {
			if (array_key_exists($value['initial'],$alphabet)) {
				$alphabet[$value['initial']]['contact'] = true;
			}
		}
		$cash_admin->page_data['alphabet'] = new ArrayIterator($alphabet);
	}
}

if (isset($request_parameters[1])) {
	if ($request_parameters[0] == 'bylastname') {
		$contacts_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getcontactsbyinitials',
				'user_id' => $effective_user,
				'initial' => $request_parameters[1]
			)
		);
		if ($contacts_response['payload']) {
			$cash_admin->page_data['contact_list'] = new ArrayIterator($contacts_response['payload']);
		}
	}
}

$cash_admin->setPageContentTemplate('people_contacts');
?>