<?php
// add unique page settings:
$page_title = 'Assets: Main';
$page_tips = 'There’s a pretty huge overlap between assets and content. So the goal here should be placed on adding new assets, finding existing assets, editing metadata, and setting permissions for things. Display of any assets should be largely handled in the content section.';
/*
pagemenu:
Defined in the top-level page (no slashes) — a simple multidimensional array.
The top level of the array is a listing of menu titles like "Actions" or "FAQs"
keyed to arrays of menu options. Menu depth is determined by the number of
slashes in the menu option key, which serves as the link for the menu item.

So: 
$pagememu = array(
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
$pagememu = array(
	'Actions' => array(
		'assets/add/' => 'Add Assets',
			'assets/adhoc/add/' => 'Add Ad-Hoc Asset',
			'assets/collection/add/' => 'Add A Collection',
			'assets/release/add/' => 'Add A Release',
		'assets/find/' => 'Find Assets'
	)
);
?>