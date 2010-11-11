<?php
/**
 * So dumb. So needed.
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class EchoPlant {
	public function __construct($request_type,$request) {
		echo $request_type . ": \n";
		print_r($request);
	}
} // END class 
?>