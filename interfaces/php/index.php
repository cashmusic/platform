<?php 
// include the necessary bits, define the page directory
// Define constants too
$root = dirname(__FILE__);
$cashmusic_root = realpath($root . "/../../framework/php/cashmusic.php");

$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
// env settings allow use on multi-server, multi-user instances
if ($cash_settings) {
	// thanks to json_decode this will be null if the 
	if (isset($cash_settings['platforminitlocation'])) {
		$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
	}	
}

require_once($cashmusic_root);

$user_id = 1;

$template_request = new CASHRequest(
	array(
		'cash_request_type' => 'system', 
		'cash_action' => 'getnewesttemplate',
		'user_id' => $user_id
	)
);
$template = $template_request->response['payload'];

if ($template) {
	$element_embeds = false;
	$page_vars = array();
	$found_elements = preg_match_all('/{{{element_(.*?)}}}/',$template,$element_embeds, PREG_PATTERN_ORDER);
	if ($found_elements) {

		foreach ($element_embeds[1] as $element_id) {
			ob_start();
			CASHSystem::embedElement($element_id);
			$page_vars['element_' . $element_id] = ob_get_contents();
			ob_end_clean();
		}
		
	}

	echo CASHSystem::renderMustache($template,$page_vars);
}
?>