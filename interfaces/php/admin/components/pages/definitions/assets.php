<?php
// add unique page settings:
$page_title = 'Assets: Main';
$page_tips = '';
/*
pagemenu:
Defined in the top-level page (no slashes) — a simple multidimensional array.
The top level of the array is a listing of menu titles like "Actions" or "FAQs"
keyed to arrays of menu options. Menu depth is determined by the number of
slashes in the menu option key, which serves as the link for the menu item.

So: 
$page_memu = array(
	'Actions' => array(
		'assets/add' => 'Add Assets',
			'assets/add/adhoc' => 'Add Ad-Hoc Asset',
			'assets/add/collection' => 'Add A Collection',
			'assets/add/release' => 'Add A Release',
		'assets/find' => 'Find Assets'
	);
);

Will render as:
Actions
- Add Assets
	+ Add Ad-Hoc Asset
	+ Add A Collection
	+ Add A Release
- Find Assets

You dig?

*/
$page_memu = array(
	'Actions' => array(
		'assets/add/' => 'Add Assets',
			'assets/add/single/' => 'Add Single Asset',
			'assets/add/playlist/' => 'Add Playlist',
		'assets/find/' => 'Find Assets'
	)
);

$page_data = array();

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostaccessed',
		'user_id' => getPersistentData('cash_effective_user')
	)
);
$page_data['asset_mostaccessed'] = $page_request->response['payload'];

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => getPersistentData('cash_effective_user')
	)
);
$page_data['asset_recentlyadded'] = $page_request->response['payload'];

?>