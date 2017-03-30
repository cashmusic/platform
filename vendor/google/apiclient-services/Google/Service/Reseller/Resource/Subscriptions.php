<?php
/*
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * The "subscriptions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $resellerService = new Google_Service_Reseller(...);
 *   $subscriptions = $resellerService->subscriptions;
 *  </code>
 */
class Google_Service_Reseller_Resource_Subscriptions extends Google_Service_Resource
{
  /**
   * Activates a subscription previously suspended by the reseller
   * (subscriptions.activate)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function activate($customerId, $subscriptionId, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId);
    $params = array_merge($params, $optParams);
    return $this->call('activate', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Changes the plan of a subscription (subscriptions.changePlan)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param Google_Service_Reseller_ChangePlanRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function changePlan($customerId, $subscriptionId, Google_Service_Reseller_ChangePlanRequest $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('changePlan', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Changes the renewal settings of a subscription
   * (subscriptions.changeRenewalSettings)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param Google_Service_Reseller_RenewalSettings $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function changeRenewalSettings($customerId, $subscriptionId, Google_Service_Reseller_RenewalSettings $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('changeRenewalSettings', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Changes the seats configuration of a subscription (subscriptions.changeSeats)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param Google_Service_Reseller_Seats $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function changeSeats($customerId, $subscriptionId, Google_Service_Reseller_Seats $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('changeSeats', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Cancels/Downgrades a subscription. (subscriptions.delete)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param string $deletionType Whether the subscription is to be fully cancelled
   * or downgraded
   * @param array $optParams Optional parameters.
   */
  public function delete($customerId, $subscriptionId, $deletionType, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId, 'deletionType' => $deletionType);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a subscription of the customer. (subscriptions.get)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function get($customerId, $subscriptionId, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Creates/Transfers a subscription for the customer. (subscriptions.insert)
   *
   * @param string $customerId Id of the Customer
   * @param Google_Service_Reseller_Subscription $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string customerAuthToken An auth token needed for transferring a
   * subscription. Can be generated at https://www.google.com/a/cpanel/customer-
   * domain/TransferToken. Optional.
   * @return Google_Service_Reseller_Subscription
   */
  public function insert($customerId, Google_Service_Reseller_Subscription $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Lists subscriptions of a reseller, optionally filtered by a customer name
   * prefix. (subscriptions.listSubscriptions)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string customerAuthToken An auth token needed if the customer is
   * not a resold customer of this reseller. Can be generated at
   * https://www.google.com/a/cpanel/customer-domain/TransferToken.Optional.
   * @opt_param string customerId Id of the Customer
   * @opt_param string customerNamePrefix Prefix of the customer's domain name by
   * which the subscriptions should be filtered. Optional
   * @opt_param string maxResults Maximum number of results to return
   * @opt_param string pageToken Token to specify next page in the list
   * @return Google_Service_Reseller_Subscriptions
   */
  public function listSubscriptions($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Reseller_Subscriptions");
  }
  /**
   * Starts paid service of a trial subscription (subscriptions.startPaidService)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function startPaidService($customerId, $subscriptionId, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId);
    $params = array_merge($params, $optParams);
    return $this->call('startPaidService', array($params), "Google_Service_Reseller_Subscription");
  }
  /**
   * Suspends an active subscription (subscriptions.suspend)
   *
   * @param string $customerId Id of the Customer
   * @param string $subscriptionId Id of the subscription, which is unique for a
   * customer
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Subscription
   */
  public function suspend($customerId, $subscriptionId, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'subscriptionId' => $subscriptionId);
    $params = array_merge($params, $optParams);
    return $this->call('suspend', array($params), "Google_Service_Reseller_Subscription");
  }
}
