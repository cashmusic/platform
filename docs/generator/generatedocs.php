<?php 
function parseComments($filename) {
	$regex = "/(?:\/\*\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)/"; 

	if (file_exists($filename)) {
		if (strpos($filename,'.md')) {
			include_once(dirname(__FILE__) . '/lib/markdown.php');
			return Markdown(file_get_contents($filename));
		} else {
			$file_contents = file_get_contents($filename);
			preg_match_all($regex, $file_contents, $comments); // parse out docblocks
			if (basename($filename) !== 'cashmusic.php') {
				$function_names = array(basename($filename, ".php"));
			} else {
				$function_names = array('cashmusic.php');
			}

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
			$replace_these = array("\n","@package");
			$replace_these_with = array('<br />','</span><br /><br />@package');
			$return_string .= '<h1 class="classname">' . $key . '</h1>'
			 . '<span class="classdescription">' . str_replace($replace_these,$replace_these_with,$comment);
			$return_string = str_replace(
				'http://www.gnu.org/licenses/agpl-3.0.html',
				'<a href="http://www.gnu.org/licenses/agpl-3.0.html">http://www.gnu.org/licenses/agpl-3.0.html</a>',
				$return_string
			);
			$first_comment = false;
		} else {
			$replace_these = array("\n\n","\n@");
			$replace_these_with = array('<br /><br />','<br />@');
			$parsed_function_name = str_replace('$','<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$',$key);
			if (strpos($parsed_function_name,'<br />')) {
				$parsed_function_name = str_replace(')','<br />)',$parsed_function_name);
			}
			$return_string .= '<h2 class="functionname">' . $parsed_function_name . '</h2>'
			 . '<div class="commentblock">' . str_replace($replace_these,$replace_these_with,$comment) . '</div>';
		}
	}
	return $return_string;
}

function buildDocOutput($comments_or_text,$index_array,$is_index=false) {
	$final_doc_contents = '';
	if (file_exists(dirname(__FILE__) . '/page_header.inc')) {
		$final_doc_contents .= file_get_contents(dirname(__FILE__) . '/page_header.inc');
	}
	$final_doc_contents .= '<div id="pagemenu">' . buildMenu($index_array,$is_index) . '</div>';
	
	if (is_array($comments_or_text)) {
		$final_doc_contents .= formatParsedArray($comments_or_text);
	} else {
		$final_doc_contents .= $comments_or_text;
	}
	if (file_exists(dirname(__FILE__) . '/page_footer.inc')) {
		$final_doc_contents .= file_get_contents(dirname(__FILE__) . '/page_footer.inc');
	}
	$replace_these = array('@param','@return');
	$replace_these_with = array('<span class="param">@param</span>','<span class="return">@return</span>');
	return str_replace($replace_these,$replace_these_with,$final_doc_contents);
}

function buildMenu($index_array,$is_index=false) {
	$nav_level = '../';
	if ($is_index) {
		$nav_level = '';
	}
	$first_category = true;
	$return_string = '<h2><a href="' . $nav_level . 'index.html">Index</a></h2>';
	$usecolor = 0;
	foreach ($index_array as $index => $item_list) {
		if ($index !== 'home') {
			$return_string .= '<h2>' . $index . '</h2>';
			$usecolor++;
			if (count($item_list)) {
				$return_string .= '<ul class="nobullets">';
				foreach ($item_list as $key => $item) {
					if (is_dir($item)) {
						if ($tmp_dir = opendir($item)) {
							while (false !== ($file = readdir($tmp_dir))) {
								if (substr($file,0,1) != "." && !is_dir($file)) {
									$return_string .= '<li>' . formatMenuLink($nav_level,basename($file, ".php"),$file,$index,$usecolor) . '</li>';
								}
							}
							closedir($tmp_dir);
						}
					} else {
						$return_string .= '<li>' . formatMenuLink($nav_level,$key,$item,$index,$usecolor) . '</li>';
					}
				}
				$return_string .= '</ul>';
			}
		}
	}
	return $return_string;
}

function formatMenuLink($nav_level,$key,$item,$index,$color=1) {
	$return_string = '';
	if ($color > 5) { 
		$color = 0;
	}
	$replace_these = array(' ','(',')','.php','_');
	$return_string .= '<a href="' . $nav_level . strtolower(str_replace(' ','',$index)) . '/' . strtolower(str_replace($replace_these,'',$key)) . '.html" class="usecolor' . $color . '">';
	$return_string .= $key . '</a>';
	return $return_string;
}

function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
			} 
		} 
		reset($objects); 
		rmdir($dir); 
	} 
}

function readAndWriteAllDocs($index_array,$output_dir) {
	// this will blow away all old docs and make a new set each time. don't be precious.
	foreach ($index_array as $index => $item_list) {
		if ($index == 'home') {
			file_put_contents($output_dir . '/index.html',buildDocOutput(parseComments($item_list[0]),$index_array,true));
		} else {
			$tmp_dirname = $output_dir . '/' . strtolower(str_replace(' ','',$index));
			if (is_dir($tmp_dirname)) {
				rrmdir($tmp_dirname);
			}
			if (mkdir($tmp_dirname)) {
				if (count($item_list)) {
					foreach ($item_list as $key => $item) {
						$replace_these = array(' ','(',')','.php','_');
						if (is_dir($item)) {
							if ($tmp_dir = opendir($item)) {
								while (false !== ($file = readdir($tmp_dir))) {
									if (substr($file,0,1) != "." && !is_dir($file)) {
										file_put_contents($tmp_dirname . '/' . strtolower(str_replace($replace_these,'',basename($file,'.php'))) . '.html',buildDocOutput(parseComments($item . '/' . $file),$index_array));
									}
								}
								closedir($tmp_dir);
							}
						} else {
							file_put_contents($tmp_dirname . '/' . strtolower(str_replace($replace_these,'',$key)) . '.html',buildDocOutput(parseComments($item),$index_array));
						}
					}
				}
			} else {
				return false;
			}
		}
	}
	return true;
}

$index_array = array(
	'home' => array(
		dirname(__FILE__) . '/../welcome.md'
	),
	'Getting Started' => array(
		'Overview' => dirname(__FILE__) . '/../README.md',
		'Quick Start' => dirname(__FILE__) . '/../../README.md',
		'Style Guide (code)' => dirname(__FILE__) . '/../styleguidecode.md'
	),
	'Bootstrap Script' => array(
		'cashmusic.php' => dirname(__FILE__) . '/../../framework/php/cashmusic.php'
	),
	'Core Classes' => array(
		dirname(__FILE__) . '/../../framework/php/classes/core'
	),
	'Plant Classes' => array(
		dirname(__FILE__) . '/../../framework/php/classes/plants'
	)
);


$output_dir = dirname(__FILE__) . '/..';
if (readAndWriteAllDocs($index_array,$output_dir)) {
	echo "success creating docs\n\n";
} else {
	echo "failed to create docs\n\n";
}


//echo buildDocOutput(parseComments('../README.md'),$index_array);
?>