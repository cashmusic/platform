<?php
$cash_admin->page_data['ui_title'] = 'CASH Music: Main Page';

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_main_content'] = '<a href="' . ADMIN_WWW_BASE_PATH . '/assets/" class="usecolor1">Assets</a>, your songs, photos, cover art, etc. <a href="' 
		. ADMIN_WWW_BASE_PATH . '/people/" class="usecolor2">People</a>, fans, mailing lists, anyone you need to connect with on a regular basis. <a href="' 
		. ADMIN_WWW_BASE_PATH . '/commerce/" class="usecolor3">Commerce</a> is where you’ll find info on all your orders. And <a href="' 
		. ADMIN_WWW_BASE_PATH . '/calendar/" class="usecolor4">Calendar</a>, keeps a record of all your shows in one place.<br /><br />'
		. 'The last main category is <a href="' . ADMIN_WWW_BASE_PATH . '/elements/" class="usecolor5">Elements</a>, where Assets, People, Commerce, and Calendar can be combined to make customized tools for your site. Things like email collection, song players, and social feeds all just a copy/paste away.<br /><br />'
		. '<div class="moreinfospc">Need more info? Check out the <a href="' . ADMIN_WWW_BASE_PATH . '/help/gettingstarted/" class="helplink">Getting Started</a> page.</div></div>';
}

$cash_admin->setPageContentTemplate('mainpage');
?>