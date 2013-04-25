<?php

if (isset($_POST['doemailsend'])) {
	$mailing_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addmailing',
			'user_id' => $cash_admin->effective_user_id,
			'list_id' => $_POST['email_list_id'],
			'connection_id' => $_POST['connection_id'],
			'subject' => 'Email test',
			'html_content' => $_POST['html_content']
		)
	);

	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'sendmailing',
			'mailing_id' => $mailing_response['payload']
		)
	);
}

$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',0,false,true);
$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('mass_email',0,true);
$cash_admin->setPageContentTemplate('people_mailings');
?>