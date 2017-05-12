<?php

$template_id = !empty($_POST['template_id']) ? $_POST['template_id'] : "none";
$html_content = !empty($_POST['html_content']) ? $_POST['html_content'] : false;
$subject = !empty($_POST['mail_subject']) ? $_POST['mail_subject'] : false;
$list_id = !empty($_POST['email_list_id']) ? $_POST['email_list_id'] : false;
$connection_id = !empty($_POST['connection_id']) ? $_POST['connection_id'] : false;
$mail_from = !empty($_POST['mail_from']) ? $_POST['mail_from'] : false;
$asset_id = ($_POST['attached_asset']) ? $_POST['attached_asset'] : false;
$test_recipients = !empty($_POST['test_recipients']) ? preg_replace('/\s+/', '', $_POST['test_recipients']) : "";

$persisted_values = $admin_primary_cash_request->sessionGet("mailing_data");

// send a test email
if (!empty($_POST['action']) && $_POST['action'] == 'dotestsend') {

    // save values in session for persistence
    $admin_primary_cash_request->sessionSet("mailing_data", [
        'template_id' => $template_id,
        'html_content' => $html_content,
        'subject' => $subject,
        'list_id' => $list_id,
        'connection_id' => $connection_id,
        'mail_from' => $mail_from,
        'asset_id' => (isset($persisted_values['asset_id'])) ? $persisted_values['asset_id'] : $asset_id
    ]);

    // we need to do some direct send here so we're not creating a redundant test email

    $mailing_result = new CASHRequest(
        array(
            'cash_request_type' => 'people',
            'cash_action' => 'buildmailingcontent',
            'template_id' => $template_id,
            'html_content' => $html_content,
            'title' => $subject,
            'subject' => $subject,
            'asset' => (isset($persisted_values['asset_id'])) ? $persisted_values['asset_id'] : $asset_id
        )
    );

    $metadata_result = $cash_admin->requestAndStore(
        array(
            'cash_request_type' => 'people',
            'cash_action' => 'getmailingmetadata',
            'mailing_id' => $mailing_response['payload'],
            'user_id' => $cash_admin->effective_user_id
        )
    );

    $html_content = $mailing_result->response['payload'];
    $test_recipients = explode(",", $test_recipients);
    $recipients = [];

    foreach($test_recipients as $recipient) {
        $recipients[] = [
            'email' => $recipient,
            'type' => 'to',
            'name' => 'Test recipient',
            'metadata' => array(
                'user_id' => 0
            )
        ];
    }

    if (!empty($_POST['template_id'])) {
        if ($_POST['template_id'] != "default") {
            $override_template = true;
        }
    }

    // skip the requests and make the request directly for testing
    $result = CASHSystem::sendMassEmail(
        $cash_admin->effective_user_id,
        $subject,
        $recipients,
        $html_content, // message body
        $subject, // message subject
        [],
        [], // local merge vars (per email)
        false,
        true,
        true,
        $mail_from,
        false
    );

    if ($mailing_result) {
        AdminHelper::formSuccess('Test Success. The mail is sent, check it for errors.','/people/mailings/');
    } else {
        AdminHelper::formFailure('Test Error. Something just didn\'t work right.','/people/mailings/');
    }

}
// send the email
if (!empty($_POST['action']) && $_POST['action'] == 'dolivesend') {

    $mailing_result = new CASHRequest(
        array(
            'cash_request_type' => 'people',
            'cash_action' => 'buildmailingcontent',
            'template_id' => $template_id,
            'html_content' => $html_content,
            'title' => $subject,
            'subject' => $subject,
            'asset' => (isset($persisted_values['asset_id'])) ? $persisted_values['asset_id'] : $asset_id
        )
    );

    $html_content = $mailing_result->response['payload'];

	$mailing_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
                'cash_action' => 'addmailing',
			'user_id' => $cash_admin->effective_user_id,
			'list_id' => $list_id,
			'connection_id' => $connection_id,
			'subject' => $subject,
			'from_name' => $mail_from,
			'html_content' => $html_content,
            'asset' => ($_POST['attached_asset']) ? $_POST['attached_asset'] : false
		)
	);

    $metadata_result = $cash_admin->requestAndStore(
        array(
            'cash_request_type' => 'people',
            'cash_action' => 'getmailingmetadata',
            'mailing_id' => $mailing_response['payload'],
            'user_id' => $cash_admin->effective_user_id
        )
    );

    if ($metadata_result) {
        //TODO:eventually might want this to be smarter, for extended metadata
        $asset_id = $metadata_result['payload'][0]['value'];
    } else {
        $asset_id = $persisted_values['asset_id'];
    }

	$mailing_result = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'sendmailing',
			'mailing_id' => $mailing_response['payload'],
            'user_id' => $cash_admin->effective_user_id,
            'asset' => $asset_id
		)
	);

    $mailing_list = $cash_admin->requestAndStore(
        array(
            'cash_request_type' => 'people',
            'cash_action' => 'getlist',
            'user_id' => $cash_admin->effective_user_id,
            'list_id' => $list_id
        )
    );

    // we don't need this session data anymore.
    $admin_primary_cash_request->sessionClear("mailing_data");

	if ($mailing_result) {
		AdminHelper::formSuccess('Success. The mail is queued and sending now.','/people/mailings/');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/people/mailings/');
	}
}

$settings_test_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_test_array  = $settings_test_object->getConnectionsByScope('mass_email');

if ($settings_test_array) {
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions(
	    'people_lists',
        (isset($persisted_values['list_id'])) ? $persisted_values['list_id'] : $list_id,
        false,
        true);
	$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions(
	    'mass_email',
        (isset($persisted_values['connection_id'])) ? $persisted_values['connection_id'] : $connection_id,
        true);
}

$user_request = $cash_admin->requestAndStore(
    array(
        'cash_request_type' => 'people', 
        'cash_action' => 'getuser',
        'user_id' => $cash_admin->effective_user_id
    )
);

// let's just set template vars up here, for persistence' sake
if (is_array($persisted_values)) {
    $cash_admin->page_data = array_merge($cash_admin->page_data, $persisted_values);
}

$cash_admin->page_data['email_address'] = $user_request['payload']['email_address'];

$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions(
    'assets',
    (isset($persisted_values['asset_id'])) ? $persisted_values['asset_id'] : $asset_id,
    $cash_admin->getAllFavoriteAssets(),
    true
);

$cash_admin->setPageContentTemplate('people_mailings');
?>