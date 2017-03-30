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
 * The "enterprises" collection of methods.
 * Typical usage is:
 *  <code>
 *   $androidenterpriseService = new Google_Service_AndroidEnterprise(...);
 *   $enterprises = $androidenterpriseService->enterprises;
 *  </code>
 */
class Google_Service_AndroidEnterprise_Resource_Enterprises extends Google_Service_Resource
{
  /**
   * Deletes the binding between the EMM and enterprise. This is now deprecated;
   * use this to unenroll customers that were previously enrolled with the
   * 'insert' call, then enroll them again with the 'enroll' call.
   * (enterprises.delete)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   */
  public function delete($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Enrolls an enterprise with the calling EMM. (enterprises.enroll)
   *
   * @param string $token The token provided by the enterprise to register the
   * EMM.
   * @param Google_Service_AndroidEnterprise_Enterprise $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Enterprise
   */
  public function enroll($token, Google_Service_AndroidEnterprise_Enterprise $postBody, $optParams = array())
  {
    $params = array('token' => $token, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('enroll', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }
  /**
   * Retrieves the name and domain of an enterprise. (enterprises.get)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Enterprise
   */
  public function get($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }
  /**
   * Returns the store layout resource. (enterprises.getStoreLayout)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_StoreLayout
   */
  public function getStoreLayout($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('getStoreLayout', array($params), "Google_Service_AndroidEnterprise_StoreLayout");
  }
  /**
   * Establishes the binding between the EMM and an enterprise. This is now
   * deprecated; use enroll instead. (enterprises.insert)
   *
   * @param string $token The token provided by the enterprise to register the
   * EMM.
   * @param Google_Service_AndroidEnterprise_Enterprise $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Enterprise
   */
  public function insert($token, Google_Service_AndroidEnterprise_Enterprise $postBody, $optParams = array())
  {
    $params = array('token' => $token, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }
  /**
   * Looks up an enterprise by domain name. (enterprises.listEnterprises)
   *
   * @param string $domain The exact primary domain name of the enterprise to look
   * up.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_EnterprisesListResponse
   */
  public function listEnterprises($domain, $optParams = array())
  {
    $params = array('domain' => $domain);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_EnterprisesListResponse");
  }
  /**
   * Sends a test push notification to validate the EMM integration with the
   * Google Cloud Pub/Sub service for this enterprise.
   * (enterprises.sendTestPushNotification)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_EnterprisesSendTestPushNotificationResponse
   */
  public function sendTestPushNotification($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('sendTestPushNotification', array($params), "Google_Service_AndroidEnterprise_EnterprisesSendTestPushNotificationResponse");
  }
  /**
   * Set the account that will be used to authenticate to the API as the
   * enterprise. (enterprises.setAccount)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param Google_Service_AndroidEnterprise_EnterpriseAccount $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_EnterpriseAccount
   */
  public function setAccount($enterpriseId, Google_Service_AndroidEnterprise_EnterpriseAccount $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setAccount', array($params), "Google_Service_AndroidEnterprise_EnterpriseAccount");
  }
  /**
   * Sets the store layout resource. (enterprises.setStoreLayout)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param Google_Service_AndroidEnterprise_StoreLayout $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_StoreLayout
   */
  public function setStoreLayout($enterpriseId, Google_Service_AndroidEnterprise_StoreLayout $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setStoreLayout', array($params), "Google_Service_AndroidEnterprise_StoreLayout");
  }
  /**
   * Unenrolls an enterprise from the calling EMM. (enterprises.unenroll)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   */
  public function unenroll($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('unenroll', array($params));
  }
}
