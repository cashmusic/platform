<?php
$cash_admin->page_data['ui_title'] = '';
$cash_admin->page_data['id'] = $request_parameters[0];

$cash_admin->page_data['connection_options'] = AdminHelper::echoConnectionsOptions('mass_email',0,true);

$cash_admin->setPageContentTemplate('commerce_items_send');
?>
