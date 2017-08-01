<?php
/*
 * Copyright 2014 Google Inc.
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
 * The "filterSets" collection of methods.
 * Typical usage is:
 *  <code>
 *   $adexchangebuyer2Service = new Google_Service_AdExchangeBuyerII(...);
 *   $filterSets = $adexchangebuyer2Service->filterSets;
 *  </code>
 */
class Google_Service_AdExchangeBuyerII_Resource_AccountsFilterSets extends Google_Service_Resource
{
  /**
   * Creates the specified filter set for the account with the given account ID.
   * (filterSets.create)
   *
   * @param string $accountId Account ID of the buyer.
   * @param Google_Service_AdExchangeBuyerII_FilterSet $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool isTransient Whether the filter set is transient, or should be
   * persisted indefinitely. By default, filter sets are not transient. If
   * transient, it will be available for at least 1 hour after creation.
   * @return Google_Service_AdExchangeBuyerII_FilterSet
   */
  public function create($accountId, Google_Service_AdExchangeBuyerII_FilterSet $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_AdExchangeBuyerII_FilterSet");
  }
  /**
   * Deletes the requested filter set from the account with the given account ID.
   * (filterSets.delete)
   *
   * @param string $accountId Account ID of the buyer.
   * @param string $filterSetId The ID of the filter set to delete.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AdExchangeBuyerII_Adexchangebuyer2Empty
   */
  public function delete($accountId, $filterSetId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterSetId' => $filterSetId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_AdExchangeBuyerII_Adexchangebuyer2Empty");
  }
  /**
   * Retrieves the requested filter set for the account with the given account ID.
   * (filterSets.get)
   *
   * @param string $accountId Account ID of the buyer.
   * @param string $filterSetId The ID of the filter set to get.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AdExchangeBuyerII_FilterSet
   */
  public function get($accountId, $filterSetId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterSetId' => $filterSetId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdExchangeBuyerII_FilterSet");
  }
  /**
   * Lists all filter sets for the account with the given account ID.
   * (filterSets.listAccountsFilterSets)
   *
   * @param string $accountId Account ID of the buyer.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken A token identifying a page of results the server
   * should return. Typically, this is the value of
   * ListFilterSetsResponse.nextPageToken returned from the previous call to the
   * accounts.filterSets.list method.
   * @opt_param int pageSize Requested page size. The server may return fewer
   * results than requested. If unspecified, the server will pick an appropriate
   * default.
   * @return Google_Service_AdExchangeBuyerII_ListFilterSetsResponse
   */
  public function listAccountsFilterSets($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdExchangeBuyerII_ListFilterSetsResponse");
  }
}
