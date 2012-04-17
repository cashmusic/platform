<?php
/**
 * Upgrade script: v1 to v2
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

// include('./framework/php/cashmusic.php');

if (CASHRequest::$version == 1) {
	/***************************
	 *
	 * 0. DEFINITIONS
	 *
	 ***************************/
	require_once(CASH_PLATFORM_ROOT.'/classes/core/CASHDBA.php');
	$cash_settings = parse_ini_file(CASH_PLATFORM_ROOT.'/settings/cashmusic.ini.php');

	function simpleXOR($input, $key) {
		// append key on itself until it is longer than the input
		while (strlen($key) < strlen($input)) { $key .= $key; }

		// trim key to the length of the input
		$key = substr($key, 0, strlen($input));

		// Simple XOR'ing, each input byte with each key byte.
		$result = '';
		for ($i = 0; $i < strlen($input); $i++) {
			$result .= $input{$i} ^ $key{$i};
		}
		return $result;
	}


	/***************************
	 *
	 * 1. UPDATE ALL FILES
	 *
	 ***************************/



	/***************************
	 *
	 * 2. DO SCHEMA CHANGES
	 *
	 ***************************/
	$maindb = new CASHDBA(
		$cash_settings['hostname'],
		$cash_settings['username'],
		$cash_settings['password'],
		$cash_settings['database'],
		$cash_settings['driver']
	);


	/***************************
	 *
	 * 2. ENCODE CONNECTION DATA
	 *
	 ***************************/
	$key = $cash_settings['salt'];
	$all_connections = $maindb->getData(
		'connections',
		'*'
	);
	$total_changed_connections = 0;
	foreach ($all_connections as $connection) {
		$result = $maindb->setData(
			'connections',
			array(
				'data' => base64_encode(simpleXOR($connection['data'], $key))
			),
			array(
				'id' => array(
					'condition' => '=',
					'value' => $connection['id']
				)
			)
		);
		if ($result) {
			$total_changed_connections++;
		}
		// simpleXOR(base64_decode($cipher_text), $key);
	}
	echo $total_changed_connections . ' connections encoded<br />';
}
?>