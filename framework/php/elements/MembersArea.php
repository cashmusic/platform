<?php
/**
 * Members Area element
 *
 * @package membersarea.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class MembersArea extends ElementBase {
	public $type = 'membersarea';
	public $name = 'Members Area';

	public function getData() {
		$show_secure_content = false;
		//$this->element_data['browserid_js'] = CASHSystem::getBrowserIdJS($this->element_id);
		if ($this->status_uid == 'people_signintolist_200') {
			$show_secure_content = true;
		} elseif ($this->status_uid == 'people_signintolist_400') {
			// sign-in failed, try element-specific password and check that the 
			// address is for realy realz on the list
			if (trim($this->original_request['password']) == trim($this->options['alternate_password']) && trim($this->options['alternate_password']) != '') {
				$status_request = new CASHRequest(array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getaddresslistinfo',
					'address' => $this->original_request['address'],
					'list_id' => $this->options['email_list_id']
				));
				if ($status_request->response['payload']) {
					$show_secure_content = true;
				}
			}
		}
		if ($show_secure_content) {
			if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
				include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
			}
			$this->element_data['secure_content'] = Markdown($this->element_data['secure_content']);
			$this->setTemplate('success');
		}
		return $this->element_data;
	}
} // END class 
?>