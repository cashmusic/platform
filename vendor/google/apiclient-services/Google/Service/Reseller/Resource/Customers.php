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
 * The "customers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $resellerService = new Google_Service_Reseller(...);
 *   $customers = $resellerService->customers;
 *  </code>
 */
class Google_Service_Reseller_Resource_Customers extends Google_Service_Resource
{
  /**
   * Gets a customer resource if one exists and is owned by the reseller.
   * (customers.get)
   *
   * @param string $customerId Id of the Customer
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Customer
   */
  public function get($customerId, $optParams = array())
  {
    $params = array('customerId' => $customerId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Reseller_Customer");
  }
  /**
   * Creates a customer resource if one does not already exist. (customers.insert)
   *
   * @param Google_Service_Reseller_Customer $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string customerAuthToken An auth token needed for inserting a
   * customer for which domain already exists. Can be generated at
   * https://admin.google.com/TransferToken. Optional.
   * @return Google_Service_Reseller_Customer
   */
  public function insert(Google_Service_Reseller_Customer $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Reseller_Customer");
  }
  /**
   * Update a customer resource if one it exists and is owned by the reseller.
   * This method supports patch semantics. (customers.patch)
   *
   * @param string $customerId Id of the Customer
   * @param Google_Service_Reseller_Customer $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Customer
   */
  public function patch($customerId, Google_Service_Reseller_Customer $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Reseller_Customer");
  }
  /**
   * Update a customer resource if one it exists and is owned by the reseller.
   * (customers.update)
   *
   * @param string $customerId Id of the Customer
   * @param Google_Service_Reseller_Customer $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Reseller_Customer
   */
  public function update($customerId, Google_Service_Reseller_Customer $postBody, $optParams = array())
  {
    $params = array('customerId' => $customerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Reseller_Customer");
  }
}
