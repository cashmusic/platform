<?php
function parseComments($filename) {
	$regex = "/(?:\/\*\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)/"; 

	if (file_exists($filename)) {
		$file_contents = file_get_contents($filename);
		preg_match_all($regex, $file_contents, $comments); // parse out docblocks
		$function_names = array();

		// time to find function names
		foreach ($comments[0] as $key => $comment) {
		    $comment_length = strlen($comment);
			// first trim off everything before and including the current doc block
			$file_remainder = substr($file_contents,(strpos($file_contents,$comment) + $comment_length));
			// now find and ditch the first occurence of the function keyword
			$file_remainder = substr($file_remainder,(strpos($file_remainder,'function ') + 9));
			// now pare it down to the function name
			$function_name = trim(substr($file_remainder,0,(strpos($file_remainder,'('))));
			// yeah i know. but here's one last check so we don't get a bunch of extra stuff for abstract function names
			$trim_for_abstract = strrpos($function_name,';');
			if ($trim_for_abstract) {
				$function_name = substr($function_name,0,$trim_for_abstract);
			}
			// finally add it to the name array
			$function_names[] = $function_name;
		}

		// now let's merge and cleanse those so the output data makes sense
		$return_array = array();
		if (count($comments[0]) == count($function_names)) {
			$replace_these = array('*/','/**',' * ','* ',' *',"\t");
			foreach ($function_names as $key => $function) {
				$cleansed_comment = str_replace($replace_these, '', $comments[0][$key]);
				// remove multiple spaces
				$cleansed_comment = preg_replace('! +!', ' ', $cleansed_comment);
				//some last tidying up
				$also_replace_these = array("\n\n","\n@");
				$also_replace_these_with = array('<br /><br />','<br />@');
				$cleansed_comment = str_replace($also_replace_these, $also_replace_these_with, $cleansed_comment);
				$return_array[$function] = $cleansed_comment;
			}
			return $return_array;
		} else {
			// something is bad. sorry.
			return false;
		}
	} else {
		// file not found
		return false;
	}
}

// create an array to house all data for output to mustache, other initial variables
$docs_data = array();
$current_directory = dirname(__FILE__);
include_once($current_directory . '/../../framework/cashmusic.php');

$docs_data['page_content'] = '';
$all_requests = '';
$all_requests_menu = '<li>Request types:</li>';

// ALL THE PLANTS!!!
$all_plants = array(
	'system' => array(
		'classname' => 'SystemPlant',
		'filename' => $current_directory . '/../../framework/classes/plants/SystemPlant.php'
	),
	'asset' =>  array(
		'classname' => 'AssetPlant',
		'filename' => $current_directory . '/../../framework/classes/plants/AssetPlant.php'
	),
	'people' =>  array(
		'classname' => 'PeoplePlant',
		'filename' => $current_directory . '/../../framework/classes/plants/PeoplePlant.php'
	),
	'commerce' =>  array(
		'classname' => 'CommercePlant',
		'filename' => $current_directory . '/../../framework/classes/plants/CommercePlant.php'
	),
	'calendar' =>  array(
		'classname' => 'CalendarPlant',
		'filename' => $current_directory . '/../../framework/classes/plants/CalendarPlant.php'
	),
	'element' =>  array(
		'classname' => 'ElementPlant',
		'filename' => $current_directory . '/../../framework/classes/plants/ElementPlant.php'
	)
);

foreach ($all_plants as $type => $plant) {
	$comments = parseComments($plant['filename']);
	include_once($plant['filename']);

	$plant_name = $plant['classname'];
	$plant = new $plant_name('direct',false);
	$routing_table = $plant->getRoutingTable();
	$actions = array();

	foreach ($routing_table as $action => $details) {
		// reflect the target method for each route, returning an array of params
		$method = new ReflectionMethod($plant, $details[0]);
		$params = $method->getParameters();
		$final_parameters = array();
		foreach ($params as $param) {
			// $param is an instance of ReflectionParameter
			$param_name = $param->getName();
			$param_optional = false;
			$param_default = null;
			if ($param->isOptional()) {
				$param_optional = true;
				$param_default = $param->getDefaultValue();
			}
			$final_parameters[$param_name] = array(
				'optional' => $param_optional,
				'default' => $param_default
			);
		}
		// add to the final array of acceptable actions
		$actions[$action] = array(
			'allowed_methods' => $details[1],
			'parameters' => $final_parameters,
			'comment' => false
		);
		if (isset($comments[$details[0]])) {
			$actions[$action]['comment'] = $comments[$details[0]];
		}
	}

	$final_output = '<h3>PHP Core: ' . ucfirst($type) . ' requests</h3><p>All actions defined for \'' . $type . '\' type requests:';
	foreach ($actions as $action => $details) {
		$final_output .= '<div class="request_action">';
		$final_output .= '<h4 class="action_name">' . $type . ' / ' . $action . '</h4>';
		
		$final_output .= '<div class="action_params">';
		$final_output .= '<b>Allowed methods:</b> ';
		if (is_array($details['allowed_methods'])) {
			$final_output .= implode(", ", $details['allowed_methods']);
		} else {
			$final_output .= $details['allowed_methods'];
		}
		$final_output .= '<div class="params">';
		$final_output .= '<b>Parameters:</b><ul>';
		if (is_array($details['parameters'])) {
			foreach ($details['parameters'] as $name => $paramdetails) {
				$final_output .= '<li>' . $name . ' <i>(';
				if ($paramdetails['optional']) {
					$final_output .= 'default: ' . var_export($paramdetails['default'],true) . ')</i></li>';
				} else {
					$final_output .= 'REQUIRED)</i></li>';
				}
			}
		} else {
			$final_output .= '<li>none.</li>';	
		}
		$final_output .= '</ul></div></div>';
		if ($details['comment']) {
			$final_output .= '<p class="action_comments">' . $details['comment'] . '</p>';
		}
		$final_output .= '</div>';
	}

	$all_requests .= '<div class="section" id="requests_' . $type . '">' . $final_output . '</div>';
	$all_requests_menu .= '<li><a class="sub" href="#requests_' . $type . '">' . ucwords($type) . '</a></li>';

	//$docs_data[$type . 'requests'] = $final_output;
}

// warm up the markdownificator
include_once($current_directory . '/../../framework/lib/markdown/markdown.php');

$defined_index = json_decode(file_get_contents($current_directory . '/index.json'),true);

$docs_data['nav_menu'] = '';

foreach ($defined_index as $key => $value) {
	$mainsectioncode = strtolower(str_replace(' ','', $key));
	$docs_data['page_content'] .= '<div class="section main" id="' . $mainsectioncode . '"><h2>' . $key . '</h2>';
	$docs_data['page_content'] .= Markdown(file_get_contents($current_directory . '/writing/' . $mainsectioncode . '.md'));
	$docs_data['page_content'] .= '</div>';
	$docs_data['nav_menu'] .= '<li class="toplevel"><a href="#' . $mainsectioncode . '">' . $key . '</a>';
	if (count($value['subnav'])) {
		$docs_data['nav_menu'] .= '<ul class="sectionnav">';
		foreach ($value['subnav'] as $sub_value) {
			$subsectioncode = strtolower(str_replace(array(' ','/'),'', $sub_value));
			if ($mainsectioncode == 'phpcore' && $subsectioncode == 'requests') {
				$docs_data['page_content'] .= Markdown(file_get_contents($current_directory . '/writing/' . $mainsectioncode . '_' . $subsectioncode . '.md'));
				$docs_data['nav_menu'] .= $all_requests_menu;
			} else {
				$docs_data['page_content'] .= '<div class="section" id="' . $mainsectioncode . '_' . $subsectioncode . '"><h3>' . $key . ': ' . $sub_value . '</h3>';
				$docs_data['page_content'] .= Markdown(file_get_contents($current_directory . '/writing/' . $mainsectioncode . '_' . $subsectioncode . '.md'));
				$docs_data['page_content'] .= '</div>';
				$docs_data['nav_menu'] .= '<li><a class="sub" href="#' . $mainsectioncode . '_' . $subsectioncode . '">' . $sub_value . '</a></li>';
			}
		}
		$docs_data['nav_menu'] .= '</ul>';
	} else {
		$docs_data['nav_menu'] .= '<div class="sectionnav"></div>';	
	}
	$docs_data['nav_menu'] .= '</li>';
}

$docs_data['page_content'] = str_replace('[PLACEHOLDER - REQUEST CONTENT IS GENERATED AUTOMATICALLY - DO NOT EDIT]', $all_requests, $docs_data['page_content']);

// include Mustache because you know it's time for that
include_once($current_directory . '/../../framework/lib/mustache/Mustache.php');
$magnumpi = new Mustache;

echo $magnumpi->render(file_get_contents($current_directory . '/templates/index.mustache'), $docs_data);
?>