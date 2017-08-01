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
 * The "sites" collection of methods.
 * Typical usage is:
 *  <code>
 *   $adexperiencereportService = new Google_Service_AdExperienceReport(...);
 *   $sites = $adexperiencereportService->sites;
 *  </code>
 */
class Google_Service_AdExperienceReport_Resource_Sites extends Google_Service_Resource
{
  /**
   * Gets a summary of the ads rating of a site. (sites.get)
   *
   * @param string $name The required site name. It should be a site property
   * registered in Search Console. The server will return an error of BAD_REQUEST
   * if this field is not filled in.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AdExperienceReport_SiteSummaryResponse
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdExperienceReport_SiteSummaryResponse");
  }
}
