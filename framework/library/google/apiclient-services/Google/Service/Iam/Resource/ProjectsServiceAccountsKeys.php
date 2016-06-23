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
 * The "keys" collection of methods.
 * Typical usage is:
 *  <code>
 *   $iamService = new Google_Service_Iam(...);
 *   $keys = $iamService->keys;
 *  </code>
 */
class Google_Service_Iam_Resource_ProjectsServiceAccountsKeys extends Google_Service_Resource
{
  /**
   * Creates a service account key and returns it. (keys.create)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". Using '-' as a wildcard for
   * the project, will infer the project from the account. The account value can
   * be the email address or the unique_id of the service account.
   * @param Google_Service_Iam_CreateServiceAccountKeyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_ServiceAccountKey
   */
  public function create($name, Google_Service_Iam_CreateServiceAccountKeyRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Iam_ServiceAccountKey");
  }
  /**
   * Deletes a service account key. (keys.delete)
   *
   * @param string $name The resource name of the service account key in the
   * format "projects/{project}/serviceAccounts/{account}/keys/{key}". Using '-'
   * as a wildcard for the project will infer the project from the account. The
   * account value can be the email address or the unique_id of the service
   * account.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_IamEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Iam_IamEmpty");
  }
  /**
   * Gets the ServiceAccountKey by key id. (keys.get)
   *
   * @param string $name The resource name of the service account key in the
   * format "projects/{project}/serviceAccounts/{account}/keys/{key}". Using '-'
   * as a wildcard for the project will infer the project from the account. The
   * account value can be the email address or the unique_id of the service
   * account.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_ServiceAccountKey
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Iam_ServiceAccountKey");
  }
  /**
   * Lists service account keys (keys.listProjectsServiceAccountsKeys)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". Using '-' as a wildcard for
   * the project, will infer the project from the account. The account value can
   * be the email address or the unique_id of the service account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string keyTypes The type of keys the user wants to list. If empty,
   * all key types are included in the response. Duplicate key types are not
   * allowed.
   * @return Google_Service_Iam_ListServiceAccountKeysResponse
   */
  public function listProjectsServiceAccountsKeys($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Iam_ListServiceAccountKeysResponse");
  }
}
