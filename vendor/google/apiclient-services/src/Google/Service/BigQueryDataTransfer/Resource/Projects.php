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
 * The "projects" collection of methods.
 * Typical usage is:
 *  <code>
 *   $bigquerydatatransferService = new Google_Service_BigQueryDataTransfer(...);
 *   $projects = $bigquerydatatransferService->projects;
 *  </code>
 */
class Google_Service_BigQueryDataTransfer_Resource_Projects extends Google_Service_Resource
{
  /**
   * Returns true if data transfer is enabled for a project. (projects.isEnabled)
   *
   * @param string $name The name of the project resource in the form:
   * `projects/{project_id}`
   * @param Google_Service_BigQueryDataTransfer_IsEnabledRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_BigQueryDataTransfer_IsEnabledResponse
   */
  public function isEnabled($name, Google_Service_BigQueryDataTransfer_IsEnabledRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('isEnabled', array($params), "Google_Service_BigQueryDataTransfer_IsEnabledResponse");
  }
  /**
   * Enables or disables data transfer for a project. This method requires the
   * additional scope of 'https://www.googleapis.com/auth/cloudplatformprojects'
   * to manage the cloud project permissions. (projects.setEnabled)
   *
   * @param string $name The name of the project resource in the form:
   * `projects/{project_id}`
   * @param Google_Service_BigQueryDataTransfer_SetEnabledRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_BigQueryDataTransfer_BigquerydatatransferEmpty
   */
  public function setEnabled($name, Google_Service_BigQueryDataTransfer_SetEnabledRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setEnabled', array($params), "Google_Service_BigQueryDataTransfer_BigquerydatatransferEmpty");
  }
}
