<?php
function getElementsData() {
	$elements_dirname = ADMIN_BASE_PATH.'/includes/elements';
	if ($elements_dir = opendir($elements_dirname)) {
		$tmpArray = array();
		while (false !== ($dir = readdir($elements_dir))) {
			if (substr($dir,0,1) != "." && is_dir($elements_dirname . '/' . $dir)) {
				$tmpKey = strtolower($dir);
				if (@file_exists($elements_dirname . '/' . $dir . '/metadata.json')) {
					$tmpValue = json_decode(@file_get_contents($elements_dirname . '/' . $dir . '/metadata.json'));
					if ($tmpValue) {
						$tmpArray["$tmpKey"] = $tmpValue;
					}
				}
			}
		}
		closedir($elements_dir);
		if (count($tmpArray)) {
			return $tmpArray;
		} else {
			return false;
		}
	} else {
		echo 'not dir';
		return false;
	}
}

function getEffectiveUser() {
	$helper_cash_request = new CASHRequest();
	$result = $helper_cash_request->sessionGetPersistent('cash_effective_user');
	unset($helper_cash_request);
	return $result;
}
?>