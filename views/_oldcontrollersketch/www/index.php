<?php
// set initial variabes
$appPath = '../app/';
$daoPath = '../dao/';
$libPath = '../lib/';

// grab subdomain (context) and path (path) from .htaccess redirect
if (isset($_REQUEST['c'])) {
	$request_context = $_REQUEST['c'];
} else {
	$request_context = null;
}
if (isset($_REQUEST['p'])) {
	$request_path = trim($_REQUEST['p'],'/');
} else {
	$request_path = null;
}

require_once($appPath.'Request.php');
require_once($appPath.'assembler/Assembler.php');
require_once($appPath.'processor/Processor.php');
$userRequest = new Request($request_context,$request_path,$_POST);

	/* 
	TESTS AND QUICK OUTPUT OF CONTEXT/COMMAND/ARGS
	
	test ability to instantiate new object referentially in php < 5.3
	...SUCCESS
	 
	$requestObject = 'Request';
	$userRequest = new $requestObject($request_context,$request_path,$_POST);
	*/
	
	/*
	echo parsed context/command/args on screen as test...
	*/
	echo 'Context: ' . $userRequest->getContext() . '<br />';
	echo 'Command: ' . $userRequest->getCommand() . '<br />';
	echo 'Args: <br />';
	foreach ($userRequest->getArgs() as $key => $value) {
		echo "$key = '$value'<br />";
	}

?>