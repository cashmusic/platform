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
 * The "courseWork" collection of methods.
 * Typical usage is:
 *  <code>
 *   $classroomService = new Google_Service_Classroom(...);
 *   $courseWork = $classroomService->courseWork;
 *  </code>
 */
class Google_Service_Classroom_Resource_CoursesCourseWork extends Google_Service_Resource
{
  /**
   * Creates course work. The resulting course work (and corresponding student
   * submissions) are associated with the Developer Console project of the [OAuth
   * client ID](https://support.google.com/cloud/answer/6158849) used to make the
   * request. Classroom API requests to modify course work and student submissions
   * must be made with an OAuth client ID from the associated Developer Console
   * project. This method returns the following error codes: * `PERMISSION_DENIED`
   * if the requesting user is not permitted to access the requested course,
   * create course work in the requested course, or for access errors. *
   * `INVALID_ARGUMENT` if the request is malformed. * `NOT_FOUND` if the
   * requested course does not exist. (courseWork.create)
   *
   * @param string $courseId Identifier of the course. This identifier can be
   * either the Classroom-assigned identifier or an alias.
   * @param Google_Service_Classroom_CourseWork $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Classroom_CourseWork
   */
  public function create($courseId, Google_Service_Classroom_CourseWork $postBody, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_CourseWork");
  }
  /**
   * Returns course work. This method returns the following error codes: *
   * `PERMISSION_DENIED` if the requesting user is not permitted to access the
   * requested course or course work, or for access errors. * `INVALID_ARGUMENT`
   * if the request is malformed. * `NOT_FOUND` if the requested course or course
   * work does not exist. (courseWork.get)
   *
   * @param string $courseId Identifier of the course. This identifier can be
   * either the Classroom-assigned identifier or an alias.
   * @param string $id Identifier of the course work.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Classroom_CourseWork
   */
  public function get($courseId, $id, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_CourseWork");
  }
  /**
   * Returns a list of course work that the requester is permitted to view. Course
   * students may only view `PUBLISHED` course work. Course teachers and domain
   * administrators may view all course work. This method returns the following
   * error codes: * `PERMISSION_DENIED` if the requesting user is not permitted to
   * access the requested course or for access errors. * `INVALID_ARGUMENT` if the
   * request is malformed. * `NOT_FOUND` if the requested course does not exist.
   * (courseWork.listCoursesCourseWork)
   *
   * @param string $courseId Identifier of the course. This identifier can be
   * either the Classroom-assigned identifier or an alias.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string courseWorkStates Restriction on the work status to return.
   * Only courseWork that matches is returned. If unspecified, items with a work
   * status of `PUBLISHED` is returned.
   * @opt_param string orderBy Optional sort ordering for results. A comma-
   * separated list of fields with an optional sort direction keyword. Supported
   * fields are `updateTime` and `dueDate`. Supported direction keywords are `asc`
   * and `desc`. If not specified, `updateTime desc` is the default behavior.
   * Examples: `dueDate asc,updateTime desc`, `updateTime,dueDate desc`
   * @opt_param int pageSize Maximum number of items to return. Zero or
   * unspecified indicates that the server may assign a maximum. The server may
   * return fewer than the specified number of results.
   * @opt_param string pageToken nextPageToken value returned from a previous list
   * call, indicating that the subsequent page of results should be returned. The
   * list request must be otherwise identical to the one that resulted in this
   * token.
   * @return Google_Service_Classroom_ListCourseWorkResponse
   */
  public function listCoursesCourseWork($courseId, $optParams = array())
  {
    $params = array('courseId' => $courseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListCourseWorkResponse");
  }
}
