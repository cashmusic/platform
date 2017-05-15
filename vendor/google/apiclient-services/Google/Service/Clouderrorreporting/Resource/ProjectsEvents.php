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
 * The "events" collection of methods.
 * Typical usage is:
 *  <code>
 *   $clouderrorreportingService = new Google_Service_Clouderrorreporting(...);
 *   $events = $clouderrorreportingService->events;
 *  </code>
 */
class Google_Service_Clouderrorreporting_Resource_ProjectsEvents extends Google_Service_Resource
{
  /**
   * Lists the specified events. (events.listProjectsEvents)
   *
   * @param string $projectName The resource name of the Google Cloud Platform
   * project. Required. Example: projects/my-project
   * @param array $optParams Optional parameters.
   *
   * @opt_param string timeRange.period Restricts the query to the specified time
   * range.
   * @opt_param string serviceFilter.service The exact value to match against
   * [`ServiceContext.service`](/error-
   * reporting/reference/rest/v1beta1/ServiceContext#FIELDS.service).
   * @opt_param string groupId The group for which events shall be returned.
   * Required.
   * @opt_param string serviceFilter.version The exact value to match against
   * [`ServiceContext.version`](/error-
   * reporting/reference/rest/v1beta1/ServiceContext#FIELDS.version).
   * @opt_param int pageSize The maximum number of results to return per response.
   * @opt_param string pageToken A `next_page_token` provided by a previous
   * response.
   * @return Google_Service_Clouderrorreporting_ListEventsResponse
   */
  public function listProjectsEvents($projectName, $optParams = array())
  {
    $params = array('projectName' => $projectName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Clouderrorreporting_ListEventsResponse");
  }
}
