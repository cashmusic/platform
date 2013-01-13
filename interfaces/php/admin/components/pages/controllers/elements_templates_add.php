<?php
$cash_admin->page_data['template'] = 'Enter your own HTML or use one of the included defaults above.';
$cash_admin->page_data['page_default_template'] = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/page.mustache');
$cash_admin->page_data['embed_default_template'] = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/embed.mustache');
$cash_admin->page_data['adding'] = true;

$cash_admin->page_data['button_text'] = 'Save the template';

$cash_admin->setPageContentTemplate('elements_template_details');
?>