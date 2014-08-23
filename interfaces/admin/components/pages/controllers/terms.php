<?php
if (file_exists(ADMIN_BASE_PATH . '/terms.md')) {
	if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
		include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
	}
	$cash_admin->page_data['terms_markup'] = Markdown(file_get_contents(ADMIN_BASE_PATH . '/terms.md'));
	$cash_admin->setPageContentTemplate('terms');
}
?>