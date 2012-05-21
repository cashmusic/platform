<?php
	$cash_admin->page_data['platform_path'] = realpath(CASH_PLATFORM_PATH);
	$cash_admin->page_data['platform_root'] = realpath(CASH_PLATFORM_ROOT);
	$cash_admin->setPageContentTemplate('help_gettingstarted');
?>