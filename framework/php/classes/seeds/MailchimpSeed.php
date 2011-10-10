<?php
/**
 * Mailchimp seed to connect to the mailchimp 1.3 API
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
class MailchimpSeed extends SeedBase {
	protected $mailchimp;
	public $url;

	public function __construct($apikey) {
		$this->settings_type = 'com.mailchimp';
		$this->connectDB();
		$this->getCASHSettings();
		require_once(CASH_PLATFORM_ROOT.'/lib/mailchimp/MCAPI.class.php');
		$this->mailchimp = new MCAPI($apikey);
		$this->url       = 'http://us2.api.mailchimp.com/1.3/';
	}
} // END class
?>
