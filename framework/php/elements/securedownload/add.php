<?php if (!isset($_POST['doelementadd'])) { ?>
	<form name="emailcollection" method="post" action="" id="securedownload">
		<input type="hidden" name="doelementadd" value="makeitso" />
		<input type="hidden" name="element_type" value="securedownload" />
		<h3>Element Details</h3>

		<label for="element_name">Name</label><br />
		<input type="text" id="element_name" name="element_name" placeholder="Give It A Name" /> 

		<div class="row_seperator">.</div>
		<div class="col_oneoftwo">

			<label for="asset_id">Verification Mailing List</label><br />
			<select id="email_list_id" name="email_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('people_lists'); ?>
			</select>
			<br /><br />
			<input type='checkbox' class='checkorradio' name='skip_login' value='' /> <label for="skip_login">Skip login (use on secure sites)</label>
			
		</div>
		<div class="col_oneoftwo lastcol">

			<label for="asset_id">The Downloadable Asset</label><br />
			<select id="asset_id" name="asset_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('assets',false,$cash_admin->getAllFavoriteAssets()); ?>
			</select>
			
		</div>

		<div class="row_seperator">.</div>
		<br />
		<label for="alternate_password">Universal Password</label><br />
		<input type="text" id="alternate_password" name="alternate_password" value="" placeholder="A password or phrase to unlock this for every person." />
		
		<div class="row_seperator">.</div>
		<label for="message_success">Success Message</label><br />
		<input type="text" id="message_success" name="message_success" value="Click here for your download:" />

		<div class="row_seperator">.</div>
		<div>
			<br />
			<input class="button" type="submit" value="Add That Element" />
		</div>

	</form>
		
<?php } else {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	if (isset($_POST['skip_login'])) {
		$skip_login = 1;
	} else {
		$skip_login = 0;
	}
	
	$element_add_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'addelement',
			'name' => $_POST['element_name'],
			'type' => $_POST['element_type'],
			'options_data' => array(
				'alternate_password' => $_POST['alternate_password'],
				'message_success' => $_POST['message_success'],
				'email_list_id' => $_POST['email_list_id'],
				'skip_login' => $skip_login,
				'asset_id' => $_POST['asset_id']
			),
			'user_id' => $effective_user
		)
	);
	if ($element_add_request->response['status_uid'] == 'element_addelement_200') {
	?>
	
		<h3>Success</h3>
		<p>
		Your new <b>Email Collection</b> element is ready to go. To begin using it immediately insert
		this embed code on any page:
		</p>
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $element_add_request->response['payload']; ?>); // CASH element (<?php echo $_POST['element_name'] . ' / ' . $_POST['element_type']; ?>) ?&gt;
		</code>
		<br />
		<p>
		Enjoy!
		</p>

	<?php } else { ?>
		
		<h3>Error</h3>
		<p>
		There was a problem creating the element. <a href="./">Please try again.</a>
<!-- <? var_dump($element_add_request->response) ?> -->
		</p>

<?php 
	}
}	
?>
