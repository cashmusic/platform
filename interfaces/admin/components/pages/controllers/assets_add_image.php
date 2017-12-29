<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

// parsing posted data:
if (isset($_POST['doassetadd'])) {

	$parent_id = -1;
	if ($_POST['parent_type'] == 'release') {
		if ($_POST['parent_id']) {
			$parent_id = $_POST['parent_id'];
		}
	}

	$add_response = $admin_request->request('asset')
	                        ->action('addasset')
	                        ->with([
                                'title' => '',
                                'description' => '',
                                'parent_id' => $parent_id,
                                'connection_id' => $_POST['connection_id'],
                                'location' => $_POST['asset_location'],
                                'user_id' => $cash_admin->effective_user_id,
                                'type' => 'image'
							])->get();

	if ($add_response['payload']) {
		// check for metadata settings
		if ($_POST['parent_type'] == 'release') {
			// try getting the parent asset
			$asset_response = $admin_request->request('asset')
			                        ->action('getasset')
			                        ->with(['id' => $_POST['parent_id']])->get();

			// found it. now we can overwrite or extend the original metadata
			if ($asset_response['payload']) {
				// modify the existing chunk o metadata
				$asset = $asset_response['payload'];
				if (is_cash_model($asset)) $asset = $asset->toArray();

				$new_metadata = $asset['metadata'];
				$new_metadata['cover'] = $add_response['payload'];

				// make it public
				$edit_response = $admin_request->request('asset')
				                        ->action('makepublic')
				                        ->with([
                                            'id' => $add_response['payload'],
                                            'commit' => true,
                                            'user_id' => $cash_admin->effective_user_id
										])->get();

				// now make the actual edits
				$edit_response = $admin_request->request('asset')
				                        ->action('editasset')
				                        ->with([
                                            'id' => $_POST['parent_id'],
                                            'user_id' => $cash_admin->effective_user_id,
                                            'metadata' => $new_metadata
										])->get();
			}
			$admin_helper->formSuccess('Success.','/assets/edit/' . $_POST['parent_id']);
		}
		if ($_POST['parent_type'] == 'item') {
			// make it public
			$edit_response = $admin_request->request('asset')
			                        ->action('makepublic')
			                        ->with([
                                        'id' => $add_response['payload'],
                                        'commit' => true,
                                        'user_id' => $cash_admin->effective_user_id
									])->get();

			// tell the item to use the asset
			$item_response = $admin_request->request('commerce')
			                        ->action('edititem')
			                        ->with([
                                        'id' => $_POST['parent_id'],
                                        'descriptive_asset' => $add_response['payload'],
                                        'user_id' => $cash_admin->effective_user_id
									])->get();
			
			$admin_helper->formSuccess('Success.','/commerce/items/edit/' . $_POST['parent_id']);
		}
	} else {
		if ($_POST['parent_type'] == 'release') {
			$admin_helper->formFailure('Error. Something didn\'t work.','/assets/edit/' . $_POST['parent_id']);
		} elseif ($_POST['parent_type'] == 'item') {
			$admin_helper->formFailure('Error. Something didn\'t work.','/commerce/items/edit/' . $_POST['parent_id']);
		} else {
			$admin_helper->formFailure('Error. Something didn\'t work.','/assets/');
		}
	}
}

$cash_admin->page_data['form_state_action'] = 'doassetadd';
$cash_admin->page_data['asset_button_text'] = 'Save changes';
$cash_admin->page_data['ui_title'] = 'Add an image';

$cash_admin->page_data['connection_options'] = $admin_helper->echoConnectionsOptions('assets', 0, true);

if (isset($request_parameters[1])) {
	$cash_admin->page_data['parent_type'] = $request_parameters[0];
	$cash_admin->page_data['parent_id'] = $request_parameters[1];
}

$cash_admin->page_data['assets_add_action'] = true;
$cash_admin->setPageContentTemplate('assets_details_image');
?>
