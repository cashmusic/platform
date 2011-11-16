<?php
/**
 * Abstract base for all elements
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 * This file is generously sponsored by Francois Wolmarans
 *
 **/
abstract class ElementBase extends CASHData {
	protected $element_id, $status_uid, $options, $element;
	const type = 'unknown';
	const name = 'Unknown Element';

	abstract public function getMarkup();

	public function __construct($element_id=0,$element=false,$status_uid=false) {
		// FYI: the element class takes an element object by reference because
		// ElementPlant needs to query the element_type anyway. So there didn't 
		// seem like much point in hitting the database twice every request.
		//
		// This is a note to myself as much as anyone else. Always feels silly.
		// -- jvd
		$this->element = $element;
		$this->element_id = $element_id;
		$this->status_uid = $status_uid;
		$this->options = $element['options'];
	}

	public function getName() {
		return self::name;
	}

	public function getType() {
		return self::type;
	}

} // END class 
?>