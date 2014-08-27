<?php
/**
 * Pushes changes from the main repo to a hosted instance
 *
 * This script pushes all repo changes tothe cloud install repo or a local copy 
 * of a hosted multi-user instance. It only copies changes (by checking md5 hashes) 
 * and will create any needed directories. It skips any .htaccess files, connection
 * settings, config files, or files named constants.php.
 *
 * The idea is for easy updates to core stuff without over-writing instance 
 * specific files or settings.
 * 
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/
function readStdin($prompt, $valid_inputs = false, $default = '') {
	// Courtesy of http://us3.php.net/manual/en/features.commandline.io-streams.php#101307
	while(!isset($input) || (is_array($valid_inputs) && !in_array(strtolower($input), $valid_inputs))) {
		echo $prompt;
		$input = strtolower(trim(fgets(STDIN)));
		if(empty($input) && !empty($default)) {
			$input = $default;
		}
	}
	return $input;
}

// recursive rmdir:
function profile_directory($dir,$trim_from_output,&$add_to) { 
	if (is_dir($dir)) {
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if (
				$object != "." && 
				$object != ".." && 
				$object != ".DS_Store" && 
				$object != ".git" && 
				$object != ".htaccess" && 
				$object != "cashmusic.ini.php" &&
				$object != "cashmusic_template.ini.php" &&
				$object != "connections.json" &&
				$object != "_connections.json" &&
				$object != "constants.php"
			) { 
				if (filetype($dir."/".$object) == "dir") {
					profile_directory($dir."/".$object,$trim_from_output,$add_to); 
				} else {
					$object_name = ltrim(str_replace($trim_from_output,'',$dir."/".$object),'/');
					if (
						substr($object_name,0,1)  != '/' &&
						substr($object_name,0,1)  != '.' &&
						substr($object_name,0,7)  != 'LICENSE' &&
						substr($object_name,0,8)  != 'Makefile' &&
						substr($object_name,0,9)  != 'README.md' &&
						substr($object_name,0,10) != 'privacy.md' &&
						substr($object_name,0,8)  != 'terms.md' 
					) {
						$add_to[$object_name] = md5_file($dir."/".$object);
					}
				} 
			} 
		}
	} 
}

if(!defined('STDIN')) {
	echo "Command-line only. Usage:<br / >&gt; php push_changes.php";
} else {
	$from_base_dir = readStdin("\nLocation of the master repo (copy from): ", false);
	$to_base_dir   = readStdin("Location of the hosted instance (copy to): ", false);
	$do_copy       = readStdin("\nDo full copy? Answer 'n' for test and report (y/n): ", false, 'n');

	if (strtolower($do_copy) == 'y') {
		$do_copy = true;
	} else {
		$do_copy = false;
	}

	$from_base_dir = rtrim($from_base_dir,DIRECTORY_SEPARATOR);
	$to_base_dir   = rtrim($to_base_dir,DIRECTORY_SEPARATOR);

	$to_scan = array(
		'/framework'         => '/framework',
		'/interfaces/admin'  => '/admin',
		'/interfaces/api'    => '/api',
		'/interfaces/public' => '/public'
	);

	$total_affected = 0; 
	$list_affected  = "\n";
	$copy_successes = 0;
	foreach ($to_scan as $from_dir => $to_dir) {
		$copy_from = array();
		$copy_to   = array();

		profile_directory($from_base_dir.$from_dir,$from_base_dir.$from_dir,$copy_from);
		profile_directory($to_base_dir.$to_dir,$to_base_dir.$to_dir,$copy_to);

		foreach ($copy_from as $filename => $hash) {
			$positive_match = false;
			if (!isset($copy_to[$filename])) {
				// always copy new files
				$positive_match = true;
			} else {
				if ($copy_to[$filename] !== $hash) {
					// only copy existing files if they have a different md5 hash
					$positive_match = true;
				}
			}
			if ($positive_match) {
				$list_affected .= $to_dir . DIRECTORY_SEPARATOR . $filename;
				$total_affected++;
				if ($do_copy) {
					if (!is_dir($to_base_dir.$to_dir.DIRECTORY_SEPARATOR.dirname($filename))) {
						// double-check we have a directory to copy to, if not then make it
						mkdir($to_base_dir.$to_dir.DIRECTORY_SEPARATOR.dirname($filename),0777,true);
					}
					$success = @copy(
						$from_base_dir.$from_dir.DIRECTORY_SEPARATOR.$filename,
						$to_base_dir.$to_dir.DIRECTORY_SEPARATOR.$filename
					);
					if ($success) {
						$list_affected .= " \033[0;32m(copy ok!)\033[0m";
						$copy_successes++;
					} else {
						$list_affected .= " \033[0;31m(copy failed. sad.)\033[0m";
					}
				}
				$list_affected .= "\n";
			}
		}
	}

	echo $list_affected;
	if ($total_affected) {
		$copy_status = '';
		if ($do_copy) {
			if ($copy_successes == $total_affected) {
				$copy_status = " \033[0;32mAll copies successful.\033[0m";
			} else {
				$copy_status = " \033[0;31mThere were errors copying some files.\033[0m";
			}
		}
		echo "\n\n\033[0;32mFound [ $total_affected ] files to update.\033[0m$copy_status\n\n";
	} else {
		echo "\n\n\033[0;32mEverything is up to date. \033[0m\n\n"; 
	}	
}