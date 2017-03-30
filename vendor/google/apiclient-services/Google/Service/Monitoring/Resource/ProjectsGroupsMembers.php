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
 * The "members" collection of methods.
 * Typical usage is:
 *  <code>
 *   $monitoringService = new Google_Service_Monitoring(...);
 *   $members = $monitoringService->members;
 *  </code>
 */
class Google_Service_Monitoring_Resource_ProjectsGroupsMembers extends Google_Service_Resource
{
  /**
   * Lists the monitored resources that are members of a group.
   * (members.listProjectsGroupsMembers)
   *
   * @param string $name The group whose members are listed. The format is
   * `"projects/{project_id_or_number}/groups/{group_id}"`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize A positive number that is the maximum number of
   * results to return.
   * @opt_param string pageToken If this field is not empty then it must contain
   * the `nextPageToken` value returned by a previous call to this method. Using
   * this field causes the method to return additional results from the previous
   * method call.
   * @opt_param string filter An optional [list
   * filter](/monitoring/api/learn_more#filtering) describing the members to be
   * returned. The filter may reference the type, labels, and metadata of
   * monitored resources that comprise the group. For example, to return only
   * resources representing Compute Engine VM instances, use this filter:
   * resource.type = "gce_instance"
   * @opt_param string interval.endTime Required. The end of the interval. The
   * interval includes this time.
   * @opt_param string interval.startTime If this value is omitted, the interval
   * is a point in time, `endTime`. If `startTime` is present, it must be earlier
   * than (less than) `endTime`. The interval begins after `startTime`—it does not
   * include `startTime`.
   * @return Google_Service_Monitoring_ListGroupMembersResponse
   */
  public function listProjectsGroupsMembers($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Monitoring_ListGroupMembersResponse");
  }
}
