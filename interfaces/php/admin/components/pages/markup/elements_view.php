<?php
if ($request_parameters) {
	if ($page_request->response['status_uid'] == 'element_getelement_200') {
		?>
		The embed code for this element is:
		</p>
		<code>
			&lt;?php CASHSystem::embedElement(<?php echo $page_request->response['payload']['id']; ?>); // CASH element (<?php echo $page_request->response['payload']['name'] . ' / ' . $page_request->response['payload']['type']; ?>) ?&gt;
		</code>
		<br /><br />
		<span class="highlightcopy">Add usage statistics, embed locations, any other analytics...</span>
		<?php
	} else {
		echo "There was a problem getting the element's details. Please <a href=\"" . ADMIN_WWW_BASE_PATH . "/elements/view/\">try again</a>.";
	}
} else {
	echo '<h3>All Defined Elements</h3><br />';
	echo AdminHelper::simpleULFromResponse($cash_admin->getStoredResponse('getelementsforuser'));
}
?>