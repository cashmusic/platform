<h2>Batch Add Contacts</h2>
<?php
if ($page_message) {
	echo '<p><span class="highlightcopy">' . $page_message . '</span></p>';
}
?>
<p>
	Paste in a list of email addresses, separated by comma or a new line. Optionally
	add to an existing list.
</p>

<form name="batchaddcontacts" method="post" action="">
	<input type="hidden" name="dobatchcontactsadd" value="makeitso" />
	<div>
		<label>Email Addresses</label>
		<textarea id="element_content" name="element_content" class="tall"></textarea>
		
		<div class="row_seperator">.</div>
			<label for="email_list_id">Target List</label><br />
			<select id="email_list_id" name="email_list_id">
				<option value="0">none</option>
				<?php AdminHelper::echoFormOptions('people_lists'); ?>
			</select>
	</div>
	<div>
		<br />
		<input class="button" type="submit" value="Add Contacts" />
	</div>

</form>