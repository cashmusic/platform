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
 * The "sinks" collection of methods.
 * Typical usage is:
 *  <code>
 *   $loggingService = new Google_Service_Logging(...);
 *   $sinks = $loggingService->sinks;
 *  </code>
 */
class Google_Service_Logging_Resource_ProjectsSinks extends Google_Service_Resource
{
  /**
   * Creates a sink. (sinks.create)
   *
   * @param string $projectName The resource name of the project in which to
   * create the sink. Example: `"projects/my-project-id"`. The new sink must be
   * provided in the request.
   * @param Google_Service_Logging_LogSink $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Logging_LogSink
   */
  public function create($projectName, Google_Service_Logging_LogSink $postBody, $optParams = array())
  {
    $params = array('projectName' => $projectName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Logging_LogSink");
  }
  /**
   * Deletes a sink. (sinks.delete)
   *
   * @param string $sinkName The resource name of the sink to delete. Example:
   * `"projects/my-project-id/sinks/my-sink-id"`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Logging_LoggingEmpty
   */
  public function delete($sinkName, $optParams = array())
  {
    $params = array('sinkName' => $sinkName);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Logging_LoggingEmpty");
  }
  /**
   * Gets a sink. (sinks.get)
   *
   * @param string $sinkName The resource name of the sink to return. Example:
   * `"projects/my-project-id/sinks/my-sink-id"`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Logging_LogSink
   */
  public function get($sinkName, $optParams = array())
  {
    $params = array('sinkName' => $sinkName);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Logging_LogSink");
  }
  /**
   * Lists sinks. (sinks.listProjectsSinks)
   *
   * @param string $projectName Required. The resource name of the project
   * containing the sinks. Example: `"projects/my-logging-project"`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional. If the `pageToken` parameter is
   * supplied, then the next page of results is retrieved. The `pageToken`
   * parameter must be set to the value of the `nextPageToken` from the previous
   * response. The value of `projectName` must be the same as in the previous
   * request.
   * @opt_param int pageSize Optional. The maximum number of results to return
   * from this request. You must check for presence of `nextPageToken` to
   * determine if additional results are available, which you can retrieve by
   * passing the `nextPageToken` value as the `pageToken` parameter in the next
   * request.
   * @return Google_Service_Logging_ListSinksResponse
   */
  public function listProjectsSinks($projectName, $optParams = array())
  {
    $params = array('projectName' => $projectName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Logging_ListSinksResponse");
  }
  /**
   * Creates or updates a sink. (sinks.update)
   *
   * @param string $sinkName The resource name of the sink to update. Example:
   * `"projects/my-project-id/sinks/my-sink-id"`. The updated sink must be
   * provided in the request and have the same name that is specified in
   * `sinkName`. If the sink does not exist, it is created.
   * @param Google_Service_Logging_LogSink $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Logging_LogSink
   */
  public function update($sinkName, Google_Service_Logging_LogSink $postBody, $optParams = array())
  {
    $params = array('sinkName' => $sinkName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Logging_LogSink");
  }
}
