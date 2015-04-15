<?php
// include the necessary bits, define the page directory
// Define constants too
$root = __DIR__;
$cashmusic_root = realpath($root . "/../../framework/cashmusic.php");

if (!file_exists($cashmusic_root)) {
	$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
	// env settings allow use on multi-server, multi-user instances
	if ($cash_settings) {
		// thanks to json_decode this will be null if the
		if (isset($cash_settings['platforminitlocation'])) {
			$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
		}
	}
}

$settings = array(
	'admin_base_path' => $root,
	'admin_www_base_path' => '/admin',
	'cash_platform_path' => $cashmusic_root
);

/*********************************************************
 *
 * OPTIONAL SETTINGS
 *
 *********************************************************/
$settings['allow_signups'] = true; // should people be able to sign up from the admin page?
$settings['cdn_url'] = 'https://cashmusic.org/admin'; // base CDN for some static assets (img tags, mostly)
$settings['jquery_url'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'; // CDN for jQuery
$settings['jqueryui_url'] = '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js'; // CDN for jQuery UI
$settings['minimum_password_length'] = 10; // doesn't allow passwords shorter than this value
$settings['subdomain_usernames'] = true; // subdomain or subdirectory for user URL scheme (x.cashmusic.org v cashmuisc.org/x)

// finally look for admin.ini.php for custom settings. if it exists pull it in.
if (file_exists(__DIR__.'/admin.ini.php')) {
	$settings = array_merge($settings,parse_ini_file(__DIR__.'/admin.ini.php'));
}

foreach ($settings as $key => $value) {
	define(strtoupper($key),$value);
}
?>
