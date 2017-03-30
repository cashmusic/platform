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
 * The "instances" collection of methods.
 * Typical usage is:
 *  <code>
 *   $appengineService = new Google_Service_Appengine(...);
 *   $instances = $appengineService->instances;
 *  </code>
 */
class Google_Service_Appengine_Resource_AppsServicesVersionsInstances extends Google_Service_Resource
{
  /**
   * Lists the instances of a version.
   * (instances.listAppsServicesVersionsInstances)
   *
   * @param string $appsId Part of `name`. Name of the resource requested. For
   * example: "apps/myapp/services/default/versions/v1".
   * @param string $servicesId Part of `name`. See documentation of `appsId`.
   * @param string $versionsId Part of `name`. See documentation of `appsId`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Maximum results to return per page.
   * @opt_param string pageToken Continuation token for fetching the next page of
   * results.
   * @return Google_Service_Appengine_ListInstancesResponse
   */
  public function listAppsServicesVersionsInstances($appsId, $servicesId, $versionsId, $optParams = array())
  {
    $params = array('appsId' => $appsId, 'servicesId' => $servicesId, 'versionsId' => $versionsId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Appengine_ListInstancesResponse");
  }
}
