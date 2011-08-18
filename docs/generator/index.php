<?php 
function parseComments($filename) { 
	$regex = "/(?:\/\*\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)/"; 

	if (file_exists($filename)) {
		$file_contents = file_get_contents($filename);
		preg_match_all($regex, $file_contents, $comments); // parse out docblocks

		$function_names = array(basename($filename, ".php"));

		// time to find function names
		foreach ($comments[0] as $key => $comment) {
			if ($key == 0) {
				continue;
			}
		    $comment_length = strlen($comment);
			// first trim off everything before and including the current doc block
			$file_remainder = substr($file_contents,(strpos($file_contents,$comment) + $comment_length));
			// now find and ditch the first occurence of the function keyword
			$file_remainder = substr($file_remainder,(strpos($file_remainder,'function ') + 9));
			// now pare it down to the function name
			$function_name = trim(substr($file_remainder,0,(strpos($file_remainder,'{'))));
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

function formatParsedArray($comments_array) {
	$first_comment = true;
	$return_string = '';
	foreach ($comments_array as $key => $comment) {
		if ($first_comment) {
			$return_string .= '<h1 class="classname">' . $key . '</h1>'
			 . str_replace("\n",'<br />',$comment);
			$return_string = str_replace(
				'http://www.gnu.org/licenses/agpl-3.0.html',
				'<a href="http://www.gnu.org/licenses/agpl-3.0.html">http://www.gnu.org/licenses/agpl-3.0.html</a>',
				$return_string
			);
			$first_comment = false;
		} else {
			$replace_these = array("\n\n","\n@");
			$replace_these_with = array('<br /><br />','<br />@');
			$return_string .= '<h2 class="functionname">' . $key . '</h2>'
			 . str_replace($replace_these,$replace_these_with,$comment);
		}
	}
	return $return_string;
}

function buildDocOutput($comments_array) {
	$final_doc_contents = '';
	if (file_exists('./page_header.inc')) {
		$final_doc_contents .= file_get_contents('./page_header.inc');
	}
	$final_doc_contents .= formatParsedArray($comments_array);
	if (file_exists('./page_footer.inc')) {
		$final_doc_contents .= file_get_contents('./page_footer.inc');
	}
	$replace_these = array('@param','@return');
	$replace_these_with = array('<span class="param">@param</span>','<span class="return">@return</span>');
	return str_replace($replace_these,$replace_these_with,$final_doc_contents);
}

$doc_array = parseComments('../../framework/php/classes/core/PlantBase.php');
echo buildDocOutput($doc_array);
?>