<?php
// add unique page settings:
$page_title = 'People: Manage Contacts';
$page_tips = '';

$page_message = false;
if (isset($_POST['dobatchcontactsadd'])) {
	if (!empty($_POST['element_content'])) {
		$email_array = array_map('trim',explode(",",str_replace(PHP_EOL,',',$_POST['element_content'])));
		if (count($email_array) > 0) {
			if ($_POST['email_list_id']) {
				$total_added = 0;
				foreach ($email_array as $address) {
					$add_request = new CASHRequest(array(
						'cash_request_type' => 'people', 
						'cash_action' => 'addaddresstolist',
						'address' => $address,
						'list_id' => $_POST['email_list_id']
					));
					if ($add_request->response['payload']) {
						$total_added++;
					}
				}
				$page_message = 'Success. Added ' . $total_added . ' new emails to your list.';
			} else {
				$page_message = 'Please select a list to add contacts to.';
			}
		}
	}
}
?>