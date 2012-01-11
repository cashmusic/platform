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
	protected $element_id, $status_uid, $original_request, $options, $element, $unlocked = false;
	const type = 'unknown';
	const name = 'Unknown Element';

	abstract public function getMarkup();

	public function __construct($element_id=0,$element=false,$status_uid=false,$original_request=false,$original_response=false) {
		// FYI: the element class takes an element object by reference because
		// ElementPlant needs to query the element_type anyway. So there didn't 
		// seem like much point in hitting the database twice every request.
		//
		// This is a note to myself as much as anyone else. Always feels silly.
		// -- jvd
		$this->element = $element;
		$this->element_id = $element_id;
		$this->original_request = $original_request;
		$this->original_response = $original_response;
		$this->status_uid = $status_uid;
		if (isset($_REQUEST['element_id'])) {
			if ($_REQUEST['element_id'] != $this->element_id) {
				$this->status_uid = false;
			}
		}
		$this->options = $element['options'];
		if ($this->isUnlocked()) {
			$this->unlocked = true;
		}
		// check for an init() in the defined element. if it exists, call it
		if (method_exists($this,'init')) {
			$this->init();
		}
	}

	public function lock() {
		$lock_session = $this->sessionGet('unlocked_elements');
		if (is_array($lock_session)) {
			$key = array_search($this->element_id, $lock_session);
			if ($key !== false) {
				unset($lock_session[$key]);
				$this->sessionSet('unlocked_elements',$lock_session);
			}
		}
		$this->unlocked = false;
	}

	public function unlock() {
		$lock_session = $this->sessionGet('unlocked_elements');
		if (is_array($lock_session)) {
			$key = array_search($this->element_id, $lock_session);
			if ($key === false) {
				$lock_session[] = $this->element_id;
				$this->sessionSet('unlocked_elements',$lock_session);
			}
		} else {
			$this->sessionSet('unlocked_elements',array($this->element_id));
		}
		$this->unlocked = true;
	}

	public function isUnlocked() {
		$lock_session = $this->sessionGet('unlocked_elements');
		if (is_array($lock_session)) {
			$key = array_search($this->element_id, $lock_session);
			if ($key !== false) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function getName() {
		return self::name;
	}

	public function getType() {
		return self::type;
	}

} // END class 
?>