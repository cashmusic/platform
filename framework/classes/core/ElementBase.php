<?php
/**
 * Abstract base for all elements
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 * This file is generously sponsored by Francois Wolmarans
 *
 **/
abstract class ElementBase extends CASHData {
	protected $element_id, $session_id, $status_uid, $template, $element_data, $original_request, $original_response, $options, $element, $appdata, $unlocked=false, $mustache=false;
	public $type  = 'unknown';
	public $name  = 'Unknown Element';
	public $error = '';

	abstract public function getData();

	public function __construct($element_id=0,$element=false,$status_uid=false,$original_request=false,$original_response=false) {
		// FYI: the element class takes an element object by reference because
		// ElementPlant needs to query the element_type anyway. So there didn't
		// seem like much point in hitting the database twice every request.
		//
		// This is a note to myself as much as anyone else. Always feels silly.
		// -- jvd
		$this->element = $element;
		$this->element_id = $element_id;
		$this->session_id = false;
		$this->original_request = $original_request;
		$this->original_response = $original_response;
		$this->status_uid = $status_uid;
		$this->template = 'default';
		if (isset($_REQUEST['element_id'])) {
			if ($_REQUEST['element_id'] != $this->element_id) {
				$this->status_uid = false;
			}
		}
		if (isset($_REQUEST['session_id'])) {
			$this->session_id = $_REQUEST['session_id'];
		} else {
			$session_request = new CASHRequest(
				 array(
					  'cash_request_type' => 'system',
					  'cash_action' => 'startjssession'
				 )
			);
			if ($session_request->response['payload']) {
				$s = json_decode($session_request->response['payload'],true);
				$this->session_id = $s['id'];
			}
		}
		$this->startSession($this->session_id);
		$this->options = $element['options'];
		if ($this->isUnlocked()) {
			$this->unlocked = true;
		}
		$this->element_data = array(
			'element_id' => $this->element_id,
			'element_type' => $this->type,
			'status_uid' => $this->status_uid,
			'user_id' => $this->element['user_id'],
			'www_url' => CASH_PUBLIC_URL,
			'api_url' => CASH_API_URL,
			'session_id' => $this->session_id
		);
		if (isset($_SERVER['HTTP_HOST'])) {
			$this->element_data['current_url'] = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		}
		$this->appdata = $this->getAppData();
		if (is_array($this->appdata)) {
			if (isset($this->appdata['copy'])) {
				if (is_array($this->appdata['copy']['en'])) {
					foreach ($this->appdata['copy']['en'] as $key => $val) {
						$this->element_data['copy_' . $key] = $val;
					}
				}
			}
		}
		if (is_array($this->options)) {
			$this->element_data = array_merge($this->element_data,$this->options);
		}
		if (file_exists(CASH_PLATFORM_ROOT . '/lib/mustache/Mustache.php')) {
			include_once(CASH_PLATFORM_ROOT . '/lib/mustache/Mustache.php');
			$this->mustache = new Mustache;
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

	public function getMarkup() {
		if ($this->template == 'default') {
			$this->element_data['template'] = $this->getTemplate('default');
		}
		$this->getData(); // call getData() first as it not only sets data but the correct template
		if ($this->error) {
			$this->element_data['error_message'] = $this->error;
		}
		return $this->mustache->render($this->element_data['template'],$this->element_data);
	}

	public function setTemplate($template_name) {
		$this->template = $template_name;
		$this->element_data['template'] = $this->getTemplate($template_name);
	}

	public function getTemplate() {
		if (file_exists(CASH_PLATFORM_ROOT . '/elements/' . $this->type . '/templates/' . $this->template  . '.mustache')) {
			return file_get_contents(CASH_PLATFORM_ROOT . '/elements/' . $this->type . '/templates/' . $this->template . '.mustache');
		} else {
			return false;
		}
	}

	public function getAppData() {
		if (file_exists(CASH_PLATFORM_ROOT . '/elements/' . $this->type . '/app.json')) {
			$app_json = json_decode(file_get_contents(CASH_PLATFORM_ROOT . '/elements/' . $this->type . '/app.json'),true);
			return $app_json;
		} else {
			return false;
		}
	}

	public function setError($msg) {
		$this->error = $msg;
	}

	public function getError() {
		if ($this->error) {
			return $this->error;
		} else {
			return false;
		}
	}

} // END class
?>
