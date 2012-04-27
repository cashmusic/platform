<?php
// add unique page settings:
$page_title = 'People: Add List';
$page_tips = '';

// parsing posted data:
if (isset($_POST['dolistadd'])) {
	// do the actual list add stuffs...
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$list_add_request = new CASHRequest(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'addlist',
			'name' => $_POST['list_name'],
			'description' => $_POST['list_description'],
			'connection_id' => $_POST['connection_id'],
			'user_id' => $effective_user,
		)
	);
}
?>