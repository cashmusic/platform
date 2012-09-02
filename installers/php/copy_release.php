<?php
/**
 * CASH Music Release Builder
 *
 * Reads a release_profile.json and copies all needed release files next to it
 * 
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/
if(!defined('STDIN')) { // force CLI, the browser is *so* 2007...
	echo "Please run installer from the command line. usage:<br / >&gt; php copy_release.php <SOURCE> <DESTINATION> -- REQUIRES release_profile.json be present";
} else {
	if (count($argv) < 3) {
		echo "\nWrong. Usage: php copy_release.php <SOURCE> <DESTINATION> -- REQUIRES release_profile.json be present\n";
	} else {
		if (file_exists($argv[2] . '/release_profile.json')) {
			$release_profile = json_decode(file_get_contents($argv[2] . '/release_profile.json'),true);
			$files = $release_profile['blobs'];

			foreach ($files as $file => $hash) {
				$path = pathinfo($file);
				if (!is_dir($argv[2].'/'.$path['dirname'])) mkdir($argv[2].'/'.$path['dirname'],0755,true);
				copy($argv[1].'/'.$file, $argv[2].'/'.$file);
			}

			echo "\n\nRelease created\n\n";
		}
	}
}