<?php
/**
 * Static Content element
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class StaticContent extends ElementBase {
	const type = 'staticcontent';
	const name = 'Static Content';

	public function getData() {
		$return_array = array(
			'storedcontent' => $this->options->storedcotent
		);
		
		return json_encode($return_array);
	}

	public function getMarkup() {
		return $this->options->storedcotent;	
	}
} // END class 
?>