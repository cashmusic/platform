<?php
// set initial variabes
$rootPath = realpath('.');
$appPath = $rootPath . '/app/';
$interfacePath = $rootPath . '/interface/';
$wwwAssetPath = '/interface/';

// grab path from .htaccess redirect
if (isset($_REQUEST['p'])) {
	$request_path = trim($_REQUEST['p'],'/');
} else {
	$request_path = null;
}

require_once($appPath.'userRequest.php');
// require_once($appPath.'assembler/Assembler.php');
// require_once($appPath.'processor/Processor.php');
$userRequest = new userRequest($request_path);
$contentSource = $userRequest->getPage();
if (is_array($userRequest->getSubs())) {
	foreach ($userRequest->getSubs() as $key => $value) {
		$contentSource .=  "_$value";
	}
} else {
	if ($contentSource == 'default') {
		$contentSource = 'login';
	}
}


$contentSource .= ".php";

session_start();
if (file_exists($interfacePath . 'content/' . $contentSource)) {
	include($interfacePath . 'content/' . $contentSource);
} else {
	include($interfacePath . 'content/error.php');
}
include($interfacePath . '/html/default.php');
?>
