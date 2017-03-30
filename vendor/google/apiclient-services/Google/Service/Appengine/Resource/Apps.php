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
 * The "apps" collection of methods.
 * Typical usage is:
 *  <code>
 *   $appengineService = new Google_Service_Appengine(...);
 *   $apps = $appengineService->apps;
 *  </code>
 */
class Google_Service_Appengine_Resource_Apps extends Google_Service_Resource
{
  /**
   * Gets information about an application. (apps.get)
   *
   * @param string $appsId Part of `name`. Name of the application to get. For
   * example: "apps/myapp".
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool ensureResourcesExist Certain resources associated with an
   * application are created on-demand. Controls whether these resources should be
   * created when performing the `GET` operation. If specified and any resources
   * could not be created, the request will fail with an error code. Additionally,
   * this parameter can cause the request to take longer to complete. Note: This
   * parameter will be deprecated in a future version of the API.
   * @return Google_Service_Appengine_Application
   */
  public function get($appsId, $optParams = array())
  {
    $params = array('appsId' => $appsId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Appengine_Application");
  }
}
