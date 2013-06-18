<?php
$misc_message = false;
if (isset($_POST['domisc'])) {
	CASHSystem::setSystemSetting('timezone',$_POST['timezone']);
	CASHSystem::setSystemSetting('systememail',$_POST['systememail']);
	AdminHelper::formSuccess('Success. All changed.');
}

$migrate_message = false;
if (isset($_POST['domigrate'])) {
	$new_settings = array (
		'hostname' => $_POST['hostname'],
		'username' => $_POST['adminuser'],
		'password' => $_POST['adminpassword'],
		'database' => $_POST['databasename']
	);
	$migrate_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'migratedb',
			'todriver' => $_POST['driver'],
			'tosettings' => $new_settings
		)
	);
	if ($migrate_response['payload']) {
		AdminHelper::formSuccess('Success. Database upgraded. Enjoy!');
	} else {
		AdminHelper::formFailure('Error. There was a problem migrating your data.');
	}
}
$platform_settings = CASHSystem::getSystemSettings();

$cash_admin->page_data['system_email'] = $platform_settings['systememail'];
$cash_admin->page_data['timezone_options'] = AdminHelper::drawTimeZones($platform_settings['timezone']);
$db_types = array(
	'mysql' => 'MySQL',
	'sqlite' => 'SQLite'
);
$db_type = 'unknown';
if (array_key_exists($platform_settings['driver'],$db_types)) {
	$cash_admin->page_data['db_type'] = $db_types[$platform_settings['driver']];
}
if ($cash_admin->page_data['db_type'] == 'MySQL') {
	$cash_admin->page_data['migrate_from_mysql'] = true;
} elseif ($cash_admin->page_data['db_type'] == 'SQLite') {
	$cash_admin->page_data['migrate_from_sqlite'] = true;
}

if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

// handle all of the currency options, first the change
if (isset($_POST['currency_id'])) {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'setsettings',
			'type' => 'use_currency',
			'value' => $_POST['currency_id'],
			'user_id' => $cash_admin->effective_user_id
		)
	);
}
// now get the current setting
$settings_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system', 
		'cash_action' => 'getsettings',
		'type' => 'use_currency',
		'user_id' => $cash_admin->effective_user_id
	)
);
if ($settings_response['payload']) {
	$current_currency = $settings_response['payload'];
} else {
	$current_currency = 'USD';
}
$cash_admin->page_data['currency_options'] = AdminHelper::echoCurrencyOptions($current_currency);


$cash_admin->setPageContentTemplate('settings');
?>