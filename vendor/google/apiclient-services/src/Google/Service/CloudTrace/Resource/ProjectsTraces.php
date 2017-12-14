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
 * The "traces" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudtraceService = new Google_Service_CloudTrace(...);
 *   $traces = $cloudtraceService->traces;
 *  </code>
 */
class Google_Service_CloudTrace_Resource_ProjectsTraces extends Google_Service_Resource
{
  /**
   * Sends new spans to Stackdriver Trace or updates existing traces. If the name
   * of a trace that you send matches that of an existing trace, new spans are
   * added to the existing trace. Attempt to update existing spans results
   * undefined behavior. If the name does not match, a new trace is created with
   * given set of spans. (traces.batchWrite)
   *
   * @param string $name Required. Name of the project where the spans belong. The
   * format is `projects/PROJECT_ID`.
   * @param Google_Service_CloudTrace_BatchWriteSpansRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTrace_CloudtraceEmpty
   */
  public function batchWrite($name, Google_Service_CloudTrace_BatchWriteSpansRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('batchWrite', array($params), "Google_Service_CloudTrace_CloudtraceEmpty");
  }
  /**
   * Returns of a list of traces that match the specified filter conditions.
   * (traces.listProjectsTraces)
   *
   * @param string $parent Required. The project where the trace data is stored.
   * The format is `projects/PROJECT_ID`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Opional. Return only traces that match this [trace
   * filter](/trace/docs/trace-filters). Example:
   *
   *     "label:/http/url root:/_ah/background my_label:17"
   * @opt_param string endTime Optional. Do not return traces whose start time is
   * later than this time.
   * @opt_param string pageToken Optional. If present, then retrieve the next
   * batch of results from the preceding call to this method.  `page_token` must
   * be the value of `next_page_token` from the previous response.  The values of
   * other method parameters should be identical to those in the previous call.
   * @opt_param string startTime Optional. Do not return traces whose end time is
   * earlier than this time.
   * @opt_param int pageSize Optional. The maximum number of results to return
   * from this request. Non-positive values are ignored. The presence of
   * `next_page_token` in the response indicates that more results might be
   * available, even if fewer than the maximum number of results is returned by
   * this request.
   * @opt_param string orderBy Optional. A single field used to sort the returned
   * traces. Only the following field names can be used:
   *
   * *   `trace_id`: the trace's ID field *   `name`:  the root span's resource
   * name *   `duration`: the difference between the root span's start time and
   * end time *   `start`:  the start time of the root span
   *
   * Sorting is in ascending order unless `desc` is appended to the sort field
   * name. Example: `"name desc"`).
   * @return Google_Service_CloudTrace_ListTracesResponse
   */
  public function listProjectsTraces($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudTrace_ListTracesResponse");
  }
  /**
   * Returns a list of spans within a trace. (traces.listSpans)
   *
   * @param string $parent Required: The resource name of the trace containing the
   * spans to list. The format is `projects/PROJECT_ID/traces/TRACE_ID`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional. If present, then retrieve the next
   * batch of results from the preceding call to this method. `page_token` must be
   * the value of `next_page_token` from the previous response. The values of
   * other method parameters should be identical to those in the previous call.
   * @return Google_Service_CloudTrace_ListSpansResponse
   */
  public function listSpans($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('listSpans', array($params), "Google_Service_CloudTrace_ListSpansResponse");
  }
}
