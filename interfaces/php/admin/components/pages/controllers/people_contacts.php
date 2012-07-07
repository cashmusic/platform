<?php
if (isset($_POST['dobatchcontactsadd'])) {
	if (!isset($_POST['email_list_id'])) {
		AdminHelper::formFailure('Please select a list to add contacts to.');
	}
	if (!empty($_POST['element_content'])) {
		$email_array = array_map('trim',explode(",",str_replace(PHP_EOL,',',$_POST['element_content'])));
		if (count($email_array) > 0) {
			$total_added = 0;
			foreach ($email_array as $address) {
				$add_request = new CASHRequest(array(
					'cash_request_type' => 'people', 
					'cash_action' => 'addaddresstolist',
					'do_not_verify' => 1,
					'address' => $address,
					'list_id' => $_POST['email_list_id']
				));
				if ($add_request->response['payload']) {
					$total_added++;
				}
			}
			AdminHelper::formSuccess('Success. Added ' . $total_added . ' new emails to your list.');
		} else {
			AdminHelper::formFailure('Could not find any valid email addresses. Please try again.');
		}
	} else {
		AdminHelper::formFailure('Error. Please try again.');
	}
}

$cash_admin->page_data['list_options'] = AdminHelper::echoFormOptions('people_lists',0,false,true);

$cash_admin->setPageContentTemplate('people_contacts');
?>