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
 * The "users" collection of methods.
 * Typical usage is:
 *  <code>
 *   $androidenterpriseService = new Google_Service_AndroidEnterprise(...);
 *   $users = $androidenterpriseService->users;
 *  </code>
 */
class Google_Service_AndroidEnterprise_Resource_Users extends Google_Service_Resource
{
  /**
   * Generates a token (activation code) to allow this user to configure their
   * work account in the Android Setup Wizard. Revokes any previously generated
   * token.
   *
   * This call only works with Google managed accounts. (users.generateToken)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_UserToken
   */
  public function generateToken($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('generateToken', array($params), "Google_Service_AndroidEnterprise_UserToken");
  }
  /**
   * Retrieves a user's details. (users.get)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_User
   */
  public function get($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_User");
  }
  /**
   * Retrieves the set of products a user is entitled to access.
   * (users.getAvailableProductSet)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_ProductSet
   */
  public function getAvailableProductSet($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('getAvailableProductSet', array($params), "Google_Service_AndroidEnterprise_ProductSet");
  }
  /**
   * Looks up a user by their primary email address. (users.listUsers)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $email The exact primary email address of the user to look up.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_UsersListResponse
   */
  public function listUsers($enterpriseId, $email, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'email' => $email);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_UsersListResponse");
  }
  /**
   * Revokes a previously generated token (activation code) for the user.
   * (users.revokeToken)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   */
  public function revokeToken($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('revokeToken', array($params));
  }
  /**
   * Modifies the set of products a user is entitled to access.
   * (users.setAvailableProductSet)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $userId The ID of the user.
   * @param Google_Service_AndroidEnterprise_ProductSet $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_ProductSet
   */
  public function setAvailableProductSet($enterpriseId, $userId, Google_Service_AndroidEnterprise_ProductSet $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setAvailableProductSet', array($params), "Google_Service_AndroidEnterprise_ProductSet");
  }
}
