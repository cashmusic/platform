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
 * The "projects" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudresourcemanagerService = new Google_Service_CloudResourceManager(...);
 *   $projects = $cloudresourcemanagerService->projects;
 *  </code>
 */
class Google_Service_CloudResourceManager_Resource_Projects extends Google_Service_Resource
{
  /**
   * Marks the Project identified by the specified `project_id` (for example, `my-
   * project-123`) for deletion. This method will only affect the Project if the
   * following criteria are met: + The Project does not have a billing account
   * associated with it. + The Project has a lifecycle state of ACTIVE. This
   * method changes the Project's lifecycle state from ACTIVE to DELETE_REQUESTED.
   * The deletion starts at an unspecified time, at which point the lifecycle
   * state changes to DELETE_IN_PROGRESS. Until the deletion completes, you can
   * check the lifecycle state checked by retrieving the Project with GetProject,
   * and the Project remains visible to ListProjects. However, you cannot update
   * the project. After the deletion completes, the Project is not retrievable by
   * the GetProject and ListProjects methods. The caller must have modify
   * permissions for this Project. (projects.delete)
   *
   * @param string $projectId The Project ID (for example, `foo-bar-123`).
   * Required.
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_CloudresourcemanagerEmpty
   */
  public function delete($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudResourceManager_CloudresourcemanagerEmpty");
  }
  /**
   * Retrieves the Project identified by the specified `project_id` (for example,
   * `my-project-123`). The caller must have read permissions for this Project.
   * (projects.get)
   *
   * @param string $projectId The Project ID (for example, `my-project-123`).
   * Required.
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Project
   */
  public function get($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudResourceManager_Project");
  }
  /**
   * Returns the IAM access control policy for the specified Project. Permission
   * is denied if the policy or the resource does not exist.
   * (projects.getIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * requested. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `getIamPolicy`
   * documentation.
   * @param Google_Service_CloudResourceManager_GetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Policy
   */
  public function getIamPolicy($resource, Google_Service_CloudResourceManager_GetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', array($params), "Google_Service_CloudResourceManager_Policy");
  }
  /**
   * Lists Projects that are visible to the user and satisfy the specified filter.
   * This method returns Projects in an unspecified order. New Projects do not
   * necessarily appear at the end of the list. (projects.listProjects)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken A pagination token returned from a previous call
   * to ListProjects that indicates from where listing should continue. Optional.
   * @opt_param int pageSize The maximum number of Projects to return in the
   * response. The server can return fewer Projects than requested. If
   * unspecified, server picks an appropriate default. Optional.
   * @opt_param string filter An expression for filtering the results of the
   * request. Filter rules are case insensitive. The fields eligible for filtering
   * are: + `name` + `id` + labels.key where *key* is the name of a label Some
   * examples of using labels as filters: |Filter|Description|
   * |------|-----------| |name:*|The project has a name.| |name:Howl|The
   * project's name is `Howl` or `howl`.| |name:HOWL|Equivalent to above.|
   * |NAME:howl|Equivalent to above.| |labels.color:*|The project has the label
   * `color`.| |labels.color:red|The project's label `color` has the value `red`.|
   * |labels.color:red label.size:big|The project's label `color` has the value
   * `red` and its label `size` has the value `big`. Optional.
   * @return Google_Service_CloudResourceManager_ListProjectsResponse
   */
  public function listProjects($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudResourceManager_ListProjectsResponse");
  }
  /**
   * Sets the IAM access control policy for the specified Project. Replaces any
   * existing policy. The following constraints apply when using `setIamPolicy()`:
   * + Project currently supports only `user:{emailid}` and
   * `serviceAccount:{emailid}` members in a `Binding` of a `Policy`. + To be
   * added as an `owner`, a user must be invited via Cloud Platform console and
   * must accept the invitation. + Members cannot be added to more than one role
   * in the same policy. + There must be at least one owner who has accepted the
   * Terms of Service (ToS) agreement in the policy. Calling `setIamPolicy()` to
   * to remove the last ToS-accepted owner from the policy will fail. + Calling
   * this method requires enabling the App Engine Admin API. Note: Removing
   * service accounts from policies or changing their roles can render services
   * completely inoperable. It is important to understand how the service account
   * is being used before removing or updating its roles. (projects.setIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * specified. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `setIamPolicy`
   * documentation.
   * @param Google_Service_CloudResourceManager_SetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Policy
   */
  public function setIamPolicy($resource, Google_Service_CloudResourceManager_SetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', array($params), "Google_Service_CloudResourceManager_Policy");
  }
  /**
   * Returns permissions that a caller has on the specified Project.
   * (projects.testIamPermissions)
   *
   * @param string $resource REQUIRED: The resource for which the policy detail is
   * being requested. `resource` is usually specified as a path, such as
   * `projectsprojectzoneszonedisksdisk*`. The format for the path specified in
   * this value is resource specific and is specified in the `testIamPermissions`
   * documentation.
   * @param Google_Service_CloudResourceManager_TestIamPermissionsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_TestIamPermissionsResponse
   */
  public function testIamPermissions($resource, Google_Service_CloudResourceManager_TestIamPermissionsRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', array($params), "Google_Service_CloudResourceManager_TestIamPermissionsResponse");
  }
  /**
   * Restores the Project identified by the specified `project_id` (for example,
   * `my-project-123`). You can only use this method for a Project that has a
   * lifecycle state of DELETE_REQUESTED. After deletion starts, as indicated by a
   * lifecycle state of DELETE_IN_PROGRESS, the Project cannot be restored. The
   * caller must have modify permissions for this Project. (projects.undelete)
   *
   * @param string $projectId The project ID (for example, `foo-bar-123`).
   * Required.
   * @param Google_Service_CloudResourceManager_UndeleteProjectRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_CloudresourcemanagerEmpty
   */
  public function undelete($projectId, Google_Service_CloudResourceManager_UndeleteProjectRequest $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('undelete', array($params), "Google_Service_CloudResourceManager_CloudresourcemanagerEmpty");
  }
  /**
   * Updates the attributes of the Project identified by the specified
   * `project_id` (for example, `my-project-123`). The caller must have modify
   * permissions for this Project. (projects.update)
   *
   * @param string $projectId The project ID (for example, `my-project-123`).
   * Required.
   * @param Google_Service_CloudResourceManager_Project $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Project
   */
  public function update($projectId, Google_Service_CloudResourceManager_Project $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_CloudResourceManager_Project");
  }
}
