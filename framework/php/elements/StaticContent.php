<?php
/**
 * Static Content element
 *
 * @package staticcontent.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by \m/
 *
 **/
class StaticContent extends ElementBase {
	public $type = 'staticcontent';
	public $name = 'Plain Text';
	
	public $markdown = false;

	public function getData() {
		if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
			include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
		}
		$this->element_data['storedcotent'] = Markdown($this->element_data['storedcotent']);
		return $this->element_data;
	}
} // END class 
?>