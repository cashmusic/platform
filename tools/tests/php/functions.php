<?php
function getTestEnv($varname) {
	if (file_exists(dirname(__FILE__) . '/__test_environment.json')) {
		$environment = json_decode(file_get_contents(dirname(__FILE__) . '/__test_environment.json'),true);
		if (isset($environment[$varname])) {
			return $environment[$varname];
		} else {
			return false;
		}
	} else {
		return getenv($varname);
	}
}
?>