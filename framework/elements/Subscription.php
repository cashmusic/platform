<?php
/**
 * Email For Download element
 *
 * @package downloadcodes.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by Anant Narayanan [anant@kix.in]
 * define FALSE TRUE — Just kidding.
 *
 **/

use Cashmusic\Elements\subscription as SubscriptionElement;

class Subscription extends ElementBase {
	public $type = 'subscription';
	public $name = 'Subscription';

	public function getData() {

        $this->startSession();

        //$session = new CASHRequest(null);
        $user_id = $this->sessionGet("user_id");
        $plan_id = $this->sessionGet("plan_id");
        $authenticated = $this->sessionGet("subscription_authenticated");

		// this is where we get data
		$subscription_element = new SubscriptionElement\Data(
            $this->element_data['user_id'],
            $this->element_data['plan_id']
			);

		$this->element_data = array_merge($this->element_data, $subscription_element->data);

        if (!$this->element_data['paypal_connection'] && !$this->element_data['stripe_public_key']) {
            $this->setError("No valid payment connection found.");
        }

        // if we're logged in already, show them the my account button instead of login
        if ($plan_id == $this->element_data['plan_id'] && $authenticated) {
            $this->element_data['logged_in'] = true;
        }

        // authentication process start
		if (!empty($_REQUEST['key'])) {
            $validate_request = new CASHRequest(
                array(
                    'cash_request_type' => 'system',
                    'cash_action' => 'validateresetflag',
                    'address' => $_REQUEST['address'],
                    'key' => $_REQUEST['key']
                )
            );

            if ($validate_request->response['payload']) {
                $this->element_data['key'] = true;
				$email = isset($_REQUEST['address']) ? $_REQUEST['address'] : "";

				if (empty($email)) {
                    $this->element_data['error_message'] = "Something went wrong.";

                    return $this->element_data;
				}

                $this->sessionSet('email_address', $email);

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuseridforaddress',
                        'address' => $email
                    )
                );
                if ($user_request->response['payload']) {
                    $this->sessionSet('user_id', $user_request->response['payload']);
				} else {
                    $this->element_data['error_message'] = "We couldn't find your user.";
                    return $this->element_data;
				}
			} else {
                $this->element_data['error_message'] = "Something went wrong.";
			}

		}

		if (isset($_REQUEST['state'])) {

        	// set state and fire the appropriate method in Element\State class

            $this->element_data['email_address'] = $this->sessionGet('email_address');
            $this->element_data['user_id'] = $this->sessionGet('user_id');

        	$subscription_states = new SubscriptionElement\States($_REQUEST['state']);

			$subscription_states->router(function($template, $values) {
                $this->setTemplate($template);
                $this->element_data = array_merge($this->element_data, $values);
			});


            if ($_REQUEST['state'] == "success") {
                $this->setTemplate('success');
            }

			if ($_REQUEST['state'] == "verified") {

                $user_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'getuser',
                        'user_id' => $this->element_data['user_id']
                    )
                );

                $this->element_data['has_password'] = false;

                if ($user_request->response['payload']) {

                	if ($user_request->response['payload']['is_admin']) {
                		$this->element_data['has_password'] = true;
					}
				}

				$this->setTemplate('settings');
			}

			if ($_REQUEST['state'] == "validatelogin") {

				// check if the passwords actually match
				if($_REQUEST['password'] != $_REQUEST['confirm_password']) {
					$this->element_data['error_message'] = "Your password confirmation doesn't match.";
                    $this->setTemplate('settings');
				}

                if (!defined('MINIMUM_PASSWORD_LENGTH')) {
                    define('MINIMUM_PASSWORD_LENGTH',10);
                }
                if (strlen($_REQUEST['password']) < MINIMUM_PASSWORD_LENGTH) {
                    $this->element_data['error_message'] = "Minimum password lengh of 10 characters.";
                    $this->setTemplate('settings');
                }

				// validate the request to change things

                $password_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'system',
                        'cash_action' => 'setlogincredentials',
                        'user_id' => $this->element_data['user_id'],
						'password' => $_REQUEST['password'],
						'is_admin' => true
                    )
                );

                if ($password_request->response['payload'] !== false) {
                    $this->setTemplate('logged_in_index');
                } else {
                    $this->element_data['error_message'] = "There was an error setting your password.";
                    $this->setTemplate('settings');
				}
			}

            if ($_REQUEST['state'] == "login") {
                $this->setTemplate('login');
            }

            if ($_REQUEST['state'] == "validate_login") {
                // verify login
                $email = (isset($_REQUEST['email'])) ? trim($_REQUEST['email']) : false;
                $password = (isset($_REQUEST['password'])) ? trim($_REQUEST['password']) : false;

                $plan_id = (isset($_REQUEST['plan_id'])) ? $_REQUEST['plan_id'] : false;

                $password_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'loginsubscriber',
                        'email' => $email,
                        'password' => $password,
                        'plan_id' => $plan_id
                    )
                );

                if ($password_request->response['payload']) {
                	// valid login + valid subscription
                	if ($password_request->response['payload'] == "200") {

                        // we need to make sure this is isolated by subscription---
                        // maybe later we can actually have subscriptions switchable

                        $this->setTemplate('logged_in_index');
					}

                    if ($password_request->response['payload'] == "401") {
                        $this->element_data['error_message'] = "Sorry, that's not a valid subscription login.";
                        $this->setTemplate('login');
                    }
				}



				/*$this->element_data['error_message'] = "We're cool bro";

                $data_request = new CASHRequest(null);
                $session = $data_request->getAllSessionData();

                $this->setTemplate('logged_in_index');*/

                //$this->setTemplate('logged_in_index');
				// or return to login with errors

            }


			if ($_REQUEST['state'] == "logged_in_index") {

            	// we need to make sure this is isolated by subscription---
				// maybe later we can actually have subscriptions switchable
                $this->setTemplate('logged_in_index');
			}
		}



		return $this->element_data;
	}
} // END class
?>
