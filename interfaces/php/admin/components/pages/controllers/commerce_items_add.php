<?php
$cash_admin->page_data['form_state_action'] = 'doitemadd';
if (isset($request_parameters[1])) {
	if ($request_parameters[0] == 'selectedasset') {
		$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$request_parameters[1],false,true);
	}
} else {
	$cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',0,false,true);
}

$cash_admin->setPageContentTemplate('commerce_items_details');
?>