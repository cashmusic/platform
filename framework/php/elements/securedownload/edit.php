<?php 
$page_data = $cash_admin->getStoredResponse('originalelement',true);
if (isset($_POST['skip_login'])) {
	$skip_login = 1;
} else {
	$skip_login = 0;
}

if (isset($_POST['doelementedit'])) {
	$element_edit_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'editelement',
			'id' => $page_data['id'],
			'name' => $_POST['element_name'],
			'options_data' => array(
				'alternate_password' => $_POST['alternate_password'],
				'message_success' => $_POST['message_success'],
				'email_list_id' => $_POST['email_list_id'],
				'skip_login' => $skip_login,
				'asset_id' => $_POST['asset_id']
			)
		)
	);
	if ($element_edit_request->response['status_uid'] == 'element_editelement_200') {
		$element_edit_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelement',
				'id' => $page_data['id']
			)
		);
		$page_data = $element_edit_request->response['payload'];
	?>
	
		<h3>Success</h3>
		<p>
		</p>
		Your edits have been made and can be seen below. To embed the element us this code:
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $page_data['id']; ?>); // CASH element (<?php echo $page_data['name'] . ' / ' . $page_data['type']; ?>) ?&gt;
		</code>
		<br />

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem editing the element. <a href="./">Please try again.</a>
		</p>

<?php
	}
}
?>
	<form method="post" action="">
		<input type="hidden" name="doelementedit" value="makeitso">
		<input type="hidden" name="element_id" value="<?php echo $page_data['id']; ?>">
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" value="<?php echo $page_data['name']; ?>" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">

			<label for="asset_id">Verification Mailing List</label><br />
			<select id="email_list_id" name="email_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('people_lists',$page_data['options']['email_list_id']); ?>
			</select>
			<?php
			if ($page_data['options']['skip_login']) {
				$checkstring = "checked='checked'";
				
			} else {
				$checkstring = "";
			}
			?>
			<br /><br />
			<input type='checkbox' class='checkorradio' name='skip_login' value='' <?php echo $checkstring; ?> /> <label for="skip_login">Skip login (use on secure sites)</label>

		</div>
		<div class="col_oneoftwo lastcol">

			<label for="asset_id">The Downloadable Asset</label><br />
			<select id="asset_id" name="asset_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('assets',$page_data['options']['asset_id'],$cash_admin->getAllFavoriteAssets()); ?>
			</select>
			
		</div>

		<div class="row_seperator">.</div>
		<br />
		<label for="alternate_password">Universal Password</label><br />
		<input type="text" id="alternate_password" name="alternate_password" value="<?php echo $page_data['options']['alternate_password']; ?>" />

		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="<?php echo $page_data['options']['message_success']; ?>" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Edit The Element" />
		</div>

	</form>