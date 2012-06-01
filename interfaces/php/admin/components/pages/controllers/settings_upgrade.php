<?php
$cash_admin->page_data['platform_version'] = CASHRequest::$version;
$cash_admin->page_data['upgrade_available'] = false;

$current_profile_url = 'https://raw.github.com/cashmusic/DIY/master/installers/php/update/releaseprofiles/release_'
					 . $cash_admin->page_data['platform_version']
					 . '.json';
if (CASHSystem::getURLContents($current_profile_url)) {
	// found stable upgrade path...neat! let's check for a proper upgrade script:
	$upgrade_script_url = 'https://raw.github.com/cashmusic/DIY/master/installers/php/update/updatescripts/'
						. $cash_admin->page_data['platform_version']
						. '.php';
	$upgrade_script_contents = CASHSystem::getURLContents($upgrade_script_url);
	if ($upgrade_script_contents) {
		// okay rad, got an upgrade script too.
		$cash_admin->page_data['upgrade_available'] = true;
	}
} else {
	$cash_admin->page_data['is_edge'] = true;
}

$cash_admin->setPageContentTemplate('settings_upgrade');
?>