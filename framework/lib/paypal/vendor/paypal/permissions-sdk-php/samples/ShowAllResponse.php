
<?php
if(isset($service) && $service instanceof PermissionsService) {
	?>
<table id="apiResponse">
	<tr>
		<td>Request:</td>
	</tr>
	<tr>
		<td><textarea rows="10" cols="100"><?php echo $service->getLastRequest();?></textarea>
		</td>
	</tr>
	<tr>
		<td>Response:</td>
	</tr>
	<tr>
		<td><textarea rows="10" cols="100"><?php echo $service->getLastResponse();?></textarea>
		</td>
	</tr>
</table>
<br />
<?php 
}
if(isset($response)) {
	echo "<pre>";
	print_r($response);
	echo "</pre>";
}
?>
<a href="index.php">Home</a>