<?php
if (file_exists(ADMIN_BASE_PATH . '/privacy.md')) {
	if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
		include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
	}
	$cash_admin->page_data['privacy_markup'] = Markdown(file_get_contents(ADMIN_BASE_PATH . '/privacy.md'));
	$cash_admin->setPageContentTemplate('privacy');
}
?>