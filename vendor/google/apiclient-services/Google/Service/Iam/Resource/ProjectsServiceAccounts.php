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
 * The "serviceAccounts" collection of methods.
 * Typical usage is:
 *  <code>
 *   $iamService = new Google_Service_Iam(...);
 *   $serviceAccounts = $iamService->serviceAccounts;
 *  </code>
 */
class Google_Service_Iam_Resource_ProjectsServiceAccounts extends Google_Service_Resource
{
  /**
   * Creates a service account and returns it. (serviceAccounts.create)
   *
   * @param string $name Required. The resource name of the project associated
   * with the service accounts, such as "projects/123"
   * @param Google_Service_Iam_CreateServiceAccountRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_ServiceAccount
   */
  public function create($name, Google_Service_Iam_CreateServiceAccountRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Iam_ServiceAccount");
  }
  /**
   * Deletes a service acount. (serviceAccounts.delete)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". Using '-' as a wildcard for
   * the project, will infer the project from the account. The account value can
   * be the email address or the unique_id of the service account.
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
   * Gets a ServiceAccount (serviceAccounts.get)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". Using '-' as a wildcard for
   * the project, will infer the project from the account. The account value can
   * be the email address or the unique_id of the service account.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_ServiceAccount
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Iam_ServiceAccount");
  }
  /**
   * Returns the IAM access control policy for specified IAM resource.
   * (serviceAccounts.getIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * requested. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `getIamPolicy`
   * documentation.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_Policy
   */
  public function getIamPolicy($resource, $optParams = array())
  {
    $params = array('resource' => $resource);
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', array($params), "Google_Service_Iam_Policy");
  }
  /**
   * Lists service accounts for a project.
   * (serviceAccounts.listProjectsServiceAccounts)
   *
   * @param string $name Required. The resource name of the project associated
   * with the service accounts, such as "projects/123"
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional limit on the number of service accounts to
   * include in the response. Further accounts can subsequently be obtained by
   * including the [ListServiceAccountsResponse.next_page_token] in a subsequent
   * request.
   * @opt_param string pageToken Optional pagination token returned in an earlier
   * [ListServiceAccountsResponse.next_page_token].
   * @return Google_Service_Iam_ListServiceAccountsResponse
   */
  public function listProjectsServiceAccounts($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Iam_ListServiceAccountsResponse");
  }
  /**
   * Sets the IAM access control policy for the specified IAM resource.
   * (serviceAccounts.setIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * specified. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `setIamPolicy`
   * documentation.
   * @param Google_Service_Iam_SetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_Policy
   */
  public function setIamPolicy($resource, Google_Service_Iam_SetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', array($params), "Google_Service_Iam_Policy");
  }
  /**
   * Signs a blob using a service account. (serviceAccounts.signBlob)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". Using '-' as a wildcard for
   * the project, will infer the project from the account. The account value can
   * be the email address or the unique_id of the service account.
   * @param Google_Service_Iam_SignBlobRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_SignBlobResponse
   */
  public function signBlob($name, Google_Service_Iam_SignBlobRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('signBlob', array($params), "Google_Service_Iam_SignBlobResponse");
  }
  /**
   * Tests the specified permissions against the IAM access control policy for the
   * specified IAM resource. (serviceAccounts.testIamPermissions)
   *
   * @param string $resource REQUIRED: The resource for which the policy detail is
   * being requested. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `testIamPermissions`
   * documentation.
   * @param Google_Service_Iam_TestIamPermissionsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_TestIamPermissionsResponse
   */
  public function testIamPermissions($resource, Google_Service_Iam_TestIamPermissionsRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', array($params), "Google_Service_Iam_TestIamPermissionsResponse");
  }
  /**
   * Updates a service account. Currently, only the following fields are
   * updatable: 'display_name' . The 'etag' is mandatory. (serviceAccounts.update)
   *
   * @param string $name The resource name of the service account in the format
   * "projects/{project}/serviceAccounts/{account}". In requests using '-' as a
   * wildcard for the project, will infer the project from the account and the
   * account value can be the email address or the unique_id of the service
   * account. In responses the resource name will always be in the format
   * "projects/{project}/serviceAccounts/{email}".
   * @param Google_Service_Iam_ServiceAccount $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_ServiceAccount
   */
  public function update($name, Google_Service_Iam_ServiceAccount $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Iam_ServiceAccount");
  }
}
