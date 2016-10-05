<?php

if (isset($_POST['doemailsend'])) {

	if ($_POST['template_id'] == 'default') {
        if (CASH_DEBUG) {
            error_log(
                'default'
            );
        }

        // parse the html content for any markdown
        $html_content = CASHSystem::parseMarkdown($_POST['html_content']);

        if ($template = CASHSystem::setMustacheTemplate("user_email")) {

            // render the mustache template and return
            $html_content = CASHSystem::renderMustache(
                $template, array(
                    // array of values to be passed to the mustache template
                    'encoded_html' => $html_content,
                    'message_title' => $_POST['mail_subject'],
                    'subject' => $_POST['mail_subject'],
                    'cdn_url' => (defined('CDN_URL')) ? CDN_URL : CASH_ADMIN_URL
                )
            );
        }

	} else if ($_POST['template_id'] == 'none') {
        if (CASH_DEBUG) {
            error_log(
                'none'
            );
        }

		$html_content = $_POST['html_content'];
	} else {

        if (CASH_DEBUG) {
            error_log(
                'nothing set'
            );
        }

        $html_content = CASHSystem::parseMarkdown($_POST['html_content']);
	}

	// make sure we include an unsubscribe link
	if (!stripos($html_content,'{{{unsubscribe_link}}}')) {
		if (stripos($html_content,'</body>')) {
			$html_content = str_ireplace('</body>','<br /><br />{{{unsubscribe_link}}}</body>',$html_content);
		} else {
			$html_content = $html_content . '<br /><br />{{{unsubscribe_link}}}';
		}
	}


    if (CASH_DEBUG) {
        error_log(
            'added mailing'
        );
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

    if (CASH_DEBUG) {
        error_log(
            'sent mailing'
        );
    }

	$mailing_result = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'sendmailing',
			'mailing_id' => $mailing_response['payload'],
            'test' => 'true'
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