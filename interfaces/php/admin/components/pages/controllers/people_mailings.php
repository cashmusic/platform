<?php

if (isset($_POST['doemailsend'])) {
	if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
		include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
	}

	if ($_POST['template_id'] == 'default') {
		$template = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/user_email.mustache');
		$marked_content = Markdown($_POST['html_content']);
		$html_content = str_replace('{{{encoded_html}}}',$marked_content,$template);
		$html_content = str_replace('{{subject}}', strip_tags($_POST['mail_subject']),$html_content);
	} else if ($_POST['template_id'] == 'none') {
		$html_content = $_POST['html_content'];
	} else {
		$html_content = Markdown($_POST['html_content']);
	}

	// make sure we include an unsubscribe link
	if (!stripos($html_content,'{{{unsubscribe_link}}}')) {
		if (stripos($html_content,'</body>')) {
			$html_content = str_ireplace('</body>','<br /><br />{{{unsubscribe_link}}}</body>',$html_content);
		} else {
			$html_content = $html_content . '<br /><br />{{{unsubscribe_link}}}';
		}
	}

	$mailing_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addmailing',
			'user_id' => $cash_admin->effective_user_id,
			'list_id' => $_POST['email_list_id'],
			'connection_id' => $_POST['connection_id'],
			'subject' => $_POST['mail_subject'],
			'from_name' => $_POST['mail_from'],
			'html_content' => $html_content
		)
	);

	$mailing_result = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'sendmailing',
			'mailing_id' => $mailing_response['payload']
		)
	);

	if ($mailing_result) {
		AdminHelper::formSuccess('Success. The mail is sent, just kick back and watch.','/people/mailings/');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/people/mailings/');
	}
}

$settings_test_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_test_array  = $settings_test_object->getConnectionsByScope('mass_email');

if ($settings_test_array) {
	$cash_admin->page_data['options_people_lists'] = AdminHelper::echoFormOptions('people_lists',0,false,true);
	$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('mass_email',0,true);
}

$cash_admin->setPageContentTemplate('people_mailings');
?>