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
 * The "services" collection of methods.
 * Typical usage is:
 *  <code>
 *   $appengineService = new Google_Service_Appengine(...);
 *   $services = $appengineService->services;
 *  </code>
 */
class Google_Service_Appengine_Resource_AppsServices extends Google_Service_Resource
{
  /**
   * Deletes a service and all enclosed versions. (services.delete)
   *
   * @param string $appsId Part of `name`. Name of the resource requested. For
   * example: "apps/myapp/services/default".
   * @param string $servicesId Part of `name`. See documentation of `appsId`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Appengine_Operation
   */
  public function delete($appsId, $servicesId, $optParams = array())
  {
    $params = array('appsId' => $appsId, 'servicesId' => $servicesId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Appengine_Operation");
  }
  /**
   * Gets the current configuration of the service. (services.get)
   *
   * @param string $appsId Part of `name`. Name of the resource requested. For
   * example: "apps/myapp/services/default".
   * @param string $servicesId Part of `name`. See documentation of `appsId`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Appengine_Service
   */
  public function get($appsId, $servicesId, $optParams = array())
  {
    $params = array('appsId' => $appsId, 'servicesId' => $servicesId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Appengine_Service");
  }
  /**
   * Lists all the services in the application. (services.listAppsServices)
   *
   * @param string $appsId Part of `name`. Name of the resource requested. For
   * example: "apps/myapp".
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Maximum results to return per page.
   * @opt_param string pageToken Continuation token for fetching the next page of
   * results.
   * @return Google_Service_Appengine_ListServicesResponse
   */
  public function listAppsServices($appsId, $optParams = array())
  {
    $params = array('appsId' => $appsId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Appengine_ListServicesResponse");
  }
  /**
   * Updates the configuration of the specified service. (services.patch)
   *
   * @param string $appsId Part of `name`. Name of the resource to update. For
   * example: "apps/myapp/services/default".
   * @param string $servicesId Part of `name`. See documentation of `appsId`.
   * @param Google_Service_Appengine_Service $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string mask Standard field mask for the set of fields to be
   * updated.
   * @opt_param bool migrateTraffic Whether to use Traffic Migration to shift
   * traffic gradually. Traffic can only be migrated from a single version to
   * another single version.
   * @return Google_Service_Appengine_Operation
   */
  public function patch($appsId, $servicesId, Google_Service_Appengine_Service $postBody, $optParams = array())
  {
    $params = array('appsId' => $appsId, 'servicesId' => $servicesId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Appengine_Operation");
  }
}
